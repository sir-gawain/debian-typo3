<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Extbase Team
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
 * Value object containing the relevant informations for a class,
 * this object is build by the classInfoFactory - or could also be restored from a cache
 * 
 * @author Daniel Pötzinger
 */
class Tx_Extbase_Object_Container_ClassInfo {

	/**
	 * The classname of the class where the infos belong to
	 * @var string
	 */
	private $className;

	/**
	 * The constructor Dependencies for the class in the format:
	 * 	 array(
	 *     0 => array( <-- parameters for argument 1
	 *       'name' => <arg name>, <-- name of argument
	 *       'dependency' => <classname>, <-- if the argument is a class, the type of the argument
	 *       'defaultvalue' => <mixed>) <-- if the argument is optional, its default value
	 *     ),
	 *     1 => ...
	 *   )
	 * 
	 * @var array
	 */
	private $constructorArguments;
	
	/**
	 * All setter injections in the format
	 * 	array (<nameOfMethod> => <classNameToInject> )
	 * 
	 * @var array
	 */
	private $injectMethods;

	/**
	 * 
	 * @param string $className
	 * @param array $constructorArguments
	 * @param array $injectMethods
	 */
	public function __construct($className, array $constructorArguments, array $injectMethods) {
		$this->className = $className;
		$this->constructorArguments = $constructorArguments;
		$this->injectMethods = $injectMethods;
	}
	
	/**
	 * @return the $className
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @return the $constructorArguments
	 */
	public function getConstructorArguments() {
		return $this->constructorArguments;
	}

	/**
	 * @return the $injectMethods
	 */
	public function getInjectMethods() {
		return $this->injectMethods;
	}
	
	/**
	 * @return the $injectMethods
	 */
	public function hasInjectMethods() {
		return (count($this->injectMethods) > 0);
	}
}