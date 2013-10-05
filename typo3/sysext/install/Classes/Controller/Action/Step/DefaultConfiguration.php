<?php
namespace TYPO3\CMS\Install\Controller\Action\Step;

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

use TYPO3\CMS\Install\Controller\Action;

/**
 * Set production defaults
 */
class DefaultConfiguration extends Action\AbstractAction implements StepInterface {

	/**
	 * Set defaults of auto configuration, mark installation as completed
	 *
	 * @return array<\TYPO3\CMS\Install\Status\StatusInterface>
	 */
	public function execute() {
		/** @var \TYPO3\CMS\Install\Configuration\FeatureManager $featureManager */
		$featureManager = $this->objectManager->get('TYPO3\\CMS\\Install\\Configuration\\FeatureManager');
		// Get best matching configuration presets
		$configurationValues = $featureManager->getBestMatchingConfigurationForAllFeatures();

		// Setting SYS/isInitialInstallationInProgress to FALSE marks this instance installation as complete
		$configurationValues['SYS/isInitialInstallationInProgress'] = FALSE;

		/** @var $configurationManager \TYPO3\CMS\Core\Configuration\ConfigurationManager */
		$configurationManager = $this->objectManager->get('TYPO3\\CMS\\Core\\Configuration\\ConfigurationManager');
		$configurationManager->setLocalConfigurationValuesByPathValuePairs($configurationValues);

		/** @var \TYPO3\CMS\Install\Service\SessionService $session */
		$session = $this->objectManager->get('TYPO3\\CMS\\Install\\Service\\SessionService');
		$session->destroySession();

		/** @var $formProtection \TYPO3\CMS\Core\FormProtection\InstallToolFormProtection */
		$formProtection = \TYPO3\CMS\Core\FormProtection\FormProtectionFactory::get(
			'TYPO3\\CMS\\Core\\FormProtection\\InstallToolFormProtection'
		);
		$formProtection->clean();

		// @TODO: This and similar code in ToolController should be moved to enable install file service
		$enableInstallToolFile = PATH_site . 'typo3conf/ENABLE_INSTALL_TOOL';
		if (is_file($enableInstallToolFile) && trim(file_get_contents($enableInstallToolFile)) !== 'KEEP_FILE') {
			unlink($enableInstallToolFile);
		}

		\TYPO3\CMS\Core\Utility\HttpUtility::redirect('../../../index.php', \TYPO3\CMS\Core\Utility\HttpUtility::HTTP_STATUS_303);
	}

	/**
	 * Step needs to be executed if 'isInitialInstallationInProgress' is set to TRUE in LocalConfiguration
	 *
	 * @return boolean
	 */
	public function needsExecution() {
		$result = FALSE;
		if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['isInitialInstallationInProgress'])
			&& $GLOBALS['TYPO3_CONF_VARS']['SYS']['isInitialInstallationInProgress'] === TRUE
		) {
			$result = TRUE;
		}
		return $result;
	}

	/**
	 * Render this step
	 *
	 * @return string
	 */
	public function handle() {
		$this->initializeHandle();
		return $this->view->render();
	}
}
