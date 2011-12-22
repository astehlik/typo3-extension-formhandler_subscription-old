<?php
/**
 * Created by JetBrains PhpStorm.
 * User: astehlik
 * Date: 22.12.11
 * Time: 01:26
 * To change this template use File | Settings | File Templates.
 */
class Tx_FormhandlerSubscription_Finisher_Subscribe extends Tx_Formhandler_AbstractFinisher {

	/**
	 * Name of the table that contains the subscriber records
	 *
	 * @var string
	 */
	var $subscribersTable;

	/**
	 * The main method called by the controller
	 *
	 * @return array The probably modified GET/POST parameters
	 */
	public function process() {

		if (!$this->settings['table']) {
			throw new Exception('The subscribers table needs to be specified');
		} else {
			$this->subscribersTable = $this->settings['table'];
		}

		if (!$this->subscriberExists()) {
			$this->setTemplateSuffix('_NEW_SUBSCRIBER');
			$result = $this->runFinishers($this->settings['finishersNewSubscriber.']);
		} else {
			if (!$this->subscriberIsConfirmed()) {
				$this->setTemplateSuffix('_UNCONFIRMED_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['finishersExistingUnconfirmedSubscriber.']);
			} else {
				$this->setTemplateSuffix('_CONFIRMED_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['finishersExistingConfirmedSubscriber.']);
			}
		}

		return $result;
	}

	protected function setTemplateSuffix($templateSuffix) {
		if (intval($this->settings['setTemplateSuffix'])) {
			$this->globals->setTemplateSuffix($templateSuffix);
		}
	}

	protected function subscriberExists() {
		return $this->checkExistenceInDatabase('checkExistenceWhere');
	}

	protected function subscriberIsConfirmed() {
		return $this->checkExistenceInDatabase('checkConfirmedWhere');
	}

	protected function checkExistenceInDatabase($whereConfigKey) {
		$exists = FALSE;
		$where = $this->utilityFuncs->getSingle($this->settings, $whereConfigKey);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->subscribersTable, $where);
		var_dump($where);
		if($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
			$exists = TRUE;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $exists;
	}

	protected function runFinishers($finisherConfig) {

		ksort($finisherConfig);

		foreach ($finisherConfig as $idx => $tsConfig) {
			if ($idx !== 'disabled') {
				if (is_array($tsConfig) && isset($tsConfig['class']) && !empty($tsConfig['class'])) {
					if (intval($tsConfig['disable']) !== 1) {
						$className = $this->utilityFuncs->prepareClassName($tsConfig['class']);
						$finisher = $this->componentManager->getComponent($className);
						$tsConfig['config.'] = $this->addDefaultComponentConfig($tsConfig['config.']);
						$finisher->init($this->gp, $tsConfig['config.']);
						$finisher->validateConfig();

						//if the finisher returns HTML (e.g. Tx_Formhandler_Finisher_SubmittedOK)
						if ($tsConfig['config.']['returns']) {
							$this->globals->getSession()->set('finished', TRUE);
							return $finisher->process();
						} else {
							$this->gp = $finisher->process();
							$this->globals->setGP($this->gp);
						}
					}
				} else {
					$this->utilityFuncs->throwException('classesarray_error');
				}
			}
		}
	}

	protected function addDefaultComponentConfig($conf) {
		if (!$conf['langFiles']) {
			$conf['langFiles'] = $this->langFiles;
		}
		$conf['formValuesPrefix'] = $this->settings['formValuesPrefix'];
		$conf['templateSuffix'] = $this->settings['templateSuffix'];
		return $conf;
	}
}
