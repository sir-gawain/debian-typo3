<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Steffen Kamper (info@sk-typo3.de)
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
 * TYPO3 pageRender class (new in TYPO3 4.3.0)
 * This class render the HTML of a webpage, usable for BE and FE
 *
 * @author	Steffen Kamper <info@sk-typo3.de>
 * @package TYPO3
 * @subpackage t3lib
 * $Id: class.t3lib_pagerenderer.php 9029 2010-10-10 15:47:02Z steffenk $
 */
class t3lib_PageRenderer implements t3lib_Singleton {

	protected $compressJavascript = FALSE;
	protected $compressCss = FALSE;
	protected $removeLineBreaksFromTemplate = FALSE;

	protected $concatenateFiles = FALSE;

	protected $moveJsFromHeaderToFooter = FALSE;

	/* @var t3lib_cs Instance of t3lib_cs */
	protected $csConvObj;
	protected $lang;

	// static array containing associative array for the included files
	protected static $jsFiles = array ();
	protected static $jsFooterFiles = array ();
	protected static $jsLibs = array ();
	protected static $jsFooterLibs = array ();
	protected static $cssFiles = array ();

	protected $title;
	protected $charSet;
	protected $favIcon;
	protected $baseUrl;

	// static header blocks
	protected $xmlPrologAndDocType = '';
	protected $metaTags = array ();
	protected $inlineComments = array ();
	protected $headerData = array ();
	protected $footerData = array ();
	protected $titleTag = '<title>|</title>';
	protected $metaCharsetTag = '<meta http-equiv="Content-Type" content="text/html; charset=|" />';
	protected $htmlTag = '<html>';
	protected $headTag = '<head>';
	protected $baseUrlTag = '<base href="|" />';
	protected $iconMimeType = '';
	protected $shortcutTag = '<link rel="shortcut icon" href="%1$s"%2$s />
<link rel="icon" href="%1$s"%2$s />';

	// static inline code blocks
	protected $jsInline = array ();
	protected $jsFooterInline = array ();
	protected $extOnReadyCode = array ();
	protected $cssInline = array ();

	protected $bodyContent;

	protected $templateFile;

	protected $jsLibraryNames = array ('prototype', 'scriptaculous', 'extjs');

	const PART_COMPLETE = 0;
	const PART_HEADER = 1;
	const PART_FOOTER = 2;

	// internal flags for JS-libraries
	protected $addPrototype = FALSE;
	protected $addScriptaculous = FALSE;
	protected $addScriptaculousModules = array ('builder' => FALSE, 'effects' => FALSE, 'dragdrop' => FALSE, 'controls' => FALSE, 'slider' => FALSE);
	protected $addExtJS = FALSE;
	protected $addExtCore = FALSE;
	protected $extJSadapter = 'ext/ext-base.js';

	protected $enableExtJsDebug = FALSE;
	protected $enableExtCoreDebug = FALSE;

	// available adapters for extJs
	const EXTJS_ADAPTER_JQUERY = 'jquery';
	const EXTJS_ADAPTER_PROTOTYPE = 'prototype';
	const EXTJS_ADAPTER_YUI = 'yui';

	protected $extJStheme = TRUE;
	protected $extJScss = TRUE;

	protected $enableExtJSQuickTips = false;

	protected $inlineLanguageLabels = array ();
	protected $inlineSettings = array ();

	protected $inlineJavascriptWrap = array ();

	// saves error messages generated during compression
	protected $compressError = '';

	// used by BE modules
	public $backPath;

	/**
	 * Constructor
	 *
	 * @param string $templateFile	declare the used template file. Omit this parameter will use default template
	 * @param string $backPath	relative path to typo3-folder. It varies for BE modules, in FE it will be typo3/
	 * @return void
	 */
	public function __construct($templateFile = '', $backPath = NULL) {

		$this->reset();
		$this->csConvObj = t3lib_div::makeInstance('t3lib_cs');

		if (strlen($templateFile)) {
			$this->templateFile = $templateFile;
		}
		$this->backPath = isset($backPath) ? $backPath : $GLOBALS['BACK_PATH'];

		$this->inlineJavascriptWrap = array(
			'<script type="text/javascript">' . chr(10) . '/*<![CDATA[*/' . chr(10) . '<!-- ' . chr(10),
			'// -->' . chr(10) . '/*]]>*/' . chr(10) . '</script>' . chr(10)
		);
		$this->inlineCssWrap = array(
			'<style type="text/css">' . chr(10) . '/*<![CDATA[*/' . chr(10) . '<!-- ' . chr(10),
			'-->' . chr(10) . '/*]]>*/' . chr(10) . '</style>' . chr(10)
		);

	}

	/**
	 * reset all vars to initial values
	 *
	 * @return void
	 */
	protected function reset() {
		$this->templateFile = TYPO3_mainDir . 'templates/template_page_backend.html';
		$this->jsFiles = array ();
		$this->jsFooterFiles = array ();
		$this->jsInline = array ();
		$this->jsFooterInline = array ();
		$this->jsLibs = array ();
		$this->cssFiles = array ();
		$this->cssInline = array ();
		$this->metaTags = array ();
		$this->inlineComments = array ();
		$this->headerData = array ();
		$this->footerData = array ();
		$this->extOnReadyCode = array ();
	}
	/*****************************************************/
	/*                                                   */
	/*  Public Setters                                   */
	/*                                                   */
	/*                                                   */
	/*****************************************************/

