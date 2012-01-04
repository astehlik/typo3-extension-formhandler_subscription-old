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
	 * The database field that contains the uid of the
	 * newsletter subscriber records
	 *
	 * @var string
	 */
	var $uidField = 'uid';

	/**
	 * If this is true the template suffix will be set according
	 * to the current database query result. These suffixes will
	 * be used: _NEW_SUBSCRIBER, _UNCONFIRMED_SUBSCRIBER and
	 * _CONFIRMED_SUBSCRIBER
	 *
	 * @var bool
	 */
	var $setTemplateSuffix = TRUE;

	/**
	 * Name of the table that contains the subscriber records
	 *
	 * @var string
	 */
	var $subscribersTable;

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @param array $gp
	 * @param array $settings
	 * @return void
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		if (!$this->settings['subscribersTable']) {
			throw new Exception('The subscribers table needs to be specified');
		} else {
			$this->subscribersTable = $this->utilityFuncs->getSingle($this->settings, 'subscribersTable');
		}

		if (array_key_exists('setTemplateSuffix', $this->settings)) {
			$this->setTemplateSuffix = (boolean)$this->settings['setTemplateSuffix'];
		}

		if ($this->settings['uidField']) {
			$this->uidField = $this->settings['uidField'];
		}
	}

	/**
	 * The main method called by the controller
	 *
	 * @return string|array The output that should be displayed to the user (if any) or the GET/POST data array
	 */
	public function process() {

		$existingSubscriberResult = $this->getRecordsFromDatabase('checkExistenceSelect');

		if (!$this->recordExists($existingSubscriberResult)) {
			$this->setTemplateSuffix('_NEW_SUBSCRIBER');
			$result = $this->runFinishers($this->settings['finishersNewSubscriber.']);
		} else {

			$subscriberData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($existingSubscriberResult);
			$this->gp['saveDB'][] = array(
				'table' => $this->subscribersTable,
				'uidField' => $this->uidField,
				'uid' => $subscriberData[$this->uidField],
			);

				// add subscriber data to the gp array
			$this->gp['subscriberData'] = $subscriberData;
			$this->globals->setGP($this->gp);

			$confirmedSubscriberResult = $this->getRecordsFromDatabase('checkConfirmedSelect');
			if (!$this->recordExists($confirmedSubscriberResult)) {
				$this->setTemplateSuffix('_UNCONFIRMED_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['finishersExistingUnconfirmedSubscriber.']);
			} else {
				$this->setTemplateSuffix('_CONFIRMED_SUBSCRIBER');
				$result = $this->runFinishers($this->settings['finishersExistingConfirmedSubscriber.']);
			}
		}

		if (strlen($result)) {
			return $result;
		} else {
			return $this->gp;
		}
	}


	/**
	 * Adds some default configuration to a compontent
	 *
	 * @see Tx_Formhandler_Controller_Form::addDefaultComponentConfig()
	 * @param array $conf
	 * @return array
	 */
	protected function addDefaultComponentConfig($conf) {
		if (!$conf['langFiles']) {
			$conf['langFiles'] = $this->langFiles;
		}

			//@TODO: Check if this is still needed since these values are read from $this->globals most of the time
		$conf['formValuesPrefix'] = $this->settings['formValuesPrefix'];
		$conf['templateSuffix'] = $this->settings['templateSuffix'];
		return $conf;
	}

	/**
	 * Uses the select TypoScript configuration to get records
	 * out of the database. If a record is found this method
	 * will return true
	 *
	 * @param string $selectConfigKey
	 * @return pointer
	 */
	protected function getRecordsFromDatabase($selectConfigKey) {

		$selectConfig = $this->settings[$selectConfigKey . '.'];

			// Backup showHiddenRecordsSetting
		$currentShowHiddenSetting = $GLOBALS['TSFE']->showHiddenRecords;

		if (intval($selectConfig['showHidden'])) {
			$GLOBALS['TSFE']->showHiddenRecords = 1;
		} else {
			$GLOBALS['TSFE']->showHiddenRecords = 0;
		}

		$query = $this->globals->getCObj()->getQuery($this->subscribersTable, $selectConfig);

			// Restore showHiddenRecords setting
		$GLOBALS['TSFE']->showHiddenRecords = $currentShowHiddenSetting;

		$this->utilityFuncs->debugMessage('sql_request', array($query));
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		if ($GLOBALS['TYPO3_DB']->sql_error()) {
			$this->utilityFuncs->debugMessage('error', array($GLOBALS['TYPO3_DB']->sql_error()), 3);
		}

		return $res;
	}

	/**
	 * Runs the finishers configured in the given configuration
	 * array
	 *
	 * @see Tx_Formhandler_Controller_Form::processFinished()
	 * @param array $finisherConfig
	 * @return null
	 */
	protected function runFinishers($finisherConfig) {

		ksort($finisherConfig);
		$returnValue = NULL;

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
							$returnValue =  $finisher->process();
							break;
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

		return $returnValue;
	}

	/**
	 * Sets the template suffix to the given string
	 * if this was not disabledin the settings
	 *
	 * @param $templateSuffix
	 */
	protected function setTemplateSuffix($templateSuffix) {
		if ($this->setTemplateSuffix) {
			$this->globals->setTemplateSuffix($templateSuffix);
		}
	}

	/**
	 * Returns true if the subscriber already exists in the database
	 *
	 * @param pointer $res
	 * @return bool
	 */
	protected function recordExists($res) {
		return ($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0);
	}
}
