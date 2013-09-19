<?php
namespace TYPO3\CMS\Install\Controller\Action;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Christian Kuhn <lolli@schwarzbu.ch>
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * General purpose controller action helper methods and bootstrap
 */
abstract class AbstractAction {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager = NULL;

	/**
	 * @var \TYPO3\CMS\Install\View\StandaloneView
	 * @inject
	 */
	protected $view = NULL;

	/**
	 * @var string Name of controller. One of the strings 'step', 'tool' or 'common'
	 */
	protected $controller = '';

	/**
	 * @var string Name of target action, set by controller
	 */
	protected $action = '';

	/**
	 * @var string Form token for CSRF protection
	 */
	protected $token = '';

	/**
	 * @var array Values in $_POST['install']
	 */
	protected $postValues = array();

	/**
	 * @var array<\TYPO3\CMS\Install\Status\StatusInterface> Optional status message from controller
	 */
	protected $messages = array();

	/**
	 * Initialize this action
	 *
	 * @return string content
	 */
	protected function initialize() {
		$viewRootPath = GeneralUtility::getFileAbsFileName('EXT:install/Resources/Private/');
		$controllerActionDirectoryName = ucfirst($this->controller);
		$mainTemplate = ucfirst($this->action);
		$this->view->setTemplatePathAndFilename($viewRootPath . 'Templates/Action/' . $controllerActionDirectoryName . '/' . $mainTemplate . '.html');
		$this->view->setLayoutRootPath($viewRootPath . 'Layouts/');
		$this->view->setPartialRootPath($viewRootPath . 'Partials/');
		$this->view
			// time is used in js and css as parameter to force loading of resources
			->assign('time', time())
			->assign('action', $this->action)
			->assign('controller', $this->controller)
			->assign('token', $this->token)
			->assign('context', $this->getContext())
			->assign('messages', $this->messages)
			->assign('typo3Version', TYPO3_version)
			->assign('siteName', $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']);
	}

	/**
	 * Set form protection token
	 *
	 * @param string $token Form protection token
	 * @return void
	 */
	public function setToken($token) {
		$this->token = $token;
	}

	/**
	 * Set action group. Either string 'step', 'tool' or 'common'
	 *
	 * @param string $controller Controller name
	 * @return void
	 */
	public function setController($controller) {
		$this->controller = $controller;
	}

	/**
	 * Set action name. This is usually similar to the class name,
	 * only for loginForm, the action is login
	 *
	 * @param string $action Name of target action for forms
	 * @return void
	 */
	public function setAction($action) {
		$this->action = $action;
	}

	/**
	 * Set POST form values of install tool
	 *
	 * @param array $postValues
	 * @return void
	 */
	public function setPostValues(array $postValues) {
		$this->postValues = $postValues;
	}

	/**
	 * Status messages from controller
	 *
	 * @param array<\TYPO3\CMS\Install\Status\StatusInterface> $messages
	 */
	public function setMessages(array $messages = array()) {
		$this->messages = $messages;
	}

	/**
	 * Return TRUE if dbal and adodb extension is loaded
	 *
	 * @return boolean TRUE if dbal and adodb is loaded
	 */
	protected function isDbalEnabled() {
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('adodb')
			&& \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('dbal')
		) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Context determines if the install tool is called within backend or standalone
	 *
	 * @return string Either 'standalone' or 'backend'
	 */
	protected function getContext() {
		$context = 'standalone';
		$formValues = GeneralUtility::_GP('install');
		if (isset($formValues['context'])) {
			$context = $formValues['context'] === 'backend' ? 'backend' : 'standalone';
		}
		return $context;
	}

	/**
	 * Get database instance.
	 * Will be initialized if it does not exist yet.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabase() {
		static $database;
		if (!is_object($database)) {
			/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $database */
			$database = $this->objectManager->get('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
			$database->setDatabaseUsername($GLOBALS['TYPO3_CONF_VARS']['DB']['username']);
			$database->setDatabasePassword($GLOBALS['TYPO3_CONF_VARS']['DB']['password']);
			$database->setDatabaseHost($GLOBALS['TYPO3_CONF_VARS']['DB']['host']);
			$database->setDatabasePort($GLOBALS['TYPO3_CONF_VARS']['DB']['port']);
			$database->setDatabaseSocket($GLOBALS['TYPO3_CONF_VARS']['DB']['socket']);
			$database->setDatabaseName($GLOBALS['TYPO3_CONF_VARS']['DB']['database']);
			$database->connectDB();
		}
		return $database;
	}

	/**
	 * Some actions like the database analyzer and the upgrade wizards need additional
	 * bootstrap actions performed.
	 *
	 * Those actions can potentially fatal if some old extension is loaded that triggers
	 * a fatal in ext_localconf or ext_tables code! Use only if really needed.
	 *
	 * @return void
	 */
	protected function loadExtLocalconfDatabaseAndExtTables() {
		\TYPO3\CMS\Core\Core\Bootstrap::getInstance()
			->loadTypo3LoadedExtAndExtLocalconf(FALSE)
			->applyAdditionalConfigurationSettings()
			->initializeTypo3DbGlobal()
			->loadExtensionTables(FALSE);
	}
}
?>