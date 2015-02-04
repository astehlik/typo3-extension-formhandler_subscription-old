<?php
namespace Tx\FormhandlerSubscription\Tests\Unit\Finisher;

/*                                                                        *
 * This script belongs to the TYPO3 extension "formhandler_subscription". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Tx\FormhandlerSubscription\Exceptions\InvalidSettingException;
use Tx\FormhandlerSubscription\Exceptions\MissingSettingException;
use Tx\FormhandlerSubscription\Utils\AuthCodeUtils;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for the finisher that generates an auth code that is stored
 * in the database
 */
class GenerateAuthCodeDbTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * Instance of auth code db finisher
	 *
	 * @var \Tx\FormhandlerSubscription\Finisher\GenerateAuthCodeDB
	 */
	protected $authCodeDbFinisher;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $formhandlerUtilityFuncs;

	/**
	 * @var array
	 */
	protected $defaultSettings = array(
		'table' => 'testtable',
	);

	/**
	 * Initialize the DB auth code finisher class.
	 */
	public function setUp() {

		$this->authCodeDbFinisher = $this->getAccessibleMock('Tx\\FormhandlerSubscription\\Finisher\\GenerateAuthCodeDB', array('dummy'), array(), '', FALSE);

		$this->formhandlerUtilityFuncs = $this->getMock('stdClass', array('getSingle'));
		$this->authCodeDbFinisher->_set('utilityFuncs', $this->formhandlerUtilityFuncs);

		/** @var AuthCodeUtils $authCodeUtils */
		$authCodeUtils = $this->getMock('Tx\\FormhandlerSubscription\\Utils\\AuthCodeUtils', array('dummy'), array(), '', FALSE);
		$this->authCodeDbFinisher->setAuthCodeUtils($authCodeUtils);
	}

	/**
	 * @test
	 */
	public function hiddenFieldDefaultIsHidden() {
		$this->authCodeDbFinisher->init(array(), $this->defaultSettings);
		$hiddenFieldName = $this->authCodeDbFinisher->getHiddenFieldName();
		$this->assertEquals('hidden', $hiddenFieldName);
	}

	/**
	 * @test
	 */
	public function hiddenFieldIsReadFromSettings() {

		$GLOBALS['TCA']['testtable']['ctrl']['enablecolumns']['disabled'] = 'hiddenValueFromTca';

		$settings = array_merge($this->defaultSettings, array(
			'hiddenField' => 'hiddenValueFromSettings',
		));

		$this->authCodeDbFinisher->init(array(), $settings);
		$hiddenFieldName = $this->authCodeDbFinisher->getHiddenFieldName();
		$this->assertEquals('hiddenValueFromSettings', $hiddenFieldName);
	}

	/**
	 * @test
	 */
	public function hiddenFieldIsReadFromTca() {

		$GLOBALS['TCA']['testtable']['ctrl']['enablecolumns']['disabled'] = 'hiddenValueFromTca';

		$this->formhandlerUtilityFuncs->expects($this->once())->method('getSingle')->will($this->returnValue('testtable'));
		$this->authCodeDbFinisher->init(array(), $this->defaultSettings);
		$hiddenFieldName = $this->authCodeDbFinisher->getHiddenFieldName();
		$this->assertEquals('hiddenValueFromTca', $hiddenFieldName);
	}

	/**
	 * @test
	 */
	public function actionDefaultIsEnable() {
		$this->authCodeDbFinisher->init(array(), $this->defaultSettings);
		$authCodeAction = $this->authCodeDbFinisher->getAuthCodeAction();
		$this->assertEquals(AuthCodeUtils::ACTION_ENABLE_RECORD, $authCodeAction);
	}

	/**
	 * @test
	 */
	public function actionInvalidThrowsException() {

		$settings = array_merge($this->defaultSettings, array(
			'action' => 'invalidAction'
		));

		try {
			$this->authCodeDbFinisher->init(array(), $settings);
		} catch (InvalidSettingException $invalidSettingException) {
			$this->assertEquals('action', $invalidSettingException->getInvalidSetting());
			return;
		}

		$this->fail('No exception was thrown even though the action was invalid');
	}

	/**
	 * @test
	 */
	public function tableNameIsRequired() {

		try {
			$this->authCodeDbFinisher->init(array(), array());
		} catch (MissingSettingException $missingSettingException) {
			$this->assertEquals('table', $missingSettingException->getMissingSetting());
			return;
		}

		$this->fail('No exception was thrown even though the table name was missing');
	}


	/**
	 * @test
	 */
	public function tableNameIsSet() {
		$this->formhandlerUtilityFuncs->expects($this->once())->method('getSingle')->will($this->returnValue('testtable'));
		$this->authCodeDbFinisher->init(array(), $this->defaultSettings);
		$tableName = $this->authCodeDbFinisher->getTableName();
		$this->assertEquals('testtable', $tableName);
	}

	/**
	 * @test
	 */
	public function uidFieldDefaultIsUid() {
		$this->authCodeDbFinisher->init(array(), $this->defaultSettings);
		$uidFieldName = $this->authCodeDbFinisher->getUidFieldName();
		$this->assertEquals('uid', $uidFieldName);
	}

	/**
	 * @test
	 */
	public function uidFieldDefaultIsSet() {
		$settings = array_merge($this->defaultSettings, array('uidField' => 'testUidFieldName'));
		$this->authCodeDbFinisher->init(array(), $settings);
		$uidFieldName = $this->authCodeDbFinisher->getUidFieldName();
		$this->assertEquals('testUidFieldName', $uidFieldName);
	}
}