<?php

########################################################################
# Extension Manager/Repository config file for ext "dbal".
#
# Auto generated 28-09-2010 16:16
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Database Abstraction Layer',
	'description' => 'A database abstraction layer implementation for TYPO3 4.0 based on ADOdb and offering a lot of other features...',
	'category' => 'be',
	'shy' => 0,
	'dependencies' => 'adodb',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author' => 'Xavier Perseguers',
	'author_email' => 'typo3@perseguers.ch',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'version' => '1.0.6',
	'_md5_values_when_last_written' => 'a:35:{s:9:"ChangeLog";s:4:"b301";s:26:"class.ux_db_list_extra.php";s:4:"6b88";s:21:"class.ux_t3lib_db.php";s:4:"7e65";s:28:"class.ux_t3lib_sqlparser.php";s:4:"e922";s:16:"ext_autoload.php";s:4:"bd13";s:21:"ext_conf_template.txt";s:4:"f5cf";s:12:"ext_icon.gif";s:4:"c9ba";s:17:"ext_localconf.php";s:4:"b7ca";s:14:"ext_tables.php";s:4:"b187";s:14:"ext_tables.sql";s:4:"1f95";s:27:"doc/class.tslib_fe.php.diff";s:4:"0083";s:14:"doc/manual.sxw";s:4:"2102";s:45:"handlers/class.tx_dbal_handler_openoffice.php";s:4:"f1cc";s:43:"handlers/class.tx_dbal_handler_rawmysql.php";s:4:"d523";s:40:"handlers/class.tx_dbal_handler_xmldb.php";s:4:"e7bf";s:31:"lib/class.tx_dbal_sqlengine.php";s:4:"89a4";s:33:"lib/class.tx_dbal_tsparserext.php";s:4:"420b";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"6e63";s:14:"mod1/index.php";s:4:"fd87";s:18:"mod1/locallang.xml";s:4:"0b57";s:22:"mod1/locallang_mod.xml";s:4:"86ef";s:19:"mod1/moduleicon.gif";s:4:"2b8f";s:10:"res/README";s:4:"be19";s:30:"res/oracle/indexed_search.diff";s:4:"ec81";s:23:"res/oracle/realurl.diff";s:4:"86da";s:25:"res/oracle/scheduler.diff";s:4:"7c06";s:27:"res/oracle/templavoila.diff";s:4:"1fd5";s:43:"res/postgresql/postgresql-compatibility.sql";s:4:"5299";s:22:"tests/BaseTestCase.php";s:4:"6f20";s:26:"tests/FakeDbConnection.php";s:4:"8fb1";s:23:"tests/dbGeneralTest.php";s:4:"c74c";s:22:"tests/dbOracleTest.php";s:4:"a3ca";s:30:"tests/sqlParserGeneralTest.php";s:4:"f127";s:30:"tests/fixtures/oci8.config.php";s:4:"433e";}',
	'constraints' => array(
		'depends' => array(
			'adodb' => '5.10.0-',
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.3.0-4.3.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>