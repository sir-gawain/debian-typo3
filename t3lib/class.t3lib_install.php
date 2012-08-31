<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 1999-2011 Kasper Skårhøj (kasperYYYY@typo3.com)
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
 * Class to setup values in localconf.php and verify the TYPO3 DB tables/fields
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */
/**
 * Class to setup values in localconf.php and verify the TYPO3 DB tables/fields
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_install {

	// External, Static
	// Set to string which identifies the script using this class.
	/**
	 * @todo Define visibility
	 */
	public $updateIdentity = '';

	// Prefix for checkbox fields when updating database.
	/**
	 * @todo Define visibility
	 */
	public $dbUpdateCheckboxPrefix = 'TYPO3_INSTALL[database_update]';

	// If this is set, modifications to localconf.php is done by adding new lines to the array only. If unset, existing values are recognized and changed.
	/**
	 * @todo Define visibility
	 */
	public $localconf_addLinesOnly = 0;

	// If set and addLinesOnly is disabled, lines will be change only if they are after this token (on a single line!) in the file
	protected $localconf_startEditPointToken = '## INSTALL SCRIPT EDIT POINT TOKEN - all lines after this points may be changed by the install script!';

	protected $localconf_endEditPointToken = '## INSTALL SCRIPT EDIT END POINT TOKEN - all lines before this points may be changed by the install script!';

	// If TRUE, this class will allow the user to update the localconf.php file. Is set TRUE in the init.php file.
	/**
	 * @todo Define visibility
	 */
	public $allowUpdateLocalConf = 0;

	// Backpath (used for icons etc.)
	/**
	 * @todo Define visibility
	 */
	public $backPath = '../';

	// Internal, dynamic:
	// Used to indicate that a value is change in the line-array of localconf and that it should be written.
	/**
	 * @todo Define visibility
	 */
	public $setLocalconf = 0;

	// Used to set (error)messages from the executing functions like mail-sending, writing Localconf and such
	/**
	 * @todo Define visibility
	 */
	public $messages = array();

	// Updated with line in localconf.php file that was changed.
	/**
	 * @todo Define visibility
	 */
	public $touchedLine = 0;

	/**
	 * @var \TYPO3\CMS\Install\Sql\SchemaMigrator Instance of SQL handler
	 */
	protected $sqlHandler = NULL;

	/**
	 * Constructor function
	 */
	public function __construct() {
		$this->sqlHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Install\\Sql\\SchemaMigrator');
	}

	/**************************************
	 *
	 * Writing to localconf.php
	 ***************************************/
	/**
	 * This functions takes an array with lines from localconf.php, finds a variable and inserts the new value.
	 *
	 * @param array $line_array The localconf.php file exploded into an array by linebreaks. (see writeToLocalconf_control())
	 * @param string $variable The variable name to find and substitute. This string must match the first part of a trimmed line in the line-array. Matching is done backwards so the last appearing line will be substituted.
	 * @param string $value Is the value to be insert for the variable
	 * @param boolean $quoteValue Whether the given value should be quoted before being written
	 * @return void
	 * @see writeToLocalconf_control()
	 */
	public function setValueInLocalconfFile(&$line_array, $variable, $value, $quoteValue = TRUE) {
		if (!$this->checkForBadString($value)) {
			return 0;
		}
		// Initialize
		$found = 0;
		$this->touchedLine = '';
		$inArray = in_array($this->localconf_startEditPointToken, $line_array);
		// Flag is set if the token should be set but is not yet.
		$tokenSet = $this->localconf_startEditPointToken && !$inArray;
		$stopAtToken = $this->localconf_startEditPointToken && $inArray;
		$hasEndToken = in_array($this->localconf_endEditPointToken, $line_array);
		$respectEndToken = $hasEndToken;
		$comment = (' Modified or inserted by ' . $this->updateIdentity) . '.';
		$replace = array('["', '"]');
		$search = array('[\'', '\']');
		$varDoubleQuotes = str_replace($search, $replace, $variable);
		// Search for variable name
		if (!$this->localconf_addLinesOnly && !$tokenSet) {
			$line_array = array_reverse($line_array);
			foreach ($line_array as $k => $v) {
				$v2 = trim($v);
				if ($respectEndToken) {
					if (strcmp($v2, $this->localconf_endEditPointToken)) {
						$respectEndToken = FALSE;
					} else {
						continue;
					}
				}
				if ($stopAtToken && !strcmp($v2, $this->localconf_startEditPointToken)) {
					break;
				}
				// If stopAtToken and token found, break out of the loop..
				if (!strcmp(substr($v2, 0, strlen(($variable . ' '))), ($variable . ' '))) {
					$mainparts = explode($variable, $v, 2);
					// Should ALWAYS be.
					if (count($mainparts) == 2) {
						$subparts = explode('//', $mainparts[1], 2);
						if ($quoteValue) {
							$value = ('\'' . $this->slashValueForSingleDashes($value)) . '\'';
						}
						$line_array[$k] = (((($mainparts[0] . $variable) . ' = ') . $value) . ';	') . (('//' . $comment) . str_replace($comment, '', $subparts[1]));
						$this->touchedLine = (count($line_array) - $k) - 1;
						$found = 1;
						break;
					}
				} elseif (!strcmp(substr($v2, 0, strlen(($varDoubleQuotes . ' '))), ($varDoubleQuotes . ' '))) {
					// Due to a bug in the update wizard (fixed in TYPO3 4.1.7) it is possible
					// that $TYPO3_CONF_VARS['SYS']['compat_version'] was enclosed by "" (double
					// quotes) instead of the expected '' (single quotes) when is was written to
					// localconf.php. The following code was added to make sure that values with
					// double quotes are updated, too.
					$mainparts = explode($varDoubleQuotes, $v, 2);
					// Should ALWAYS be.
					if (count($mainparts) == 2) {
						$subparts = explode('//', $mainparts[1], 2);
						if ($quoteValue) {
							$value = ('\'' . $this->slashValueForSingleDashes($value)) . '\'';
						}
						$line_array[$k] = (((($mainparts[0] . $variable) . ' = ') . $value) . ';	') . (('//' . $comment) . str_replace($comment, '', $subparts[1]));
						$this->touchedLine = (count($line_array) - $k) - 1;
						$found = 1;
						break;
					}
				}
			}
			$line_array = array_reverse($line_array);
		}
		if (!$found) {
			if ($tokenSet) {
				$line_array[] = $this->localconf_startEditPointToken;
				$line_array[] = '';
			}
			if ($quoteValue) {
				$value = ('\'' . $this->slashValueForSingleDashes($value)) . '\'';
			}
			$line_array[] = ((($variable . ' = ') . $value) . ';	// ') . $comment;
			if (!$hasEndToken) {
				$line_array[] = '';
				$line_array[] = $this->localconf_endEditPointToken;
			}
			$this->touchedLine = -1;
		}
		if ($variable == '$typo_db_password') {
			$this->messages[] = 'Updated ' . $variable;
		} else {
			$this->messages[] = ($variable . ' = ') . htmlspecialchars($value);
		}
		$this->setLocalconf = 1;
	}

	/**
	 * Takes an array with lines from localconf.php, finds a variable and inserts the new array value.
	 *
	 * @param array $lines the localconf.php file exploded into an array by line breaks. {@see writeToLocalconf_control()}
	 * @param string $variable the variable name to find and substitute. This string must match the first part of a trimmed line in the line-array. Matching is done backwards so the last appearing line will be substituted.
	 * @param array $value value to be assigned to the variable
	 * @return void
	 * @see writeToLocalconf_control()
	 */
	public function setArrayValueInLocalconfFile(array &$lines, $variable, array $value) {
		$commentKey = '## ';
		$inArray = in_array($commentKey . $this->localconf_startEditPointToken, $lines);
		$tokenSet = $this->localconf_startEditPointToken && !$inArray;
		// Flag is set if the token should be set but is not yet
		$stopAtToken = $this->localconf_startEditPointToken && $inArray;
		$comment = ('Modified or inserted by ' . $this->updateIdentity) . '.';
		$format = '%s = %s;	// ' . $comment;
		$insertPos = count($lines);
		$startPos = 0;
		if (!($this->localconf_addLinesOnly || $tokenSet)) {
			for ($i = count($lines) - 1; $i > 0; $i--) {
				$line = trim($lines[$i]);
				if ($stopAtToken && \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($line, $this->localconf_startEditPointToken)) {
					break;
				}
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($line, '?>')) {
					$insertPos = $i;
				}
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($line, $variable)) {
					$startPos = $i;
					break;
				}
			}
		}
		if ($startPos) {
			$this->touchedLine = $startPos;
			$endPos = $startPos;
			for ($i = $startPos; $i < count($lines); $i++) {
				$line = trim($lines[$i]);
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($line, ');')) {
					$endPos = $i;
					break;
				}
			}
			$startLines = array_slice($lines, 0, $startPos);
			$endLines = array_slice($lines, $endPos + 1);
			$lines = $startLines;
			$definition = $this->array_export($value);
			$lines[] = sprintf($format, $variable, $definition);
			foreach ($endLines as $line) {
				$lines[] = $line;
			}
		} else {
			$lines[$insertPos] = sprintf($format, $variable, $this->array_export($value));
			$lines[] = '?>';
			$this->touchedLine = -1;
		}
	}

	/**
	 * Returns a parsable string representation of an array variable. This methods enhances
	 * standard method var_export from PHP to take TYPO3's CGL into account.
	 *
	 * @param array $variable
	 * @return string
	 */
	protected function array_export(array $variable) {
		$lines = explode('
', var_export($variable, TRUE));
		$out = 'array(';
		for ($i = 1; $i < count($lines); $i++) {
			$out .= '
';
			// Make the space-indented declaration tab-indented instead
			while (substr($lines[$i], 0, 2) === '  ') {
				$out .= '	';
				$lines[$i] = substr($lines[$i], 2);
			}
			$out .= $lines[$i];
			// Array declaration should be next to the assignment and no space between
			// "array" and its opening parenthesis should exist
			if (preg_match('/\\s=>\\s$/', $lines[$i])) {
				$out .= preg_replace('/^\\s*array \\(/', 'array(', $lines[$i + 1]);
				$i++;
			}
		}
		return $out;
	}

	/**
	 * Writes or returns lines from localconf.php
	 *
	 * @param mixed $inlines Array of lines to write back to localconf.php. Possibly
	 * @param string $absFullPath Absolute path of alternative file to use (Notice: this path is not validated in terms of being inside 'TYPO3 space')
	 * @return mixed If $inlines is not an array it will return an array with the lines from localconf.php. Otherwise it will return a status string, either "continue" (updated) or "nochange" (not updated)
	 * @see setValueInLocalconfFile()
	 * @todo Define visibility
	 */
	public function writeToLocalconf_control($inlines = '', $absFullPath = '') {
		$tmpExt = '.TMP.php';
		$writeToLocalconf_dat = array();
		$writeToLocalconf_dat['file'] = $absFullPath ? $absFullPath : PATH_typo3conf . 'localconf.php';
		$writeToLocalconf_dat['tmpfile'] = $writeToLocalconf_dat['file'] . $tmpExt;
		// Checking write state of localconf.php
		if (!$this->allowUpdateLocalConf) {
			throw new RuntimeException('TYPO3 Fatal Error: ->allowUpdateLocalConf flag in the install object is not set and therefore "localconf.php" cannot be altered.', 1270853915);
		}
		if (!@is_writable($writeToLocalconf_dat['file'])) {
			throw new RuntimeException(('TYPO3 Fatal Error: ' . $writeToLocalconf_dat['file']) . ' is not writable!', 1270853916);
		}
		// Splitting localconf.php file into lines
		$lines = explode(LF, str_replace(CR, '', trim(\TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($writeToLocalconf_dat['file']))));
		$writeToLocalconf_dat['endLine'] = array_pop($lines);
		// Getting "? >" ending.
		// Checking if "updated" line was set by this tool - if so remove old line.
		$updatedLine = array_pop($lines);
		$writeToLocalconf_dat['updatedText'] = ('// Updated by ' . $this->updateIdentity) . ' ';
		if (!strstr($updatedLine, $writeToLocalconf_dat['updatedText'])) {
			array_push($lines, $updatedLine);
		}
		// Setting a line and write
		if (is_array($inlines)) {
			// Setting configuration
			$updatedLine = $writeToLocalconf_dat['updatedText'] . date(($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' H:i:s'));
			array_push($inlines, $updatedLine);
			array_push($inlines, $writeToLocalconf_dat['endLine']);
			if ($this->setLocalconf) {
				$success = $this->writeToLocalconf($inlines, $absFullPath);
				if ($success) {
					return 'continue';
				} else {
					return 'nochange';
				}
			} else {
				return 'nochange';
			}
		} else {
			// Return lines found in localconf.php
			return $lines;
		}
	}

	/**
	 * Writes lines to localconf.php.
	 *
	 * @param array $lines Array of lines to write back to localconf.php
	 * @param string $absFullPath Absolute path of alternative file to use (Notice: this path is not validated in terms of being inside 'TYPO3 space')
	 * @return boolean TRUE if method succeeded, otherwise FALSE
	 */
	public function writeToLocalconf(array $lines, $absFullPath = '') {
		$tmpExt = '.TMP.php';
		$writeToLocalconf_dat = array();
		$writeToLocalconf_dat['file'] = $absFullPath ? $absFullPath : PATH_typo3conf . 'localconf.php';
		$writeToLocalconf_dat['tmpfile'] = $writeToLocalconf_dat['file'] . $tmpExt;
		// Checking write state of localconf.php:
		if (!$this->allowUpdateLocalConf) {
			throw new RuntimeException('TYPO3 Fatal Error: ->allowUpdateLocalConf flag in the install object is not set and therefore "localconf.php" cannot be altered.', 1270853915);
		}
		if (!@is_writable($writeToLocalconf_dat['file'])) {
			throw new RuntimeException(('TYPO3 Fatal Error: ' . $writeToLocalconf_dat['file']) . ' is not writable!', 1270853916);
		}
		$writeToLocalconf_dat['endLine'] = array_pop($lines);
		// Getting "? >" ending.
		if (!strstr(('?' . '>'), $writeToLocalconf_dat['endLine'])) {
			$lines[] = $writeToLocalconf_dat['endLine'];
			$writeToLocalconf_dat['endLine'] = '?' . '>';
		}
		// Checking if "updated" line was set by this tool - if so remove old line.
		$updatedLine = array_pop($lines);
		$writeToLocalconf_dat['updatedText'] = ('// Updated by ' . $this->updateIdentity) . ' ';
		if (!strstr($updatedLine, $writeToLocalconf_dat['updatedText'])) {
			$lines[] = $updatedLine;
		}
		$updatedLine = $writeToLocalconf_dat['updatedText'] . date(($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' H:i:s'));
		$lines[] = $updatedLine;
		$lines[] = $writeToLocalconf_dat['endLine'];
		$success = FALSE;
		if (!\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($writeToLocalconf_dat['tmpfile'], implode(LF, $lines))) {
			$msg = ('typo3conf/localconf.php' . $tmpExt) . ' could not be written - maybe a write access problem?';
		} elseif (strcmp(\TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($writeToLocalconf_dat['tmpfile']), implode(LF, $lines))) {
			@unlink($writeToLocalconf_dat['tmpfile']);
			$msg = ('typo3conf/localconf.php' . $tmpExt) . ' was NOT written properly (written content didn\'t match file content) - maybe a disk space problem?';
		} elseif (!@copy($writeToLocalconf_dat['tmpfile'], $writeToLocalconf_dat['file'])) {
			$msg = ('typo3conf/localconf.php could not be replaced by typo3conf/localconf.php' . $tmpExt) . ' - maybe a write access problem?';
		} else {
			@unlink($writeToLocalconf_dat['tmpfile']);
			$success = TRUE;
			$msg = 'Configuration written to typo3conf/localconf.php';
		}
		$this->messages[] = $msg;
		if (!$success) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::sysLog($msg, 'Core', \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_ERROR);
		}
		return $success;
	}

	/**
	 * Checking for linebreaks in the string
	 *
	 * @param string $string String to test
	 * @return boolean Returns TRUE if string is OK
	 * @see setValueInLocalconfFile()
	 * @todo Define visibility
	 */
	public function checkForBadString($string) {
		return preg_match((('/[' . LF) . CR) . ']/', $string) ? FALSE : TRUE;
	}

	/**
	 * Replaces ' with \' and \ with \\
	 *
	 * @param string $value Input value
	 * @return string Output value
	 * @see setValueInLocalconfFile()
	 * @todo Define visibility
	 */
	public function slashValueForSingleDashes($value) {
		$value = str_replace('\'.LF.\'', '###INSTALL_TOOL_LINEBREAK###', $value);
		$value = str_replace('\'', '\\\'', str_replace('\\', '\\\\', $value));
		$value = str_replace('###INSTALL_TOOL_LINEBREAK###', '\'.LF.\'', $value);
		return $value;
	}

	/**
	 * Creates a table which checkboxes for updating database.
	 *
	 * @param array $arr Array of statements (key / value pairs where key is used for the checkboxes)
	 * @param string $label Label for the table.
	 * @param boolean $checked If set, then checkboxes are set by default.
	 * @param boolean $iconDis If set, then icons are shown.
	 * @param array $currentValue Array of "current values" for each key/value pair in $arr. Shown if given.
	 * @param boolean $cVfullMsg If set, will show the prefix "Current value" if $currentValue is given.
	 * @return string HTML table with checkboxes for update. Must be wrapped in a form.
	 * @todo Define visibility
	 */
	public function generateUpdateDatabaseForm_checkboxes($arr, $label, $checked = TRUE, $iconDis = FALSE, $currentValue = array(), $cVfullMsg = FALSE) {
		$out = array();
		if (is_array($arr)) {
			$tableId = uniqid('table');
			if (count($arr) > 1) {
				$out[] = ((((((('
					<tr class="update-db-fields-batch">
						<td valign="top">
							<input type="checkbox" id="' . $tableId) . '-checkbox"') . ($checked ? ' checked="checked"' : '')) . '
							onclick="$(\'') . $tableId) . '\').select(\'input[type=checkbox]\').invoke(\'setValue\', $(this).checked);" />
						</td>
						<td nowrap="nowrap"><label for="') . $tableId) . '-checkbox" style="cursor:pointer"><strong>select/deselect all</strong></label></td>
					</tr>';
			}
			foreach ($arr as $key => $string) {
				$ico = '';
				$warnings = array();
				if ($iconDis) {
					if (preg_match('/^TRUNCATE/i', $string)) {
						$ico .= ('<img src="' . $this->backPath) . 'gfx/icon_warning.gif" width="18" height="16" align="top" alt="" /><strong> </strong>';
						$warnings['clear_table_info'] = 'Clearing the table is sometimes neccessary when adding new keys. In case of cache_* tables this should not hurt at all. However, use it with care.';
					} elseif (stristr($string, ' user_')) {
						$ico .= ('<img src="' . $this->backPath) . 'gfx/icon_warning.gif" width="18" height="16" align="top" alt="" /><strong>(USER) </strong>';
					} elseif (stristr($string, ' app_')) {
						$ico .= ('<img src="' . $this->backPath) . 'gfx/icon_warning.gif" width="18" height="16" align="top" alt="" /><strong>(APP) </strong>';
					} elseif (stristr($string, ' ttx_') || stristr($string, ' tx_')) {
						$ico .= ('<img src="' . $this->backPath) . 'gfx/icon_warning.gif" width="18" height="16" align="top" alt="" /><strong>(EXT) </strong>';
					}
				}
				$out[] = ((((((((((('
					<tr>
						<td valign="top"><input type="checkbox" id="db-' . $key) . '" name="') . $this->dbUpdateCheckboxPrefix) . '[') . $key) . ']" value="1"') . ($checked ? ' checked="checked"' : '')) . ' /></td>
						<td nowrap="nowrap"><label for="db-') . $key) . '">') . nl2br(($ico . htmlspecialchars($string)))) . '</label></td>
					</tr>';
				if (isset($currentValue[$key])) {
					$out[] = ('
					<tr>
						<td valign="top"></td>
						<td nowrap="nowrap" style="color:#666666;">' . nl2br(((((!$cVfullMsg ? 'Current value: ' : '') . '<em>') . $currentValue[$key]) . '</em>'))) . '</td>
					</tr>';
				}
			}
			if (count($warnings)) {
				$out[] = ('
					<tr>
						<td valign="top"></td>
						<td style="color:#666666;"><em>' . implode('<br />', $warnings)) . '</em></td>
					</tr>';
			}
			// Compile rows:
			$content = ((((('
				<!-- Update database fields / tables -->
				<h3>' . $label) . '</h3>
				<table border="0" cellpadding="2" cellspacing="2" id="') . $tableId) . '" class="update-db-fields">') . implode('', $out)) . '
				</table>';
		}
		return $content;
	}

}

?>