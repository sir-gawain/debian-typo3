<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Standard functions available for the TYPO3 backend.
 * You are encouraged to use this class in your own applications (Backend Modules)
 *
 * Call ALL methods without making an object!
 * Eg. to get a page-record 51 do this: 't3lib_BEfunc::getRecord('pages',51)'
 *
 * $Id: class.t3lib_befunc.php 4650 2009-01-05 13:40:20Z steffenk $
 * Usage counts are based on search 22/2 2003 through whole backend source of typo3/
 * Revised for TYPO3 3.6 July/2003 by Kasper Skaarhoj
 * XHTML compliant
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  185: class t3lib_BEfunc
 *
 *              SECTION: SQL-related, selecting records, searching
 *  206:     function deleteClause($table,$tableAlias='')
 *  230:     function getRecord($table,$uid,$fields='*',$where='',$useDeleteClause=true)
 *  253:     function getRecordWSOL($table,$uid,$fields='*',$where='',$useDeleteClause=true)
 *  286:     function getRecordRaw($table,$where='',$fields='*')
 *  309:     function getRecordsByField($theTable,$theField,$theValue,$whereClause='',$groupBy='',$orderBy='',$limit='',$useDeleteClause=true)
 *  342:     function searchQuery($searchWords,$fields,$table='')
 *  357:     function listQuery($field,$value)
 *  369:     function splitTable_Uid($str)
 *  384:     function getSQLselectableList($in_list,$tablename,$default_tablename)
 *  412:     function BEenableFields($table,$inv=0)
 *
 *              SECTION: SQL-related, DEPRECATED functions
 *  476:     function mm_query($select,$local_table,$mm_table,$foreign_table,$whereClause='',$groupBy='',$orderBy='',$limit='')
 *  498:     function DBcompileInsert($table,$fields_values)
 *  512:     function DBcompileUpdate($table,$where,$fields_values)
 *
 *              SECTION: Page tree, TCA related
 *  542:     function BEgetRootLine($uid,$clause='',$workspaceOL=FALSE)
 *  598:     function openPageTree($pid,$clearExpansion)
 *  643:     function getRecordPath($uid, $clause, $titleLimit, $fullTitleLimit=0)
 *  686:     function getExcludeFields()
 *  716:     function getExplicitAuthFieldValues()
 *  787:     function getSystemLanguages()
 *  812:     function readPageAccess($id,$perms_clause)
 *  843:     function getTCAtypes($table,$rec,$useFieldNameAsKey=0)
 *  896:     function getTCAtypeValue($table,$rec)
 *  919:     function getSpecConfParts($str, $defaultExtras)
 *  950:     function getSpecConfParametersFromArray($pArr)
 *  978:     function getFlexFormDS($conf,$row,$table,$fieldName='',$WSOL=TRUE)
 *
 *              SECTION: Caching related
 * 1105:     function storeHash($hash,$data,$ident)
 * 1125:     function getHash($hash,$expTime=0)
 *
 *              SECTION: TypoScript related
 * 1161:     function getPagesTSconfig($id,$rootLine='',$returnPartArray=0)
 * 1217:     function updatePagesTSconfig($id,$pageTS,$TSconfPrefix,$impParams='')
 * 1272:     function implodeTSParams($p,$k='')
 *
 *              SECTION: Users / Groups related
 * 1309:     function getUserNames($fields='username,usergroup,usergroup_cached_list,uid',$where='')
 * 1327:     function getGroupNames($fields='title,uid', $where='')
 * 1344:     function getListGroupNames($fields='title,uid')
 * 1363:     function blindUserNames($usernames,$groupArray,$excludeBlindedFlag=0)
 * 1396:     function blindGroupNames($groups,$groupArray,$excludeBlindedFlag=0)
 *
 *              SECTION: Output related
 * 1437:     function daysUntil($tstamp)
 * 1449:     function date($tstamp)
 * 1460:     function datetime($value)
 * 1472:     function time($value)
 * 1488:     function calcAge($seconds,$labels = 'min|hrs|days|yrs')
 * 1514:     function dateTimeAge($tstamp,$prefix=1,$date='')
 * 1532:     function titleAttrib($content='',$hsc=0)
 * 1545:     function titleAltAttrib($content)
 * 1569:     function thumbCode($row,$table,$field,$backPath,$thumbScript='',$uploaddir=NULL,$abs=0,$tparams='',$size='')
 * 1637:     function getThumbNail($thumbScript,$theFile,$tparams='',$size='')
 * 1654:     function titleAttribForPages($row,$perms_clause='',$includeAttrib=1)
 * 1716:     function getRecordIconAltText($row,$table='pages')
 * 1758:     function getLabelFromItemlist($table,$col,$key)
 * 1784:     function getItemLabel($table,$col,$printAllWrap='')
 * 1809:     function getRecordTitle($table,$row,$prep=0)
 * 1847:     function getProcessedValue($table,$col,$value,$fixed_lgd_chars=0,$defaultPassthrough=0,$noRecordLookup=FALSE,$uid=0)
 * 2009:     function getProcessedValueExtra($table,$fN,$fV,$fixed_lgd_chars=0,$uid=0)
 * 2033:     function getFileIcon($ext)
 * 2047:     function getCommonSelectFields($table,$prefix='')
 * 2090:     function makeConfigForm($configArray,$defaults,$dataPrefix)
 *
 *              SECTION: Backend Modules API functions
 * 2165:     function helpTextIcon($table,$field,$BACK_PATH,$force=0)
 * 2187:     function helpText($table,$field,$BACK_PATH,$styleAttrib='')
 * 2239:     function cshItem($table,$field,$BACK_PATH,$wrap='',$onlyIconMode=FALSE, $styleAttrib='')
 * 2277:     function editOnClick($params,$backPath='',$requestUri='')
 * 2296:     function viewOnClick($id,$backPath='',$rootLine='',$anchor='',$altUrl='',$addGetVars='',$switchFocus=TRUE)
 * 2328:     function getModTSconfig($id,$TSref)
 * 2349:     function getFuncMenu($mainParams,$elementName,$currentValue,$menuItems,$script='',$addparams='')
 * 2392:     function getFuncCheck($mainParams,$elementName,$currentValue,$script='',$addparams='',$tagParams='')
 * 2417:     function getFuncInput($mainParams,$elementName,$currentValue,$size=10,$script="",$addparams="")
 * 2438:     function unsetMenuItems($modTSconfig,$itemArray,$TSref)
 * 2461:     function getSetUpdateSignal($set='')
 * 2512:     function getModuleData($MOD_MENU, $CHANGED_SETTINGS, $modName, $type='', $dontValidateList='', $setDefaultList='')
 *
 *              SECTION: Core
 * 2585:     function compilePreviewKeyword($getVarsStr, $beUserUid, $ttl=172800)
 * 2613:     function lockRecords($table='',$uid=0,$pid=0)
 * 2642:     function isRecordLocked($table,$uid)
 * 2682:     function exec_foreign_table_where_query($fieldValue,$field='',$TSconfig=array(),$prefix='')
 * 2763:     function getTCEFORM_TSconfig($table,$row)
 * 2814:     function getTSconfig_pidValue($table,$uid,$pid)
 * 2844:     function getPidForModTSconfig($table,$uid,$pid)
 * 2860:     function getTSCpid($table,$uid,$pid)
 * 2876:     function firstDomainRecord($rootLine)
 * 2898:     function getDomainStartPage($domain, $path='')
 * 2928:     function RTEsetup($RTEprop,$table,$field,$type='')
 * 2947:     function &RTEgetObj()
 * 2986:     function &softRefParserObj($spKey)
 * 3018:     function explodeSoftRefParserList($parserList)
 * 3050:     function isModuleSetInTBE_MODULES($modName)
 * 3073:     function referenceCount($table,$ref,$msg='')
 *
 *              SECTION: Workspaces / Versioning
 * 3132:     function selectVersionsOfRecord($table, $uid, $fields='*', $workspace=0)
 * 3180:     function fixVersioningPid($table,&$rr,$ignoreWorkspaceMatch=FALSE)
 * 3220:     function workspaceOL($table,&$row,$wsid=-99)
 * 3268:     function getWorkspaceVersionOfRecord($workspace, $table, $uid, $fields='*')
 * 3297:     function getLiveVersionOfRecord($table,$uid,$fields='*')
 * 3319:     function isPidInVersionizedBranch($pid, $table='',$returnStage=FALSE)
 * 3342:     function versioningPlaceholderClause($table)
 * 3356:     function countVersionsOfRecordsOnPage($workspace,$pageId, $allTables=FALSE)
 * 3391:     function wsMapId($table,$uid)
 *
 *              SECTION: Miscellaneous
 * 3421:     function typo3PrintError($header,$text,$js='',$head=1)
 * 3465:     function TYPO3_copyRightNotice()
 * 3489:     function displayWarningMessages()
 * 3546:     function getPathType_web_nonweb($path)
 * 3558:     function ADMCMD_previewCmds($pageinfo)
 * 3580:     function processParams($params)
 * 3606:     function getListOfBackendModules($name,$perms_clause,$backPath='',$script='index.php')
 *
 * TOTAL FUNCTIONS: 99
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once (PATH_t3lib.'class.t3lib_loaddbgroup.php');


