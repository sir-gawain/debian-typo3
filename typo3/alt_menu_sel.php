<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Creates the selector-box menu.
 * The selector-box menu is an alternative to the vertical default menu.
 * If configured to appear it will be displayed in the top-frame.
 *
 * $Id: alt_menu_sel.php 3439 2008-03-16 19:16:51Z flyguide $
 * Revised for TYPO3 3.6 2/2003 by Kasper Skaarhoj
 * XHTML compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   73: class SC_alt_menu_sel
 *   81:     function main()
 *  108:     function printContent()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require ('init.php');
require ('template.php');
require_once (PATH_t3lib.'class.t3lib_loadmodules.php');
require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once ('class.alt_menu_functions.inc');








/**
 * Script Class for rendering the selector box menu
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_alt_menu_sel {
	var $content;

	/**
	 * Main function, making the selector box menu
	 *
	 * @return	void
	 */
	function main()	{
		global $TBE_MODULES,$TBE_TEMPLATE;

			// Initialize modules
		$loadModules = t3lib_div::makeInstance('t3lib_loadModules');
		$loadModules->observeWorkspaces = TRUE;
		$loadModules->load($TBE_MODULES);

			// Start page
		$TBE_TEMPLATE->form = '<form action="">';
		$TBE_TEMPLATE->docType = 'xhtml_trans';

			// add menu JS
		$alt_menuObj = t3lib_div::makeInstance('alt_menu_functions');
		$TBE_TEMPLATE->JScodeArray[] = $alt_menuObj->generateMenuJScode($loadModules->modules);

		$this->content.=$TBE_TEMPLATE->startPage('Selector box menu');

			// Make menu and add it:
		$this->content.=$alt_menuObj->topMenu($loadModules->modules,0,'',2);

			// End page:
		$this->content.=$TBE_TEMPLATE->endPage();
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		echo $this->content;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/alt_menu_sel.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/alt_menu_sel.php']);
}











// Make instance:
$SOBE = t3lib_div::makeInstance('SC_alt_menu_sel');
$SOBE->main();
$SOBE->printContent();
?>
