<?php

########################################################################
# Extension Manager/Repository config file for ext: "tstemplate_objbrowser"
#
# Auto generated 23-04-2008 10:53
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Web>Template, Object Browser',
	'description' => 'The Object Browser writes out the TypoScript configuration in an object tree style.',
	'category' => 'module',
	'shy' => 1,
	'dependencies' => 'tstemplate',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Kasper Skaarhoj',
	'author_email' => 'kasperYYYY@typo3.com',
	'author_company' => 'Curby Soft Multimedia',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '0.1.0',
	'_md5_values_when_last_written' => 'a:5:{s:9:"ChangeLog";s:4:"63a4";s:33:"class.tx_tstemplateobjbrowser.php";s:4:"32d3";s:12:"ext_icon.gif";s:4:"4226";s:14:"ext_tables.php";s:4:"7ff6";s:12:"doc/TODO.txt";s:4:"6bb0";}',
	'constraints' => array(
		'depends' => array(
			'tstemplate' => '',
			'php' => '5.1.0-0.0.0',
			'typo3' => '4.2.0-4.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>