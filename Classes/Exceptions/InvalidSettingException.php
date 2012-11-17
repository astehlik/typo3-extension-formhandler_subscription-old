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
 * This exception is used when a setting has an invalid value
 */
class Tx_FormhandlerSubscription_Exceptions_InvalidSettingException extends Tx_FormhandlerSubscription_Exceptions_AbstractException {

	/**
	 * The setting that was invalid
	 *
	 * @var string
	 */
	protected $invalidSetting;

	/**
	 * Creates a new exception
	 *
	 * @param string $invalidSetting The name of the invalid setting
	 * @param string $details Optional additional details of the exception
	 */
	public function __construct($invalidSetting, $details = '') {
		$this->invalidSetting = $invalidSetting;
		$message = 'This setting has an invalid value: ' . $invalidSetting;
		$message .= strlen($details) ? '. ' . $details : '';
		parent::__construct($message);
	}

	/**
	 * Returns the name of the invalid setting
	 *
	 * @return string
	 */
	public function getInvalidSetting() {
		return $this->invalidSetting;
	}

}

?>