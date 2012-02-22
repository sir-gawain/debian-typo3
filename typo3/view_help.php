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
 * Document for viewing the online help texts, also known as TCA_DESCR.
 * See Inside TYPO3 for details.
 *
 * $Id: view_help.php 4087 2008-09-09 08:37:27Z dmitry $
 * Revised for TYPO3 3.7 5/2004 by Kasper Skaarhoj
 * XHTML-trans compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   91: class local_t3lib_parsehtml extends t3lib_parsehtml
 *  102:     function processContent($value,$dir,$conf)
 *
 *
 *  122: class SC_view_help
 *  146:     function init()
 *  167:     function main()
 *  198:     function printContent()
 *
 *              SECTION: Rendering main modes
 *  221:     function render_TOC()
 *  338:     function render_TOC_el($table, $tocCat, &$outputSections, &$tocArray, &$CSHkeys)
 *  370:     function render_TOC_makeTocList($tocArray)
 *  409:     function render_Table($table)
 *  469:     function render_Single($table,$field)
 *
 *              SECTION: Rendering CSH items
 *  515:     function make_seeAlso($value,$anchorTable='')
 *  564:     function printImage($images,$descr)
 *  599:     function headerLine($str,$type=0)
 *  620:     function prepareContent($str)
 *  635:     function printItem($table,$field,$anchors=0)
 *  668:     function getTableFieldNames($table,$field)
 *  691:     function getTableFieldLabel($table,$field='',$mergeToken=': ')
 *
 *              SECTION: Glossary related
 *  726:     function createGlossaryIndex()
 *  785:     function substituteGlossaryWords($code)
 *  801:     function substituteGlossaryWords_htmlcleaner_callback($code)
 *
 * TOTAL FUNCTIONS: 19
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require('init.php');
require('template.php');
$LANG->includeLLFile('EXT:lang/locallang_view_help.xml');
require_once(PATH_t3lib.'class.t3lib_loadmodules.php');
require_once(PATH_t3lib.'class.t3lib_parsehtml.php');


/**
 * Extension of the parse_html class.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class local_t3lib_parsehtml extends t3lib_parsehtml {

	/**
	 * Processing content between tags for HTML_cleaner
	 *
	 * @param	string		The value
	 * @param	integer		Direction, either -1 or +1. 0 (zero) means no change to input value.
	 * @param	mixed		Not used, ignore.
	 * @return	string		The processed value.
	 * @access private
	 */
	function processContent($value,$dir,$conf)	{
		$value = $this->pObj->substituteGlossaryWords_htmlcleaner_callback($value);

		return $value;
	}
}







