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
 * This controller can be used for submitting request via ajax.
 */
class Tx_FormhandlerSubscription_Controller_AjaxSubmitController extends Tx_Formhandler_Controller_Form {

	/**
	 * We are always submitting the last step of a form where
	 * the values are validated and the finishers will be
	 * executed if the submitted data is valid.
	 */
	protected function getStepInformation() {
		parent::getStepInformation();
		$this->lastStep = $this->totalSteps;
		$this->currentStep = $this->totalSteps + 1;
	}

	/**
	 * We always process the form as if it was submitted
	 *
	 * @return bool
	 */
	protected function isFormSubmitted() {
		return TRUE;
	}

	/**
	 * If there are errors in the submitted values we print
	 * them in a JSON object
	 *
	 * @return string
	 */
	protected function processNotValid() {
		parent::processNotValid();
		return json_encode(array(
			'status' => 'error',
			'errors' =>$this->errors
		));
	}

	/**
	 * If all values are OK we output a success state in a
	 * JSON object after the finishers have been executed
	 *
	 * @return string
	 */
	protected function processFinished() {
		parent::processFinished();
		return json_encode(array(
			'status' => 'success',
		));
	}
}
?>