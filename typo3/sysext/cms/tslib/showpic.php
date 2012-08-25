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
 * Shows a picture from uploads/* in enlarged format in a separate window.
 * Picture file and settings is supplied by GET-parameters: file, width, height, sample, alternativeTempPath, effects, frame, bodyTag, title, wrap, md5
 *
 * Revised for TYPO3 3.6 June/2003 by Kasper Skårhøj
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 */

if (!defined('PATH_typo3conf')) {
	die('The configuration path was not properly defined!');
}
require_once(PATH_t3lib.'class.t3lib_stdgraphic.php');

/**
 * Script Class, generating the page output.
 * Instantiated in the bottom of this script.
 *
 * @author Kasper Skårhøj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tslib
 */
class SC_tslib_showpic {
		// Page content accumulated here.
	var $content;

		// Parameters loaded into these internal variables:
	var $file;
	var $width;
	var $height;
	var $sample;
	var $alternativeTempPath;
	var $effects;
	var $frame;
	var $bodyTag;
	var $title;
	var $wrap;
	var $md5;

	/**
	 * @var string
	 */
	protected $parametersEncoded;

	/**
	 * Init function, setting the input vars in the global space.
	 *
	 * @return void
	 */
	function init() {
			// Loading internal vars with the GET/POST parameters from outside:
		$this->file = t3lib_div::_GP('file');
		$parametersArray = t3lib_div::_GP('parameters');
		$this->frame = t3lib_div::_GP('frame');
		$this->md5 = t3lib_div::_GP('md5');
			// Check parameters
			// If no file-param or parameters are given, we must exit
		if (!$this->file || !isset($parametersArray) || !is_array($parametersArray)) {
			t3lib_utility_Http::setResponseCodeAndExit(t3lib_utility_Http::HTTP_STATUS_410);
		}

		$this->parametersEncoded = implode('', $parametersArray);

			// Chech md5-checksum: If this md5-value does not match the one submitted, then we fail... (this is a kind of security that somebody don't just hit the script with a lot of different parameters
		$md5_value = t3lib_div::hmac(
			implode(
				'|',
				array($this->file, $this->parametersEncoded)
			)
		);

		if ($md5_value !== $this->md5) {
			t3lib_utility_Http::setResponseCodeAndExit(t3lib_utility_Http::HTTP_STATUS_410);
		}

		$parameters = unserialize(base64_decode($this->parametersEncoded));
		foreach ($parameters as $parameterName => $parameterValue) {
			$this->$parameterName = $parameterValue;
		}

			// Check the file. If must be in a directory beneath the dir of this script...
			// $this->file remains unchanged, because of the code in stdgraphic, but we do check if the file exists within the current path
		$test_file = PATH_site . $this->file;
		if (!t3lib_div::validPathStr($test_file)) {
			t3lib_utility_Http::setResponseCodeAndExit(t3lib_utility_Http::HTTP_STATUS_410);
		}
		if (!@is_file($test_file)) {
			t3lib_utility_Http::setResponseCodeAndExit(t3lib_utility_Http::HTTP_STATUS_404);
		}
	}

	/**
	 * Main function which creates the image if needed and outputs the HTML code for the page displaying the image.
	 * Accumulates the content in $this->content
	 *
	 * @return void
	 */
	function main() {

			// Creating stdGraphic object, initialize it and make image:
		$img = t3lib_div::makeInstance('t3lib_stdGraphic');
		$img->mayScaleUp = 0;
		$img->init();
		if ($this->sample) {
			$img->scalecmd = '-sample';
		}
		if ($this->alternativeTempPath && t3lib_div::inList($GLOBALS['TYPO3_CONF_VARS']['FE']['allowedTempPaths'], $this->alternativeTempPath)) {
			$img->tempPath = $this->alternativeTempPath;
		}

			// Need to connect to database, because this is used (typo3temp_db_tracking, cached image dimensions).
		$GLOBALS['TYPO3_DB']->connectDB();

		if (strstr($this->width . $this->height, 'm')) {
			$max = 'm';
		} else {
			$max = '';
		}

		$this->height = t3lib_utility_Math::forceIntegerInRange($this->height, 0);
		$this->width = t3lib_utility_Math::forceIntegerInRange($this->width, 0);
		if ($this->frame) {
			$this->frame = intval($this->frame);
		}
		$imgInfo = $img->imageMagickConvert($this->file, 'web', $this->width.$max, $this->height, $img->IMparams($this->effects), $this->frame, '');

			// Create HTML output:
		$this->content = '';
		$this->content .= '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>' . htmlspecialchars($this->title ? $this->title : 'Image').'</title>
	' . ($this->title ? '' : '<meta name="robots" content="noindex,follow" />') . '
</head>
		' . ($this->bodyTag ? $this->bodyTag : '<body>');

		if (is_array($imgInfo)) {
			$wrapParts = explode('|', $this->wrap);
			$this->content.=trim($wrapParts[0]).$img->imgTag($imgInfo).trim($wrapParts[1]);
		}
		$this->content .= '
		</body>
		</html>';
	}

	/**
	 * Outputs the content from $this->content
	 *
	 * @return void
	 */
	function printContent() {
		echo $this->content;
	}
}

	// Make instance:
$SOBE = t3lib_div::makeInstance('SC_tslib_showpic');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>