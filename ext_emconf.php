<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "formhandler_subscription".
 *
 * Auto generated 11-01-2013 17:23
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Formhandler Subscription',
	'description' => 'Provides additional classes for the formhandler extension to build (newsletter) subscribe and modify / unsubscripe forms. It comes with some YAML based example templates.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.6.0',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'author' => 'Alexander Stehlik',
	'author_email' => 'alexander.stehlik.deleteme@googlemail.com',
	'author_company' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '6.2.1-6.2.99',
			'formhandler' => '1.4.0-0.0.0',
		),
		'conflicts' => array(),
		'suggests' => array(
			'tt_address' => '',
			'tinyurls' => '0.0.1-0.0.0',
		),
	),
);