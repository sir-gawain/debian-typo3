<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}
	// Register the edit panel view.
$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/classes/class.frontendedit.php']['edit'] = 'EXT:feedit/Classes/FrontendEditPanel.php:FrontendEditPanel';
?>