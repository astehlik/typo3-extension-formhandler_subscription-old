<?php
/*
 * Register necessary class names with autoloader
 *
 */
$classesPath = t3lib_extMgm::extPath('formhandler_subscription', 'Classes/');
return array(
	'tx_formhandlersubscription_api_subscriptionapi'	=> $classesPath . 'API/Tx_FormhandlerSubscription_API_SubscriptionAPI.php',
);
?>