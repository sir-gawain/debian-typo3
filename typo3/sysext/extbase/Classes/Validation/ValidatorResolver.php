<?php
namespace TYPO3\CMS\Extbase\Validation;

/***************************************************************
 *  Copyright notice
 *
 *  This class is a backport of the corresponding class of TYPO3 Flow.
 *  All credits go to the TYPO3 Flow team.
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
 * Validator resolver to automatically find a appropriate validator for a given subject
 */
class ValidatorResolver implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Match validator names and options
	 *
	 * @var string
	 */
	const PATTERN_MATCH_VALIDATORS = '/
			(?:^|,\\s*)
			(?P<validatorName>[a-z0-9_:.\\\\]+)
			\\s*
			(?:\\(
				(?P<validatorOptions>(?:\\s*[a-z0-9]+\\s*=\\s*(?:
					"(?:\\\\"|[^"])*"
					|\'(?:\\\\\'|[^\'])*\'
					|(?:\\s|[^,"\']*)
				)(?:\\s|,)*)*)
			\\))?
		/ixS';

	/**
	 * Match validator options (to parse actual options)
	 *
	 * @var string
	 */
	const PATTERN_MATCH_VALIDATOROPTIONS = '/
			\\s*
			(?P<optionName>[a-z0-9]+)
			\\s*=\\s*
			(?P<optionValue>
				"(?:\\\\"|[^"])*"
				|\'(?:\\\\\'|[^\'])*\'
				|(?:\\s|[^,"\']*)
			)
		/ixS';

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var array
	 */
	protected $baseValidatorConjunctions = array();

	/**
	 * Injects the object manager
	 *
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager A reference to the object manager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Injects the reflection service
	 *
	 * @param \TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * Get a validator for a given data type. Returns a validator implementing
	 * the \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface or NULL if no validator
	 * could be resolved.
	 *
	 * @param string $validatorName Either one of the built-in data types or fully qualified validator class name
	 * @param array $validatorOptions Options to be passed to the validator
	 * @return \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface Validator or NULL if none found.
	 */
	public function createValidator($validatorName, array $validatorOptions = array()) {
		$validatorClassName = $this->resolveValidatorObjectName($validatorName);
		if ($validatorClassName === FALSE) {
			return NULL;
		}
		$validator = $this->objectManager->get($validatorClassName, $validatorOptions);
		if (!$validator instanceof \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface) {
			return NULL;
		}
		if (method_exists($validator, 'setOptions')) {
			// @deprecated since Extbase 1.4.0, will be removed in Extbase 6.1
			$validator->setOptions($validatorOptions);
		}
		return $validator;
	}

	/**
	 * Resolves and returns the base validator conjunction for the given data type.
	 *
	 * If no validator could be resolved (which usually means that no validation is necessary),
	 * NULL is returned.
	 *
	 * @param string $dataType The data type to search a validator for. Usually the fully qualified object name
	 * @return \TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator The validator conjunction or NULL
	 */
	public function getBaseValidatorConjunction($dataType) {
		if (!isset($this->baseValidatorConjunctions[$dataType])) {
			$this->baseValidatorConjunctions[$dataType] = $this->buildBaseValidatorConjunction($dataType);
		}
		return $this->baseValidatorConjunctions[$dataType];
	}

	/**
	 * Detects and registers any validators for arguments:
	 * - by the data type specified in the
	 *
	 * @param string $className
	 * @param string $methodName
	 * @throws Exception\NoSuchValidatorException
	 * @throws Exception\InvalidValidationConfigurationException
	 * @return array An Array of ValidatorConjunctions for each method parameters.
	 */
	public function buildMethodArgumentsValidatorConjunctions($className, $methodName) {
		$validatorConjunctions = array();
		$methodParameters = $this->reflectionService->getMethodParameters($className, $methodName);
		$methodTagsValues = $this->reflectionService->getMethodTagsValues($className, $methodName);
		if (!count($methodParameters)) {
			// early return in case no parameters were found.
			return $validatorConjunctions;
		}
		foreach ($methodParameters as $parameterName => $methodParameter) {
			$validatorConjunction = $this->createValidator('Conjunction');
			$typeValidator = $this->createValidator($methodParameter['type']);
			if ($typeValidator !== NULL) {
				$validatorConjunction->addValidator($typeValidator);
			}
			$validatorConjunctions[$parameterName] = $validatorConjunction;
		}
		if (isset($methodTagsValues['validate'])) {
			foreach ($methodTagsValues['validate'] as $validateValue) {
				$parsedAnnotation = $this->parseValidatorAnnotation($validateValue);
				foreach ($parsedAnnotation['validators'] as $validatorConfiguration) {
					$newValidator = $this->createValidator($validatorConfiguration['validatorName'], $validatorConfiguration['validatorOptions']);
					if ($newValidator === NULL) {
						throw new \TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException('Invalid validate annotation in ' . $className . '->' . $methodName . '(): Could not resolve class name for  validator "' . $validatorConfiguration['validatorName'] . '".', 1239853109);
					}
					if (isset($validatorConjunctions[$parsedAnnotation['argumentName']])) {
						$validatorConjunctions[$parsedAnnotation['argumentName']]->addValidator($newValidator);
					} else {
						throw new \TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationConfigurationException('Invalid validate annotation in ' . $className . '->' . $methodName . '(): Validator specified for argument name "' . $parsedAnnotation['argumentName'] . '", but this argument does not exist.', 1253172726);
					}
				}
			}
		}
		return $validatorConjunctions;
	}

	/**
	 * Builds a base validator conjunction for the given data type.
	 *
	 * The base validation rules are those which were declared directly in a class (typically
	 * a model) through some @validate annotations on properties.
	 *
	 * Additionally, if a custom validator was defined for the class in question, it will be added
	 * to the end of the conjunction. A custom validator is found if it follows the naming convention
	 * "Replace '\Model\' by '\Validator\' and append "Validator".
	 *
	 * Example: $dataType is F3\Foo\Domain\Model\Quux, then the Validator will be found if it has the
	 * name F3\Foo\Domain\Validator\QuuxValidator
	 *
	 * @param string $dataType The data type to build the validation conjunction for. Needs to be the fully qualified object name.
	 * @throws Exception\NoSuchValidatorException
	 * @return \TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator The validator conjunction or NULL
	 */
	protected function buildBaseValidatorConjunction($dataType) {
		$validatorConjunction = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Validation\\Validator\\ConjunctionValidator');
		// Model based validator
		if (class_exists($dataType)) {
			$validatorCount = 0;
			$objectValidator = $this->createValidator('GenericObject');
			foreach ($this->reflectionService->getClassPropertyNames($dataType) as $classPropertyName) {
				$classPropertyTagsValues = $this->reflectionService->getPropertyTagsValues($dataType, $classPropertyName);
				if (!isset($classPropertyTagsValues['validate'])) {
					continue;
				}
				foreach ($classPropertyTagsValues['validate'] as $validateValue) {
					$parsedAnnotation = $this->parseValidatorAnnotation($validateValue);
					foreach ($parsedAnnotation['validators'] as $validatorConfiguration) {
						$newValidator = $this->createValidator($validatorConfiguration['validatorName'], $validatorConfiguration['validatorOptions']);
						if ($newValidator === NULL) {
							throw new \TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException('Invalid validate annotation in ' . $dataType . '::' . $classPropertyName . ': Could not resolve class name for  validator "' . $validatorConfiguration['validatorName'] . '".', 1241098027);
						}
						$objectValidator->addPropertyValidator($classPropertyName, $newValidator);
						$validatorCount++;
					}
				}
			}
			if ($validatorCount > 0) {
				$validatorConjunction->addValidator($objectValidator);
			}
		}
		// Custom validator for the class
		$possibleValidatorClassName = str_replace(array('_Model_', '\\Model\\'), array('_Validator_', '\\Validator\\'), $dataType) . 'Validator';
		$customValidator = $this->createValidator($possibleValidatorClassName);
		if ($customValidator !== NULL) {
			$validatorConjunction->addValidator($customValidator);
		}
		return $validatorConjunction;
	}

	/**
	 * Parses the validator options given in @validate annotations.
	 *
	 * @param string $validateValue
	 * @return array
	 */
	protected function parseValidatorAnnotation($validateValue) {
		$matches = array();
		if ($validateValue[0] === '$') {
			$parts = explode(' ', $validateValue, 2);
			$validatorConfiguration = array('argumentName' => ltrim($parts[0], '$'), 'validators' => array());
			preg_match_all(self::PATTERN_MATCH_VALIDATORS, $parts[1], $matches, PREG_SET_ORDER);
		} else {
			$validatorConfiguration = array('validators' => array());
			preg_match_all(self::PATTERN_MATCH_VALIDATORS, $validateValue, $matches, PREG_SET_ORDER);
		}
		foreach ($matches as $match) {
			$validatorOptions = array();
			if (isset($match['validatorOptions'])) {
				$validatorOptions = $this->parseValidatorOptions($match['validatorOptions']);
			}
			$validatorConfiguration['validators'][] = array('validatorName' => $match['validatorName'], 'validatorOptions' => $validatorOptions);
		}
		return $validatorConfiguration;
	}

	/**
	 * Parses $rawValidatorOptions not containing quoted option values.
	 * $rawValidatorOptions will be an empty string afterwards (pass by ref!).
	 *
	 * @param string &$rawValidatorOptions
	 * @return array An array of optionName/optionValue pairs
	 */
	protected function parseValidatorOptions($rawValidatorOptions) {
		$validatorOptions = array();
		$parsedValidatorOptions = array();
		preg_match_all(self::PATTERN_MATCH_VALIDATOROPTIONS, $rawValidatorOptions, $validatorOptions, PREG_SET_ORDER);
		foreach ($validatorOptions as $validatorOption) {
			$parsedValidatorOptions[trim($validatorOption['optionName'])] = trim($validatorOption['optionValue']);
		}
		array_walk($parsedValidatorOptions, array($this, 'unquoteString'));
		return $parsedValidatorOptions;
	}

	/**
	 * Removes escapings from a given argument string and trims the outermost
	 * quotes.
	 *
	 * This method is meant as a helper for regular expression results.
	 *
	 * @param string &$quotedValue Value to unquote
	 * @return void
	 */
	protected function unquoteString(&$quotedValue) {
		switch ($quotedValue[0]) {
			case '"':
				$quotedValue = str_replace('\\"', '"', trim($quotedValue, '"'));
				break;
			case '\'':
				$quotedValue = str_replace('\\\'', '\'', trim($quotedValue, '\''));
				break;
		}
		$quotedValue = str_replace('\\\\', '\\', $quotedValue);
	}

	/**
	 * Returns an object of an appropriate validator for the given class. If no validator is available
	 * FALSE is returned
	 *
	 * @param string $validatorName Either the fully qualified class name of the validator or the short name of a built-in validator
	 * @return string|boolean Name of the validator object or FALSE
	 */
	protected function resolveValidatorObjectName($validatorName) {
		if (strpbrk($validatorName, '_\\') !== FALSE && class_exists($validatorName)) {
			return $validatorName;
		}
		list($extensionName, $extensionValidatorName) = explode(':', $validatorName);
		if (empty($extensionValidatorName)) {
			$possibleClassName = 'TYPO3\\CMS\\Extbase\\Validation\\Validator\\' . $this->unifyDataType($validatorName) . 'Validator';
		} else {
			if (strpos($extensionName, '.') !== FALSE) {
				$extensionNameParts = explode('.', $extensionName);
				$extensionName = array_pop($extensionNameParts);
				$vendorName = implode('\\', $extensionNameParts);
				$possibleClassName = $vendorName . '\\' . $extensionName . '\\Validation\\Validator\\' . $extensionValidatorName . 'Validator';
			} else {
				$possibleClassName = 'Tx_' . $extensionName . '_Validation_Validator_' . $extensionValidatorName . 'Validator';
			}
		}
		if (class_exists($possibleClassName)) {
			return $possibleClassName;
		}
		return FALSE;
	}

	/**
	 * Preprocess data types. Used to map primitive PHP types to DataTypes used in Extbase.
	 *
	 * @param string $type Data type to unify
	 * @return string unified data type
	 */
	protected function unifyDataType($type) {
		switch ($type) {
			case 'int':
				$type = 'Integer';
				break;
			case 'bool':
				$type = 'Boolean';
				break;
			case 'double':
				$type = 'Float';
				break;
			case 'numeric':
				$type = 'Number';
				break;
			case 'mixed':
				$type = 'Raw';
				break;
		}
		return ucfirst($type);
	}
}

?>