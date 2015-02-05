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

use Tx\FormhandlerSubscription\Utils\AuthCodeUtils;
use Tx_Formhandler_AbstractFinisher as FormhandlerAbstractFinisher;

/**
 * Checks if the submitted uid matches the one that was stored with the submitted
 * auth code.
 */
class ValidateAuthCodeUID extends FormhandlerAbstractFinisher {

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
	 * @return void
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		$this->utils = AuthCodeUtils::getInstance();
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

		$authCodeRecord = $this->utils->getAuthCodeDataFromDB($authCode);
		if (!isset($authCodeRecord)) {
			$this->utilityFuncs->throwException('validateauthcode_no_record_found');
		}

		$uidGP = $this->utilityFuncs->getSingle($this->settings, 'compareUid');
		if (!$uidGP) {
			$uidField = $authCodeRecord->getReferenceTableUidField();
			$uidGP = $this->gp[$uidField];
		}

		$uidGP = intval($uidGP);
		$uidAuthCode = $authCodeRecord->getReferenceTableUid();

		if ($uidGP !== $uidAuthCode) {
			$this->utilityFuncs->throwException('The submitted uid ' . $uidGP . ' does not match the one the auth code was created for: ' . $uidAuthCode);
		}

		return $this->gp;
	}
}