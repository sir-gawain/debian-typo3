<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2009 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Dynamic configuation of the system-related tables, typ. sys_* series
 *
 * $Id: tbl_cms.php 6430 2009-11-16 16:31:24Z ohader $
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 */





// ******************************************************************
// fe_users
//
// FrontEnd users - login on the website
// ******************************************************************
$TCA['fe_users'] = array(
	'ctrl' => $TCA['fe_users']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'username,password,usergroup,lockToDomain,name,title,company,address,zip,city,country,email,www,telephone,fax,disable,starttime,endtime,lastlogin'
	),
	'feInterface' => $TCA['fe_users']['feInterface'],
	'columns' => array(
		'username' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users.username',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'max' => '50',
				'eval' => 'nospace,lower,uniqueInPid,required'
			)
		),
		'password' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users.password',
			'config' => array(
				'type' => 'input',
				'size' => '10',
				'max' => '40',
				'eval' => 'nospace,required,password'
			)
		),
		'usergroup' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users.usergroup',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'ORDER BY fe_groups.title',
				'size' => '6',
				'minitems' => '1',
				'maxitems' => '50'
			)
		),
		'lockToDomain' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_users.lockToDomain',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '50',
				'checkbox' => '',
				'softref' => 'substitute'
			)
		),
		'name' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.name',
			'config' => array(
				'type' => 'input',
				'size' => '40',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'address' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.address',
			'config' => array(
				'type' => 'text',
				'cols' => '20',
				'rows' => '3'
			)
		),
		'telephone' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.phone',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
				'size' => '20',
				'max' => '20'
			)
		),
		'fax' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.fax',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '20'
			)
		),
		'email' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.email',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'title' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.title_person',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '40'
			)
		),
		'zip' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.zip',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
				'size' => '10',
				'max' => '10'
			)
		),
		'city' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.city',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '50'
			)
		),
		'country' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.country',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '40'
			)
		),
		'www' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.www',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
				'size' => '20',
				'max' => '80'
			)
		),
		'company' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.company',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim',
				'size' => '20',
				'max' => '80'
			)
		),
		'image' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.image',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/pics',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '6',
				'minitems' => '0'
			)
		),
		'disable' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.disable',
			'config' => array(
				'type' => 'check'
			)
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
				)
			)
		),
		'TSconfig' => array(
			'exclude' => 1,
			'label' => 'TSconfig:',
			'config' => array(
				'type' => 'text',
				'cols' => '40',
				'rows' => '10',
				'wizards' => array(
					'_PADDING' => 4,
					'0' => array(
#						'type' => t3lib_extMgm::isLoaded('tsconfig_help')?'popup':'',
						'title'  => 'TSconfig QuickReference',
						'script' => 'wizard_tsconfig.php?mode=fe_users',
						'icon'   => 'wizard_tsconfig.gif',
						'JSopenParams' => 'height=500,width=780,status=0,menubar=0,scrollbars=1',
					)
				),
				'softref' => 'TSconfig'
			),
			'defaultExtras' => 'fixed-font : enable-tab',
		),
		'lastlogin' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.lastlogin',
			'config' => array(
				'type' => 'input',
				'readOnly' => '1',
				'size' => '12',
				'eval' => 'datetime',
				'default' => 0,
			)
		)
	),
	'types' => array(
		'0' => array('showitem' => '
			disable,username;;;;1-1-1, password, usergroup, lastlogin;;;;1-1-1,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_users.tabs.personelData, name;;1;;1-1-1, address, zip, city, country, telephone, fax, email, www, image;;;;2-2-2,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_users.tabs.options, lockToDomain;;;;1-1-1, TSconfig;;;;2-2-2,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_users.tabs.access, starttime, endtime,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_users.tabs.extended

		')
	),
	'palettes' => array(
		'1' => array('showitem' => 'title,company')
	)
);





