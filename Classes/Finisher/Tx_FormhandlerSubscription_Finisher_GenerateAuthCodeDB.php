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
 * Generates an auth code and stores it in the database
 *
 * Works similiar to Tx_Formhandler_Finisher_GenerateAuthCode but the generated
 * auth code is stored in the database and it references another record in the
 * database.
 *
 * At the moment two actions can be authorized with a generated auth code: accessing
 * a form (accessForm) and unhiding the referenced record (enableRecord).
 */
class Tx_FormhandlerSubscription_Finisher_GenerateAuthCodeDB extends Tx_Formhandler_Finisher_GenerateAuthCode {

	/**
	 * The table that contains the records that are referenced
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The field that contains the uid of the referenced record
	 *
	 * @var string
	 */
	protected $uidField = 'uid';

	/**
	 * The field that marks the referenced record as hidden
	 *
	 * @var string
	 */
	protected $hiddenField = '';

	/**
	 * The action that will be executed when the user provides
	 * the correct auth code
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Auth code related utility functions
	 *
	 * @var Tx_FormhandlerSubscription_Utils_AuthCode
	 */
	protected $utils;

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @param array $gp
	 * @param array $settings
	 * @return void
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		$this->utils = Tx_FormhandlerSubscription_Utils_AuthCode::getInstance();

		if (!$this->settings['table']) {
			throw new Exception('The table needs to be specified');
		} else {
			$this->table = $this->utilityFuncs->getSingle($this->settings, 'table');
		}

		if ($this->settings['uidField']) {
			$this->uidField = $this->settings['uidField'];
		}

		if (!empty($this->settings['action'])) {
			$this->action = $this->settings['action'];
		} else {
			$this->action = Tx_FormhandlerSubscription_Utils_AuthCode::ACTION_ENABLE_RECORD;
		}

		$this->utils->checkAuthCodeAction($this->action);

		if ($this->settings['hiddenField']) {
			$this->hiddenField = $this->settings['hiddenField'];
		} elseif ($GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['hidden']) {
			$this->hiddenField = $GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['hidden'];
		} else {
			$this->hiddenField = 'hidden';
		}
	}

	/**
	 * Checks, if the form values prefix should be overwritten
	 * and sets it to the configured value
	 *
	 * @return array the GET/POST data array
	 */
	public function process() {

		$currentFormValuesPrefix = $this->globals->getFormValuesPrefix();

		if (!empty($this->settings['overrideFormValuesPrefix'])) {
			$this->globals->setFormValuesPrefix($this->settings['overrideFormValuesPrefix']);
		}

		parent::process();

		$this->globals->setFormValuesPrefix($currentFormValuesPrefix);

		return $this->gp;
	}

	/**
	 * Creates a new entry in the tx_formhandler_subscription_authcodes table
	 * and return a hash value to send by email as an auth code.
	 *
	 * @param array The submitted form data
	 * @return string The auth code
	 */
	protected function generateAuthCode($row) {

		$serializedRowData = serialize($row);
		$authCode = t3lib_div::getRandomHexString(16);
		$authCode = md5($serializedRowData . $authCode);
		$time = time();

		$this->utils->clearAuthCodes($this->table, $this->uidField, $row[$this->uidField]);

		$authCodeInsertData = array(
			'pid' => '',
			'tstamp' => $time,
			'crdate' => $time,
			'reference_table' => $this->table,
			'reference_table_uid_field' => $this->uidField,
			'reference_table_uid' => $row[$this->uidField],
			'reference_table_hidden_field' => $this->hiddenField,
			'action' => $this->action,
			'serialized_auth_data' => $serializedRowData,
			'auth_code' => $authCode
		);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery(
			'tx_formhandler_subscription_authcodes',
			$authCodeInsertData
		);

		return $authCode;
	}
}
?>