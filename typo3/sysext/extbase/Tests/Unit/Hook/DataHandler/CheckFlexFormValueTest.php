<?php
namespace TYPO3\CMS\Extbase\Tests\Unit\Hook\DataHandler;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2013 Extbase Team (http://forge.typo3.org/projects/typo3v4-mvc)
 *  Extbase is a backport of TYPO3 Flow. All credits go to the TYPO3 Flow team.
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
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class CheckFlexFormValueTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {

	/**
	 * @var \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected $dataHandler;

	public function setUp() {
		$this->dataHandler = $this->objectManager->get('TYPO3\CMS\Core\DataHandling\DataHandler');
	}

	/**
	 * @test
	 */
	public function checkFlexFormValueBeforeMergeRemovesSwitchableControllerActions() {
		$currentFlexFormDataArray = array(
			'foo' => array(
				'bar' => 'baz',
				'qux' => array(
					'quux' => 'quuux',
					'switchableControllerActions' => array()
				),
				'switchableControllerActions' => array()
			),
			'switchableControllerActions' => array()
		);

		$expectedFlexFormDataArray = array(
			'foo' => array(
				'bar' => 'baz',
				'qux' => array(
					'quux' => 'quuux',
				),
			),
		);

		$newFlexFormDataArray = array();
		/** @var \TYPO3\CMS\Extbase\Hook\DataHandler\CheckFlexFormValue $checkFlexFormValue */
		$checkFlexFormValue = $this->objectManager->get('TYPO3\CMS\Extbase\Hook\DataHandler\CheckFlexFormValue');
		$checkFlexFormValue->checkFlexFormValue_beforeMerge($this->dataHandler, $currentFlexFormDataArray, $newFlexFormDataArray);

		$this->assertSame($expectedFlexFormDataArray, $currentFlexFormDataArray);
	}
}
