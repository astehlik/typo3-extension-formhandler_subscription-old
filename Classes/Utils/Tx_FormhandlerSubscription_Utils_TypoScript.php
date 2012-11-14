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
 * A class providing helper functions for auth codes stored in the database
 */
class Tx_FormhandlerSubscription_Utils_TypoScript {

	public function gpArrayCount($content, $settings) {

		if (!isset($settings['gpName'])) {
			throw new InvalidArgumentException('Required parameter gpName is missing');
		}

		$gpParts = t3lib_div::trimExplode('|', $settings['gpName'], TRUE);

		if (!count($gpParts)) {
			throw new InvalidArgumentException('No valid variable name was set in gpName parameter');
		}

		$arrayCount = 0;

		$gpVars = t3lib_div::_GP($gpParts[0]);
		array_shift($gpParts);

		foreach($gpParts as $gpName) {

			if (is_array($gpVars)) {
				$gpVars = $gpVars[$gpName];
			} else {
				break;
			}
		}

		if (is_array($gpVars)) {
			$arrayCount = count($gpVars);
		}

		return $arrayCount;
	}
}
?>