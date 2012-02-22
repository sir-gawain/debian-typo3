<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Stanislas Rolland <stanislas.rolland(arobas)fructifor.ca>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * TYPO3 Color plugin for htmlArea RTE
 *
 * @author Stanislas Rolland <stanislas.rolland(arobas)fructifor.ca>
 *
 * TYPO3 SVN ID: $Id: class.tx_rtehtmlarea_typo3color.php 3288 2008-02-26 05:22:55Z stan $
 *
 */

require_once(t3lib_extMgm::extPath('rtehtmlarea').'class.tx_rtehtmlareaapi.php');

class tx_rtehtmlarea_typo3color extends tx_rtehtmlareaapi {

	protected $extensionKey = 'rtehtmlarea';	// The key of the extension that is extending htmlArea RTE
	protected $pluginName = 'TYPO3Color';	// The name of the plugin registered by the extension
	protected $relativePathToLocallangFile = 'extensions/TYPO3Color/locallang.xml';	// Path to this main locallang file of the extension relative to the extension dir.
	protected $relativePathToSkin = 'extensions/TYPO3Color/skin/htmlarea.css';		// Path to the skin (css) file relative to the extension dir.
	protected $htmlAreaRTE;				// Reference to the invoking object
	protected $thisConfig;				// Reference to RTE PageTSConfig
	protected $toolbar;				// Reference to RTE toolbar array
	protected $LOCAL_LANG; 				// Frontend language array
	
	protected $pluginButtons = 'textcolor,bgcolor';
	protected $convertToolbarForHtmlAreaArray = array (
		'textcolor'		=> 'ForeColor',
		'bgcolor'		=> 'HiliteColor',
		);
	
	public function main($parentObject) {
		return parent::main($parentObject) && (!$this->thisConfig['disableSelectColor'] || $this->htmlAreaRTE->client['BROWSER'] == 'gecko');
	}
	
	/**
	 * Return JS configuration of the htmlArea plugins registered by the extension
	 *
	 * @param	integer		Relative id of the RTE editing area in the form
	 *
	 * @return string		JS configuration for registered plugins
	 *
	 * The returned string will be a set of JS instructions defining the configuration that will be provided to the plugin(s)
	 * Each of the instructions should be of the form:
	 * 	RTEarea['.$RTEcounter.']["buttons"]["button-id"]["property"] = "value";
	 */
	public function buildJavascriptConfiguration($RTEcounter) {
		
			// Process colors configuration
		$registerRTEinJavascriptString = $this->buildJSColorsConfig($RTEcounter);
		
		return $registerRTEinJavascriptString;
	}
	
	/**
	 * Return Javascript configuration of colors
	 *
	 * @param	integer		$RTEcounter: The index number of the current RTE editing area within the form.
	 *
	 * @return	string		Javascript configuration of colors
	 */
	function buildJSColorsConfig($RTEcounter) {
		
		if ($this->htmlAreaRTE->is_FE()) {
			$RTEProperties = $this->htmlAreaRTE->RTEsetup;
		} else {
			$RTEProperties = $this->htmlAreaRTE->RTEsetup['properties'];
		}
		
		$configureRTEInJavascriptString = '';
		
		$configureRTEInJavascriptString .= '
			RTEarea['.$RTEcounter.'].disableColorPicker = ' . (trim($this->thisConfig['disableColorPicker']) ? 'true' : 'false') . ';';
		
			// Building JS array of configured colors
		if (is_array($RTEProperties['colors.']) )  {
			$HTMLAreaColorname = array();
			foreach ($RTEProperties['colors.'] as $colorName => $conf) {
				$colorName=substr($colorName,0,-1);
				$colorLabel = $this->htmlAreaRTE->getPageConfigLabel($conf['name']);
				$HTMLAreaColorname[$colorName] = '
				[' . $colorLabel . ' , "' . $conf['value'] . '"]';
			}
		}
		
			// Setting the list of colors if specified in the RTE config
		if ($this->thisConfig['colors'] ) {
			$HTMLAreaJSColors = '[';
			$HTMLAreaColors = t3lib_div::trimExplode(',' , $this->htmlAreaRTE->cleanList($this->thisConfig['colors']));
			$HTMLAreaColorsIndex = 0;
			foreach ($HTMLAreaColors as $colorName) {
				if($HTMLAreaColorsIndex && $HTMLAreaColorname[$colorName]) { 
					$HTMLAreaJSColors .= ',';
				}
				$HTMLAreaJSColors .= $HTMLAreaColorname[$colorName];
				$HTMLAreaColorsIndex++;
			}
			$HTMLAreaJSColors .= '];';
			$configureRTEInJavascriptString .= '
			RTEarea['.$RTEcounter.'].colors = '. $HTMLAreaJSColors;
		}
		
		return $configureRTEInJavascriptString;
	}

} // end of class

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rtehtmlarea/extensions/TYPO3Color/class.tx_rtehtmlarea_typo3color.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rtehtmlarea/extensions/TYPO3Color/class.tx_rtehtmlarea_typo3color.php']);
}

?>