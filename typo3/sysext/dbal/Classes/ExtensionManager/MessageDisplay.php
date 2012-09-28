<?php
namespace TYPO3\CMS\Dbal\ExtensionManager;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2011 Xavier Perseguers <xavier@typo3.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * Class that renders fields for the Extension Manager configuration.
 *
 * @author Xavier Perseguers <xavier@typo3.org>
 * @package TYPO3
 * @subpackage dbal
 */
class MessageDisplay {

	/**
	 * Renders a message for EM.
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\TypoScript\ConfigurationForm $tsObj
	 * @return string
	 * @todo Define visibility
	 */
	public function displayMessage(array &$params, \TYPO3\CMS\Core\TypoScript\ConfigurationForm $tsObj) {
		$out = '
			<div>
				<div class="typo3-message message-information">
					<div class="message-header">PostgreSQL</div>
					<div class="message-body">
						If you use a PostgreSQL database, make sure to run SQL scripts located in<br />
						<tt>' . \TYPO3\CMS\Core\Extension\ExtensionManager::extPath('dbal') . 'res/postgresql/</tt><br />
						to ensure best compatibility with TYPO3.
					</div>
				</div>
			</div>
		';
		return $out;
	}

}


?>