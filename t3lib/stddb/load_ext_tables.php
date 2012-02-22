<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

/**
 * Loading the ext_tables.php files of the installed extensions when caching to "temp_CACHED_" files is NOT enabled.
 *
 * $Id: load_ext_tables.php 2663 2007-11-05 09:22:23Z ingmars $
 * Revised for TYPO3 3.6 July/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @see tslib_fe::includeTCA(), typo3/init.php
 */
$temp_TYPO3_LOADED_EXT = $GLOBALS['TYPO3_LOADED_EXT'];
reset($temp_TYPO3_LOADED_EXT);
while(list($_EXTKEY,$temp_lEDat)=each($temp_TYPO3_LOADED_EXT))	{
	if (is_array($temp_lEDat) && $temp_lEDat['ext_tables.php'])	{
		$_EXTCONF = $TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY];
		require($temp_lEDat['ext_tables.php']);
	}
}
?>