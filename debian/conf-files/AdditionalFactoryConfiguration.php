<?php

return array(
	'FE' => array(
			// configure login forms to use rsa security level
		'loginSecurityLevel' => 'rsa',
	),
	'BE' => array(
		'installToolPassword' => 'bacb98acf97e0b6112b1d1b650b84971',

			// disable the donate now! popup window
		'allowDonateWindow' => '0',

			// Force TYPO3 to use utf-8 in frontend and backend
		'forceCharset' => 'utf-8',

			// If safe_mode is activated with TYPO3, disable use of
			// external programs
			// typo3-dummy provides links to the most important external programs in
			// /var/lib/typo3-dummy/execdir, so exec() function is enabled by default.
		'disable_exec_function' => '0',

			// configure login forms to use rsa security level
		'loginSecurityLevel' => 'rsa',
	),
	'GFX' => array(

			// TYPO3 prefers the use of GIF-files and most likely your visitors on
			// your website does too as not all browsers support PNG yet.
		'gdlib_png' => '1',

			// set this true to get some better results in GIFBUILDER
		'png_truecolor' => '1',

			// enabling the use of gdblib2 for image processing
		'gdlib_2' => '1',

			// last versions of imagemagick renamed combine to composite. It will
			// be set by basic configuration automatically.
		'im_combine_filename' => 'combine',

			// This value should be set to 1 if imagemagick version is greater
			// than 5.2
		'im_negate_mask' => '1',
		'im_imvMaskState' => '1',
		'im_mask_temp_ext_gif' => '1',

			// The value should be 0 if the version of imagemagick is greater than
			// 5, otherwise the creation of effects is getting too slow
		'im_no_effects' => '1',
		'im_v5effects' => '1',

			// Path to the imagemagick manipulation tools like convert,
			// composite and identify
		'im_path' => '/var/lib/typo3-dummy/execdir/',
		'im_path_lzw' => '/var/lib/typo3-dummy/execdir/',

			// Set Value to 1 if version of ImageMagick is greater than 4.9
		'im_version_5' => 'gm',

			// This variable can be empty if ImageMagick is compiled with LZW.
			// Otherwise you have to set the path to LZW
			//$TYPO3_CONF_VARS['GFX']['im_path_lzw'] = '';

			// Image file formats that should be accepted by Typo3
		'imagefile_ext' => 'gif,jpg,jpeg,tif,bmp,pcx,tga,png,pdf',

		'im_noFramePrepended' => '1',

			// Enables the preview of images to make the choice more easy
		'thumbnails' => '1',

			// Preview of images in png or gif format.
			// Should be the same as 'gdlib_png'
		'thumbnails_png' => '1',

			// Check freetype quicktest in the basic configuration if text is
			// exceeding the image borders. If yes, you are using Freetype 2 and
			// need to set TTFdpi to 96 dpi
		'TTFdpi' => '96',

			// The list of file extensions to be considered as images
		'imagefile_ext' => 'gif,jpg,jpeg,tif,bmp,pcx,tga,png,swf,pdf,ai',

			// Track images generated by imagemagick in the database (prevents double image rendering)
		'enable_typo3temp_db_tracking' => '1',
	),
 	'EXT' => array(
 		'extListArray' => array(
			'cshmanual',
			'opendocs',
			'recycler',
			'scheduler',
			'linkvalidator',
			'rsaauth',
			'saltedpasswords',
		),

		'saltedpasswords' => 'a:2:{s:3:"FE.";a:2:{s:7:"enabled";s:1:"1";s:21:"saltedPWHashingMethod";s:28:"tx_saltedpasswords_salts_md5";}s:3:"BE.";a:2:{s:7:"enabled";s:1:"1";s:21:"saltedPWHashingMethod";s:28:"tx_saltedpasswords_salts_md5";}}',
	),
	'SYS' => array(
		'sitename' => 'New TYPO3 site',

			// encryption key. set is to a long unique string.
		'encryptionKey' => '###ENCKEY###',

			// Set compat level to current version
		'compat_version' => '6.2',

			// define search path for binaries
		'binPath' => '/var/lib/typo3-dummy/execdir/',

			// disable logging of deprecated functions
		'enableDeprecationLog' => '',

			// set memory limit to 48 mb.
			// You may want to set this limit higher to get the extension manager working.
		'setMemoryLimit' => '128',

			// Defines which of these PHP-features to use for various Charset conversing
			// functions in t3lib_cs.
		't3lib_cs_convMethod' => 'iconv',

			// Let TYPO3 init mysql to use utf-8 connections
		'setDBinit' => 'SET NAMES utf8;',

		'useCachingFramework' => '0',

		'caching' => array(
			'cacheBackendAssignments' = array(
				'cache_hash' => array(
					'backend' => 't3lib_cache_backend_File',
					'options' => array(
					)
				),
				'cache_pages' => array(
					'backend' => 't3lib_cache_backend_Memcached',
					'options' => array(
						'servers' => array('localhost:11211'),
					)
				),
				'cache_pagesection' => array(
					'backend' => 't3lib_cache_backend_Memcached',
					'options' => array(
						'servers' => array('localhost:11211'),
					)
				)
			),
		),
	),
);
?>