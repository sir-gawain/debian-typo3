<?php
namespace TYPO3\CMS\Backend\Tests\Unit\Utility;

/***************************************************************
 * Copyright notice
 *
 * (c) 2009-2013 Oliver Klee (typo3-coding@oliverklee.de)
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

use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Testcase for \TYPO3\CMS\Core\Utility\BackendUtility
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackendUtilityTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\CMS\Backend\Utility\BackendUtility
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new BackendUtility();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	///////////////////////////////////////
	// Tests concerning calcAge
	///////////////////////////////////////
	/**
	 * Data provider for calcAge function
	 *
	 * @return array
	 */
	public function calcAgeDataProvider() {
		return array(
			'Single year' => array(
				'seconds' => 60 * 60 * 24 * 365,
				'expectedLabel' => '1 year'
			),
			'Plural years' => array(
				'seconds' => 60 * 60 * 24 * 365 * 2,
				'expectedLabel' => '2 yrs'
			),
			'Single negative year' => array(
				'seconds' => 60 * 60 * 24 * 365 * -1,
				'expectedLabel' => '-1 year'
			),
			'Plural negative years' => array(
				'seconds' => 60 * 60 * 24 * 365 * 2 * -1,
				'expectedLabel' => '-2 yrs'
			),
			'Single day' => array(
				'seconds' => 60 * 60 * 24,
				'expectedLabel' => '1 day'
			),
			'Plural days' => array(
				'seconds' => 60 * 60 * 24 * 2,
				'expectedLabel' => '2 days'
			),
			'Single negative day' => array(
				'seconds' => 60 * 60 * 24 * -1,
				'expectedLabel' => '-1 day'
			),
			'Plural negative days' => array(
				'seconds' => 60 * 60 * 24 * 2 * -1,
				'expectedLabel' => '-2 days'
			),
			'Single hour' => array(
				'seconds' => 60 * 60,
				'expectedLabel' => '1 hour'
			),
			'Plural hours' => array(
				'seconds' => 60 * 60 * 2,
				'expectedLabel' => '2 hrs'
			),
			'Single negative hour' => array(
				'seconds' => 60 * 60 * -1,
				'expectedLabel' => '-1 hour'
			),
			'Plural negative hours' => array(
				'seconds' => 60 * 60 * 2 * -1,
				'expectedLabel' => '-2 hrs'
			),
			'Single minute' => array(
				'seconds' => 60,
				'expectedLabel' => '1 min'
			),
			'Plural minutes' => array(
				'seconds' => 60 * 2,
				'expectedLabel' => '2 min'
			),
			'Single negative minute' => array(
				'seconds' => 60 * -1,
				'expectedLabel' => '-1 min'
			),
			'Plural negative minutes' => array(
				'seconds' => 60 * 2 * -1,
				'expectedLabel' => '-2 min'
			),
			'Zero seconds' => array(
				'seconds' => 0,
				'expectedLabel' => '0 min'
			)
		);
	}

	/**
	 * @test
	 * @dataProvider calcAgeDataProvider
	 */
	public function calcAgeReturnsExpectedValues($seconds, $expectedLabel) {
		$this->assertSame($expectedLabel, $this->fixture->calcAge($seconds));
	}

	///////////////////////////////////////
	// Tests concerning getProcessedValue
	///////////////////////////////////////
	/**
	 * @test
	 * @see http://bugs.typo3.org/view.php?id=11875
	 */
	public function getProcessedValueForZeroStringIsZero() {
		$this->assertEquals('0', $this->fixture->getProcessedValue('tt_content', 'header', '0'));
	}

	/**
	 * @test
	 */
	public function getProcessedValueForGroup() {
		$this->assertSame('1, 2', $this->fixture->getProcessedValue('tt_content', 'multimedia', '1,2'));
	}

	/**
	 * @test
	 */
	public function getProcessedValueForGroupWithOneAllowedTable() {
		/** @var \PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Backend\Utility\BackendUtility $fixture */
		$fixture = $this->getMock('TYPO3\\CMS\\Backend\\Utility\\BackendUtility', array('getRecordWSOL'));
		$fixture->staticExpects($this->at(0))->method('getRecordWSOL')->will($this->returnValue(array('title' => 'Page 1')));
		$fixture->staticExpects($this->at(1))->method('getRecordWSOL')->will($this->returnValue(array('title' => 'Page 2')));
		$this->assertSame('Page 1, Page 2', $fixture->getProcessedValue('tt_content', 'pages', '1,2'));
	}

	/**
	 * @test
	 */
	public function getProcessedValueForGroupWithMultipleAllowedTables() {
		/** @var \PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Backend\Utility\BackendUtility $fixture */
		$fixture = $this->getMock('TYPO3\\CMS\\Backend\\Utility\\BackendUtility', array('getRecordWSOL'));
		$fixture->staticExpects($this->at(0))->method('getRecordWSOL')->will($this->returnValue(array('title' => 'Page 1')));
		$fixture->staticExpects($this->at(1))->method('getRecordWSOL')->will($this->returnValue(array('header' => 'Content 2')));
		$this->assertSame('Page 1, Content 2', $fixture->getProcessedValue('sys_category', 'items', 'pages_1,tt_content_2'));
	}

	/**
	 * Tests concerning getCommenSelectFields
	 */

	/**
	 * Data provider for getCommonSelectFieldsReturnsCorrectFields
	 *
	 * @return array The test data with $table, $prefix, $presetFields, $tca, $expectedFields
	 */
	public function getCommonSelectFieldsReturnsCorrectFieldsDataProvider() {
		return array(
			'only uid' => array(
				'table' => 'test_table',
				'prefix' => '',
				'presetFields' => array(),
				'tca' => array(),
				'expectedFields' => 'uid'
			),
			'label set' => array(
				'table' => 'test_table',
				'prefix' => '',
				'presetFields' => array(),
				'tca' => array(
					'ctrl' => array(
						'label' => 'label'
					)
				),
				'expectedFields' => 'uid,label'
			),
			'label_alt set' => array(
				'table' => 'test_table',
				'prefix' => '',
				'presetFields' => array(),
				'tca' => array(
					'ctrl' => array(
						'label_alt' => 'label,label2'
					)
				),
				'expectedFields' => 'uid,label,label2'
			),
			'versioningWS set' => array(
				'table' => 'test_table',
				'prefix' => '',
				'presetFields' => array(),
				'tca' => array(
					'ctrl' => array(
						'versioningWS' => '2'
					)
				),
				'expectedFields' => 'uid,t3ver_id,t3ver_state,t3ver_wsid,t3ver_count'
			),
			'selicon_field set' => array(
				'table' => 'test_table',
				'prefix' => '',
				'presetFields' => array(),
				'tca' => array(
					'ctrl' => array(
						'selicon_field' => 'field'
					)
				),
				'expectedFields' => 'uid,field'
			),
			'typeicon_column set' => array(
				'table' => 'test_table',
				'prefix' => '',
				'presetFields' => array(),
				'tca' => array(
					'ctrl' => array(
						'typeicon_column' => 'field'
					)
				),
				'expectedFields' => 'uid,field'
			),
			'enablecolumns set' => array(
				'table' => 'test_table',
				'prefix' => '',
				'presetFields' => array(),
				'tca' => array(
					'ctrl' => array(
						'enablecolumns' => array(
							'disabled' => 'hidden',
							'starttime' => 'start',
							'endtime' => 'stop',
							'fe_group' => 'groups'
						)
					)
				),
				'expectedFields' => 'uid,hidden,start,stop,groups'
			),
			'label set to uid' => array(
				'table' => 'test_table',
				'prefix' => '',
				'presetFields' => array(),
				'tca' => array(
					'ctrl' => array(
						'label' => 'uid'
					)
				),
				'expectedFields' => 'uid'
			)
		);
	}

	/**
	 * @test
	 * @dataProvider getCommonSelectFieldsReturnsCorrectFieldsDataProvider
	 */
	public function getCommonSelectFieldsReturnsCorrectFields($table, $prefix = '', array $presetFields, array $tca, $expectedFields = '') {
		$tcaBackup = $GLOBALS['TCA'][$table];
		unset($GLOBALS['TCA'][$table]);
		$GLOBALS['TCA'][$table] = $tca;
		$selectFields = $this->fixture->getCommonSelectFields($table, $prefix, $presetFields);
		unset($GLOBALS['TCA'][$table]);
		$GLOBALS['TCA'][$table] = $tcaBackup;
		$this->assertEquals($selectFields, $expectedFields);
	}

	/**
	 * Tests concerning getLabelFromItemlist
	 */

	/**
	 * Data provider for getLabelFromItemlistReturnsCorrectFields
	 *
	 * @return array The test data with $table, $col, $key, $expectedLabel
	 */
	public function getLabelFromItemlistReturnsCorrectFieldsDataProvider() {
		return array(
			'item set' => array(
				'table' => 'tt_content',
				'col' => 'menu_type',
				'key' => '1',
				'tca' => array(
					'columns' => array(
						'menu_type' => array(
							'config' => array(
								'items' => array(
									array('Item 1', '0'),
									array('Item 2', '1'),
									array('Item 3', '3')
								)
							)
						)
					)
				),
				'expectedLabel' => 'Item 2'
			),
			'item set twice' => array(
				'table' => 'tt_content',
				'col' => 'menu_type',
				'key' => '1',
				'tca' => array(
					'columns' => array(
						'menu_type' => array(
							'config' => array(
								'items' => array(
									array('Item 1', '0'),
									array('Item 2a', '1'),
									array('Item 2b', '1'),
									array('Item 3', '3')
								)
							)
						)
					)
				),
				'expectedLabel' => 'Item 2a'
			),
			'item not found' => array(
				'table' => 'tt_content',
				'col' => 'menu_type',
				'key' => '5',
				'tca' => array(
					'columns' => array(
						'menu_type' => array(
							'config' => array(
								'items' => array(
									array('Item 1', '0'),
									array('Item 2', '1'),
									array('Item 3', '2')
								)
							)
						)
					)
				),
				'expectedLabel' => NULL
			)
		);
	}

	/**
	 * @test
	 * @dataProvider getLabelFromItemlistReturnsCorrectFieldsDataProvider
	 */
	public function getLabelFromItemlistReturnsCorrectFields($table, $col = '', $key = '', array $tca, $expectedLabel = '') {
		$tcaBackup = $GLOBALS['TCA'][$table];
		unset($GLOBALS['TCA'][$table]);
		$GLOBALS['TCA'][$table] = $tca;
		$label = $this->fixture->getLabelFromItemlist($table, $col, $key);
		unset($GLOBALS['TCA'][$table]);
		$GLOBALS['TCA'][$table] = $tcaBackup;
		$this->assertEquals($label, $expectedLabel);
	}

	/**
	 * Tests concerning getLabelFromItemListMerged
	 */

	/**
	 * Data provider for getLabelFromItemListMerged
	 *
	 * @return array The test data with $pageId, $table, $column, $key, $expectedLabel
	 */
	public function getLabelFromItemListMergedReturnsCorrectFieldsDataProvider() {
		return array(
			'no field found' => array(
				'pageId' => '123',
				'table' => 'tt_content',
				'col' => 'menu_type',
				'key' => '10',
				'tca' => array(
					'columns' => array(
						'menu_type' => array(
							'config' => array(
								'items' => array(
									array('Item 1', '0'),
									array('Item 2', '1'),
									array('Item 3', '3')
								)
							)
						)
					)
				),
				'expectedLabel' => ''
			),
			'no tsconfig set' => array(
				'pageId' => '123',
				'table' => 'tt_content',
				'col' => 'menu_type',
				'key' => '1',
				'tca' => array(
					'columns' => array(
						'menu_type' => array(
							'config' => array(
								'items' => array(
									array('Item 1', '0'),
									array('Item 2', '1'),
									array('Item 3', '3')
								)
							)
						)
					)
				),
				'expectedLabel' => 'Item 2'
			)
		);
	}

	/**
	 * @test
	 * @dataProvider getLabelFromItemListMergedReturnsCorrectFieldsDataProvider
	 */
	public function getLabelFromItemListMergedReturnsCorrectFields($pageId, $table, $column = '', $key = '', array $tca, $expectedLabel = '') {
		$tcaBackup = $GLOBALS['TCA'][$table];
		unset($GLOBALS['TCA'][$table]);
		$GLOBALS['TCA'][$table] = $tca;
		$label = $this->fixture->getLabelFromItemListMerged($pageId, $table, $column, $key);
		unset($GLOBALS['TCA'][$table]);
		$GLOBALS['TCA'][$table] = $tcaBackup;
		$this->assertEquals($label, $expectedLabel);
	}

	/**
	 * Tests concerning getFuncCheck
	 */

	/**
	 * @test
	 */
	public function getFuncCheckReturnsInputTagWithValueAttribute() {
		$this->assertStringMatchesFormat('<input %Svalue="1"%S/>', BackendUtility::getFuncCheck('params', 'test', TRUE));
	}

	/**
	 * Tests concerning getExcludeFields
	 */

	/**
	 * @return array
	 */
	public function getExcludeFieldsDataProvider() {
		return array(
			'getExcludeFields does not return fields not configured as exclude field' => array(
				array(
					'tx_foo' => array(
						'ctrl' => array(
							'title' => 'foo',
						),
						'columns' => array(
							'bar' => array(
								'label' => 'bar',
								'exclude' => 1
							),
							'baz' => array(
								'label' => 'bar',
							),
						)
					)
				),
				array(
					array(
						'foo: bar',
						'tx_foo:bar',
					),
				)
			),
			'getExcludeFields returns fields from root level tables if root level restriction should be ignored' => array(
				array(
					'tx_foo' => array(
						'ctrl' => array(
							'title' => 'foo',
							'rootLevel' => TRUE,
							'security' => array(
								'ignoreRootLevelRestriction' => TRUE,
							),
						),
						'columns' => array(
							'bar' => array(
								'label' => 'bar',
								'exclude' => 1
							),
						)
					)
				),
				array(
					array(
						'foo: bar',
						'tx_foo:bar',
					),
				)
			),
			'getExcludeFields does not return fields from root level tables' => array(
				array(
					'tx_foo' => array(
						'ctrl' => array(
							'title' => 'foo',
							'rootLevel' => TRUE,
						),
						'columns' => array(
							'bar' => array(
								'label' => 'bar',
								'exclude' => 1
							),
						)
					)
				),
				array()
			),
			'getExcludeFields does not return fields from admin only level tables' => array(
				array(
					'tx_foo' => array(
						'ctrl' => array(
							'title' => 'foo',
							'adminOnly' => TRUE,
						),
						'columns' => array(
							'bar' => array(
								'label' => 'bar',
								'exclude' => 1
							),
						)
					)
				),
				array()
			),
			'getExcludeFields sorts tables and properties with flexform fields properly' => array(
				array(
					'tx_foo' => array(
						'ctrl' => array(
							'title' => 'foo'
						),
						'columns' => array(
							'foo' => array(
								'label' => 'foo',
								'exclude' => 1
							),
							'bar' => array(
								'label' => 'bar',
								'exclude' => 1
							),
							'abarfoo' => array(
								'label' => 'abarfoo',
								'config' => array(
									'type' => 'flex',
									'ds' => array(
										'*,dummy' => '<?xml version="1.0" encoding="utf-8"?>
<T3DataStructure>
	<sheets>
		<sGeneral>
			<ROOT>
				<type>array</type>
				<el>
					<xmlTitle>
						<TCEforms>
							<exclude>1</exclude>
							<label>The Title:</label>
							<config>
								<type>input</type>
								<size>48</size>
							</config>
						</TCEforms>
					</xmlTitle>
				</el>
			</ROOT>
		</sGeneral>
	</sheets>
</T3DataStructure>'
									)
								)
							)
						)
					),
					'tx_foobar' => array(
						'ctrl' => array(
							'title' => 'foobar'
						),
						'columns' => array(
							'foo' => array(
								'label' => 'foo',
								'exclude' => 1
							),
							'bar' => array(
								'label' => 'bar',
								'exclude' => 1
							)
						)
					),
					'tx_bar' => array(
						'ctrl' => array(
							'title' => 'bar'
						),
						'columns' => array(
							'foo' => array(
								'label' => 'foo',
								'exclude' => 1
							),
							'bar' => array(
								'label' => 'bar',
								'exclude' => 1
							)
						)
					)
				),
				array(
					array(
						'bar: bar',
						'tx_bar:bar'
					),
					array(
						'bar: foo',
						'tx_bar:foo'
					),
					array(
						'abarfoo dummy: The Title:',
						'tx_foo:abarfoo;dummy;sGeneral;xmlTitle'
					),
					array(
						'foo: bar',
						'tx_foo:bar'
					),
					array(
						'foo: foo',
						'tx_foo:foo'
					),
					array(
						'foobar: bar',
						'tx_foobar:bar'
					),
					array(
						'foobar: foo',
						'tx_foobar:foo'
					),
				)
			)
		);
	}

	/**
	 * @param $tca
	 * @param $expected
	 *
	 * @test
	 * @dataProvider getExcludeFieldsDataProvider
	 */
	public function getExcludeFieldsReturnsCorrectFieldList($tca, $expected) {
		$GLOBALS['TCA'] = $tca;
		$this->assertSame($expected, BackendUtility::getExcludeFields());
	}

	/**
	 * Tests concerning viewOnClick
	 */

	/**
	 * @test
	 */
	public function viewOnClickReturnsOnClickCodeWithAlternativeUrl() {
		$alternativeUrl = 'https://typo3.org/about/typo3-the-cms/the-history-of-typo3/#section';
		$onclickCode = 'var previewWin = window.open(\'' . $alternativeUrl . '\',\'newTYPO3frontendWindow\');';
		$this->assertStringMatchesFormat($onclickCode, BackendUtility::viewOnClick(NULL, NULL, NULL, NULL, $alternativeUrl, NULL, FALSE));
	}

	/**
	 * Tests concerning replaceMarkersInWhereClause
	 */

	/**
	 * @return array
	 */
	public function replaceMarkersInWhereClauseDataProvider() {
		return array(
			'replaceMarkersInWhereClause replaces record field marker with quoted string' => array(
				' AND dummytable.title=\'###REC_FIELD_dummyfield###\'',
				array(
					'_THIS_ROW' => array(
						'dummyfield' => 'Hello World'
					)
				),
				' AND dummytable.title=\'Hello World\''
			),
			'replaceMarkersInWhereClause replaces record field marker with fullquoted string' => array(
				' AND dummytable.title=###REC_FIELD_dummyfield###',
				array(
					'_THIS_ROW' => array(
						'dummyfield' => 'Hello World'
					)
				),
				' AND dummytable.title=\'Hello World\''
			),
			'replaceMarkersInWhereClause replaces multiple record field markers' => array(
				' AND dummytable.title=\'###REC_FIELD_dummyfield###\' AND dummytable.pid=###REC_FIELD_pid###',
				array(
					'_THIS_ROW' => array(
						'dummyfield' => 'Hello World',
						'pid' => 42
					)
				),
				' AND dummytable.title=\'Hello World\' AND dummytable.pid=\'42\''
			),
			'replaceMarkersInWhereClause replaces current pid with integer' => array(
				' AND dummytable.uid=###CURRENT_PID###',
				array(
					'_CURRENT_PID' => 42
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces current pid with string' => array(
				' AND dummytable.uid=###CURRENT_PID###',
				array(
					'_CURRENT_PID' => '42string'
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces current record uid with integer' => array(
				' AND dummytable.uid=###THIS_UID###',
				array(
					'_THIS_UID' => 42
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces current record uid with string' => array(
				' AND dummytable.uid=###THIS_UID###',
				array(
					'_THIS_UID' => '42string'
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces current record cid with integer' => array(
				' AND dummytable.uid=###THIS_CID###',
				array(
					'_THIS_CID' => 42
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces current record cid with string' => array(
				' AND dummytable.uid=###THIS_CID###',
				array(
					'_THIS_CID' => '42string'
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces storage pid with integer' => array(
				' AND dummytable.uid=###STORAGE_PID###',
				array(
					'_STORAGE_PID' => 42
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces storage pid with string' => array(
				' AND dummytable.uid=###STORAGE_PID###',
				array(
					'_STORAGE_PID' => '42string'
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces siteroot uid with integer' => array(
				' AND dummytable.uid=###SITEROOT###',
				array(
					'_SITEROOT' => 42
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces siteroot uid with string' => array(
				' AND dummytable.uid=###SITEROOT###',
				array(
					'_SITEROOT' => '42string'
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces page tsconfig id with integer' => array(
				' AND dummytable.uid=###PAGE_TSCONFIG_ID###',
				array(
					'dummyfield' => array(
						'PAGE_TSCONFIG_ID' => 42
					)
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces page tsconfig id with string' => array(
				' AND dummytable.uid=###PAGE_TSCONFIG_ID###',
				array(
					'dummyfield' => array(
						'PAGE_TSCONFIG_ID' => '42string'
					)
				),
				' AND dummytable.uid=42'
			),
			'replaceMarkersInWhereClause replaces page tsconfig id list' => array(
				' AND dummytable.uid IN (###PAGE_TSCONFIG_IDLIST###)',
				array(
					'dummyfield' => array(
						'PAGE_TSCONFIG_IDLIST' => '1,a,2,b,3,c'
					)
				),
				' AND dummytable.uid IN (1,0,2,0,3,0)'
			),
			'replaceMarkersInWhereClause replaces page tsconfig id list with empty string' => array(
				' AND dummytable.uid IN (###PAGE_TSCONFIG_IDLIST###)',
				array(
					'dummyfield' => array(
						'PAGE_TSCONFIG_IDLIST' => ''
					)
				),
				' AND dummytable.uid IN (0)'
			),
			'replaceMarkersInWhereClause replaces page tsconfig string' => array(
				' AND dummytable.title=\'###PAGE_TSCONFIG_STR###\'',
				array(
					'dummyfield' => array(
						'PAGE_TSCONFIG_STR' => '42'
					)
				),
				' AND dummytable.title=\'42\''
			),
			'replaceMarkersInWhereClause replaces all markers' => array(
				' AND dummytable.title=\'###REC_FIELD_dummyfield###\'' .
				' AND dummytable.uid=###REC_FIELD_uid###' .
				' AND dummytable.pid=###CURRENT_PID###' .
				' AND dummytable.l18n_parent=###THIS_UID###' .
				' AND dummytable.cid=###THIS_CID###' .
				' AND dummytable.storage_pid=###STORAGE_PID###' .
				' AND dummytable.siteroot=###SITEROOT###' .
				' AND dummytable.config_uid=###PAGE_TSCONFIG_ID###' .
				' AND dummytable.idlist IN (###PAGE_TSCONFIG_IDLIST###)' .
				' AND dummytable.string=\'###PAGE_TSCONFIG_STR###\'',
				array(
					'_THIS_ROW' => array(
						'dummyfield' => 'Hello World',
						'uid' => 42
					),
					'_CURRENT_PID' => '1',
					'_THIS_UID' => 2,
					'_THIS_CID' => 3,
					'_STORAGE_PID' => 4,
					'_SITEROOT' => 5,
					'dummyfield' => array(
						'PAGE_TSCONFIG_ID' => 6,
						'PAGE_TSCONFIG_IDLIST' => '1,2,3',
						'PAGE_TSCONFIG_STR' => 'string'
					)
				),
				' AND dummytable.title=\'Hello World\' AND dummytable.uid=\'42\' AND dummytable.pid=1' .
				' AND dummytable.l18n_parent=2 AND dummytable.cid=3 AND dummytable.storage_pid=4' .
				' AND dummytable.siteroot=5 AND dummytable.config_uid=6 AND dummytable.idlist IN (1,2,3)' .
				' AND dummytable.string=\'string\'',
			),
		);
	}

	/**
	 * @test
	 * @dataProvider replaceMarkersInWhereClauseDataProvider
	 */
	public function replaceMarkersInWhereClauseReturnsValidWhereClause($whereClause, $tsConfig, $expected) {
		$this->assertSame($expected, BackendUtility::replaceMarkersInWhereClause($whereClause, 'dummytable', 'dummyfield', $tsConfig));
	}

	/**
	 * @test
	 */
	public function getModTSconfigIgnoresValuesFromUserTsConfigIfNoSet() {
		$completeConfiguration = array(
			'value' => 'bar',
			'properties' => array(
				'permissions.' => array(
					'file.' => array(
						'default.' => array('readAction' => '1'),
						'1.' => array('writeAction' => '1'),
						'0.' => array('readAction' => '0'),
					),
				)
			)
		);

		/** @var \PHPUnit_Framework_MockObject_MockObject|\TYPO3\CMS\Backend\Utility\BackendUtility $fixture */
		$fixture = $this->getMock('TYPO3\\CMS\\Backend\\Utility\\BackendUtility', array('getPagesTSconfig'));

		$GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
		$GLOBALS['BE_USER']->expects($this->at(0))->method('getTSConfig')->will($this->returnValue($completeConfiguration));
		$GLOBALS['BE_USER']->expects($this->at(1))->method('getTSConfig')->will($this->returnValue(array('value' => NULL, 'properties' => NULL)));

		$this->assertSame($completeConfiguration, $fixture->getModTSconfig(42, 'notrelevant'));
	}


}
