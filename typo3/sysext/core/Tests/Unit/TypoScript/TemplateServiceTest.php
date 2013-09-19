<?php
namespace TYPO3\CMS\Core\Tests\Unit\TypoScript;

/***************************************************************
 * Copyright notice
 *
 * (c) 2012-2013 Christian Kuhn <lolli@schwarzbu.ch>
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
 * Testcase for \TYPO3\CMS\Core\TypoScript\TemplateService
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class TemplateServiceTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * Enable backup of global and system variables
	 *
	 * @var boolean
	 */
	protected $backupGlobals = TRUE;

	/**
	 * @var \TYPO3\CMS\Core\TypoScript\TemplateService
	 */
	protected $templateService;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface|\TYPO3\CMS\Core\TypoScript\TemplateService
	 */
	protected $templateServiceMock;

	/**
	 * Sets up this test case.
	 *
	 * @return void
	 */
	protected function setUp() {
		$this->templateService = new \TYPO3\CMS\Core\TypoScript\TemplateService();
		$this->templateServiceMock = $this->getAccessibleMock('\\TYPO3\\CMS\\Core\\TypoScript\\TemplateService', array('dummy'));
	}

	/**
	 * Tears down this test case.
	 *
	 * @return void
	 */
	protected function tearDown() {
		unset($this->templateService);
		unset($this->templateServiceMock);
	}

	/**
	 * @test
	 */
	public function versionOlCallsVersionOlOfPageSelectClassWithGivenRow() {
		$row = array('foo');
		$GLOBALS['TSFE'] = new \stdClass();
		$sysPageMock = $this->getMock('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$sysPageMock->expects($this->once())->method('versionOL')->with('sys_template', $row);
		$GLOBALS['TSFE']->sys_page = $sysPageMock;
		$this->templateService->versionOL($row);
	}

	/**
	 * @test
	 */
	public function extensionStaticFilesAreNotProcessedIfNotExplicitlyRequested() {
		$identifier = uniqid('test');
		$GLOBALS['TYPO3_LOADED_EXT'] = array(
			$identifier => array(
				'ext_typoscript_setup.txt' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
					'core', 'Tests/Unit/TypoScript/Fixtures/ext_typoscript_setup.txt'
				),
			),
		);

		$this->templateService->runThroughTemplates(array(), 0);
		$this->assertFalse(
			in_array('test.Core.TypoScript = 1', $this->templateService->config)
		);
	}

	/**
	 * @test
	 */
	public function extensionStaticsAreProcessedIfExplicitlyRequested() {
		$identifier = uniqid('test');
		$GLOBALS['TYPO3_LOADED_EXT'] = array(
			$identifier => array(
				'ext_typoscript_setup.txt' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
					'core', 'Tests/Unit/TypoScript/Fixtures/ext_typoscript_setup.txt'
				),
			),
		);

		$this->templateService->setProcessExtensionStatics(TRUE);
		$this->templateService->runThroughTemplates(array(), 0);
		$this->assertTrue(
			in_array('test.Core.TypoScript = 1', $this->templateService->config)
		);
	}

	/**
	 * @test
	 */
	public function updateRootlineDataOverwritesOwnArrayData() {
		$originalRootline = array(
			0 => array('uid' => 2, 'title' => 'originalTitle'),
			1 => array('uid' => 3, 'title' => 'originalTitle2'),
		);

		$updatedRootline = array(
			0 => array('uid' => 1, 'title' => 'newTitle'),
			1 => array('uid' => 2, 'title' => 'newTitle2'),
			2 => array('uid' => 3, 'title' => 'newTitle3'),
		);

		$expectedRootline = array(
			0 => array('uid' => 2, 'title' => 'newTitle2'),
			1 => array('uid' => 3, 'title' => 'newTitle3'),
		);

		$this->templateServiceMock->_set('rootLine', $originalRootline);
		$this->templateServiceMock->updateRootlineData($updatedRootline);
		$this->assertEquals($expectedRootline, $this->templateServiceMock->_get('rootLine'));
	}

	/**
	 * @test
	 * @expectedException \RuntimeException
	 */
	public function updateRootlineDataWithInvalidNewRootlineThrowsException() {
		$originalRootline = array(
			0 => array('uid' => 2, 'title' => 'originalTitle'),
			1 => array('uid' => 3, 'title' => 'originalTitle2'),
		);

		$newInvalidRootline = array(
			0 => array('uid' => 1, 'title' => 'newTitle'),
			1 => array('uid' => 2, 'title' => 'newTitle2'),
		);

		$this->templateServiceMock->_set('rootLine', $originalRootline);
		$this->templateServiceMock->updateRootlineData($newInvalidRootline);
	}

}

?>