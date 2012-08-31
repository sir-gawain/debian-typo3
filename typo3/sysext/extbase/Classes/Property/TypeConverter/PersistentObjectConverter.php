<?php
namespace TYPO3\CMS\Extbase\Property\TypeConverter;

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
 * This converter transforms arrays or strings to persistent objects. It does the following:
 *
 * - If the input is string, it is assumed to be a UID. Then, the object is fetched from persistence.
 * - If the input is array, we check if it has an identity property.
 *
 * - If the input has an identity property and NO additional properties, we fetch the object from persistence.
 * - If the input has an identity property AND additional properties, we fetch the object from persistence,
 * create a clone on it, and set the sub-properties. We only do this if the configuration option "CONFIGURATION_MODIFICATION_ALLOWED" is TRUE.
 * - If the input has NO identity property, but additional properties, we create a new object and return it.
 * However, we only do this if the configuration option "CONFIGURATION_CREATION_ALLOWED" is TRUE.
 *
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @api
 */
class PersistentObjectConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter implements \TYPO3\CMS\Core\SingletonInterface {

	const CONFIGURATION_MODIFICATION_ALLOWED = 1;
	const CONFIGURATION_CREATION_ALLOWED = 2;
	const CONFIGURATION_TARGET_TYPE = 3;
	/**
	 * @var array
	 */
	protected $sourceTypes = array('string', 'array');

	/**
	 * @var string
	 */
	protected $targetType = 'object';

