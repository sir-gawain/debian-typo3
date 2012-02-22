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
 * Provides a simplified layer for making Constant Editor style configuration forms
 *
 * $Id: class.t3lib_tsstyleconfig.php 3439 2008-03-16 19:16:51Z flyguide $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   79: class t3lib_tsStyleConfig extends t3lib_tsparser_ext
 *   98:     function ext_initTSstyleConfig($configTemplate,$pathRel,$pathAbs,$backPath)
 *  118:     function ext_setValueArray($theConstants,$valueArray)
 *  145:     function ext_getCategoriesForModMenu()
 *  155:     function ext_makeHelpInformationForCategory($cat)
 *  168:     function ext_getForm($cat,$theConstants,$script="",$addFields="")
 *  175:     function uFormUrl(aname)
 *  197:     function ext_displayExample()
 *  213:     function ext_mergeIncomingWithExisting($arr)
 *  221:     function ext_getKeyImage($key)
 *  231:     function ext_getTSCE_config_image($imgConf)
 *  244:     function ext_fNandV($params)
 *  262:     function ext_loadResources($absPath)
 *  278:     function ext_putValueInConf($key, $var)
 *  288:     function ext_removeValueInConf($key)
 *
 * TOTAL FUNCTIONS: 14
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_t3lib.'class.t3lib_tsparser_ext.php');











