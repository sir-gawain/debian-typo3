<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005-2009 Stanislas Rolland <typo3(arobas)sjbr.ca>
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
 * Configuration of the htmlArea RTE extension
 *
 * @author	Stanislas Rolland <typo3(arobas)sjbr.ca>
 *
 * $Id: ext_localconf.php 6346 2009-11-06 03:25:02Z stan $  *
 */

if (!defined ("TYPO3_MODE")) 	die ('Access denied.');

if(!$TYPO3_CONF_VARS['BE']['RTEenabled'])  $TYPO3_CONF_VARS['BE']['RTEenabled'] = 1;

	// Registering the RTE object
$TYPO3_CONF_VARS['BE']['RTE_reg'][$_EXTKEY] = array('objRef' => 'EXT:'.$_EXTKEY.'/class.tx_rtehtmlarea_base.php:&tx_rtehtmlarea_base');

	// Make the extension version number available to the extension scripts
require_once(t3lib_extMgm::extPath($_EXTKEY) . 'ext_emconf.php');
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['version'] = $EM_CONF[$_EXTKEY]['version'];

	// Unserializing the configuration so we can use it here
$_EXTCONF = unserialize($_EXTCONF);

	// Add default RTE transformation configuration
t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/res/proc/pageTSConfig.txt">');

	// Add default Page TSonfig RTE configuration
if (strstr($_EXTCONF['defaultConfiguration'],'Minimal')) {
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['defaultConfiguration'] = 'Advanced';
} elseif (strstr($_EXTCONF['defaultConfiguration'],'Demo')) {
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['defaultConfiguration'] = 'Demo';
} else {
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['defaultConfiguration'] = 'Typical';
}
t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/res/' . strtolower($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['defaultConfiguration']) . '/pageTSConfig.txt">');

	// Add default User TSonfig RTE configuration
t3lib_extMgm::addUserTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/res/' . strtolower($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['defaultConfiguration']) . '/userTSConfig.txt">');

	// Add Clear RTE Cache to Clear Cache menu
require_once(t3lib_extMgm::extPath('rtehtmlarea').'hooks/clearrtecache/ext_localconf.php');

	// Troubleshooting and experimentation
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableDebugMode'] = $_EXTCONF['enableDebugMode'] ? $_EXTCONF['enableDebugMode'] : 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableCompressedScripts'] = $_EXTCONF['enableCompressedScripts'] ? $_EXTCONF['enableCompressedScripts'] : 0;

	// Integrating with DAM
	// DAM browser may be enabled here only for DAM version lower than 1.1
	// If DAM 1.1+ is installed, the setting must be unset, DAM own EM setting should be used
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableDAMBrowser'] = 0;
if (t3lib_extMgm::isLoaded('dam')) {
	$saveExtKey = $_EXTKEY;
	$_EXTKEY = 'dam';
	require(t3lib_extMgm::extPath('dam') . 'ext_emconf.php');
	$_EXTKEY = $saveExtKey;
	if (t3lib_div::int_from_ver($EM_CONF['dam']['version']) < 1001000) {
			// Register DAM element browser rendering
		$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableDAMBrowser'] = $_EXTCONF['enableDAMBrowser'] ? $_EXTCONF['enableDAMBrowser'] : 0;
		if ($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableDAMBrowser']) {
			$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/browse_links.php']['browserRendering'][] = 'EXT:'.$_EXTKEY.'/mod4/class.tx_rtehtmlarea_dam_browse_media.php:&tx_rtehtmlarea_dam_browse_media';
			$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/browse_links.php']['browserRendering'][] = 'EXT:'.$_EXTKEY.'/mod3/class.tx_rtehtmlarea_dam_browse_links.php:&tx_rtehtmlarea_dam_browse_links';
		}
	}
}

	// Configure Lorem Ipsum hook to insert nonsense in wysiwyg mode
