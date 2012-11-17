<?php

/*                                                                        *
 * This script belongs to the TYPO3 extension "formhandler_subscription". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

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
class Tx_FormhandlerSubscription_Finisher_GenerateAuthCodeDB extends Tx_Formhandler_Finisher_GenerateAuthCode {

	/**
	 * The action that will be executed when the user provides
	 * the correct auth code
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * The field that marks the referenced record as hidden
	 *
	 * @var string
	 */
	protected $hiddenField = '';

	/**
	 * The table that contains the records that are referenced
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Tiny URL API
	 *
	 * @var Tx_Tinyurls_TinyUrl_Api
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
	 * @var Tx_FormhandlerSubscription_Utils_AuthCode
	 */
	protected $utils;

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @param array $gp
	 * @param array $settings
	 * @throws Tx_FormhandlerSubscription_Exceptions_MissingSettingException If not all requires settings have heen set
	 * @return void
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		if (!isset($this->utils)) {
			$this->utils = Tx_FormhandlerSubscription_Utils_AuthCode::getInstance();
		}

		if (!$this->settings['table']) {
			throw new Tx_FormhandlerSubscription_Exceptions_MissingSettingException('table');
		} else {
			$this->table = $this->utilityFuncs->getSingle($this->settings, 'table');
		}

		if ($this->settings['uidField']) {
			$this->uidField = $this->settings['uidField'];
		}

		if (!empty($this->settings['action'])) {
			$this->action = $this->settings['action'];
		} else {
			$this->action = Tx_FormhandlerSubscription_Utils_AuthCode::ACTION_ENABLE_RECORD;
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

		parent::process();

		$this->generateTinyUrl();

		$this->globals->setFormValuesPrefix($currentFormValuesPrefix);

		return $this->gp;
	}

	/**
	 * Injector for auth code utilities
	 *
	 * @param $authCodeUtilities Tx_FormhandlerSubscription_Utils_AuthCode
	 */
	public function setAuthCodeUtils($authCodeUtilities) {
		$this->utils = $authCodeUtilities;
	}

	/**
	 * Injector for tiny URL API
	 *
	 * @param $tinyUrlApi Tx_Tinyurls_TinyUrl_Api
	 */
	public function setTinyUrlApi($tinyUrlApi) {
		$this->tinyUrlApi = $tinyUrlApi;
	}

	/**
	 * Creates a new entry in the tx_formhandler_subscription_authcodes table
	 * and return a hash value to send by email as an auth code.
	 *
	 * @param array $row The submitted form data
	 * @return string The auth code
	 */
	protected function generateAuthCode($row) {

		return $this->utils->generateAuthCode(
			$row,
			$this->action,
			$this->table,
			$this->uidField,
			$this->hiddenField
		);
	}

	/**
	 * Creates a tiny url if enabled in configuration and extension
	 * is available
	 */
	protected function generateTinyUrl() {


		if (!($this->settings['generateTinyUrl'] && t3lib_extMgm::isLoaded('tinyurls'))) {
			return;
		}

		if (!isset($this->tinyUrlApi)) {
			$this->tinyUrlApi = t3lib_div::makeInstance('Tx_Tinyurls_TinyUrl_Api');
		}

		$this->tinyUrlApi->setDeleteOnUse(1);
		$this->tinyUrlApi->setUrlKey($this->gp['generated_authCode']);
		$this->tinyUrlApi->setValidUntil($this->utils->getAuthCodeValidityTimestamp());

		$url = $this->gp['authCodeUrl'];
		$this->gp['authCodeUrl'] = $this->tinyUrlApi->getTinyUrl($url);

	}
}
?>