<?php
/**
 * Created by JetBrains PhpStorm.
 * User: astehlik
 * Date: 04.01.12
 * Time: 16:10
 * To change this template use File | Settings | File Templates.
 */
class Tx_FormhandlerSubscription_Finisher_ValidateAuthCodeUID  extends Tx_Formhandler_AbstractFinisher {

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