if (t3lib_extMgm::isLoaded('lorem_ipsum') && (TYPO3_MODE == 'BE')) {
    $TYPO3_CONF_VARS['EXTCONF']['lorem_ipsum']['RTE_insert'][] = 'tx_rtehtmlarea_base->loremIpsumInsert';
}

	// Initialize plugin registration array
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins'] = array();
	// Status Bar configuration
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['StatusBar'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['StatusBar']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/StatusBar/class.tx_rtehtmlarea_statusbar.php:&tx_rtehtmlarea_statusbar';
	// Editor Mode configuration
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['EditorMode'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['EditorMode']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/EditorMode/class.tx_rtehtmlarea_editormode.php:&tx_rtehtmlarea_editormode';
	// Inline Elements configuration
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultInline'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultInline']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/DefaultInline/class.tx_rtehtmlarea_defaultinline.php:&tx_rtehtmlarea_defaultinline';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultInline']['addIconsToSkin'] = 1;
if ($_EXTCONF['enableInlineElements']) {
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['InlineElements'] = array();
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['InlineElements']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/InlineElements/class.tx_rtehtmlarea_inlineelements.php:&tx_rtehtmlarea_inlineelements';
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/extensions/InlineElements/res/pageTSConfig.txt">');
}
	// Block Elements configuration
	// Set compatibility warnings in the Update Wizard of the Install Tool for indentation and alignment
$TYPO3_CONF_VARS['SC_OPTIONS']['ext/install']['compat_version']['tx_rtehtmlarea_indent'] = array(
	'title' => 'htmlArea RTE: Using CSS classes for indentation and alignment',
	'version' => 4002000,
	'description' => '<ul>
				<li><b>Indentation is produced by a CSS class instead of the blockquote element.</b><br />You will need to specify in Page TSConfig the class to be used for indentation using property buttons.indent.useClass (default is "indent"). You will need to define this class in your stylesheets and ensure that it is allowed by the RTE transformation (RTE.default.proc). Alternatively, you may continue using the blockquote element by setting property buttons.indent.useBlockquote. You may also want to add the new blockquote button to the RTE toolbar.</li>
				<li><b>Text alignment is produced by CSS classes instead of deprecated align attribute.</b><br />You will need to specify in Page TSConfig the class to be used for each text alignment button using property buttons.[<i>left, center, right or justifyfull</i>].useClass (defaults are "align-left", "align-center", "align-right", "align-justify"). You will need to define these classes in your stylesheets, and ensure that they are allowed by the RTE transformation (RTE.default.proc). Alternatively, you may continue using deprecated align attribute by setting property buttons.[<i>left, center, right or justifyfull</i>].useAlignAttribute.</li>
			</ul>'
);
	// Add compatibility Page TSConfig for indentation and alignment
