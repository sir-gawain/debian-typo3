<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2011 Workspaces Team (http://forge.typo3.org/projects/show/typo3v4-workspaces)
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
 * @author Workspaces Team (http://forge.typo3.org/projects/show/typo3v4-workspaces)
 * @package Workspaces
 * @subpackage Service
 */
class tx_Workspaces_Service_Befunc {

	protected static $pageCache = array();

	/**
	 * Hooks into the t3lib_beFunc::viewOnClick and redirects to the workspace preview
	 * only if we're in a workspace and if the frontend-preview is disabled.
	 *
	 * @param  $pageUid
	 * @param  $backPath
	 * @param  $rootLine
	 * @param  $anchorSection
	 * @param  $viewScript
	 * @param  $additionalGetVars
	 * @param  $switchFocus
	 * @return void
	 */
	public function preProcess(&$pageUid, $backPath, $rootLine, $anchorSection, &$viewScript, $additionalGetVars, $switchFocus) {

			// In case a $pageUid is submitted we need to make sure it points to a live-page
		if ($pageUid >  0) {
			$pageUid = $this->getLivePageUid($pageUid);
		}

		if ($GLOBALS['BE_USER']->workspace !== 0) {
			$ctrl = t3lib_div::makeInstance('Tx_Workspaces_Controller_PreviewController', FALSE);
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			/** @var $uriBuilder Tx_Extbase_MVC_Web_Routing_UriBuilder */
			$uriBuilder = $objectManager->create('Tx_Extbase_MVC_Web_Routing_UriBuilder');
			/**
			 *  This seems to be very harsh to set this directly to "/typo3 but the viewOnClick also
			 *  has /index.php as fixed value here and dealing with the backPath is very error-prone
			 *
			 *  @todo make sure this would work in local extension installation too
			 */
			$backPath = '/' . TYPO3_mainDir;
				// @todo why do we need these additional params? the URIBuilder should add the controller, but he doesn't :(
			$additionalParams = '&tx_workspaces_web_workspacesworkspaces%5Bcontroller%5D=Preview&M=web_WorkspacesWorkspaces&id=';
			$viewScript = $backPath . $uriBuilder->uriFor('index', array(), 'Tx_Workspaces_Controller_PreviewController', 'workspaces', 'web_workspacesworkspaces') . $additionalParams;
		}
	}

	/**
	 * Find the Live-Uid for a given page,
	 * the results are cached at run-time to avoid too many database-queries
	 *
	 * @throws InvalidArgumentException
	 * @param  $uid
	 * @return void
	 */
	protected function getLivePageUid($uid) {
		if (!isset(self::$pageCache[$uid])) {
			$rec = t3lib_beFunc::getRecord('pages', $uid);
			if (is_array($rec)) {
				self::$pageCache[$uid] = $rec['t3ver_oid'] ? $rec['t3ver_oid'] : $uid;
			} else {
				throw new InvalidArgumentException('uid is supposed to point to an existing page - given value was:' . $uid, 1290628113);
			}
		}
		return self::$pageCache[$uid];
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/Service/Befunc.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/workspaces/Classes/Service/Befunc.php']);
}
?>