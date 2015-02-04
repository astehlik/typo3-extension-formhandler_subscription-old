<?php
namespace Tx\FormhandlerSubscription\View;

/*                                                                        *
 * This script belongs to the TYPO3 extension "formhandler_subscription". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Tx_Formhandler_View_Mail as FormhandlerMailView;

/**
 * Provides a non htmlspecialchars encoded auth code url marker for plain text mails
 *
 * In plain text mail simply use the ###value_authCodeUrlPlain### marker.
 */
class AuthCodeMailView extends FormhandlerMailView {

	/**
	 * It works like the parent version but it adds an additional marker
	 * ###value_authCodeUrlPlain### that contains an htmlspecialchars decoded
	 * version of the auth code url.
	 *
	 * @see Tx_Formhandler_View_Mail::getValueMarkers()
	 * @param $values
	 * @param int $level
	 * @param string $prefix
	 * @return array array containing the value markers (###value_XXX###)
	 */
	protected function getValueMarkers($values, $level = 0, $prefix = 'value_') {

		$markers = parent::getValueMarkers($values, $level, $prefix);

		if (array_key_exists('###value_authCodeUrl###', $markers)) {
			$markers['###value_authCodeUrlPlain###'] = htmlspecialchars_decode($markers['###value_authCodeUrl###']);
			$markers['###VALUE_AUTHCODEURLPLAIN###'] = $markers['###value_authCodeUrlPlain###'];
		}

		return $markers;
	}
}