<?php

########################################################################
# Extension Manager/Repository config file for ext "formhandler_subscription".
#
# Auto generated 12-09-2012 15:48
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Formhandler Subscription',
	'description' => 'Provides additional classes for the formhandler extension to build (newsletter) subscribe and modify / unsubscripe forms. It comes with some YAML based example templates.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.3.0',
	'dependencies' => 'formhandler',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Alexander Stehlik',
	'author_email' => 'alexander.stehlik.deleteme@googlemail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
			'formhandler' => '1.4.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'tinyurls' => '0.0.1-0.0.0',
		),
	),
	'_md5_values_when_last_written' => 'a:25:{s:16:"ext_autoload.php";s:4:"c003";s:12:"ext_icon.gif";s:4:"0d47";s:17:"ext_localconf.php";s:4:"c55e";s:14:"ext_tables.php";s:4:"58b9";s:14:"ext_tables.sql";s:4:"7397";s:62:"Classes/API/Tx_FormhandlerSubscription_API_SubscriptionAPI.php";s:4:"69d9";s:81:"Classes/Controller/Tx_FormhandlerSubscription_Controller_AjaxSubmitController.php";s:4:"bc09";s:75:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_GenerateAuthCodeDB.php";s:4:"7b7d";s:77:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_InvalidateAuthCodeDB.php";s:4:"5137";s:77:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_RemoveAuthCodeRecord.php";s:4:"ff95";s:66:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_Subscribe.php";s:4:"b471";s:76:"Classes/Finisher/Tx_FormhandlerSubscription_Finisher_ValidateAuthCodeUID.php";s:4:"2491";s:83:"Classes/PreProcessor/Tx_FormhandlerSubscription_PreProcessor_ValidateAuthCodeDB.php";s:4:"6fa4";s:59:"Classes/Utils/Tx_FormhandlerSubscription_Utils_AuthCode.php";s:4:"eb45";s:61:"Classes/View/Tx_FormhandlerSubscription_View_AuthCodeMail.php";s:4:"1ede";s:36:"Configuration/Settings/constants.txt";s:4:"725c";s:32:"Configuration/Settings/setup.txt";s:4:"ae59";s:29:"Resources/Language/Global.xml";s:4:"c4f4";s:34:"Resources/T3D/pagestructure-de.t3d";s:4:"e53b";s:34:"Resources/T3D/pagestructure-en.t3d";s:4:"12f8";s:43:"Resources/Templates/RemoveSubscription.html";s:4:"3860";s:44:"Resources/Templates/RequestSubscription.html";s:4:"fd81";s:38:"Resources/Templates/RequestUpdate.html";s:4:"d60b";s:43:"Resources/Templates/UpdateSubscription.html";s:4:"be1b";s:14:"doc/manual.sxw";s:4:"b652";}',
	'suggests' => array(
	),
);

?>