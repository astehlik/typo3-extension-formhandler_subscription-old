<?php
/**
 * Created by JetBrains PhpStorm.
 * User: astehlik
 * Date: 24.12.11
 * Time: 11:30
 * To change this template use File | Settings | File Templates.
 */
class Tx_FormhandlerSubscription_View_AuthCodeMail extends Tx_Formhandler_View_Mail {

	protected function getValueMarkers($values, $level = 0, $prefix = 'value_') {
		$markers = parent::getValueMarkers($values, $level, $prefix);

		if (array_key_exists('###value_authCodeUrl###', $markers)) {
			$markers['###value_authCodeUrlPlain###'] = htmlspecialchars_decode($markers['###value_authCodeUrl###']);
			$markers['###VALUE_AUTHCODEURLPLAIN###'] = $markers['###value_authCodeUrlPlain###'];
		}

		return $markers;
	}

}
