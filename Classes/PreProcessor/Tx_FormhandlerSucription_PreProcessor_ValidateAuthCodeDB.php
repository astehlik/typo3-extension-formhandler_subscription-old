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
 *                                                                        */

/**
 * A pre processor validating an auth code generated by Finisher_GenerateAuthCode.
 *
 * @author	Reinhard Führicht <rf@typoheads.at>
 * @package	Tx_Formhandler
 * @subpackage	PreProcessor
 */
class Tx_FormhandlerSucription_PreProcessor_ValidateAuthCodeDB extends Tx_Formhandler_PreProcessor_ValidateAuthCode {

	/**
	 * The main method called by the controller
	 *
	 * @return array
	 */
	public function process() {

		try {

			$authCode = trim($this->gp['authCode']);

			if (!strlen($authCode)) {
				if (intval($this->settings['authCodeRequired'])) {
					$this->utilityFuncs->throwException('validateauthcode_insufficient_params');
				} else {
					return $this->gp;
				}
			}

			$authCode = $GLOBALS['TYPO3_DB']->fullQuoteStr($authCode, 'tx_formhandler_subscription_authcodes');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_formhandler_subscription_authcodes', 'auth_code=' . $authCode);
			if (!($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res))) {
				$this->utilityFuncs->throwException('validateauthcode_no_record_found');
			}

			$authCodeData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			if (intval($authCodeData['update_hidden_field'])) {
				$this->updateHiddenField($authCodeData);
			}

			$redirectPage = $this->utilityFuncs->getSingle($this->settings, 'redirectPage');
			if($redirectPage) {
				$this->utilityFuncs->doRedirect($redirectPage, $this->settings['correctRedirectUrl'], $this->settings['additionalParams.']);
			}
		} catch(Exception $e) {
			$redirectPage = $this->utilityFuncs->getSingle($this->settings, 'errorRedirectPage');
			if($redirectPage) {
				$this->utilityFuncs->doRedirect($redirectPage, $this->settings['correctRedirectUrl'], $this->settings['additionalParams.']);
			} else {
				throw new Exception($e->getMessage());
			}
		}

		return $this->gp;
	}

	protected function updateHiddenField($authCodeData) {

		$updateTable = $authCodeData['reference_table'];
		$uidField = $authCodeData['reference_table_uid_field'];
		$uid = $authCodeData['reference_table_uid'];
		$hiddenField = $authCodeData['reference_table_hidden_field'];

		$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($updateTable, $uidField . '=' . $uid, array($hiddenField => 0));
		if (!$res) {
			$this->utilityFuncs->throwException('validateauthcode_update_failed');
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
	}

}
?>