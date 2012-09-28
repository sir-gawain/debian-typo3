<?php
namespace TYPO3\CMS\Backend\Controller;

/**
 * Script Class for the Context Sensitive Menu in TYPO3 (rendered in top frame, normally writing content dynamically to list frames).
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 * @see template::getContextMenuCode()
 */
class ClickMenuController {

	// Internal, static: GPvar:
	// Back path.
	/**
	 * @todo Define visibility
	 */
	public $backPath;

	// Definition of which item the click menu should be made for.
	/**
	 * @todo Define visibility
	 */
	public $item;

	// Defines the name of the document object for which to reload the URL.
	/**
	 * @todo Define visibility
	 */
	public $reloadListFrame;

	// Internal:
	// Content accumulation
	/**
	 * @todo Define visibility
	 */
	public $content = '';

	// Template object
	/**
	 * @todo Define visibility
	 */
	public $doc;

	// Files to include_once() - set in init() function
	/**
	 * @todo Define visibility
	 */
	public $include_once = array();

	// Internal array of classes for extending the clickmenu
	/**
	 * @todo Define visibility
	 */
	public $extClassArray = array();

	// If set, then the clickmenu will NOT display in the top frame.
	/**
	 * @todo Define visibility
	 */
	public $dontDisplayTopFrameCM = 0;

	/**
	 * Constructor function for script class.
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function init() {
		// Setting GPvars:
		$this->backPath = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('backPath');
		$this->item = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('item');
		$this->reloadListFrame = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('reloadListFrame');
		// Setting pseudo module name
		$this->MCONF['name'] = 'xMOD_alt_clickmenu.php';
		// Takes the backPath as a parameter BUT since we are worried about someone forging a backPath (XSS security hole) we will check with sent md5 hash:
		$inputBP = explode('|', $this->backPath);
		if (count($inputBP) == 2 && $inputBP[1] == \TYPO3\CMS\Core\Utility\GeneralUtility::shortMD5($inputBP[0] . '|' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'])) {
			$this->backPath = $inputBP[0];
		} else {
			$this->backPath = $GLOBALS['BACK_PATH'];
		}
		// Setting internal array of classes for extending the clickmenu:
		$this->extClassArray = $GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'];
		// Traversing that array and setting files for inclusion:
		if (is_array($this->extClassArray)) {
			foreach ($this->extClassArray as $extClassConf) {
				if ($extClassConf['path']) {
					$this->include_once[] = $extClassConf['path'];
				}
			}
		}
		// Initialize template object
		if (!$this->ajax) {
			$this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
			$this->doc->backPath = $GLOBALS['BACK_PATH'];
		}
		// Setting mode for display and background image in the top frame
		$this->dontDisplayTopFrameCM = $this->doc->isCMlayers() && !$GLOBALS['BE_USER']->getTSConfigVal('options.contextMenu.options.alwaysShowClickMenuInTopFrame');
		if ($this->dontDisplayTopFrameCM) {
			$this->doc->bodyTagId .= '-notop';
		}
		// Setting clickmenu timeout
		$secs = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($GLOBALS['BE_USER']->getTSConfigVal('options.contextMenu.options.clickMenuTimeOut'), 1, 100, 5);
		// default is 5
		// Setting the JavaScript controlling the timer on the page
		$listFrameDoc = $this->reloadListFrame != 2 ? 'top.content.list_frame' : 'top.content';
		$this->doc->JScode .= $this->doc->wrapScriptTags('
	var date = new Date();
	var mo_timeout = Math.floor(date.getTime()/1000);

	roImg = "' . \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIconClasses('status-status-current') . '";

	routImg = "t3-icon-empty";

	function mo(c) {	//
		var name="roimg_"+c;
		document.getElementById(name).className = roImg;
		updateTime();
	}
	function mout(c) {	//
		var name="roimg_"+c;
		document[name].src = routImg;
		updateTime();
	}
	function updateTime() {	//
		date = new Date();
		mo_timeout = Math.floor(date.getTime()/1000);
	}
	function timeout_func() {	//
		date = new Date();
		if (Math.floor(date.getTime()/1000)-mo_timeout > ' . $secs . ') {
			hideCM();
			return false;
		} else {
			window.setTimeout("timeout_func();",1*1000);
		}
	}
	function hideCM() {	//
		window.location.href="alt_topmenu_dummy.php";
		return false;
	}

		// Start timer
	timeout_func();

	' . ($this->reloadListFrame ? '
		// Reload list frame:
	if(' . $listFrameDoc . '){' . $listFrameDoc . '.location.href=' . $listFrameDoc . '.location.href;}' : '') . '
		');
	}

	/**
	 * Main function - generating the click menu in whatever form it has.
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function main() {
		$this->ajax = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('ajax') ? TRUE : FALSE;
		// Initialize Clipboard object:
		$clipObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Clipboard\\Clipboard');
		$clipObj->initializeClipboard();
		// This locks the clipboard to the Normal for this request.
		$clipObj->lockToNormal();
		// Update clipboard if some actions are sent.
		$CB = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('CB');
		$clipObj->setCmd($CB);
		$clipObj->cleanCurrent();
		// Saves
		$clipObj->endClipboard();
		// Create clickmenu object
		$clickMenu = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\ClickMenu\\ClickMenu');
		// Set internal vars in clickmenu object:
		$clickMenu->clipObj = $clipObj;
		$clickMenu->extClassArray = $this->extClassArray;
		$clickMenu->dontDisplayTopFrameCM = $this->dontDisplayTopFrameCM;
		$clickMenu->backPath = $this->backPath;
		// Start page
		if (!$this->ajax) {
			$this->content .= $this->doc->startPage('Context Sensitive Menu');
		}
		// Set content of the clickmenu with the incoming var, "item"
		$this->content .= $clickMenu->init();
	}

	/**
	 * End page and output content.
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function printContent() {
		if (!$this->ajax) {
			$this->content .= $this->doc->endPage();
			$this->content = $this->doc->insertStylesAndJS($this->content);
			echo $this->content;
		} else {
			header('Content-Type: text/xml');
			echo '<?xml version="1.0"?>' . LF . '<t3ajax>' . $this->content . '</t3ajax>';
		}
	}

}


?>