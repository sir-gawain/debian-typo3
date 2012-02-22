<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2009 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Contains translation tools
 *
 * $Id: class.t3lib_transl8tools.php 6190 2009-10-20 17:44:02Z ohader $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   67: class t3lib_transl8tools
 *   74:     function getSystemLanguages($page_id=0,$backPath='')
 *  132:     function translationInfo($table,$uid,$sys_language_uid=0)
 *  187:     function getTranslationTable($table)
 *  197:     function isTranslationInOwnTable($table)
 *  209:     function foreignTranslationTable($table)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */










/**
 * Contains translation tools
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_transl8tools	{

	/**
	 * Returns array of system languages
	 * @param	integer		page id (only used to get TSconfig configuration setting flag and label for default language)
	 * @param	string		Backpath for flags
	 * @return	array
	 */
	function getSystemLanguages($page_id=0,$backPath='')	{
		global $TCA,$LANG;

			// Icons and language titles:
		t3lib_div::loadTCA('sys_language');
		$flagAbsPath = t3lib_div::getFileAbsFileName($TCA['sys_language']['columns']['flag']['config']['fileFolder']);
		$flagIconPath = $backPath.'../'.substr($flagAbsPath, strlen(PATH_site));

		$modSharedTSconfig = t3lib_BEfunc::getModTSconfig($page_id, 'mod.SHARED');
		$languageIconTitles = array();

			// Set default:
		$languageIconTitles[0] = array(
			'uid' => 0,
			'title' => strlen ($modSharedTSconfig['properties']['defaultLanguageLabel']) ? $modSharedTSconfig['properties']['defaultLanguageLabel'].' ('.$LANG->getLL('defaultLanguage').')' : $LANG->getLL('defaultLanguage'),
			'ISOcode' => 'DEF',
			'flagIcon' => strlen($modSharedTSconfig['properties']['defaultLanguageFlag']) && @is_file($flagAbsPath.$modSharedTSconfig['properties']['defaultLanguageFlag']) ? $flagIconPath.$modSharedTSconfig['properties']['defaultLanguageFlag'] : null,
		);

			// Set "All" language:
		$languageIconTitles[-1]=array(
			'uid' => -1,
			'title' => $LANG->getLL('multipleLanguages'),
			'ISOcode' => 'DEF',
			'flagIcon' => $flagIconPath.'multi-language.gif',
		);

			// Find all system languages:
		$sys_languages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'*',
			'sys_language',
			''
		);
		foreach($sys_languages as $row)		{
			$languageIconTitles[$row['uid']] = $row;

			if ($row['static_lang_isocode'] && t3lib_extMgm::isLoaded('static_info_tables'))	{
				$staticLangRow = t3lib_BEfunc::getRecord('static_languages',$row['static_lang_isocode'],'lg_iso_2');
				if ($staticLangRow['lg_iso_2']) {
					$languageIconTitles[$row['uid']]['ISOcode'] = $staticLangRow['lg_iso_2'];
				}
			}
			if (strlen ($row['flag'])) {
				$languageIconTitles[$row['uid']]['flagIcon'] = @is_file($flagAbsPath.$row['flag']) ? $flagIconPath.$row['flag'] : '';
			}
		}

		return $languageIconTitles;
	}

	/**
	 * Information about translation for an element
	 * Will overlay workspace version of record too!
	 *
	 * @param	string		Table name
	 * @param	integer		Record uid
	 * @param	integer		Language uid. If zero, then all languages are selected.
	 * @param	array		The record to be translated
	 * @param	array		select fields for the query which fetches the translations of the current record
	 * @return	array		Array with information. Errors will return string with message.
	 */
	function translationInfo($table, $uid, $sys_language_uid = 0, $row = NULL, $selFieldList = '') {
		global $TCA;

		if ($TCA[$table] && $uid)	{
			t3lib_div::loadTCA($table);

			if ($row === NULL) {
				$row = t3lib_BEfunc::getRecordWSOL($table, $uid);
			}

			if (is_array($row))	{
				$trTable = $this->getTranslationTable($table);
				if ($trTable)	{
					if ($trTable!==$table || $row[$TCA[$table]['ctrl']['languageField']] <= 0)	{
						if ($trTable!==$table || $row[$TCA[$table]['ctrl']['transOrigPointerField']] == 0)	{

								// Look for translations of this record, index by language field value:
							$translationsTemp = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
								($selFieldList ? $selFieldList : 'uid,'.$TCA[$trTable]['ctrl']['languageField']),
								$trTable,
								$TCA[$trTable]['ctrl']['transOrigPointerField'] . '=' . intval($uid) .
									' AND pid=' . intval($table === 'pages' ? $row['uid'] : $row['pid']).	// Making exception for pages of course where the translations will always be ON the page, not on the level above...
									' AND '.$TCA[$trTable]['ctrl']['languageField'].(!$sys_language_uid ? '>0' : '='.intval($sys_language_uid)).
									t3lib_BEfunc::deleteClause($trTable).
									t3lib_BEfunc::versioningPlaceholderClause($trTable)
							);

							$translations = array();
							$translations_errors = array();
							foreach($translationsTemp as $r)	{
								if (!isset($translations[$r[$TCA[$trTable]['ctrl']['languageField']]]))	{
									$translations[$r[$TCA[$trTable]['ctrl']['languageField']]] = $r;
								} else {
									$translations_errors[$r[$TCA[$trTable]['ctrl']['languageField']]][] = $r;
								}
							}

							return array(
								'table' => $table,
								'uid' => $uid,
								'CType' => $row['CType'],
								'sys_language_uid' => $row[$TCA[$table]['ctrl']['languageField']],
								'translation_table' => $trTable,
								'translations' => $translations,
								'excessive_translations' => $translations_errors
							);
						} else return 'Record "'.$table.'_'.$uid.'" seems to be a translation already (has a relation to record "'.$row[$TCA[$table]['ctrl']['transOrigPointerField']].'")';
					} else return 'Record "'.$table.'_'.$uid.'" seems to be a translation already (has a language value "'.$row[$TCA[$table]['ctrl']['languageField']].'", relation to record "'.$row[$TCA[$table]['ctrl']['transOrigPointerField']].'")';
				} else return 'Translation is not supported for this table!';
			} else return 'Record "'.$table.'_'.$uid.'" was not found';
		} else return 'No table "'.$table.'" or no UID value';
	}

	/**
	 * Returns the table in which translations for input table is found.
	 *
	 * @param	[type]		$table: ...
	 * @return	[type]		...
	 */
	function getTranslationTable($table) {
		return $this->isTranslationInOwnTable($table) ? $table : $this->foreignTranslationTable($table);
	}

	/**
	 * Returns true, if the input table has localization enabled and done so with records from the same table
	 *
	 * @param	[type]		$table: ...
	 * @return	[type]		...
	 */
	function isTranslationInOwnTable($table) {
		global $TCA;

		return $TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'] && !$TCA[$table]['ctrl']['transOrigPointerTable'];
	}

	/**
	 * Returns foreign translation table, if any
	 *
	 * @param	[type]		$table: ...
	 * @return	[type]		...
	 */
	function foreignTranslationTable($table) {
		global $TCA;

		$trTable = $TCA[$table]['ctrl']['transForeignTable'];

		if ($trTable && $TCA[$trTable] && $TCA[$trTable]['ctrl']['languageField'] && $TCA[$trTable]['ctrl']['transOrigPointerField'] && $TCA[$trTable]['ctrl']['transOrigPointerTable']===$table)	{
			return $trTable;
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_transl8tools.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_transl8tools.php']);
}
?>