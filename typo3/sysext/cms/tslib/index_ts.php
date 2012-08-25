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
 * This is the MAIN DOCUMENT of the TypoScript driven standard front-end (from the "cms" extension)
 * Basically put this is the "index.php" script which all requests for TYPO3 delivered pages goes to in the frontend (the website)
 * The script configures constants, includes libraries and does a little logic here and there in order to instantiate the right classes to create the webpage.
 * All the real data processing goes on in the "tslib/" classes which this script will include and use as needed.
 *
 * Revised for TYPO3 3.6 June/2003 by Kasper Skårhøj
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tslib
 */

	// Constants defined
define('TYPO3_MODE', 'FE');

	// Prevent any output until AJAX/compression is initialized to stop
	// AJAX/compression data corruption
ob_start();

Typo3_Bootstrap::getInstance()
	->registerExtDirectComponents()
	->populateLocalConfiguration()
	->initializeCachingFramework()
	->registerAutoloader()
	->checkUtf8DatabaseSettingsOrDie()
	->transferDeprecatedCurlSettings()
	->setCacheHashOptions()
	->enforceCorrectProxyAuthScheme()
	->setDefaultTimezone()
	->initializeL10nLocales()
	->configureImageProcessingOptions()
	->convertPageNotFoundHandlingToBoolean()
	->registerGlobalDebugFunctions()
	->registerSwiftMailer()
	->configureExceptionHandling()
	->setMemoryLimit()
	->defineTypo3RequestTypes()
	->populateTypo3LoadedExtGlobal(TRUE)
	->loadAdditionalConfigurationFromExtensions(TRUE)
	->deprecationLogForOldExtCacheSetting()
	->initializeExceptionHandling()
	->requireAdditionalExtensionFiles()
	->setFinalCachingFrameworkCacheConfiguration()
	->defineLoggingAndExceptionConstants()
	->unsetReservedGlobalVariables();

if (!t3lib_extMgm::isLoaded('cms')) {
	die('<strong>Error:</strong> The main frontend extension "cms" was not loaded. Enable it in the extension manager in the backend.');
}

	// Timetracking started
if ($_COOKIE[t3lib_beUserAuth::getCookieName()]) {
	require_once(PATH_t3lib . 'class.t3lib_timetrack.php');
	$TT = new t3lib_timeTrack();
} else {
	require_once(PATH_t3lib . 'class.t3lib_timetracknull.php');
	$TT = new t3lib_timeTrackNull();
}
$TT->start();

Typo3_Bootstrap::getInstance()->initializeTypo3DbGlobal(FALSE);

// Hook to preprocess the current request:
if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'])) {
	foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest'] as $hookFunction) {
		$hookParameters = array();
		t3lib_div::callUserFunction($hookFunction, $hookParameters, $hookParameters);
	}
	unset($hookFunction);
	unset($hookParameters);
}

	// Look for extension ID which will launch alternative output engine
if ($temp_extId = t3lib_div::_GP('eID')) {
	if ($classPath = t3lib_div::getFileAbsFileName($TYPO3_CONF_VARS['FE']['eID_include'][$temp_extId])) {
			// Remove any output produced until now
		ob_clean();

		require($classPath);
	}
	exit;
}

	// Create $TSFE object (TSFE = TypoScript Front End)
	// Connecting to database
/** @var $TSFE tslib_fe */
$TSFE = t3lib_div::makeInstance('tslib_fe',
	$TYPO3_CONF_VARS,
	t3lib_div::_GP('id'),
	t3lib_div::_GP('type'),
	t3lib_div::_GP('no_cache'),
	t3lib_div::_GP('cHash'),
	t3lib_div::_GP('jumpurl'),
	t3lib_div::_GP('MP'),
	t3lib_div::_GP('RDCT')
);


if ($TYPO3_CONF_VARS['FE']['pageUnavailable_force'] &&
	!t3lib_div::cmpIP(t3lib_div::getIndpEnv('REMOTE_ADDR'), $TYPO3_CONF_VARS['SYS']['devIPmask'])) {
	$TSFE->pageUnavailableAndExit('This page is temporarily unavailable.');
}

$TSFE->connectToDB();
$TSFE->sendRedirect();

	// Output compression
	// Remove any output produced until now
ob_clean();
if ($TYPO3_CONF_VARS['FE']['compressionLevel'] && extension_loaded('zlib')) {
	if (t3lib_utility_Math::canBeInterpretedAsInteger($TYPO3_CONF_VARS['FE']['compressionLevel'])) {
			// Prevent errors if ini_set() is unavailable (safe mode)
		@ini_set('zlib.output_compression_level', $TYPO3_CONF_VARS['FE']['compressionLevel']);
	}
	ob_start(array(t3lib_div::makeInstance('tslib_fecompression'), 'compressionOutputHandler'));
}

	// FE_USER
$TT->push('Front End user initialized', '');
/** @var $TSFE tslib_fe */
$TSFE->initFEuser();
$TT->pull();

	// BE_USER
/** @var $BE_USER t3lib_tsfeBeUserAuth */
$BE_USER = $TSFE->initializeBackendUser();

	// Process the ID, type and other parameters
	// After this point we have an array, $page in TSFE, which is the page-record of the current page, $id
$TT->push('Process ID', '');
	// Initialize admin panel since simulation settings are required here:
if ($TSFE->isBackendUserLoggedIn()) {
	$BE_USER->initializeAdminPanel();
}