// ******************************************************************
// fe_groups
//
// FrontEnd usergroups - Membership of these determines access to elements
// ******************************************************************
$TCA['fe_groups'] = array(
	'ctrl' => $TCA['fe_groups']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,hidden,subgroup,lockToDomain,description'
	),
	'columns' => array(
		'hidden' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.disable',
			'exclude' => 1,
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'title' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_groups.title',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'max' => '50',
				'eval' => 'trim,required'
			)
		),
		'subgroup' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_groups.subgroup',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'fe_groups',
				'foreign_table_where' => 'AND NOT(fe_groups.uid = ###THIS_UID###) AND fe_groups.hidden=0 ORDER BY fe_groups.title',
				'size' => 6,
				'autoSizeMax' => 10,
				'minitems' => 0,
				'maxitems' => 20
			)
		),
		'lockToDomain' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:fe_groups.lockToDomain',
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '50',
				'checkbox' => ''
			)
		),
		'description' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.description',
			'config' => array(
				'type' => 'text',
				'rows' => 5,
				'cols' => 48
			)
		),
		'TSconfig' => array(
			'exclude' => 1,
			'label' => 'TSconfig:',
			'config' => array(
				'type' => 'text',
				'cols' => '40',
				'rows' => '10',
				'wizards' => array(
					'_PADDING' => 4,
					'0' => array(
#						'type' => t3lib_extMgm::isLoaded('tsconfig_help')?'popup':'',
						'title' => 'TSconfig QuickReference',
						'script' => 'wizard_tsconfig.php?mode=fe_users',
						'icon' => 'wizard_tsconfig.gif',
						'JSopenParams' => 'height=500,width=780,status=0,menubar=0,scrollbars=1',
					)
				),
				'softref' => 'TSconfig'
			),
			'defaultExtras' => 'fixed-font : enable-tab',
		)
	),
	'types' => array(
		'0' => array('showitem' => '
			hidden;;;;1-1-1,title;;;;2-2-2,description,subgroup;;;;3-3-3,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_groups.tabs.options, lockToDomain;;;;1-1-1, TSconfig;;;;2-2-2,
			--div--;LLL:EXT:cms/locallang_tca.xml:fe_groups.tabs.extended
		')
	)
);




// ******************************************************************
// sys_domain
// ******************************************************************
$TCA['sys_domain'] = array(
	'ctrl' => $TCA['sys_domain']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,domainName,redirectTo'
	),
	'columns' => array(
		'domainName' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.php:sys_domain.domainName',
			'config' => array(
				'type' => 'input',
				'size' => '35',
				'max' => '80',
				'eval' => 'required,unique,lower,trim',
				'softref' => 'substitute'
			),
		),
		'redirectTo' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.php:sys_domain.redirectTo',
			'config' => array(
				'type' => 'input',
				'size' => '35',
				'max' => '120',
				'checkbox' => '',
				'default' => '',
				'eval' => 'trim',
				'softref' => 'substitute'
			),
		),
		'redirectHttpStatusCode' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_domain.redirectHttpStatusCode',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:cms/locallang_tca.xml:sys_domain.redirectHttpStatusCode.301', '301'),
					array('LLL:EXT:cms/locallang_tca.xml:sys_domain.redirectHttpStatusCode.302', '302'),
					array('LLL:EXT:cms/locallang_tca.xml:sys_domain.redirectHttpStatusCode.303', '303'),
					array('LLL:EXT:cms/locallang_tca.xml:sys_domain.redirectHttpStatusCode.307', '307'),
				),
				'size' => 1,
				'maxitems' => 1,
			),
		),
		'hidden' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.disable',
			'exclude' => 1,
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'prepend_params' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.php:sys_domain.prepend_params',
			'exclude' => 1,
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'forced' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.php:sys_domain.forced',
			'exclude' => 1,
			'config' => array(
				'type' => 'check',
				'default' => '1'
			)
		)
	),
	'types' => array(
		'1' => array('showitem' => 'hidden;;;;1-1-1,domainName;;1;;3-3-3,prepend_params,forced;;;;4-4-4')
	),
	'palettes' => array(
		'1' => array('showitem' => 'redirectTo, redirectHttpStatusCode')
	)
);





