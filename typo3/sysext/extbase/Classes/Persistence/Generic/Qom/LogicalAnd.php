<?php
namespace TYPO3\CMS\Extbase\Persistence\Generic\Qom;

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
 * Performs a logical conjunction of two other constraints.
 *
 * To satisfy the And constraint, a node-tuple must satisfy both constraint1 and
 * constraint2.
 *
 * @package Extbase
 * @subpackage Persistence\QOM
 * @version $Id$
 * @scope prototype
 */
class LogicalAnd implements \TYPO3\CMS\Extbase\Persistence\Generic\Qom\AndInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface
	 */
	protected $constraint1;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface
	 */
	protected $constraint2;

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint1
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint2
	 */
	public function __construct(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint1, \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint2) {
		$this->constraint1 = $constraint1;
		$this->constraint2 = $constraint2;
	}

	/**
	 * Fills an array with the names of all bound variables in the constraints
	 *
	 * @param array &$boundVariables
	 * @return void
	 */
	public function collectBoundVariableNames(&$boundVariables) {
		$this->constraint1->collectBoundVariableNames($boundVariables);
		$this->constraint2->collectBoundVariableNames($boundVariables);
	}

	/**
	 * Gets the first constraint.
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface the constraint; non-null
	 */
	public function getConstraint1() {
		return $this->constraint1;
	}

	/**
	 * Gets the second constraint.
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface the constraint; non-null
	 */
	public function getConstraint2() {
		return $this->constraint2;
	}

}


?>