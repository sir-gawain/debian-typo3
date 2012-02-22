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
 * Wizard for inserting TSconfig in form fields. (page,user or TS)
 *
 * $Id: wizard_tsconfig.php 3439 2008-03-16 19:16:51Z flyguide $
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
 *   98: class ext_TSparser extends t3lib_tsparser_ext
 *  106:     function makeHtmlspecialchars($P)
 *
 *
 *  127: class SC_wizard_tsconfig
 *  149:     function init()
 *  202:     function setValue(field,value)
 *  232:     function mixerField(cmd,objString)
 *  258:     function str_replace(match,replace,string)
 *  280:     function jump(show,objString)
 *  295:     function main()
 *  320:     function printContent()
 *  333:     function browseTSprop($mode,$show)
 *
 *              SECTION: Module functions
 *  419:     function getObjTree()
 *  449:     function setObj(&$objTree,$strArr,$params)
 *  469:     function revertFromSpecialChars($str)
 *  482:     function doLink($params)
 *  495:     function removePointerObjects($objArray)
 *  514:     function linkToObj($str,$uid,$objString='')
 *  527:     function printTable($table,$objString,$objTree)
 *  608:     function linkProperty($str,$propertyName,$prefix,$datatype)
 *
 * TOTAL FUNCTIONS: 17
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



$BACK_PATH='';
require ('init.php');
require ('template.php');
$LANG->includeLLFile('EXT:lang/locallang_wizards.xml');
require_once (PATH_t3lib.'class.t3lib_parsehtml.php');
require_once (PATH_t3lib.'class.t3lib_tstemplate.php');
require_once (PATH_t3lib.'class.t3lib_tsparser_ext.php');












/**
 * TypoScript parser extension class.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class ext_TSparser extends t3lib_tsparser_ext {

	/**
	 * Pass through of incoming value for link.
	 *
	 * @param	array		P array
	 * @return	string		The "_LINK" key value, straight away.
	 */
	function makeHtmlspecialchars($P)	{
		return $P['_LINK'];
	}
}










