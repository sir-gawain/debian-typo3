<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Steffen Kamper <info@sk-typo3.de>
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
 * Contains the update class for merging advanced and normal pagetype.
 * 
 * @author Steffen Kamper <info@sk-typo3.de>
 * @version $Id: class.tx_coreupdates_mergeadvanced.php 3078 2008-02-05 18:36:07Z ohader $
 */
class tx_coreupdates_mergeadvanced {
	var $versionNumber;	// version number coming from t3lib_div::int_from_ver()

	/**
	 * parent object
	 *
	 * @var tx_install
	 */
	var $pObj;
	var $userInput;	// user input


	/**
	 * Checks if an update is needed
	 *
	 * @param	string		&$description: The description for the update
	 * @return	boolean		whether an update is needed (true) or not (false)
	 */
	public function checkForUpdate(&$description) {
		$result = false;
		$description = 'Merges the "Advanced" pagetype (doktype 2) to "Standard" (doktype 1) because "Standard" now has the same features, and "Advanced" is not needed anymore.';

		if ($this->versionNumber >= 4002000) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'doktype=2', '', '', '1');
			if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
				$result = true;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $result;
	}


	/**
	 * Performs the database update. Changes the doktype from 2 (advanced) to 1 (standard)
	 *
	 * @param	array		&$dbQueries: queries done in this update
	 * @param	mixed		&$customMessages: custom messages
	 * @return	boolean		whether it worked (true) or not (false)
	 */
	public function performUpdate(&$dbQueries, &$customMessages) {
		$result = false;
		if($this->versionNumber >= 4002000)	{
			$updateArray = array(
				'doktype' => 1,
			);

			$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('pages', 'doktype=2', $updateArray);
			$dbQueries[] = str_replace(chr(10), ' ', $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
			
			if ($GLOBALS['TYPO3_DB']->sql_error()) {
				$customMessages = 'SQL-ERROR: ' . htmlspecialchars($GLOBALS['TYPO3_DB']->sql_error());
			} else {
				$result = true;
			}
		}
		return $result;
	}
}
?>