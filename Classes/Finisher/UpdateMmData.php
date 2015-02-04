<?php
namespace Tx\FormhandlerSubscription\Finisher;

/*                                                                        *
 * This script belongs to the TYPO3 extension "formhandler_subscription". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Tx\FormhandlerSubscription\Utils\AuthCodeUtils;
use Tx_Formhandler_AbstractFinisher as FormhandlerAbstractFinisher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Updates data in a mm table
 */
class UpdateMmData extends FormhandlerAbstractFinisher {

	/**
	 * Array containing all uids that are allowed
	 *
	 * @var array
	 */
	protected $allowedForeignUids;

	/**
	 * If this is TRUE all records will be deleted an if no allowed foreign UIDs have
	 * been set no error will be thrown
	 *
	 * @var bool
	 */
	protected $deleteAll = FALSE;

	/**
	 * Array containing the new foreign uids (that will be stored in the table)
	 *
	 * @var array
	 */
	protected $foreignUids = array();

	/**
	 * The local uid
	 *
	 * @var int
	 */
	protected $localUid;

	/**
	 * The table where the mm relation is stored in
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The field name in the table that contains the local uids
	 *
	 * @var string
	 */
	protected $uidLocalField = 'uid_local';

	/**
	 * The field name in the table that contains the foreign uids
	 *
	 * @var string
	 */
	protected $uidForeignField = 'uid_foreign';

	/**
	 * Auth code related utility functions
	 *
	 * @var AuthCodeUtils
	 */
	protected $utils;

	/**
	 * TYPO3 database
	 *
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected $typo3Db;

	/**
	 * Inits the finisher mapping settings values to internal attributes.
	 *
	 * @param array $gp
	 * @param array $settings
	 * @return void
	 */
	public function init($gp, $settings) {

		parent::init($gp, $settings);

		$this->typo3Db = $GLOBALS['TYPO3_DB'];
		$this->utils = AuthCodeUtils::getInstance();
	}

	/**
	 * Removes all entries for the current local uid from the mm table and adds the selected
	 * entries to the database
	 *
	 * @return array the GET/POST data array
	 */
	public function process() {

		$this->initialize();

		$insertArray = array();

		foreach ($this->foreignUids as $foreignUid) {

			$foreignUid = MathUtility::forceIntegerInRange($foreignUid, 0);

			if (!$foreignUid) {
				continue;
			}

			$foreignUidAllowed = array_search($foreignUid, $this->allowedForeignUids);
			if ($foreignUidAllowed === FALSE) {
				$this->utilityFuncs->throwException('A non-allowed foreign uid was submitted.');
			}

			$insertArray[] = array(
				$this->localUid,
				$foreignUid
			);
		}

		$this->deleteExistingRelations();

		if (!$this->deleteAll) {
			$this->insertNewRelations($insertArray);
		}

		return $this->gp;
	}

	/**
	 * Inserts new relations in the mm table if related records have been submitted
	 *
	 * @param array $insertArray
	 */
	public function insertNewRelations($insertArray) {

		if (!count($insertArray)) {
			return;
		}

		$insertQuery = $this->typo3Db->INSERTmultipleRows($this->table, array($this->uidLocalField, $this->uidForeignField), $insertArray);
		$this->utilityFuncs->debugMessage('sql_request', array($insertQuery));
		$insertResult = $this->typo3Db->sql_query($insertQuery);
		if (!$insertResult) {
			$this->utilityFuncs->throwException('Error in SQL query for inserting new relations: ' . $this->typo3Db->sql_error());
		}
	}

	/**
	 * Removes existing relations from the mm table from the database
	 */
	protected function deleteExistingRelations() {
		$deleteQuery = $this->typo3Db->DELETEquery($this->table, $this->uidLocalField . '=' . $this->localUid);
		$this->utilityFuncs->debugMessage('sql_request', array($deleteQuery));
		$deleteResult = $this->typo3Db->sql_query($deleteQuery);
		if (!$deleteResult) {
			$this->utilityFuncs->throwException('Error in SQL query for deleting existing relations: ' . $this->typo3Db->sql_error());
		}
	}

	/**
	 * Initializes and validates all configuration options
	 */
	protected function initialize() {

		$table = trim($this->utilityFuncs->getSingle($this->settings, 'table'));
		if (!empty($table)) {
			$this->table = $table;
		} else {
			$this->utilityFuncs->throwException('The name of the mm table is required when using the UpdateMmData finisher.');
		}

		$uidLocalField = trim($this->utilityFuncs->getSingle($this->settings, 'uidLocalField'));
		if (!empty($uidLocalField)) {
			$this->uidLocalField = $uidLocalField;
		}

		$uidForeignField = trim($this->utilityFuncs->getSingle($this->settings, 'uidForeignField'));
		if (!empty($uidForeignField)) {
			$this->uidForeignField = $uidForeignField;
		}

		$localUid = (string)$this->utilityFuncs->getSingle($this->settings, 'localUid');
		$localUid = MathUtility::forceIntegerInRange($localUid, 0);
		if ($localUid) {
			$this->localUid = $localUid;
		} else {
			$this->utilityFuncs->throwException('The local uid could not be determined in the UpdateMmData finisher.');
		}

		// it would be more flexible to use stdWrap for getting the field value
		// but stdWrap will always return a string (it will not return an array)
		$foreignUidsField = (string)$this->utilityFuncs->getSingle($this->settings, 'foreignUidsField');
		if (array_key_exists($foreignUidsField, $this->gp)) {
			$foreignUids = $this->gp[$foreignUidsField];
			if (is_array($foreignUids)) {
				$this->foreignUids = $foreignUids;
			}
		}

		$disableForeignUidCheck = (bool)$this->utilityFuncs->getSingle($this->settings, 'disableForeignUidCheck');
		$this->deleteAll = (bool)$this->utilityFuncs->getSingle($this->settings, 'deleteAll');

		$allowedForeignUids = $this->utilityFuncs->getSingle($this->settings, 'allowedForeignUids');
		$allowedForeignUids = GeneralUtility::trimExplode(',', $allowedForeignUids, TRUE);
		if (count($allowedForeignUids)) {
			$this->allowedForeignUids = $allowedForeignUids;
		} elseif (!($this->deleteAll || $disableForeignUidCheck)) {
			$this->utilityFuncs->throwException('Please provide a list of allowed foreign uids in the allowedForeignUids setting or set deleteAll to TRUE.');
		}
	}
}