/**
 * Script Class for rendering the TSconfig/TypoScript property browser.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_wizard_tsconfig {

		// Internal, dynamic:
	/**
	 * document template object
	 *
	 * @var mediumDoc
	 */
	var $doc;
	var $content;				// Content accumulation for the module.

		// Internal, static: GPvars
	var $P;						// Wizard parameters, coming from TCEforms linking to the wizard.
	var $mode;					// "page", "tsref" or "beuser"
	var $show;					// Pointing to an entry in static_tsconfig_help to show.
	var $objString;				// Object path - for display.
	var $onlyProperty;			// If set, the "mixed-field" is not shown and you can select only one property at a time.





	/**
	 * Initialization of the class
	 *
	 * @return	void
	 */
	function init()	{
		global $LANG,$BACK_PATH;

			// Check if the tsconfig_help extension is loaded - which is mandatory for this wizard to work.
		t3lib_extMgm::isLoaded('tsconfig_help',1);

			// Init GPvars:
		$this->P = t3lib_div::_GP('P');
		$this->mode = t3lib_div::_GP('mode');
		$this->show = t3lib_div::_GP('show');
		$this->objString = t3lib_div::_GP('objString');
		$this->onlyProperty = t3lib_div::_GP('onlyProperty');
			// Preparing some JavaScript code:
		if (!is_array($this->P['fieldChangeFunc']))	$this->P['fieldChangeFunc']=array();
		unset($this->P['fieldChangeFunc']['alert']);
		$update='';
		foreach($this->P['fieldChangeFunc'] as $k=>$v)	{
			$update.= '
			window.opener.'.$v;
		}

			// Init the document table object:
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->docType = 'xhtml_trans';
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" name="editform">';

			// Adding Styles (should go into stylesheet?)
		$this->doc->inDocStylesArray[] = '
			A:link {text-decoration: bold; color: '.$this->doc->hoverColor.';}
			A:visited {text-decoration: bold; color: '.$this->doc->hoverColor.';}
			A:active {text-decoration: bold; color: '.$this->doc->hoverColor.';}
			A:hover {color: '.$this->doc->bgColor2.'}
		';

		$this->doc->JScode.=$this->doc->wrapScriptTags('
			function checkReference_name()	{	// Checks if the input field containing the name exists in the document
				if (window.opener && window.opener.document && window.opener.document.'.$this->P['formName'].' && window.opener.document.'.$this->P['formName'].'["'.$this->P['itemName'].'"] )	{
					return window.opener.document.'.$this->P['formName'].'["'.$this->P['itemName'].'"];
				}
			}
			function checkReference_value()	{	// Checks if the input field containing the value exists in the document
				if (window.opener && window.opener.document && window.opener.document.'.$this->P['formName'].' && window.opener.document.'.$this->P['formName'].'["'.$this->P['itemValue'].'"] )	{
					return window.opener.document.'.$this->P['formName'].'["'.$this->P['itemValue'].'"];
				}
			}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$field,value: ...
	 * @return	[type]		...
	 */
			function setValue(field,value)	{
				var nameField = checkReference_name();
				var valueField = checkReference_value();
				if (nameField)	{
					if (valueField)	{	// This applies to the TS Object Browser module
						nameField.value=field;
						valueField.value=value;
					} else {		// This applies to the Info/Modify module and the Page TSconfig field
						if (value) {
							nameField.value=field+"="+value+"\n"+nameField.value;
						} else {
							nameField.value=field+"\n"+nameField.value;
						}
					}
					'.$update.'
					window.opener.focus();
				}
				close();
			}
			function getValue()	{	// This is never used. Remove it?
				var field = checkReference_name();
				if (field)	{
					return field.value;
				} else {
					close();
				}
			}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$cmd,objString: ...
	 * @return	[type]		...
	 */
			function mixerField(cmd,objString)	{
				var temp;
				switch(cmd)	{
					case "Indent":
						temp = str_replace("\n","\n  ","\n"+document.editform.mixer.value);
						document.editform.mixer.value = temp.substr(1);
					break;
					case "Outdent":
						temp = str_replace("\n  ","\n","\n"+document.editform.mixer.value);
						document.editform.mixer.value = temp.substr(1);
					break;
					case "Transfer":
						setValue(document.editform.mixer.value);
					break;
					case "Wrap":
						document.editform.mixer.value=objString+" {\n"+document.editform.mixer.value+"\n}";
					break;
				}
			}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$match,replace,string: ...
	 * @return	[type]		...
	 */
			function str_replace(match,replace,string)	{
				var input = ""+string;
				var matchStr = ""+match;
				if (!matchStr)	{return string;}
				var output = "";
				var pointer=0;
				var pos = input.indexOf(matchStr);
				while (pos!=-1)	{
					output+=""+input.substr(pointer, pos-pointer)+replace;
					pointer=pos+matchStr.length;
					pos = input.indexOf(match,pos+1);
				}
				output+=""+input.substr(pointer);
				return output;
			}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$show,objString: ...
	 * @return	[type]		...
	 */
			function jump(show,objString)	{
				window.location.href = "'.t3lib_div::linkThisScript(array('show'=>'','objString'=>'')).'&show="+show+"&objString="+objString;
			}
		');


			// Start the page:
		$this->content.=$this->doc->startPage($LANG->getLL('tsprop'));
	}

	/**
	 * Main function, rendering the content of the TypoScript property browser, including links to online resources
	 *
	 * @return	void
	 */
	function main()	{
		global $LANG;

			// Adding module content:
		$this->content.=$this->doc->section($LANG->getLL('tsprop'),$this->browseTSprop($this->mode,$this->show),0,1);

			// Adding link to TSref:
		if ($this->mode=='tsref')	{
			$this->content.=$this->doc->section($LANG->getLL('tsprop_TSref'),'
			<a href="'.htmlspecialchars('http://typo3.org/documentation/document-library/references/doc_core_tsref/current/view/').'" target="_blank">'.$LANG->getLL('tsprop_TSref',1).'</a>
			',0,1);
		}
			// Adding link to admin guides etc:
		if ($this->mode=='page' || $this->mode=='beuser')	{
			$this->content.=$this->doc->section($LANG->getLL('tsprop_tsconfig'),'
			<a href="'.htmlspecialchars('http://typo3.org/documentation/document-library/references/doc_core_tsconfig/current/view/').'" target="_blank">'.$LANG->getLL('tsprop_tsconfig',1).'</a>
			',0,1);
		}
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
	}

	/**
	 * Create the content of the module:
	 *
	 * @param	string		Object string
	 * @param	integer		Pointing to an entry in static_tsconfig_help to show.
	 * @return	string		HTML
	 */
	function browseTSprop($mode,$show)	{
		global $LANG;

			// Get object tree:
		$objTree = $this->getObjTree();

			// Show single element, if show is set.
		$out='';
		if ($show)	{
				// Get the entry data:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'static_tsconfig_help', 'uid='.intval($show));
			$rec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$table = unserialize($rec['appdata']);
			$obj_string = strtr($this->objString,'()','[]');	// Title:

				// Title and description:
			$out.='<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('show'=>''))).'" class="typo3-goBack">'.
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/goback.gif','width="14" height="14"').' alt="" />'.
					htmlspecialchars($obj_string).
					'</a><br />';
			if ($rec['title'])	$out.= '<strong>'.htmlspecialchars($rec['title']).': </strong>';
			if ($rec['description'])	$out.= nl2br(htmlspecialchars(trim($rec['description']))).'<br />';

				// Printing the content:
			$out.= '<br />'.$this->printTable($table, $obj_string, $objTree[$mode.'.']);
			$out.='<hr />';

				// Printing the "mixer-field":
			if (!$this->onlyProperty)	{
				$links=array();
				$links[]='<a href="#" onclick="mixerField(\'Indent\');return false;">'.$LANG->getLL('tsprop_mixer_indent',1).'</a>';
				$links[]='<a href="#" onclick="mixerField(\'Outdent\');return false;">'.$LANG->getLL('tsprop_mixer_outdent',1).'</a>';
				$links[]='<a href="#" onclick="mixerField(\'Wrap\',unescape(\''.rawurlencode($obj_string).'\'));return false;">'.$LANG->getLL('tsprop_mixer_wrap',1).'</a>';
				$links[]='<a href="#" onclick="mixerField(\'Transfer\');return false;">'.$LANG->getLL('tsprop_mixer_transfer',1).'</a>';
				$out.='<textarea rows="5" name="mixer" wrap="off"'.$this->doc->formWidthText(48,'','off').' class="fixed-font enable-tab"></textarea>';
				$out.='<br /><strong>'.implode('&nbsp; | &nbsp;',$links).'</strong>';
				$out.='<hr />';
			}
		}


			// SECTION: Showing property tree:
		$tmpl = t3lib_div::makeInstance('ext_TSparser');
		$tmpl->tt_track = 0;	// Do not log time-performance information
		$tmpl->fixedLgd=0;
		$tmpl->linkObjects=0;
		$tmpl->bType='';
		$tmpl->ext_expandAllNotes=1;
		$tmpl->ext_noPMicons=1;
		$tmpl->ext_noSpecialCharsOnLabels=1;

		if (is_array($objTree[$mode.'.']))	{
			$out.='


			<!--
				TSconfig, object tree:
			-->
				<table border="0" cellpadding="0" cellspacing="0" id="typo3-objtree">
					<tr>
						<td nowrap="nowrap">'.$tmpl->ext_getObjTree($this->removePointerObjects($objTree[$mode.'.']),'','','','','1').'</td>
					</tr>
				</table>';
		}

		return $out;
	}







	/***************************
	 *
	 * Module functions
	 *
	 ***************************/

	/**
	 * Create object tree from static_tsconfig_help table
	 *
	 * @return	array		Object tree.
	 * @access private
	 */
	function getObjTree()	{
		$objTree=array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,obj_string,title', 'static_tsconfig_help', '');
		while($rec = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$rec['obj_string'] = $this->revertFromSpecialChars($rec['obj_string']);
			$p = explode(';',$rec['obj_string']);
			while(list(,$v)=each($p))	{
				$p2 = t3lib_div::trimExplode(':',$v,1);
				$subp=t3lib_div::trimExplode('/',$p2[1],1);
				while(list(,$v2)=each($subp))	{
					$this->setObj($objTree,explode('.',$p2[0].'.'.$v2),array($rec,$v2));
				}
			}
		}
		return $objTree;
	}

	/**
	 * Sets the information from a static_tsconfig_help record in the object array.
	 * Makes recursive calls.
	 *
	 * @param	array		Object tree array, passed by value!
	 * @param	array		Array of elements from object path (?)
	 * @param	array		Array with record and something else (?)
	 * @return	void
	 * @access private
	 * @see getObjTree()
	 */
	function setObj(&$objTree,$strArr,$params)	{
		$key = current($strArr);
		reset($strArr);
		if (count($strArr)>1)	{
			array_shift($strArr);
			if (!isset($objTree[$key.'.']))	$objTree[$key.'.']=array();
			$this->setObj($objTree[$key.'.'],$strArr,$params);
		} else {
			$objTree[$key]=$params;
			$objTree[$key]['_LINK']=$this->doLink($params);
		}
	}

	/**
	 * Converts &gt; and &lt; to > and <
	 *
	 * @param	string		Input string
	 * @return	string		Output string
	 * @access private
	 */
	function revertFromSpecialChars($str)	{
		$str = str_replace('&gt;','>',$str);
		$str = str_replace('&lt;','<',$str);
		return $str;
	}

	/**
	 * Creates a link based on input params array:
	 *
	 * @param	array		Parameters
	 * @return	string		The link.
	 * @access private
	 */
	function doLink($params)	{
		$title = trim($params[0]['title'])?trim($params[0]['title']):'[GO]';
		$str = $this->linkToObj($title,$params[0]['uid'],$params[1]);
		return $str;
	}

	/**
	 * Remove pointer strings from an array
	 *
	 * @param	array		Input array
	 * @return	array		Modified input array
	 * @access private
	 */
	function removePointerObjects($objArray)	{
		reset($objArray);
		while(list($k)=each($objArray))	{
			if (substr(trim($k),0,2)=="->" && trim($k)!='->.')	{
				$objArray['->.'][substr(trim($k),2)]=$objArray[$k];
				unset($objArray[$k]);
			}
		}
		return $objArray;
	}

	/**
	 * Linking string to object by UID
	 *
	 * @param	string		String to link
	 * @param	integer		UID of a static_tsconfig_help record.
	 * @param	string		Title string for that record!
	 * @return	string		Linked string
	 */
	function linkToObj($str,$uid,$objString='')	{
		$aOnClick='jump(\''.rawurlencode($uid).'\',\''.rawurlencode($objString).'\');return false;';
		return '<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.htmlspecialchars($str).'</a>';
	}

	/**
	 * Creates a table of properties:
	 *
	 * @param	array		Array with properties for the current object path
	 * @param	string		Object path
	 * @param	array		Object tree
	 * @return	string		HTML content.
	 */
	function printTable($table,$objString,$objTree)	{
		if (is_array($table['rows']))	{

				// Initialize:
			$lines=array();

				// Adding header:
			$lines[]='
				<tr>
					<td><img src="clear.gif" width="175" height="1" alt="" /></td>
					<td><img src="clear.gif" width="100" height="1" alt="" /></td>
					<td><img src="clear.gif" width="400" height="1" alt="" /></td>
					<td><img src="clear.gif" width="70" height="1" alt="" /></td>
				</tr>';
			$lines[]='
				<tr class="bgColor5">
					<td><strong>Property:</strong></td>
					<td><strong>Data type:</strong></td>
					<td><strong>Description:</strong></td>
					<td><strong>Default:</strong></td>
				</tr>';

				// Traverse the content of "rows":
			foreach($table['rows'] as $row)	{

					// Linking:
				$lP=t3lib_div::trimExplode(chr(10),$row['property'],1);
				$lP2=array();
				while(list($k,$lStr)=each($lP))	{
					$lP2[$k] = $this->linkProperty($lStr,$lStr,$objString,$row['datatype']);
				}
				$linkedProperties=implode('<hr />',$lP2);

					// Data type:
				$dataType = $row['datatype'];

					// Generally "->[something]"
				$reg=array();
				ereg('->[[:alnum:]_]*',$dataType,$reg);
				if ($reg[0] && is_array($objTree[$reg[0]]))	{
					$dataType = str_replace($reg[0],'<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('show'=>$objTree[$reg[0]][0]['uid'],'objString'=>$objString.'.'.$lP[0]))).'">'.htmlspecialchars($reg[0]).'</a>',$dataType);
				}

					// stdWrap
				if (!strstr($dataType,'->stdWrap') && strstr(strip_tags($dataType),'stdWrap'))	{
						// Potential problem can be that "stdWrap" is substituted inside another A-tag. So maybe we should even check if there is already a <A>-tag present and if so, not make a substitution?
					$dataType = str_replace('stdWrap','<a href="'.htmlspecialchars(t3lib_div::linkThisScript(array('show'=>$objTree['->stdWrap'][0]['uid'],'objString'=>$objString.'.'.$lP[0]))).'">stdWrap</a>',$dataType);
				}


				$lines[]='
					<tr class="bgColor4">
						<td valign="top" class="bgColor4-20"><strong>'.$linkedProperties.'</strong></td>
						<td valign="top">'.nl2br($dataType.'&nbsp;').'</td>
						<td valign="top">'.nl2br($row['description']).'</td>
						<td valign="top">'.nl2br($row['default']).'</td>
					</tr>';
			}
				// Return it all:
			return '



			<!--
				TSconfig, attribute selector:
			-->
				<table border="0" cellpadding="0" cellspacing="1" width="500" id="typo3-attributes">
					'.implode('',$lines).'
				</table>';
		}
	}

	/**
	 * Creates a link on a property.
	 *
	 * @param	string		String to link
	 * @param	string		Property value.
	 * @param	string		Object path prefix to value
	 * @param	string		Data type
	 * @return	string		Linked $str
	 */
	function linkProperty($str,$propertyName,$prefix,$datatype)	{
		$out='';

			// Setting preset value:
		if (strstr($datatype,'boolean'))	{
			$propertyVal='1';	// preset "1" to boolean values.
		}

			// Adding mixer features; The plus icon:
		if(!$this->onlyProperty)	{
			$aOnClick = 'document.editform.mixer.value=unescape(\'  '.rawurlencode($propertyName.'='.$propertyVal).'\')+\'\n\'+document.editform.mixer.value; return false;';
			$out.= '<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/plusbullet2.gif','width="18" height="16"').' title="'.$GLOBALS['LANG']->getLL('tsprop_addToList',1).'" align="top" alt="" />'.
					'</a>';
			$propertyName = $prefix.'.'.$propertyName;
		}

			// Wrap string:
		$aOnClick = 'setValue(unescape(\''.rawurlencode($propertyName).'\'),unescape(\''.rawurlencode($propertyVal).'\')); return false;';
		$out.= '<a href="#" onclick="'.htmlspecialchars($aOnClick).'">'.$str.'</a>';

			// Return link:
		return $out;
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/wizard_tsconfig.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/wizard_tsconfig.php']);
}












// Make instance:
$SOBE = t3lib_div::makeInstance('SC_wizard_tsconfig');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>