if (!t3lib_div::compat_version('4.2.0')) {
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/res/indentalign/pageTSConfig.txt">');
}
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['BlockElements'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['BlockElements']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/BlockElements/class.tx_rtehtmlarea_blockelements.php:&tx_rtehtmlarea_blockelements';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['BlockElements']['addIconsToSkin'] = 0;
	// Set compatibility warning in the Update Wizard of the Install Tool for definition lists
if (t3lib_extMgm::isLoaded('rtehtmlarea_definitionlist')) {
	$TYPO3_CONF_VARS['SC_OPTIONS']['ext/install']['compat_version']['tx_rtehtmlarea_definitionlist'] = array(
		'title' => 'htmlArea RTE: Integration of Definition List feature',
		'version' => 4003000,
		'description' => 'Support for definition lists has been integrated into htmlArea RTE.<br />You should uninstall extension "Definition Lists for htmlArea RTE" (key: rtehtmlarea_definitionlist)'
	);
}
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefinitionList'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefinitionList']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/DefinitionList/class.tx_rtehtmlarea_definitionlist.php:&tx_rtehtmlarea_definitionlist';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefinitionList']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['BlockStyle'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['BlockStyle']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/BlockStyle/class.tx_rtehtmlarea_blockstyle.php:&tx_rtehtmlarea_blockstyle';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['CharacterMap'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['CharacterMap']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/CharacterMap/class.tx_rtehtmlarea_charactermap.php:&tx_rtehtmlarea_charactermap';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['CharacterMap']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['Acronym'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['Acronym']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/Acronym/class.tx_rtehtmlarea_acronym.php:&tx_rtehtmlarea_acronym';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['Acronym']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['Acronym']['disableInFE'] = 1;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['UserElements'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['UserElements']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/UserElements/class.tx_rtehtmlarea_userelements.php:&tx_rtehtmlarea_userelements';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['UserElements']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['UserElements']['disableInFE'] = 1;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TextStyle'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TextStyle']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/TextStyle/class.tx_rtehtmlarea_textstyle.php:&tx_rtehtmlarea_textstyle';

	// Enable images and add default Page TSonfig RTE configuration for enabling images with the Minimal and Typical default configuration
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableImages'] = $_EXTCONF['enableImages'] ? $_EXTCONF['enableImages'] : 0;
if ($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['defaultConfiguration'] == 'Demo') {
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableImages'] = 1;
}
if ($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableImages']) {
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultImage'] = array();
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultImage']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/DefaultImage/class.tx_rtehtmlarea_defaultimage.php:&tx_rtehtmlarea_defaultimage';
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultImage']['addIconsToSkin'] = 0;
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Image'] = array();
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Image']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/TYPO3Image/class.tx_rtehtmlarea_typo3image.php:&tx_rtehtmlarea_typo3image';
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Image']['addIconsToSkin'] = 0;
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Image']['disableInFE'] = 1;
	if ($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['defaultConfiguration'] == 'Advanced' || $TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['defaultConfiguration'] == 'Typical') {
		t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/res/image/pageTSConfig.txt">');
	}
}
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultLink'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultLink']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/DefaultLink/class.tx_rtehtmlarea_defaultlink.php:&tx_rtehtmlarea_defaultlink';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultLink']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Link'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Link']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/TYPO3Link/class.tx_rtehtmlarea_typo3link.php:&tx_rtehtmlarea_typo3link';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Link']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Link']['disableInFE'] = 1;
	// Add default Page TSonfig RTE configuration for enabling links accessibility icons
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableAccessibilityIcons'] = $_EXTCONF['enableAccessibilityIcons'] ? $_EXTCONF['enableAccessibilityIcons'] : 0;
if ($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['enableAccessibilityIcons']) {
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/res/accessibilityicons/pageTSConfig.txt">');
}
	// Register features that use the style attribute
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['allowStyleAttribute'] = $_EXTCONF['allowStyleAttribute'] ? $_EXTCONF['allowStyleAttribute'] : 0;
if ($TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['allowStyleAttribute']) {
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultColor'] = array();
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultColor']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/DefaultColor/class.tx_rtehtmlarea_defaultcolor.php:&tx_rtehtmlarea_defaultcolor';
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultColor']['addIconsToSkin'] = 0;
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultColor']['disableInFE'] = 0;
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Color'] = array();
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Color']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/TYPO3Color/class.tx_rtehtmlarea_typo3color.php:&tx_rtehtmlarea_typo3color';
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Color']['addIconsToSkin'] = 0;
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3Color']['disableInFE'] = 0;
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SelectFont'] = array();
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SelectFont']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/SelectFont/class.tx_rtehtmlarea_selectfont.php:&tx_rtehtmlarea_selectfont';
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SelectFont']['addIconsToSkin'] = 0;
	$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SelectFont']['disableInFE'] = 0;
	t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/res/style/pageTSConfig.txt">');
}
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['InsertSmiley'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['InsertSmiley']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/InsertSmiley/class.tx_rtehtmlarea_insertsmiley.php:&tx_rtehtmlarea_insertsmiley';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['InsertSmiley']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['InsertSmiley']['disableInFE'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['Language'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['Language']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/Language/class.tx_rtehtmlarea_language.php:&tx_rtehtmlarea_language';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['Language']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['Language']['disableInFE'] = 0;

	// Spell checking configuration
$TYPO3_CONF_VARS['FE']['eID_include']['rtehtmlarea_spellchecker'] = 'EXT:'.$_EXTKEY.'/pi1/class.tx_rtehtmlarea_pi1.php';
$TYPO3_CONF_VARS['BE']['AJAX']['rtehtmlarea::spellchecker'] = 'EXT:'.$_EXTKEY.'/pi1/class.tx_rtehtmlarea_pi1.php:tx_rtehtmlarea_pi1->main';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/SpellChecker/class.tx_rtehtmlarea_spellchecker.php:&tx_rtehtmlarea_spellchecker';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker']['disableInFE'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker']['AspellDirectory'] = $_EXTCONF['AspellDirectory'] ? $_EXTCONF['AspellDirectory'] : '/usr/bin/aspell';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker']['noSpellCheckLanguages'] = $_EXTCONF['noSpellCheckLanguages'] ? $_EXTCONF['noSpellCheckLanguages'] : 'ja,km,ko,lo,th,zh,b5,gb';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker']['forceCommandMode'] = $_EXTCONF['forceCommandMode'] ? $_EXTCONF['forceCommandMode'] : 0;
	// The following two properties DEPRECATED as of TYPO3 4.3.0
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker']['dictionaryList'] = $_EXTCONF['dictionaryList'] ? $_EXTCONF['dictionaryList'] : 'en';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['SpellChecker']['defaultDictionary'] = $_EXTCONF['defaultDictionary'] ? $_EXTCONF['defaultDictionary'] : 'en';

