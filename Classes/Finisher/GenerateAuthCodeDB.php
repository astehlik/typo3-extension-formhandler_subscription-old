<?php
namespace Tx\FormhandlerSubscription\Finisher;

/*                                                                        *
 * This script belongs to the TYPO3 extension "formhandler_subscription". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Tx\Authcode\Domain\Enumeration\AuthCodeAction;
use Tx\FormhandlerSubscription\Exceptions\MissingSettingException;
use Tx\FormhandlerSubscription\Utils\AuthCodeUtils;
use Tx_Formhandler_Finisher_GenerateAuthCode as FormhandlerAuthCodeFinisher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Generates an auth code and stores it in the database
 *
 * Works similiar to Tx_Formhandler_Finisher_GenerateAuthCode but the generated
 * auth code is stored in the database and it references another record in the
 * database.
 *
 * At the moment two actions can be authorized with a generated auth code: accessing
 * a form (accessForm) and unhiding the referenced record (enableRecord).
 */
class GenerateAuthCodeDB extends FormhandlerAuthCodeFinisher {

	/**
	 * The action that will be executed when the user provides
	 * the correct auth code
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * @var \Tx\Authcode\Domain\Repository\AuthCodeRepository
	 */
	protected $authCodeRepository;

	/**
	 * The field that marks the referenced record as hidden
	 *
	 * @var string
	 */
	protected $hiddenField = '';

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * The table that contains the records that are referenced
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Tiny URL API
	 *
	 * @var \Tx_Tinyurls_TinyUrl_Api
	 */
	protected $tinyUrlApi;

	/**
	 * The field that contains the uid of the referenced record
	 *
	 * @var string
	 */
	protected $uidField = 'uid';

	/**
	 * Auth code related utility functions
	 *
	 * @var AuthCodeUtils
	 */
	protected $utils;

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @param array $gp
	 * @param array $settings
	 * @throws MissingSettingException If not all requires settings have heen set
	 * @return void
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		if (!isset($this->utils)) {
			$this->utils = AuthCodeUtils::getInstance();
		}

		if (!isset($this->objectManager))  {
			$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
		}

		if (!isset($this->authCodeRepository)) {
			$this->authCodeRepository = $this->objectManager->get('Tx\\Authcode\\Domain\\Repository\\AuthCodeRepository');
		}

		if (!$this->settings['table']) {
			throw new MissingSettingException('table');
		} else {
			$this->table = (string)$this->utilityFuncs->getSingle($this->settings, 'table');
		}

		if ($this->settings['uidField']) {
			$this->uidField = $this->settings['uidField'];
		}

		if (!empty($this->settings['action'])) {
			if ($this->settings['action'] === 'accessForm') {
				$this->utilityFuncs->debugMessage('Using the accessForm action for the GenerateAuthCodeDB finisher is deprecated! Use accessPage instead.', array(), 2);
				GeneralUtility::deprecationLog('formhandler_subscription: Using the accessForm action for the GenerateAuthCodeDB finisher is deprecated. Use accessPage instead.');
				$this->settings['action'] = AuthCodeAction::ACCESS_PAGE;
			}
			$this->action = $this->settings['action'];
		} else {
			$this->action = AuthCodeAction::RECORD_ENABLE;
		}

		$this->utils->checkAuthCodeAction($this->action);

