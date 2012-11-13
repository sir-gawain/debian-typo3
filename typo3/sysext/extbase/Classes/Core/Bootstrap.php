<?php
namespace TYPO3\CMS\Extbase\Core;

/***************************************************************
 *  Copyright notice
 *
 *  This class is a backport of the corresponding class of TYPO3 Flow.
 *  All credits go to the TYPO3 Flow team.
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
 * Creates a request an dispatches it to the controller which was specified
 * by TS Setup, flexForm and returns the content to the v4 framework.
 *
 * This class is the main entry point for extbase extensions.
 */
class Bootstrap implements \TYPO3\CMS\Extbase\Core\BootstrapInterface {

	/**
	 * Back reference to the parent content object
	 * This has to be public as it is set directly from TYPO3
	 *
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	public $cObj;

	/**
	 * The application context
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Core\Cache\CacheManager
	 */
	protected $cacheManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\Service
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 */
	protected $persistenceManager;

	/**
	 * Explicitly initializes all necessary Extbase objects by invoking the various initialize* methods.
	 *
	 * Usually this method is only called from unit tests or other applications which need a more fine grained control over
	 * the initialization and request handling process. Most other applications just call the run() method.
	 *
	 * @param array $configuration The TS configuration array
	 * @throws \RuntimeException
	 * @return void
	 * @see run()
	 * @api
	 */
	public function initialize($configuration) {
		if (!defined('TYPO3_cliMode') || TYPO3_cliMode !== TRUE) {
			if (!isset($configuration['extensionName']) || strlen($configuration['extensionName']) === 0) {
				throw new \RuntimeException('Invalid configuration: "extensionName" is not set', 1290623020);
			}
			if (!isset($configuration['pluginName']) || strlen($configuration['pluginName']) === 0) {
				throw new \RuntimeException('Invalid configuration: "pluginName" is not set', 1290623027);
			}
		}
		$this->initializeObjectManager();
		$this->initializeConfiguration($configuration);
		$this->configureObjectManager();
		$this->initializeCache();
		$this->initializeReflection();
		$this->initializePersistence();
	}

	/**
	 * Initializes the Object framework.
	 *
	 * @return void
	 * @see initialize()
	 */
	protected function initializeObjectManager() {
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
	}

	/**
	 * Initializes the Object framework.
	 *
	 * @param array $configuration
	 * @return void
	 * @see initialize()
	 */
	public function initializeConfiguration($configuration) {
		$this->configurationManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
		$contentObject = isset($this->cObj) ? $this->cObj : \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
		$this->configurationManager->setContentObject($contentObject);
		$this->configurationManager->setConfiguration($configuration);
	}

	/**
	 * Configures the object manager object configuration from
	 * config.tx_extbase.objects
	 *
	 * @return void
	 * @see initialize()
	 */
	public function configureObjectManager() {
		$typoScriptSetup = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (!is_array($typoScriptSetup['config.']['tx_extbase.']['objects.'])) {
			return;
		}
		$objectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\Container\\Container');
		foreach ($typoScriptSetup['config.']['tx_extbase.']['objects.'] as $classNameWithDot => $classConfiguration) {
			if (isset($classConfiguration['className'])) {
				$originalClassName = rtrim($classNameWithDot, '.');
				$objectContainer->registerImplementation($originalClassName, $classConfiguration['className']);
			}
		}
	}

	/**
	 * Initializes the cache framework
	 *
	 * @return void
	 * @see initialize()
	 */
	protected function initializeCache() {
		$this->cacheManager = $GLOBALS['typo3CacheManager'];
	}

	/**
	 * Initializes the Reflection Service
	 *
	 * @return void
	 * @see initialize()
	 */
	protected function initializeReflection() {
		$this->reflectionService = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Reflection\\Service');
		$this->reflectionService->setDataCache($this->cacheManager->getCache('extbase_reflection'));
		if (!$this->reflectionService->isInitialized()) {
			$this->reflectionService->initialize();
		}
	}