// ******************************************************************
// pages_language_overlay
// ******************************************************************
$TCA['pages_language_overlay'] = array(
	'ctrl' => $TCA['pages_language_overlay']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,hidden,starttime,endtime,keywords,description,abstract'
	),
	'columns' => array(
		'doktype' => $TCA['pages']['columns']['doktype'],
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
				)
			)
		),
		'title' => array(
			'l10n_mode' => 'prefixLangTitle',
			'label' => $TCA['pages']['columns']['title']['label'],
			'l10n_cat' => 'text',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'eval' => 'required'
			)
		),
		'subtitle' => array(
			'exclude' => 1,
			'l10n_cat' => 'text',
			'label' => $TCA['pages']['columns']['subtitle']['label'],
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'eval' => ''
			)
		),
		'nav_title' => array(
			'exclude' => 1,
			'l10n_cat' => 'text',
			'label' => $TCA['pages']['columns']['nav_title']['label'],
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '256',
				'checkbox' => '',
				'eval' => 'trim'
			)
		),
		'keywords' => array(
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['keywords']['label'],
			'config' => array(
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'description' => array(
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['description']['label'],
			'config' => array(
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'abstract' => array(
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['abstract']['label'],
			'config' => array(
				'type' => 'text',
				'cols' => '40',
				'rows' => '3'
			)
		),
		'author' => array(
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['author']['label'],
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'author_email' => array(
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['author_email']['label'],
			'config' => array(
				'type' => 'input',
				'size' => '20',
				'eval' => 'trim',
				'max' => '80'
			)
		),
		'media' => array(
			'exclude' => 1,
			'label' => $TCA['pages']['columns']['media']['label'],
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $TCA['pages']['columns']['media']['config']['allowed'],
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/media',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '5',
				'minitems' => '0'
			)
		),
		'url' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.php:pages.url',
			'config' => array(
				'type' => 'input',
				'size' => '25',
				'max' => '255',
				'eval' => 'trim'
			)
		),
		'urltype' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.type',
			'config' => array(
				'type' => 'select',
				'items' => $TCA['pages']['columns']['urltype']['config']['items'],
				'default' => '1'
			)
		),
		'shortcut' => array (
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.shortcut_page',
			'config' => array (
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => '3',
				'maxitems' => '1',
				'minitems' => '0',
				'show_thumbs' => '1',
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest',
					),
				),
			),
		),
		'shortcut_mode' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:cms/locallang_tca.xml:pages.shortcut_mode',
			'config' => array (
				'type' => 'select',
				'items' => $TCA['pages']['columns']['shortcut_mode']['config']['items'],
				'default' => '0'
			)
		),
		'sys_language_uid' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'tx_impexp_origuid' => array('config'=>array('type'=>'passthrough')),
		'l18n_diffsource' => array('config'=>array('type'=>'passthrough')),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max' => '255',
			)
		),
	),
	'types' => array(
			// Standard
		'1'   => array('showitem' => 'doktype;;;;1-1-1, hidden, sys_language_uid, title;;;;2-2-2, subtitle, nav_title,                                                                                              --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.metadata, --palette--;LLL:EXT:lang/locallang_general.xml:LGL.author;5;;3-3-3, abstract, keywords, description, --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files, media;;;;4-4-4, --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access, starttime, endtime'),

			// External URL - URL and URL type can be different for the translated page
		'3'   => array('showitem' => 'doktype;;;;1-1-1, hidden, sys_language_uid, title;;;;2-2-2, subtitle,            --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.url, url;;;;3-3-3, urltype,                                                                                                                                                                                 --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files, media;;;;4-4-4, --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access, starttime, endtime'),

			// Shortcut - shortcut and shortcut mode can be different for the translated page
		'4'   => array('showitem' => 'doktype;;;;1-1-1, hidden, sys_language_uid, title;;;;2-2-2, subtitle,            --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.shortcut, shortcut;;;;3-3-3, shortcut_mode,                                                                                                                                                                 --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files, media;;;;4-4-4, --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access, starttime, endtime'),

			// Mount Point - mount point options can _NOT_ be different for the translated page
		'7'   => array('showitem' => 'doktype;;;;1-1-1, hidden, sys_language_uid, title;;;;2-2-2, subtitle, nav_title,                                                                                                                                                                                                                                                              --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.files, media;;;;4-4-4, --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.access, starttime, endtime'),

			// Separator
		'199' => array('showitem' => 'doktype;;;;1-1-1, hidden, sys_language_uid, title;;;;2-2-2'),

			// Sysfolder
		'254' => array('showitem' => 'doktype;;;;1-1-1, hidden, sys_language_uid, title;LLL:EXT:lang/locallang_general.xml:LGL.title;;;2-2-2'),

			// Recycler
		'255' => array('showitem' => 'doktype;;;;1-1-1, hidden, sys_language_uid, title;;;;2-2-2')
	),
	'palettes' => array(
		'5' => array('showitem' => 'author,author_email', 'canNotCollapse' => true)
	)
);



