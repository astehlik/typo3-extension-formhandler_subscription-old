<?php
/**
 * Created by JetBrains PhpStorm.
 * User: astehlik
 * Date: 14.11.12
 * Time: 11:22
 * To change this template use File | Settings | File Templates.
 */
class Tx_FormhandlerSubscription_Mvc_ActionController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * Contains environment information of the current formhandler request
	 *
	 * @var Tx_FormhandlerSubscription_Mvc_FormhandlerData
	 */
	protected $formhandlerData;

	/**
	 * Injects the current formhandler data
	 *
	 * @param Tx_FormhandlerSubscription_Mvc_FormhandlerData $formhandlerData
	 */
	public function injectFormhandlerData(Tx_FormhandlerSubscription_Mvc_FormhandlerData $formhandlerData) {
		$this->formhandlerData = $formhandlerData;
	}

	protected function resolveView() {
		return NULL;
	}

}
