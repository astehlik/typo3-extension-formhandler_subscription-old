<?php
namespace Tx\FormhandlerSubscription\Utils;

/*                                                                        *
 * This script belongs to the TYPO3 extension "formhandler_subscription". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Tx\Authcode\Domain\Enumeration\AuthCodeType;
use Tx\FormhandlerSubscription\Exceptions\InvalidSettingException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class providing helper functions for auth codes stored in the database
 */
class AuthCodeUtils {

	/**
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeAction enumeration instead.
	 * @see \Tx\Authcode\Domain\Enumeration\AuthCodeAction::RECORD_ENABLE
	 * @const
	 */
	const ACTION_ENABLE_RECORD = 'enableRecord';

	/**
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeAction enumeration instead.
	 * @see \Tx\Authcode\Domain\Enumeration\AuthCodeAction::ACCESS_PAGE
	 * @const
	 */
	const ACTION_ACCESS_FORM = 'accessPage';

	/**
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeType enumeration instead.
	 * @see \Tx\Authcode\Domain\Enumeration\AuthCodeType::RECORD
	 * @const
	 */
	const TYPE_RECORD = 'record';

	/**
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeType enumeration instead.
	 * @see \Tx\Authcode\Domain\Enumeration\AuthCodeType::INDEPENDENT
	 * @const
	 */
	const TYPE_INDEPENDENT = 'independent';

	/**
	 * @var \Tx\Authcode\Domain\Repository\AuthCodeRecordRepository
	 */
	protected $authCodeRecordRepository;

	/**
	 * @var \Tx\Authcode\Domain\Repository\AuthCodeRepository
	 */
	protected $authCodeRepository;

	/**
	 * Globals of the formhandler extension
	 *
	 * @var \Tx_Formhandler_Globals
	 */
	protected $globals;

	/**
	 * Formhandler utility functions
	 *
	 * @var \Tx_Formhandler_UtilityFuncs
	 */
	protected $formhandlerUtils;

	/**
	 * Stores the current instance of the utils class
	 *
	 * @var AuthCodeUtils
	 */
	static protected $instance = NULL;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
	 */
	protected $objectManager;

	/**
	 * TYPO3 Frontend user
	 *
	 * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
	 */
	var $tsfeUser = NULL;

	/**
	 * Singleton for getting the current instance of the utils class
	 *
	 * @static
	 * @return AuthCodeUtils
	 */
	static public function getInstance() {

		if (!ExtensionManagementUtility::isLoaded('authcode')) {
			throw new \RuntimeException('The authcode Extension is required to use the ValidateAuthCodeDB pre-processor.');
		}

		if (self::$instance === NULL) {
			self::$instance = new AuthCodeUtils();
		}
		return self::$instance;
	}

	/**
	 * Initializes the formhandler globals and the expiry timestamp
	 */
	public function __construct() {

		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->authCodeRepository = $this->objectManager->get('Tx\\Authcode\\Domain\\Repository\\AuthCodeRepository');
		$this->authCodeRecordRepository = $this->objectManager->get('Tx\\Authcode\\Domain\\Repository\\AuthCodeRecordRepository');

		$this->formhandlerUtils = \Tx_Formhandler_UtilityFuncs::getInstance();
		$this->globals = \Tx_Formhandler_Globals::getInstance();

		$this->tsfeUser = $GLOBALS['TSFE']->fe_user;

		$settings = $this->globals->getSettings();
		if (array_key_exists('authCodeDBExpiryTime', $settings)) {
			$this->authCodeRepository->setAuthCodeExpiryTime($settings['authCodeDBExpiryTime']);
		}

		if (array_key_exists('authCodeDBAutoDeleteExpired', $settings)) {
			$this->authCodeRepository->setAutoDeleteExpiredAuthCodes($settings['authCodeDBAutoDeleteExpired']);
		}
	}

	/**
	 * Removes all auth codes that reference the given record
	 *
	 * @param $table string
	 * @param $uidField string
	 * @param $uid string
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRepository::clearAssociatedAuthCodes()
	 */
	public function clearAuthCodes($table, $uidField, $uid) {
		$authCode = $this->createAuthCode();
		$authCode->setType(AuthCodeType::RECORD);
		$authCode->setReferenceTable($table);
		$authCode->setReferenceTableUidField($uidField);
		$authCode->setReferenceTableUid($uid);
		$this->authCodeRepository->clearAssociatedAuthCodes($authCode);
	}