	/**
	 * Sets the title
	 *
	 * @param string $title	title of webpage
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Sets xml prolog and docType
	 *
	 * @param string $xmlPrologAndDocType	complete tags for xml prolog and docType
	 * @return void
	 */
	public function setXmlPrologAndDocType($xmlPrologAndDocType) {
		$this->xmlPrologAndDocType = $xmlPrologAndDocType;
	}

	/**
	 * Sets meta charset
	 *
	 * @param string $charSet	used charset
	 * @return void
	 */
	public function setCharSet($charSet) {
		$this->charSet = $charSet;
	}

	/**
	 * Sets language
	 *
	 * @param string $lang	used language
	 * @return void
	 */
	public function setLanguage($lang) {
		$this->lang = $lang;
	}

	/**
	 * Sets html tag
	 *
	 * @param string $htmlTag	html tag
	 * @return void
	 */
	public function setHtmlTag($htmlTag) {
		$this->htmlTag = $htmlTag;
	}

	/**
	 * Sets head tag
	 *
	 * @param string $tag	head tag
	 * @return void
	 */
	public function setHeadTag($headTag) {
		$this->headTag = $headTag;
	}

	/**
	 * Sets favicon
	 *
	 * @param string $favIcon
	 * @return void
	 */
	public function setFavIcon($favIcon) {
		$this->favIcon = $favIcon;
	}

	/**
	 * Sets icon mime type
	 *
	 * @param string $iconMimeType
	 * @return void
	 */
	public function setIconMimeType($iconMimeType) {
		$this->iconMimeType = $iconMimeType;
	}

	/**
	 * Sets base url
	 *
	 * @param string $url
	 * @return void
	 */
	public function setBaseUrl($baseUrl) {
		$this->baseUrl = $baseUrl;
	}

	/**
	 * Sets template file
	 *
	 * @param string $file
	 * @return void
	 */
	public function setTemplateFile($file) {
		$this->templateFile = $file;
	}

	/**
	 * Sets back path
	 *
	 * @param string $backPath
	 * @return void
	 */
	public function setBackPath($backPath) {
		$this->backPath = $backPath;
	}

	/**
	 * Sets Content for Body
	 *
	 * @param string $content
	 * @return void
	 */
	public function setBodyContent($content) {
		$this->bodyContent = $content;
	}

	/*****************************************************/
	/*                                                   */
	/*  Public Enablers                                  */
	/*                                                   */
	/*                                                   */
	/*****************************************************/
	/**
	 * Enables MoveJsFromHeaderToFooter
	 *
	 * @param void
	 * @return void
	 */
	public function enableMoveJsFromHeaderToFooter() {
		$this->moveJsFromHeaderToFooter = TRUE;
	}

	/**
	 * Enables compression of javascript
	 *
	 * @param void
	 * @return void
	 */
	public function enableCompressJavascript() {
		$this->compressJavascript = TRUE;
	}

	/**
	 * Enables compression of css
	 *
	 * @param void
	 * @return void
	 */
	public function enableCompressCss() {
		$this->compressCss = TRUE;
	}

	/**
	/**
	 * Enables concatenation of js/css files
	 *
	 * @param void
	 * @return void
	 */
	public function enableConcatenateFiles() {
		$this->concatenateFiles = TRUE;
	}

	/**
	 * Sets removal of all line breaks in template
	 *
	 * @param void
	 * @return void
	 */
	public function enableRemoveLineBreaksFromTemplate() {
		$this->removeLineBreaksFromTemplate = TRUE;
	}

	/*****************************************************/
	/*                                                   */
	/*  Public Getters                                   */
	/*                                                   */
	/*                                                   */
	/*****************************************************/

	/**
	 * Gets the title
	 *
	 * @return string $title		title of webpage
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Gets the charSet
	 *
	 * @return string $charSet
	 */
	public function getCharSet() {
		return $this->charSet;
	}

	/**
	 * Gets the language
	 *
	 * @return string $lang
	 */
	public function getLanguage() {
		return $this->lang;
	}

	/**
	 * Gets html tag
	 *
	 * @return string $htmlTag	html tag
	 */
	public function getHtmlTag() {
		return $this->htmlTag;
	}

	/**
	 * Gets head tag
	 *
	 * @return string $tag	head tag
	 */
	public function getHeadTag() {
		return $this->headTag;
	}

	/**
	 * Gets favicon
	 *
	 * @return string $favIcon
	 */
	public function getFavIcon() {
		return $this->favIcon;
	}

	/**
	 * Gets icon mime type
	 *
	 * @return string $iconMimeType
	 */
	public function getIconMimeType() {
		return $this->iconMimeType;
	}

	/**
	 * Gets base url
	 *
	 * @return string $url
	 */
	public function getBaseUrl() {
		return $this->baseUrl;
	}

	/**
	 * Gets template file
	 *
	 * @return string $file
	 */
	public function getTemplateFile($file) {
		return $this->templateFile;
	}