// ******************************************************************
// sys_template
// ******************************************************************
$TCA['sys_template'] = array(
	'ctrl' => $TCA['sys_template']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,clear,root,include_static,basedOn,nextLevel,resources,sitetitle,description,hidden,starttime,endtime'
	),
	'columns' => array(
		'title' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.title',
			'config' => array(
				'type' => 'input',
				'size' => '25',
				'max' => '256',
				'eval' => 'required'
			)
		),
		'hidden' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.disable',
			'exclude' => 1,
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'starttime' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.starttime',
			'exclude' => 1,
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.endtime',
			'exclude' => 1,
			'config' => array(
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
				)
			)
		),
		'root' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.root',
			'config' => array(
				'type' => 'check'
			)
		),
		'clear' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.clear',
			'config' => array(
				'type' => 'check',
				'items' => array(
					array('Constants', ''),
					array('Setup', '')
				),
				'cols' => 2
			)
		),
		'sitetitle' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.sitetitle',
			'config' => array(
				'type' => 'input',
				'size' => '25',
				'max' => '256'
			)
		),
		'constants' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.constants',
			'config' => array(
				'type' => 'text',
				'cols' => '48',
				'rows' => '10',
				'wrap' => 'OFF',
				'softref' => 'TStemplate,email[subst],url[subst]'
			),
			'defaultExtras' => 'fixed-font : enable-tab',
		),
		'resources' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.resources',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'].',html,htm,ttf,pfb,pfm,txt,css,tmpl,inc,ico,js,xml',
				'max_size' => $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'],
				'uploadfolder' => 'uploads/tf',
				'show_thumbs' => '1',
				'size' => '7',
				'maxitems' => '100',
				'minitems' => '0'
			)
		),
		'nextLevel' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.nextLevel',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'sys_template',
				'show_thumbs' => '1',
				'size' => '1',
				'maxitems' => '1',
				'minitems' => '0',
				'default' => '',
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest',
					),
				),
			)
		),
		'include_static' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.include_static',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'static_template',
				'foreign_table_where' => 'ORDER BY static_template.title DESC',
				'size' => 10,
				'maxitems' => 20,
				'default' => '',
			),
		),
		'include_static_file' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.include_static_file',
			'config' => array(
				'type' => 'select',
				'size' => 10,
				'maxitems' => 100,
				'items' => array(
				),
				'softref' => 'ext_fileref'
			)
		),
		'basedOn' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.basedOn',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'sys_template',
				'show_thumbs' => '1',
				'size' => '3',
				'maxitems' => '50',
				'autoSizeMax' => 10,
				'minitems' => '0',
				'default' => '',
				'wizards' => array(
					'_PADDING' => 4,
					'_VERTICAL' => 1,
					'suggest' => array(
						'type' => 'suggest',
					),
					'edit' => array(
						'type' => 'popup',
						'title' => 'Edit template',
						'script' => 'wizard_edit.php',
						'popup_onlyOpenIfSelected' => 1,
						'icon' => 'edit2.gif',
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
					),
					'add' => array(
						'type' => 'script',
						'title' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.basedOn_add',
						'icon' => 'add.gif',
						'params' => array(
							'table'=>'sys_template',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
						'script' => 'wizard_add.php',
					)
				)
			)
		),
		'includeStaticAfterBasedOn' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.includeStaticAfterBasedOn',
			'exclude' => 1,
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'config' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.config',
			'config' => array(
				'type' => 'text',
				'rows' => 10,
				'cols' => 48,
				'wizards' => array(
					'_PADDING' => 4,
					'0' => array(
#						'type' => t3lib_extMgm::isLoaded('tsconfig_help')?'popup':'',
						'title' => 'TSref online',
						'script' => 'wizard_tsconfig.php?mode=tsref',
						'icon' => 'wizard_tsconfig.gif',
						'JSopenParams' => 'height=500,width=780,status=0,menubar=0,scrollbars=1',
					)
				),
				'wrap' => 'OFF',
				'softref' => 'TStemplate,email[subst],url[subst]'
			),
			'defaultExtras' => 'fixed-font : enable-tab',
		),
		'editorcfg' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.editorcfg',
			'config' => array(
				'type' => 'text',
				'rows' => 8,
				'cols' => 48,
				'wrap' => 'OFF'
			),
			'defaultExtras' => 'fixed-font : enable-tab',
		),
		'description' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.description',
			'config' => array(
				'type' => 'text',
				'rows' => 5,
				'cols' => 48
			)
		),
		'static_file_mode' => array(
			'label' => 'LLL:EXT:cms/locallang_tca.xml:sys_template.static_file_mode',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('LLL:EXT:cms/locallang_tca.xml:sys_template.static_file_mode.0', '0'),
					array('LLL:EXT:cms/locallang_tca.xml:sys_template.static_file_mode.1', '1'),
					array('LLL:EXT:cms/locallang_tca.xml:sys_template.static_file_mode.2', '2'),
				),
				'default' => '0'
			)
		),
		'tx_impexp_origuid' => array('config' => array('type' => 'passthrough')),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => '30',
				'max'  => '255',
			)
		),
	),
	'types' => array(
		'1' => array('showitem' => '
			hidden,title;;1;;2-2-2, sitetitle, constants;;;;3-3-3, config, description;;;;4-4-4,
			--div--;LLL:EXT:cms/locallang_tca.xml:sys_template.tabs.options, clear, root, nextLevel, editorcfg;;;;5-5-5,
			--div--;LLL:EXT:cms/locallang_tca.xml:sys_template.tabs.include, include_static,includeStaticAfterBasedOn,6-6-6, include_static_file, basedOn, static_file_mode,
			--div--;LLL:EXT:cms/locallang_tca.xml:sys_template.tabs.files, resources,
			--div--;LLL:EXT:cms/locallang_tca.xml:sys_template.tabs.access, starttime, endtime'
		)
	)
);





