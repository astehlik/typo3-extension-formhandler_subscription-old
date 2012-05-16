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
 * This class can be used independently from the context (e.g. an eID request)
 * for executing subscription logic.
 *
 * It uses the AJAX API of formhandler_subscription.
 */
class Tx_FormhandlerSubscription_API_SubscriptionAPI {

	/**
	 * The page UID where the request will be sent to, if not set
	 * we will try to detect it automatically
	 *
	 * @var null
	 */
	var $pageUid = NULL;

	/**
	 * The prefix for the form values
	 *
	 * @var string
	 */
	var $formValuesPrefix = 'tx_formhandler_subscription';

	/**
	 * Initializes required parameters
	 */
	protected function initialize() {
		$this->initializePageUid();
	}

	/**
	 * Tries to autodetect the target page UID
	 *
	 * @throws RuntimeException If the page id can not be determined
	 */
	protected function initializePageUid() {

		if (isset($this->pageUid)) {
			return;
		}

		/**
		 * @var t3lib_pageSelect $pageSelect
		 */
		$pageSelect = t3lib_div::makeInstance('t3lib_pageSelect');
		$whereStatement = "tt_content.list_type='formhandler_pi1' AND pages.uid=tt_content.pid ";
		$whereStatement .= 'AND tt_content.pi_flexform LIKE \'%<field index="predefined">%<value index="vDEF">formhandler_subscription_remove_subscription.</value>%</field>%\'';
		$whereStatement .= $pageSelect->enableFields('pages');
		$whereStatement .= $pageSelect->enableFields('tt_content');

		$contentResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pages.uid', 'pages,tt_content', $whereStatement, '', '', '1');


		if ($contentResult === FALSE) {
			throw new RuntimeException('Error detecting target PID: ' . $GLOBALS['TYPO3_DB']->sql_error());
		}

		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($contentResult)) {
			throw new RuntimeException('The target PID could not be detected. No active formhandler plugin content element was found.');
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_row($contentResult);
		$this->pageUid = $row[0];
	}

	/**
	 * Requests the subscription to the newsletter with the given subscriber
	 * data.
	 *
	 * @param array $subscriberData
	 */
	public function requestSubscription($subscriberData) {
		$this->initialize();
		$this->executeRequest(254447653, $subscriberData);
	}

	/**
	 * If you want to submit the values with a custom form value prefix
	 * you can overwrite the default (tx_formhandler_subscription) here
	 *
	 * @param $formValuesPrefix
	 */
	public function setFormValuePrefix($formValuesPrefix) {
		$this->formValuesPrefix = $formValuesPrefix;
	}

	/**
	 * If you do not want the target PID to be detected automatically you
	 * can overwrite it here
	 *
	 * @param int $pageUid
	 */
	public function setPageUid($pageUid) {
		$this->pageUid = $pageUid;
	}

	/**
	 * Executes the request to the AJAX API and returns the result in
	 * an array
	 *
	 * @param int $typeNum the page type number that should be used in this request
	 * @param array $urlParameters this array contains the URL parameters that will be submitted to the AJAX script
	 * @return array
	 * @throws RuntimeException If request or the parsing of the response to JSON fails
	 */
	protected function executeRequest($typeNum, $urlParameters) {

		$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_HOST');
		$urlParameters = t3lib_div::implodeArrayForUrl($this->formValuesPrefix, $urlParameters);

		$getUrlReport = array();
		$url = $url . '?id=' . $this->pageUid . '&type=' . $typeNum . $urlParameters;
		$result = t3lib_div::getUrl($url, 0, FALSE, $getUrlReport);

		if ($result === FALSE) {
			throw new RuntimeException('Error fetching URL ' . $url . ': ' . $getUrlReport['message']);
		}

		$resultData = json_decode($result);
		if (!isset($resultData)) {
			throw new RuntimeException('JSON object could not be parsed from result: ' . $result . ' fetched from ' . $url);
		}

		return $resultData;
	}
}
