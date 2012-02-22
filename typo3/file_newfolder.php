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
 * Web>File: Create new folders in the filemounts
 *
 * $Id: file_newfolder.php 4407 2008-11-01 17:21:28Z ohader $
 * Revised for TYPO3 3.6 November/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   74: class SC_file_newfolder
 *  101:     function init()
 *  161:     function main()
 *  255:     function printContent()
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



$BACK_PATH = '';
require('init.php');
require('template.php');
require_once(PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once(PATH_t3lib.'class.t3lib_parsehtml.php');










/**
 * Script Class for the create-new script; Displays a form for creating up to 10 folders or one new text file
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_file_newfolder {

		// External, static:
	var $folderNumber=10;

		// Internal, static:
	/**
	 * document template object
	 *
	 * @var smallDoc
	 */
	var $doc;

	/**
	 * File processing object
	 *
	 * @var t3lib_basicFileFunctions
	 */
	var $basicff;
	var $icon;			// Will be set to the proper icon for the $target value.
	var $shortPath;		// Relative path to current found filemount
	var $title;			// Name of the filemount

		// Internal, static (GPVar):
	var $number;
	var $target;		// Set with the target path inputted in &target
	var $returnUrl;		// Return URL of list module.

		// Internal, dynamic:
	var $content;		// Accumulating content



	/**
	 * Constructor function for class
	 *
	 * @return	void
	 */
	function init()	{
		global $LANG,$BACK_PATH,$TYPO3_CONF_VARS;

			// Initialize GPvars:
		$this->number = t3lib_div::_GP('number');
		$this->target = t3lib_div::_GP('target');
		$this->returnUrl = t3lib_div::_GP('returnUrl');

			// Init basic-file-functions object:
		$this->basicff = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$this->basicff->init($GLOBALS['FILEMOUNTS'],$TYPO3_CONF_VARS['BE']['fileExtensions']);

			// Cleaning and checking target
		$this->target = $GLOBALS['LANG']->csConvObj->conv($this->target, 'utf-8', $GLOBALS['LANG']->charSet);
		$this->target = $this->basicff->is_directory($this->target);
		$key=$this->basicff->checkPathAgainstMounts($this->target.'/');
		if (!$this->target || !$key)	{
			t3lib_BEfunc::typo3PrintError ('Parameter Error','Target was not a directory!','');
			exit;
		}

			// Finding the icon
		switch($GLOBALS['FILEMOUNTS'][$key]['type'])	{
			case 'user':	$this->icon = 'gfx/i/_icon_ftp_user.gif';	break;
			case 'group':	$this->icon = 'gfx/i/_icon_ftp_group.gif';	break;
			default:		$this->icon = 'gfx/i/_icon_ftp.gif';	break;
		}

		$this->icon = '<img'.t3lib_iconWorks::skinImg($this->backPath,$this->icon,'width="18" height="16"').' title="" alt="" />';

			// Relative path to filemount, $key:
		$this->shortPath = substr($this->target,strlen($GLOBALS['FILEMOUNTS'][$key]['path']));

			// Setting title:
		$this->title = $this->icon.$GLOBALS['FILEMOUNTS'][$key]['name'].': '.$this->shortPath;

			// Setting template object
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType = 'xhtml_trans';
		$this->doc->setModuleTemplate('templates/file_newfolder.html');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->JScode=$this->doc->wrapScriptTags('
			var path = "'.$this->target.'";

			function reload(a)	{	//
				if (!changed || (changed && confirm('.$LANG->JScharCode($LANG->sL('LLL:EXT:lang/locallang_core.php:mess.redraw')).')))	{
					var params = "&target="+encodeURIComponent(path)+"&number="+a+"&returnUrl=' 
							. urlencode($GLOBALS['LANG']->csConvObj->conv($this->returnUrl, $GLOBALS['LANG']->charSet, 'utf-8')) 
							. '";
					window.location.href = "file_newfolder.php?"+params;
				}
			}
			function backToList()	{	//
				top.goToModule("file_list");
			}

			var changed = 0;
		');
	}

	/**
	 * Main function, rendering the main module content
	 *
	 * @return	void
	 */
	function main()	{
		global $LANG;

			// start content compilation
		$this->content.=$this->doc->startPage($LANG->sL('LLL:EXT:lang/locallang_core.php:file_newfolder.php.pagetitle'));


			// Make page header:
		$pageContent='';
		$pageContent.=$this->doc->header($LANG->sL('LLL:EXT:lang/locallang_core.php:file_newfolder.php.pagetitle'));
		$pageContent.=$this->doc->spacer(5);
		$pageContent.=$this->doc->divider(5);


		$code = '<form action="tce_file.php" method="post" name="editform">';
			// Making the selector box for the number of concurrent folder-creations
		$this->number = t3lib_div::intInRange($this->number,1,10);
		$code .= '
			<div id="c-select">
				<select name="number" onchange="reload(this.options[this.selectedIndex].value);">';
		for ($a=1;$a<=$this->folderNumber;$a++)	{
			$code.='
					<option value="'.$a.'"'.($this->number==$a?' selected="selected"':'').'>'.$a.' '.$LANG->sL('LLL:EXT:lang/locallang_core.php:file_newfolder.php.folders',1).'</option>';
		}
		$code.='
				</select>
			</div>
			';

			// Making the number of new-folder boxes needed:
		$code.='
			<div id="c-createFolders">
		';
		for ($a=0;$a<$this->number;$a++)	{
			$code.='
					<input'.$this->doc->formWidth(20).' type="text" name="file[newfolder]['.$a.'][data]" onchange="changed=true;" />
					<input type="hidden" name="file[newfolder]['.$a.'][target]" value="'.htmlspecialchars($this->target).'" />
				';
		}
		$code.='
			</div>
		';

			// Making submit button for folder creation:
		$code.='
			<div id="c-submitFolders">
				<input type="submit" value="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:file_newfolder.php.submit',1).'" />
				<input type="submit" value="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.cancel',1).'" onclick="backToList(); return false;" />
				<input type="hidden" name="redirect" value="'.htmlspecialchars($this->returnUrl).'" />
			</div>
			';

			// CSH:
		$code.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'file_newfolder', $GLOBALS['BACK_PATH'],'<br/>');

		$pageContent.= $code;



			// Add spacer:
		$pageContent.= $this->doc->spacer(10);

			// Switching form tags:
		$pageContent.= $this->doc->sectionEnd();
		$pageContent.= '</form><form action="tce_file.php" method="post" name="editform2">';

			// Add form fields for creation of a new, blank text file:
		$code='
			<div id="c-newFile">
				<p>['.htmlspecialchars($GLOBALS['TYPO3_CONF_VARS']['SYS']['textfile_ext']).']</p>
				<input'.$this->doc->formWidth(20).' type="text" name="file[newfile][0][data]" onchange="changed=true;" />
				<input type="hidden" name="file[newfile][0][target]" value="'.htmlspecialchars($this->target).'" />
			</div>
			';

			// Submit button for creation of a new file:
		$code.='
			<div id="c-submitFiles">
				<input type="submit" value="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:file_newfolder.php.newfile_submit',1).'" />
				<input type="submit" value="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.cancel',1).'" onclick="backToList(); return false;" />
				<input type="hidden" name="redirect" value="'.htmlspecialchars($this->returnUrl).'" />
			</div>
			';

			// CSH:
		$code.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'file_newfile', $GLOBALS['BACK_PATH'],'<br/>');
		$pageContent.= $this->doc->section($LANG->sL('LLL:EXT:lang/locallang_core.php:file_newfolder.php.newfile'),$code);

		$docHeaderButtons = array();

			// Add the HTML as a section:
		$markerArray = array(
			'CSH' => $docHeaderButtons['csh'],
			'FUNC_MENU' => t3lib_BEfunc::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']),
			'CONTENT' => $pageContent,
			'PATH' => $this->title,
		);

		$this->content.= $this->doc->moduleBody(array(), $docHeaderButtons, $markerArray);
		$this->content.= $this->doc->endPage();

		$this->content = $this->doc->insertStylesAndJS($this->content);
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/file_newfolder.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/file_newfolder.php']);
}












// Make instance:
$SOBE = t3lib_div::makeInstance('SC_file_newfolder');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>