	/**
	 * Initializes the persistence framework
	 *
	 * @return void
	 * @see initialize()
	 */
	public function initializePersistence() {
		$this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
	}

	/**
	 * Runs the the Extbase Framework by resolving an appropriate Request Handler and passing control to it.
	 * If the Framework is not initialized yet, it will be initialized.
	 *
	 * @param string $content The content. Not used
	 * @param array $configuration The TS configuration array
	 * @return string $content The processed content
	 * @api
	 */
	public function run($content, $configuration) {
		$this->initialize($configuration);
		// CLI
		if (defined('TYPO3_cliMode') && TYPO3_cliMode === TRUE) {
			$content = $this->handleCommandLineRequest();
		} else {
			$content = $this->handleWebRequest();
		}
		return $content;
	}

	/**
	 * @return string
	 */
	protected function handleCommandLineRequest() {
		$commandLine = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
		$request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Cli\\RequestBuilder')->build(array_slice($commandLine, 1));
		$response = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Cli\\Response');
		$extensionName = $request->getControllerExtensionName();
		$this->configurationManager->setConfiguration(array('extensionName' => $extensionName));
		$this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Dispatcher')->dispatch($request, $response);
		$content = $response->getContent();
		$this->resetSingletons();
		return $content;
	}

	/**
	 * @return string
	 */
	protected function handleWebRequest() {
		$requestHandlerResolver = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\RequestHandlerResolver');
		$requestHandler = $requestHandlerResolver->resolveRequestHandler();
		$response = $requestHandler->handleRequest();
		// If response is NULL after handling the request we need to stop
		// This happens for instance, when a USER object was converted to a USER_INT
		// @see TYPO3\CMS\Extbase\Mvc\Web\FrontendRequestHandler::handleRequest()
		if ($response === NULL) {
			$this->reflectionService->shutdown();
			return '';
		}
		if (count($response->getAdditionalHeaderData()) > 0) {
			$GLOBALS['TSFE']->additionalHeaderData[] = implode(chr(10), $response->getAdditionalHeaderData());
		}
		$response->sendHeaders();
		$content = $response->getContent();
		$this->resetSingletons();
		return $content;
	}

	/**
	 * Resets global singletons for the next plugin
	 *
	 * @return void
	 */
	protected function resetSingletons() {
		$this->persistenceManager->persistAll();
		$this->reflectionService->shutdown();
	}

	/**
	 * This method forwards the call to run(). This method is invoked by the mod.php
	 * function of TYPO3.
	 *
	 * @param string $moduleSignature
	 * @throws \RuntimeException
	 * @return boolean TRUE, if the request request could be dispatched
	 * @see run()
	 */
	public function callModule($moduleSignature) {
		if (!isset($GLOBALS['TBE_MODULES']['_configuration'][$moduleSignature])) {
			return FALSE;
		}
		$moduleConfiguration = $GLOBALS['TBE_MODULES']['_configuration'][$moduleSignature];
		// Check permissions and exit if the user has no permission for entry
		$GLOBALS['BE_USER']->modAccess($moduleConfiguration, TRUE);
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id')) {
			// Check page access
			$permClause = $GLOBALS['BE_USER']->getPagePermsClause(TRUE);
			$access = is_array(\TYPO3\CMS\Backend\Utility\BackendUtility::readPageAccess((int) \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id'), $permClause));
			if (!$access) {
				throw new \RuntimeException('You don\'t have access to this page', 1289917924);
			}
		}
		// BACK_PATH is the path from the typo3/ directory from within the
		// directory containing the controller file. We are using mod.php dispatcher
		// and thus we are already within typo3/ because we call typo3/mod.php
		$GLOBALS['BACK_PATH'] = '';
		$configuration = array(
			'extensionName' => $moduleConfiguration['extensionName'],
			'pluginName' => $moduleSignature
		);
		if (isset($moduleConfiguration['vendorName'])) {
			$configuration['vendorName'] = $moduleConfiguration['vendorName'];
		}
		$content = $this->run('', $configuration);
		print $content;
		return TRUE;
	}

}


?>