<?php
/**
 * Created by JetBrains PhpStorm.
 * User: astehlik
 * Date: 02.01.12
 * Time: 22:23
 * To change this template use File | Settings | File Templates.
 */
class Tx_FormhandlerSubscription_Finisher_RemoveAuthCodeRecord extends Tx_Formhandler_AbstractFinisher {

	/**
	 * @var Tx_FormhandlerSubscription_Utils_AuthCode
	 */
	protected $utils;

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @param array $gp
	 * @param array $settings
	 * @return void
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		$this->utils = Tx_FormhandlerSubscription_Utils_AuthCode::getInstance();
	}

	/**
	 * Checks, if a valid auth code was submitted and invalidates it
	 */
	public function process() {

		$authCode = $this->utils->getAuthCode();

		if (empty($authCode)) {
			$this->utilityFuncs->throwException('validateauthcode_insufficient_params');
		}

		$authCodeData = $this->utils->getAuthCodeDataFromDB($authCode);
		if (!isset($authCodeData)) {
			$this->utilityFuncs->throwException('validateauthcode_no_record_found');
		}

		$markAsDeleted = FALSE;
		if (intval($this->settings['markAsDeleted'])) {
			$markAsDeleted = TRUE;
		}
		$this->utils->removeAuthCodeRecordFromDB($authCodeData, $markAsDeleted);

		$this->utils->clearAuthCodeFromSession();
		$this->utils->clearAuthCodesByRowData($authCodeData);
		$this->gp = $this->utils->clearAuthCodeFromGP($this->gp);
	}

}