	/**
	 * Gets MoveJsFromHeaderToFooter
	 *
	 * @return boolean
	 */
	public function getMoveJsFromHeaderToFooter() {
		return $this->moveJsFromHeaderToFooter;
	}

	/**
	 * Gets compress of javascript
	 *
	 * @return boolean
	 */
	public function getCompressJavascript() {
		return $this->compressJavascript;
	}

	/**
	 * Gets compress of css
	 *
	 * @return boolean
	 */
	public function getCompressCss() {
		return $this->compressCss;
	}

	/**
	 * Gets concatenate of files
	 *
	 * @return boolean
	 */
	public function getConcatenateFiles() {
		return $this->concatenateFiles;
	}

	/**
	 * Gets remove of empty lines from template
	 *
	 * @return boolean
	 */
	public function getRemoveLineBreaksFromTemplate() {
		return $this->removeLineBreaksFromTemplate;
	}

	/**
	 * Gets content for body
	 *
	 * @return string
	 */
	public function getBodyContent() {
		return $this->bodyContent;
	}

	/*****************************************************/
	/*                                                   */
	/*  Public Function to add Data                      */
	/*                                                   */
	/*                                                   */
	/*****************************************************/

	/**
	 * Adds meta data
	 *
	 * @param string $meta	meta data (complete metatag)
	 * @return void
	 */
	public function addMetaTag($meta) {
		if (!in_array($meta, $this->metaTags)) {
			$this->metaTags[] = $meta;
		}
	}

	/**
	 * Adds inline HTML comment
	 *
	 * @param string $comment
	 * @return void
	 */
	public function addInlineComment($comment) {
		if (!in_array($comment, $this->inlineComments)) {
			$this->inlineComments[] = $comment;
		}
	}

	/**
	 * Adds header data
	 *
	 * @param string $data 	free header data for HTML header
	 * @return void
	 */
	public function addHeaderData($data) {
		if (!in_array($data, $this->headerData)) {
			$this->headerData[] = $data;
		}
	}

	/**
	 * Adds footer data
	 *
	 * @param string $data 	free header data for HTML header
	 * @return void
	 */
	public function addFooterData($data) {
		if (!in_array($data, $this->footerData)) {
			$this->footerData[] = $data;
		}
	}

	/* Javascript Files */

	/**
	 * Adds JS Library. JS Library block is rendered on top of the JS files.
	 *
	 * @param string $name
	 * @param string $file
	 * @param string $type
	 * @param boolean $compress		flag if library should be compressed
	 * @param boolean $forceOnTop	flag if added library should be inserted at begin of this block
	 * @param string $allWrap
	 * @return void
	 */
	public function addJsLibrary($name, $file, $type = 'text/javascript', $compress = FALSE, $forceOnTop = FALSE, $allWrap = '') {
		if (!in_array(strtolower($name), $this->jsLibs)) {
			$this->jsLibs[strtolower($name)] = array (
				'file'        => $file,
				'type'        => $type,
				'section'     => self::PART_HEADER,
				'compress'    => $compress,
				'forceOnTop'  => $forceOnTop,
				'allWrap'     => $allWrap
			);
		}

	}

	/**
	 * Adds JS Library to Footer. JS Library block is rendered on top of the Footer JS files.
	 *
	 * @param string $name
	 * @param string $file
	 * @param string $type
	 * @param boolean $compress	flag if library should be compressed
	 * @param boolean $forceOnTop	flag if added library should be inserted at begin of this block
	 * @param string $allWrap
	 * @return void
	 */
	public function addJsFooterLibrary($name, $file, $type = 'text/javascript', $compress = FALSE, $forceOnTop = FALSE, $allWrap = '') {
		if (!in_array(strtolower($name), $this->jsLibs)) {
			$this->jsLibs[strtolower($name)] = array (
				'file'        => $file,
				'type'        => $type,
				'section'     => self::PART_FOOTER,
				'compress'    => $compress,
				'forceOnTop'  => $forceOnTop,
				'allWrap'     => $allWrap
			);
		}

	}

	/**
	 * Adds JS file
	 *
	 * @param string $file
	 * @param string $type
	 * @param boolean $compress
	 * @param boolean $forceOnTop
	 * @param string $allWrap
	 * @return void
	 */
	public function addJsFile($file, $type = 'text/javascript', $compress = TRUE, $forceOnTop = FALSE, $allWrap = '') {
		if (!isset($this->jsFiles[$file])) {
			$this->jsFiles[$file] = array (
				'type'        => $type,
				'section'     => self::PART_HEADER,
				'compress'    => $compress,
				'forceOnTop'  => $forceOnTop,
				'allWrap'     => $allWrap
			);
		}
	}

	/**
	 * Adds JS file to footer
	 *
	 * @param string $file
	 * @param string $type
	 * @param boolean $compress
	 * @param boolean $forceOnTop
	 * @return void
	 */
	public function addJsFooterFile($file, $type = 'text/javascript', $compress = TRUE, $forceOnTop = FALSE, $allWrap = '') {
		if (!isset($this->jsFiles[$file])) {
			$this->jsFiles[$file] = array (
				'type'        => $type,
				'section'     => self::PART_FOOTER,
				'compress'    => $compress,
				'forceOnTop'  => $forceOnTop,
				'allWrap'     => $allWrap
			);
		}
	}

