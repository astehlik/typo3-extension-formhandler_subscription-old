<?php
namespace Tx\FormhandlerSubscription\Exceptions;

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
class MissingSettingException extends AbstractException {

	/**
	 * The name of the missing section.
	 *
	 * @var string
	 */
	protected $missingSetting;

	/**
	 * @param string $missingSetting
	 */
	public function __construct($missingSetting) {
		$this->missingSetting = $missingSetting;
		parent::__construct('A required setting is missing: ' . $missingSetting);
	}

	/**
	 * @return string
	 */
	public function getMissingSetting() {
		return $this->missingSetting;
	}
}
