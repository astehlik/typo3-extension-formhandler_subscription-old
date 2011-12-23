<?php

class Tx_FormhandlerSubscription_Finisher_GenerateAuthCodeDB extends Tx_Formhandler_Finisher_GenerateAuthCode {

	var $table;

	var $uidField = 'uid';

	var $hiddenField = '';

	var $updateHiddenField = 1;

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @param array $gp
	 * @param array $settings
	 * @return void
	 */
	public function init($gp, $settings) {

		if (!$this->settings['table']) {
			throw new Exception('The table needs to be specified');
		} else {
			$this->table = $this->utilityFuncs->getSingle($this->settings, 'table');
		}

		if ($this->settings['uidField']) {
			$this->uidField = $this->settings['uidField'];
		}

		if (intval($this->settings['doNotUpdateHiddenField'])) {
			$this->updateHiddenField = 0;
		}

		if ($this->settings['hiddenField']) {
			$this->hiddenField = $this->settings['hiddenField'];
		} elseif ($GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['hidden']) {
			$this->hiddenField = $GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['hidden'];
		} else {
			$this->hiddenField = 'hidden';
		}
	}

	/**
	 * Return a hash value to send by email as an auth code.
	 *
	 * @param array The submitted form data
	 * @return string The auth code
	 */
	protected function generateAuthCode($row) {

		$serializedRowData = serialize($row);
		$authCode = t3lib_div::getRandomHexString(16);
		$authCode = md5($serializedRowData, $authCode);
		$time = time();

		$authCodeInsertData = array(
			'pid' => '',
			'tstamp' => $time,
			'crdate' => $time,
			'reference_table' => $this->table,
			'reference_table_uid_field' => $this->uidField,
			'reference_table_uid' => $row[$this->uidField],
			'reference_table_hidden_field' => $this->hiddenField,
			'update_hidden_field' => $this->updateHiddenField,
			'serialized_auth_data' => $serializedRowData,
			'authCode' => $authCode
		);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_formhandler_subscription_authcodes', $authCode);

		return $authCode;
	}

}
?>