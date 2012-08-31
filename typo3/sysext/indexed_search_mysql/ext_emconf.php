<?php
/***************************************************************
 * Extension Manager/Repository config file for ext: "indexed_search_mysql"
 *
 * Auto generated 18-03-2008 20:13
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/
$EM_CONF[$_EXTKEY] = array(
	'title' => 'MySQL driver for Indexed Search Engine',
	'description' => 'MySQL specific driver for Indexed Search Engine. Allows usage of MySQL-only features like FULLTEXT indexes.',
	'category' => 'misc',
	'shy' => 0,
	'dependencies' => 'cms,indexed_search',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => 1,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Michael Stucki',
	'author_email' => 'michael@typo3.org',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '2.13.0',
	'_md5_values_when_last_written' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.6-',
			'typo3' => '4.6.0-',
			'indexed_search' => '2.13.0-'
		),
		'conflicts' => array(),
		'suggests' => array()
	),
	'suggests' => array()
);
?>