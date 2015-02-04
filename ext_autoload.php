<?php
// We need to register some formhandler classes because they can not automagically be autoloaded during unit testing.
$formhandlerClassesPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('formhandler', 'Classes/');
return array(
	'tx_formhandler_abstractclass' => $formhandlerClassesPath . 'Component/Tx_Formhandler_AbstractClass.php',
	'tx_formhandler_abstractcomponent' => $formhandlerClassesPath . 'Component/Tx_Formhandler_AbstractComponent.php',
	'tx_formhandler_component_manager' => $formhandlerClassesPath . 'Component/Tx_Formhandler_Component_Manager.php',
	'tx_formhandler_abstractfinisher' => $formhandlerClassesPath . 'Finisher/Tx_Formhandler_AbstractFinisher.php',
	'tx_formhandler_finisher_generateauthcode' => $formhandlerClassesPath . 'Finisher/Tx_Formhandler_Finisher_GenerateAuthCode.php',
);