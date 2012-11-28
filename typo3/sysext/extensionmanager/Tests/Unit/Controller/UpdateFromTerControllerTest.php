<?php
namespace TYPO3\CMS\Extensionmanager\Tests\Unit\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2012 Susanne Moog, <typo3@susannemoog.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Update from TER controller test
 *
 */
class UpdateFromTerControllerTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * Enable backup of global and system variables
	 *
	 * @var boolean
	 */
	protected $backupGlobals = TRUE;

	/**
	 * @test
	 * @return void
	 */
	public function updateExtensionListFromTerCallsUpdateExtListIfExtensionListIsEmpty() {
		$controllerMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Controller\\UpdateFromTerController', array('dummy'));
		$repositoryRepositoryMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\RepositoryRepository', array('findOneByUid'));
		$repositoryModelMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Repository', array('getLastUpdate','getExtensionCount'));
		$repositoryHelperMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Repository\\Helper', array('updateExtList'));
		$viewMock = $this->getAccessibleMock('TYPO3\\CMS\\Fluid\\View\\TemplateView', array('assign'));
		$requestMock = $this->getAccessibleMock('TYPO3\\CMS\\Extbase\\Mvc\\Request', array('hasArgument', 'getArgument'));
		$viewMock->expects($this->any())->method('assign')->will($this->returnValue($viewMock));
		$repositoryModelMock->expects($this->once())->method('getExtensionCount')->will($this->returnValue(0));
		$repositoryRepositoryMock->expects($this->once())->method('findOneByUid')->with(1)->will($this->returnValue($repositoryModelMock));
		$repositoryHelperMock->expects($this->once())->method('updateExtList');

		$controllerMock->_set('repositoryRepository', $repositoryRepositoryMock);
		$controllerMock->_set('repositoryHelper', $repositoryHelperMock);
		$controllerMock->_set('settings', array('repositoryUid' => 1));
		$controllerMock->_set('view', $viewMock);
		$controllerMock->_set('request', $requestMock);
		$controllerMock->updateExtensionListFromTerAction();
	}

	/**
	 * @test
	 * @return void
	 */
	public function updateExtensionListFromTerCallsUpdateExtListIfExtensionListIsNotEmpty() {
		$controllerMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Controller\\UpdateFromTerController', array('dummy'));
		$repositoryRepositoryMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\RepositoryRepository', array('findOneByUid'));
		$repositoryModelMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Repository', array('getLastUpdate','getExtensionCount'));
		$repositoryHelperMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Repository\\Helper', array('updateExtList'));
		$viewMock = $this->getAccessibleMock('TYPO3\\CMS\\Fluid\\View\\TemplateView', array('assign'));
		$requestMock = $this->getAccessibleMock('TYPO3\\CMS\\Extbase\\Mvc\\Request', array('hasArgument', 'getArgument'));
		$viewMock->expects($this->any())->method('assign')->will($this->returnValue($viewMock));
		$repositoryModelMock->expects($this->once())->method('getExtensionCount')->will($this->returnValue(1));
		$repositoryRepositoryMock->expects($this->once())->method('findOneByUid')->with(1)->will($this->returnValue($repositoryModelMock));
		$repositoryHelperMock->expects($this->never())->method('updateExtList');

		$controllerMock->_set('repositoryRepository', $repositoryRepositoryMock);
		$controllerMock->_set('repositoryHelper', $repositoryHelperMock);
		$controllerMock->_set('settings', array('repositoryUid' => 1));
		$controllerMock->_set('view', $viewMock);
		$controllerMock->_set('request', $requestMock);
		$controllerMock->updateExtensionListFromTerAction();
	}

	/**
	 * @test
	 * @return void
	 */
	public function updateExtensionListFromTerCallsUpdateExtListIfForceUpdateCheckIsSet() {
		$controllerMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Controller\\UpdateFromTerController', array('dummy'));
		$repositoryRepositoryMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Repository\\RepositoryRepository', array('findOneByUid'));
		$repositoryModelMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Domain\\Model\\Repository', array('getLastUpdate','getExtensionCount'));
		$repositoryHelperMock = $this->getAccessibleMock('TYPO3\\CMS\\Extensionmanager\\Utility\\Repository\\Helper', array('updateExtList'));
		$viewMock = $this->getAccessibleMock('TYPO3\\CMS\\Fluid\\View\\TemplateView', array('assign'));
		$requestMock = $this->getAccessibleMock('TYPO3\\CMS\\Extbase\\Mvc\\Request', array('hasArgument', 'getArgument'));
		$viewMock->expects($this->any())->method('assign')->will($this->returnValue($viewMock));
		$repositoryModelMock->expects($this->once())->method('getExtensionCount')->will($this->returnValue(1));
		$repositoryRepositoryMock->expects($this->once())->method('findOneByUid')->with(1)->will($this->returnValue($repositoryModelMock));
		$repositoryHelperMock->expects($this->once())->method('updateExtList');

		$controllerMock->_set('repositoryRepository', $repositoryRepositoryMock);
		$controllerMock->_set('repositoryHelper', $repositoryHelperMock);
		$controllerMock->_set('settings', array('repositoryUid' => 1));
		$controllerMock->_set('view', $viewMock);
		$controllerMock->_set('request', $requestMock);
		$controllerMock->updateExtensionListFromTerAction(TRUE);
	}

}


?>