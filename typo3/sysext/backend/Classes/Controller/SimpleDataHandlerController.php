<?php
namespace TYPO3\CMS\Backend\Controller;

/**
 * Script Class, creating object of t3lib_TCEmain and sending the posted data to the object.
 * Used by many smaller forms/links in TYPO3, including the QuickEdit module.
 * Is not used by alt_doc.php though (main form rendering script) - that uses the same class (TCEmain) but makes its own initialization (to save the redirect request).
 * For all other cases than alt_doc.php it is recommended to use this script for submitting your editing forms - but the best solution in any case would probably be to link your application to alt_doc.php, that will give you easy form-rendering as well.
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SimpleDataHandlerController {

	// Internal, static: GPvar
	// Array. Accepts options to be set in TCE object. Currently it supports "reverseOrder" (boolean).
	/**
	 * @todo Define visibility
	 */
	public $flags;

	// Data array on the form [tablename][uid][fieldname] = value
	/**
	 * @todo Define visibility
	 */
	public $data;

	// Command array on the form [tablename][uid][command] = value. This array may get additional data set internally based on clipboard commands send in CB var!
	/**
	 * @todo Define visibility
	 */
	public $cmd;

	// Array passed to ->setMirror.
	/**
	 * @todo Define visibility
	 */
	public $mirror;

	// Cache command sent to ->clear_cacheCmd
	/**
	 * @todo Define visibility
	 */
	public $cacheCmd;

	// Redirect URL. Script will redirect to this location after performing operations (unless errors has occured)
	/**
	 * @todo Define visibility
	 */
	public $redirect;

	// Boolean. If set, errors will be printed on screen instead of redirection. Should always be used, otherwise you will see no errors if they happen.
	/**
	 * @todo Define visibility
	 */
	public $prErr;

	// Clipboard command array. May trigger changes in "cmd"
	/**
	 * @todo Define visibility
	 */
	public $CB;

	// Verification code
	/**
	 * @todo Define visibility
	 */
	public $vC;

	// Boolean. Update Page Tree Trigger. If set and the manipulated records are pages then the update page tree signal will be set.
	/**
	 * @todo Define visibility
	 */
	public $uPT;

	// String, general comment (for raising stages of workspace versions)
	/**
	 * @todo Define visibility
	 */
	public $generalComment;

	// Internal, dynamic:
	// Files to include after init() function is called:
	/**
	 * @todo Define visibility
	 */
	public $include_once = array();

	/**
	 * TYPO3 Core Engine
	 *
	 * @var \TYPO3\CMS\Core\DataHandler\DataHandler
	 * @todo Define visibility
	 */
	public $tce;

	/**
	 * Initialization of the class
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function init() {
		// GPvars:
		$this->flags = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('flags');
		$this->data = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('data');
		$this->cmd = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('cmd');
		$this->mirror = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('mirror');
		$this->cacheCmd = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('cacheCmd');
		$this->redirect = \TYPO3\CMS\Core\Utility\GeneralUtility::sanitizeLocalUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('redirect'));
		$this->prErr = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('prErr');
		$this->_disableRTE = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('_disableRTE');
		$this->CB = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('CB');
		$this->vC = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('vC');
		$this->uPT = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('uPT');
		$this->generalComment = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('generalComment');
		// Creating TCEmain object
		$this->tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandler\\DataHandler');
		$this->tce->stripslashes_values = 0;
		$this->tce->generalComment = $this->generalComment;
		// Configuring based on user prefs.
		if ($GLOBALS['BE_USER']->uc['recursiveDelete']) {
			// TRUE if the delete Recursive flag is set.
			$this->tce->deleteTree = 1;
		}
		if ($GLOBALS['BE_USER']->uc['copyLevels']) {
			// Set to number of page-levels to copy.
			$this->tce->copyTree = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($GLOBALS['BE_USER']->uc['copyLevels'], 0, 100);
		}
		if ($GLOBALS['BE_USER']->uc['neverHideAtCopy']) {
			$this->tce->neverHideAtCopy = 1;
		}
		$TCAdefaultOverride = $GLOBALS['BE_USER']->getTSConfigProp('TCAdefaults');
		if (is_array($TCAdefaultOverride)) {
			$this->tce->setDefaultsFromUserTS($TCAdefaultOverride);
		}
		// Reverse order.
		if ($this->flags['reverseOrder']) {
			$this->tce->reverseOrder = 1;
		}
	}

	/**
	 * Clipboard pasting and deleting.
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function initClipboard() {
		if (is_array($this->CB)) {
			$clipObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Clipboard\\Clipboard');
			$clipObj->initializeClipboard();
			if ($this->CB['paste']) {
				$clipObj->setCurrentPad($this->CB['pad']);
				$this->cmd = $clipObj->makePasteCmdArray($this->CB['paste'], $this->cmd);
			}
			if ($this->CB['delete']) {
				$clipObj->setCurrentPad($this->CB['pad']);
				$this->cmd = $clipObj->makeDeleteCmdArray($this->cmd);
			}
		}
	}

	/**
	 * Executing the posted actions ...
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function main() {
		// LOAD TCEmain with data and cmd arrays:
		$this->tce->start($this->data, $this->cmd);
		if (is_array($this->mirror)) {
			$this->tce->setMirror($this->mirror);
		}
		// Checking referer / executing
		$refInfo = parse_url(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_REFERER'));
		$httpHost = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
		if ($httpHost != $refInfo['host'] && $this->vC != $GLOBALS['BE_USER']->veriCode() && !$GLOBALS['TYPO3_CONF_VARS']['SYS']['doNotCheckReferer']) {
			$this->tce->log('', 0, 0, 0, 1, 'Referer host "%s" and server host "%s" did not match and veriCode was not valid either!', 1, array($refInfo['host'], $httpHost));
		} else {
			// Register uploaded files
			$this->tce->process_uploads($_FILES);
			// Execute actions:
			$this->tce->process_datamap();
			$this->tce->process_cmdmap();
			// Clearing cache:
			$this->tce->clear_cacheCmd($this->cacheCmd);
			// Update page tree?
			if ($this->uPT && (isset($this->data['pages']) || isset($this->cmd['pages']))) {
				\TYPO3\CMS\Backend\Utility\BackendUtility::setUpdateSignal('updatePageTree');
			}
		}
	}

	/**
	 * Redirecting the user after the processing has been done.
	 * Might also display error messages directly, if any.
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function finish() {
		// Prints errors, if...
		if ($this->prErr) {
			$this->tce->printLogErrorMessages($this->redirect);
		}
		if ($this->redirect && !$this->tce->debug) {
			\TYPO3\CMS\Core\Utility\HttpUtility::redirect($this->redirect);
		}
	}

}


?>