	/**
	 * Clears all auth codes that match the given identifier for the given context
	 *
	 * @param string $identifier
	 * @param string $context
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRepository::clearAssociatedAuthCodes()
	 */
	public function clearTableIndependentAuthCodes($identifier, $context) {
		$authCode = $this->createAuthCode();
		$authCode->setType(AuthCodeType::INDEPENDENT);
		$authCode->setIdentifier($identifier);
		$authCode->setIdentifierContext($context);
		$this->authCodeRepository->clearAssociatedAuthCodes($authCode);
	}

	/**
	 * Removes all auth codes that reference the given record
	 *
	 * @param array $authCodeRow
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRepository::clearAssociatedAuthCodes()
	 */
	public function clearAuthCodesByRowData($authCodeRow) {
		/** @noinspection PhpDeprecationInspection The current method is also deprecated. */
		$this->clearAuthCodes(
			$authCodeRow['reference_table'],
			$authCodeRow['reference_table_uid_field'],
			$authCodeRow['reference_table_uid']
		);
	}

	/**
	 * Checks if the given action is valid
	 *
	 * @param string $action the action that should be checked
	 * @throws InvalidSettingException if action is invalid
	 */
	public function checkAuthCodeAction($action) {

		try {
			new \Tx\Authcode\Domain\Enumeration\AuthCodeAction($action);
		} catch (\TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException $exception) {
			throw new InvalidSettingException('action');
		}
	}

	/**
	 * Generates a new auth code based on the given row data and clears
	 * all other auth codes that reference the same row
	 *
	 * @param array $row
	 * @param string $action
	 * @param string $table
	 * @param string $uidField
	 * @param string $hiddenField
	 * @return string
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRecordRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRepository::generateRecordAuthCode()
	 *
	 */
	public function generateAuthCode($row, $action, $table, $uidField, $hiddenField) {

		$authCode = $this->createAuthCode(AuthCodeType::RECORD);

		$authCode->setReferenceTableHiddenField($hiddenField);
		$authCode->setReferenceTableUidField($uidField);
		$authCode->setAdditionalData($row);

		$this->authCodeRepository->generateRecordAuthCode($authCode, $action, $table, $row[$uidField]);

		return $authCode->getAuthCode();
	}

	/**
	 * Generates an auth code for accessing a form that is independent from
	 * any table records but only needs an identifier and a context name for that
	 * identifier.
	 *
	 * The identifier should be unique in the given context.
	 *
	 * @param $identifier
	 * @param $context
	 * @param null $additionalData
	 * @return string
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRecordRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRepository::generateIndependentAuthCode()
	 */
	public function generateTableIndependentAuthCode($identifier, $context, $additionalData = NULL) {

		$authCode = $this->createAuthCode();

		if (isset($additionalData)) {
			$authCode->setAdditionalData($additionalData);
		}

		$this->authCodeRepository->generateIndependentAuthCode($authCode, $identifier, $context);

		return $authCode->getAuthCode();
	}

	/**
	 * Tries to read the auth code from the GET/POST data array or
	 * from the session.
	 *
	 * @return string
	 */
	public function getAuthCode() {

		$authCode = '';

		// We need to use the global GET/POST variables because if the form is not submitted $this->gp will be empty
		// because \Tx_Formhandler_Controller_Form::reset() is called.
		$formValuesPrefix = $this->globals->getFormValuesPrefix();
		if (empty($formValuesPrefix)) {
			$authCode = GeneralUtility::_GP('authCode');
		} else {
			$gpArray = GeneralUtility::_GP($formValuesPrefix);
			if (is_array($gpArray) && array_key_exists('authCode', $gpArray)) {
				$authCode = $gpArray['authCode'];
			}
		}

		if (empty($authCode)) {
			$authCode = $this->getAuthCodeFromSession();
		}

		return $authCode;
	}

	/**
	 * Retrieves the data of the given auth code from the database.
	 * Before executing the query to get the auth code data expired auth codes are deleted from the database if this is not disabled in the settings.
	 * If a valid auth code is found the code is refreshed to prevent expiration whil the user accesses a protected page.
	 *
	 * @param string $authCode the submitted auth code
	 * @return \Tx\Authcode\Domain\Model\AuthCode|NULL NULL if no data was found, otherwise an associative array of the auth code data
	 */
	public function getAuthCodeDataFromDB($authCode) {

		$this->formhandlerUtils->debugMessage('Trying to read auth code data from database');

		$authCode = $this->authCodeRepository->findOneByAuthCode($authCode);

		if (isset($authCode)) {
			$this->authCodeRepository->refreshAuthCode($authCode);
		}

		return $authCode;
	}

