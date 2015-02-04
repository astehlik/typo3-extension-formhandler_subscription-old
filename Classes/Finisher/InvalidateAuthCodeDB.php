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
 * Checks, if a valid auth code was submitted and invalidates it
 */
class InvalidateAuthCodeDB extends FormhandlerAbstractFinisher {

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
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		$this->utils = AuthCodeUtils::getInstance();
	}

	/**
	 * Checks, if a valid auth code was submitted and invalidates it
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

		$this->utils->clearAuthCodeFromSession();
		$this->utils->clearAuthCodesByRowData($authCodeData);
		$this->gp = $this->utils->clearAuthCodeFromGP($this->gp);

		return $this->gp;
	}
}