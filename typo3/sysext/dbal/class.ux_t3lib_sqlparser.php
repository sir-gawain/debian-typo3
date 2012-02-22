<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2009 Kasper Skaarhoj (kasperYYYY@typo3.com)
*  (c) 2004-2009 Karsten Dambekalns <karsten@typo3.org>
*  (c) 2009-2010 Xavier Perseguers <typo3@perseguers.ch>
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
 * PHP SQL engine
 *
 * $Id: class.ux_t3lib_sqlparser.php 36761 2010-08-14 16:00:33Z xperseguers $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Karsten Dambekalns <k.dambekalns@fishfarm.de>
 * @author	Xavier Perseguers <typo3@perseguers.ch>
 */


/**
 * PHP SQL engine / server
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class ux_t3lib_sqlparser extends t3lib_sqlparser {

	/**
	 * Compiles a "SELECT [output] FROM..:" field list based on input array (made with ->parseFieldList())
	 * Can also compile field lists for ORDER BY and GROUP BY.
	 *
	 * @param	array		Array of select fields, (made with ->parseFieldList())
	 * @param	boolean		Whether comments should be compiled
	 * @return	string		Select field string
	 * @see parseFieldList()
	 */
	public function compileFieldList($selectFields, $compileComments = TRUE) {
			// TODO: Handle SQL hints in comments according to current DBMS
		return parent::compileFieldList($selectFields, FALSE);
	}

	/**
	 * Add slashes function used for compiling queries
	 * This method overrides the method from t3lib_sqlparser because
	 * the input string is already properly escaped.
	 *
	 * @param	string		Input string
	 * @return	string		Output string
	 */
	protected function compileAddslashes($str) {
		return $str;
	}

	/*************************
	 *
	 * Compiling queries
	 *
	 *************************/

	/**
	 * Compiles an INSERT statement from components array
	 *
	 * @param array Array of SQL query components
	 * @return string SQL INSERT query
	 * @see parseINSERT()
	 */
	protected function compileINSERT($components) {
		switch ((string)$GLOBALS['TYPO3_DB']->handlerCfg[$GLOBALS['TYPO3_DB']->lastHandlerKey]['type']) {
			case 'native':
				$query = parent::compileINSERT($components);
				break;
			case 'adodb':
				if (isset($components['VALUES_ONLY']) && is_array($components['VALUES_ONLY'])) {
					$fields = $GLOBALS['TYPO3_DB']->cache_fieldType[$components['TABLE']];
					$fc = 0;
					foreach ($fields as $fn => $fd) {
						$query[$fn] = $components['VALUES_ONLY'][$fc++][0];
					}
				} else {
						// Initialize:
					foreach ($components['FIELDS'] as $fN => $fV) {
						$query[$fN]=$fV[0];
					}
				}
				break;
		}

		return $query;
	}

	/**
	 * Compiles a DROP TABLE statement from components array
	 *
	 * @param array Array of SQL query components
	 * @return string SQL DROP TABLE query
	 * @see compileSQL()
	 */
	private function compileDROPTABLE($components) {
		switch ((string)$GLOBALS['TYPO3_DB']->handlerCfg[$GLOBALS['TYPO3_DB']->lastHandlerKey]['type'])	{
			case 'native':
				$query = 'DROP TABLE' . ($components['ifExists'] ? ' IF EXISTS' : '') . ' ' . $components['TABLE'];
				break;
			case 'adodb':
				$handlerKey = $GLOBALS['TYPO3_DB']->handler_getFromTableList($components['TABLE']);
				$tableName = $GLOBALS['TYPO3_DB']->quoteName($components['TABLE'], $handlerKey, TRUE);
				$query = $GLOBALS['TYPO3_DB']->handlerInstance[$handlerKey]->DataDictionary->DropTableSQL($tableName);
				break;
		}

		return $query;
	}

	/**
	 * Compiles a CREATE TABLE statement from components array
	 *
	 * @param	array		Array of SQL query components
	 * @return	array		array with SQL CREATE TABLE/INDEX command(s)
	 * @see parseCREATETABLE()
	 */
	public function compileCREATETABLE($components) {
			// Execute query (based on handler derived from the TABLE name which we actually know for once!)
		switch ((string)$GLOBALS['TYPO3_DB']->handlerCfg[$GLOBALS['TYPO3_DB']->handler_getFromTableList($components['TABLE'])]['type']) {
			case 'native':
				$query[] = parent::compileCREATETABLE($components);
				break;
			case 'adodb':
					// Create fields and keys:
				$fieldsKeys = array();
				$indexKeys = array();

				foreach ($components['FIELDS'] as $fN => $fCfg) {
					$handlerKey = $GLOBALS['TYPO3_DB']->handler_getFromTableList($components['TABLE']);
					$fieldsKeys[$fN] = $GLOBALS['TYPO3_DB']->quoteName($fN, $handlerKey, TRUE) . ' ' . $this->compileFieldCfg($fCfg['definition']);
				}

				if (isset($components['KEYS']) && is_array($components['KEYS'])) {
					foreach($components['KEYS'] as $kN => $kCfg) {
						if ($kN === 'PRIMARYKEY') {
							foreach ($kCfg as $n => $field) {
								$fieldsKeys[$field] .= ' PRIMARY';
							}
						} elseif ($kN === 'UNIQUE') {
							foreach ($kCfg as $n => $field) {
								$indexKeys = array_merge($indexKeys, $GLOBALS['TYPO3_DB']->handlerInstance[$GLOBALS['TYPO3_DB']->handler_getFromTableList($components['TABLE'])]->DataDictionary->CreateIndexSQL($n, $components['TABLE'], $field, array('UNIQUE')));
							}
						} else {
							$indexKeys = array_merge($indexKeys, $GLOBALS['TYPO3_DB']->handlerInstance[$GLOBALS['TYPO3_DB']->handler_getFromTableList($components['TABLE'])]->DataDictionary->CreateIndexSQL($components['TABLE'] . '_' . $kN, $components['TABLE'], $kCfg));
						}
					}
				}

					// Generally create without OID on PostgreSQL
				$tableOptions = array('postgres' => 'WITHOUT OIDS');

					// Fetch table/index generation query:
				$tableName = $GLOBALS['TYPO3_DB']->quoteName($components['TABLE'], NULL, TRUE);
				$query = array_merge($GLOBALS['TYPO3_DB']->handlerInstance[$GLOBALS['TYPO3_DB']->lastHandlerKey]->DataDictionary->CreateTableSQL($tableName, implode(',' . chr(10), $fieldsKeys), $tableOptions), $indexKeys);
				break;
		}

		return $query;
	}

	/**
	 * Compiles an ALTER TABLE statement from components array
	 *
	 * @param array Array of SQL query components
	 * @return string SQL ALTER TABLE query
	 * @see parseALTERTABLE()
	 */
	public function compileALTERTABLE($components) {
			// Execute query (based on handler derived from the TABLE name which we actually know for once!)
		switch ((string)$GLOBALS['TYPO3_DB']->handlerCfg[$GLOBALS['TYPO3_DB']->lastHandlerKey]['type']) {
			case 'native':
				$query[] = parent::compileALTERTABLE($components);
				break;
			case 'adodb':
				$tableName = $GLOBALS['TYPO3_DB']->quoteName($components['TABLE'], NULL, TRUE);
				$fieldName = $GLOBALS['TYPO3_DB']->quoteName($components['FIELD'], NULL, TRUE);
				switch (strtoupper(str_replace(array(' ', "\n", "\r", "\t"), '', $components['action']))) {
					case 'ADD':
						$query = $GLOBALS['TYPO3_DB']->handlerInstance[$GLOBALS['TYPO3_DB']->lastHandlerKey]->DataDictionary->AddColumnSQL($tableName, $fieldName . ' ' . $this->compileFieldCfg($components['definition']));
						break;
					case 'CHANGE':
						$query = $GLOBALS['TYPO3_DB']->handlerInstance[$GLOBALS['TYPO3_DB']->lastHandlerKey]->DataDictionary->AlterColumnSQL($tableName, $fieldName . ' ' . $this->compileFieldCfg($components['definition']));
						break;
					case 'DROP':
					case 'DROPKEY':
						break;
					case 'ADDKEY':
					case 'ADDPRIMARYKEY':
					case 'ADDUNIQUE':
						$query .= ' (' . implode(',', $components['fields']) . ')';
						break;
				}
				break;
		}

		return $query;
	}

	/**
	 * Compile field definition
	 *
	 * @param	array		Field definition parts
	 * @return	string		Field definition string
	 */
	public function compileFieldCfg($fieldCfg) {
		switch ((string)$GLOBALS['TYPO3_DB']->handlerCfg[$GLOBALS['TYPO3_DB']->lastHandlerKey]['type']) {
			case 'native':
				$cfg = parent::compileFieldCfg($fieldCfg);
				break;
			case 'adodb':
					// Set type:
				$type = $GLOBALS['TYPO3_DB']->MySQLMetaType($fieldCfg['fieldType']);
				$cfg = $type;

					// Add value, if any:
				if (strlen($fieldCfg['value']) && (in_array($type, array('C', 'C2')))) {
					$cfg .= ' '.$fieldCfg['value'];
				} elseif (!isset($fieldCfg['value']) && (in_array($type, array('C', 'C2')))) {
					$cfg .= ' 255'; // add 255 as length for varchar without specified length (e.g. coming from tinytext, tinyblob)
				}

					// Add additional features:
				$noQuote = TRUE;
				if (is_array($fieldCfg['featureIndex'])) {

						// MySQL assigns DEFAULT value automatically if NOT NULL, fake this here
						// numeric fields get 0 as default, other fields an empty string
					if (isset($fieldCfg['featureIndex']['NOTNULL']) && !isset($fieldCfg['featureIndex']['DEFAULT']) && !isset($fieldCfg['featureIndex']['AUTO_INCREMENT'])) {
						switch ($type) {
							case 'I8':
							case 'F':
							case 'N':
								$fieldCfg['featureIndex']['DEFAULT'] = array('keyword' => 'DEFAULT', 'value' => array('0', ''));
								break;
							default:
								$fieldCfg['featureIndex']['DEFAULT'] = array('keyword' => 'DEFAULT', 'value' => array('', '\''));
						}
					}

					foreach ($fieldCfg['featureIndex'] as $feature => $featureDef) {
						switch (TRUE) {
								// unsigned only for mysql, as it is mysql specific
							case ($feature === 'UNSIGNED' && !$GLOBALS['TYPO3_DB']->runningADOdbDriver('mysql')):
								// auto_increment is removed, it is handled by (emulated) sequences
							case ($feature === 'AUTO_INCREMENT'):
								// never add NOT NULL if running on Oracle and we have an empty string as default
							case ($feature === 'NOTNULL' && $GLOBALS['TYPO3_DB']->runningADOdbDriver('oci8')):
								continue;
							case ($feature === 'NOTNULL'):
								$cfg .= ' NOTNULL';
								break;
							default:
								$cfg .= ' ' . $featureDef['keyword'];
						}

							// Add value if found:
						if (is_array($featureDef['value'])) {
							if ($featureDef['value'][0] === '') {
								$cfg .= ' "\'\'"';
							} else {
								$cfg .= ' ' . $featureDef['value'][1] . $this->compileAddslashes($featureDef['value'][0]) . $featureDef['value'][1];
								if (!is_numeric($featureDef['value'][0])) {
									$noQuote = FALSE;
								}
							}
						}
					}
				}
				if ($noQuote) {
					$cfg .= ' NOQUOTE';
				}
				break;
		}

			// Return field definition string:
		return $cfg;
	}

	/**
	 * Checks if the submitted feature index contains a default value definition and the default value
	 *
	 * @param array $featureIndex A feature index as produced by parseFieldDef()
	 * @return boolean
	 * @see t3lib_sqlparser::parseFieldDef()
	 */
	public function checkEmptyDefaultValue($featureIndex) {
		if (is_array($featureIndex['DEFAULT']['value'])) {
			if (!is_numeric($featureIndex['DEFAULT']['value'][0]) && empty($featureIndex['DEFAULT']['value'][0])) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Implodes an array of WHERE clause configuration into a WHERE clause.
	 *
	 * DBAL-specific: The only(!) handled "calc" operators supported by parseWhereClause() are:
	 * - the bitwise logical and (&)
	 * - the addition (+)
	 * - the substraction (-)
	 * - the multiplication (*)
	 * - the division (/)
	 * - the modulo (%)
	 *
	 * @param array WHERE clause configuration
	 * @return string WHERE clause as string.
	 * @see	t3lib_sqlparser::parseWhereClause()
	 */
	public function compileWhereClause($clauseArray, $functionMapping = TRUE) {
		switch ((string)$GLOBALS['TYPO3_DB']->handlerCfg[$GLOBALS['TYPO3_DB']->lastHandlerKey]['type']) {
			case 'native':
				$output = parent::compileWhereClause($clauseArray);
				break;
			case 'adodb':
					// Prepare buffer variable:
				$output = '';

					// Traverse clause array:
				if (is_array($clauseArray)) {
					foreach($clauseArray as $k => $v) {

							// Set operator:
						$output .= $v['operator'] ? ' ' . $v['operator'] : '';

							// Look for sublevel:
						if (is_array($v['sub'])) {
							$output .= ' (' . trim($this->compileWhereClause($v['sub'], $functionMapping)) . ')';
						} else {

								// Set field/table with modifying prefix if any:
							$output .= ' ' . trim($v['modifier']) . ' ';

								// DBAL-specific: Set calculation, if any:
							if ($v['calc'] === '&' && $functionMapping) {
								switch(TRUE) {
									case $GLOBALS['TYPO3_DB']->runningADOdbDriver('oci8'):
											// Oracle only knows BITAND(x,y) - sigh
										$output .= 'BITAND(' . trim(($v['table'] ? $v['table'] . '.' : '') . $v['field']) . ',' . $v['calc_value'][1] . $this->compileAddslashes($v['calc_value'][0]) . $v['calc_value'][1] . ')';
										break;
									default:
											// MySQL, MS SQL Server, PostgreSQL support the &-syntax
										$output .= trim(($v['table'] ? $v['table'] . '.' : '') . $v['field']) . $v['calc'] . $v['calc_value'][1] . $this->compileAddslashes($v['calc_value'][0]) . $v['calc_value'][1];
										break;
								}
							} elseif ($v['calc']) {
								$output .= trim(($v['table'] ? $v['table'] . '.' : '') . $v['field']) . $v['calc'];
								if (isset($v['calc_table'])) {
									$output .= trim(($v['calc_table'] ? $v['calc_table'] . '.' : '') . $v['calc_field']);
								} else {
									$output .= $v['calc_value'][1] . $this->compileAddslashes($v['calc_value'][0]) . $v['calc_value'][1];
								}
							} elseif (!($GLOBALS['TYPO3_DB']->runningADOdbDriver('oci8') && preg_match('/(NOT )?LIKE/', $v['comparator']) && $functionMapping)) {
								$output .= trim(($v['table'] ? $v['table'] . '.' : '') . $v['field']);
							}

								// Set comparator:
							if ($v['comparator']) {
								switch (TRUE) {
									case ($GLOBALS['TYPO3_DB']->runningADOdbDriver('oci8') && preg_match('/(NOT )?LIKE/', $v['comparator']) && $functionMapping):
												// Oracle cannot handle LIKE on CLOB fields - sigh
											if (isset($v['value']['operator'])) {
												$values = array();
												foreach ($v['value']['args'] as $fieldDef) {
													$values[] = ($fieldDef['table'] ? $fieldDef['table'] . '.' : '') . $fieldDef['field'];
												}
												$compareValue = ' ' . $v['value']['operator'] . '(' . implode(',', $values) . ')';
											} else {
												$compareValue = $v['value'][1] . $this->compileAddslashes(trim($v['value'][0], '%')) . $v['value'][1];
											}
											if (t3lib_div::isFirstPartOfStr($v['comparator'], 'NOT')) {
												$output .= 'NOT ';
											}
											$output .= '(dbms_lob.instr(' . trim(($v['table'] ? $v['table'] . '.' : '') . $v['field']) . ', ' . $compareValue . ',1,1) > 0)';
										break;
									default:
										$output .= ' ' . $v['comparator'];

											// Detecting value type; list or plain:
										if (t3lib_div::inList('NOTIN,IN', strtoupper(str_replace(array(' ', "\t", "\r", "\n"), '', $v['comparator'])))) {
											if (isset($v['subquery'])) {
												$output .= ' (' . $this->compileSELECT($v['subquery']) . ')';
											} else {
												$valueBuffer = array();
												foreach ($v['value'] as $realValue) {
													$valueBuffer[] = $realValue[1] . $this->compileAddslashes($realValue[0]) . $realValue[1];
												}
												$output .= ' (' . trim(implode(',', $valueBuffer)) . ')';
											}
										} else if (isset($v['value']['operator'])) {
											$values = array();
											foreach ($v['value']['args'] as $fieldDef) {
												$values[] = ($fieldDef['table'] ? $fieldDef['table'] . '.' : '') . $fieldDef['field'];
											}
											$output .= ' ' . $v['value']['operator'] . '(' . implode(',', $values) . ')';
										} else {
											$output .= ' ' . $v['value'][1] . $this->compileAddslashes($v['value'][0]) . $v['value'][1];
										}
										break;
								}
							}
						}
					}
				}
				break;
		}

		return $output;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dbal/class.ux_t3lib_sqlparser.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dbal/class.ux_t3lib_sqlparser.php']);
}

?>