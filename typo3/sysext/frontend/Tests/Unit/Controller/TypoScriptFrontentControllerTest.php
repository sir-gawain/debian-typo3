<?php
namespace TYPO3\CMS\Frontend\Tests\Unit\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
 *
 * @package TYPO3
 * @subpackage tslib
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TypoScriptFrontendControllerTest extends \tx_phpunit_testcase {

	/**
	 * Enable backup of global and system variables
	 *
	 * @var boolean
	 */
	protected $backupGlobals = TRUE;

	/**
	 * Exclude TYPO3_DB from backup/ restore of $GLOBALS
	 * because resource types cannot be handled during serializing
	 *
	 * @var array
	 */
	protected $backupGlobalsBlacklist = array('TYPO3_DB');

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject|\Tx_Phpunit_Interface_AccessibleObject|\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = $this->getAccessibleMock('\\TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', array('dummy'), array(), '', FALSE);
		$this->fixture->TYPO3_CONF_VARS = $GLOBALS['TYPO3_CONF_VARS'];
		$this->fixture->TYPO3_CONF_VARS['SYS']['encryptionKey'] = '170928423746123078941623042360abceb12341234231';
	}

	public function tearDown() {
		unset($this->fixture);
	}

	////////////////////////////////
	// Tests concerning rendering content
	////////////////////////////////
	/**
	 * @test
	 */
	public function headerAndFooterMarkersAreReplacedDuringIntProcessing() {
		$GLOBALS['TSFE'] = $this->setupTsfeMockForHeaderFooterReplacementCheck();
		$GLOBALS['TSFE']->INTincScript();
		$this->assertContains('headerData', $GLOBALS['TSFE']->content);
		$this->assertContains('footerData', $GLOBALS['TSFE']->content);
	}

	/**
	 * This is the callback that mimics a USER_INT extension
	 */
	public function INTincScript_processCallback() {
		$GLOBALS['TSFE']->additionalHeaderData[] = 'headerData';
		$GLOBALS['TSFE']->additionalFooterData[] = 'footerData';
	}

	/**
	 * Setup a tslib_fe object only for testing the header and footer
	 * replacement during USER_INT rendering
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function setupTsfeMockForHeaderFooterReplacementCheck() {
		/** @var \PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController $tsfe */
		$tsfe = $this->getMock('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController', array(
			'INTincScript_process',
			'INTincScript_includeLibs',
			'INTincScript_loadJSCode',
			'setAbsRefPrefix'
		), array(), '', FALSE);
		$tsfe->expects($this->once())->method('INTincScript_process')->will($this->returnCallback(array($this, 'INTincScript_processCallback')));
		$tsfe->content = file_get_contents(__DIR__ . '/Fixtures/renderedPage.html');
		$tsfe->config['INTincScript_ext']['divKey'] = '679b52796e75d474ccbbed486b6837ab';
		$tsfe->config['INTincScript'] = array('INT_SCRIPT.679b52796e75d474ccbbed486b6837ab' => array());
		$GLOBALS['TT'] = new \TYPO3\CMS\Core\TimeTracker\NullTimeTracker();
		return $tsfe;
	}

	////////////////////////////////
	// Tests concerning codeString
	////////////////////////////////
	/**
	 * @test
	 */
	public function codeStringForNonEmptyStringReturns10CharacterHashAndCodedString() {
		$this->assertRegExp('/^[0-9a-f]{10}:[a-zA-Z0-9+=\\/]+$/', $this->fixture->codeString('Hello world!'));
	}

	/**
	 * @test
	 */
	public function decodingCodedStringReturnsOriginalString() {
		$clearText = 'Hello world!';
		$this->assertEquals($clearText, $this->fixture->codeString($this->fixture->codeString($clearText), TRUE));
	}

	//////////////////////
	// Tests concerning sL
	//////////////////////
	/**
	 * @test
	 */
	public function localizationReturnsUnchangedStringIfNotLocallangLabel() {
		$string = uniqid();
		$this->assertEquals($string, $this->fixture->sL($string));
	}

	//////////////////////////////////////////
	// Tests concerning roundTripCryptString
	//////////////////////////////////////////
	/**
	 * @test
	 */
	public function roundTripCryptStringCreatesStringWithSameLengthAsInputString() {
		$clearText = 'Hello world!';
		$this->assertEquals(strlen($clearText), strlen($this->fixture->_callRef('roundTripCryptString', $clearText)));
	}

	/**
	 * @test
	 */
	public function roundTripCryptStringCreatesResultDifferentFromInputString() {
		$clearText = 'Hello world!';
		$this->assertNotEquals($clearText, $this->fixture->_callRef('roundTripCryptString', $clearText));
	}

	/**
	 * @test
	 */
	public function roundTripCryptStringAppliedTwoTimesReturnsOriginalString() {
		$clearText = 'Hello world!';
		$refValue = $this->fixture->_callRef('roundTripCryptString', $clearText);
		$this->assertEquals($clearText, $this->fixture->_callRef('roundTripCryptString', $refValue));
	}

}

?>