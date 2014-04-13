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
 * This finisher checks if a subscriber exists and calls different sub-finishers
 *
 * The finisher handles three cases: the subscriber does not exist, the subscriber exists
 * but is not confirmed or the subscriber exists and is confirmed.
 *
 * Depending on the result different sub-finishers are called. These can be configured
 * like normal finishers in the config keys finishersNewSubscriber,
 * finishersExistingUnconfirmedSubscriber and finishersExistingConfirmedSubscriber.
 *
 * If a subscriber exists the its data will be loaded to the GP array and is accessible
 * with the 'subscriberData' key. Additionally, the record data is stored in the 'saveDB'
 * key to simulate the behaviour of Tx_Formhandler_Finisher_DB that lets the
 * Tx_Formhandler_Finisher_GenerateAuthCode do it's work
 *
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

		if (array_key_exists('setTemplateSuffix', $this->settings) && (intval($this->settings['setTemplateSuffix']) == 0)) {
			$this->setTemplateSuffix = FALSE;
		}

		if ($this->settings['uidField']) {
			$this->uidField = $this->settings['uidField'];
		}
	}

	/**
	 * Checks, if the subscriber exists and calls the sub-finishers accordingly
	 *
	 * @return string|array The output that should be displayed to the user (if any) or the GET/POST data array
	 */
	public function process() {

		$existingSubscriberResult = $this->getRecordsFromDatabase('checkExistenceSelect');

		if (!$this->recordExists($existingSubscriberResult)) {
			$this->setTemplateSuffix('_NEW_SUBSCRIBER');
			$result = $this->runFinishers($this->settings['finishersNewSubscriber.']);
		} else {

			$subscriberData = $this->getDatabaseConnection()->sql_fetch_assoc($existingSubscriberResult);

				// This is needed for generating an auth code for the
				// subscriber record, normally these variables are set
				// by Tx_Formhandler_Finisher_DB
			$this->gp['saveDB'][] = array(
				'table' => $this->subscribersTable,
				'uidField' => $this->uidField,
				'uid' => $subscriberData[$this->uidField],
			);

				// make subscriber data available in the the gp array
			$this->gp['subscriberData'] = $subscriberData;

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
		$conf['langFiles'] = $this->settings['langFiles'];
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
	 * @return boolean|\mysqli_result|object MySQLi result object / DBAL object
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

		/**
		 * @var tslib_cObj $cObj
		 */
		$cObj = $this->globals->getCObj();
		$query = $cObj->getQuery($this->subscribersTable, $selectConfig);

			// Restore showHiddenRecords setting
		$GLOBALS['TSFE']->showHiddenRecords = $currentShowHiddenSetting;

		$this->utilityFuncs->debugMessage('sql_request', array($query));
		$res = $this->getDatabaseConnection()->sql_query($query);
		if ($this->getDatabaseConnection()->sql_error()) {
			$this->utilityFuncs->debugMessage('error', array($this->getDatabaseConnection()->sql_error()), 3);
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

		$returnValue = NULL;
		ksort($finisherConfig);

		foreach ($finisherConfig as $idx => $tsConfig) {
			if ($idx !== 'disabled') {
				$className = $this->utilityFuncs->getPreparedClassName($tsConfig);
				if (is_array($tsConfig) && strlen($className) > 0) {
					if (intval($this->utilityFuncs->getSingle($tsConfig, 'disable')) !== 1) {

						/** @var Tx_Formhandler_AbstractComponent $finisher */
						/** @noinspection PhpVoidFunctionResultUsedInspection */
						$finisher = $this->componentManager->getComponent($className);
						$tsConfig['config.'] = $this->addDefaultComponentConfig($tsConfig['config.']);
						$finisher->init($this->gp, $tsConfig['config.']);
						$finisher->validateConfig();

							//if the finisher returns HTML (e.g. Tx_Formhandler_Finisher_SubmittedOK)
						if (intval($this->utilityFuncs->getSingle($tsConfig['config.'], 'returns')) === 1) {
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
	 * if this was not disabled in the settings
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
	 * @param resource boolean|\mysqli_result|object MySQLi result object / DBAL object
	 * @return bool
	 */
	protected function recordExists($res) {
		return ($res && $this->getDatabaseConnection()->sql_num_rows($res) > 0);
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}