// ******************************************************************
// static_template
// ******************************************************************
$TCA['static_template'] = array(
	'ctrl' => $TCA['static_template']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'title,include_static,description'
	),
	'columns' => array(
		'title' => array(
			'label' => 'Template title:',
			'config' => array(
				'type' => 'input',
				'size' => '25',
				'max' => '256',
				'eval' => 'required'
			)
		),
		'constants' => array(
			'label' => 'Constants:',
			'config' => array(
				'type' => 'text',
				'cols' => '48',
				'rows' => '10',
				'wrap' => 'OFF'
			),
			'defaultExtras' => 'fixed-font : enable-tab',
		),
		'include_static' => array(
			'label' => 'Include static:',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'static_template',
				'foreign_table_where' => 'ORDER BY static_template.title',
				'size' => 10,
				'maxitems' => 20,
				'default' => ''
			)
		),
		'config' => array(
			'label' => 'Setup:',
			'config' => array(
				'type' => 'text',
				'rows' => 10,
				'cols' => 48,
				'wrap' => 'OFF'
			),
			'defaultExtras' => 'fixed-font : enable-tab',
		),
		'editorcfg' => array(
			'label' => 'Backend Editor Configuration:',
			'config' => array(
				'type' => 'text',
				'rows' => 4,
				'cols' => 48,
				'wrap' => 'OFF'
			),
			'defaultExtras' => 'fixed-font : enable-tab',
		),
		'description' => array(
			'label' => 'Description:',
			'config' => array(
				'type' => 'text',
				'rows' => 10,
				'cols' => 48
			)
		)
	),
	'types' => array(
		'1' => array('showitem' => 'title;;;;2-2-2, constants;;;;3-3-3, config, include_static;;;;5-5-5, description;;;;5-5-5, editorcfg')
	)
);



?>