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
 * Checks if the submitted uid matches the one that was stored with the submitted
 * auth code.
 */
class Tx_FormhandlerSubscription_Finisher_ValidateAuthCodeUID  extends Tx_Formhandler_AbstractFinisher {

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
	 * @return void
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		$this->utils = Tx_FormhandlerSubscription_Utils_AuthCode::getInstance();
	}

	/**
	 * Checks, if a valid auth code was submitted and if the submitted uid
	 * matches the one that was used for generating the auth code
	 *
	 * @return array the GET/POST data array
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

		$uidField = $authCodeData['reference_table_uid_field'];
		$uidGP = $this->gp[$uidField];

		$uidAuthCode = $authCodeData['reference_table_uid'];

		if ($uidGP !== $uidAuthCode) {
			throw new Exception('The submitted uid does not match the one the auth code was created for.');
		}

		return $this->gp;
	}
}
?>