		if ($this->settings['hiddenField']) {
			$this->hiddenField = $this->settings['hiddenField'];
		} elseif ($GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['disabled']) {
			$this->hiddenField = $GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['disabled'];
		} else {
			$this->hiddenField = 'hidden';
		}
	}

	/**
	 * Returns the action that is bound to the current auth code
	 *
	 * @return string
	 */
	public function getAuthCodeAction() {
		return $this->action;
	}

	/**
	 * Returns the name of the table field that disables the referenced record
	 *
	 * @return string
	 */
	public function getHiddenFieldName() {
		return $this->hiddenField;
	}

	/**
	 * Returns the name of the table that contains the referenced record
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->table;
	}

	/**
	 * Returns the name of the table field that contains the uid of the referenced record
	 *
	 * @return string
	 */
	public function getUidFieldName() {
		return $this->uidField;
	}

	/**
	 * Checks, if the form values prefix should be overwritten
	 * and sets it to the configured value
	 *
	 * @return array the GET/POST data array
	 */
	public function process() {

		$currentFormValuesPrefix = $this->globals->getFormValuesPrefix();

		if (!empty($this->settings['overrideFormValuesPrefix'])) {
			$this->globals->setFormValuesPrefix($this->settings['overrideFormValuesPrefix']);
		}

		$independentMode = FALSE;
		if ($this->settings['independentMode']) {
			$independentMode = $this->utilityFuncs->getSingle($this->settings, 'independentMode');
		}

		if ($independentMode) {
			$this->generateRecordIndependentAuthCode();
		} else {
			parent::process();
		}

		$this->generateTinyUrl();

		$this->globals->setFormValuesPrefix($currentFormValuesPrefix);

		return $this->gp;
	}

	/**
	 * Injector for auth code utilities
	 *
	 * @param $authCodeUtilities AuthCodeUtils
	 */
	public function setAuthCodeUtils($authCodeUtilities) {
		$this->utils = $authCodeUtilities;
	}

	/**
	 * Injector for tiny URL API
	 *
	 * @param $tinyUrlApi \Tx_Tinyurls_TinyUrl_Api
	 */
	public function setTinyUrlApi($tinyUrlApi) {
		$this->tinyUrlApi = $tinyUrlApi;
	}

	/**
	 * Creates a new record auth code and returns a hash value to send by email as an auth code.
	 *
	 * @param array $row The submitted form data
	 * @return string The auth code
	 */
	protected function generateAuthCode($row) {

		/** @var \Tx\Authcode\Domain\Model\AuthCode $authCode */
		$authCode = $this->objectManager->get('Tx\\Authcode\\Domain\\Model\\AuthCode');

		$authCode->setReferenceTableUidField($this->uidField);
		$authCode->setReferenceTableHiddenField($this->hiddenField);

		$this->authCodeRepository->generateRecordAuthCode($authCode, $this->action, $this->table, $row[$this->uidField]);

		return $authCode->getAuthCode();
	}

	/**
	 * Generates an auth code that is independent from a database record.
	 *
	 * @throws MissingSettingException
	 */
	protected function generateRecordIndependentAuthCode() {

		$identifier = '';
		$context = '';

		if ($this->settings['identifier']) {
			$identifier = trim($this->utilityFuncs->getSingle($this->settings, 'identifier'));
		}

		if (empty($identifier)) {
			throw new MissingSettingException('identifier');
		}

		if ($this->settings['context']) {
			$context = trim($this->utilityFuncs->getSingle($this->settings, 'context'));
		}

		if (empty($context)) {
			throw new MissingSettingException('context');
		}

		/** @var \Tx\Authcode\Domain\Model\AuthCode $authCodeRecord */
		$authCodeRecord = $this->objectManager->get('Tx\\Authcode\\Domain\\Model\\AuthCode');
		$this->authCodeRepository->generateIndependentAuthCode($authCodeRecord, $identifier, $context);
		$authCode = $authCodeRecord->getAuthCode();

		$this->gp['generated_authCode'] = $authCode;

		// Looking for the page, which should be used for the authCode Link:
		// first look for TS-setting 'authCodePage'
		// second look for redirect_page-setting
		// third use current page
		if (isset($this->settings['authCodePage'])) {
			$authCodePage = $this->utilityFuncs->getSingle($this->settings, 'authCodePage');
		} else {
			$authCodePage = $this->utilityFuncs->pi_getFFvalue($this->cObj->data['pi_flexform'], 'redirect_page', 'sMISC');
		}
		if (!$authCodePage) {
			$authCodePage = $GLOBALS['TSFE']->id;
		}

		// Create the parameter-array for the authCode Link
		$paramsArray = array('authCode' => $authCode);

		// If we have set a formValuesPrefix, add it to the parameter-array
		$formValuesPrefix = $this->globals->getFormValuesPrefix();
		if (!empty($formValuesPrefix)) {
			$paramsArray = array($formValuesPrefix => $paramsArray);
		}

		// Create the link, using typolink function, use baseUrl if set, else use t3lib_div::getIndpEnv('TYPO3_SITE_URL')
		$url = $this->cObj->getTypoLink_URL($authCodePage, $paramsArray);
		$tmpArr = parse_url($url);
		if (empty($tmpArr['scheme'])) {
			$url = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . ltrim($url, '/');
		}

		$this->gp['authCodeUrl'] = $url;
	}

	/**
	 * Creates a tiny url if enabled in configuration and extension
	 * is available
	 */
	protected function generateTinyUrl() {


		if (!($this->settings['generateTinyUrl'] && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tinyurls'))) {
			return;
		}

		if (!isset($this->tinyUrlApi)) {
			$this->tinyUrlApi = GeneralUtility::makeInstance('Tx_Tinyurls_TinyUrl_Api');
		}

		$this->tinyUrlApi->setDeleteOnUse(1);
		$this->tinyUrlApi->setUrlKey($this->gp['generated_authCode']);
		$this->tinyUrlApi->setValidUntil($this->authCodeRepository->getValidUntil()->getTimestamp());

		$url = $this->gp['authCodeUrl'];
		$this->gp['authCodeUrl'] = $this->tinyUrlApi->getTinyUrl($url);
	}
}