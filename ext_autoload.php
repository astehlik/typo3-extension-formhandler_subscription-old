<?php
/*
 * Register necessary class names with autoloader
 *
 */
$classesPath = t3lib_extMgm::extPath('formhandler_subscription', 'Classes/');
$unitTestsPath = t3lib_extMgm::extPath('formhandler_subscription', 'Tests/Unit/');
$formhandlerClassesPath = t3lib_extMgm::extPath('formhandler', 'Classes/');
return array(
	'tx_formhandler_abstractclass' => $formhandlerClassesPath . 'Component/Tx_Formhandler_AbstractClass.php',
	'tx_formhandler_abstractcomponent' => $formhandlerClassesPath . 'Component/Tx_Formhandler_AbstractComponent.php',
	'tx_formhandler_component_manager' => $formhandlerClassesPath . 'Component/Tx_Formhandler_Component_Manager.php',
	'tx_formhandler_abstractfinisher' => $formhandlerClassesPath . 'Finisher/Tx_Formhandler_AbstractFinisher.php',
	'tx_formhandler_finisher_generateauthcode' => $formhandlerClassesPath . 'Finisher/Tx_Formhandler_Finisher_GenerateAuthCode.php',
	'tx_formhandlersubscription_api_subscriptionapi' => $classesPath . 'API/Tx_FormhandlerSubscription_API_SubscriptionAPI.php',
	'tx_formhandlersubscription_exceptions_missingsettingexception' => $classesPath . 'Exceptions/MissingSettingException.php',
	'tx_formhandlersubscription_exceptions_abstractexception' => $classesPath . 'Exceptions/AbstractException.php',
	'tx_formhandlersubscription_finisher_generateauthcodedb' => $classesPath . 'Finisher/Tx_FormhandlerSubscription_Finisher_GenerateAuthCodeDB.php',
	'tx_formhandlersubscription_mvc_formhandlerdata' => $classesPath . 'Mvc/FormhandlerData.php',
	'tx_formhandlersubscription_test_unit_fixtures_mockcomponentmanager' => $unitTestsPath . 'Fixtures/MockComponentManager.php',
	'tx_formhandlersubscription_test_unit_fixtures_mockglobals' => $unitTestsPath . 'Fixtures/MockGlobals.php',
	'tx_formhandlersubscription_test_unit_fixtures_mockutilityfuncs' => $unitTestsPath . 'Fixtures/MockUtilityFuncs.php',
	'tx_formhandlersubscription_utils_typoscript' => $classesPath . 'Utils/Tx_FormhandlerSubscription_Utils_TypoScript.php',
);

?>