	/*Javascript Inline Blocks */

	/**
	 * Adds JS inline code
	 *
	 * @param string $name
	 * @param string $block
	 * @param boolean $compress
	 * @param boolean $forceOnTop
	 * @return void
	 */
	public function addJsInlineCode($name, $block, $compress = TRUE, $forceOnTop = FALSE) {
		if (!isset($this->jsInline[$name]) && !empty($block)) {
			$this->jsInline[$name] = array (
				'code'        => $block . chr(10),
				'section'     => self::PART_HEADER,
				'compress'    => $compress,
				'forceOnTop'  => $forceOnTop
			);
		}
	}

	/**
	 * Adds JS inline code to footer
	 *
	 * @param string $name
	 * @param string $block
	 * @param boolean $compress
	 * @param boolean $forceOnTop
	 * @return void
	 */
	public function addJsFooterInlineCode($name, $block, $compress = TRUE, $forceOnTop = FALSE) {
		if (!isset($this->jsInline[$name]) && !empty($block)) {
			$this->jsInline[$name] = array (
				'code'        => $block . chr(10),
				'section'     => self::PART_FOOTER,
				'compress'    => $compress,
				'forceOnTop'  => $forceOnTop
			);
		}
	}

	/**
	 * Adds Ext.onready code, which will be wrapped in Ext.onReady(function() {...});
	 *
	 * @param string $block
	 * @return void
	 */
	public function addExtOnReadyCode($block) {
		if (!in_array($block, $this->extOnReadyCode)) {
			$this->extOnReadyCode[] = $block;
		}
	}

	/* CSS Files */

	/**
	 * Adds CSS file
	 *
	 * @param string $file
	 * @param string $rel
	 * @param string $media
	 * @param string $title
	 * @param boolean $compress
	 * @param boolean $forceOnTop
	 * @return void
	 */
	public function addCssFile($file, $rel = 'stylesheet', $media = 'all', $title = '', $compress = TRUE, $forceOnTop = FALSE, $allWrap = '') {
		if (!isset($this->cssFiles[$file])) {
			$this->cssFiles[$file] = array (
				'rel'        => $rel,
				'media'      => $media,
				'title'      => $title,
				'compress'   => $compress,
				'forceOnTop' => $forceOnTop,
				'allWrap'    => $allWrap
			);
		}
	}

	/*CSS Inline Blocks */

	/**
	 * Adds CSS inline code
	 *
	 * @param string $name
	 * @param string $block
	 * @param boolean $compress
	 * @param boolean $forceOnTop
	 * @return void
	 */
	public function addCssInlineBlock($name, $block, $compress = FALSE, $forceOnTop = FALSE) {
		if (!isset($this->cssInline[$name]) && !empty($block)) {
			$this->cssInline[$name] = array (
				'code'       => $block,
				'compress'   => $compress,
				'forceOnTop' => $forceOnTop
			);
		}
	}

	/* JS Libraries */

	/**
	 *  call function if you need the prototype library
	 *
	 * @return void
	 */
	public function loadPrototype() {
		$this->addPrototype = TRUE;
	}

	/**
	 * call function if you need the Scriptaculous library
	 *
	 * @param string $modules   add modules you need. use "all" if you need complete modules
	 * @return void
	 */
	public function loadScriptaculous($modules = '') {
		// Scriptaculous require prototype, so load prototype too.
		$this->addPrototype = TRUE;
		$this->addScriptaculous = TRUE;
		if ($modules) {
			if ($modules == 'all') {
				foreach ($this->addScriptaculousModules as $key => $value) {
					$this->addScriptaculousModules[$key] = TRUE;
				}
			} else {
				$mods = t3lib_div::trimExplode(',', $modules);
				foreach ($mods as $mod) {
					if (isset($this->addScriptaculousModules[strtolower($mod)])) {
						$this->addScriptaculousModules[strtolower($mod)] = TRUE;
					}
				}
			}
		}
	}

	/**
	 * call this function if you need the extJS library
	 *
	 * @param boolean $css flag, if set the ext-css will be loaded
	 * @param boolean $theme flag, if set the ext-theme "grey" will be loaded
	 * @param string $adapter choose alternative adapter, possible values: yui, prototype, jquery
	 * @return void
	 */
	public function loadExtJS($css = TRUE, $theme = TRUE, $adapter = '') {
		if ($adapter) {
			// empty $adapter will always load the ext adapter
			switch (t3lib_div::strtolower(trim($adapter))) {
				case self::EXTJS_ADAPTER_YUI :
					$this->extJSadapter = 'yui/ext-yui-adapter.js';
					break;
				case self::EXTJS_ADAPTER_PROTOTYPE :
					$this->extJSadapter = 'prototype/ext-prototype-adapter.js';
					break;
				case self::EXTJS_ADAPTER_JQUERY :
					$this->extJSadapter = 'jquery/ext-jquery-adapter.js';
					break;
			}
		}
		$this->addExtJS = TRUE;
		$this->extJStheme = $theme;
		$this->extJScss = $css;

	}

	/**
	 * Enables ExtJs QuickTips
	 * Need extJs loaded
	 *
	 * @return void
	 *
	 */
	public function enableExtJSQuickTips() {
		$this->enableExtJSQuickTips = TRUE;
	}


