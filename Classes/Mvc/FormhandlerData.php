<?php
/**
 * Created by JetBrains PhpStorm.
 * User: astehlik
 * Date: 14.11.12
 * Time: 12:01
 * To change this template use File | Settings | File Templates.
 */
class Tx_FormhandlerSubscription_Mvc_FormhandlerData implements t3lib_Singleton {

	protected $gpOriginal;

	protected $gpUpdated;

	public function initialize($gp) {
		$this->gpOriginal = $gp;
		$this->gpUpdated = $gp;
	}

	public function getGpOriginal() {
		return $this->gpOriginal;
	}

	public function getGpUpdated() {
		return $this->gpUpdated;
	}

	public function getGpValue($name) {
		return $this->gpUpdated[$name];
	}

	public function getGpValueOriginal($name) {
		return $this->gpOriginal[$name];
	}

	public function removeGpValue($name) {
		unset($this->gpUpdated);
	}

	public function setGpUpdated($gpUpdated) {
		$this->gpUpdated = $gpUpdated;
	}

	public function setGpValue($name, $value) {
		$this->gpUpdated[$name] = $value;
	}
}
