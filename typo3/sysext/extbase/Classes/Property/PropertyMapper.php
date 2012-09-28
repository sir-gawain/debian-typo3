<?php
namespace TYPO3\CMS\Extbase\Property;

/*                                                                        *
 * This script belongs to the Extbase framework                           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
/**
 * The Property Mapper transforms simple types (arrays, strings, integers, floats, booleans) to objects or other simple types.
 * It is used most prominently to map incoming HTTP arguments to objects.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class PropertyMapper implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder
	 */
	protected $configurationBuilder;

	/**
	 * @var \TYPO3\CMS\Extbase\Service\TypeHandlingService
	 */
	protected $typeHandlingService;

	/**
	 * A multi-dimensional array which stores the Type Converters available in the system.
	 * It has the following structure:
	 * 1. Dimension: Source Type
	 * 2. Dimension: Target Type
	 * 3. Dimension: Priority
	 * Value: Type Converter instance
	 *
	 * @var array
	 */
	protected $typeConverters = array();

	/**
	 * A list of property mapping messages (errors, warnings) which have occured on last mapping.
	 *
	 * @var \TYPO3\CMS\Extbase\Error\Result
	 */
	protected $messages;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder $propertyMappingConfigurationBuilder
	 * @return void
	 */
	public function injectPropertyMappingConfigurationBuilder(\TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationBuilder $propertyMappingConfigurationBuilder) {
		$this->configurationBuilder = $propertyMappingConfigurationBuilder;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Service\TypeHandlingService $typeHandlingService
	 * @return void
	 */
	public function injectTypeHandlingService(\TYPO3\CMS\Extbase\Service\TypeHandlingService $typeHandlingService) {
		$this->typeHandlingService = $typeHandlingService;
	}

	/**
	 * Lifecycle method, called after all dependencies have been injected.
	 * Here, the typeConverter array gets initialized.
	 *
	 * @throws Exception\DuplicateTypeConverterException
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function initializeObject() {
		foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters'] as $typeConverterClassName) {
			$typeConverter = $this->objectManager->get($typeConverterClassName);
			foreach ($typeConverter->getSupportedSourceTypes() as $supportedSourceType) {
				if (isset($this->typeConverters[$supportedSourceType][$typeConverter->getSupportedTargetType()][$typeConverter->getPriority()])) {
					throw new \TYPO3\CMS\Extbase\Property\Exception\DuplicateTypeConverterException('There exist at least two converters which handle the conversion from "' . $supportedSourceType . '" to "' . $typeConverter->getSupportedTargetType() . '" with priority "' . $typeConverter->getPriority() . '": ' . get_class($this->typeConverters[$supportedSourceType][$typeConverter->getSupportedTargetType()][$typeConverter->getPriority()]) . ' and ' . get_class($typeConverter), 1297951378);
				}
				$this->typeConverters[$supportedSourceType][$typeConverter->getSupportedTargetType()][$typeConverter->getPriority()] = $typeConverter;
			}
		}
	}

	/**
	 * Map $source to $targetType, and return the result
	 *
	 * @param mixed $source the source data to map. MUST be a simple type, NO object allowed!
	 * @param string $targetType The type of the target; can be either a class name or a simple type.
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration Configuration for the property mapping. If NULL, the PropertyMappingConfigurationBuilder will create a default configuration.
	 * @throws Exception
	 * @return mixed an instance of $targetType
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function convert($source, $targetType, \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		if ($configuration === NULL) {
			$configuration = $this->configurationBuilder->build();
		}
		$currentPropertyPath = array();
		$this->messages = new \TYPO3\CMS\Extbase\Error\Result();
		try {
			return $this->doMapping($source, $targetType, $configuration, $currentPropertyPath);
		} catch (\Exception $e) {
			throw new \TYPO3\CMS\Extbase\Property\Exception('Exception while property mapping at property path "' . implode('.', $currentPropertyPath) . '":' . $e->getMessage(), 1297759968, $e);
		}
	}

	/**
	 * Get the messages of the last Property Mapping
	 *
	 * @return \TYPO3\CMS\Extbase\Error\Result
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function getMessages() {
		return $this->messages;
	}

	/**
	 * Internal function which actually does the property mapping.
	 *
	 * @param mixed $source the source data to map. MUST be a simple type, NO object allowed!
	 * @param string $targetType The type of the target; can be either a class name or a simple type.
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration Configuration for the property mapping.
	 * @param array $currentPropertyPath The property path currently being mapped; used for knowing the context in case an exception is thrown.
	 * @throws Exception\TypeConverterException
	 * @return mixed an instance of $targetType
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function doMapping($source, $targetType, \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration, &$currentPropertyPath) {
		if ($source === NULL) {
			$source = '';
		}
		$typeConverter = $this->findTypeConverter($source, $targetType, $configuration);
		if (!is_object($typeConverter) || !$typeConverter instanceof \TYPO3\CMS\Extbase\Property\TypeConverterInterface) {
			throw new \TYPO3\CMS\Extbase\Property\Exception\TypeConverterException('Type converter for "' . $source . '" -> "' . $targetType . '" not found.');
		}
		$convertedChildProperties = array();
		foreach ($typeConverter->getSourceChildPropertiesToBeConverted($source) as $sourcePropertyName => $sourcePropertyValue) {
			$targetPropertyName = $configuration->getTargetPropertyName($sourcePropertyName);
			if (!$configuration->shouldMap($targetPropertyName)) {
				continue;
			}
			$targetPropertyType = $typeConverter->getTypeOfChildProperty($targetType, $targetPropertyName, $configuration);
			$subConfiguration = $configuration->getConfigurationFor($targetPropertyName);
			$currentPropertyPath[] = $targetPropertyName;
			$targetPropertyValue = $this->doMapping($sourcePropertyValue, $targetPropertyType, $subConfiguration, $currentPropertyPath);
			array_pop($currentPropertyPath);
			if ($targetPropertyValue !== NULL) {
				$convertedChildProperties[$targetPropertyName] = $targetPropertyValue;
			}
		}
		$result = $typeConverter->convertFrom($source, $targetType, $convertedChildProperties, $configuration);
		if ($result instanceof \TYPO3\CMS\Extbase\Error\Error) {
			$this->messages->forProperty(implode('.', $currentPropertyPath))->addError($result);
			$result = NULL;
		}
		return $result;
	}

	/**
	 * Determine the type converter to be used. If no converter has been found, an exception is raised.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @throws Exception\TypeConverterException
	 * @throws Exception\InvalidTargetException
	 * @return \TYPO3\CMS\Extbase\Property\TypeConverterInterface Type Converter which should be used to convert between $source and $targetType.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function findTypeConverter($source, $targetType, \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration) {
		if ($configuration->getTypeConverter() !== NULL) {
			return $configuration->getTypeConverter();
		}
		$sourceType = $this->determineSourceType($source);
		if (!is_string($targetType)) {
			throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException('The target type was no string, but of type "' . gettype($targetType) . '"', 1297941727);
		}
		if (strpos($targetType, '<') !== FALSE) {
			$targetType = substr($targetType, 0, strpos($targetType, '<'));
		}
		$converter = NULL;
		if ($this->typeHandlingService->isSimpleType($targetType)) {
			if (isset($this->typeConverters[$sourceType][$targetType])) {
				$converter = $this->findEligibleConverterWithHighestPriority($this->typeConverters[$sourceType][$targetType], $source, $targetType);
			}
		} else {
			$converter = $this->findFirstEligibleTypeConverterInObjectHierarchy($source, $sourceType, $targetType);
		}
		if ($converter === NULL) {
			throw new \TYPO3\CMS\Extbase\Property\Exception\TypeConverterException('No converter found which can be used to convert from "' . $sourceType . '" to "' . $targetType . '".');
		}
		return $converter;
	}

	/**
	 * Tries to find a suitable type converter for the given source and target type.
	 *
	 * @param string $source The actual source value
	 * @param string $sourceType Type of the source to convert from
	 * @param string $targetClass Name of the target class to find a type converter for
	 * @return mixed Either the matching object converter or NULL
	 * @throws Exception\InvalidTargetException
	 */
	protected function findFirstEligibleTypeConverterInObjectHierarchy($source, $sourceType, $targetClass) {
		if (!class_exists($targetClass) && !interface_exists($targetClass)) {
			throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException('Could not find a suitable type converter for "' . $targetClass . '" because no such class or interface exists.', 1297948764);
		}
		if (!isset($this->typeConverters[$sourceType])) {
			return NULL;
		}
		$convertersForSource = $this->typeConverters[$sourceType];
		if (isset($convertersForSource[$targetClass])) {
			$converter = $this->findEligibleConverterWithHighestPriority($convertersForSource[$targetClass], $source, $targetClass);
			if ($converter !== NULL) {
				return $converter;
			}
		}
		foreach (class_parents($targetClass) as $parentClass) {
			if (!isset($convertersForSource[$parentClass])) {
				continue;
			}
			$converter = $this->findEligibleConverterWithHighestPriority($convertersForSource[$parentClass], $source, $targetClass);
			if ($converter !== NULL) {
				return $converter;
			}
		}
		$converters = $this->getConvertersForInterfaces($convertersForSource, class_implements($targetClass));
		$converter = $this->findEligibleConverterWithHighestPriority($converters, $source, $targetClass);
		if ($converter !== NULL) {
			return $converter;
		}
		if (isset($convertersForSource['object'])) {
			return $this->findEligibleConverterWithHighestPriority($convertersForSource['object'], $source, $targetClass);
		} else {
			return NULL;
		}
	}

	/**
	 * @param mixed $converters
	 * @param mixed $source
	 * @param string $targetType
	 * @return mixed Either the matching object converter or NULL
	 */
	protected function findEligibleConverterWithHighestPriority($converters, $source, $targetType) {
		if (!is_array($converters)) {
			return NULL;
		}
		krsort($converters);
		reset($converters);
		foreach ($converters as $converter) {
			if ($converter->canConvertFrom($source, $targetType)) {
				return $converter;
			}
		}
		return NULL;
	}

	/**
	 * @param array $convertersForSource
	 * @param array $interfaceNames
	 * @return array
	 * @throws Exception\DuplicateTypeConverterException
	 */
	protected function getConvertersForInterfaces(array $convertersForSource, array $interfaceNames) {
		$convertersForInterface = array();
		foreach ($interfaceNames as $implementedInterface) {
			if (isset($convertersForSource[$implementedInterface])) {
				foreach ($convertersForSource[$implementedInterface] as $priority => $converter) {
					if (isset($convertersForInterface[$priority])) {
						throw new \TYPO3\CMS\Extbase\Property\Exception\DuplicateTypeConverterException('There exist at least two converters which handle the conversion to an interface with priority "' . $priority . '". ' . get_class($convertersForInterface[$priority]) . ' and ' . get_class($converter), 1297951338);
					}
					$convertersForInterface[$priority] = $converter;
				}
			}
		}
		return $convertersForInterface;
	}

	/**
	 * Determine the type of the source data, or throw an exception if source was an unsupported format.
	 *
	 * @param mixed $source
	 * @throws Exception\InvalidSourceException
	 * @return string the type of $source
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function determineSourceType($source) {
		if (is_string($source)) {
			return 'string';
		} elseif (is_array($source)) {
			return 'array';
		} elseif (is_float($source)) {
			return 'float';
		} elseif (is_integer($source)) {
			return 'integer';
		} elseif (is_bool($source)) {
			return 'boolean';
		} else {
			throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException('The source is not of type string, array, float, integer or boolean, but of type "' . gettype($source) . '"', 1297773150);
		}
	}

}


?>