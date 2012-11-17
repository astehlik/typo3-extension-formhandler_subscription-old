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
 * This exception is used when a required setting is missing
 */
class Tx_FormhandlerSubscription_Exceptions_MissingSettingException extends Tx_FormhandlerSubscription_Exceptions_AbstractException {

	protected $missingSetting;

	public function __construct($missingSetting) {
		$this->missingSetting = $missingSetting;
		parent::__construct('A required setting is missing: ' . $missingSetting);
	}

	public function getMissingSetting() {
		return $this->missingSetting;
	}

}
