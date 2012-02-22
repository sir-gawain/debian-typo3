<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
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

/**
 * Evaluates to the upper-case string value (or values, if multi-valued) of
 * operand.
 *
 * If operand does not evaluate to a string value, its value is first converted
 * to a string.
 *
 * If operand evaluates to null, the UpperCase operand also evaluates to null.
 *
 * @package Extbase
 * @subpackage Persistence\QOM
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class Tx_Extbase_Persistence_QOM_UpperCase implements Tx_Extbase_Persistence_QOM_UpperCaseInterface {

	/**
	 * @var Tx_Extbase_Persistence_QOM_DynamicOperandInterface
	 */
	protected $operand;

	/**
	 * Constructs this UpperCase instance
	 *
	 * @param Tx_Extbase_Persistence_QOM_DynamicOperandInterface $constraint
	 */
	public function __construct(Tx_Extbase_Persistence_QOM_DynamicOperandInterface $operand) {
		$this->operand = $operand;
	}

	/**
	 * Gets the operand whose value is converted to a upper-case string.
	 *
	 * @return Tx_Extbase_Persistence_QOM_DynamicOperandInterface the operand; non-null
	 */
	public function getOperand() {
		return $this->operand;
	}

}
?>