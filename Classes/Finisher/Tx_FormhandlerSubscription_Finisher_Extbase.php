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
 * This finisher calls an extbase plugin
 */
class Tx_FormhandlerSubscription_Finisher_Extbase extends Tx_Formhandler_AbstractFinisher {

	/**
	 * Calls the exbase bootstrap
	 */
	public function process() {

		/**
		 * @var Tx_FormhanderSubscription_Mvc_FormhandlerData $formhandlerData
		 */
		$formhandlerData = t3lib_div::makeInstance('Tx_FormhandlerSubscription_Mvc_FormhandlerData');
		$formhandlerData->initialize($this->gp);

		/**
		 * @var Tx_Extbase_Core_Bootstrap $extbaseBootstrap
		 */
		$extbaseBootstrap = t3lib_div::makeInstance('Tx_Extbase_Core_Bootstrap');
		$extbaseBootstrap->run('', $this->settings);

		return $formhandlerData->getGpUpdated();
	}
}

?>