$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['FindReplace'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['FindReplace']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/FindReplace/class.tx_rtehtmlarea_findreplace.php:&tx_rtehtmlarea_findreplace';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['FindReplace']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['FindReplace']['disableInFE'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['RemoveFormat'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['RemoveFormat']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/RemoveFormat/class.tx_rtehtmlarea_removeformat.php:&tx_rtehtmlarea_removeformat';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['RemoveFormat']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['RemoveFormat']['disableInFE'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultClean'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['DefaultClean']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/DefaultClean/class.tx_rtehtmlarea_defaultclean.php:&tx_rtehtmlarea_defaultclean';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3HtmlParser'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3HtmlParser']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/TYPO3HtmlParser/class.tx_rtehtmlarea_typo3htmlparser.php:&tx_rtehtmlarea_typo3htmlparser';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TYPO3HtmlParser']['disableInFE'] = 1;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['QuickTag'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['QuickTag']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/QuickTag/class.tx_rtehtmlarea_quicktag.php:&tx_rtehtmlarea_quicktag';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['QuickTag']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['QuickTag']['disableInFE'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TableOperations'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TableOperations']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/TableOperations/class.tx_rtehtmlarea_tableoperations.php:&tx_rtehtmlarea_tableoperations';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TableOperations']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['TableOperations']['disableInFE'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['AboutEditor'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['AboutEditor']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/AboutEditor/class.tx_rtehtmlarea_abouteditor.php:&tx_rtehtmlarea_abouteditor';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['AboutEditor']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['AboutEditor']['disableInFE'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['ContextMenu'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['ContextMenu']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/ContextMenu/class.tx_rtehtmlarea_contextmenu.php:&tx_rtehtmlarea_contextmenu';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['ContextMenu']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['ContextMenu']['disableInFE'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['UndoRedo'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['UndoRedo']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/UndoRedo/class.tx_rtehtmlarea_undoredo.php:&tx_rtehtmlarea_undoredo';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['UndoRedo']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['UndoRedo']['disableInFE'] = 0;

	// Copy & Paste configuration
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['CopyPaste'] = array();
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['CopyPaste']['objectReference'] = 'EXT:'.$_EXTKEY.'/extensions/CopyPaste/class.tx_rtehtmlarea_copypaste.php:&tx_rtehtmlarea_copypaste';
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['CopyPaste']['addIconsToSkin'] = 0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['CopyPaste']['disableInFE'] =  0;
$TYPO3_CONF_VARS['EXTCONF'][$_EXTKEY]['plugins']['CopyPaste']['mozillaAllowClipboardURL'] = 'https://addons.mozilla.org/firefox/downloads/latest/852/addon-852-latest.xpi';

?>