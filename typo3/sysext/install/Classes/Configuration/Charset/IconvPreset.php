<?php
namespace TYPO3\CMS\Install\Configuration\Charset;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Christian Kuhn <lolli@schwarzbu.ch>
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Install\Configuration;

/**
 * Iconv charset preset
 */
class IconvPreset extends Configuration\AbstractPreset implements Configuration\PresetInterface {

	/**
	 * @var string Name of preset
	 */
	protected $name = 'Iconv';

	/**
	 * @var integer Priority of preset
	 */
	protected $priority = 80;

	/**
	 * @var array Configuration values handled by this preset
	 */
	protected $configurationValues = array(
		'SYS/t3lib_cs_convMethod' => 'iconv',
		'SYS/t3lib_cs_utils' => 'iconv',
	);

	/**
	 * Check if iconv PHP module is loaded
	 *
	 * @return boolean TRUE if iconv PHP module is loaded
	 */
	public function isAvailable() {
		$result = FALSE;
		if (extension_loaded('iconv')) {
			$result = TRUE;
		}
		return $result;
	}
}