	/**
	 * @var integer
	 */
	protected $priority = 1;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @var \TYPO3\CMS\Extbase\Reflection\Service
	 */
	protected $reflectionService;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager
	 * @return void
	 */
	public function injectPersistenceManager(\TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Reflection\Service $reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\CMS\Extbase\Reflection\Service $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * We can only convert if the $targetType is either tagged with entity or value object.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @return boolean
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function canConvertFrom($source, $targetType) {
		$isValueObject = is_subclass_of($targetType, 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractValueObject');
		$isEntity = is_subclass_of($targetType, 'TYPO3\\CMS\\Extbase\\DomainObject\\AbstractEntity');
		return $isEntity || $isValueObject;
	}

	/**
	 * All properties in the source array except __identity are sub-properties.
	 *
	 * @param mixed $source
	 * @return array
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getSourceChildPropertiesToBeConverted($source) {
		if (is_string($source)) {
			return array();
		}
		if (isset($source['__identity'])) {
			unset($source['__identity']);
		}
		return $source;
	}

	/**
	 * The type of a property is determined by the reflection service.
	 *
	 * @param string $targetType
	 * @param string $propertyName
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException
	 * @return string
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function getTypeOfChildProperty($targetType, $propertyName, \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration) {
		$configuredTargetType = $configuration->getConfigurationFor($propertyName)->getConfigurationValue('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\PersistentObjectConverter', self::CONFIGURATION_TARGET_TYPE);
		if ($configuredTargetType !== NULL) {
			return $configuredTargetType;
		}
		$schema = $this->reflectionService->getClassSchema($targetType);
		if (!$schema->hasProperty($propertyName)) {
			throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException(((('Property "' . $propertyName) . '" was not found in target object of type "') . $targetType) . '".', 1297978366);
		}
		$propertyInformation = $schema->getProperty($propertyName);
		return $propertyInformation['type'] . ($propertyInformation['elementType'] !== NULL ? ('<' . $propertyInformation['elementType']) . '>' : '');
	}

	/**
	 * Convert an object from $source to an entity or a value object.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @throws \InvalidArgumentException
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException
	 * @return object the target type
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		if (is_array($source)) {
			$object = $this->handleArrayData($source, $targetType, $convertedChildProperties, $configuration);
		} elseif (is_string($source)) {
			$object = $this->fetchObjectFromPersistence($source, $targetType);
		} else {
			throw new \InvalidArgumentException('Only strings and arrays are accepted.', 1305630314);
		}
		foreach ($convertedChildProperties as $propertyName => $propertyValue) {
			$result = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($object, $propertyName, $propertyValue);
			if ($result === FALSE) {
				throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException(((('Property "' . $propertyName) . '" could not be set in target object of type "') . $targetType) . '".', 1297935345);
			}
		}
		return $object;
	}

	/**
	 * Handle the case if $source is an array.
	 *
	 * @param array $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidPropertyMappingConfigurationException
	 * @return object
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function handleArrayData(array $source, $targetType, array &$convertedChildProperties, \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL) {
		if (isset($source['__identity'])) {
			$object = $this->fetchObjectFromPersistence($source['__identity'], $targetType);
			if (count($source) > 1) {
				if ($configuration === NULL || $configuration->getConfigurationValue('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\PersistentObjectConverter', self::CONFIGURATION_MODIFICATION_ALLOWED) !== TRUE) {
					throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidPropertyMappingConfigurationException('Modification of persistent objects not allowed. To enable this, you need to set the PropertyMappingConfiguration Value "CONFIGURATION_MODIFICATION_ALLOWED" to TRUE.', 1297932028);
				}
				$object = clone $object;
			}
			return $object;
		} else {
			if ($configuration === NULL || $configuration->getConfigurationValue('TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\PersistentObjectConverter', self::CONFIGURATION_CREATION_ALLOWED) !== TRUE) {
				throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidPropertyMappingConfigurationException('Creation of objects not allowed. To enable this, you need to set the PropertyMappingConfiguration Value "CONFIGURATION_CREATION_ALLOWED" to TRUE');
			}
			return $this->buildObject($convertedChildProperties, $targetType);
		}
	}

	/**
	 * Fetch an object from persistence layer.
	 *
	 * @param mixed $identity
	 * @param string $targetType
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException
	 * @return object
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function fetchObjectFromPersistence($identity, $targetType) {
		if (is_numeric($identity)) {
			$object = $this->persistenceManager->getObjectByIdentifier($identity, $targetType);
		} else {
			throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidSourceException(('The identity property "' . $identity) . '" is no UID.', 1297931020);
		}
		if ($object === NULL) {
			throw new \TYPO3\CMS\Extbase\Property\Exception\TargetNotFoundException(('Object with identity "' . print_r($identity, TRUE)) . '" not found.', 1297933823);
		}
		return $object;
	}

	/**
	 * Builds a new instance of $objectType with the given $possibleConstructorArgumentValues. If
	 * constructor argument values are missing from the given array the method
	 * looks for a default value in the constructor signature. Furthermore, the constructor arguments are removed from $possibleConstructorArgumentValues
	 *
	 * @param array &$possibleConstructorArgumentValues
	 * @param string $objectType
	 * @return object The created instance
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException if a required constructor argument is missing
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function buildObject(array &$possibleConstructorArgumentValues, $objectType) {
		try {
			$constructorSignature = $this->reflectionService->getMethodParameters($objectType, '__construct');
		} catch (\ReflectionException $reflectionException) {
			$constructorSignature = array();
		}
		$constructorArguments = array();
		foreach ($constructorSignature as $constructorArgumentName => $constructorArgumentInformation) {
			if (array_key_exists($constructorArgumentName, $possibleConstructorArgumentValues)) {
				$constructorArguments[] = $possibleConstructorArgumentValues[$constructorArgumentName];
				unset($possibleConstructorArgumentValues[$constructorArgumentName]);
			} elseif ($constructorArgumentInformation['optional'] === TRUE) {
				$constructorArguments[] = $constructorArgumentInformation['defaultValue'];
			} else {
				throw new \TYPO3\CMS\Extbase\Property\Exception\InvalidTargetException(((('Missing constructor argument "' . $constructorArgumentName) . '" for object of type "') . $objectType) . '".', 1268734872);
			}
		}
		return call_user_func_array(array($this->objectManager, 'create'), array_merge(array($objectType), $constructorArguments));
	}

}


?>