	/**
	 * Reads the data of the record that is referenced by the auth code
	 * from the database
	 *
	 * @param \Tx\Authcode\Domain\Model\AuthCode $authCode
	 * @return array|NULL NULL if no data was found, otherwise an associative array of the record data
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRecordRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRecordRepository::getAuthCodeRecordFromDB()
	 */
	public function getAuthCodeRecordFromDB(\Tx\Authcode\Domain\Model\AuthCode $authCode) {
		return $this->authCodeRecordRepository->getAuthCodeRecordFromDB($authCode);
	}

	/**
	 * Stores the given auth code in the session
	 *
	 * @param string|\Tx\Authcode\Domain\Model\AuthCode $authCode
	 */
	public function storeAuthCodeInSession($authCode) {

		if ($authCode instanceof \Tx\Authcode\Domain\Model\AuthCode) {
			$authCode = $authCode->getAuthCode();
		}

		$sesAuthCode = $this->tsfeUser->getKey('ses', 'formhandler_auth_code');

		// Performance: Only update the auth code in the session if it is
		// not already stored
		if ($sesAuthCode !== $authCode) {
			$this->tsfeUser->setKey('ses', 'formhandler_auth_code', $authCode);
			$this->tsfeUser->storeSessionData();
		}
	}

	/**
	 * Deletes the records that is referenced by the auth code from
	 * the database
	 *
	 * @param array|\Tx\Authcode\Domain\Model\AuthCode $authCodeData
	 * @param bool $markAsDeleted
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRecordRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRecordRepository::removeAssociatedRecord()
	 */
	public function removeAuthCodeRecordFromDB($authCodeData, $markAsDeleted = FALSE) {
		if (is_array($authCodeData)) {
			$authCode = $this->authCodeRepository->findByUid($authCodeData['uid']);
		} elseif ($authCodeData instanceof \Tx\Authcode\Domain\Model\AuthCode) {
			$authCode = $authCodeData;
		} else {
			throw new \InvalidArgumentException('$authCodeData must either be an array or an instance of \\Tx\\Authcode\\Domain\\Model\\AuthCode');
		}
		$this->authCodeRecordRepository->removeAssociatedRecord($authCode, !$markAsDeleted);
	}

	/**
	 * Tries to read the auth code from the session
	 *
	 * @return string
	 */
	public function getAuthCodeFromSession() {
		return $this->tsfeUser->getKey('ses', 'formhandler_auth_code');
	}

	/**
	 * Removes the auth code from the session
	 */
	public function clearAuthCodeFromSession() {
		$this->tsfeUser->setKey('ses', 'formhandler_auth_code', NULL);
		$this->tsfeUser->storeSessionData();
	}

	/**
	 * Clears the auth code from the given $gp array and
	 * the global $gp array
	 *
	 * @param array $gp
	 * @return array
	 */
	public function clearAuthCodeFromGP($gp) {

		$globalGP = $this->globals->getGP();
		unset($globalGP['authCode']);
		unset($globalGP['authCodeData']);
		unset($globalGP['authCodeRecord']);
		$this->globals->setGP($globalGP);

		unset($gp['authCode']);
		unset($gp['authCodeData']);
		unset($gp['authCodeRecord']);
		return $gp;
	}

	/**
	 * Removes all auth codes from the database where the tstamp is older than the allowed timestamp defined in expiredAuthCodeTimestamp.
	 *
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRepository::deleteExpiredAuthCodesFromDatabase()
	 */
	public function deleteExpiredAuthCodesFromDatabase() {
		$this->authCodeRepository->deleteExpiredAuthCodesFromDatabase();
	}

	/**
	 * Returns the timestamp when the currently generated auth codes will expire.
	 *
	 * @return int validity timestamp
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRepository::getValidUntil()
	 */
	public function getAuthCodeValidityTimestamp() {
		return $this->authCodeRepository->getValidUntil()->getTimestamp();
	}

	/**
	 * Sets a new auth code expiry time.
	 *
	 * @param string $authCodeExpiryTime Time that will be parsed with strtotime
	 * @throws \Exception if string can not be parsed
	 * @deprecated Since 0.7.0, will be removed in version 1.0.0, use AuthCodeRepository instead.
	 * @see \Tx\Authcode\Domain\Repository\AuthCodeRepository::setAuthCodeExpiryTime()
	 */
	public function setAuthCodeExpiryTime($authCodeExpiryTime) {
		$this->authCodeRepository->setAuthCodeExpiryTime($authCodeExpiryTime);
	}

	/**
	 * @return \Tx\Authcode\Domain\Model\AuthCode
	 */
	protected function createAuthCode() {
		return $this->objectManager->get('Tx\\Authcode\\Domain\\Model\\AuthCode');
	}
}