$TSFE->checkAlternativeIdMethods();
$TSFE->clear_preview();
$TSFE->determineId();

	// Now, if there is a backend user logged in and he has NO access to this page, then re-evaluate the id shown!
if ($TSFE->isBackendUserLoggedIn() && (!$BE_USER->extPageReadAccess($TSFE->page) || t3lib_div::_GP('ADMCMD_noBeUser')))	{	// t3lib_div::_GP('ADMCMD_noBeUser') is placed here because workspacePreviewInit() might need to know if a backend user is logged in!

		// Remove user
	unset($BE_USER);
	$TSFE->beUserLogin = 0;

		// Re-evaluate the page-id.
	$TSFE->checkAlternativeIdMethods();
	$TSFE->clear_preview();
	$TSFE->determineId();
}
$TSFE->makeCacheHash();
$TT->pull();

	// Admin Panel & Frontend editing
if ($TSFE->isBackendUserLoggedIn()) {
	Typo3_Bootstrap::getInstance()->initializeSpriteManager(FALSE);

	$BE_USER->initializeFrontendEdit();
	if ($BE_USER->adminPanel instanceof tslib_AdminPanel) {
		Typo3_Bootstrap::getInstance()->initializeLanguageObject();
	}
	if ($BE_USER->frontendEdit instanceof t3lib_frontendedit) {
		$BE_USER->frontendEdit->initConfigOptions();
	}
}

	// Get compressed $TCA-Array();
	// After this, we should now have a valid $TCA, though minimized
$TSFE->getCompressedTCarray();

	// Starts the template
$TT->push('Start Template', '');
$TSFE->initTemplate();
$TT->pull();

	// Get from cache
$TT->push('Get Page from cache', '');
$TSFE->getFromCache();
$TT->pull();

	// Get config if not already gotten
	// After this, we should have a valid config-array ready
$TSFE->getConfigArray();

	// Convert POST data to internal "renderCharset" if different from the metaCharset
$TSFE->convPOSTCharset();

	// Setting language and locale
$TT->push('Setting language and locale', '');
$TSFE->settingLanguage();
$TSFE->settingLocale();
$TT->pull();

	// Check JumpUrl
$TSFE->setExternalJumpUrl();
$TSFE->checkJumpUrlReferer();

	// Check Submission of data.
	// This is done at this point, because we need the config values
switch ($TSFE->checkDataSubmission()) {
	case 'email':
		$TSFE->sendFormmail();
	break;
}

	// Check for shortcut page and redirect
$TSFE->checkPageForShortcutRedirect();

	// Generate page
$TSFE->setUrlIdToken();

$TT->push('Page generation', '');
	if ($TSFE->isGeneratePage()) {
		$TSFE->generatePage_preProcessing();
		$temp_theScript=$TSFE->generatePage_whichScript();

		if ($temp_theScript) {
			include($temp_theScript);
		} else {
			include(PATH_tslib.'pagegen.php');
		}
		$TSFE->generatePage_postProcessing();
	} elseif ($TSFE->isINTincScript()) {
		include(PATH_tslib.'pagegen.php');
	}
$TT->pull();

	// $TSFE->config['INTincScript']
if ($TSFE->isINTincScript())		{
	$TT->push('Non-cached objects', '');
	$TSFE->INTincScript();
	$TT->pull();
}

	// Output content
$sendTSFEContent = FALSE;
if ($TSFE->isOutputting()) {
	$TT->push('Print Content', '');
	$TSFE->processOutput();
	$sendTSFEContent = TRUE;
	$TT->pull();
}

	// Store session data for fe_users
$TSFE->storeSessionData();

	// Statistics
$TYPO3_MISC['microtime_end'] = microtime(TRUE);
$TSFE->setParseTime();
if ($TSFE->isOutputting() && (!empty($TSFE->TYPO3_CONF_VARS['FE']['debug']) || !empty($TSFE->config['config']['debug']))) {
	$TSFE->content .=  LF . '<!-- Parsetime: ' . $TSFE->scriptParseTime . 'ms -->';
}
$TSFE->statistics();

	// Check JumpUrl
$TSFE->jumpurl();

	// Preview info
$TSFE->previewInfo();

	// Hook for end-of-frontend
$TSFE->hook_eofe();

	// Finish timetracking
$TT->pull();

	// Check memory usage
t3lib_utility_Monitor::peakMemoryUsage();

	// beLoginLinkIPList
echo $TSFE->beLoginLinkIPList();

	// Admin panel
if (is_object($BE_USER) && $BE_USER->isAdminPanelVisible() && $TSFE->isBackendUserLoggedIn()) {
	$TSFE->content = str_ireplace('</head>', $BE_USER->adminPanel->getAdminPanelHeaderData() . '</head>', $TSFE->content);
	$TSFE->content = str_ireplace('</body>', $BE_USER->displayAdminPanel() . '</body>', $TSFE->content);
}

if ($sendTSFEContent) {
	echo $TSFE->content;
}

	// Debugging Output
if (isset($error) && is_object($error) && @is_callable(array($error, 'debugOutput'))) {
	$error->debugOutput();
}
if (TYPO3_DLOG) {
	t3lib_div::devLog('END of FRONTEND session', 'cms', 0, array('_FLUSH' => TRUE));
}

Typo3_Bootstrap::getInstance()->shutdown();

?>