/**
 * Script Class for rendering the Context Sensitive Help documents, either the single display in the small pop-up window or the full-table view in the larger window.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_view_help {
	var $allowedHTML = '<strong><em><b><i>';

		// For these vars, see init()
	var $limitAccess;	// If set access to fields and tables is checked. Should be done for true database tables.
	var $table;			// The "table" key
	var $field;			// The "field" key

		// Internal, static: GPvar:
	var $tfID;			// Table/FIeld id.
	var $ffID;			// Flexform file/field information
	var $back;			// Back (previous tfID)
	var $renderALL;		// If set, then in TOC mode the FULL manual will be printed as well!

		// Internal, dynamic:
	var $content;	// Content accumulation.
	var $glossaryWords;		// Glossary words



	/**
	 * Initialize the class for various input etc.
	 *
	 * @return	void
	 */
	function init()	{
		global $LANG, $TCA;

			// Setting GPvars:
		$this->tfID = t3lib_div::_GP('tfID');
		if (!$this->tfID) {
			if (($this->ffID = t3lib_div::_GP('ffID'))) {
				$this->ffID = unserialize(base64_decode($this->ffID));
			}
		}
		$this->back = t3lib_div::_GP('back');
		$this->renderALL = t3lib_div::_GP('renderALL');

			// Set internal table/field to the parts of "tfID" incoming var.
		list($this->table,$this->field) = explode('.',$this->tfID);

			// limitAccess is checked if the $this->table really IS a table (and if the user is NOT a translator who should see all!)
		$showAllToUser = t3lib_BEfunc::isModuleSetInTBE_MODULES('txllxmltranslateM1') && $GLOBALS['BE_USER']->check('modules','txllxmltranslateM1');
		$this->limitAccess = isset($TCA[$this->table]) ? !$showAllToUser : FALSE;
	}

	/**
	 * Main function, rendering the display
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$TCA_DESCR,$TCA,$TBE_TEMPLATE;

			// Start HTML output accumulation:
		$TBE_TEMPLATE->docType = 'xhtml_trans';
		$TBE_TEMPLATE->divClass = 'typo3-view-help';
		$this->content.= $TBE_TEMPLATE->startPage($LANG->getLL('title'));

		if ($this->field=='*')	{ // If ALL fields is supposed to be shown:
			$this->createGlossaryIndex();
			$this->content.= $this->render_Table($this->table);
		} elseif ($this->tfID) { // ... otherwise show only single field:
			$this->createGlossaryIndex();
			$this->content.= $this->render_Single($this->table,$this->field);
		}
		elseif (is_array($this->ffID)) {
			$this->content.= $this->render_SingleFlex();
		} else {	// Render Table Of Contents if nothing else:
			$this->content.= $this->render_TOC();
		}

			// Print close-button:
#		$this->content.='<br /><form action=""><input type="submit" value="'.htmlspecialchars($LANG->getLL('close')).'" onclick="self.close(); return false;" /></form><br/>';

			// End page:
		$this->content.= '<br/>';
		$this->content.= $TBE_TEMPLATE->endPage();
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		echo $this->content;
	}









	/************************************
	 *
	 * Rendering main modes
	 *
	 ************************************/

	/**
	 * Creates Table Of Contents and possibly "Full Manual" mode if selected.
	 *
	 * @return	string		HTML content
	 */
	function render_TOC()	{
		global $TCA_DESCR,$TCA,$LANG,$BE_USER,$TBE_MODULES;

			// Initialize:
		$CSHkeys = array_flip(array_keys($TCA_DESCR));
		$TCAkeys = array_keys($TCA);

		$outputSections = array();
		$tocArray = array();


			// TYPO3 Core Features:
		$LANG->loadSingleTableDescription('xMOD_csh_corebe');
		$this->render_TOC_el('xMOD_csh_corebe', 'core', $outputSections, $tocArray, $CSHkeys);

			// Backend Modules:
		$loadModules = t3lib_div::makeInstance('t3lib_loadModules');
		$loadModules->load($TBE_MODULES);
		foreach($loadModules->modules as $mainMod => $info)	{
			$cshKey = '_MOD_'.$mainMod;
			if ($CSHkeys[$cshKey])	{
				$LANG->loadSingleTableDescription($cshKey);
				$this->render_TOC_el($cshKey, 'modules', $outputSections, $tocArray, $CSHkeys);
			}

			if (is_array($info['sub']))	{
				foreach($info['sub'] as $subMod => $subInfo)	{
					$cshKey = '_MOD_'.$mainMod.'_'.$subMod;
					if ($CSHkeys[$cshKey])	{
						$LANG->loadSingleTableDescription($cshKey);
						$this->render_TOC_el($cshKey, 'modules', $outputSections, $tocArray, $CSHkeys);
					}
				}
			}
		}

			// Database Tables:
		foreach($TCAkeys as $table)	{
				// Load descriptions for table $table
			$LANG->loadSingleTableDescription($table);
			if (is_array($TCA_DESCR[$table]['columns']) && $BE_USER->check('tables_select',$table))	{
				$this->render_TOC_el($table, 'tables', $outputSections, $tocArray, $CSHkeys);
			}
		}

			// Extensions
		foreach($CSHkeys as $cshKey => $value)	{
			if (t3lib_div::isFirstPartOfStr($cshKey, 'xEXT_') && !isset($TCA[$cshKey]))	{
				$LANG->loadSingleTableDescription($cshKey);
				$this->render_TOC_el($cshKey, 'extensions', $outputSections, $tocArray, $CSHkeys);
			}
		}

			// Glossary
		foreach($CSHkeys as $cshKey => $value)	{
			if (t3lib_div::isFirstPartOfStr($cshKey, 'xGLOSSARY_') && !isset($TCA[$cshKey]))	{
				$LANG->loadSingleTableDescription($cshKey);
				$this->render_TOC_el($cshKey, 'glossary', $outputSections, $tocArray, $CSHkeys);
			}
		}

			// Other:
		foreach($CSHkeys as $cshKey => $value)	{
			if (!t3lib_div::isFirstPartOfStr($cshKey, '_MOD_') && !isset($TCA[$cshKey]))	{
				$LANG->loadSingleTableDescription($cshKey);
				$this->render_TOC_el($cshKey, 'other', $outputSections, $tocArray, $CSHkeys);
			}
		}


			// COMPILE output:
		$output = '';
		$output.= '

			<h1>'.$LANG->getLL('manual_title',1).'</h1>
			';

		$output.= '

			<h2>'.$LANG->getLL('introduction',1).'</h2>
			<p class="introduction">'.$LANG->getLL('description',1).'</p>';

		$output.= '

			<h2>'.$LANG->getLL('TOC',1).'</h2>'.
			$this->render_TOC_makeTocList($tocArray);

		if (!$this->renderALL)	{
		$output.= '
				<br/>
				<p class="c-nav"><a href="view_help.php?renderALL=1">'.$LANG->getLL('full_manual',1).'</a></p>';
		}

		if ($this->renderALL)	{
			$output.= '

				<h2>'.$LANG->getLL('full_manual_chapters',1).'</h2>'.
				implode('


				<!-- NEW SECTION: -->
				',$outputSections);
		}
		
		$output .= '<hr /><p class="manual-title">'.t3lib_BEfunc::TYPO3_copyRightNotice().'</p>';
		
		return $output;
	}

	/**
	 * Creates a TOC list element and renders corresponding HELP content if "renderALL" mode is set.
	 *
	 * @param	string		CSH key / Table name
	 * @param	string		TOC category keyword: "core", "modules", "tables", "other"
	 * @param	array		Array for accumulation of rendered HELP Content (in "renderALL" mode). Passed by reference!
	 * @param	array		TOC array; Here TOC index elements are created. Passed by reference!
	 * @param	array		CSH keys array. Every item rendered will be unset in this array so finally we can see what CSH keys are not processed yet. Passed by reference!
	 * @return	void
	 */
	function render_TOC_el($table, $tocCat, &$outputSections, &$tocArray, &$CSHkeys)	{
		global $LANG;

		if ($this->renderALL)	{	// Render full manual right here!
			$outputSections[$table] = $this->render_Table($table);

			if ($outputSections[$table])	{
				$outputSections[$table] = '

		<!-- New CSHkey/Table: '.$table.' -->
		<p class="c-nav"><a name="ANCHOR_'.$table.'" href="#">'.$LANG->getLL('to_top',1).'</a></p>
		<h2>'.$this->getTableFieldLabel($table).'</h2>

		'.$outputSections[$table];
				$tocArray[$tocCat][$table] = '<a href="#ANCHOR_'.$table.'">'.$this->getTableFieldLabel($table).'</a>';
			} else {
				unset($outputSections[$table]);
			}
		} else {	// Only TOC:
			$tocArray[$tocCat][$table] = '<p><a href="view_help.php?tfID='.rawurlencode($table.'.*').'">'.$this->getTableFieldLabel($table).'</a></p>';
		}

			// Unset CSH key:
		unset($CSHkeys[$table]);
	}

	/**
	 * Renders the TOC index as a HTML bullet list from TOC array
	 *
	 * @param	array		ToC Array.
	 * @return	string		HTML bullet list for index.
	 */
	function render_TOC_makeTocList($tocArray)	{
		global $LANG;

			// The Various manual sections:
		$keys = explode(',', 'core,modules,tables,extensions,glossary,other');

			// Create TOC bullet list:
		$output = '';
		foreach($keys as $tocKey)	{
			if (is_array($tocArray[$tocKey]))	{
				$output.='
					<li>'.$LANG->getLL('TOC_'.$tocKey,1).'
						<ul>
							<li>'.implode('</li>
							<li>',$tocArray[$tocKey]).'</li>
						</ul>
					</li>';
			}
		}

			// Compile TOC:
		$output = '

			<!-- TOC: -->
			<div class="c-toc">
				<ul>
				'.$output.'
				</ul>
			</div>';

		return $output;
	}

	/**
	 * Render CSH for a full cshKey/table
	 *
	 * @param	string		CSH key / table name
	 * @return	string		HTML output
	 */
	function render_Table($table)	{
		global $BE_USER,$TCA_DESCR,$TCA,$LANG;

		$output = '';

			// Load table TCA
		t3lib_div::loadTCA($table);

			// Load descriptions for table $table
		$LANG->loadSingleTableDescription($table);

		if (is_array($TCA_DESCR[$table]['columns']) && (!$this->limitAccess || $BE_USER->check('tables_select',$table)))	{
				// Initialize variables:
			$parts = array();
			$parts[0] = '';	// Reserved for header of table

				// Traverse table columns as listed in TCA_DESCR
			reset($TCA_DESCR[$table]['columns']);
			while(list($field) = each($TCA_DESCR[$table]['columns']))	{

				$fieldValue = isset($TCA[$table]) && strcmp($field,'') ? $TCA[$table]['columns'][$field] : array();

				if (is_array($fieldValue) && (!$this->limitAccess || !$fieldValue['exclude'] || $BE_USER->check('non_exclude_fields',$table.':'.$field)))	{
					if (!$field)	{
						$parts[0] = $this->printItem($table,'',1);	// Header
					} else {
						$parts[] = $this->printItem($table,$field,1);	// Field
					}
				}
			}

			if (!strcmp($parts,''))	unset($parts[0]);
			$output.= implode('<br />',$parts);
		}

			// Substitute glossary words:
		$output = $this->substituteGlossaryWords($output);

			// TOC link:
		if (!$this->renderALL)	{
			$tocLink = '<p class="c-nav"><a href="view_help.php">'.$LANG->getLL('goToToc',1).'</a></p>';

			$output =
				$tocLink.'
				<br/>'.
				$output.'
				<br />'.
				$tocLink;
		}

		return $output;
	}

	/**
	 * Renders CSH for a single field.
	 *
	 * @param	string		CSH key / table name
	 * @param	string		Sub key / field name
	 * @return	string		HTML output
	 */
	function render_Single($table,$field)	{
		global $LANG, $TCA;

		$output = '';

			// Load descriptions for table $table
		$LANG->loadSingleTableDescription($table);

			// Render single item:
		$output.= $this->printItem($table,$field);

			// Substitute glossary words:
		$output = $this->substituteGlossaryWords($output);

			// Link to Full table description and TOC:
		$getLLKey = $this->limitAccess ? 'fullDescription' : 'fullDescription_module';
		$output.= '<br />
			<p class="c-nav"><a href="view_help.php?tfID='.rawurlencode($table.'.*').'">'.$LANG->getLL($getLLKey,1).'</a></p>
			<p class="c-nav"><a href="view_help.php">'.$LANG->getLL('goToToc',1).'</a></p>';

		return $output;
	}


	/**
	 * Renders CSH for a single field.
	 *
	 * @param	string		CSH key / table name
	 * @param	string		Sub key / field name
	 * @return	string		HTML output
	 */
	function render_SingleFlex() {
		$output = '';

			// Render
		$output.= $this->printItemFlex();

			// Substitute glossary words:
		return $this->substituteGlossaryWords($output);
	}


	/************************************
	 *
	 * Rendering CSH items
	 *
	 ************************************/

	/**
	 * Make seeAlso links from $value
	 *
	 * @param	string		See-also input codes
	 * @param	string		If $anchorTable is set to a tablename, then references to this table will be made as anchors, not URLs.
	 * @return	string		See-also links HTML
	 */
	function make_seeAlso($value,$anchorTable='')	{
		global $TCA,$BE_USER,$TCA_DESCR;

			// Split references by comma, vert.line or linebreak
		$items = split(',|'.chr(10),$value);
		$lines = array();

		foreach($items as $val)	{
			$val = trim($val);
			if ($val)	{
				$iP = explode(':',$val);
				$iPUrl = t3lib_div::trimExplode('|',$val);
					// URL reference:
				if (substr($iPUrl[1],0,4)=='http')	{
					$lines[] = '<a href="'.htmlspecialchars($iPUrl[1]).'" target="_blank"><em>'.htmlspecialchars($iPUrl[0]).'</em></a>';
				} elseif (substr($iPUrl[1],0,5)=='FILE:')	{
					$fileName = t3lib_div::getFileAbsFileName(substr($iPUrl[1],5),1,1);
					if ($fileName && @is_file($fileName))	{
						$fileName = '../'.substr($fileName,strlen(PATH_site));
						$lines[] = '<a href="'.htmlspecialchars($fileName).'" target="_blank"><em>'.htmlspecialchars($iPUrl[0]).'</em></a>';
					}
				} else {
					// "table" reference
					t3lib_div::loadTCA($iP[0]);

					if (!isset($TCA[$iP[0]]) || ((!$iP[1] || is_array($TCA[$iP[0]]['columns'][$iP[1]])) && (!$this->limitAccess || ($BE_USER->check('tables_select',$iP[0]) && (!$iP[1] || !$TCA[$iP[0]]['columns'][$iP[1]]['exclude'] || $BE_USER->check('non_exclude_fields',$iP[0].':'.$iP[1]))))))	{	// Checking read access:

							// Load table descriptions:
						#$LANG->loadSingleTableDescription($iP[0]);
						if (isset($TCA_DESCR[$iP[0]]))	{
								// Make see-also link:
							$href = ($this->renderALL || ($anchorTable && $iP[0]==$anchorTable) ? '#'.implode('.',$iP) : 'view_help.php?tfID='.rawurlencode(implode('.',$iP)).'&back='.$this->tfID);
							$label = $this->getTableFieldLabel($iP[0],$iP[1],' / ');
							$lines[] = '<a href="'.htmlspecialchars($href).'">'.htmlspecialchars($label).'</a>';
						}
					}
				}
			}
		}
		return implode('<br />',$lines);
	}

	/**
	 * Will return an image tag with description in italics.
	 *
	 * @param	string		Image file reference (list of)
	 * @param	string		Description string (divided for each image by line break)
	 * @return	string		Image HTML codes
	 */
	function printImage($images,$descr)	{
		$code = '';
			// Splitting:
		$imgArray = t3lib_div::trimExplode(',', $images, 1);
		if (count($imgArray))	{
			$descrArray = explode(chr(10),$descr,count($imgArray));

			foreach($imgArray as $k => $image)	{
				$descr = $descrArray[$k];

				$absImagePath = t3lib_div::getFileAbsFileName($image,1,1);
				if ($absImagePath && @is_file($absImagePath))	{
					$imgFile = substr($absImagePath,strlen(PATH_site));
					$imgInfo = @getimagesize($absImagePath);
					if (is_array($imgInfo))	{
						$imgFile = '../'.$imgFile;
						$code.= '<br /><img src="'.$imgFile.'" '.$imgInfo[3].' class="c-inlineimg" alt="" /><br />
						';
						$code.= '<p><em>'.$GLOBALS['LANG']->hscAndCharConv($descr,1).'</em></p>
						';
					} else $code.= '<div style="background-color: red; border: 1px solid black; color: white;">NOT AN IMAGE: '.$imgFile.'</div>';
				} else $code.= '<div style="background-color: red; border: 1px solid black; color: white;">IMAGE FILE NOT FOUND: '.$image.'</div>';
			}
		}

		return $code;
	}

	/**
	 * Returns header HTML content
	 *
	 * @param	string		Header text
	 * @param	string		Header type (1, 0)
	 * @return	string		The HTML for the header.
	 */
	function headerLine($str,$type=0)	{
		switch($type)	{
			case 1:
				$str='<h3>'.htmlspecialchars($str).'</h3>
				';
			break;
			case 0:
				$str='<h4 class="uppercase">'.htmlspecialchars($str).'</h4>
				';
			break;
		}

		return $str;
	}

	/**
	 * Returns prepared content
	 *
	 * @param	string		Content to format.
	 * @return	string		Formatted content.
	 */
	function prepareContent($str)	{
		$str = $GLOBALS['LANG']->hscAndCharConv($str,0);
		return '<p>'.nl2br(trim(strip_tags($str,$this->allowedHTML))).'</p>
		';
	}

	/**
	 * Prints a single $table/$field information piece
	 * If $anchors is set, then seeAlso references to the same table will be page-anchors, not links.
	 *
	 * @param	string		Table name
	 * @param	string		Field name
	 * @param	boolean		If anchors is to be shown.
	 * @return	string		HTML content
	 */
	function printItem($table,$field,$anchors=0)	{
		global $TCA_DESCR, $LANG, $TCA, $BE_USER;

			// Load full table definition in $TCA
		t3lib_div::loadTCA($table);

		if ($table && (!$field || is_array($TCA_DESCR[$table]['columns'][$field])))	{
				// Make seeAlso references.
			$seeAlsoRes = $this->make_seeAlso($TCA_DESCR[$table]['columns'][$field]['seeAlso'],$anchors?$table:'');

				// Making item:
			$out= '<a name="'.$table.'.'.$field.'"></a>
					'.
					$this->headerLine($this->getTableFieldLabel($table,$field),1).
					$this->prepareContent($TCA_DESCR[$table]['columns'][$field]['description']).
					($TCA_DESCR[$table]['columns'][$field]['details'] ? $this->headerLine($LANG->getLL('details').':').$this->prepareContent($TCA_DESCR[$table]['columns'][$field]['details']) : '').
					($TCA_DESCR[$table]['columns'][$field]['syntax'] ? $this->headerLine($LANG->getLL('syntax').':').$this->prepareContent($TCA_DESCR[$table]['columns'][$field]['syntax']) : '').
					($TCA_DESCR[$table]['columns'][$field]['image'] ? $this->printImage($TCA_DESCR[$table]['columns'][$field]['image'],$TCA_DESCR[$table]['columns'][$field]['image_descr']) : '').
					($TCA_DESCR[$table]['columns'][$field]['seeAlso'] && $seeAlsoRes ? $this->headerLine($LANG->getLL('seeAlso').':').'<p>'.$seeAlsoRes.'</p>' : '').
					($this->back ? '<br /><p><a href="'.htmlspecialchars('view_help.php?tfID='.rawurlencode($this->back)).'" class="typo3-goBack">'.htmlspecialchars($LANG->getLL('goBack')).'</a></p>' : '').
			'<br />';
		}
		return $out;
	}

	/**
	 * Prints a single $table/$field information piece
	 * If $anchors is set, then seeAlso references to the same table will be page-anchors, not links.
	 *
	 * @param	string		Table name
	 * @param	string		Field name
	 * @param	boolean		If anchors is to be shown.
	 * @return	string		HTML content
	 */
	function printItemFlex() {
		// Get all texts
		foreach (explode(',', 'description,details,syntax,image,image_descr,seeAlso') as $var) {
			// Double $ below is not a error!
			$$var = $GLOBALS['LANG']->sL($this->ffID['cshFile'] . ':' . $this->ffID['field'] . '.' . $var);
		}
		// Make seeAlso references.
		$seeAlsoRes = $this->make_seeAlso($seeAlso);

			// Making item:
		$out= $this->headerLine($this->ffID['title'], 1) .
				$this->prepareContent($description) .
				($details ? $this->headerLine($GLOBALS['LANG']->getLL('details').':') . $this->prepareContent($details) : '') .
				($syntax ? $this->headerLine($GLOBALS['LANG']->getLL('syntax').':') . $this->prepareContent($syntax) : '') .
				($image ? $this->printImage($image, $image_descr) : '') .
				($seeAlso && $seeAlsoRes ? $this->headerLine($GLOBALS['LANG']->getLL('seeAlso').':').'<p>'.$seeAlsoRes.'</p>' : '') .
		'<br />';
		return $out;
	}

	/**
	 * Returns labels for $table and $field.
	 * If $table is "_MOD_" prefixed, the part after "_MOD_" is returned (non-tables, fx. modules)
	 *
	 * @param	string		Table name
	 * @param	string		Field name
	 * @return	array		Table and field labels in a numeric array
	 */
	function getTableFieldNames($table,$field)	{
		global $TCA, $TCA_DESCR, $LANG;

			$LANG->loadSingleTableDescription($table);

			$tableName = is_array($TCA_DESCR[$table]['columns']['']) && $TCA_DESCR[$table]['columns']['']['alttitle'] ?
							$TCA_DESCR[$table]['columns']['']['alttitle'] :
							(isset($TCA[$table]) ? $TCA[$table]['ctrl']['title'] : ereg_replace('^_MOD_','',$table));
			$fieldName = is_array($TCA_DESCR[$table]['columns'][$field]) && $TCA_DESCR[$table]['columns'][$field]['alttitle'] ?
							$TCA_DESCR[$table]['columns'][$field]['alttitle'] :
							(isset($TCA[$table])&&isset($TCA[$table]['columns'][$field]) ? $TCA[$table]['columns'][$field]['label'] : $field);
		return array($tableName,$fieldName);
	}

	/**
	 * Returns composite label for table/field
	 *
	 * @param	string		Table name
	 * @param	string		Field name
	 * @param	string		Token to merge the two strings with.
	 * @return	array		Table and field labels in a numeric array
	 * @see getTableFieldNames()
	 */
	function getTableFieldLabel($table,$field='',$mergeToken=': ')	{
		global $LANG;

			// Get table / field parts:
		list($tableName,$fieldName) = $this->getTableFieldNames($table,$field);

			// Create label:
		$labelStr = $LANG->sL($tableName).
					($field ? $mergeToken.ereg_replace(':$','', trim($LANG->sL($fieldName))):'');

		return $labelStr;
	}











	/******************************
	 *
	 * Glossary related
	 *
	 ******************************/

	/**
	 * Creates glossary index in $this->glossaryWords
	 * Glossary is cached in cache_hash table and so will be updated only when cache is cleared.
	 *
	 * @return	void
	 */
	function createGlossaryIndex()	{
		global $TCA_DESCR,$TCA,$LANG;

			// Create hash string and try to retrieve glossary array:
		$hash = md5('typo3/view_help.php:glossary');
 		list($this->glossaryWords,$this->substWords) = unserialize(t3lib_BEfunc::getHash($hash));

			// Generate glossary words if not found:
		if (!is_array($this->glossaryWords)) {

				// Initialize:
			$this->glossaryWords = array();
			$this->substWords = array();
			$CSHkeys = array_flip(array_keys($TCA_DESCR));

				// Glossary
			foreach($CSHkeys as $cshKey => $value)	{
				if (t3lib_div::isFirstPartOfStr($cshKey, 'xGLOSSARY_') && !isset($TCA[$cshKey]))	{
					$LANG->loadSingleTableDescription($cshKey);

					if (is_array($TCA_DESCR[$cshKey]['columns']))	{

							// Traverse table columns as listed in TCA_DESCR
						reset($TCA_DESCR[$cshKey]['columns']);
						while(list($field,$data) = each($TCA_DESCR[$cshKey]['columns']))	{
							if ($field)	{
								$this->glossaryWords[$cshKey.'.'.$field] = array(
									'title' => trim($data['alttitle'] ? $data['alttitle'] : $cshKey),
									'description' =>  str_replace('%22','%23%23%23', rawurlencode($data['description'])),
								);
							}
						}
					}
				}
			}

				// First, create unique list of words:
			foreach($this->glossaryWords as $key => $value)	{
				$word = strtolower($value['title']);	// Making word lowercase in order to filter out same words in different cases.

				if ($word!=='')	{
					$this->substWords[$word] = $value;
					$this->substWords[$word]['key'] = $key;
				}
			}

			krsort($this->substWords);

			t3lib_BEfunc::storeHash($hash,serialize(array($this->glossaryWords,$this->substWords)),'Glossary');
		}
	}

	/**
	 * Processing of all non-HTML content in the output
	 * Will be done by a call-back to ->substituteGlossaryWords_htmlcleaner_callback()
	 *
	 * @param	string		Input HTML code
	 * @return	string		Output HTML code
	 */
	function substituteGlossaryWords($code) {
		$htmlParser = t3lib_div::makeInstance('local_t3lib_parsehtml');
		$htmlParser->pObj = &$this;
		$code = $htmlParser->HTMLcleaner($code, array(), 1);

		return $code;
	}

	/**
	 * Substituting glossary words in the CSH
	 * (This is a call-back function from "class local_t3lib_parsehtml extends t3lib_parsehtml", see top of this script)
	 *
	 * @param	string		Input HTML string
	 * @return	string		HTML with substituted words in.
	 * @coauthor	alex widschwendter, media.res kommunikationsloesungen
	 */
	function substituteGlossaryWords_htmlcleaner_callback($code)	{
		if (is_array($this->substWords) && count($this->substWords) && strlen(trim($code)))	{

				// Substitute words:
			foreach($this->substWords as $wordKey => $wordSet)	{
					// quoteMeta used so special chars (which should not occur though) in words will not break the regex. Seemed to work (- kasper)
				$parts = preg_split('/( |[\(])('.quoteMeta($wordSet['title']).')([\.\!\)\?\:\,]+| )/i', ' '.$code.' ', 2, PREG_SPLIT_DELIM_CAPTURE);
				if (count($parts) == 5)	{
					$parts[2] = '<a class="glossary-term" href="'.htmlspecialchars('view_help.php?tfID='.rawurlencode($wordSet['key']).'&back='.$this->tfID).'" title="'.rawurlencode(htmlspecialchars(t3lib_div::fixed_lgd_cs(rawurldecode($wordSet['description']),80))).'">'.
								htmlspecialchars($parts[2]).
								'</a>';
					$code = substr(implode('',$parts),1,-1);

						// Disable entry so it doesn't get used next time:
					unset($this->substWords[$wordKey]);
				}
			}
			$code = str_replace('###', '&quot;',rawurldecode($code));
		}

		return $code;
	}

}


// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/view_help.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/view_help.php']);
}






// Make instance:
$SOBE = t3lib_div::makeInstance('SC_view_help');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>
