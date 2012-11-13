<?php
namespace TYPO3\CMS\Core\Tests\Unit\Database;

/***************************************************************
 * Copyright notice
 *
 * (c) 2010-2011 Ernesto Baschny (ernst@cron-it.de)
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
 * Testcase for TYPO3\CMS\Core\Database\DatabaseConnection
 *
 * @package TYPO3
 * @subpackage t3lib
 * @author Ernesto Baschny <ernst@cron-it.de>
 */
class DatabaseConnectionTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	private $fixture = NULL;

	private $testTable;

	public function setUp() {
		$this->fixture = $GLOBALS['TYPO3_DB'];
		$this->testTable = 'test_t3lib_dbtest';
		$this->fixture->sql_query('CREATE TABLE ' . $this->testTable . ' (
			id int(11) unsigned NOT NULL auto_increment,
			fieldblob mediumblob,
			PRIMARY KEY (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		');
	}

	public function tearDown() {
		$this->fixture->sql_query('DROP TABLE ' . $this->testTable . ';');
		unset($this->fixture);
	}

	//////////////////////////////////////////////////
	// Write/Read tests for charsets and binaries
	//////////////////////////////////////////////////
	/**
	 * @test
	 */
	public function storedFullAsciiRangeReturnsSameData() {
		$binaryString = '';
		for ($i = 0; $i < 256; $i++) {
			$binaryString .= chr($i);
		}
		$this->fixture->exec_INSERTquery($this->testTable, array('fieldblob' => $binaryString));
		$id = $this->fixture->sql_insert_id();
		$entry = $this->fixture->exec_SELECTgetRows('fieldblob', $this->testTable, 'id = ' . $id);
		$this->assertEquals($binaryString, $entry[0]['fieldblob']);
	}

	/**
	 * @test
	 */
	public function storedGzipCompressedDataReturnsSameData() {
		$testStringWithBinary = @gzcompress('sdfkljer4587');
		$this->fixture->exec_INSERTquery($this->testTable, array('fieldblob' => $testStringWithBinary));
		$id = $this->fixture->sql_insert_id();
		$entry = $this->fixture->exec_SELECTgetRows('fieldblob', $this->testTable, 'id = ' . $id);
		$this->assertEquals($testStringWithBinary, $entry[0]['fieldblob']);
	}

	////////////////////////////////
	// Tests concerning listQuery
	////////////////////////////////
	/**
	 * @test
	 * @see http://bugs.typo3.org/view.php?id=15211
	 */
	public function listQueryWithIntegerCommaAsValue() {
		// Note: 44 = ord(',')
		$this->assertEquals($this->fixture->listQuery('dummy', 44, 'table'), $this->fixture->listQuery('dummy', '44', 'table'));
	}

	/////////////////////////////////////////////////
	// Tests concerning escapeStringForLikeComparison
	/////////////////////////////////////////////////
	/**
	 * @test
	 */
	public function escapeStringForLikeComparison() {
		$this->assertEquals('foo\\_bar\\%', $this->fixture->escapeStrForLike('foo_bar%', 'table'));
	}

	/////////////////////////////////////////////////
	// Tests concerning stripOrderByForOrderByKeyword
	/////////////////////////////////////////////////


	/**
	 * Data Provider for stripGroupByForGroupByKeyword()
	 *
	 * @see stripOrderByForOrderByKeyword()
	 * @return array
	 */
	public function stripOrderByForOrderByKeywordDataProvider() {
		return array(
			'single ORDER BY' => array('ORDER BY name, tstamp', 'name, tstamp'),
			'single ORDER BY in lower case' => array('order by name, tstamp', 'name, tstamp'),
			'ORDER BY with additional space behind' => array('ORDER BY  name, tstamp', 'name, tstamp'),
			'ORDER BY without space between the words' => array('ORDERBY name, tstamp', 'name, tstamp'),
			'ORDER BY added twice' => array('ORDER BY ORDER BY name, tstamp', 'name, tstamp'),
			'ORDER BY added twice without spaces in the first occurrence' => array('ORDERBY ORDER BY  name, tstamp', 'name, tstamp'),
			'ORDER BY added twice without spaces in the second occurrence' => array('ORDER BYORDERBY name, tstamp', 'name, tstamp'),
			'ORDER BY added twice without spaces' => array('ORDERBYORDERBY name, tstamp', 'name, tstamp'),
			'ORDER BY added twice without spaces afterwards' => array('ORDERBYORDERBYname, tstamp', 'name, tstamp'),
		);
	}

	/**
	 * @test
	 * @dataProvider stripOrderByForOrderByKeywordDataProvider
	 * @param string $orderByClause The clause to test
	 * @param string $expectedResult The expected result
	 * @return void
	 */
	public function stripOrderByForOrderByKeyword($orderByClause, $expectedResult) {
		$strippedQuery = $this->fixture->stripOrderBy($orderByClause);
		$this->assertEquals($expectedResult, $strippedQuery);
	}

	/////////////////////////////////////////////////
	// Tests concerning stripGroupByForGroupByKeyword
	/////////////////////////////////////////////////

	/**
	 * Data Provider for stripGroupByForGroupByKeyword()
	 *
	 * @see stripGroupByForGroupByKeyword()
	 * @return array
	 */
	public function stripGroupByForGroupByKeywordDataProvider() {
		return array(
			'single GROUP BY' => array('GROUP BY name, tstamp', 'name, tstamp'),
			'single GROUP BY in lower case' => array('group by name, tstamp', 'name, tstamp'),
			'GROUP BY with additional space behind' => array('GROUP BY  name, tstamp', 'name, tstamp'),
			'GROUP BY without space between the words' => array('GROUPBY name, tstamp', 'name, tstamp'),
			'GROUP BY added twice' => array('GROUP BY GROUP BY name, tstamp', 'name, tstamp'),
			'GROUP BY added twice without spaces in the first occurrence' => array('GROUPBY GROUP BY  name, tstamp', 'name, tstamp'),
			'GROUP BY added twice without spaces in the second occurrence' => array('GROUP BYGROUPBY name, tstamp', 'name, tstamp'),
			'GROUP BY added twice without spaces' => array('GROUPBYGROUPBY name, tstamp', 'name, tstamp'),
			'GROUP BY added twice without spaces afterwards' => array('GROUPBYGROUPBYname, tstamp', 'name, tstamp'),
		);
	}

	/**
	 * @test
	 * @dataProvider stripGroupByForGroupByKeywordDataProvider
	 * @param string $groupByClause The clause to test
	 * @param string $expectedResult The expected result
	 * @return void
	 */
	public function stripGroupByForGroupByKeyword($groupByClause, $expectedResult) {
		$strippedQuery = $this->fixture->stripGroupBy($groupByClause);
		$this->assertEquals($expectedResult, $strippedQuery);
	}

}

?>