<?php
/* $Id: ext_localconf.php 7251 2010-04-06 18:57:45Z francois $ */

if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

	// register the hook to actually do the work within TCEmain
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['version'] = t3lib_extMgm::extPath('version', 'class.tx_version_tcemain.php:&tx_version_tcemain');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass']['version'] = t3lib_extMgm::extPath('version', 'class.tx_version_tcemain.php:&tx_version_tcemain');
	// Register hook for overriding the icon status overlay
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideIconOverlay']['version'] = t3lib_extMgm::extPath('version', 'class.tx_version_iconworks.php:&tx_version_iconworks');

if (TYPO3_MODE == 'BE') {

	// add default notification options to every page
t3lib_extMgm::addPageTSconfig('
	tx_version.workspaces.stageNotificationEmail.subject = LLL:EXT:version/Resources/Private/Language/locallang_emails.xml:subject
	tx_version.workspaces.stageNotificationEmail.message = LLL:EXT:version/Resources/Private/Language/locallang_emails.xml:message
	# tx_version.workspaces.stageNotificationEmail.additionalHeaders =
');
}


?>