/**
 * Provides a simplified layer for making Constant Editor style configuration forms
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_tsStyleConfig extends t3lib_tsparser_ext	{
		// internal
	var $categories = array();
	var $ext_dontCheckIssetValues=1;
	var $ext_CEformName="tsStyleConfigForm";
	var $ext_noCEUploadAndCopying=1;
	var $ext_printAll=1;
	var $ext_defaultOnlineResourceFlag=1;

	var $ext_incomingValues = array();


	/**
	 * @param	string		pathRel is the path relative to the typo3/ directory
	 * @param	string		pathAbs is the absolute path from root
	 * @param	string		backPath is the backReference from current position to typo3/ dir
	 * @param	[type]		$backPath: ...
	 * @return	[type]		...
	 */
	function ext_initTSstyleConfig($configTemplate,$pathRel,$pathAbs,$backPath)	{
		$this->tt_track = 0;	// Do not log time-performance information
		$this->constants=array($configTemplate,"");

		$theConstants = $this->generateConfig_constants();	// The editable constants are returned in an array.

		$this->ext_localGfxPrefix=$pathAbs;
		$this->ext_localWebGfxPrefix=$backPath.$pathRel;
		$this->ext_backPath = $backPath;

		return $theConstants;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$theConstants: ...
	 * @param	[type]		$valueArray: ...
	 * @return	[type]		...
	 */
	function ext_setValueArray($theConstants,$valueArray)	{

		$temp = $this->flatSetup;
		$this->flatSetup = Array();
		$this->flattenSetup($valueArray,"","");
		$this->objReg = $this->ext_realValues = $this->flatSetup;
		$this->flatSetup = $temp;


		reset($theConstants);
		while(list($k,$p)=each($theConstants))	{
			if (isset($this->objReg[$k]))	{
				$theConstants[$k]["value"] = $this->ext_realValues[$k];
			}
		}

		$this->categories=array(); // Reset the default pool of categories.
		$this->ext_categorizeEditableConstants($theConstants);	// The returned constants are sorted in categories, that goes into the $this->categories array

		return $theConstants;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function ext_getCategoriesForModMenu()	{
		return $this->ext_getCategoryLabelArray();
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$cat: ...
	 * @return	[type]		...
	 */
	function ext_makeHelpInformationForCategory($cat)	{
		return $this->ext_getTSCE_config($cat);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$cat: ...
	 * @param	[type]		$theConstants: ...
	 * @param	[type]		$script: ...
	 * @param	[type]		$addFields: ...
	 * @return	[type]		...
	 */
	function ext_getForm($cat,$theConstants,$script="",$addFields="")	{
		$this->ext_makeHelpInformationForCategory($cat);
		$printFields = trim($this->ext_printFields($theConstants,$cat));

		$content='';
		$content.='
		<script language="javascript" type="text/javascript">
			function uFormUrl(aname)	{
				document.'.$this->ext_CEformName.'.action = "'.t3lib_div::linkThisScript().'#"+aname;
			}
		</script>
		';
		$content.= '<form action="'.($script?$script:t3lib_div::linkThisScript()).'" name="'.$this->ext_CEformName.'" method="POST" enctype="'.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["form_enctype"].'">';
		$content.= $addFields;
#		$content.= '<input type="Submit" name="submit" value="Update"><BR>';
		$content.= $printFields;
		$content.= '<input type="Submit" name="submit" value="Update">';

		$example = $this->ext_displayExample();
		$content.= $example?'<HR>'.$example:"";

		return $content;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function ext_displayExample()	{
		global $SOBE,$tmpl;
		if ($this->helpConfig["imagetag"] || $this->helpConfig["description"] || $this->helpConfig["header"])	{
			$out = '<div align="center">'.$this->helpConfig["imagetag"].'</div><BR>'.
				($this->helpConfig["description"] ? implode(explode("//",$this->helpConfig["description"]),"<BR>")."<BR>" : "").
				($this->helpConfig["bulletlist"] ? "<ul><li>".implode(explode("//",$this->helpConfig["bulletlist"]),"<li>")."</ul>" : "<BR>");
		}
		return $out;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$arr: ...
	 * @return	[type]		...
	 */
	function ext_mergeIncomingWithExisting($arr)	{
		$parseObj = t3lib_div::makeInstance("t3lib_TSparser");
		$parseObj->parse(implode(chr(10),$this->ext_incomingValues));
		$arr2 = $parseObj->setup;
		return t3lib_div::array_merge_recursive_overrule($arr,$arr2);
	}

		// extends:
	function ext_getKeyImage($key)	{
		return '<img'.t3lib_iconWorks::skinImg($this->ext_backPath,'gfx/rednumbers/'.$key.'.gif','').' hspace="2" align="top" alt="" />';
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$imgConf: ...
	 * @return	[type]		...
	 */
	function ext_getTSCE_config_image($imgConf)	{
		$iFile=$this->ext_localGfxPrefix.$imgConf;
		$tFile=$this->ext_localWebGfxPrefix.$imgConf;
		$imageInfo=@getImagesize($iFile);
		return '<img src="'.$tFile.'" '.$imageInfo[3].'>';
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$params: ...
	 * @return	[type]		...
	 */
	function ext_fNandV($params)	{
		$fN='data['.$params["name"].']';
		$fV=$params["value"]=isset($this->ext_realValues[$params["name"]]) ? $this->ext_realValues[$params["name"]] : $params["default_value"];
		$reg = array();
		if (preg_match('/^\{[\$][a-zA-Z0-9\.]*\}$/',trim($fV),$reg))	{		// Values entered from the constantsedit cannot be constants!
			$fV="";
		}
		$fV=htmlspecialchars($fV);
#debug(array($params,$fN,$fV,isset($this->ext_realValues[$params["name"]])));
		return array($fN,$fV,$params);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$absPath: ...
	 * @return	[type]		...
	 */
	function ext_loadResources($absPath)	{
		$this->ext_readDirResources($GLOBALS["TYPO3_CONF_VARS"]["MODS"]["web_ts"]["onlineResourceDir"]);
		if (is_dir($absPath))	{
			$absPath = ereg_replace("\/$","",$absPath);
			$this->readDirectory($absPath);
		}
		$this->ext_resourceDims();
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$key: ...
	 * @param	[type]		$var: ...
	 * @return	[type]		...
	 */
	function ext_putValueInConf($key, $var)	{
		$this->ext_incomingValues[$key]=$key."=".$var;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$key: ...
	 * @return	[type]		...
	 */
	function ext_removeValueInConf($key)	{
		// Nothing...
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tsstyleconfig.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_tsstyleconfig.php']);
}
?>