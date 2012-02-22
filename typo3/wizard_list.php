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
 * Wizard to list records from a page id.
 *
 * $Id: wizard_list.php 3439 2008-03-16 19:16:51Z flyguide $
 * Revised for TYPO3 3.6 November/2003 by Kasper Skaarhoj
 * XHTML compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   74: class SC_wizard_list
 *   93:     function init()
 *  105:     function main()
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



$BACK_PATH='';
require ('init.php');
require ('template.php');
$LANG->includeLLFile('EXT:lang/locallang_wizards.xml');











/**
 * Script Class for redirecting the user to the Web > List module if a wizard-link has been clicked in TCEforms
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_wizard_list {

		// Internal, static:
	var $pid;					// PID

		// Internal, static: GPvars
	var $P;						// Wizard parameters, coming from TCEforms linking to the wizard.
	var $table;					// Table to show, if none, then all tables are listed in list module.
	var $id;					// Page id to list.





	/**
	 * Initialization of the class, setting GPvars.
	 *
	 * @return	void
	 */
	function init()	{
		$this->P = t3lib_div::_GP('P');
		$this->table = t3lib_div::_GP('table');
		$this->id = t3lib_div::_GP('id');
	}

	/**
	 * Main function
	 * Will issue a location-header, redirecting either BACK or to a new alt_doc.php instance...
	 *
	 * @return	void
	 */
	function main()	{

			// Get this record
		$origRow = t3lib_BEfunc::getRecord($this->P['table'],$this->P['uid']);

			// Get TSconfig for it.
		$TSconfig = t3lib_BEfunc::getTCEFORM_TSconfig($this->table,is_array($origRow)?$origRow:array('pid'=>$this->P['pid']));

			// Set [params][pid]
		if (substr($this->P['params']['pid'],0,3)=='###' && substr($this->P['params']['pid'],-3)=='###')	{
			$this->pid = intval($TSconfig['_'.substr($this->P['params']['pid'],3,-3)]);
		} else $this->pid = intval($this->P['params']['pid']);

			// Make redirect:
		if (!strcmp($this->pid,'') || strcmp($this->id,''))	{	// If pid is blank OR if id is set, then return...
			header('Location: '.t3lib_div::locationHeaderUrl($this->P['returnUrl']));
		} else {	// Otherwise, show the list:
			header('Location: '.t3lib_div::locationHeaderUrl('db_list.php?id='.$this->pid.'&table='.$this->P['params']['table'].'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))));
		}
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/wizard_list.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/wizard_list.php']);
}












// Make instance:
$SOBE = t3lib_div::makeInstance('SC_wizard_list');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>