	/**
	 * call function if you need the ExtCore library
	 *
	 * @return void
	 */
	public function loadExtCore() {
		$this->addExtCore = TRUE;
	}

	/**
	 * call this function to load debug version of ExtJS. Use this for development only
	 *
	 */
	public function enableExtJsDebug() {
		$this->enableExtJsDebug = TRUE;
	}

	/**
	 * call this function to load debug version of ExtCore. Use this for development only
	 *
	 * @return void
	 */
	public function enableExtCoreDebug() {
		$this->enableExtCoreDebug = TRUE;
	}

	/**
	 * Adds Javascript Inline Label. This will occur in TYPO3.lang - object
	 * The label can be used in scripts with TYPO3.lang.<key>
	 * Need extJs loaded
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function addInlineLanguageLabel($key, $value) {
		$this->inlineLanguageLabels[$key] = $value;
	}

	/**
	 * Adds Javascript Inline Label Array. This will occur in TYPO3.lang - object
	 * The label can be used in scripts with TYPO3.lang.<key>
	 * Array will be merged with existing array.
	 * Need extJs loaded
	 *
	 * @param array $array
	 * @return void
	 */
	public function addInlineLanguageLabelArray(array $array) {
		$this->inlineLanguageLabels = array_merge($this->inlineLanguageLabels, $array);
	}

	/**
	 * Adds Javascript Inline Setting. This will occur in TYPO3.settings - object
	 * The label can be used in scripts with TYPO3.setting.<key>
	 * Need extJs loaded
	 *
	 * @param string $namespace
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function addInlineSetting($namespace, $key, $value) {
		if ($namespace) {
			if (strpos($namespace, '.')) {
				$parts = explode('.', $namespace);
				$a = &$this->inlineSettings;
				foreach ($parts as $part) {
					$a = &$a[$part];
				}
				$a[$key] = $value;
			} else {
				$this->inlineSettings[$namespace][$key] = $value;
			}
		} else {
			$this->inlineSettings[$key] = $value;
		}
	}

	/**
	 * Adds Javascript Inline Setting. This will occur in TYPO3.settings - object
	 * The label can be used in scripts with TYPO3.setting.<key>
	 * Array will be merged with existing array.
	 * Need extJs loaded
	 *
	 * @param string $namespace
	 * @param array $array
	 * @return void
	 */
	public function addInlineSettingArray($namespace, array $array) {
		if ($namespace) {
			if (strpos($namespace, '.')) {
				$parts = explode('.', $namespace);
				$a = &$this->inlineSettings;
				foreach ($parts as $part) {
					$a = &$a[$part];
				}
				$a = array_merge((array) $a, $array);
			} else {
				$this->inlineSettings[$namespace] = array_merge((array) $this->inlineSettings[$namespace], $array);
			}
		} else {
			$this->inlineSettings = array_merge($this->inlineSettings, $array);
		}
	}

	/**
	 * Adds content to body content
	 *
	 * @param string $content
	 * @return void
	 */
	public function addBodyContent($content) {
		$this->bodyContent .= $content;
	}

	/*****************************************************/
	/*                                                   */
	/*  Render Functions                                 */
	/*                                                   */
	/*                                                   */
	/*****************************************************/

