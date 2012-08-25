<?php

/*                                                                        *
 * This script is backported from the FLOW3 package "TYPO3.Fluid".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * Abstract Fluid Compiled template.
 *
 * INTERNAL!!
 *
 */
abstract class Tx_Fluid_Core_Compiler_AbstractCompiledTemplate implements Tx_Fluid_Core_Parser_ParsedTemplateInterface {

	/**
	 * @var array
	 */
	protected $viewHelpersByPositionAndContext = array();

	// These tokens are replaced by the Backporter for implementing different behavior in TYPO3 v4
	/**
	 * @var Tx_Extbase_Object_Container_Container
	 */
	protected static $objectContainer;

	/**
	 * @var string
	 */
	static protected $defaultEncoding = NULL;

	/**
	 * Public such that it is callable from within closures
	 *
	 * @param integer $uniqueCounter
	 * @param Tx_Fluid_Core_Rendering_RenderingContextInterface $renderingContext
	 * @param string $viewHelperName
	 * @return Tx_Fluid_Core_ViewHelper_AbstractViewHelper
	 * @internal
	 */
	public function getViewHelper($uniqueCounter, Tx_Fluid_Core_Rendering_RenderingContextInterface $renderingContext, $viewHelperName) {
		if (self::$objectContainer === NULL) {
			self::$objectContainer = t3lib_div::makeInstance('Tx_Extbase_Object_Container_Container'); // Singleton
		}
		if (isset($this->viewHelpersByPositionAndContext[$uniqueCounter])) {
			if ($this->viewHelpersByPositionAndContext[$uniqueCounter]->contains($renderingContext)) {
				$viewHelper = $this->viewHelpersByPositionAndContext[$uniqueCounter][$renderingContext];
				$viewHelper->resetState();
				return $viewHelper;
			} else {
				$viewHelperInstance = self::$objectContainer->getInstance($viewHelperName);
				if ($viewHelperInstance instanceof t3lib_Singleton) {
					$viewHelperInstance->resetState();
				}
				$this->viewHelpersByPositionAndContext[$uniqueCounter]->attach($renderingContext, $viewHelperInstance);
				return $viewHelperInstance;
			}
		} else {
			$this->viewHelpersByPositionAndContext[$uniqueCounter] = t3lib_div::makeInstance('Tx_Extbase_Persistence_ObjectStorage');
			$viewHelperInstance = self::$objectContainer->getInstance($viewHelperName);
			if ($viewHelperInstance instanceof t3lib_Singleton) {
				$viewHelperInstance->resetState();
			}
			$this->viewHelpersByPositionAndContext[$uniqueCounter]->attach($renderingContext, $viewHelperInstance);
			return $viewHelperInstance;
		}
	}

	/**
	 * @return boolean
	 */
	public function isCompilable() {
		return FALSE;
	}

	/**
	 * @return boolean
	 */
	public function isCompiled() {
		return TRUE;
	}

	/**
	 * @return string
	 * @internal
	 */
	static public function resolveDefaultEncoding() {
		if (static::$defaultEncoding === NULL) {
			static::$defaultEncoding = 'UTF-8';
		}
		return static::$defaultEncoding;
	}

}
?>