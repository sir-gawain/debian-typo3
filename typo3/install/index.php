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
 * Starter-script for install screen
 *
 * $Id: index.php 3439 2008-03-16 19:16:51Z flyguide $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */



// **************************************************************************
// Insert some security here, if you don't trust the Install Tool Password:
// **************************************************************************

error_reporting (E_ALL ^ E_NOTICE);
$PATH_thisScript = str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME'])));

	// Only allow Install Tool access if the file "typo3conf/ENABLE_INSTALL_TOOL" is found
$enableInstallToolFile = dirname(dirname(dirname($PATH_thisScript))).'/typo3conf/ENABLE_INSTALL_TOOL';

	// Change 1==2 to 1==1 if you want to lock the Install Tool regardless of the file ENABLE_INSTALL_TOOL
if (1==2 || ($_SERVER['REMOTE_ADDR']!='127.0.0.1' && !@is_file($enableInstallToolFile)))	{
	die('The Install Tool is locked.<br /><br /><strong>Fix:</strong> Create a file typo3conf/ENABLE_INSTALL_TOOL<br />This file may simply be empty.<br /><br />For security reasons, it is highly recommended to rename<br />or delete the file after the operation is finished.');
}



// *****************************************************************************
// Defining constants necessary for the install-script to invoke the installer
// *****************************************************************************
define('TYPO3_MOD_PATH', 'install/');
$BACK_PATH='../';

	// Defining this variable and setting it non-false will invoke the install-screen called from init.php
define('TYPO3_enterInstallScript', '1');
require ('../init.php');
?>