	/**
	 * render the section (Header or Footer)
	 *
	 * @param int $part	section which should be rendered: self::PART_COMPLETE, self::PART_HEADER or self::PART_FOOTER
	 * @return string	content of rendered section
	 */
	public function render($part = self::PART_COMPLETE) {

		$jsFiles = '';
		$cssFiles = '';
		$cssInline = '';
		$jsInline = '';
		$jsFooterInline = '';
		$jsFooterLibs = '';
		$jsFooterFiles = '';


		// preRenderHook for possible manuipulation
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'])) {
			$params = array (
				'jsLibs'         => &$this->jsLibs,
				'jsFiles'        => &$this->jsFiles,
				'jsFooterFiles'  => &$this->jsFooterFiles,
				'cssFiles'       => &$this->cssFiles,
				'headerData'     => &$this->headerData,
				'footerData'     => &$this->footerData,
				'jsInline'       => &$this->jsInline,
				'cssInline'      => &$this->cssInline,
			);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'] as $hook) {
				t3lib_div::callUserFunction($hook, $params, $this);
			}
		}

		$jsLibs = $this->renderJsLibraries();

		if ($this->compressCss || $this->compressJavascript) {
				// do the file compression
			$this->doCompress();
		}
		if ($this->concatenateFiles) {
				// do the file concatenation
			$this->doConcatenate();
		}

		$metaTags = implode(chr(10), $this->metaTags);

		if (count($this->cssFiles)) {
			foreach ($this->cssFiles as $file => $properties) {
				$file = htmlspecialchars(t3lib_div::resolveBackPath($file));
				$tag = '<link rel="' . $properties['rel'] . '" type="text/css" href="' . $file . '" media="' . $properties['media'] . '"' . ($properties['title'] ? ' title="' . $properties['title'] . '"' : '') . ' />';
				if ($properties['allWrap'] && strpos($properties['allWrap'], '|') !== FALSE) {
					$tag = str_replace('|', $tag, $properties['allWrap']);
				}
				if ($properties['forceOnTop']) {
					$cssFiles = $tag . chr(10) . $cssFiles;
				} else {
					$cssFiles .= chr(10) . $tag;
				}
			}
		}

		if (count($this->cssInline)) {

			foreach ($this->cssInline as $name => $properties) {
				if ($properties['forceOnTop']) {
					$cssInline = '/*' . htmlspecialchars($name) . '*/' . chr(10) . $properties['code'] . chr(10) . $cssInline;
				} else {
					$cssInline .= '/*' . htmlspecialchars($name) . '*/' . chr(10) . $properties['code'] . chr(10);
				}
			}
			$cssInline = $this->inlineCssWrap[0] . $cssInline . $this->inlineCssWrap[1];

		}

		if (count($this->jsLibs)) {
			foreach ($this->jsLibs as $name => $properties) {
				$properties['file'] = htmlspecialchars(t3lib_div::resolveBackPath($properties['file']));
				$tag = '<script src="' . $properties['file'] . '" type="' . $properties['type'] . '"></script>';
				if ($properties['allWrap'] && strpos($properties['allWrap'], '|') !== FALSE) {
					$tag = str_replace('|', $tag, $properties['allWrap']);
				}
				if ($properties['forceOnTop']) {
					if ($properties['section'] === self::PART_HEADER) {
						$jsLibs = $tag . chr(10) . $jsLibs;
					} else {
						$jsFooterLibs = $tag . chr(10) . $jsFooterLibs;
					}
				} else {
					if ($properties['section'] === self::PART_HEADER) {
						$jsLibs .= chr(10) . $tag;
					} else {
						$jsFooterLibs .= chr(10) . $tag;
					}
				}

			}
		}

		if (count($this->jsFiles)) {
			foreach ($this->jsFiles as $file => $properties) {
					$file = htmlspecialchars(t3lib_div::resolveBackPath($file));
					$tag = '<script src="' . $file . '" type="' . $properties['type'] . '"></script>';
					if ($properties['allWrap'] && strpos($properties['allWrap'], '|') !== FALSE) {
						$tag = str_replace('|', $tag, $properties['allWrap']);
					}
					if ($properties['forceOnTop']) {
						if ($properties['section'] === self::PART_HEADER) {
							$jsFiles = $tag . chr(10) . $jsFiles;
						} else {
							$jsFooterFiles = $tag . chr(10) . $jsFooterFiles;
						}
					} else {
						if ($properties['section'] === self::PART_HEADER) {
							$jsFiles .= chr(10) . $tag;
						} else {
							$jsFooterFiles .= chr(10) . $tag;
						}
					}
			}
		}

		if (count($this->jsInline)) {
			foreach ($this->jsInline as $name => $properties) {
				if ($properties['forceOnTop']) {
					if ($properties['section'] === self::PART_HEADER) {
						$jsInline = '/*' . htmlspecialchars($name) . '*/' . chr(10) . $properties['code'] . chr(10) . $jsInline;
					} else {
						$jsFooterInline = '/*' . htmlspecialchars($name) . '*/' . chr(10) . $properties['code'] . chr(10) . $jsFooterInline;
					}
				} else {
					if ($properties['section'] === self::PART_HEADER) {
						$jsInline .= '/*' . htmlspecialchars($name) . '*/' . chr(10) . $properties['code'] . chr(10);
					} else {
						$jsFooterInline .= '/*' . htmlspecialchars($name) . '*/' . chr(10) . $properties['code'] . chr(10);
					}
				}
			}
		}


		if ($jsInline) {
			$jsInline = $this->inlineJavascriptWrap[0] . $jsInline . $this->inlineJavascriptWrap[1];
		}

		if ($jsFooterInline) {
			$jsFooterInline = $this->inlineJavascriptWrap[0] . $jsFooterInline . $this->inlineJavascriptWrap[1];
		}


			// get template
		$templateFile = t3lib_div::getFileAbsFileName($this->templateFile, TRUE);
		$template = t3lib_div::getURL($templateFile);

		if ($this->removeLineBreaksFromTemplate) {
			$template = strtr($template, array(chr(10) => '', chr(13) => ''));
		}
		if ($part != self::PART_COMPLETE) {
			$templatePart = explode('###BODY###', $template);
			$template = $templatePart[$part - 1];
		}

		if ($this->moveJsFromHeaderToFooter) {
			$jsFooterLibs = $jsLibs . chr(10) . $jsFooterLibs;
			$jsLibs = '';
			$jsFooterFiles = $jsFiles . chr(10) . $jsFooterFiles;
			$jsFiles = '';
			$jsFooterInline = $jsInline . chr(10) . $jsFooterInline;
			$jsInline = '';
		}

		$markerArray = array(
			'XMLPROLOG_DOCTYPE' => $this->xmlPrologAndDocType,
			'HTMLTAG'           => $this->htmlTag,
			'HEADTAG'           => $this->headTag,
			'METACHARSET'       => $this->charSet ? str_replace('|', htmlspecialchars($this->charSet), $this->metaCharsetTag) : '',
			'INLINECOMMENT'     => $this->inlineComments ? chr(10) . chr(10) . '<!-- ' . chr(10) . implode(chr(10), $this->inlineComments) . '-->' . chr(10) . chr(10) : '',
			'BASEURL'           => $this->baseUrl ? str_replace('|', $this->baseUrl, $this->baseUrlTag) : '',
			'SHORTCUT'          => $this->favIcon ? sprintf($this->shortcutTag, htmlspecialchars($this->favIcon), $this->iconMimeType) : '',
			'CSS_INCLUDE'       => $cssFiles,
			'CSS_INLINE'        => $cssInline,
			'JS_INLINE'         => $jsInline,
			'JS_INCLUDE'        => $jsFiles,
			'JS_LIBS'        	=> $jsLibs,
			'TITLE'             => $this->title ? str_replace('|', htmlspecialchars($this->title), $this->titleTag) : '',
			'META'              => $metaTags,
			'HEADERDATA'        => $this->headerData ? implode(chr(10), $this->headerData) : '',
			'FOOTERDATA'        => $this->footerData ? implode(chr(10), $this->footerData) : '',
			'JS_LIBS_FOOTER' 	=> $jsFooterLibs,
			'JS_INCLUDE_FOOTER' => $jsFooterFiles,
			'JS_INLINE_FOOTER'  => $jsFooterInline,
			'BODY'				=> $this->bodyContent,
		);

		$markerArray = array_map('trim', $markerArray);

		$this->reset();
		return trim(t3lib_parsehtml::substituteMarkerArray($template, $markerArray, '###|###'));
	}

	/**
	 * helper function for render the javascript libraries
	 *
	 * @return string	content with javascript libraries
	 */
	protected function renderJsLibraries() {
		$out = '';

		if ($this->addPrototype) {
			$out .= '<script src="' . $this->backPath . 'contrib/prototype/prototype.js" type="text/javascript"></script>' . chr(10);
			unset($this->jsFiles[$this->backPath . 'contrib/prototype/prototype.js']);
		}

		if ($this->addScriptaculous) {
			$mods = array ();
			foreach ($this->addScriptaculousModules as $key => $value) {
				if ($this->addScriptaculousModules[$key]) {
					$mods[] = $key;
				}
			}
				// resolve dependencies
			if (in_array('dragdrop', $mods) || in_array('controls', $mods)) {
				$mods = array_merge(array('effects'), $mods);
			}

			if (count($mods)) {
				$moduleLoadString = '?load=' . implode(',', $mods);
			}

			$out .= '<script src="' . $this->backPath . 'contrib/scriptaculous/scriptaculous.js' . $moduleLoadString . '" type="text/javascript"></script>' . chr(10);
			unset($this->jsFiles[$this->backPath . 'contrib/scriptaculous/scriptaculous.js' . $moduleLoadString]);
		}

			// include extCore
		if ($this->addExtCore) {
			$out .= '<script src="' . $this->backPath . 'contrib/extjs/ext-core' . ($this->enableExtCoreDebug ? '-debug' : '') . '.js" type="text/javascript"></script>' . chr(10);
			unset($this->jsFiles[$this->backPath . 'contrib/extjs/ext-core' . ($this->enableExtCoreDebug ? '-debug' : '') . '.js']);
		}

			// include extJS
		if ($this->addExtJS) {
				// use the base adapter all the time
			$out .= '<script src="' . $this->backPath . 'contrib/extjs/adapter/' . ($this->enableExtJsDebug ? str_replace('.js', '-debug.js', $this->extJSadapter) : $this->extJSadapter) . '" type="text/javascript"></script>' . chr(10);
			$out .= '<script src="' . $this->backPath . 'contrib/extjs/ext-all' . ($this->enableExtJsDebug ? '-debug' : '') . '.js" type="text/javascript"></script>' . chr(10);

				// add extJS localization
			$localeMap = $this->csConvObj->isoArray; // load standard ISO mapping and modify for use with ExtJS
			$localeMap[''] = 'en';
			$localeMap['default'] = 'en';
			$localeMap['gr'] = 'el_GR'; // Greek
			$localeMap['no'] = 'no_BO'; // Norwegian Bokmaal
			$localeMap['se'] = 'se_SV'; // Swedish


			$extJsLang = isset($localeMap[$this->lang]) ? $localeMap[$this->lang] : $this->lang;
				// TODO autoconvert file from UTF8 to current BE charset if necessary!!!!
			$extJsLocaleFile = 'contrib/extjs/locale/ext-lang-' . $extJsLang . '-min.js';
			if (file_exists(PATH_typo3 . $extJsLocaleFile)) {
				$out .= '<script src="' . $this->backPath . $extJsLocaleFile . '" type="text/javascript" charset="utf-8"></script>' . chr(10);
			}


				// remove extjs from JScodeLibArray
			unset(
				$this->jsFiles[$this->backPath . 'contrib/extjs/ext-all.js'], $this->jsFiles[$this->backPath . 'contrib/extjs/ext-all-debug.js']
			);
		}

			// Convert labels/settings back to UTF-8 since json_encode() only works with UTF-8:
		if ($this->getCharSet() !== 'utf-8') {
			if ($this->inlineLanguageLabels) {
				$this->csConvObj->convArray($this->inlineLanguageLabels, $this->getCharSet(), 'utf-8');
			}
			if ($this->inlineSettings) {
				$this->csConvObj->convArray($this->inlineSettings, $this->getCharSet(), 'utf-8');
			}
		}

		$inlineSettings = $this->inlineLanguageLabels ? 'TYPO3.lang = ' . json_encode($this->inlineLanguageLabels) . ';' : '';
		$inlineSettings .= $this->inlineSettings ? 'TYPO3.settings = ' . json_encode($this->inlineSettings) . ';' : '';

		if ($this->addExtCore || $this->addExtJS) {
				// set clear.gif, move it on top, add handler code
			$code = '';
			if (count($this->extOnReadyCode)) {
				foreach ($this->extOnReadyCode as $block) {
					$code .= $block;
				}
			}

			$out .= $this->inlineJavascriptWrap[0] . '
				Ext.ns("TYPO3");
				Ext.BLANK_IMAGE_URL = "' . htmlspecialchars(t3lib_div::locationHeaderUrl($this->backPath . 'gfx/clear.gif')) . '";' . chr(10) .
				$inlineSettings .
				'Ext.onReady(function() {' .
				($this->enableExtJSQuickTips ? 'Ext.QuickTips.init();' . chr(10) : '') . $code .
				' });' . $this->inlineJavascriptWrap[1];
			unset ($this->extOnReadyCode);

			if ($this->extJStheme) {
				if (isset($GLOBALS['TBE_STYLES']['extJS']['theme'])) {
					$this->addCssFile($this->backPath . $GLOBALS['TBE_STYLES']['extJS']['theme'], 'stylesheet', 'screen', '', FALSE, TRUE);
				} else {
					$this->addCssFile($this->backPath . 'contrib/extjs/resources/css/xtheme-blue.css', 'stylesheet', 'screen', '', FALSE, TRUE);
				}
			}
			if ($this->extJScss) {
				if (isset($GLOBALS['TBE_STYLES']['extJS']['all'])) {
					$this->addCssFile($this->backPath . $GLOBALS['TBE_STYLES']['extJS']['all'], 'stylesheet', 'screen', '', FALSE, TRUE);
				} else {
					$this->addCssFile($this->backPath . 'contrib/extjs/resources/css/ext-all-notheme.css', 'stylesheet', 'screen', '', FALSE, TRUE);
				}
			}
		} else {
			if ($inlineSettings) {
				$out .= $this->inlineJavascriptWrap[0] . $inlineSettings . $this->inlineJavascriptWrap[1];
		}
		}

		return $out;
	}

	/*****************************************************/
	/*                                                   */
	/*  Tools                                            */
	/*                                                   */
	/*                                                   */
	/*****************************************************/

	/**
	 * concatenate files into one file
	 * registered handler
	 * TODO: implement own method
	 *
	 * @return void
	 */
	protected function doConcatenate() {
		// traverse the arrays, concatenate in one file
		// then remove concatenated files from array and add the concatenated file


			// extern concatination
		if ($this->concatenateFiles && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['concatenateHandler']) {
			// use extern concatenate routine
			$params = array (
				'jsLibs'         => &$this->jsLibs,
				'jsFiles'        => &$this->jsFiles,
				'jsFooterFiles'  => &$this->jsFooterFiles,
				'cssFiles'       => &$this->cssFiles,
				'headerData'     => &$this->headerData,
				'footerData'     => &$this->footerData,
			);
			t3lib_div::callUserFunction($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['concatenateHandler'], $params, $this);
		} else {
			// own method, nothing implemented atm


		}
	}

	/**
	 * compress inline code
	 *
	 */
	protected function doCompress() {

		if ($this->compressJavascript && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['jsCompressHandler']) {
			// use extern compress routine
			$params = array (
				'jsInline'        => &$this->jsInline,
				'jsFooterInline'  => &$this->jsFooterInline,
				'jsLibs'          => &$this->jsLibs,
				'jsFiles'         => &$this->jsFiles,
				'jsFooterFiles'   => &$this->jsFooterFiles,
				'headerData'      => &$this->headerData,
				'footerData'      => &$this->footerData,
			);
			t3lib_div::callUserFunction($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['jsCompressHandler'], $params, $this);
		} else {
				// traverse the arrays, compress files

			if ($this->compressJavascript) {
				if (count($this->jsInline)) {
					foreach ($this->jsInline as $name => $properties) {
						if ($properties['compress']) {
							$error = '';
							$this->jsInline[$name]['code'] = t3lib_div::minifyJavaScript($properties['code'], $error);
							if ($error) {
								$this->compressError .= 'Error with minify JS Inline Block "' . $name . '": ' . $error . chr(10);
							}
						}
					}
				}
			}
		}

		if ($this->compressCss && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['cssCompressHandler']) {
				// use extern compress routine
			$params = array (
				'cssInline'  => &$this->cssInline,
				'cssFiles'   => &$this->cssFiles,
				'headerData' => &$this->headerData,
				'footerData' => &$this->footerData,
			);
			t3lib_div::callUserFunction($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['cssCompressHandler'], $params, $this);
		} else {
			if ($this->compressCss) {
				// own method, nothing implemented atm
			}
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_pagerenderer.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_pagerenderer.php']);
}
?>