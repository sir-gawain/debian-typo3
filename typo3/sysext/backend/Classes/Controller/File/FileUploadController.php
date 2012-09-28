<?php
namespace TYPO3\CMS\Backend\Controller\File;

/**
 * Script Class for display up to 10 upload fields
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class FileUploadController {

	// Internal, static:
	/**
	 * Document template object
	 *
	 * @var \TYPO3\CMS\Backend\Template\SmallDocumentTemplate
	 * @todo Define visibility
	 */
	public $doc;

	// Name of the filemount
	/**
	 * @todo Define visibility
	 */
	public $title;

	// Internal, static (GPVar):
	// Set with the target path inputted in &target
	/**
	 * @todo Define visibility
	 */
	public $target;

	// Return URL of list module.
	/**
	 * @todo Define visibility
	 */
	public $returnUrl;

	// Internal, dynamic:
	// Accumulating content
	/**
	 * @todo Define visibility
	 */
	public $content;

	/**
	 * The folder object which is the target directory for the upload
	 *
	 * @var \TYPO3\CMS\Core\Resource\Folder $folderObject
	 */
	protected $folderObject;

	/**
	 * Constructor for initializing the class
	 *
	 * @return 	void
	 * @todo Define visibility
	 */
	public function init() {
		// Initialize GPvars:
		$this->target = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('target');
		$this->returnUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::sanitizeLocalUrl(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('returnUrl'));
		if (!$this->returnUrl) {
			$this->returnUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . TYPO3_mainDir . \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('file_list') . '&id=' . rawurlencode($this->target);
		}
		// Create the folder object
		if ($this->target) {
			$this->folderObject = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance()->retrieveFileOrFolderObject($this->target);
		}
		// Cleaning and checking target directory
		if (!$this->folderObject) {
			$title = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_file_list.xml:paramError', TRUE);
			$message = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_file_list.xml:targetNoDir', TRUE);
			throw new \RuntimeException($title . ': ' . $message, 1294586843);
		}
		// Setting the title and the icon
		$icon = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('apps-filetree-root');
		$this->title = $icon . htmlspecialchars($this->folderObject->getStorage()->getName()) . ': ' . htmlspecialchars($this->folderObject->getIdentifier());
		// Setting template object
		$this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
		$this->doc->setModuleTemplate('templates/file_upload.html');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->form = '<form action="tce_file.php" method="post" name="editform" enctype="' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['form_enctype'] . '">';
	}

	/**
	 * Main function, rendering the upload file form fields
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function main() {
		// Make page header:
		$this->content = $this->doc->startPage($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:file_upload.php.pagetitle'));
		$form = $this->renderUploadForm();
		$pageContent = $this->doc->header($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:file_upload.php.pagetitle')) . $this->doc->section('', $form);
		// Header Buttons
		$docHeaderButtons = array(
			'csh' => \TYPO3\CMS\Backend\Utility\BackendUtility::cshItem('xMOD_csh_corebe', 'file_upload', $GLOBALS['BACK_PATH'])
		);
		$markerArray = array(
			'CSH' => $docHeaderButtons['csh'],
			'FUNC_MENU' => \TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'], $this->MOD_MENU['function']),
			'CONTENT' => $pageContent,
			'PATH' => $this->title
		);
		$this->content .= $this->doc->moduleBody(array(), $docHeaderButtons, $markerArray);
		$this->content .= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);
	}

	/**
	 * This function renders the upload form
	 *
	 * @return 	string	the HTML form as a string, ready for outputting
	 * @todo Define visibility
	 */
	public function renderUploadForm() {
		// Make checkbox for "overwrite"
		$content = '
			<div id="c-override">
				<p><label for="overwriteExistingFiles"><input type="checkbox" class="checkbox" name="overwriteExistingFiles" id="overwriteExistingFiles" value="1" /> ' . $GLOBALS['LANG']->getLL('overwriteExistingFiles', 1) . '</label></p>
				<p>&nbsp;</p>
				<p>' . $GLOBALS['LANG']->getLL('uploadMultipleFilesInfo', TRUE) . '</p>
			</div>
			';
		// Produce the number of upload-fields needed:
		$content .= '
			<div id="c-upload">
		';
		// Adding 'size="50" ' for the sake of Mozilla!
		$content .= '
				<input type="file" multiple="true" name="upload_1[]" />
				<input type="hidden" name="file[upload][1][target]" value="' . htmlspecialchars($this->folderObject->getCombinedIdentifier()) . '" />
				<input type="hidden" name="file[upload][1][data]" value="1" /><br />
			';
		$content .= '
			</div>
		';
		// Submit button:
		$content .= '
			<div id="c-submit">
				<input type="hidden" name="redirect" value="' . $this->returnUrl . '" /><br />
				<input type="submit" value="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:file_upload.php.submit', 1) . '" />
			</div>
		';
		return $content;
	}

	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function printContent() {
		echo $this->content;
	}

}


?>