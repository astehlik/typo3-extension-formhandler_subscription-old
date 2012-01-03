<?php
/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *
 * $Id: Tx_Formhandler_UtilityFuncs.php 53508 2011-10-28 10:10:07Z reinhardfuehricht $
 *                                                                        */

/**
 * A class providing helper functions for Formhandler
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	Utils
 */
class Tx_FormhandlerSubscription_Utils_AuthCode {

	const ACTION_ENABLE_RECORD = 'enableRecord';
	const ACTION_ACCESS_FORM = 'accessForm';

	/**
	 * Globals of the formhandler extension
	 *
	 * @var Tx_Formhandler_Globals
	 */
	protected $globals;

	/**
	 * Stores the current instance of the utils class
	 *
	 * @var Tx_FormhandlerSubscription_Utils_AuthCode
	 */
	static protected  $instance = NULL;

	/**
	 * Singleton for getting the current instance of the utils class
	 *
	 * @static
	 * @return Tx_FormhandlerSubscription_Utils_AuthCode
	 */
	static public function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new Tx_FormhandlerSubscription_Utils_AuthCode();
		}
		return self::$instance;
	}

	/**
	 * Initializes the formhandler globals
	 */
	public function __construct() {
		$this->globals = Tx_Formhandler_Globals::getInstance();
	}

	/**
	 * Removes all auth codes that reference the given record
	 *
	 * @param $table string
	 * @param $uidField string
	 * @param $uid string
	 */
	public function clearAuthCodes($table, $uidField, $uid) {

			// remove old entries for the same record
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'tx_formhandler_subscription_authcodes',
			'reference_table=' .  $GLOBALS['TYPO3_DB']->fullQuoteStr($table, 'tx_formhandler_subscription_authcodes') .
			'AND reference_table_uid_field=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($uidField, 'tx_formhandler_subscription_authcodes') .
			'AND reference_table_uid=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($uid, 'tx_formhandler_subscription_authcodes')
		);
	}

	public function clearAuthCodesByRowData($authCodeRow) {
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
	 * @throws Exception if action is invalid
	 */
	public function checkAuthCodeAction($action) {
		switch ($action) {
			case Tx_FormhandlerSubscription_Utils_AuthCode::ACTION_ENABLE_RECORD:
			case Tx_FormhandlerSubscription_Utils_AuthCode::ACTION_ACCESS_FORM:
				break;
			default:
				throw new Exception("Invalid auth code action: " . $action);
				break;
		}
	}

	public function getAuthCode() {

		$authCode = '';

			// We need to use the global GET/POST variables because if
			// the form is not submitted $this->gp will be empty
			// because Tx_Formhandler_Controller_Form::reset
			// is called
		$formValuesPrefix = $this->globals->getFormValuesPrefix();
		if (empty($formValuesPrefix)) {
			$authCode = t3lib_div::_GP('authCode');
		} else {
			$gpArray = t3lib_div::_GP($formValuesPrefix);
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
	 * Retrieves the data of the given auth code from the database
	 *
	 * @param $authCode the submitted auth code
	 * @return NULL|array NULL if no data was found, otherwise an associative array of the auth code data
	 */
	public function getAuthCodeDataFromDB($authCode) {

		$authCodeData = NULL;

		$authCode = $GLOBALS['TYPO3_DB']->fullQuoteStr($authCode, 'tx_formhandler_subscription_authcodes');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_formhandler_subscription_authcodes', 'auth_code=' . $authCode);

		if ($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
			$authCodeData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $authCodeData;
	}

	public function getAuthCodeRecordFromDB($authCodeData) {

		$authCodeRecord = NULL;

		$table = $authCodeData['reference_table'];
		$uidField = $authCodeData['reference_table_uid_field'];
		$uid = $GLOBALS['TYPO3_DB']->fullQuoteStr($authCodeData['reference_table_uid'], $table);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $uidField . '=' . $uid);
		if ($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
			$authCodeRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $authCodeRecord;
	}

	public function storeAuthCodeInSession($authCode) {

		$sesAuthCode = $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler_auth_code');

			// Performance: Only update the auth code in the session if it is
			// not already stored
		if ($sesAuthCode !== $authCode) {
			$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler_auth_code', $authCode);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
		}
	}

	public function removeAuthCodeRecordFromDB($authCodeData, $markAsDeleted = FALSE) {

		$table = $authCodeData['reference_table'];
		$uidField = $authCodeData['reference_table_uid_field'];
		$uid = $GLOBALS['TYPO3_DB']->fullQuoteStr($authCodeData['reference_table_uid'], $table);

		t3lib_div::loadTCA($table);

		if ($markAsDeleted && array_key_exists('delete', $GLOBALS['TCA'][$table]['ctrl'])) {
			$deleteColumn = $GLOBALS['TCA'][$table]['ctrl']['delete'];
			$fieldValues[$deleteColumn] = 1;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $uidField . '=' . $uid, $fieldValues);
		} else {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $uidField . '=' . $uid);
		}
	}

	public function getAuthCodeFromSession() {
		return $GLOBALS['TSFE']->fe_user->getKey('ses', 'formhandler_auth_code');
	}

	public function clearAuthCodeFromSession() {
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'formhandler_auth_code', NULL);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
	}

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
}

?>