/**
 * Standard functions available for the TYPO3 backend.
 * Don't instantiate - call functions with "t3lib_BEfunc::" prefixed the function name.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
final class t3lib_BEfunc {



	/*******************************************
	 *
	 * SQL-related, selecting records, searching
	 *
	 *******************************************/


	/**
	 * Returns the WHERE clause " AND NOT [tablename].[deleted-field]" if a deleted-field is configured in $TCA for the tablename, $table
	 * This function should ALWAYS be called in the backend for selection on tables which are configured in TCA since it will ensure consistent selection of records, even if they are marked deleted (in which case the system must always treat them as non-existent!)
	 * In the frontend a function, ->enableFields(), is known to filter hidden-field, start- and endtime and fe_groups as well. But that is a job of the frontend, not the backend. If you need filtering on those fields as well in the backend you can use ->BEenableFields() though.
	 * Usage: 71
	 *
	 * @param	string		Table name present in $TCA
	 * @param	string		Table alias if any
	 * @return	string		WHERE clause for filtering out deleted records, eg " AND tablename.deleted=0"
	 */
	public static function deleteClause($table,$tableAlias = '') {
		global $TCA;
		if ($TCA[$table]['ctrl']['delete']) {
			return ' AND '.($tableAlias ? $tableAlias : $table).'.'.$TCA[$table]['ctrl']['delete'].'=0';
		} else {
			return '';
		}
	}

	/**
	 * Gets record with uid = $uid from $table
	 * You can set $field to a list of fields (default is '*')
	 * Additional WHERE clauses can be added by $where (fx. ' AND blabla = 1')
	 * Will automatically check if records has been deleted and if so, not return anything.
	 * $table must be found in $TCA
	 * Usage: 99
	 *
	 * @param	string		Table name present in $TCA
	 * @param	integer		UID of record
	 * @param	string		List of fields to select
	 * @param	string		Additional WHERE clause, eg. " AND blablabla = 0"
	 * @param	boolean		Use the deleteClause to check if a record is deleted (default true)
	 * @return	array		Returns the row if found, otherwise nothing
	 */
	public static function getRecord($table, $uid, $fields = '*', $where = '', $useDeleteClause = true) {
		if ($GLOBALS['TCA'][$table]) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				$fields,
				$table,
				'uid='.intval($uid).($useDeleteClause ? t3lib_BEfunc::deleteClause($table) : '').$where
			);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			if ($row) {
				return $row;
			}
		}
	}

	/**
	 * Like getRecord(), but overlays workspace version if any.
	 *
	 * @param	string		Table name present in $TCA
	 * @param	integer		UID of record
	 * @param	string		List of fields to select
	 * @param	string		Additional WHERE clause, eg. " AND blablabla = 0"
	 * @param	boolean		Use the deleteClause to check if a record is deleted (default true)
	 * @return	array		Returns the row if found, otherwise nothing
	 */
	public static function getRecordWSOL($table, $uid, $fields = '*', $where = '', $useDeleteClause = true) {
		if ($fields !== '*') {
			$internalFields = t3lib_div::uniqueList($fields.',uid,pid'.($table == 'pages' ? ',t3ver_swapmode' : ''));
			$row = t3lib_BEfunc::getRecord($table, $uid, $internalFields, $where, $useDeleteClause);
			t3lib_BEfunc::workspaceOL($table, $row);

			if (is_array ($row)) {
				foreach (array_keys($row) as $key) {
					if (!t3lib_div::inList($fields, $key) && $key{0} !== '_') {
						unset ($row[$key]);
					}
				}
			}
		} else {
			$row = t3lib_BEfunc::getRecord($table, $uid, $fields, $where);
			t3lib_BEfunc::workspaceOL($table, $row);
		}
		return $row;
	}

	/**
	 * Returns the first record found from $table with $where as WHERE clause
	 * This function does NOT check if a record has the deleted flag set.
	 * $table does NOT need to be configured in $TCA
	 * The query used is simply this:
	 * $query = 'SELECT '.$fields.' FROM '.$table.' WHERE '.$where;
	 * Usage: 5 (ext: sys_todos)
	 *
	 * @param	string		Table name (not necessarily in TCA)
	 * @param	string		WHERE clause
	 * @param	string		$fields is a list of fields to select, default is '*'
	 * @return	array		First row found, if any, FALSE otherwise
	 */
	public static function getRecordRaw($table, $where = '', $fields = '*') {
		$row = FALSE;
		if (FALSE !== ($res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, '', '', '1'))) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $row;
	}

	/**
	 * Returns records from table, $theTable, where a field ($theField) equals the value, $theValue
	 * The records are returned in an array
	 * If no records were selected, the function returns nothing
	 * Usage: 8
	 *
	 * @param	string		Table name present in $TCA
	 * @param	string		Field to select on
	 * @param	string		Value that $theField must match
	 * @param	string		Optional additional WHERE clauses put in the end of the query. DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @param	boolean		Use the deleteClause to check if a record is deleted (default true)
	 * @return	mixed		Multidimensional array with selected records (if any is selected)
	 */
	public static function getRecordsByField($theTable, $theField, $theValue, $whereClause = '', $groupBy = '', $orderBy = '', $limit = '', $useDeleteClause = true) {
		global $TCA;
		if (is_array($TCA[$theTable])) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						$theTable,
						$theField.'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($theValue, $theTable).
							($useDeleteClause ? t3lib_BEfunc::deleteClause($theTable).' ' : '').
							t3lib_BEfunc::versioningPlaceholderClause($theTable).' '.
							$whereClause,	// whereClauseMightContainGroupOrderBy
						$groupBy,
						$orderBy,
						$limit
					);
			$rows = array();
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$rows[] = $row;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			if (count($rows))	return $rows;
		}
	}

	/**
	 * Returns a WHERE clause which will make an AND search for the words in the $searchWords array in any of the fields in array $fields.
	 * Usage: 0
	 *
	 * @param	array		Array of search words
	 * @param	array		Array of fields
	 * @param	string		Table in which we are searching (for DBAL detection of quoteStr() method)
	 * @return	string		WHERE clause for search
	 * @deprecated		Use $GLOBALS['TYPO3_DB']->searchQuery() directly!
	 */
	public static function searchQuery($searchWords, $fields, $table = '') {
		return $GLOBALS['TYPO3_DB']->searchQuery($searchWords, $fields, $table);
	}

	/**
	 * Returns a WHERE clause that can find a value ($value) in a list field ($field)
	 * For instance a record in the database might contain a list of numbers, "34,234,5" (with no spaces between). This query would be able to select that record based on the value "34", "234" or "5" regardless of their positioni in the list (left, middle or right).
	 * Is nice to look up list-relations to records or files in TYPO3 database tables.
	 * Usage: 0
	 *
	 * @param	string		Table field name
	 * @param	string		Value to find in list
	 * @return	string		WHERE clause for a query
	 * @deprecated		Use $GLOBALS['TYPO3_DB']->listQuery() directly!
	 */
	public static function listQuery($field, $value) {
		return $GLOBALS['TYPO3_DB']->listQuery($field, $value, '');
	}

	/**
	 * Makes an backwards explode on the $str and returns an array with ($table, $uid).
	 * Example: tt_content_45 => array('tt_content', 45)
	 * Usage: 1
	 *
	 * @param	string		[tablename]_[uid] string to explode
	 * @return	array
	 */
	public static function splitTable_Uid($str) {
		list($uid, $table) = explode('_', strrev($str), 2);
		return array(strrev($table), strrev($uid));
	}

	/**
	 * Returns a list of pure integers based on $in_list being a list of records with table-names prepended.
	 * Ex: $in_list = "pages_4,tt_content_12,45" would result in a return value of "4,45" if $tablename is "pages" and $default_tablename is 'pages' as well.
	 * Usage: 1 (t3lib_userauthgroup)
	 *
	 * @param	string		Input list
	 * @param	string		Table name from which ids is returned
	 * @param	string		$default_tablename denotes what table the number '45' is from (if nothing is prepended on the value)
	 * @return	string		List of ids
	 */
	public static function getSQLselectableList($in_list, $tablename, $default_tablename) {
		$list = Array();
		if ((string)trim($in_list)!='') {
			$tempItemArray = explode(',', trim($in_list));
			while(list($key, $val) = each($tempItemArray)) {
				$val = strrev($val);
				$parts = explode('_', $val, 2);
				if ((string)trim($parts[0])!='') {
					$theID = intval(strrev($parts[0]));
					$theTable = trim($parts[1]) ? strrev(trim($parts[1])) : $default_tablename;
					if ($theTable==$tablename)	{$list[] = $theID;}
				}
			}
		}
		return implode(',', $list);
	}

	/**
	 * Backend implementation of enableFields()
	 * Notice that "fe_groups" is not selected for - only disabled, starttime and endtime.
	 * Notice that deleted-fields are NOT filtered - you must ALSO call deleteClause in addition.
	 * $GLOBALS["SIM_ACCESS_TIME"] is used for date.
	 * Usage: 5
	 *
	 * @param	string		$table is the table from which to return enableFields WHERE clause. Table name must have a 'ctrl' section in $TCA.
	 * @param	boolean		$inv means that the query will select all records NOT VISIBLE records (inverted selection)
	 * @return	string		WHERE clause part
	 */
	public static function BEenableFields($table, $inv = 0) {
		$ctrl = $GLOBALS['TCA'][$table]['ctrl'];
		$query = array();
		$invQuery = array();
		if (is_array($ctrl)) {
			if (is_array($ctrl['enablecolumns'])) {
				if ($ctrl['enablecolumns']['disabled']) {
					$field = $table.'.'.$ctrl['enablecolumns']['disabled'];
					$query[] = $field.'=0';
					$invQuery[] = $field.'!=0';
				}
				if ($ctrl['enablecolumns']['starttime']) {
					$field = $table.'.'.$ctrl['enablecolumns']['starttime'];
					$query[] = '('.$field.'<='.$GLOBALS['SIM_ACCESS_TIME'].')';
					$invQuery[] = '('.$field.'!=0 AND '.$field.'>'.$GLOBALS['SIM_ACCESS_TIME'].')';
				}
				if ($ctrl['enablecolumns']['endtime']) {
					$field = $table.'.'.$ctrl['enablecolumns']['endtime'];
					$query[] = '('.$field.'=0 OR '.$field.'>'.$GLOBALS['SIM_ACCESS_TIME'].')';
					$invQuery[] = '('.$field.'!=0 AND '.$field.'<='.$GLOBALS['SIM_ACCESS_TIME'].')';
				}
			}
		}
		$outQ = ($inv ? '(' . implode(' OR ', $invQuery) . ')' : implode(' AND ', $query));

		return $outQ ? ' AND ' . $outQ : '';
	}

	/**
	 * Fetches the localization for a given record.
	 *
	 * @param	string		$table: Table name present in $TCA
	 * @param	integer		$uid: The uid of the record
	 * @param	integer		$language: The uid of the language record in sys_language
	 * @param	string		$andWhereClause: Optional additional WHERE clause (default: '')
	 * @return	mixed		Multidimensional array with selected records; if none exist, false is returned
	 */
	public function getRecordLocalization($table, $uid, $language, $andWhereClause = '') {
		$recordLocalization = false;
		if (self::isTableLocalizable($table)) {
			$tcaCtrl = $GLOBALS['TCA'][$table]['ctrl'];
			$recordLocalization = t3lib_BEfunc::getRecordsByField(
				$table,
				$tcaCtrl['transOrigPointerField'],
				$uid,
				'AND '.$tcaCtrl['languageField'].'='.intval($language).($andWhereClause ? ' '.$andWhereClause : ''),
				'',
				'',
				'1'
			);
		}
		return $recordLocalization;
	}










	/*******************************************
	 *
	 * SQL-related, DEPRECATED functions
	 * (use t3lib_DB functions instead)
	 *
	 *******************************************/


	/**
	 * Returns a SELECT query, selecting fields ($select) from two/three tables joined
	 * $local_table and $mm_table is mandatory. $foreign_table is optional.
	 * The JOIN is done with [$local_table].uid <--> [$mm_table].uid_local  / [$mm_table].uid_foreign <--> [$foreign_table].uid
	 * The function is very useful for selecting MM-relations between tables adhering to the MM-format used by TCE (TYPO3 Core Engine). See the section on $TCA in Inside TYPO3 for more details.
	 * DEPRECATED - Use $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query() instead since that will return the result pointer while this returns the query. Using this function may make your application less fitted for DBAL later.
	 *
	 * @param	string		Field list for SELECT
	 * @param	string		Tablename, local table
	 * @param	string		Tablename, relation table
	 * @param	string		Tablename, foreign table
	 * @param	string		Optional additional WHERE clauses put in the end of the query. DO NOT PUT IN GROUP BY, ORDER BY or LIMIT!
	 * @param	string		Optional GROUP BY field(s), if none, supply blank string.
	 * @param	string		Optional ORDER BY field(s), if none, supply blank string.
	 * @param	string		Optional LIMIT value ([begin,]max), if none, supply blank string.
	 * @return	string		Full SQL query
	 * @deprecated
	 * @see t3lib_DB::exec_SELECT_mm_query()
	 */
	public static function mm_query($select, $local_table, $mm_table, $foreign_table, $whereClause = '', $groupBy = '', $orderBy = '', $limit = '') {
		$query = $GLOBALS['TYPO3_DB']->SELECTquery(
					$select,
					$local_table.','.$mm_table.($foreign_table?','.$foreign_table:''),
					$local_table.'.uid='.$mm_table.'.uid_local'.($foreign_table?' AND '.$foreign_table.'.uid='.$mm_table.'.uid_foreign':'').' '.
						$whereClause,	// whereClauseMightContainGroupOrderBy
					$groupBy,
					$orderBy,
					$limit
				);
		return $query;
	}

	/**
	 * Creates an INSERT SQL-statement for $table from the array with field/value pairs $fields_values.
	 * DEPRECATED - $GLOBALS['TYPO3_DB']->INSERTquery() directly instead! But better yet, use $GLOBALS['TYPO3_DB']->exec_INSERTquery()
	 *
	 * @param	string		Table name
	 * @param	array		Field values as key=>value pairs.
	 * @return	string		Full SQL query for INSERT
	 * @deprecated
	 */
	public static function DBcompileInsert($table, $fields_values) {
		return $GLOBALS['TYPO3_DB']->INSERTquery($table, $fields_values);
	}

	/**
	 * Creates an UPDATE SQL-statement for $table where $where-clause (typ. 'uid=...') from the array with field/value pairs $fields_values.
	 * DEPRECATED - $GLOBALS['TYPO3_DB']->UPDATEquery() directly instead! But better yet, use $GLOBALS['TYPO3_DB']->exec_UPDATEquery()
	 *
	 * @param	string		Database tablename
	 * @param	string		WHERE clause, eg. "uid=1"
	 * @param	array		Field values as key=>value pairs.
	 * @return	string		Full SQL query for UPDATE
	 * @deprecated
	 */
	public static function DBcompileUpdate($table, $where, $fields_values) {
		return $GLOBALS['TYPO3_DB']->UPDATEquery($table, $where, $fields_values);
	}










	/*******************************************
	 *
	 * Page tree, TCA related
	 *
	 *******************************************/

	/**
	 * Returns what is called the 'RootLine'. That is an array with information about the page records from a page id ($uid) and back to the root.
	 * By default deleted pages are filtered.
	 * This RootLine will follow the tree all the way to the root. This is opposite to another kind of root line known from the frontend where the rootline stops when a root-template is found.
	 * Usage: 1
	 *
	 * @param	integer		Page id for which to create the root line.
	 * @param	string		$clause can be used to select other criteria. It would typically be where-clauses that stops the process if we meet a page, the user has no reading access to.
	 * @param	boolean		If true, version overlay is applied. This must be requested specifically because it is usually only wanted when the rootline is used for visual output while for permission checking you want the raw thing!
	 * @return	array		Root line array, all the way to the page tree root (or as far as $clause allows!)
	 */
	public static function BEgetRootLine($uid, $clause = '', $workspaceOL = FALSE) {
		if (is_array($GLOBALS['T3_VAR']['BEgetRootLine_cache'][$uid][$clause][$workspaceOL?1:0])) {
			return $GLOBALS['T3_VAR']['BEgetRootLine_cache'][$uid][$clause][$workspaceOL?1:0];
		}
		$pid = $uid;
		$loopCheck = 100;
		$theRowArray = Array();
		$output = Array();
		while ($uid!=0 && $loopCheck>0) {
			$loopCheck--;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'pid,uid,title,TSconfig,is_siteroot,storage_pid,t3ver_oid,t3ver_wsid,t3ver_state,t3ver_swapmode,t3ver_stage',
				'pages',
				'uid='.intval($uid).' '.
					t3lib_BEfunc::deleteClause('pages').' '.
					$clause		// whereClauseMightContainGroupOrderBy
			);
			if ($GLOBALS['TYPO3_DB']->sql_error()) {
				debug($GLOBALS['TYPO3_DB']->sql_error(), 1);
			}
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if($workspaceOL)	t3lib_BEfunc::workspaceOL('pages', $row);
				if (is_array($row)) {
					t3lib_BEfunc::fixVersioningPid('pages', $row);
					$uid = $row['pid'];
					$theRowArray[] = $row;
				} else break;
			} else {
				break;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		if ($uid==0) {$theRowArray[] = Array('uid'=>0, 'title'=>'');}
		if (is_array($theRowArray)) {
			reset($theRowArray);
			$c = count($theRowArray);
			while(list($key, $val) = each($theRowArray)) {
				$c--;
				$output[$c]['uid'] = $val['uid'];
				$output[$c]['pid'] = $val['pid'];
				if (isset($val['_ORIG_pid'])) $output[$c]['_ORIG_pid'] = $val['_ORIG_pid'];
				$output[$c]['title'] = $val['title'];
				$output[$c]['TSconfig'] = $val['TSconfig'];
				$output[$c]['is_siteroot'] = $val['is_siteroot'];
				$output[$c]['storage_pid'] = $val['storage_pid'];
				$output[$c]['t3ver_oid'] = $val['t3ver_oid'];
				$output[$c]['t3ver_wsid'] = $val['t3ver_wsid'];
				$output[$c]['t3ver_state'] = $val['t3ver_state'];
				$output[$c]['t3ver_swapmode'] = $val['t3ver_swapmode'];
				$output[$c]['t3ver_stage'] = $val['t3ver_stage'];
			}
		}
		$GLOBALS['T3_VAR']['BEgetRootLine_cache'][$pid][$clause][$workspaceOL?1:0] = $output;

		return $output;
	}

	/**
	 * Opens the page tree to the specified page id
	 *
	 * @param	integer		Page id.
	 * @param	boolean		If set, then other open branches are closed.
	 * @return	void
	 */
	public static function openPageTree($pid, $clearExpansion) {
		global $BE_USER;

			// Get current expansion data:
		if ($clearExpansion) {
			$expandedPages = array();
		} else {
			$expandedPages = unserialize($BE_USER->uc['browseTrees']['browsePages']);
		}

			// Get rootline:
		$rL = t3lib_BEfunc::BEgetRootLine($pid);

			// First, find out what mount index to use (if more than one DB mount exists):
		$mountIndex = 0;
		$mountKeys = array_flip($BE_USER->returnWebmounts());
		foreach($rL as $rLDat) {
			if (isset($mountKeys[$rLDat['uid']])) {
				$mountIndex = $mountKeys[$rLDat['uid']];
				break;
			}
		}

			// Traverse rootline and open paths:
		foreach($rL as $rLDat) {
			$expandedPages[$mountIndex][$rLDat['uid']] = 1;
		}

			// Write back:
		$BE_USER->uc['browseTrees']['browsePages'] = serialize($expandedPages);
		$BE_USER->writeUC();
	}

	/**
	 * Returns the path (visually) of a page $uid, fx. "/First page/Second page/Another subpage"
	 * Each part of the path will be limited to $titleLimit characters
	 * Deleted pages are filtered out.
	 * Usage: 15
	 *
	 * @param	integer		Page uid for which to create record path
	 * @param	string		$clause is additional where clauses, eg. "
	 * @param	integer		Title limit
	 * @param	integer		Title limit of Full title (typ. set to 1000 or so)
	 * @return	mixed		Path of record (string) OR array with short/long title if $fullTitleLimit is set.
	 */
	public static function getRecordPath($uid, $clause, $titleLimit, $fullTitleLimit = 0) {
		if (!$titleLimit) { $titleLimit = 1000; }

		$loopCheck = 100;
		$output = $fullOutput = '/';
		while ($uid!=0 && $loopCheck>0) {
			$loopCheck--;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'uid,pid,title,t3ver_oid,t3ver_wsid,t3ver_swapmode',
						'pages',
						'uid='.intval($uid).
							t3lib_BEfunc::deleteClause('pages').
							(strlen(trim($clause)) ? ' AND '.$clause : '')
					);
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				t3lib_BEfunc::workspaceOL('pages', $row);
				if (is_array($row)) {
					t3lib_BEfunc::fixVersioningPid('pages', $row);

					if ($row['_ORIG_pid'] && $row['t3ver_swapmode']>0)	{	// Branch points
						$output = ' [#VEP#]'.$output;		// Adding visual token - Versioning Entry Point - that tells that THIS position was where the versionized branch got connected to the main tree. I will have to find a better name or something...
					}
					$uid = $row['pid'];
					$output = '/'.t3lib_div::fixed_lgd_cs(strip_tags($row['title']), $titleLimit).$output;
					if ($fullTitleLimit)	$fullOutput = '/'.t3lib_div::fixed_lgd_cs(strip_tags($row['title']), $fullTitleLimit).$fullOutput;
				} else break;
			} else {
				break;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}

		if ($fullTitleLimit) {
			return array($output, $fullOutput);
		} else {
			return $output;
		}
	}

	/**
	 * Returns an array with the exclude-fields as defined in TCA
	 * Used for listing the exclude-fields in be_groups forms
	 * Usage: 2 (t3lib_tceforms + t3lib_transferdata)
	 *
	 * @return	array		Array of arrays with excludeFields (fieldname, table:fieldname) from all TCA entries
	 */
	public static function getExcludeFields() {
		global $TCA;
			// All TCA keys:
		$theExcludeArray = Array();
		$tc_keys = array_keys($TCA);
		foreach($tc_keys as $table) {
				// Load table
			t3lib_div::loadTCA($table);
				// All field names configured:
			if (is_array($TCA[$table]['columns'])) {
				$f_keys = array_keys($TCA[$table]['columns']);
				foreach($f_keys as $field) {
					if ($TCA[$table]['columns'][$field]['exclude']) {
							// Get Human Readable names of fields and table:
						$Fname = $GLOBALS['LANG']->sl($TCA[$table]['ctrl']['title']).': '.$GLOBALS['LANG']->sl($TCA[$table]['columns'][$field]['label']);
							// add entry:
						$theExcludeArray[] = Array($Fname, $table.':'.$field);
					}
				}
			}
		}
		return $theExcludeArray;
	}

	/**
	 * Returns an array with explicit Allow/Deny fields.
	 * Used for listing these field/value pairs in be_groups forms
	 *
	 * @return	array		Array with information from all of $TCA
	 */
	public static function getExplicitAuthFieldValues() {
		global $TCA;

			// Initialize:
		$adLabel = array(
			'ALLOW' => $GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_core.xml:labels.allow'),
			'DENY' => $GLOBALS['LANG']->sl('LLL:EXT:lang/locallang_core.xml:labels.deny'),
		);

			// All TCA keys:
		$allowDenyOptions = Array();
		$tc_keys = array_keys($TCA);
		foreach($tc_keys as $table)	{

				// Load table
			t3lib_div::loadTCA($table);

				// All field names configured:
			if (is_array($TCA[$table]['columns'])) {
				$f_keys = array_keys($TCA[$table]['columns']);
				foreach($f_keys as $field) {
					$fCfg = $TCA[$table]['columns'][$field]['config'];
					if ($fCfg['type']=='select' && $fCfg['authMode'])	{

							// Check for items:
						if (is_array($fCfg['items'])) {
								// Get Human Readable names of fields and table:
							$allowDenyOptions[$table.':'.$field]['tableFieldLabel'] = $GLOBALS['LANG']->sl($TCA[$table]['ctrl']['title']).': '.$GLOBALS['LANG']->sl($TCA[$table]['columns'][$field]['label']);

								// Check for items:
							foreach($fCfg['items'] as $iVal) {
								if (strcmp($iVal[1], ''))	{	// Values '' is not controlled by this setting.

										// Find iMode:
									$iMode = '';
									switch((string)$fCfg['authMode']) {
										case 'explicitAllow':
											$iMode = 'ALLOW';
										break;
										case 'explicitDeny':
											$iMode = 'DENY';
										break;
										case 'individual':
											if (!strcmp($iVal[4], 'EXPL_ALLOW')) {
												$iMode = 'ALLOW';
											} elseif (!strcmp($iVal[4], 'EXPL_DENY')) {
												$iMode = 'DENY';
											}
										break;
									}

										// Set iMode:
									if ($iMode) {
										$allowDenyOptions[$table.':'.$field]['items'][$iVal[1]] = array($iMode, $GLOBALS['LANG']->sl($iVal[0]), $adLabel[$iMode]);
									}
								}
							}
						}
					}
				}
			}
		}

		return $allowDenyOptions;
	}

	/**
	 * Returns an array with system languages:
	 *
	 * @return	array		Array with languages
	 */
	public static function getSystemLanguages()	{

			// Initialize, add default language:
		$sysLanguages = array();
		$sysLanguages[] = array('Default language', 0);

			// Traverse languages
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title,flag', 'sys_language', 'pid=0'.t3lib_BEfunc::deleteClause('sys_language'));
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$sysLanguages[] = array($row['title'].' ['.$row['uid'].']', $row['uid'], ($row['flag'] ? 'flags/'.$row['flag'] : ''));
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $sysLanguages;
	}

	/**
	 * Determines whether a table is localizable and has the languageField and transOrigPointerField set in $TCA.
	 *
	 * @param	string		$table: The table to check
	 * @return	boolean		Whether a table is localizable
	 */
	public function isTableLocalizable($table) {
		$isLocalizable = false;
		if (isset($GLOBALS['TCA'][$table]['ctrl']) && is_array($GLOBALS['TCA'][$table]['ctrl'])) {
			$tcaCtrl = $GLOBALS['TCA'][$table]['ctrl'];
			$isLocalizable = (isset($tcaCtrl['languageField']) && $tcaCtrl['languageField'] && isset($tcaCtrl['transOrigPointerField']) && $tcaCtrl['transOrigPointerField']);
		}
		return $isLocalizable;
	}

	/**
	 * Returns the value of the property localizationMode in the given $config array ($TCA[<table>]['columns'][<field>]['config']).
	 * If the table is prepared for localization and no localizationMode is set, 'select' is returned by default.
	 * If the table is not prepared for localization or not defined at all in $TCA, false is returned.
	 *
	 * @param	string		$table: The name of the table to lookup in TCA
	 * @param	mixed		$fieldOrConfig: The fieldname (string) or the configuration of the field to check (array)
	 * @return	mixed		If table is localizable, the set localizationMode is returned (if property is not set, 'select' is returned by default); if table is not localizable, false is returned
	 */
	public function getInlineLocalizationMode($table, $fieldOrConfig) {
		$localizationMode = false;
		if (is_array($fieldOrConfig) && count($fieldOrConfig)) {
			$config = $fieldOrConfig;
		} elseif (is_string($fieldOrConfig) && isset($GLOBALS['TCA'][$table]['columns'][$fieldOrConfig]['config'])) {
			$config = $GLOBALS['TCA'][$table]['columns'][$fieldOrConfig]['config'];
		}
		if (is_array($config) && isset($config['type']) && $config['type']=='inline' && self::isTableLocalizable($table)) {
			$localizationMode = (isset($config['behaviour']['localizationMode']) && $config['behaviour']['localizationMode'] ? $config['behaviour']['localizationMode'] : 'select');
				// The mode 'select' is not possible when child table is not localizable at all:
			if ($localizationMode=='select' && !self::isTableLocalizable($config['foreign_table'])) {
				$localizationMode = false;
			}
		}
		return $localizationMode;
	}

	/**
	 * Returns a page record (of page with $id) with an extra field "_thePath" set to the record path IF the WHERE clause, $perms_clause, selects the record. Thus is works as an access check that returns a page record if access was granted, otherwise not.
	 * If $id is zero a pseudo root-page with "_thePath" set is returned IF the current BE_USER is admin.
	 * In any case ->isInWebMount must return true for the user (regardless of $perms_clause)
	 * Usage: 21
	 *
	 * @param	integer		Page uid for which to check read-access
	 * @param	string		$perms_clause is typically a value generated with $BE_USER->getPagePermsClause(1);
	 * @return	array		Returns page record if OK, otherwise false.
	 */
	public static function readPageAccess($id, $perms_clause) {
		if ((string)$id!='') {
			$id = intval($id);
			if (!$id) {
				if ($GLOBALS['BE_USER']->isAdmin()) {
					$path = '/';
					$pageinfo['_thePath'] = $path;
					return $pageinfo;
				}
			} else {
				$pageinfo = t3lib_BEfunc::getRecord('pages', $id, '*', ($perms_clause ? ' AND '.$perms_clause : ''));
				if ($pageinfo['uid'] && $GLOBALS['BE_USER']->isInWebMount($id, $perms_clause)) {
					t3lib_BEfunc::workspaceOL('pages', $pageinfo);
					if (is_array($pageinfo)) {
						t3lib_BEfunc::fixVersioningPid('pages', $pageinfo);
						list($pageinfo['_thePath'], $pageinfo['_thePathFull']) = t3lib_BEfunc::getRecordPath(intval($pageinfo['uid']), $perms_clause, 15, 1000);
						return $pageinfo;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Returns the "types" configuration parsed into an array for the record, $rec, from table, $table
	 * Usage: 6
	 *
	 * @param	string		Table name (present in TCA)
	 * @param	array		Record from $table
	 * @param	boolean		If $useFieldNameAsKey is set, then the fieldname is associative keys in the return array, otherwise just numeric keys.
	 * @return	array
	 */
	public static function getTCAtypes($table, $rec, $useFieldNameAsKey = 0) {
		global $TCA;

		t3lib_div::loadTCA($table);
		if ($TCA[$table])	{

				// Get type value:
			$fieldValue = t3lib_BEfunc::getTCAtypeValue($table, $rec);

				// Get typesConf
			$typesConf = $TCA[$table]['types'][$fieldValue];

				// Get fields list and traverse it
			$fieldList = explode(',', $typesConf['showitem']);
			$altFieldList = array();

				// Traverse fields in types config and parse the configuration into a nice array:
			foreach($fieldList as $k => $v) {
				list($pFieldName, $pAltTitle, $pPalette, $pSpec) = t3lib_div::trimExplode(';', $v);
				$defaultExtras = is_array($TCA[$table]['columns'][$pFieldName]) ? $TCA[$table]['columns'][$pFieldName]['defaultExtras'] : '';
				$specConfParts = t3lib_BEfunc::getSpecConfParts($pSpec, $defaultExtras);

				$fieldList[$k] = array(
					'field' => $pFieldName,
					'title' => $pAltTitle,
					'palette' => $pPalette,
					'spec' => $specConfParts,
					'origString' => $v
				);
				if ($useFieldNameAsKey) {
					$altFieldList[$fieldList[$k]['field']] = $fieldList[$k];
				}
			}
			if ($useFieldNameAsKey) {
				$fieldList = $altFieldList;
			}

				// Return array:
			return $fieldList;
		}
	}

	/**
	 * Returns the "type" value of $rec from $table which can be used to look up the correct "types" rendering section in $TCA
	 * If no "type" field is configured in the "ctrl"-section of the $TCA for the table, zero is used.
	 * If zero is not an index in the "types" section of $TCA for the table, then the $fieldValue returned will default to 1 (no matter if that is an index or not)
	 * Usage: 7
	 *
	 * @param	string		Table name present in TCA
	 * @param	array		Record from $table
	 * @return	string		Field value
	 * @see getTCAtypes()
	 */
	public static function getTCAtypeValue($table, $rec) {
		global $TCA;

			// If no field-value, set it to zero. If there is no type matching the field-value (which now may be zero...) test field-value '1' as default.
		t3lib_div::loadTCA($table);
		if ($TCA[$table]) {
			$field = $TCA[$table]['ctrl']['type'];
			$fieldValue = $field ? ($rec[$field] ? $rec[$field] : 0) : 0;
			if (!is_array($TCA[$table]['types'][$fieldValue]))	$fieldValue = 1;
			return $fieldValue;
		}
	}

	/**
	 * Parses a part of the field lists in the "types"-section of $TCA arrays, namely the "special configuration" at index 3 (position 4)
	 * Elements are splitted by ":" and within those parts, parameters are splitted by "|".
	 * Everything is returned in an array and you should rather see it visually than listen to me anymore now...  Check out example in Inside TYPO3
	 * Usage: 5
	 *
	 * @param	string		Content from the "types" configuration of TCA (the special configuration) - see description of function
	 * @param	string		The ['defaultExtras'] value from field configuration
	 * @return	array
	 */
	public static function getSpecConfParts($str, $defaultExtras)	{

			// Add defaultExtras:
		$specConfParts = t3lib_div::trimExplode(':', $defaultExtras.':'.$str, 1);

		$reg = array();
		if (count($specConfParts)) {
			foreach($specConfParts as $k2 => $v2) {
				unset($specConfParts[$k2]);
				if (ereg('(.*)\[(.*)\]', $v2, $reg)) {
					$specConfParts[trim($reg[1])] = array(
						'parameters' => t3lib_div::trimExplode('|', $reg[2], 1)
					);
				} else {
					$specConfParts[trim($v2)] = 1;
				}
			}
		} else {
			$specConfParts = array();
		}
		return $specConfParts;
	}

	/**
	 * Takes an array of "[key] = [value]" strings and returns an array with the keys set as keys pointing to the value.
	 * Better see it in action! Find example in Inside TYPO3
	 * Usage: 6
	 *
	 * @param	array		Array of "[key] = [value]" strings to convert.
	 * @return	array
	 */
	public static function getSpecConfParametersFromArray($pArr) {
		$out = array();
		if (is_array($pArr)) {
			reset($pArr);
			while(list($k, $v) = each($pArr)) {
				$parts = explode('=', $v, 2);
				if (count($parts)==2) {
					$out[trim($parts[0])] = trim($parts[1]);
				} else {
					$out[$k] = $v;
				}
			}
		}
		return $out;
	}

	/**
	 * Finds the Data Structure for a FlexForm field
	 * NOTE ON data structures for deleted records: This function may fail to deliver the data structure for a record for a few reasons: a) The data structure could be deleted (either with deleted-flagged or hard-deleted), b) the data structure is fetched using the ds_pointerField_searchParent in which case any deleted record on the route to the final location of the DS will make it fail. In theory, we can solve the problem in the case where records that are deleted-flagged keeps us from finding the DS - this is done at the markers ###NOTE_A### where we make sure to also select deleted records. However, we generally want the DS lookup to fail for deleted records since for the working website we expect a deleted-flagged record to be as inaccessible as one that is completely deleted from the DB. Any way we look at it, this may lead to integrity problems of the reference index and even lost files if attached. However, that is not really important considering that a single change to a data structure can instantly invalidate large amounts of the reference index which we do accept as a cost for the flexform features. Other than requiring a reference index update, deletion of/changes in data structure or the failure to look them up when completely deleting records may lead to lost files in the uploads/ folders since those are now without a proper reference.
	 * Usage: 5
	 *
	 * @param	array		Field config array
	 * @param	array		Record data
	 * @param	string		The table name
	 * @param	string		Optional fieldname passed to hook object
	 * @param	boolean		Boolean; If set, workspace overlay is applied to records. This is correct behaviour for all presentation and export, but NOT if you want a true reflection of how things are in the live workspace.
	 * @param	integer		SPECIAL CASES: Use this, if the DataStructure may come from a parent record and the INPUT row doesn't have a uid yet (hence, the pid cannot be looked up). Then it is necessary to supply a PID value to search recursively in for the DS (used from TCEmain)
	 * @return	mixed		If array, the data structure was found and returned as an array. Otherwise (string) it is an error message.
	 * @see t3lib_TCEforms::getSingleField_typeFlex()
	 */
	public static function getFlexFormDS($conf, $row, $table, $fieldName = '', $WSOL = TRUE, $newRecordPidValue = 0) {
		global $TYPO3_CONF_VARS;

			// Get pointer field etc from TCA-config:
		$ds_pointerField = 	$conf['ds_pointerField'];
		$ds_array = 		$conf['ds'];
		$ds_tableField = 	$conf['ds_tableField'];
		$ds_searchParentField = 	$conf['ds_pointerField_searchParent'];

			// Find source value:
		$dataStructArray = '';
		if (is_array($ds_array))	{	// If there is a data source array, that takes precedence
				// If a pointer field is set, take the value from that field in the $row array and use as key.
			if ($ds_pointerField)	{

					// Up to two pointer fields can be specified in a comma separated list.
				$pointerFields = t3lib_div::trimExplode(',', $ds_pointerField);
				if(count($pointerFields) == 2) { // If we have two pointer fields, the array keys should contain both field values separated by comma. The asterisk "*" catches all values. For backwards compatibility, it's also possible to specify only the value of the first defined ds_pointerField.
					if($ds_array[$row[$pointerFields[0]].','.$row[$pointerFields[1]]]) {	// Check if we have a DS for the combination of both pointer fields values
						$srcPointer = $row[$pointerFields[0]].','.$row[$pointerFields[1]];
					} elseif($ds_array[$row[$pointerFields[1]].',*']) {	// Check if we have a DS for the value of the first pointer field suffixed with ",*"
						$srcPointer = $row[$pointerFields[1]].',*';
					} elseif($ds_array['*,'.$row[$pointerFields[1]]]) {	// Check if we have a DS for the value of the second pointer field prefixed with "*,"
						$srcPointer = '*,'.$row[$pointerFields[1]];
					} elseif($ds_array[$row[$pointerFields[0]]]) {	// Check if we have a DS for just the value of the first pointer field (mainly for backwards compatibility)
						$srcPointer = $row[$pointerFields[0]];
					}
				} else {
					$srcPointer = $row[$pointerFields[0]];
				}

				$srcPointer = isset($ds_array[$srcPointer]) ? $srcPointer : 'default';
			} else $srcPointer = 'default';

				// Get Data Source: Detect if it's a file reference and in that case read the file and parse as XML. Otherwise the value is expected to be XML.
			if (substr($ds_array[$srcPointer], 0, 5)=='FILE:') {
				$file = t3lib_div::getFileAbsFileName(substr($ds_array[$srcPointer], 5));
				if ($file && @is_file($file)) {
					$dataStructArray = t3lib_div::xml2array(t3lib_div::getUrl($file));
				} else $dataStructArray = 'The file "'.substr($ds_array[$srcPointer], 5).'" in ds-array key "'.$srcPointer.'" was not found ("'.$file.'")';	// Error message.
			} else {
				$dataStructArray = t3lib_div::xml2array($ds_array[$srcPointer]);
			}

		} elseif ($ds_pointerField) {	// If pointer field AND possibly a table/field is set:
				// Value of field pointed to:
			$srcPointer = $row[$ds_pointerField];

				// Searching recursively back if 'ds_pointerField_searchParent' is defined (typ. a page rootline, or maybe a tree-table):
			if ($ds_searchParentField && !$srcPointer) {
				$rr = t3lib_BEfunc::getRecord($table, $row['uid'], 'uid,'.$ds_searchParentField);	// Get the "pid" field - we cannot know that it is in the input record! ###NOTE_A###
				if ($WSOL) {
					t3lib_BEfunc::workspaceOL($table, $rr);
					t3lib_BEfunc::fixVersioningPid($table, $rr, TRUE);	// Added "TRUE" 23/03/06 before 4.0. (Also to similar call below!).  Reason: When t3lib_refindex is scanning the system in Live workspace all Pages with FlexForms will not find their inherited datastructure. Thus all references from workspaces are removed! Setting TRUE means that versioning PID doesn't check workspace of the record. I can't see that this should give problems anywhere. See more information inside t3lib_refindex!
				}
				$uidAcc = array();	// Used to avoid looping, if any should happen.
				$subFieldPointer = $conf['ds_pointerField_searchParent_subField'];
				while(!$srcPointer)		{

					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'uid,'.$ds_pointerField.','.$ds_searchParentField.($subFieldPointer?','.$subFieldPointer:''),
									$table,
									'uid='.intval($newRecordPidValue ? $newRecordPidValue : $rr[$ds_searchParentField]).t3lib_BEfunc::deleteClause($table)	###NOTE_A###
								);
					$newRecordPidValue = 0;
					$rr = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					$GLOBALS['TYPO3_DB']->sql_free_result($res);

						// break if no result from SQL db or if looping...
					if (!is_array($rr) || isset($uidAcc[$rr['uid']]))	break;
					$uidAcc[$rr['uid']] = 1;

					if ($WSOL) {
						t3lib_BEfunc::workspaceOL($table, $rr);
						t3lib_BEfunc::fixVersioningPid($table, $rr, TRUE);
					}
					$srcPointer = ($subFieldPointer && $rr[$subFieldPointer]) ? $rr[$subFieldPointer] : $rr[$ds_pointerField];
				}
			}

				// If there is a srcPointer value:
			if ($srcPointer) {
				if (t3lib_div::testInt($srcPointer))	{	// If integer, then its a record we will look up:
					list($tName, $fName) = explode(':', $ds_tableField, 2);
					if ($tName && $fName && is_array($GLOBALS['TCA'][$tName])) {
						$dataStructRec = t3lib_BEfunc::getRecord($tName, $srcPointer);
						if ($WSOL) {
							t3lib_BEfunc::workspaceOL($tName, $dataStructRec);
						}
						$dataStructArray = t3lib_div::xml2array($dataStructRec[$fName]);
					} else $dataStructArray = 'No tablename ('.$tName.') or fieldname ('.$fName.') was found an valid!';
				} else {	// Otherwise expect it to be a file:
					$file = t3lib_div::getFileAbsFileName($srcPointer);
					if ($file && @is_file($file)) {
						$dataStructArray = t3lib_div::xml2array(t3lib_div::getUrl($file));
					} else $dataStructArray = 'The file "'.$srcPointer.'" was not found ("'.$file.'")';	// Error message.
				}
			} else $dataStructArray = 'No source value in fieldname "'.$ds_pointerField.'"';	// Error message.
		} else $dataStructArray = 'No proper configuration!';

			// Hook for post-processing the Flexform DS. Introduces the possibility to configure Flexforms via TSConfig
		if (is_array ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'])) {
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'] as $classRef) {
				$hookObj = &t3lib_div::getUserObj($classRef);
				if (method_exists($hookObj, 'getFlexFormDS_postProcessDS')) {
					$hookObj->getFlexFormDS_postProcessDS($dataStructArray, $conf, $row, $table, $fieldName);
				}
			}
		}

		return $dataStructArray;
	}


















	/*******************************************
	 *
	 * Caching related
	 *
	 *******************************************/

	/**
	 * Stores the string value $data in the 'cache_hash' table with the hash key, $hash, and visual/symbolic identification, $ident
	 * IDENTICAL to the function by same name found in t3lib_page:
	 * Usage: 2
	 *
	 * @param	string		32 bit hash string (eg. a md5 hash of a serialized array identifying the data being stored)
	 * @param	string		The data string. If you want to store an array, then just serialize it first.
	 * @param	string		$ident is just a textual identification in order to inform about the content! May be 20 characters long.
	 * @return	void
	 */
	public static function storeHash($hash, $data, $ident) {
		$insertFields = array(
			'hash' => $hash,
			'content' => $data,
			'ident' => $ident,
			'tstamp' => time()
		);
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_hash', 'hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($hash, 'cache_hash'));
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('cache_hash', $insertFields);
	}

	/**
	 * Returns string value stored for the hash string in the table "cache_hash"
	 * Can be used to retrieved a cached value
	 * IDENTICAL to the function by same name found in t3lib_page
	 * Usage: 2
	 *
	 * @param	string		Hash key, 32 bytes hex
	 * @param	integer		$expTime represents the expire time in seconds. For instance a value of 3600 would allow cached content within the last hour, otherwise nothing is returned.
	 * @return	string
	 */
	public static function getHash($hash, $expTime = 0) {
			// if expTime is not set, the hash will never expire
		$expTime = intval($expTime);
		if ($expTime) {
			$whereAdd = ' AND tstamp > '.(time()-$expTime);
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('content', 'cache_hash', 'hash='.$GLOBALS['TYPO3_DB']->fullQuoteStr($hash, 'cache_hash').$whereAdd);
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return (is_array($row) ? $row['content'] : null);
	}








	/*******************************************
	 *
	 * TypoScript related
	 *
	 *******************************************/

	/**
	 * Returns the Page TSconfig for page with id, $id
	 * Requires class "t3lib_TSparser"
	 * Usage: 26 (spec. in ext info_pagetsconfig)
	 *
	 * @param	integer		Page uid for which to create Page TSconfig
	 * @param	array		If $rootLine is an array, that is used as rootline, otherwise rootline is just calculated
	 * @param	boolean		If $returnPartArray is set, then the array with accumulated Page TSconfig is returned non-parsed. Otherwise the output will be parsed by the TypoScript parser.
	 * @return	array		Page TSconfig
	 * @see t3lib_TSparser
	 */
	public static function getPagesTSconfig($id, $rootLine = '', $returnPartArray = 0) {
		$id = intval($id);
		if (!is_array($rootLine)) {
			$rootLine = t3lib_BEfunc::BEgetRootLine($id, '', TRUE);
		}
		ksort($rootLine);	// Order correctly
		$TSdataArray = array();
		$TSdataArray['defaultPageTSconfig'] = $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'];	// Setting default configuration:
		foreach($rootLine as $k => $v) {
			$TSdataArray['uid_'.$v['uid']] = $v['TSconfig'];
		}
		$TSdataArray = t3lib_TSparser::checkIncludeLines_array($TSdataArray);
		if ($returnPartArray) {
			return $TSdataArray;
		}

			// Parsing the user TS (or getting from cache)
		$userTS = implode($TSdataArray, chr(10).'[GLOBAL]'.chr(10));
		$hash = md5('pageTS:'.$userTS);
		$cachedContent = t3lib_BEfunc::getHash($hash, 0);
		$TSconfig = array();
		if (isset($cachedContent)) {
			$TSconfig = unserialize($cachedContent);
		} else {
			$parseObj = t3lib_div::makeInstance('t3lib_TSparser');
			$parseObj->parse($userTS);
			$TSconfig = $parseObj->setup;
			t3lib_BEfunc::storeHash($hash, serialize($TSconfig), 'PAGES_TSconfig');
		}

			// get User TSconfig overlay
		$userTSconfig = $GLOBALS['BE_USER']->userTS['page.'];
		if (is_array($userTSconfig)) {
			$TSconfig = t3lib_div::array_merge_recursive_overrule($TSconfig, $userTSconfig);
		}
		return $TSconfig;
	}

	/**
	 * Updates Page TSconfig for a page with $id
	 * The function seems to take $pageTS as an array with properties and compare the values with those that already exists for the "object string", $TSconfPrefix, for the page, then sets those values which were not present.
	 * $impParams can be supplied as already known Page TSconfig, otherwise it's calculated.
	 *
	 * THIS DOES NOT CHECK ANY PERMISSIONS. SHOULD IT?
	 * More documentation is needed.
	 *
	 * Usage: 1 (ext. direct_mail)
	 *
	 * @param	integer		Page id
	 * @param	array		Page TS array to write
	 * @param	string		Prefix for object paths
	 * @param	array		[Description needed.]
	 * @return	void
	 * @internal
	 * @see implodeTSParams(), getPagesTSconfig()
	 */
	public static function updatePagesTSconfig($id, $pageTS, $TSconfPrefix, $impParams = '') {
		$id = intval($id);
		if (is_array($pageTS) && $id>0) {
			if (!is_array($impParams)) {
				$impParams =t3lib_BEfunc::implodeTSParams(t3lib_BEfunc::getPagesTSconfig($id));
			}
			reset($pageTS);
			$set = array();
			while(list($f, $v) = each($pageTS)) {
				$f = $TSconfPrefix.$f;
				if ((!isset($impParams[$f])&&trim($v)) || strcmp(trim($impParams[$f]), trim($v))) {
					$set[$f] = trim($v);
				}
			}
			if (count($set)) {
					// Get page record and TS config lines
				$pRec = t3lib_befunc::getRecord('pages', $id);
				$TSlines = explode(chr(10), $pRec['TSconfig']);
				$TSlines = array_reverse($TSlines);
					// Reset the set of changes.
				reset($set);
				while(list($f, $v) = each($set)) {
					reset($TSlines);
					$inserted = 0;
					while(list($ki, $kv) = each($TSlines)) {
						if (substr($kv, 0, strlen($f)+1)==$f.'=') {
							$TSlines[$ki] = $f.'='.$v;
							$inserted = 1;
							break;
						}
					}
					if (!$inserted) {
						$TSlines = array_reverse($TSlines);
						$TSlines[] = $f.'='.$v;
						$TSlines = array_reverse($TSlines);
					}
				}
				$TSlines = array_reverse($TSlines);

					// store those changes
				$TSconf = implode(chr(10), $TSlines);

				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('pages', 'uid='.intval($id), array('TSconfig' => $TSconf));
			}
		}
	}

	/**
	 * Implodes a multi dimensional TypoScript array, $p, into a one-dimentional array (return value)
	 * Usage: 3
	 *
	 * @param	array		TypoScript structure
	 * @param	string		Prefix string
	 * @return	array		Imploded TypoScript objectstring/values
	 */
	public static function implodeTSParams($p, $k = '') {
		$implodeParams = array();
		if (is_array($p)) {
			reset($p);
			while(list($kb, $val) = each($p)) {
				if (is_array($val)) {
					$implodeParams = array_merge($implodeParams, t3lib_BEfunc::implodeTSParams($val, $k.$kb));
				} else {
					$implodeParams[$k.$kb] = $val;
				}
			}
		}
		return $implodeParams;
	}








	/*******************************************
	 *
	 * Users / Groups related
	 *
	 *******************************************/

	/**
	 * Returns an array with be_users records of all user NOT DELETED sorted by their username
	 * Keys in the array is the be_users uid
	 * Usage: 14 (spec. ext. "beuser" and module "web_perm")
	 *
	 * @param	string		Optional $fields list (default: username,usergroup,usergroup_cached_list,uid) can be used to set the selected fields
	 * @param	string		Optional $where clause (fx. "AND username='pete'") can be used to limit query
	 * @return	array
	 */
	public static function getUserNames($fields = 'username,usergroup,usergroup_cached_list,uid', $where = '') {
		$be_user_Array = Array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, 'be_users', 'pid=0 '.$where.t3lib_BEfunc::deleteClause('be_users'), '', 'username');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$be_user_Array[$row['uid']] = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $be_user_Array;
	}

	/**
	 * Returns an array with be_groups records (title, uid) of all groups NOT DELETED sorted by their title
	 * Usage: 8 (spec. ext. "beuser" and module "web_perm")
	 *
	 * @param	string		Field list
	 * @param	string		WHERE clause
	 * @return	array
	 */
	public static function getGroupNames($fields = 'title,uid', $where = '') {
		$be_group_Array = Array();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, 'be_groups', 'pid=0 '.$where.t3lib_BEfunc::deleteClause('be_groups'), '', 'title');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$be_group_Array[$row['uid']] = $row;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $be_group_Array;
	}

	/**
	 * Returns an array with be_groups records (like ->getGroupNames) but:
	 * - if the current BE_USER is admin, then all groups are returned, otherwise only groups that the current user is member of (usergroup_cached_list) will be returned.
	 * Usage: 2 (module "web_perm" and ext. taskcenter)
	 *
	 * @param	string		Field list; $fields specify the fields selected (default: title,uid)
	 * @return	array
	 */
	public static function getListGroupNames($fields = 'title, uid') {
		$exQ = ' AND hide_in_lists=0';
		if (!$GLOBALS['BE_USER']->isAdmin()) {
			$exQ.=' AND uid IN ('.($GLOBALS['BE_USER']->user['usergroup_cached_list']?$GLOBALS['BE_USER']->user['usergroup_cached_list']:0).')';
		}
		return t3lib_BEfunc::getGroupNames($fields, $exQ);
	}

	/**
	 * Returns the array $usernames with the names of all users NOT IN $groupArray changed to the uid (hides the usernames!).
	 * If $excludeBlindedFlag is set, then these records are unset from the array $usernames
	 * Takes $usernames (array made by t3lib_BEfunc::getUserNames()) and a $groupArray (array with the groups a certain user is member of) as input
	 * Usage: 8
	 *
	 * @param	array		User names
	 * @param	array		Group names
	 * @param	boolean		If $excludeBlindedFlag is set, then these records are unset from the array $usernames
	 * @return	array		User names, blinded
	 */
	public static function blindUserNames($usernames, $groupArray, $excludeBlindedFlag = 0) {
		if (is_array($usernames) && is_array($groupArray)) {
			while(list($uid, $row) = each($usernames)) {
				$userN = $uid;
				$set = 0;
				if ($row['uid']!=$GLOBALS['BE_USER']->user['uid']) {
					reset($groupArray);
					while(list(,$v) = each($groupArray)) {
						if ($v && t3lib_div::inList($row['usergroup_cached_list'], $v)) {
							$userN = $row['username'];
							$set = 1;
						}
					}
				} else {
					$userN = $row['username'];
					$set = 1;
				}
				$usernames[$uid]['username'] = $userN;
				if ($excludeBlindedFlag && !$set) {unset($usernames[$uid]);}
			}
		}
		return $usernames;
	}

	/**
	 * Corresponds to blindUserNames but works for groups instead
	 * Usage: 2 (module web_perm)
	 *
	 * @param	array		Group names
	 * @param	array		Group names (reference)
	 * @param	boolean		If $excludeBlindedFlag is set, then these records are unset from the array $usernames
	 * @return	array
	 */
	public static function blindGroupNames($groups, $groupArray, $excludeBlindedFlag = 0) {
		if (is_array($groups) && is_array($groupArray)) {
			while(list($uid, $row) = each($groups)) {
				$groupN = $uid;
				$set = 0;
				if (t3lib_div::inArray($groupArray, $uid)) {
					$groupN = $row['title'];
					$set = 1;
				}
				$groups[$uid]['title'] = $groupN;
				if ($excludeBlindedFlag && !$set) {unset($groups[$uid]);}
			}
		}
		return $groups;
	}













	/*******************************************
	 *
	 * Output related
	 *
	 *******************************************/

	/**
	 * Returns the difference in days between input $tstamp and $EXEC_TIME
	 * Usage: 2 (class t3lib_BEfunc)
	 *
	 * @param	integer		Time stamp, seconds
	 * @return	integer
	 */
	public static function daysUntil($tstamp) {
		$delta_t = $tstamp-$GLOBALS['EXEC_TIME'];
		return ceil($delta_t/(3600*24));
	}

	/**
	 * Returns $tstamp formatted as "ddmmyy" (According to $TYPO3_CONF_VARS['SYS']['ddmmyy'])
	 * Usage: 11
	 *
	 * @param	integer		Time stamp, seconds
	 * @return	string		Formatted time
	 */
	public static function date($tstamp) {
		return date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $tstamp);
	}

	/**
	 * Returns $tstamp formatted as "ddmmyy hhmm" (According to $TYPO3_CONF_VARS['SYS']['ddmmyy'] AND $TYPO3_CONF_VARS['SYS']['hhmm'])
	 * Usage: 28
	 *
	 * @param	integer		Time stamp, seconds
	 * @return	string		Formatted time
	 */
	public static function datetime($value) {
		return date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'].' '.$GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'], $value);
	}

	/**
	 * Returns $value (in seconds) formatted as hh:mm:ss
	 * For instance $value = 3600 + 60*2 + 3 should return "01:02:03"
	 * Usage: 1 (class t3lib_BEfunc)
	 *
	 * @param	integer		Time stamp, seconds
	 * @param	boolean		Output hh:mm:ss. If false: hh:mm
	 * @return	string		Formatted time
	 */
	public static function time($value, $withSeconds = TRUE) {
		$hh = floor($value/3600);
		$min = floor(($value-$hh*3600)/60);
		$sec = $value-$hh*3600-$min*60;
		$l = sprintf('%02d', $hh).':'.sprintf('%02d', $min);
		if ($withSeconds) {
			$l .= ':'.sprintf('%02d', $sec);
		}
		return $l;
	}

	/**
	 * Returns the "age" in minutes / hours / days / years of the number of $seconds inputted.
	 * Usage: 15
	 *
	 * @param	integer		$seconds could be the difference of a certain timestamp and time()
	 * @param	string		$labels should be something like ' min| hrs| days| yrs'. This value is typically delivered by this function call: $GLOBALS["LANG"]->sL("LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears")
	 * @return	string		Formatted time
	 */
	public static function calcAge($seconds, $labels = 'min|hrs|days|yrs') {
		$labelArr = explode('|', $labels);
		$prefix = '';
		if ($seconds<0)	{$prefix = '-'; $seconds = abs($seconds);}
		if ($seconds<3600) {
			$seconds = round ($seconds/60).' '.trim($labelArr[0]);
		} elseif ($seconds<24*3600) {
			$seconds = round ($seconds/3600).' '.trim($labelArr[1]);
		} elseif ($seconds<365*24*3600) {
			$seconds = round ($seconds/(24*3600)).' '.trim($labelArr[2]);
		} else {
			$seconds = round ($seconds/(365*24*3600)).' '.trim($labelArr[3]);
		}
		return $prefix.$seconds;
	}

	/**
	 * Returns a formatted timestamp if $tstamp is set.
	 * The date/datetime will be followed by the age in parenthesis.
	 * Usage: 3
	 *
	 * @param	integer		Time stamp, seconds
	 * @param	integer		1/-1 depending on polarity of age.
	 * @param	string		$date=="date" will yield "dd:mm:yy" formatting, otherwise "dd:mm:yy hh:mm"
	 * @return	string
	 */
	public static function dateTimeAge($tstamp, $prefix = 1, $date = '') {
		return $tstamp ?
				($date=='date' ? t3lib_BEfunc::date($tstamp) : t3lib_BEfunc::datetime($tstamp)).
				' ('.t3lib_BEfunc::calcAge($prefix*(time()-$tstamp), $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')).')' : '';
	}

	/**
	 * Returns either title = '' or alt = '' attribute. This depends on the client browser and whether it supports title = '' or not (which is the default)
	 * If no $content is given only the attribute name is returned.
	 * The returned attribute with content will have a leading space char.
	 * Warning: Be careful to submit empty $content var - that will return just the attribute name!
	 * Usage: 0
	 *
	 * @param	string		String to set as title-attribute. If no $content is given only the attribute name is returned.
	 * @param	boolean		If $hsc is set, then content of the attribute is htmlspecialchar()'ed (which is good for XHTML and other reasons...)
	 * @return	string
	 * @deprecated		The idea made sense with older browsers, but now all browsers should support the "title" attribute - so just hardcode the title attribute instead!
	 */
	public static function titleAttrib($content = '', $hsc = 0) {
		global $CLIENT;
		$attrib= ($CLIENT['BROWSER']=='net'&&$CLIENT['VERSION']<5)||$CLIENT['BROWSER']=='konqu' ? 'alt' : 'title';
		return strcmp($content, '')?' '.$attrib.'="'.($hsc?htmlspecialchars($content):$content).'"' : $attrib;
	}

	/**
	 * Returns alt="" and title="" attributes with the value of $content.
	 * Usage: 7
	 *
	 * @param	string		Value for 'alt' and 'title' attributes (will be htmlspecialchars()'ed before output)
	 * @return	string
	 */
	public static function titleAltAttrib($content) {
		$out = '';
		$out.=' alt="'.htmlspecialchars($content).'"';
		$out.=' title="'.htmlspecialchars($content).'"';
		return $out;
	}

	/**
	 * Returns a linked image-tag for thumbnail(s)/fileicons/truetype-font-previews from a database row with a list of image files in a field
	 * All $TYPO3_CONF_VARS['GFX']['imagefile_ext'] extension are made to thumbnails + ttf file (renders font-example)
	 * Thumbsnails are linked to the show_item.php script which will display further details.
	 * Usage: 7
	 *
	 * @param	array		$row is the database row from the table, $table.
	 * @param	string		Table name for $row (present in TCA)
	 * @param	string		$field is pointing to the field with the list of image files
	 * @param	string		Back path prefix for image tag src="" field
	 * @param	string		Optional: $thumbScript os by default 'thumbs.php' if you don't set it otherwise
	 * @param	string		Optional: $uploaddir is the directory relative to PATH_site where the image files from the $field value is found (Is by default set to the entry in $TCA for that field! so you don't have to!)
	 * @param	boolean		If set, uploaddir is NOT prepended with "../"
	 * @param	string		Optional: $tparams is additional attributes for the image tags
	 * @param	integer		Optional: $size is [w]x[h] of the thumbnail. 56 is default.
	 * @return	string		Thumbnail image tag.
	 */
	public static function thumbCode($row, $table, $field, $backPath, $thumbScript = '', $uploaddir = NULL, $abs = 0, $tparams = '', $size = '') {
		global $TCA;
			// Load table.
		t3lib_div::loadTCA($table);

			// Find uploaddir automatically
		$uploaddir = (is_null($uploaddir)) ? $TCA[$table]['columns'][$field]['config']['uploadfolder'] : $uploaddir;
		$uploaddir = preg_replace('#/$#','', $uploaddir);

			// Set thumbs-script:
		if (!$GLOBALS['TYPO3_CONF_VARS']['GFX']['thumbnails']) {
			$thumbScript = 'gfx/notfound_thumb.gif';
		} elseif(!$thumbScript) {
			$thumbScript = 'thumbs.php';
		}
			// Check and parse the size parameter
		$sizeParts = array();
		if ($size = trim($size)) {
			$sizeParts = explode('x', $size.'x'.$size);
			if(!intval($sizeParts[0])) $size = '';
		}

			// Traverse files:
		$thumbs = explode(',', $row[$field]);
		$thumbData = '';
		while(list(,$theFile) = each($thumbs)) {
			if (trim($theFile)) {
				$fI = t3lib_div::split_fileref($theFile);
				$ext = $fI['fileext'];
						// New 190201 start
				$max = 0;
				if (t3lib_div::inList('gif,jpg,png', $ext)) {
					$imgInfo = @getimagesize(PATH_site.$uploaddir.'/'.$theFile);
					if (is_array($imgInfo))	{$max = max($imgInfo[0], $imgInfo[1]);}
				}
					// use the original image if it's size fits to the thumbnail size
				if ($max && $max<=(count($sizeParts)&&max($sizeParts)?max($sizeParts):56)) {
					$theFile = $url = ($abs?'':'../').($uploaddir?$uploaddir.'/':'').trim($theFile);
					$onClick = 'top.launchView(\''.$theFile.'\',\'\',\''.$backPath.'\');return false;';
					$thumbData.= '<a href="#" onclick="'.htmlspecialchars($onClick).'"><img src="'.$backPath.$url.'" '.$imgInfo[3].' hspace="2" border="0" title="'.trim($url).'"'.$tparams.' alt="" /></a> ';
						// New 190201 stop
				} elseif ($ext=='ttf' || t3lib_div::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $ext)) {
					$theFile_abs = PATH_site.($uploaddir?$uploaddir.'/':'').trim($theFile);
					$theFile = ($abs?'':'../').($uploaddir?$uploaddir.'/':'').trim($theFile);

					$check = basename($theFile_abs).':'.filemtime($theFile_abs).':'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
					$params = '&file='.rawurlencode($theFile);
					$params.= $size?'&size='.$size:'';
					$params.= '&md5sum='.t3lib_div::shortMD5($check);

					$url = $thumbScript.'?&dummy='.$GLOBALS['EXEC_TIME'].$params;
					$onClick = 'top.launchView(\''.$theFile.'\',\'\',\''.$backPath.'\');return false;';
					$thumbData.= '<a href="#" onclick="'.htmlspecialchars($onClick).'"><img src="'.htmlspecialchars($backPath.$url).'" hspace="2" border="0" title="'.trim($theFile).'"'.$tparams.' alt="" /></a> ';
				} else {
					$icon = t3lib_BEfunc::getFileIcon($ext);
					$url = 'gfx/fileicons/'.$icon;
					$thumbData.= '<img src="'.$backPath.$url.'" hspace="2" border="0" title="'.trim($theFile).'"'.$tparams.' alt="" /> ';
				}
			}
		}
		return $thumbData;
	}

	/**
	 * Returns single image tag to thumbnail using a thumbnail script (like thumbs.php)
	 * Usage: 3
	 *
	 * @param	string		$thumbScript must point to "thumbs.php" relative to the script position
	 * @param	string		$theFile must be the proper reference to the file thumbs.php should show
	 * @param	string		$tparams are additional attributes for the image tag
	 * @param	integer		$size is the size of the thumbnail send along to "thumbs.php"
	 * @return	string		Image tag
	 */
	public static function getThumbNail($thumbScript, $theFile, $tparams = '', $size = '') {
		$check = basename($theFile).':'.filemtime($theFile).':'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
		$params = '&file='.rawurlencode($theFile);
		$params.= trim($size)?'&size='.trim($size):'';
		$params.= '&md5sum='.t3lib_div::shortMD5($check);

		$url = $thumbScript.'?&dummy='.$GLOBALS['EXEC_TIME'].$params;
		$th = '<img src="'.htmlspecialchars($url).'" title="'.trim(basename($theFile)).'"'.($tparams?" ".$tparams:"").' alt="" />';
		return $th;
	}

	/**
	 * Returns title-attribute information for a page-record informing about id, alias, doktype, hidden, starttime, endtime, fe_group etc.
	 * Usage: 8
	 *
	 * @param	array		Input must be a page row ($row) with the proper fields set (be sure - send the full range of fields for the table)
	 * @param	string		$perms_clause is used to get the record path of the shortcut page, if any (and doktype==4)
	 * @param	boolean		If $includeAttrib is set, then the 'title=""' attribute is wrapped about the return value, which is in any case htmlspecialchar()'ed already
	 * @return	string
	 */
	public static function titleAttribForPages($row, $perms_clause = '', $includeAttrib = 1) {
		global $TCA, $LANG;
		$parts = array();
		$parts[] = 'id='.$row['uid'];
		if ($row['alias'])	$parts[] = $LANG->sL($TCA['pages']['columns']['alias']['label']).' '.$row['alias'];
		if ($row['pid']<0)	$parts[] = 'v#1.'.$row['t3ver_id'];

		switch($row['t3ver_state']) {
			case 1:
				$parts[] = 'PLH WSID#'.$row['t3ver_wsid'];
			break;
			case 2:
				$parts[] = 'Deleted element!';
			break;
			case 3:
				$parts[] = 'NEW LOCATION (PLH) WSID#'.$row['t3ver_wsid'];
			break;
			case 4:
				$parts[] = 'OLD LOCATION (PNT) WSID#'.$row['t3ver_wsid'];
			break;
			case -1:
				$parts[] = 'New element!';
			break;
		}

		if ($row['doktype']=='3') {
			$parts[] = $LANG->sL($TCA['pages']['columns']['url']['label']).' '.$row['url'];
		} elseif ($row['doktype']=='4') {
			if ($perms_clause) {
				$label = t3lib_BEfunc::getRecordPath(intval($row['shortcut']), $perms_clause, 20);
			} else {
				$lRec = t3lib_BEfunc::getRecordWSOL('pages', intval($row['shortcut']), 'title');
				$label = $lRec['title'];
			}
			if ($row['shortcut_mode']>0) {
				$label.=', '.$LANG->sL($TCA['pages']['columns']['shortcut_mode']['label']).' '.
							$LANG->sL(t3lib_BEfunc::getLabelFromItemlist('pages', 'shortcut_mode', $row['shortcut_mode']));
			}
			$parts[] = $LANG->sL($TCA['pages']['columns']['shortcut']['label']).' '.$label;
		} elseif ($row['doktype']=='7') {
			if ($perms_clause) {
				$label = t3lib_BEfunc::getRecordPath(intval($row['mount_pid']), $perms_clause, 20);
			} else {
				$lRec = t3lib_BEfunc::getRecordWSOL('pages', intval($row['mount_pid']), 'title');
				$label = $lRec['title'];
			}
			$parts[] = $LANG->sL($TCA['pages']['columns']['mount_pid']['label']).' '.$label;
			if ($row['mount_pid_ol']) {
				$parts[] = $LANG->sL($TCA['pages']['columns']['mount_pid_ol']['label']);
			}
		}
		if ($row['nav_hide'])	$parts[] = ereg_replace(':$', '', $LANG->sL($TCA['pages']['columns']['nav_hide']['label']));
		if ($row['hidden'])	$parts[] = $LANG->sL('LLL:EXT:lang/locallang_core.php:labels.hidden');
		if ($row['starttime'])	$parts[] = $LANG->sL($TCA['pages']['columns']['starttime']['label']).' '.t3lib_BEfunc::dateTimeAge($row['starttime'], -1, 'date');
		if ($row['endtime'])	$parts[] = $LANG->sL($TCA['pages']['columns']['endtime']['label']).' '.t3lib_BEfunc::dateTimeAge($row['endtime'], -1, 'date');
		if ($row['fe_group']) {
			$fe_groups = array();
			foreach (t3lib_div::intExplode(',', $row['fe_group']) as $fe_group) {
				if ($fe_group<0) {
					$fe_groups[] = $LANG->sL(t3lib_BEfunc::getLabelFromItemlist('pages', 'fe_group', $fe_group));
				} else {
					$lRec = t3lib_BEfunc::getRecordWSOL('fe_groups', $fe_group, 'title');
					$fe_groups[] = $lRec['title'];
				}
			}
			$label = implode(', ', $fe_groups);
			$parts[] = $LANG->sL($TCA['pages']['columns']['fe_group']['label']).' '.$label;
		}
		$out = htmlspecialchars(implode(' - ', $parts));
		return $includeAttrib ? 'title="'.$out.'"' : $out;
	}

	/**
	 * Returns title-attribute information for ANY record (from a table defined in TCA of course)
	 * The included information depends on features of the table, but if hidden, starttime, endtime and fe_group fields are configured for, information about the record status in regard to these features are is included.
	 * "pages" table can be used as well and will return the result of ->titleAttribForPages() for that page.
	 * Usage: 10
	 *
	 * @param	array		Table row; $row is a row from the table, $table
	 * @param	string		Table name
	 * @return	string
	 */
	public static function getRecordIconAltText($row, $table = 'pages') {
		if ($table=='pages') {
			$out = t3lib_BEfunc::titleAttribForPages($row, '', 0);
		} else {
			$ctrl = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns'];

			$out = 'id='.$row['uid'];	// Uid is added
			if ($table=='pages' && $row['alias']) {
				$out.=' / '.$row['alias'];
			}
			if ($GLOBALS['TCA'][$table]['ctrl']['versioningWS'] && $row['pid']<0) {
				$out.=' - v#1.'.$row['t3ver_id'];
			}
			if ($GLOBALS['TCA'][$table]['ctrl']['versioningWS']) {
				switch($row['t3ver_state']) {
					case 1:
						$out.= ' - PLH WSID#'.$row['t3ver_wsid'];
					break;
					case 2:
						$out.= ' - Deleted element!';
					break;
					case 3:
						$out.= ' - NEW LOCATION (PLH) WSID#'.$row['t3ver_wsid'];
					break;
					case 4:
						$out.= ' - OLD LOCATION (PNT)  WSID#'.$row['t3ver_wsid'];
					break;
					case -1:
						$out.= ' - New element!';
					break;
				}
			}

			if ($ctrl['disabled'])	{		// Hidden ...
				$out.=($row[$ctrl['disabled']]?' - '.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.hidden'):'');
			}
			if ($ctrl['starttime']) {
				if ($row[$ctrl['starttime']] > $GLOBALS['EXEC_TIME']) {
					$out.=' - '.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.starttime').':'.t3lib_BEfunc::date($row[$ctrl['starttime']]).' ('.t3lib_BEfunc::daysUntil($row[$ctrl['starttime']]).' '.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.days').')';
				}
			}
			if ($row[$ctrl['endtime']]) {
				$out.=' - '.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.endtime').': '.t3lib_BEfunc::date($row[$ctrl['endtime']]).' ('.t3lib_BEfunc::daysUntil($row[$ctrl['endtime']]).' '.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.days').')';
			}
		}
		return htmlspecialchars($out);
	}

	/**
	 * Returns the label of the first found entry in an "items" array from $TCA (tablename = $table/fieldname = $col) where the value is $key
	 * Usage: 9
	 *
	 * @param	string		Table name, present in $TCA
	 * @param	string		Field name, present in $TCA
	 * @param	string		items-array value to match
	 * @return	string		Label for item entry
	 */
	public static function getLabelFromItemlist($table, $col, $key) {
		global $TCA;
			// Load full TCA for $table
		t3lib_div::loadTCA($table);

			// Check, if there is an "items" array:
		if (is_array($TCA[$table]) && is_array($TCA[$table]['columns'][$col]) && is_array($TCA[$table]['columns'][$col]['config']['items'])) {
				// Traverse the items-array...
			reset($TCA[$table]['columns'][$col]['config']['items']);
			while(list($k, $v) = each($TCA[$table]['columns'][$col]['config']['items'])) {
					// ... and return the first found label where the value was equal to $key
				if (!strcmp($v[1], $key))	return $v[0];
			}
		}
	}

	/**
	 * Returns the label-value for fieldname $col in table, $table
	 * If $printAllWrap is set (to a "wrap") then it's wrapped around the $col value IF THE COLUMN $col DID NOT EXIST in TCA!, eg. $printAllWrap = '<b>|</b>' and the fieldname was 'not_found_field' then the return value would be '<b>not_found_field</b>'
	 * Usage: 17
	 *
	 * @param	string		Table name, present in $TCA
	 * @param	string		Field name
	 * @param	string		Wrap value - set function description
	 * @return	string
	 */
	public static function getItemLabel($table, $col, $printAllWrap = '') {
		global $TCA;
			// Load full TCA for $table
		t3lib_div::loadTCA($table);
			// Check if column exists
		if (is_array($TCA[$table]) && is_array($TCA[$table]['columns'][$col])) {
				// Re
			return $TCA[$table]['columns'][$col]['label'];
		}
		if ($printAllWrap) {
			$parts = explode('|', $printAllWrap);
			return $parts[0].$col.$parts[1];
		}
	}

	/**
	 * Returns the "title"-value in record, $row, from table, $table
	 * The field(s) from which the value is taken is determined by the "ctrl"-entries 'label', 'label_alt' and 'label_alt_force'
	 * Usage: 26
	 *
	 * @param	string		Table name, present in TCA
	 * @param	array		Row from table
	 * @param	boolean		If set, result is prepared for output: The output is cropped to a limited lenght (depending on BE_USER->uc['titleLen']) and if no value is found for the title, '<em>[No title]</em>' is returned (localized). Further, the output is htmlspecialchars()'ed
	 * @param	boolean		If set, the function always returns an output. If no value is found for the title, '[No title]' is returned (localized).
	 * @return	string
	 */
	public static function getRecordTitle($table, $row, $prep = FALSE, $forceResult = TRUE) {
		global $TCA;
		if (is_array($TCA[$table]))	{

				// If configured, call userFunc
			if ($TCA[$table]['ctrl']['label_userFunc']) {
				$params['table'] = $table;
				$params['row'] = $row;
				$params['title'] = '';

				t3lib_div::callUserFunction($TCA[$table]['ctrl']['label_userFunc'], $params, $this);
				$t = $params['title'];
			} else {

					// No userFunc: Build label
				$t = t3lib_BEfunc::getProcessedValue($table, $TCA[$table]['ctrl']['label'], $row[$TCA[$table]['ctrl']['label']], 0, 0, false, $row['uid'], $forceResult);
				if ($TCA[$table]['ctrl']['label_alt'] && ($TCA[$table]['ctrl']['label_alt_force'] || !strcmp($t, ''))) {
					$altFields = t3lib_div::trimExplode(',', $TCA[$table]['ctrl']['label_alt'], 1);
					$tA = array();
					if (!empty($t))	$tA[] = $t;
					foreach ($altFields as $fN) {
						$t = trim(strip_tags($row[$fN]));
						if (strcmp($t, '')) {
							$t = t3lib_BEfunc::getProcessedValue($table, $fN, $t, 0, 0, false, $row['uid']);
							if (!$TCA[$table]['ctrl']['label_alt_force']) {
								break;
							}
							$tA[] = $t;
						}
					}
					if ($TCA[$table]['ctrl']['label_alt_force']) {
						$t = implode(', ', $tA);
					}
				}
			}

				// If the current result is empty, set it to '[No title]' (localized) and prepare for output if requested
			if ($prep || $forceResult) {
				if ($prep) {
					$t = t3lib_BEfunc::getRecordTitlePrep($t);
				}
				if (!strcmp(trim($t), '')) {
					$t = t3lib_BEfunc::getNoRecordTitle($prep);
				}
			}

			return $t;
		}
	}

	/**
	 * Crops a title string to a limited lenght and if it really was cropped, wrap it in a <span title="...">|</span>,
	 * which offers a tooltip with the original title when moving mouse over it.
	 *
	 * @param	string		$title: The title string to be cropped
	 * @param	integer		$titleLength: Crop title after this length - if not set, BE_USER->uc['titleLen'] is used
	 * @return	string		The processed title string, wrapped in <span title="...">|</span> if cropped
	 */
	public static function getRecordTitlePrep($title, $titleLength = 0) {
			// If $titleLength is not a valid positive integer, use BE_USER->uc['titleLen']:
		if (!$titleLength || !t3lib_div::testInt($titleLength) || $titleLength < 0) {
			$titleLength = $GLOBALS['BE_USER']->uc['titleLen'];
		}
		$titleOrig = htmlspecialchars($title);
		$title = htmlspecialchars(t3lib_div::fixed_lgd_cs($title, $titleLength));
			// If title was cropped, offer a tooltip:
		if ($titleOrig != $title) {
			$title = '<span title="'.$titleOrig.'">'.$title.'</span>';
		}
		return $title;
	}

	/**
	 * Get a localized [No title] string, wrapped in <em>|</em> if $prep is true.
	 *
	 * @param	boolean		$prep: Wrap result in <em>|</em>
	 * @return	string		Localized [No title] string
	 */
	public static function getNoRecordTitle($prep = FALSE) {
		$noTitle = '['.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.no_title', 1).']';
		if ($prep) $noTitle = '<em>'.$noTitle.'</em>';
		return $noTitle;
	}

	/**
	 * Returns a human readable output of a value from a record
	 * For instance a database record relation would be looked up to display the title-value of that record. A checkbox with a "1" value would be "Yes", etc.
	 * $table/$col is tablename and fieldname
	 * REMEMBER to pass the output through htmlspecialchars() if you output it to the browser! (To protect it from XSS attacks and be XHTML compliant)
	 * Usage: 24
	 *
	 * @param	string		Table name, present in TCA
	 * @param	string		Field name, present in TCA
	 * @param	string		$value is the value of that field from a selected record
	 * @param	integer		$fixed_lgd_chars is the max amount of characters the value may occupy
	 * @param	boolean		$defaultPassthrough flag means that values for columns that has no conversion will just be pass through directly (otherwise cropped to 200 chars or returned as "N/A")
	 * @param	boolean		If set, no records will be looked up, UIDs are just shown.
	 * @param	integer		uid of the current record
	 * @param	boolean		If t3lib_BEfunc::getRecordTitle is used to process the value, this parameter is forwarded.
	 * @return	string
	 */
	public static function getProcessedValue($table, $col, $value, $fixed_lgd_chars = 0, $defaultPassthrough = 0, $noRecordLookup = FALSE, $uid = 0, $forceResult = TRUE) {
		global $TCA;
		global $TYPO3_CONF_VARS;
		
		if ($col == 'uid') {
				// no need to load TCA as uid is not in TCA-array
			return $value;
		}
			// Load full TCA for $table
		t3lib_div::loadTCA($table);
			// Check if table and field is configured:
		if (is_array($TCA[$table]) && is_array($TCA[$table]['columns'][$col])) {
				// Depending on the fields configuration, make a meaningful output value.
			$theColConf = $TCA[$table]['columns'][$col]['config'];

				/*****************
				 *HOOK: pre-processing the human readable output from a record
				 ****************/
			if (is_array ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['preProcessValue'])) {
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['preProcessValue'] as $_funcRef) {
					t3lib_div::callUserFunction($_funcRef,$theColConf,$this);
				}
			}

			$l = '';
			switch((string)$theColConf['type']) {
				case 'radio':
					$l = t3lib_BEfunc::getLabelFromItemlist($table,$col,$value);
					$l = $GLOBALS['LANG']->sL($l);
				break;
				case 'select':
					if ($theColConf['MM']) {
						// Display the title of MM related records in lists
						if ($noRecordLookup) {
							$MMfield = $theColConf['foreign_table'].'.uid';
						} else	{
							$MMfields = array($theColConf['foreign_table'].'.'.$TCA[$theColConf['foreign_table']]['ctrl']['label']);
							foreach (t3lib_div::trimExplode(',', $TCA[$theColConf['foreign_table']]['ctrl']['label_alt'], 1) as $f)	{
								$MMfields[] = $theColConf['foreign_table'].'.'.$f;
							}
							$MMfield = join(',',$MMfields);
						}

						$dbGroup = t3lib_div::makeInstance('t3lib_loadDBGroup');
						$dbGroup->start($value, $theColConf['foreign_table'], $theColConf['MM'], $uid, $table, $theColConf);
						$selectUids = $dbGroup->tableArray[$theColConf['foreign_table']];

						if (is_array($selectUids) && count($selectUids)>0) {
							$MMres = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'uid, '.$MMfield,
								$theColConf['foreign_table'],
								'uid IN ('.implode(',', $selectUids).')'
							);
							while($MMrow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($MMres)) {
								$mmlA[] = ($noRecordLookup?$MMrow['uid']:t3lib_BEfunc::getRecordTitle($theColConf['foreign_table'], $MMrow, FALSE, $forceResult));
							}
							$GLOBALS['TYPO3_DB']->sql_free_result($MMres);

							if (is_array($mmlA)) {
								$l = implode('; ', $mmlA);
							} else {
								$l = '';
							}
						} else {
							$l = 'N/A';
						}
					} else {
						$l = t3lib_BEfunc::getLabelFromItemlist($table, $col, $value);
						$l = $GLOBALS['LANG']->sL($l);
						if ($theColConf['foreign_table'] && !$l && $TCA[$theColConf['foreign_table']]) {
							if ($noRecordLookup) {
								$l = $value;
							} else {
								$rParts = t3lib_div::trimExplode(',', $value, 1);
								reset($rParts);
								$lA = array();
								while(list(,$rVal) = each($rParts)) {
									$rVal = intval($rVal);
									if ($rVal>0) {
										$r = t3lib_BEfunc::getRecordWSOL($theColConf['foreign_table'], $rVal);
									} else {
										$r = t3lib_BEfunc::getRecordWSOL($theColConf['neg_foreign_table'], -$rVal);
									}
									if (is_array($r)) {
										$lA[] = $GLOBALS['LANG']->sL($rVal>0?$theColConf['foreign_table_prefix']:$theColConf['neg_foreign_table_prefix']).t3lib_BEfunc::getRecordTitle($rVal>0?$theColConf['foreign_table']:$theColConf['neg_foreign_table'], $r, FALSE, $forceResult);
									} else {
										$lA[] = $rVal?'['.$rVal.'!]':'';
									}
								}
								$l = implode(', ', $lA);
							}
						}
					}
				break;
				case 'group':
					$l = implode(', ', t3lib_div::trimExplode(',', $value, 1));
				break;
				case 'check':
					if (!is_array($theColConf['items']) || count($theColConf['items'])==1) {
						$l = $value ? 'Yes' : '';
					} else {
						reset($theColConf['items']);
						$lA = Array();
						while(list($key, $val) = each($theColConf['items'])) {
							if ($value & pow(2, $key))	{$lA[] = $GLOBALS['LANG']->sL($val[0]);}
						}
						$l = implode(', ', $lA);
					}
				break;
				case 'input':
					if ($value) {
						if (t3lib_div::inList($theColConf['eval'], 'date')) {
							$l = t3lib_BEfunc::date($value).' ('.(time()-$value>0?'-':'').t3lib_BEfunc::calcAge(abs(time()-$value), $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')).')';
						} elseif (t3lib_div::inList($theColConf['eval'], 'time')) {
							$l = t3lib_BEfunc::time($value, FALSE);
						} elseif (t3lib_div::inList($theColConf['eval'], 'timesec')) {
							$l = t3lib_BEfunc::time($value);
						} elseif (t3lib_div::inList($theColConf['eval'], 'datetime')) {
							$l = t3lib_BEfunc::datetime($value);
						} else {
							$l = $value;
						}
					}
				break;
				case 'flex':
					$l = strip_tags($value);
				break;
				default:
					if ($defaultPassthrough) {
						$l = $value;
					} elseif ($theColConf['MM']) {
						$l = 'N/A';
					} elseif ($value) {
						$l = t3lib_div::fixed_lgd_cs(strip_tags($value), 200);
					}
				break;
			}

				/*****************
				 *HOOK: post-processing the human readable output from a record
				 ****************/
			if (is_array ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'])) {
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['postProcessValue'] as $_funcRef) {
					$params = array(
						'value' => $l,
						'colConf' => $theColConf
					);
					$l = t3lib_div::callUserFunction($_funcRef, $params, $this);
				}
			}

			if ($fixed_lgd_chars) {
				return t3lib_div::fixed_lgd_cs($l, $fixed_lgd_chars);
			} else {
				return $l;
			}
		}
	}

	/**
	 * Same as ->getProcessedValue() but will go easy on fields like "tstamp" and "pid" which are not configured in TCA - they will be formatted by this function instead.
	 * Usage: 2
	 *
	 * @param	string		Table name, present in TCA
	 * @param	string		Field name
	 * @param	string		Field value
	 * @param	integer		$fixed_lgd_chars is the max amount of characters the value may occupy
	 * @param	integer		uid of the current record
	 * @param	boolean		If t3lib_BEfunc::getRecordTitle is used to process the value, this parameter is forwarded.
	 * @return	string
	 * @see getProcessedValue()
	 */
	public static function getProcessedValueExtra($table, $fN, $fV, $fixed_lgd_chars = 0, $uid = 0, $forceResult = TRUE) {
		global $TCA;
		$fVnew = t3lib_BEfunc::getProcessedValue($table, $fN, $fV, $fixed_lgd_chars, 0, 0, $uid, $forceResult);
		if (!isset($fVnew)) {
			if (is_array($TCA[$table])) {
				if ($fN==$TCA[$table]['ctrl']['tstamp'] || $fN==$TCA[$table]['ctrl']['crdate']) {
					$fVnew = t3lib_BEfunc::datetime($fV);
				} elseif ($fN=='pid'){
					$fVnew = t3lib_BEfunc::getRecordPath($fV, '1=1', 20);	// Fetches the path with no regard to the users permissions to select pages.
				} else {
					$fVnew = $fV;
				}
			}
		}
		return $fVnew;
	}

	/**
	 * Returns file icon name (from $FILEICONS) for the fileextension $ext
	 * Usage: 10
	 *
	 * @param	string		File extension, lowercase
	 * @return	string		File icon filename
	 */
	public static function getFileIcon($ext) {
		return $GLOBALS['FILEICONS'][$ext] ? $GLOBALS['FILEICONS'][$ext] : $GLOBALS['FILEICONS']['default'];
	}

	/**
	 * Returns fields for a table, $table, which would typically be interesting to select
	 * This includes uid, the fields defined for title, icon-field.
	 * Returned as a list ready for query ($prefix can be set to eg. "pages." if you are selecting from the pages table and want the table name prefixed)
	 * Usage: 3
	 *
	 * @param	string		Table name, present in TCA
	 * @param	string		Table prefix
	 * @param	array		Preset fields (must include prefix if that is used)
	 * @return	string		List of fields.
	 */
	public static function getCommonSelectFields($table, $prefix = '', $fields = array()) {
		global $TCA;
		$fields[] = $prefix.'uid';
		$fields[] = $prefix.$TCA[$table]['ctrl']['label'];

		if ($TCA[$table]['ctrl']['label_alt']) {
			$secondFields = t3lib_div::trimExplode(',', $TCA[$table]['ctrl']['label_alt'], 1);
			foreach($secondFields as $fieldN) {
				$fields[] = $prefix.$fieldN;
			}
		}
		if ($TCA[$table]['ctrl']['versioningWS']) {
			$fields[] = $prefix.'t3ver_id';
			$fields[] = $prefix.'t3ver_state';
			$fields[] = $prefix.'t3ver_wsid';
			$fields[] = $prefix.'t3ver_count';
		}

		if ($TCA[$table]['ctrl']['selicon_field'])	$fields[] = $prefix.$TCA[$table]['ctrl']['selicon_field'];
		if ($TCA[$table]['ctrl']['typeicon_column'])	$fields[] = $prefix.$TCA[$table]['ctrl']['typeicon_column'];

		if (is_array($TCA[$table]['ctrl']['enablecolumns']))		{
			if ($TCA[$table]['ctrl']['enablecolumns']['disabled'])	$fields[] = $prefix.$TCA[$table]['ctrl']['enablecolumns']['disabled'];
			if ($TCA[$table]['ctrl']['enablecolumns']['starttime'])	$fields[] = $prefix.$TCA[$table]['ctrl']['enablecolumns']['starttime'];
			if ($TCA[$table]['ctrl']['enablecolumns']['endtime'])	$fields[] = $prefix.$TCA[$table]['ctrl']['enablecolumns']['endtime'];
			if ($TCA[$table]['ctrl']['enablecolumns']['fe_group'])	$fields[] = $prefix.$TCA[$table]['ctrl']['enablecolumns']['fe_group'];
		}

		return implode(',', array_unique($fields));
	}

	/**
	 * Makes a form for configuration of some values based on configuration found in the array $configArray, with default values from $defaults and a data-prefix $dataPrefix
	 * <form>-tags must be supplied separately
	 * Needs more documentation and examples, in particular syntax for configuration array. See Inside TYPO3. That's were you can expect to find example, if anywhere.
	 * Usage: 1 (ext. direct_mail)
	 *
	 * @param	array		Field configuration code.
	 * @param	array		Defaults
	 * @param	string		Prefix for formfields
	 * @return	string		HTML for a form.
	 */
	public static function makeConfigForm($configArray, $defaults, $dataPrefix) {
		$params = $defaults;
		if (is_array($configArray)) {
			reset($configArray);
			$lines = array();
			while(list($fname, $config) = each($configArray)) {
				if (is_array($config)) {
					$lines[$fname] = '<strong>'.htmlspecialchars($config[1]).'</strong><br />';
					$lines[$fname].=$config[2].'<br />';
					switch($config[0]) {
						case 'string':
						case 'short':
							$formEl = '<input type="text" name="'.$dataPrefix.'['.$fname.']" value="'.$params[$fname].'"'.$GLOBALS['TBE_TEMPLATE']->formWidth($config[0]=='short'?24:48).' />';
						break;
						case 'check':
							$formEl = '<input type="hidden" name="'.$dataPrefix.'['.$fname.']" value="0" /><input type="checkbox" name="'.$dataPrefix.'['.$fname.']" value="1"'.($params[$fname]?' checked="checked"':'').' />';
						break;
						case 'comment':
							$formEl = '';
						break;
						case 'select':
							reset($config[3]);
							$opt = array();
							while(list($k, $v) = each($config[3])) {
								$opt[] = '<option value="'.htmlspecialchars($k).'"'.($params[$fname]==$k?' selected="selected"':'').'>'.htmlspecialchars($v).'</option>';
							}
							$formEl = '<select name="'.$dataPrefix.'['.$fname.']">'.implode('', $opt).'</select>';
						break;
						default:
							debug($config);
						break;
					}
					$lines[$fname].=$formEl;
					$lines[$fname].='<br /><br />';
				} else {
					$lines[$fname] = '<hr />';
					if ($config)	$lines[$fname].='<strong>'.strtoupper(htmlspecialchars($config)).'</strong><br />';
					if ($config)	$lines[$fname].='<br />';
				}
			}
		}
		$out = implode('', $lines);
		$out.='<input type="submit" name="submit" value="Update configuration" />';
		return $out;
	}













	/*******************************************
	 *
	 * Backend Modules API functions
	 *
	 *******************************************/

	/**
	 * Returns help-text icon if configured for.
	 * TCA_DESCR must be loaded prior to this function and $BE_USER must have 'edit_showFieldHelp' set to 'icon', otherwise nothing is returned
	 * Usage: 6
	 *
	 * @param	string		Table name
	 * @param	string		Field name
	 * @param	string		Back path
	 * @param	boolean		Force display of icon nomatter BE_USER setting for help
	 * @return	string		HTML content for a help icon/text
	 */
	public static function helpTextIcon($table, $field, $BACK_PATH, $force = 0) {
		global $TCA_DESCR, $BE_USER;

		$onClick = 'vHWin=window.open(\''.$BACK_PATH.'view_help.php?tfID='.($table.'.'.$field).'\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;';
		if (is_array($TCA_DESCR[$table]) && is_array($TCA_DESCR[$table]['columns'][$field]) && (isset($BE_USER->uc['edit_showFieldHelp']) || $force)) {
			if ($BE_USER->uc['edit_showFieldHelp'] == 'icon') {
				$text = t3lib_BEfunc::helpText($table, $field, $BACK_PATH, '');
				$text = '<span class="typo3-csh-inline">'.$GLOBALS['LANG']->hscAndCharConv($text, false).'</span>';
			}
			return '<a class="typo3-csh-link" href="#" onclick="'.htmlspecialchars($onClick).'"><img'.t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/helpbubble.gif', 'width="14" height="14"').' hspace="2" border="0" class="typo3-csh-icon" alt="" />'.$text.'</a>';
		}
	}


	/**
	 * Returns CSH help text (description), if configured for.
	 * TCA_DESCR must be loaded prior to this function and $BE_USER must have "edit_showFieldHelp" set to "text",
	 * otherwise nothing is returned
	 * Will automatically call t3lib_BEfunc::helpTextIcon() to get the icon for the text.
	 * Usage: 4
	 *
	 * @param	string		Table name
	 * @param	string		Field name
	 * @param	string		Back path
	 * @param	string		DEPRECATED: Additional style-attribute content for wrapping table (now: only in function cshItem needed)
	 * @return	string		HTML content for help text
	 */
	public static function helpText($table, $field, $BACK_PATH, $styleAttrib = '') {
		global $TCA_DESCR, $BE_USER;
		$output = '';

		if (is_array($TCA_DESCR[$table]) && is_array($TCA_DESCR[$table]['columns'][$field])) {
			$data = $TCA_DESCR[$table]['columns'][$field];
				// add see also arrow
			if ($data['image_descr'] || $data['seeAlso'] || $data['details'] || $data['syntax']) {
				$arrow = '<img'.t3lib_iconWorks::skinImg($BACK_PATH, 'gfx/rel_db.gif', 'width="13" height="12"').' class="absmiddle" alt="" />';
			}
				// add description text
			if ($data['description'] || $arrow) {
				$output = '<span class="paragraph">'.nl2br(htmlspecialchars($data['description'])).$arrow.'</span>';
			}

				// put header before the rest of the text
			if ($data['alttitle']) {
				$output = '<span class="header">'.$data['alttitle'].'</span><br />'.$output;
			}
		}
		return $output;
	}


	/**
	 * API for getting CSH icons/text for use in backend modules.
	 * TCA_DESCR will be loaded if it isn't already
	 * Usage: ?
	 *
	 * @param	string		Table name ('_MOD_'+module name)
	 * @param	string		Field name (CSH locallang main key)
	 * @param	string		Back path
	 * @param	string		Wrap code for icon-mode, splitted by "|". Not used for full-text mode.
	 * @param	boolean		If set, the full text will never be shown (only icon).
	 * 				Useful for places where it will break the page if the table with full text is shown.
	 * @param	string		Additional style-attribute content for wrapping table (full text mode only)
	 * @return	string		HTML content for help text
	 * @see helpText(), helpTextIcon()
	 */
	public static function cshItem($table, $field, $BACK_PATH, $wrap = '', $onlyIconMode = FALSE, $styleAttrib = '') {
		global $TCA_DESCR, $LANG, $BE_USER;

		if ($BE_USER->uc['edit_showFieldHelp']) {
			$LANG->loadSingleTableDescription($table);

			if (is_array($TCA_DESCR[$table])) {
				// Creating CSH icon and short description:
				$fullText = t3lib_BEfunc::helpText($table, $field, $BACK_PATH, '');
				$icon = t3lib_BEfunc::helpTextIcon($table, $field, $BACK_PATH);

				if ($fullText && !$onlyIconMode && $BE_USER->uc['edit_showFieldHelp'] == 'text')	{

					// Additional styles?
					$params = $styleAttrib ? ' style="'.$styleAttrib.'"' : '';

					// Compile table with CSH information:
					$fullText = '<table border="0" cellpadding="0" cellspacing="0" class="typo3-csh-inline"'.$params.'>
					<tr>
					<td valign="top" width="14">'.$icon.'</td>
					<td valign="top">'.$fullText.'</td>
					</tr>
					</table>';

					$output = $LANG->hscAndCharConv($fullText, false);
				} else {
					$output = $icon;

					if ($output && $wrap) {
						$wrParts = explode('|', $wrap);
						$output = $wrParts[0].$output.$wrParts[1];
					}
				}

				return $output;
			}
		}
	}

	/**
	 * Returns a JavaScript string (for an onClick handler) which will load the alt_doc.php script that shows the form for editing of the record(s) you have send as params.
	 * REMEMBER to always htmlspecialchar() content in href-properties to ampersands get converted to entities (XHTML requirement and XSS precaution)
	 * Usage: 35
	 *
	 * @param	string		$params is parameters sent along to alt_doc.php. This requires a much more details description which you must seek in Inside TYPO3s documentation of the alt_doc.php API. And example could be '&edit[pages][123] = edit' which will show edit form for page record 123.
	 * @param	string		$backPath must point back to the TYPO3_mainDir directory (where alt_doc.php is)
	 * @param	string		$requestUri is an optional returnUrl you can set - automatically set to REQUEST_URI.
	 * @return	string
	 * @see template::issueCommand()
	 */
	public static function editOnClick($params, $backPath = '', $requestUri = '') {
		$retUrl = 'returnUrl='.($requestUri==-1?"'+T3_THIS_LOCATION+'":rawurlencode($requestUri?$requestUri:t3lib_div::getIndpEnv('REQUEST_URI')));
		return "window.location.href='".$backPath."alt_doc.php?".$retUrl.$params."'; return false;";
	}

	/**
	 * Returns a JavaScript string for viewing the page id, $id
	 * It will detect the correct domain name if needed and provide the link with the right back path. Also it will re-use any window already open.
	 * Usage: 8
	 *
	 * @param	integer		$id is page id
	 * @param	string		$backpath must point back to TYPO3_mainDir (where the site is assumed to be one level above)
	 * @param	array		If root line is supplied the function will look for the first found domain record and use that URL instead (if found)
	 * @param	string		$anchor is optional anchor to the URL
	 * @param	string		$altUrl is an alternative URL which - if set - will make all other parameters ignored: The function will just return the window.open command wrapped around this URL!
	 * @param	string		Additional GET variables.
	 * @param	boolean		If true, then the preview window will gain the focus.
	 * @return	string
	 */
	public static function viewOnClick($id, $backPath = '', $rootLine = '', $anchor = '', $altUrl = '', $addGetVars = '', $switchFocus = TRUE) {
		if ($altUrl) {
			$url = $altUrl;
		} else {
				// check where to render the page
			$viewScript = ($GLOBALS['BE_USER']->workspace!=0 && !$GLOBALS['BE_USER']->user['workspace_preview']) ? '/'.TYPO3_mainDir.'mod/user/ws/wsol_preview.php?id=' : '/index.php?id=';
				// check alternate Domains
			if ($rootLine)  {
				$parts = parse_url(t3lib_div::getIndpEnv('TYPO3_SITE_URL'));
				if (t3lib_BEfunc::getDomainStartPage($parts['host'],$parts['path']))    {
					$preUrl_temp = t3lib_BEfunc::firstDomainRecord($rootLine);
				}
			}
			$preUrl = $preUrl_temp ? (t3lib_div::getIndpEnv('TYPO3_SSL') ? 'https://' : 'http://').$preUrl_temp : $backPath.'..';
			$url = $preUrl.$viewScript.$id.$addGetVars.$anchor;
		}

		return "previewWin=window.open('".$url."','newTYPO3frontendWindow');".
				($switchFocus ? 'previewWin.focus();' : '');
	}

	/**
	 * Returns the merged User/Page TSconfig for page id, $id.
	 * Please read details about module programming elsewhere!
	 * Usage: 15
	 *
	 * @param	integer		Page uid
	 * @param	string		$TSref is an object string which determines the path of the TSconfig to return.
	 * @return	array
	 */
	public static function getModTSconfig($id, $TSref) {
		$pageTS_modOptions = $GLOBALS['BE_USER']->getTSConfig($TSref, t3lib_BEfunc::getPagesTSconfig($id));
		$BE_USER_modOptions = $GLOBALS['BE_USER']->getTSConfig($TSref);
		$modTSconfig = t3lib_div::array_merge_recursive_overrule($pageTS_modOptions, $BE_USER_modOptions);
		return $modTSconfig;
	}

	/**
	 * Returns a selector box "function menu" for a module
	 * Requires the JS function jumpToUrl() to be available
	 * See Inside TYPO3 for details about how to use / make Function menus
	 * Usage: 50
	 *
	 * @param	mixed		$id is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
	 * @param	string		$elementName it the form elements name, probably something like "SET[...]"
	 * @param	string		$currentValue is the value to be selected currently.
	 * @param	array		$menuItems is an array with the menu items for the selector box
	 * @param	string		$script is the script to send the &id to, if empty it's automatically found
	 * @param	string		$addParams is additional parameters to pass to the script.
	 * @return	string		HTML code for selector box
	 */
	public static function getFuncMenu($mainParams, $elementName, $currentValue, $menuItems, $script = '', $addparams = '') {
		if (is_array($menuItems)) {
			if (!is_array($mainParams)) {
				$mainParams = array('id' => $mainParams);
			}
			$mainParams = t3lib_div::implodeArrayForUrl('', $mainParams);

			if (!$script) {
				$script = basename(PATH_thisScript);
				$mainParams.= (t3lib_div::_GET('M') ? '&M='.rawurlencode(t3lib_div::_GET('M')) : '');
			}

			$options = array();
			foreach($menuItems as $value => $label) {
				$options[] = '<option value="'.htmlspecialchars($value).'"'.(!strcmp($currentValue, $value)?' selected="selected"':'').'>'.
								t3lib_div::deHSCentities(htmlspecialchars($label)).
								'</option>';
			}
			if (count($options)) {
				$onChange = 'jumpToUrl(\''.$script.'?'.$mainParams.$addparams.'&'.$elementName.'=\'+this.options[this.selectedIndex].value,this);';
				return '

					<!-- Function Menu of module -->
					<select name="'.$elementName.'" onchange="'.htmlspecialchars($onChange).'">
						'.implode('
						',$options).'
					</select>
							';
			}
		}
	}

	/**
	 * Checkbox function menu.
	 * Works like ->getFuncMenu() but takes no $menuItem array since this is a simple checkbox.
	 * Usage: 34
	 *
	 * @param	mixed		$mainParams $id is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
	 * @param	string		$elementName it the form elements name, probably something like "SET[...]"
	 * @param	string		$currentValue is the value to be selected currently.
	 * @param	string		$script is the script to send the &id to, if empty it's automatically found
	 * @param	string		$addParams is additional parameters to pass to the script.
	 * @param	string		Additional attributes for the checkbox input tag
	 * @return	string		HTML code for checkbox
	 * @see getFuncMenu()
	 */
	public static function getFuncCheck($mainParams, $elementName, $currentValue, $script = '', $addparams = '', $tagParams = '') {
		if (!is_array($mainParams)) {
			$mainParams = array('id' => $mainParams);
		}
		$mainParams = t3lib_div::implodeArrayForUrl('', $mainParams);

		if (!$script) {basename(PATH_thisScript);}
		$onClick = 'jumpToUrl(\''.$script.'?'.$mainParams.$addparams.'&'.$elementName.'=\'+(this.checked?1:0),this);';
		return '<input type="checkbox" class="checkbox" name="'.$elementName.'"'.($currentValue?' checked="checked"':'').' onclick="'.htmlspecialchars($onClick).'"'.($tagParams?' '.$tagParams:'').' />';
	}

	/**
	 * Input field function menu
	 * Works like ->getFuncMenu() / ->getFuncCheck() but displays a input field instead which updates the script "onchange"
	 * Usage: 1
	 *
	 * @param	mixed		$id is the "&id=" parameter value to be sent to the module, but it can be also a parameter array which will be passed instead of the &id=...
	 * @param	string		$elementName it the form elements name, probably something like "SET[...]"
	 * @param	string		$currentValue is the value to be selected currently.
	 * @param	integer		Relative size of input field, max is 48
	 * @param	string		$script is the script to send the &id to, if empty it's automatically found
	 * @param	string		$addParams is additional parameters to pass to the script.
	 * @return	string		HTML code for input text field.
	 * @see getFuncMenu()
	 */
	public static function getFuncInput($mainParams, $elementName, $currentValue, $size = 10, $script = "", $addparams = "") {
		if (!is_array($mainParams)) {
			$mainParams = array('id' => $mainParams);
		}
		$mainParams = t3lib_div::implodeArrayForUrl('', $mainParams);

		if (!$script) {basename(PATH_thisScript);}
		$onChange = 'jumpToUrl(\''.$script.'?'.$mainParams.$addparams.'&'.$elementName.'=\'+escape(this.value),this);';
		return '<input type="text"'.$GLOBALS['TBE_TEMPLATE']->formWidth($size).' name="'.$elementName.'" value="'.htmlspecialchars($currentValue).'" onchange="'.htmlspecialchars($onChange).'" />';
	}

	/**
	 * Removes menu items from $itemArray if they are configured to be removed by TSconfig for the module ($modTSconfig)
	 * See Inside TYPO3 about how to program modules and use this API.
	 * Usage: 4
	 *
	 * @param	array		Module TS config array
	 * @param	array		Array of items from which to remove items.
	 * @param	string		$TSref points to the "object string" in $modTSconfig
	 * @return	array		The modified $itemArray is returned.
	 */
	public static function unsetMenuItems($modTSconfig, $itemArray, $TSref) {
			// Getting TS-config options for this module for the Backend User:
		$conf = $GLOBALS['BE_USER']->getTSConfig($TSref, $modTSconfig);
		if (is_array($conf['properties'])) {
			reset($conf['properties']);
			while(list($key, $val) = each($conf['properties'])) {
				if (!$val) {
					unset($itemArray[$key]);
				}
			}
		}
		return $itemArray;
	}

	/**
	 * Call to update the page tree frame (or something else..?) after
	 * t3lib_BEfunc::getSetUpdateSignal('updatePageTree') -> will set the page tree to be updated.
	 * t3lib_BEfunc::getSetUpdateSignal() -> will return some JavaScript that does the update (called in the typo3/template.php file, end() function)
	 * please use the setUpdateSignal function instead now, as it allows you to add more parameters
	 * Usage: 11
	 *
	 * @param	string		Whether to set or clear the update signal. When setting, this value contains strings telling WHAT to set. At this point it seems that the value "updatePageTree" is the only one it makes sense to set.
	 * @return	string		HTML code (<script> section)
	 * @see	t3lib_BEfunc::getUpdateSignalCode()
	 * @see	t3lib_BEfunc::setUpdateSignal()
	 * @deprecated	since TYPO3 4.2, please use the setUpdateSignal function instead, as it allows you to add more parameters
	 */
	public static function getSetUpdateSignal($set = '') {
			// kept for backwards compatibility if $set is empty, use "getUpdateSignalCode()" instead
		if ($set) {
			return t3lib_BEfunc::setUpdateSignal($set);
		} else {
			return t3lib_BEfunc::getUpdateSignalCode();
		}
	}


	/**
	 * Call to update the page tree frame (or something else..?) after
	 * use 'updatePageTree' as a first parameter will set the page tree to be updated.
	 * Usage: 10
	 *
	 * @param	string		Key to set the update signal. When setting, this value contains strings telling WHAT to set. At this point it seems that the value "updatePageTree" is the only one it makes sense to set. If empty, all update signals will be removed.
	 * @param	mixed		Additional information for the update signal, used to only refresh a branch of the tree
	 * @return	void
	 * @see	t3lib_BEfunc::getUpdateSignalCode()
	 */
	public function setUpdateSignal($set = '', $params = '') {
		global $BE_USER;
		$modData = $BE_USER->getModuleData('t3lib_BEfunc::getUpdateSignal', 'ses');

		if ($set) {
			$modData[$set] = array(
				'set' => $set,
				'parameter' => $params);
		} else {	// clear the module data
			$modData = array();
		}
		$BE_USER->pushModuleData('t3lib_BEfunc::getUpdateSignal', $modData);
	}


	/**
	 * Call to update the page tree frame (or something else..?) if this is set by the function
	 * setUpdateSignal(). It will return some JavaScript that does the update (called in the typo3/template.php file, end() function)
	 * Usage: 1
	 *
	 * @return	string		HTML javascript code
	 * @see	t3lib_BEfunc::setUpdateSignal()
	 */
	public function getUpdateSignalCode() {
		$signals = array();
		$modData = $GLOBALS['BE_USER']->getModuleData('t3lib_BEfunc::getUpdateSignal', 'ses');
		if (!count($modData)) {
			return '';
		}

			// Hook: Allows to let TYPO3 execute your JS code
		if (isset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['updateSignalHook'])) {
			$updateSignals = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['updateSignalHook'];
		} else {
			$updateSignals = array();
		}

			// loop through all setUpdateSignals and get the JS code
		foreach ($modData as $set => $val) {
			if (isset($updateSignals[$set])) {
				$params = array('set' => $set, 'parameter' => $val['parameter'], 'JScode' => '');
				$ref = NULL;
				t3lib_div::callUserFunction($updateSignals[$set], $params, $ref);
				$signals[] = $params['JScode'];
			} else if ($set == 'updatePageTree' || $set == 'updateFolderTree') {
				$signals[] = '
					if (top && top.content && top.content.nav_frame && top.content.nav_frame.Tree) {
						top.content.nav_frame.Tree.refresh();
					}';
			}
		}

		$content = implode(chr(10), $signals);

		t3lib_BEfunc::setUpdateSignal();	// for backwards compatibility, should be replaced
		return $content;
	}


	/**
	 * Returns an array which is most backend modules becomes MOD_SETTINGS containing values from function menus etc. determining the function of the module.
	 * This is kind of session variable management framework for the backend users.
	 * If a key from MOD_MENU is set in the CHANGED_SETTINGS array (eg. a value is passed to the script from the outside), this value is put into the settings-array
	 * Ultimately, see Inside TYPO3 for how to use this function in relation to your modules.
	 * Usage: 23
	 *
	 * @param	array		MOD_MENU is an array that defines the options in menus.
	 * @param	array		CHANGED_SETTINGS represents the array used when passing values to the script from the menus.
	 * @param	string		modName is the name of this module. Used to get the correct module data.
	 * @param	string		If type is 'ses' then the data is stored as session-lasting data. This means that it'll be wiped out the next time the user logs in.
	 * @param	string		dontValidateList can be used to list variables that should not be checked if their value is found in the MOD_MENU array. Used for dynamically generated menus.
	 * @param	string		List of default values from $MOD_MENU to set in the output array (only if the values from MOD_MENU are not arrays)
	 * @return	array		The array $settings, which holds a key for each MOD_MENU key and the values of each key will be within the range of values for each menuitem
	 */
	public static function getModuleData($MOD_MENU, $CHANGED_SETTINGS, $modName, $type = '', $dontValidateList = '', $setDefaultList = '')	{

		if ($modName && is_string($modName)) {
					// GETTING stored user-data from this module:
			$settings = $GLOBALS['BE_USER']->getModuleData($modName, $type);

			$changed = 0;
			if (!is_array($settings)) {
				$changed = 1;
				$settings = array();
			}
			if (is_array($MOD_MENU)) {
				foreach ($MOD_MENU as $key=>$var) {
						// If a global var is set before entering here. eg if submitted, then it's substituting the current value the array.
					if (is_array($CHANGED_SETTINGS) && isset($CHANGED_SETTINGS[$key]) && strcmp($settings[$key], $CHANGED_SETTINGS[$key])) {
						$settings[$key] = (string)$CHANGED_SETTINGS[$key];
						$changed = 1;
					}
						// If the $var is an array, which denotes the existence of a menu, we check if the value is permitted
					if (is_array($var) && (!$dontValidateList || !t3lib_div::inList($dontValidateList, $key))) {
							// If the setting is an array or not present in the menu-array, MOD_MENU, then the default value is inserted.
						if (is_array($settings[$key]) || !isset($MOD_MENU[$key][$settings[$key]])) {
							$settings[$key] = (string)key($var);
							$changed = 1;
						}
					}
					if ($setDefaultList && !is_array($var))	{	// Sets default values (only strings/checkboxes, not menus)
						if (t3lib_div::inList($setDefaultList, $key) && !isset($settings[$key])) {
							$settings[$key] = (string)$var;
						}
					}
				}
			} else {die ('No menu!');}

			if ($changed) {
				$GLOBALS['BE_USER']->pushModuleData($modName, $settings);
			}

			return  $settings;
		} else {die ('Wrong module name: "'.$modName.'"');}
	}













	/*******************************************
	 *
	 * Core
	 *
	 *******************************************/

	/**
	 * Set preview keyword, eg:
	 * 	$previewUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL').'index.php?ADMCMD_prev='.t3lib_BEfunc::compilePreviewKeyword('id='.$pageId.'&L='.$language.'&ADMCMD_view=1&ADMCMD_editIcons=1&ADMCMD_previewWS='.$this->workspace, $GLOBALS['BE_USER']->user['uid'], 120);
	 *
	 * todo for sys_preview:
	 * - Add a comment which can be shown to previewer in frontend in some way (plus maybe ability to write back, take other action?)
	 * - Add possibility for the preview keyword to work in the backend as well: So it becomes a quick way to a certain action of sorts?
	 *
	 * @param	string		Get variables to preview, eg. 'id=1150&L=0&ADMCMD_view=1&ADMCMD_editIcons=1&ADMCMD_previewWS=8'
	 * @param	string		32 byte MD5 hash keyword for the URL: "?ADMCMD_prev=[keyword]"
	 * @param	integer		Time-To-Live for keyword
	 * @param	integer		Which workspace to preview. Workspace UID, -1 or >0. If set, the getVars is ignored in the frontend, so that string can be empty
	 * @return	string		Returns keyword to use in URL for ADMCMD_prev=
	 */
	public static function compilePreviewKeyword($getVarsStr, $beUserUid, $ttl = 172800, $fullWorkspace = NULL) {
		$field_array = array(
			'keyword' => md5(uniqid(microtime())),
			'tstamp' => time(),
			'endtime' => time()+$ttl,
			'config' => serialize(array(
				'fullWorkspace' => $fullWorkspace,
				'getVars' => $getVarsStr,
				'BEUSER_uid' => $beUserUid
			))
		);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_preview', $field_array);

		return $field_array['keyword'];
	}

	/**
	 * Unlock or Lock a record from $table with $uid
	 * If $table and $uid is not set, then all locking for the current BE_USER is removed!
	 * Usage: 5
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @param	integer		Record pid
	 * @return	void
	 * @internal
	 * @see t3lib_transferData::lockRecord(), alt_doc.php, db_layout.php, db_list.php, wizard_rte.php
	 */
	public static function lockRecords($table = '', $uid = 0, $pid = 0) {
		$user_id = intval($GLOBALS['BE_USER']->user['uid']);
		if ($table && $uid) {
			$fields_values = array(
				'userid' => $user_id,
				'tstamp' => $GLOBALS['EXEC_TIME'],
				'record_table' => $table,
				'record_uid' => $uid,
				'username' => $GLOBALS['BE_USER']->user['username'],
				'record_pid' => $pid
			);

			$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_lockedrecords', $fields_values);
		} else {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('sys_lockedrecords', 'userid='.intval($user_id));
		}
	}

	/**
	 * Returns information about whether the record from table, $table, with uid, $uid is currently locked (edited by another user - which should issue a warning).
	 * Notice: Locking is not strictly carried out since locking is abandoned when other backend scripts are activated - which means that a user CAN have a record "open" without having it locked. So this just serves as a warning that counts well in 90% of the cases, which should be sufficient.
	 * Usage: 5
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @return	array
	 * @internal
	 * @see class.db_layout.inc, alt_db_navframe.php, alt_doc.php, db_layout.php
	 */
	public static function isRecordLocked($table, $uid) {
		global $LOCKED_RECORDS;
		if (!is_array($LOCKED_RECORDS)) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							'*',
							'sys_lockedrecords',
							'sys_lockedrecords.userid!='.intval($GLOBALS['BE_USER']->user['uid']).'
								AND sys_lockedrecords.tstamp > '.($GLOBALS['EXEC_TIME']-2*3600)
						);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$LOCKED_RECORDS[$row['record_table'].':'.$row['record_uid']] = $row;
				$LOCKED_RECORDS[$row['record_table'].':'.$row['record_uid']]['msg'] = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.lockedRecord'),
					$row['username'],
					t3lib_BEfunc::calcAge($GLOBALS['EXEC_TIME']-$row['tstamp'], $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears'))
				);
				if ($row['record_pid'] && !isset($LOCKED_RECORDS[$row['record_table'].':'.$row['record_pid']])) {
					$LOCKED_RECORDS['pages:'.$row['record_pid']]['msg'] = sprintf(
						$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.lockedRecord_content'),
						$row['username'],
						t3lib_BEfunc::calcAge($GLOBALS['EXEC_TIME']-$row['tstamp'], $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears'))
					);
				}
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
		}
		return $LOCKED_RECORDS[$table.':'.$uid];
	}

	/**
	 * Returns select statement for MM relations (as used by TCEFORMs etc)
	 * Usage: 3
	 *
	 * @param	array		Configuration array for the field, taken from $TCA
	 * @param	string		Field name
	 * @param	array		TSconfig array from which to get further configuration settings for the field name
	 * @param	string		Prefix string for the key "*foreign_table_where" from $fieldValue array
	 * @return	string		Part of query
	 * @internal
	 * @see t3lib_transferData::renderRecord(), t3lib_TCEforms::foreignTable()
	 */
	public static function exec_foreign_table_where_query($fieldValue, $field = '', $TSconfig = array(), $prefix = '') {
		global $TCA;

		t3lib_div::loadTCA($foreign_table);
		$foreign_table = $fieldValue['config'][$prefix.'foreign_table'];
		$rootLevel = $TCA[$foreign_table]['ctrl']['rootLevel'];

		$fTWHERE = $fieldValue['config'][$prefix.'foreign_table_where'];
		if (strstr($fTWHERE, '###REC_FIELD_')) {
			$fTWHERE_parts = explode('###REC_FIELD_', $fTWHERE);
			while(list($kk, $vv) = each($fTWHERE_parts)) {
				if ($kk) {
					$fTWHERE_subpart = explode('###', $vv, 2);
					$fTWHERE_parts[$kk] = $TSconfig['_THIS_ROW'][$fTWHERE_subpart[0]].$fTWHERE_subpart[1];
				}
			}
			$fTWHERE = implode('', $fTWHERE_parts);
		}

		$fTWHERE = str_replace('###CURRENT_PID###', intval($TSconfig['_CURRENT_PID']), $fTWHERE);
		$fTWHERE = str_replace('###THIS_UID###', intval($TSconfig['_THIS_UID']), $fTWHERE);
		$fTWHERE = str_replace('###THIS_CID###', intval($TSconfig['_THIS_CID']), $fTWHERE);
		$fTWHERE = str_replace('###STORAGE_PID###', intval($TSconfig['_STORAGE_PID']), $fTWHERE);
		$fTWHERE = str_replace('###SITEROOT###', intval($TSconfig['_SITEROOT']), $fTWHERE);
		$fTWHERE = str_replace('###PAGE_TSCONFIG_ID###', intval($TSconfig[$field]['PAGE_TSCONFIG_ID']), $fTWHERE);
		$fTWHERE = str_replace('###PAGE_TSCONFIG_IDLIST###', $GLOBALS['TYPO3_DB']->cleanIntList($TSconfig[$field]['PAGE_TSCONFIG_IDLIST']), $fTWHERE);
		$fTWHERE = str_replace('###PAGE_TSCONFIG_STR###', $GLOBALS['TYPO3_DB']->quoteStr($TSconfig[$field]['PAGE_TSCONFIG_STR'], $foreign_table), $fTWHERE);

			// rootLevel = -1 is not handled 'properly' here - it goes as if it was rootLevel = 1 (that is pid=0)
		$wgolParts = $GLOBALS['TYPO3_DB']->splitGroupOrderLimit($fTWHERE);
		if ($rootLevel) {
			$queryParts = array(
				'SELECT' => t3lib_BEfunc::getCommonSelectFields($foreign_table, $foreign_table.'.'),
				'FROM' => $foreign_table,
				'WHERE' => $foreign_table.'.pid=0 '.
							t3lib_BEfunc::deleteClause($foreign_table).' '.
							$wgolParts['WHERE'],
				'GROUPBY' => $wgolParts['GROUPBY'],
				'ORDERBY' => $wgolParts['ORDERBY'],
				'LIMIT' => $wgolParts['LIMIT']
			);
		} else {
			$pageClause = $GLOBALS['BE_USER']->getPagePermsClause(1);
			if ($foreign_table!='pages') {
				$queryParts = array(
					'SELECT' => t3lib_BEfunc::getCommonSelectFields($foreign_table, $foreign_table.'.'),
					'FROM' => $foreign_table.', pages',
					'WHERE' => 'pages.uid='.$foreign_table.'.pid
								AND pages.deleted=0 '.
								t3lib_BEfunc::deleteClause($foreign_table).
								' AND '.$pageClause.' '.
								$wgolParts['WHERE'],
					'GROUPBY' => $wgolParts['GROUPBY'],
					'ORDERBY' => $wgolParts['ORDERBY'],
					'LIMIT' => $wgolParts['LIMIT']
				);
			} else {
				$queryParts = array(
					'SELECT' => t3lib_BEfunc::getCommonSelectFields($foreign_table, $foreign_table.'.'),
					'FROM' => 'pages',
					'WHERE' => 'pages.deleted=0
								AND '.$pageClause.' '.
								$wgolParts['WHERE'],
					'GROUPBY' => $wgolParts['GROUPBY'],
					'ORDERBY' => $wgolParts['ORDERBY'],
					'LIMIT' => $wgolParts['LIMIT']
				);
			}
		}

		return $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
	}

	/**
	 * Returns TSConfig for the TCEFORM object in Page TSconfig.
	 * Used in TCEFORMs
	 * Usage: 4
	 *
	 * @param	string		Table name present in TCA
	 * @param	array		Row from table
	 * @return	array
	 * @see t3lib_transferData::renderRecord(), t3lib_TCEforms::setTSconfig(), SC_wizard_list::main(), SC_wizard_add::main()
	 */
	public static function getTCEFORM_TSconfig($table, $row) {
		t3lib_BEfunc::fixVersioningPid($table, $row);

		$res = array();
		$typeVal = t3lib_BEfunc::getTCAtypeValue($table, $row);

			// Get main config for the table
		list($TScID, $cPid) = t3lib_BEfunc::getTSCpid($table, $row['uid'], $row['pid']);

		$rootLine = t3lib_BEfunc::BEgetRootLine($TScID, '', TRUE);
		if ($TScID>=0) {
			$tempConf = $GLOBALS['BE_USER']->getTSConfig('TCEFORM.'.$table, t3lib_BEfunc::getPagesTSconfig($TScID, $rootLine));
			if (is_array($tempConf['properties'])) {
				while(list($key, $val) = each($tempConf['properties'])) {
					if (is_array($val)) {
						$fieldN = substr($key, 0, -1);
						$res[$fieldN] = $val;
						unset($res[$fieldN]['types.']);
						if (strcmp($typeVal, '') && is_array($val['types.'][$typeVal.'.'])) {
							$res[$fieldN] = t3lib_div::array_merge_recursive_overrule($res[$fieldN], $val['types.'][$typeVal.'.']);
						}
					}
				}
			}
		}
		$res['_CURRENT_PID'] = $cPid;
		$res['_THIS_UID'] = $row['uid'];
		$res['_THIS_CID'] = $row['cid'];
		$res['_THIS_ROW'] = $row;	// So the row will be passed to foreign_table_where_query()

		reset($rootLine);
		while(list(,$rC) = each($rootLine)) {
			if (!$res['_STORAGE_PID'])	$res['_STORAGE_PID'] = intval($rC['storage_pid']);
			if (!$res['_SITEROOT'])	$res['_SITEROOT'] = $rC['is_siteroot']?intval($rC['uid']):0;
		}

		return $res;
	}

	/**
	 * Find the real PID of the record (with $uid from $table). This MAY be impossible if the pid is set as a reference to the former record or a page (if two records are created at one time).
	 * NOTICE: Make sure that the input PID is never negative because the record was an offline version! Therefore, you should always use t3lib_BEfunc::fixVersioningPid($table,$row); on the data you input before calling this function!
	 * Usage: 2
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @param	integer		Record pid, could be negative then pointing to a record from same table whose pid to find and return.
	 * @return	integer
	 * @internal
	 * @see t3lib_TCEmain::copyRecord(), getTSCpid()
	 */
	public static function getTSconfig_pidValue($table, $uid, $pid)	{

		if (t3lib_div::testInt($pid))	{	// If pid is an integer this takes precedence in our lookup.
			$thePidValue = intval($pid);
			if ($thePidValue<0)	{	// If ref to another record, look that record up.
				$pidRec = t3lib_BEfunc::getRecord($table, abs($thePidValue), 'pid');
				$thePidValue = is_array($pidRec) ? $pidRec['pid'] : -2;	// Returns -2 if the record did not exist.
			}
			// ... else the pos/zero pid is just returned here.
		} else {	// No integer pid and we are forced to look up the $pid
			$rr = t3lib_BEfunc::getRecord($table, $uid);	// Try to fetch the record pid from uid. If the uid is 'NEW...' then this will of course return nothing...

			if (is_array($rr)) {
					// First check if the pid is -1 which means it is a workspaced element. Get the "real" record:
				if ($rr['pid']=='-1') {
					$rr = t3lib_BEfunc::getRecord($table, $rr['t3ver_oid'], 'pid');
					if (is_array($rr)) {
						$thePidValue = $rr['pid'];
					}
				} else {
					$thePidValue = $rr['pid'];	// Returning the "pid" of the record
				}
			}

			if (!$thePidValue)	$thePidValue = -1;	// Returns -1 if the record with this pid was not found.
		}

		return $thePidValue;
	}

	/**
	 * Return $uid if $table is pages and $uid is integer - otherwise the $pid
	 * Usage: 1
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @param	integer		Record pid
	 * @return	integer
	 * @internal
	 * @see t3lib_TCEforms::getTSCpid()
	 */
	public static function getPidForModTSconfig($table, $uid, $pid) {
		$retVal = ($table=='pages' && t3lib_div::testInt($uid)) ? $uid : $pid;
		return $retVal;
	}

	/**
	 * Returns the REAL pid of the record, if possible. If both $uid and $pid is strings, then pid=-1 is returned as an error indication.
	 * Usage: 8
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @param	integer		Record pid
	 * @return	array		Array of two integers; first is the REAL PID of a record and if its a new record negative values are resolved to the true PID, second value is the PID value for TSconfig (uid if table is pages, otherwise the pid)
	 * @internal
	 * @see t3lib_TCEmain::setHistory(), t3lib_TCEmain::process_datamap()
	 */
	public static function getTSCpid($table, $uid, $pid) {
			// If pid is negative (referring to another record) the pid of the other record is fetched and returned.
		$cPid = t3lib_BEfunc::getTSconfig_pidValue($table, $uid, $pid);
			// $TScID is the id of $table = pages, else it's the pid of the record.
		$TScID = t3lib_BEfunc::getPidForModTSconfig($table, $uid, $cPid);

		return array($TScID, $cPid);
	}

	/**
	 * Returns first found domain record "domainName" (without trailing slash) if found in the input $rootLine
	 * Usage: 2
	 *
	 * @param	array		Root line array
	 * @return	string		Domain name, if found.
	 */
	public static function firstDomainRecord($rootLine) {
		if (t3lib_extMgm::isLoaded('cms')) {
			reset($rootLine);
			while(list(,$row) = each($rootLine)) {
				$dRec = t3lib_BEfunc::getRecordsByField('sys_domain', 'pid', $row['uid'], ' AND redirectTo=\'\' AND hidden=0', '', 'sorting');
				if (is_array($dRec)) {
					reset($dRec);
					$dRecord = current($dRec);
					return ereg_replace('\/$', '', $dRecord['domainName']);
				}
			}
		}
	}

	/**
	 * Returns the sys_domain record for $domain, optionally with $path appended.
	 * Usage: 2
	 *
	 * @param	string		Domain name
	 * @param	string		Appended path
	 * @return	array		Domain record, if found
	 */
	public static function getDomainStartPage($domain, $path = '') {
		if (t3lib_extMgm::isLoaded('cms')) {
			$domain = explode(':', $domain);
			$domain = strtolower(ereg_replace('\.$', '', $domain[0]));
				// path is calculated.
			$path = trim(ereg_replace('\/[^\/]*$', '', $path));
				// stuff:
			$domain.=$path;

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('sys_domain.*', 'pages,sys_domain', '
				pages.uid=sys_domain.pid
				AND sys_domain.hidden=0
				AND (sys_domain.domainName='.$GLOBALS['TYPO3_DB']->fullQuoteStr($domain, 'sys_domain').' OR sys_domain.domainName='.$GLOBALS['TYPO3_DB']->fullQuoteStr($domain.'/', 'sys_domain').')'.
				t3lib_BEfunc::deleteClause('pages'),
				'', '', '1');
			$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			return $result;
		}
	}

	/**
	 * Returns overlayered RTE setup from an array with TSconfig. Used in TCEforms and TCEmain
	 * Usage: 8
	 *
	 * @param	array		The properties of Page TSconfig in the key "RTE."
	 * @param	string		Table name
	 * @param	string		Field name
	 * @param	string		Type value of the current record (like from CType of tt_content)
	 * @return	array		Array with the configuration for the RTE
	 * @internal
	 */
	public static function RTEsetup($RTEprop, $table, $field, $type = '') {
		$thisConfig = is_array($RTEprop['default.']) ? $RTEprop['default.'] : array();
		$thisFieldConf = $RTEprop['config.'][$table.'.'][$field.'.'];
		if (is_array($thisFieldConf)) {
			unset($thisFieldConf['types.']);
			$thisConfig = t3lib_div::array_merge_recursive_overrule($thisConfig, $thisFieldConf);
		}
		if ($type && is_array($RTEprop['config.'][$table.'.'][$field.'.']['types.'][$type.'.'])) {
			$thisConfig = t3lib_div::array_merge_recursive_overrule($thisConfig, $RTEprop['config.'][$table.'.'][$field.'.']['types.'][$type.'.']);
		}
		return $thisConfig;
	}

	/**
	 * Returns first possible RTE object if available.
	 * Usage: $RTEobj = &t3lib_BEfunc::RTEgetObj();
	 *
	 * @return	mixed		If available, returns RTE object, otherwise an array of messages from possible RTEs
	 */
	public static function &RTEgetObj()	{

			// If no RTE object has been set previously, try to create it:
		if (!isset($GLOBALS['T3_VAR']['RTEobj']))	{

				// Set the object string to blank by default:
			$GLOBALS['T3_VAR']['RTEobj'] = array();

				// Traverse registered RTEs:
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['BE']['RTE_reg'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['BE']['RTE_reg'] as $extKey => $rteObjCfg) {
					$rteObj = &t3lib_div::getUserObj($rteObjCfg['objRef']);
					if (is_object($rteObj)) {
						if ($rteObj->isAvailable()) {
							$GLOBALS['T3_VAR']['RTEobj'] = &$rteObj;
							break;
						} else {
							$GLOBALS['T3_VAR']['RTEobj'] = array_merge($GLOBALS['T3_VAR']['RTEobj'], $rteObj->errorLog);
						}
					}
				}
			}

			if (!count($GLOBALS['T3_VAR']['RTEobj'])) {
				$GLOBALS['T3_VAR']['RTEobj'][] = 'No RTEs configured at all';
			}
		}

			// Return RTE object (if any!)
		return $GLOBALS['T3_VAR']['RTEobj'];
	}

	/**
	 * Returns soft-reference parser for the softRef processing type
	 * Usage: $softRefObj = &t3lib_BEfunc::softRefParserObj('[parser key]');
	 *
	 * @param	string		softRef parser key
	 * @return	mixed		If available, returns Soft link parser object.
	 */
	public static function &softRefParserObj($spKey)	{

			// If no softRef parser object has been set previously, try to create it:
		if (!isset($GLOBALS['T3_VAR']['softRefParser'][$spKey]))	{

				// Set the object string to blank by default:
			$GLOBALS['T3_VAR']['softRefParser'][$spKey] = '';

				// Now, try to create parser object:
			$objRef = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser'][$spKey] ?
							$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser'][$spKey] :
							$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser_GL'][$spKey];
			if ($objRef) {
				$softRefParserObj = &t3lib_div::getUserObj($objRef, '');
				if (is_object($softRefParserObj)) {
					$GLOBALS['T3_VAR']['softRefParser'][$spKey] = &$softRefParserObj;
				}
			}
		}

			// Return RTE object (if any!)
		return $GLOBALS['T3_VAR']['softRefParser'][$spKey];
	}

	/**
	 * Returns array of soft parser references
	 *
	 * @param	string		softRef parser list
	 * @param	string		Table name
	 * @param	string		Field name
	 * @return	array		Array where the parser key is the key and the value is the parameter string
	 */
	public static function explodeSoftRefParserList($parserList)	{

			// Looking for global parsers:
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser_GL'])) {
			$parserList = implode(',', array_keys($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser_GL'])).','.$parserList;
		}

			// Return immediately if list is blank:
		if (!strlen($parserList))	return FALSE;

			// Otherwise parse the list:
		$keyList = t3lib_div::trimExplode(',', $parserList, 1);
		$output = array();

		foreach($keyList as $val) {
			$reg = array();
			if (ereg('^([[:alnum:]_-]+)\[(.*)\]$', $val, $reg)) {
				$output[$reg[1]] = t3lib_div::trimExplode(';', $reg[2], 1);
			} else {
				$output[$val] = '';
			}
		}
		return $output;
	}

	/**
	 * Returns true if $modName is set and is found as a main- or submodule in $TBE_MODULES array
	 * Usage: 1
	 *
	 * @param	string		Module name
	 * @return	boolean
	 */
	public static function isModuleSetInTBE_MODULES($modName) {
		reset($GLOBALS['TBE_MODULES']);
		$loaded = array();
		while(list($mkey, $list) = each($GLOBALS['TBE_MODULES'])) {
			$loaded[$mkey] = 1;
			if (!is_array($list) && trim($list)) {
				$subList = t3lib_div::trimExplode(',', $list, 1);
				while(list(,$skey) = each($subList)) {
					$loaded[$mkey.'_'.$skey] = 1;
				}
			}
		}
		return $modName && isset($loaded[$modName]);
	}

	/**
	 * Counting references to a record/file
	 *
	 * @param	string		Table name (or "_FILE" if its a file)
	 * @param	string		Reference: If table, then integer-uid, if _FILE, then file reference (relative to PATH_site)
	 * @param	string		Message with %s, eg. "There were %s records pointing to this file!"
	 * @return	string		Output string (or integer count value if no msg string specified)
	 */
	public static function referenceCount($table, $ref, $msg = '')	{

		if ($table=='_FILE') {

			if (t3lib_div::isFirstPartOfStr($ref, PATH_site)) {
				$ref = substr($ref, strlen(PATH_site));
			} else return '';

				// Look up the path:
			list($res) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'count(*) as count',
				'sys_refindex',
				'ref_table='.$GLOBALS['TYPO3_DB']->fullQuoteStr($table, 'sys_refindex').
					' AND ref_string='.$GLOBALS['TYPO3_DB']->fullQuoteStr($ref, 'sys_refindex').
					' AND deleted=0'
			);

		} else {
				// Look up the path:
			list($res) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'count(*) as count',
				'sys_refindex',
				'ref_table='.$GLOBALS['TYPO3_DB']->fullQuoteStr($table, 'sys_refindex').
					' AND ref_uid='.intval($ref).
					' AND deleted=0'
			);
		}

		return $res['count'] ? ($msg ? sprintf($msg, $res['count']) : $res['count']) : '';
	}














	/*******************************************
	 *
	 * Workspaces / Versioning
	 *
	 *******************************************/

	/**
	 * Select all versions of a record, ordered by version id (DESC)
	 *
	 * @param	string		Table name to select from
	 * @param	integer		Record uid for which to find versions.
	 * @param	string		Field list to select
	 * @param	integer		Workspace ID, if zero all versions regardless of workspace is found.
	 * @param	boolean		If set, deleted-flagged versions are included! (Only for clean-up script!)
	 * @return	array		Array of versions of table/uid
	 */
	public static function selectVersionsOfRecord($table, $uid, $fields = '*', $workspace = 0, $includeDeletedRecords = FALSE) {
		global $TCA;

		if ($TCA[$table] && $TCA[$table]['ctrl']['versioningWS'])	{

				// Select all versions of record:
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				$fields,
				$table,
				'((pid=-1 AND t3ver_oid='.intval($uid).($workspace!=0?' AND t3ver_wsid='.intval($workspace):'').') OR uid='.intval($uid).')'.
					($includeDeletedRecords ? '' : t3lib_BEfunc::deleteClause($table)),
				'',
				't3ver_id DESC'
			);

				// Add rows to output array:
			$realPid = 0;
			$outputRows = array();
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($uid==$row['uid']) {
					$row['_CURRENT_VERSION'] = TRUE;
					$realPid = $row['pid'];
				}
				$outputRows[] = $row;
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

				// Set real-pid:
			foreach($outputRows as $idx => $oRow) {
				$outputRows[$idx]['_REAL_PID'] = $realPid;
			}

			return $outputRows;
		}
	}

	/**
	 * Find page-tree PID for versionized record
	 * Will look if the "pid" value of the input record is -1 and if the table supports versioning - if so, it will translate the -1 PID into the PID of the original record
	 * Used whenever you are tracking something back, like making the root line.
	 * Will only translate if the workspace of the input record matches that of the current user (unless flag set)
	 * Principle; Record offline! => Find online?
	 *
	 * @param	string		Table name
	 * @param	array		Record array passed by reference. As minimum, "pid" and "uid" fields must exist! "t3ver_oid" and "t3ver_wsid" is nice and will save you a DB query.
	 * @param	boolean		Ignore workspace match
	 * @return	void		(Passed by ref). If the record had its pid corrected to the online versions pid, then "_ORIG_pid" is set to the original pid value (-1 of course). The field "_ORIG_pid" is used by various other functions to detect if a record was in fact in a versionized branch.
	 * @see t3lib_page::fixVersioningPid()
	 */
	public static function fixVersioningPid($table, &$rr, $ignoreWorkspaceMatch = FALSE) {
		global $TCA;

			// Check that the input record is an offline version from a table that supports versioning:
		if (is_array($rr) && $rr['pid']==-1 && $TCA[$table]['ctrl']['versioningWS'])	{

				// Check values for t3ver_oid and t3ver_wsid:
			if (isset($rr['t3ver_oid']) && isset($rr['t3ver_wsid']))	{	// If "t3ver_oid" is already a field, just set this:
				$oid = $rr['t3ver_oid'];
				$wsid = $rr['t3ver_wsid'];
			} else {	// Otherwise we have to expect "uid" to be in the record and look up based on this:
				$newPidRec = t3lib_BEfunc::getRecord($table, $rr['uid'], 't3ver_oid,t3ver_wsid');
				if (is_array($newPidRec)) {
					$oid = $newPidRec['t3ver_oid'];
					$wsid = $newPidRec['t3ver_wsid'];
				}
			}

				// If ID of current online version is found, look up the PID value of that:
			if ($oid && ($ignoreWorkspaceMatch || !strcmp((int)$wsid, $GLOBALS['BE_USER']->workspace))) {
				$oidRec = t3lib_BEfunc::getRecord($table, $oid, 'pid');
				if (is_array($oidRec)) {
					$rr['_ORIG_pid'] = $rr['pid'];
					$rr['pid'] = $oidRec['pid'];
				}
			}
		}
	}

	/**
	 * Workspace Preview Overlay
	 * Generally ALWAYS used when records are selected based on uid or pid. If records are selected on other fields than uid or pid (eg. "email = ....") then usage might produce undesired results and that should be evaluated on individual basis.
	 * Principle; Record online! => Find offline?
	 * Recently, this function has been modified so it MAY set $row to FALSE. This happens if a version overlay with the move-id pointer is found in which case we would like a backend preview. In other words, you should check if the input record is still an array afterwards when using this function.
	 *
	 * @param	string		Table name
	 * @param	array		Record array passed by reference. As minimum, the "uid", "pid" and "t3ver_swapmode" (pages) fields must exist! Fake fields cannot exist since the fields in the array is used as field names in the SQL look up. It would be nice to have fields like "t3ver_state" and "t3ver_mode_id" as well to avoid a new lookup inside movePlhOL().
	 * @param	integer		Workspace ID, if not specified will use $GLOBALS['BE_USER']->workspace
	 * @return	void		(Passed by ref).
	 * @see fixVersioningPid()
	 */
	public static function workspaceOL($table, &$row, $wsid = -99, $unsetMovePointers = FALSE) {
		global $TCA;

		$previewMovePlaceholders = TRUE;		// If this is false the placeholder is shown raw in the backend. I don't know if this move can be useful for users to toggle. Technically it can help debugging...

			// Initialize workspace ID:
		if ($wsid == -99)	$wsid = $GLOBALS['BE_USER']->workspace;

			// Check if workspace is different from zero and record is set:
		if ($wsid!==0 && is_array($row))	{

				// Check if input record is a move-placeholder and if so, find the pointed-to live record:
			if ($previewMovePlaceholders) {
				$orig_uid = $row['uid'];
				$orig_pid = $row['pid'];
				$movePldSwap = t3lib_BEfunc::movePlhOL($table, $row);
	#			if (!is_array($row)) return;
			}

			$wsAlt = t3lib_BEfunc::getWorkspaceVersionOfRecord($wsid, $table, $row['uid'], implode(',', array_keys($row)));

				// If version was found, swap the default record with that one.
			if (is_array($wsAlt))	{

					// Check if this is in move-state:
				if ($previewMovePlaceholders && !$movePldSwap && ($table=='pages' || (int)$TCA[$table]['ctrl']['versioningWS']>=2) && $unsetMovePointers)	{	// Only for WS ver 2... (moving)

						// If t3ver_state is not found, then find it... (but we like best if it is here...)
					if (!isset($wsAlt['t3ver_state'])) {
						$stateRec = t3lib_BEfunc::getRecord($table, $wsAlt['uid'], 't3ver_state');
						$state = $stateRec['t3ver_state'];
					} else {
						$state = $wsAlt['t3ver_state'];
					}
					if ((int)$state===4) {
							// TODO: Same problem as frontend in versionOL(). See TODO point there.
						$row = FALSE;
						return;
					}
				}

					// Always correct PID from -1 to what it should be:
				if (isset($wsAlt['pid'])) {
					$wsAlt['_ORIG_pid'] = $wsAlt['pid'];	// Keep the old (-1) - indicates it was a version...
					$wsAlt['pid'] = $row['pid'];		// Set in the online versions PID.
				}

					// For versions of single elements or page+content, swap UID and PID:
				if ($table!=='pages' || $wsAlt['t3ver_swapmode']<=0) {
					$wsAlt['_ORIG_uid'] = $wsAlt['uid'];
					$wsAlt['uid'] = $row['uid'];

						// Backend css class:
					$wsAlt['_CSSCLASS'] = $table==='pages' && $wsAlt['t3ver_swapmode']==0 ? 'ver-page' : 'ver-element';
				} else {	// This is only for page-versions with BRANCH below!
					$wsAlt['_ONLINE_uid'] = $row['uid'];

						// Backend css class:
					$wsAlt['_CSSCLASS'] = 'ver-branchpoint';
					$wsAlt['_SUBCSSCLASS'] = 'ver-branch';
				}

					// Changing input record to the workspace version alternative:
				$row = $wsAlt;
			}

				// If the original record was a move placeholder, the uid and pid of that is preserved here:
			if ($movePldSwap) {
				$row['_MOVE_PLH'] = TRUE;
				$row['_MOVE_PLH_uid'] = $orig_uid;
				$row['_MOVE_PLH_pid'] = $orig_pid;
				$row['t3ver_state'] = 3;	// For display; To make the icon right for the placeholder vs. the original
			}
		}
	}

	/**
	 * Checks if record is a move-placeholder (t3ver_state==3) and if so it will set $row to be the pointed-to live record (and return TRUE)
	 *
	 * @param	string		Table name
	 * @param	array		Row (passed by reference) - must be online record!
	 * @return	boolean		True if overlay is made.
	 * @see t3lib_page::movePlhOl()
	 */
	public static function movePlhOL($table, &$row) {
		global $TCA;

		if ($table=='pages' || (int)$TCA[$table]['ctrl']['versioningWS']>=2)	{	// Only for WS ver 2... (moving)

				// If t3ver_move_id or t3ver_state is not found, then find it... (but we like best if it is here...)
			if (!isset($row['t3ver_move_id']) || !isset($row['t3ver_state'])) {
				$moveIDRec = t3lib_BEfunc::getRecord($table, $row['uid'], 't3ver_move_id, t3ver_state');
				$moveID = $moveIDRec['t3ver_move_id'];
				$state = $moveIDRec['t3ver_state'];
			} else {
				$moveID = $row['t3ver_move_id'];
				$state = $row['t3ver_state'];
			}

				// Find pointed-to record.
			if ((int)$state===3 && $moveID) {
				if ($origRow = t3lib_BEfunc::getRecord($table, $moveID, implode(',', array_keys($row)))) {
					$row = $origRow;
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	/**
	 * Select the workspace version of a record, if exists
	 *
	 * @param	integer		Workspace ID
	 * @param	string		Table name to select from
	 * @param	integer		Record uid for which to find workspace version.
	 * @param	string		Field list to select
	 * @return	array		If found, return record, otherwise false
	 */
	public static function getWorkspaceVersionOfRecord($workspace, $table, $uid, $fields = '*') {
		global $TCA;

		if ($workspace!==0 && $TCA[$table] && $TCA[$table]['ctrl']['versioningWS'])	{

				// Select workspace version of record:
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				$fields,
				$table,
				'pid=-1 AND
				 t3ver_oid='.intval($uid).' AND
				 t3ver_wsid='.intval($workspace).
					t3lib_BEfunc::deleteClause($table)
			);

			if (is_array($rows[0]))	return $rows[0];
		}

		return FALSE;
	}

	/**
	 * Returns live version of record
	 *
	 * @param	string		Table name
	 * @param	integer		Record UID of draft, offline version
	 * @param	string		Field list, default is *
	 * @return	array		If found, the record, otherwise nothing.
	 */
	public static function getLiveVersionOfRecord($table, $uid, $fields = '*') {
		global $TCA;

			// Check that table supports versioning:
		if ($TCA[$table] && $TCA[$table]['ctrl']['versioningWS']) {
			$rec = t3lib_BEfunc::getRecord($table, $uid, 'pid,t3ver_oid');

			if ($rec['pid']==-1) {
				return t3lib_BEfunc::getRecord($table, $rec['t3ver_oid'], $fields);
			}
		}
	}

	/**
	 * Will fetch the rootline for the pid, then check if anywhere in the rootline there is a branch point and if so everything is allowed of course.
	 * Alternatively; if the page of the PID itself is a version and swapmode is zero (page+content) then tables from versioning_followPages are allowed as well.
	 *
	 * @param	integer		Page id inside of which you want to edit/create/delete something.
	 * @param	string		Table name you are checking for. If you don't give the table name ONLY "branch" types are found and returned true. Specifying table you might also get a positive response if the pid is a "page" versioning type AND the table has "versioning_followPages" set.
	 * @param	boolean		If set, the keyword "branchpoint" or "first" is not returned by rather the "t3ver_stage" value of the branch-point.
	 * @return	mixed		Returns either "branchpoint" (if branch) or "first" (if page) or false if nothing. Alternatively, it returns the value of "t3ver_stage" for the branchpoint (if any)
	 */
	public static function isPidInVersionizedBranch($pid, $table = '', $returnStage = FALSE) {
		$rl = t3lib_BEfunc::BEgetRootLine($pid);
		$c = 0;

		foreach($rl as $rec) {
			if ($rec['_ORIG_pid']==-1) {
					// In any case: is it a branchpoint, then OK...
				if ($rec['t3ver_swapmode']>0) {
					return $returnStage ? (int)$rec['t3ver_stage'] : 'branchpoint';	// OK, we are in a versionized branch
				} elseif ($c==0 && $rec['t3ver_swapmode']==0 && $table && $GLOBALS['TCA'][$table]['ctrl']['versioning_followPages'])	{	// First level: So $table must be versioning_followPages
					return $returnStage ? (int)$rec['t3ver_stage'] : 'first';	// OK, we are in a versionized branch
				}
			}
			$c++;
		}
	}

	/**
	 * Will return where clause de-selecting new(/deleted)-versions from other workspaces.
	 *
	 * @param	string		Table name
	 * @return	string		Where clause if applicable.
	 */
	public static function versioningPlaceholderClause($table) {
		if ($GLOBALS['BE_USER']->workspace!==0 && $GLOBALS['TCA'][$table] && $GLOBALS['TCA'][$table]['ctrl']['versioningWS']) {
			return ' AND ('.$table.'.t3ver_state<=0 OR '.$table.'.t3ver_wsid='.intval($GLOBALS['BE_USER']->workspace).')';
		}
	}

	/**
	 * Count number of versions on a page
	 *
	 * @param	integer		Workspace ID
	 * @param	integer		Page ID
	 * @param	boolean		If set, then all tables and not only "versioning_followPages" are found (except other pages)
	 * @return	array		Overview of records
	 */
	public static function countVersionsOfRecordsOnPage($workspace, $pageId, $allTables = FALSE) {
		$output = array();
		if ($workspace!=0) {
			foreach($GLOBALS['TCA'] as $tableName => $cfg) {
				if ($tableName!='pages' && $cfg['ctrl']['versioningWS'] && ($cfg['ctrl']['versioning_followPages'] || $allTables))	{

						// Select all records from this table in the database from the workspace
						// This joins the online version with the offline version as tables A and B
					$output[$tableName] = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows (
						'B.uid as live_uid, A.uid as offline_uid',
						$tableName.' A,'.$tableName.' B',
						'A.pid=-1'.	// Table A is the offline version and pid=-1 defines offline
							' AND B.pid='.intval($pageId).
							' AND A.t3ver_wsid='.intval($workspace).
							' AND A.t3ver_oid=B.uid'.	// ... and finally the join between the two tables.
							t3lib_BEfunc::deleteClause($tableName, 'A').
							t3lib_BEfunc::deleteClause($tableName, 'B')
					);

					if (!is_array($output[$tableName]) || !count($output[$tableName])) {
						unset($output[$tableName]);
					}
				}
			}
		}
		return $output;
	}

	/**
	 * Performs mapping of new uids to new versions UID in case of import inside a workspace.
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid (of live record placeholder)
	 * @return	integer		Uid of offline version if any, otherwise live uid.
	 */
	public static function wsMapId($table, $uid) {
		if ($wsRec = t3lib_BEfunc::getWorkspaceVersionOfRecord($GLOBALS['BE_USER']->workspace, $table, $uid, 'uid')) {
			return $wsRec['uid'];
		} else {
			return $uid;
		}
	}

	/**
	 * Returns move placeholder of online (live) version
	 *
	 * @param	string		Table name
	 * @param	integer		Record UID of online version
	 * @param	string		Field list, default is *
	 * @return	array		If found, the record, otherwise nothing.
	 */
	public static function getMovePlaceholder($table, $uid, $fields = '*') {
		global $TCA;

		$workspace = $GLOBALS['BE_USER']->workspace;
		if ($workspace!==0 && $TCA[$table] && (int)$TCA[$table]['ctrl']['versioningWS']>=2)	{

				// Select workspace version of record:
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				$fields,
				$table,
				'pid!=-1 AND
				 t3ver_state=3 AND
				 t3ver_move_id='.intval($uid).' AND
				 t3ver_wsid='.intval($workspace).
					t3lib_BEfunc::deleteClause($table)
			);

			if (is_array($rows[0]))	return $rows[0];
		}

		return FALSE;
	}





	/*******************************************
	 *
	 * Miscellaneous
	 *
	 *******************************************/

	/**
	 * Print error message with header, text etc.
	 * Usage: 19
	 *
	 * @param	string		Header string
	 * @param	string		Content string
	 * @param	boolean		Will return an alert() with the content of header and text.
	 * @param	boolean		Print header.
	 * @return	void
	 */
	public static function typo3PrintError($header, $text, $js = '', $head = 1) {
			// This prints out a TYPO3 error message.
			// If $js is set the message will be output in JavaScript
		if ($js) {
			echo "alert('".t3lib_div::slashJS($header.'\n'.$text)."');";
		} else {
			echo $head?'<html>
				<head>
					<title>Error!</title>
				</head>
				<body bgcolor="white" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">':'';
			echo '<div align="center">
					<table border="0" cellspacing="0" cellpadding="0" width="333">
						<tr>
							<td align="center">'.
								($GLOBALS['TBE_STYLES']['logo_login']?'<img src="'.$GLOBALS['BACK_PATH'].$GLOBALS['TBE_STYLES']['logo_login'].'" alt="" />':'<img src="'.$GLOBALS['BACK_PATH'].'gfx/typo3logo.gif" width="123" height="34" vspace="10" />').
							'</td>
						</tr>
						<tr>
							<td bgcolor="black">
								<table width="100%" border="0" cellspacing="1" cellpadding="10">
									<tr>
										<td bgcolor="#F4F0E8">
											<font face="verdana,arial,helvetica" size="2">';
			echo '<b><center><font size="+1">'.$header.'</font></center></b><br />'.$text;
			echo '							</font>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</div>';
			echo $head?'
				</body>
			</html>':'';
		}
	}

	/**
	 * Prints TYPO3 Copyright notice for About Modules etc. modules.
	 *
	 * @return	void
	 */
	public static function TYPO3_copyRightNotice() {
		global $TYPO3_CONF_VARS;

			// COPYRIGHT NOTICE:
		$loginCopyrightWarrantyProvider = strip_tags(trim($TYPO3_CONF_VARS['SYS']['loginCopyrightWarrantyProvider']));
		$loginCopyrightWarrantyURL = strip_tags(trim($TYPO3_CONF_VARS['SYS']['loginCopyrightWarrantyURL']));

		if (strlen($loginCopyrightWarrantyProvider)>=2 && strlen($loginCopyrightWarrantyURL)>=10) {
			$warrantyNote = 'Warranty is supplied by '.htmlspecialchars($loginCopyrightWarrantyProvider).'; <a href="'.htmlspecialchars($loginCopyrightWarrantyURL).'" target="_blank">click for details.</a>';
		} else {
			$warrantyNote = 'TYPO3 comes with ABSOLUTELY NO WARRANTY; <a href="http://typo3.com/1316.0.html" target="_blank">click for details.</a>';
		}
		$cNotice = '<a href="http://typo3.com/" target="_blank"><img src="gfx/loginlogo_transp.gif" width="75" vspace="2" hspace="4" height="19" alt="TYPO3 logo" align="left" />TYPO3 CMS ver. '.htmlspecialchars(TYPO3_version).'</a>. Copyright &copy; '.htmlspecialchars(TYPO3_copyright_year).' Kasper Sk&aring;rh&oslash;j. Extensions are copyright of their respective owners. Go to <a href="http://typo3.com/" target="_blank">http://typo3.com/</a> for details.
		'.strip_tags($warrantyNote, '<a>').' This is free software, and you are welcome to redistribute it under certain conditions; <a href="http://typo3.com/1316.0.html" target="_blank">click for details</a>. Obstructing the appearance of this notice is prohibited by law.';

		return $cNotice;
	}

	/**
	 * Display some warning messages if this installation is obviously insecure!!
	 * These warnings are only displayed to admin users
	 *
	 * @return	void
	 */
	public static function displayWarningMessages() {
		if ($GLOBALS['BE_USER']->isAdmin()) {
			$warnings = array();	// Array containing warnings that must be displayed
			$enableInstallToolFile = PATH_site.'typo3conf/ENABLE_INSTALL_TOOL';	// If this file exists, the Install Tool is enabled

			$cmd = t3lib_div::_GET('adminWarning_cmd');	// Cleanup command, if set
			switch($cmd) {
				case 'remove_ENABLE_INSTALL_TOOL':
					if (unlink($enableInstallToolFile)) {
						unset($enableInstallToolFile);
					}
				break;
			}

				// Check if the Install Tool Password is still default: joh316
			if ($GLOBALS['TYPO3_CONF_VARS']['BE']['installToolPassword']==md5('joh316')) {
				$url = 'install/index.php?redirect_url=index.php'.urlencode('?TYPO3_INSTALL[type]=about');
				$warnings["install_password"] = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.install_password'),
					'<a href="'.$url.'">',
					'</a>');
			}

				// Check if there is still a default user 'admin' with password 'password' (MD5sum = 5f4dcc3b5aa765d61d8327deb882cf99)
			$where_clause = 'username='.$GLOBALS['TYPO3_DB']->fullQuoteStr('admin', 'be_users').' AND password='.$GLOBALS['TYPO3_DB']->fullQuoteStr('5f4dcc3b5aa765d61d8327deb882cf99', 'be_users').t3lib_BEfunc::deleteClause('be_users');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, username, password', 'be_users', $where_clause);
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$url = "alt_doc.php?returnUrl=index.php&edit[be_users][".$row['uid']."]=edit";
				$warnings["backend_admin"] = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.backend_admin'),
					'<a href="'.$url.'">',
					'</a>');

			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);

			if (@is_file($enableInstallToolFile)) {
				$url = t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT').'?adminWarning_cmd=remove_ENABLE_INSTALL_TOOL';
				$warnings['install_enabled'] = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.install_enabled'),
					'<span style="white-space:nowrap;">'.$enableInstallToolFile.'</span>');
				$warnings['install_enabled'].= ' <a href="'.$url.'">'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.install_enabled_cmd').'</a>';
			}

				// Check if the encryption key is empty
			if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] == '') {
				$url = 'install/index.php?redirect_url=index.php'.urlencode('?TYPO3_INSTALL[type]=config#set_encryptionKey');
				$warnings["install_encryption"] = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.install_encryption'),
					'<a href="'.$url.'">',
					'</a>');
			}

				// Check if fileDenyPattern was changed which is dangerous on Apache
			if ($GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'] != FILE_DENY_PATTERN_DEFAULT ) {
				$warnings['file_deny_pattern'] = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.file_deny_pattern'),
					'<br /><pre>'.htmlspecialchars(FILE_DENY_PATTERN_DEFAULT).'</pre><br />');
			}

				// Check if fileDenyPattern allows to upload .htaccess files which is dangerous on Apache
			if ($GLOBALS['TYPO3_CONF_VARS']['BE']['fileDenyPattern'] != FILE_DENY_PATTERN_DEFAULT && t3lib_div::verifyFilenameAgainstDenyPattern('.htaccess')) {
				$warnings['file_deny_htaccess'] = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.file_deny_htaccess');
			}

				// Check if there are still updates to perform
			if (!t3lib_div::compat_version(TYPO3_branch)) {
				$url = 'install/index.php?redirect_url=index.php'.urlencode('?TYPO3_INSTALL[type]=update');
				$warnings["install_update"] = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.install_update'),
					'<a href="'.$url.'">',
					'</a>');
			}

				// Check if sys_refindex is empty
			list($count) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(*) as rcount', 'sys_refindex', '1=1');
			if (!$count['rcount']) {
				$url = 'sysext/lowlevel/dbint/index.php?&id=0&SET[function]=refindex';
				$warnings["backend_reference"] = sprintf(
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.backend_reference'),
					'<a href="'.$url.'">',
					'</a>');
			}

			// Hook for additional warnings
			if (is_array ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['displayWarningMessages'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['displayWarningMessages'] as $classRef) {
					$hookObj = &t3lib_div::getUserObj($classRef);
					if (method_exists($hookObj, 'displayWarningMessages_postProcess')) {
						$hookObj->displayWarningMessages_postProcess($warnings);
					}
				}
			}

			if (count($warnings)) {
				$style = ' style="margin-bottom:10px;"';
				$content = '<table border="0" cellpadding="0" cellspacing="0" class="warningbox"><tr><td>' .
					$GLOBALS['TBE_TEMPLATE']->icons(3) . '<span class="warningboxheader">' .
					$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.xml:warning.header') .
					'</span><br /><ul><li' . $style.'>' .
					implode('</li><li' . $style . '>', $warnings) . '</li></ul>' .
					'</td></tr></table>';

				unset($warnings);
				return $content;
			}
		}
		return '<p>&nbsp;</p>';
	}

	/**
	 * Returns "web" if the $path (absolute) is within the DOCUMENT ROOT - and thereby qualifies as a "web" folder.
	 * Usage: 4
	 *
	 * @param	string		Path to evaluate
	 * @return	boolean
	 */
	public static function getPathType_web_nonweb($path) {
		return t3lib_div::isFirstPartOfStr($path, t3lib_div::getIndpEnv('TYPO3_DOCUMENT_ROOT')) ? 'web' : '';
	}

	/**
	 * Creates ADMCMD parameters for the "viewpage" extension / "cms" frontend
	 * Usage: 1
	 *
	 * @param	array		Page record
	 * @return	string		Query-parameters
	 * @internal
	 */
	public static function ADMCMD_previewCmds($pageinfo) {
		if ($pageinfo['fe_group']>0) {
			$simUser = '&ADMCMD_simUser='.$pageinfo['fe_group'];
		}
		if ($pageinfo['starttime']>time()) {
			$simTime = '&ADMCMD_simTime='.$pageinfo['starttime'];
		}
		if ($pageinfo['endtime']<time() && $pageinfo['endtime']!=0) {
			$simTime = '&ADMCMD_simTime='.($pageinfo['endtime']-1);
		}
		return $simUser.$simTime;
	}

	/**
	 * Returns an array with key=>values based on input text $params
	 * $params is exploded by line-breaks and each line is supposed to be on the syntax [key] = [some value]
	 * These pairs will be parsed into an array an returned.
	 * Usage: 1
	 *
	 * @param	string		String of parameters on multiple lines to parse into key-value pairs (see function description)
	 * @return	array
	 */
	public static function processParams($params) {
		$paramArr = array();
		$lines = explode(chr(10), $params);
		while(list(,$val) = each($lines)) {
			$val = trim($val);
			if ($val) {
				$pair = explode('=', $val, 2);
				$paramArr[trim($pair[0])] = trim($pair[1]);
			}
		}
		return $paramArr;
	}

	/**
	 * Returns "list of backend modules". Most likely this will be obsolete soon / removed. Don't use.
	 * Usage: 3
	 *
	 * @param	array		Module names in array. Must be "addslashes()"ed
	 * @param	string		Perms clause for SQL query
	 * @param	string		Backpath
	 * @param	string		The URL/script to jump to (used in A tag)
	 * @return	array		Two keys, rows and list
	 * @internal
	 * @deprecated
	 * @obsolete
	 */
	public static function getListOfBackendModules($name, $perms_clause, $backPath = '', $script = 'index.php') {
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'doktype!=255 AND module IN (\''.implode('\',\'', $name).'\') AND'.$perms_clause.t3lib_BEfunc::deleteClause('pages'));
		if (!$GLOBALS['TYPO3_DB']->sql_num_rows($res))	return false;

		$out = '';
		$theRows = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$theRows[] = $row;
			$out.='<span class="nobr"><a href="'.htmlspecialchars($script.'?id='.$row['uid']).'">'.
					t3lib_iconWorks::getIconImage('pages', $row, $backPath, 'title="'.htmlspecialchars(t3lib_BEfunc::getRecordPath($row['uid'], $perms_clause, 20)).'" align="top"').
					htmlspecialchars($row['title']).
					'</a></span><br />';
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return array('rows'=>$theRows, 'list'=>$out);
	}

	/**
	 * Returns the name of the backend script relative to the TYPO3 main directory.
	 *
	 * @param	string		Name of the backend interface  (backend, backend_old, frontend) to look up the script name for. If no interface is given, the interface for the current backend user is used.
	 * @return	string		The name of the backend script relative to the TYPO3 main directory.
	 */
	public static function getBackendScript($interface = '') {
		if (!$interface) {
			$interface = $GLOBALS['BE_USER']->uc['interfaceSetup'];
		}

		switch ($interface) {
			case 'backend_old':
				$script = 'alt_main.php';
				break;
			case 'frontend':
				$script = '../.';
				break;
			case 'backend':
			default:
				$script = 'backend.php';
				break;
		}

		return $script;
	}
}
?>
