<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Module Dispatch script
 *
 * $Id: mod.php 2663 2007-11-05 09:22:23Z ingmars $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 */

unset($MCONF);
require('init.php');
require('template.php');

	// Find module path:
$temp_M = (string)t3lib_div::_GET('M');
if ($temp_path = $TBE_MODULES['_PATHS'][$temp_M])	{
	$MCONF['_'] = 'mod.php?M='.rawurlencode($temp_M);
	require($temp_path.'conf.php');
	$BACK_PATH='';
	require($temp_path.'index.php');
} else {
	#debug($TBE_MODULES);
	die('Value "'.htmlspecialchars($temp_M).'" for "M" was not found as a module');
}

?>
