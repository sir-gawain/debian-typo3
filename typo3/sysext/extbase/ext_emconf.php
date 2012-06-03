<?php

########################################################################
# Extension Manager/Repository config file for ext "extbase".
#
# Auto generated 11-10-2011 11:46
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Extbase Framework for Extensions',
	'description' => 'A framework to build extensions in the style of FLOW3 by now.',
	'category' => 'misc',
	'author' => 'Extbase Team',
	'author_email' => '',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => 'top',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '6.0.0-dev',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-0.0.0',
			'typo3' => '6.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:400:{s:13:"ChangeLog.txt";s:4:"bc0f";s:16:"ext_autoload.php";s:4:"f252";s:12:"ext_icon.gif";s:4:"e922";s:17:"ext_localconf.php";s:4:"006e";s:14:"ext_tables.php";s:4:"6862";s:14:"ext_tables.sql";s:4:"d078";s:24:"ext_typoscript_setup.txt";s:4:"c702";s:22:"Classes/Dispatcher.php";s:4:"c031";s:21:"Classes/Exception.php";s:4:"c346";s:41:"Classes/Command/HelpCommandController.php";s:4:"f952";s:54:"Classes/Configuration/AbstractConfigurationManager.php";s:4:"8ada";s:53:"Classes/Configuration/BackendConfigurationManager.php";s:4:"ae5d";s:46:"Classes/Configuration/ConfigurationManager.php";s:4:"5774";s:55:"Classes/Configuration/ConfigurationManagerInterface.php";s:4:"b4d3";s:35:"Classes/Configuration/Exception.php";s:4:"31e6";s:54:"Classes/Configuration/FrontendConfigurationManager.php";s:4:"31f2";s:53:"Classes/Configuration/Exception/ContainerIsLocked.php";s:4:"113a";s:60:"Classes/Configuration/Exception/InvalidConfigurationType.php";s:4:"9379";s:46:"Classes/Configuration/Exception/NoSuchFile.php";s:4:"75ce";s:48:"Classes/Configuration/Exception/NoSuchOption.php";s:4:"f616";s:46:"Classes/Configuration/Exception/ParseError.php";s:4:"b75a";s:26:"Classes/Core/Bootstrap.php";s:4:"cabc";s:37:"Classes/Domain/Model/FrontendUser.php";s:4:"7bb3";s:42:"Classes/Domain/Model/FrontendUserGroup.php";s:4:"794d";s:57:"Classes/Domain/Repository/FrontendUserGroupRepository.php";s:4:"0197";s:52:"Classes/Domain/Repository/FrontendUserRepository.php";s:4:"ce1e";s:45:"Classes/DomainObject/AbstractDomainObject.php";s:4:"c4d7";s:39:"Classes/DomainObject/AbstractEntity.php";s:4:"4b00";s:44:"Classes/DomainObject/AbstractValueObject.php";s:4:"7342";s:46:"Classes/DomainObject/DomainObjectInterface.php";s:4:"d689";s:23:"Classes/Error/Error.php";s:4:"5d46";s:25:"Classes/Error/Message.php";s:4:"50cb";s:24:"Classes/Error/Notice.php";s:4:"87e0";s:24:"Classes/Error/Result.php";s:4:"b4b3";s:25:"Classes/Error/Warning.php";s:4:"35ca";s:26:"Classes/MVC/Dispatcher.php";s:4:"891c";s:25:"Classes/MVC/Exception.php";s:4:"606f";s:23:"Classes/MVC/Request.php";s:4:"58ef";s:39:"Classes/MVC/RequestHandlerInterface.php";s:4:"620c";s:38:"Classes/MVC/RequestHandlerResolver.php";s:4:"860e";s:32:"Classes/MVC/RequestInterface.php";s:4:"d17e";s:24:"Classes/MVC/Response.php";s:4:"0069";s:33:"Classes/MVC/ResponseInterface.php";s:4:"0ab1";s:27:"Classes/MVC/CLI/Command.php";s:4:"1d10";s:45:"Classes/MVC/CLI/CommandArgumentDefinition.php";s:4:"d011";s:34:"Classes/MVC/CLI/CommandManager.php";s:4:"ef4b";s:27:"Classes/MVC/CLI/Request.php";s:4:"ad3c";s:34:"Classes/MVC/CLI/RequestBuilder.php";s:4:"40ca";s:34:"Classes/MVC/CLI/RequestHandler.php";s:4:"15d1";s:28:"Classes/MVC/CLI/Response.php";s:4:"e090";s:45:"Classes/MVC/Controller/AbstractController.php";s:4:"ce93";s:43:"Classes/MVC/Controller/ActionController.php";s:4:"74c5";s:35:"Classes/MVC/Controller/Argument.php";s:4:"e26f";s:40:"Classes/MVC/Controller/ArgumentError.php";s:4:"28c2";s:36:"Classes/MVC/Controller/Arguments.php";s:4:"eda9";s:45:"Classes/MVC/Controller/ArgumentsValidator.php";s:4:"5bd5";s:44:"Classes/MVC/Controller/CommandController.php";s:4:"38bf";s:53:"Classes/MVC/Controller/CommandControllerInterface.php";s:4:"22c7";s:44:"Classes/MVC/Controller/ControllerContext.php";s:4:"42c7";s:46:"Classes/MVC/Controller/ControllerInterface.php";s:4:"c450";s:40:"Classes/MVC/Controller/FlashMessages.php";s:4:"f28e";s:58:"Classes/MVC/Controller/MvcPropertyMappingConfiguration.php";s:4:"b6bc";s:69:"Classes/MVC/Controller/Exception/RequiredArgumentMissingException.php";s:4:"8a5a";s:52:"Classes/MVC/Exception/AmbiguousCommandIdentifier.php";s:4:"8d97";s:33:"Classes/MVC/Exception/Command.php";s:4:"96d0";s:38:"Classes/MVC/Exception/InfiniteLoop.php";s:4:"5984";s:43:"Classes/MVC/Exception/InvalidActionName.php";s:4:"baec";s:47:"Classes/MVC/Exception/InvalidArgumentMixing.php";s:4:"3c80";s:45:"Classes/MVC/Exception/InvalidArgumentName.php";s:4:"55a6";s:45:"Classes/MVC/Exception/InvalidArgumentType.php";s:4:"0b9c";s:46:"Classes/MVC/Exception/InvalidArgumentValue.php";s:4:"a836";s:50:"Classes/MVC/Exception/InvalidCommandIdentifier.php";s:4:"6f5f";s:43:"Classes/MVC/Exception/InvalidController.php";s:4:"f0b0";s:47:"Classes/MVC/Exception/InvalidControllerName.php";s:4:"7b4c";s:46:"Classes/MVC/Exception/InvalidExtensionName.php";s:4:"865a";s:39:"Classes/MVC/Exception/InvalidMarker.php";s:4:"4a5e";s:48:"Classes/MVC/Exception/InvalidOrNoRequestHash.php";s:4:"beff";s:46:"Classes/MVC/Exception/InvalidRequestMethod.php";s:4:"508f";s:44:"Classes/MVC/Exception/InvalidRequestType.php";s:4:"def7";s:49:"Classes/MVC/Exception/InvalidTemplateResource.php";s:4:"bbb7";s:43:"Classes/MVC/Exception/InvalidUriPattern.php";s:4:"cee0";s:43:"Classes/MVC/Exception/InvalidViewHelper.php";s:4:"686d";s:38:"Classes/MVC/Exception/NoSuchAction.php";s:4:"1889";s:40:"Classes/MVC/Exception/NoSuchArgument.php";s:4:"1bec";s:39:"Classes/MVC/Exception/NoSuchCommand.php";s:4:"0f0b";s:42:"Classes/MVC/Exception/NoSuchController.php";s:4:"baa6";s:49:"Classes/MVC/Exception/RequiredArgumentMissing.php";s:4:"0834";s:36:"Classes/MVC/Exception/StopAction.php";s:4:"d3aa";s:48:"Classes/MVC/Exception/UnsupportedRequestType.php";s:4:"27ce";s:33:"Classes/MVC/View/AbstractView.php";s:4:"3960";s:30:"Classes/MVC/View/EmptyView.php";s:4:"ec6a";s:33:"Classes/MVC/View/NotFoundView.php";s:4:"5fe7";s:34:"Classes/MVC/View/ViewInterface.php";s:4:"dc07";s:42:"Classes/MVC/Web/AbstractRequestHandler.php";s:4:"f39b";s:41:"Classes/MVC/Web/BackendRequestHandler.php";s:4:"31f7";s:42:"Classes/MVC/Web/FrontendRequestHandler.php";s:4:"33bd";s:27:"Classes/MVC/Web/Request.php";s:4:"d840";s:34:"Classes/MVC/Web/RequestBuilder.php";s:4:"d7e7";s:28:"Classes/MVC/Web/Response.php";s:4:"7156";s:38:"Classes/MVC/Web/Routing/UriBuilder.php";s:4:"51e1";s:28:"Classes/Object/Exception.php";s:4:"2f66";s:26:"Classes/Object/Manager.php";s:4:"4026";s:32:"Classes/Object/ObjectManager.php";s:4:"f53d";s:41:"Classes/Object/ObjectManagerInterface.php";s:4:"f2dc";s:38:"Classes/Object/Container/ClassInfo.php";s:4:"a386";s:43:"Classes/Object/Container/ClassInfoCache.php";s:4:"052c";s:45:"Classes/Object/Container/ClassInfoFactory.php";s:4:"38ba";s:38:"Classes/Object/Container/Container.php";s:4:"a8ac";s:69:"Classes/Object/Container/Exception/CannotInitializeCacheException.php";s:4:"4258";s:70:"Classes/Object/Container/Exception/TooManyRecursionLevelsException.php";s:4:"cd39";s:61:"Classes/Object/Container/Exception/UnknownObjectException.php";s:4:"d18e";s:46:"Classes/Object/Exception/CannotBuildObject.php";s:4:"8070";s:53:"Classes/Object/Exception/CannotReconstituteObject.php";s:4:"e8cc";s:41:"Classes/Object/Exception/InvalidClass.php";s:4:"d226";s:42:"Classes/Object/Exception/InvalidObject.php";s:4:"cc16";s:55:"Classes/Object/Exception/InvalidObjectConfiguration.php";s:4:"e82a";s:52:"Classes/Object/Exception/ObjectAlreadyRegistered.php";s:4:"eecb";s:41:"Classes/Object/Exception/UnknownClass.php";s:4:"d10a";s:45:"Classes/Object/Exception/UnknownInterface.php";s:4:"22a4";s:51:"Classes/Object/Exception/UnresolvedDependencies.php";s:4:"7d47";s:39:"Classes/Object/Exception/WrongScope.php";s:4:"cc92";s:31:"Classes/Persistence/Backend.php";s:4:"e944";s:40:"Classes/Persistence/BackendInterface.php";s:4:"88db";s:33:"Classes/Persistence/Exception.php";s:4:"1713";s:35:"Classes/Persistence/IdentityMap.php";s:4:"0073";s:40:"Classes/Persistence/LazyLoadingProxy.php";s:4:"e868";s:41:"Classes/Persistence/LazyObjectStorage.php";s:4:"2cd9";s:48:"Classes/Persistence/LoadingStrategyInterface.php";s:4:"fa0d";s:31:"Classes/Persistence/Manager.php";s:4:"11ce";s:40:"Classes/Persistence/ManagerInterface.php";s:4:"bf22";s:49:"Classes/Persistence/ObjectMonitoringInterface.php";s:4:"6054";s:37:"Classes/Persistence/ObjectStorage.php";s:4:"c75e";s:36:"Classes/Persistence/PropertyType.php";s:4:"f62a";s:29:"Classes/Persistence/Query.php";s:4:"a20b";s:36:"Classes/Persistence/QueryFactory.php";s:4:"74fe";s:45:"Classes/Persistence/QueryFactoryInterface.php";s:4:"900e";s:38:"Classes/Persistence/QueryInterface.php";s:4:"4edc";s:35:"Classes/Persistence/QueryResult.php";s:4:"af68";s:44:"Classes/Persistence/QueryResultInterface.php";s:4:"843d";s:46:"Classes/Persistence/QuerySettingsInterface.php";s:4:"1e52";s:34:"Classes/Persistence/Repository.php";s:4:"7128";s:43:"Classes/Persistence/RepositoryInterface.php";s:4:"55e7";s:31:"Classes/Persistence/Session.php";s:4:"43b4";s:42:"Classes/Persistence/Typo3QuerySettings.php";s:4:"cd0d";s:56:"Classes/Persistence/Exception/CleanStateNotMemorized.php";s:4:"7689";s:51:"Classes/Persistence/Exception/IllegalObjectType.php";s:4:"73e7";s:46:"Classes/Persistence/Exception/InvalidClass.php";s:4:"761b";s:60:"Classes/Persistence/Exception/InvalidNumberOfConstraints.php";s:4:"3a38";s:53:"Classes/Persistence/Exception/InvalidPropertyType.php";s:4:"cd9a";s:48:"Classes/Persistence/Exception/MissingBackend.php";s:4:"1d0d";s:53:"Classes/Persistence/Exception/RepositoryException.php";s:4:"1aba";s:42:"Classes/Persistence/Exception/TooDirty.php";s:4:"3347";s:57:"Classes/Persistence/Exception/UnexpectedTypeException.php";s:4:"5b57";s:47:"Classes/Persistence/Exception/UnknownObject.php";s:4:"21a6";s:51:"Classes/Persistence/Exception/UnsupportedMethod.php";s:4:"9915";s:50:"Classes/Persistence/Exception/UnsupportedOrder.php";s:4:"f4b9";s:53:"Classes/Persistence/Exception/UnsupportedRelation.php";s:4:"6bc0";s:40:"Classes/Persistence/Mapper/ColumnMap.php";s:4:"fee9";s:38:"Classes/Persistence/Mapper/DataMap.php";s:4:"2b19";s:45:"Classes/Persistence/Mapper/DataMapFactory.php";s:4:"d600";s:41:"Classes/Persistence/Mapper/DataMapper.php";s:4:"a3c7";s:40:"Classes/Persistence/QOM/AndInterface.php";s:4:"f72d";s:45:"Classes/Persistence/QOM/BindVariableValue.php";s:4:"454e";s:54:"Classes/Persistence/QOM/BindVariableValueInterface.php";s:4:"822e";s:38:"Classes/Persistence/QOM/Comparison.php";s:4:"74e7";s:47:"Classes/Persistence/QOM/ComparisonInterface.php";s:4:"f50a";s:38:"Classes/Persistence/QOM/Constraint.php";s:4:"6e7b";s:47:"Classes/Persistence/QOM/ConstraintInterface.php";s:4:"c106";s:42:"Classes/Persistence/QOM/DynamicOperand.php";s:4:"29d3";s:51:"Classes/Persistence/QOM/DynamicOperandInterface.php";s:4:"981b";s:45:"Classes/Persistence/QOM/EquiJoinCondition.php";s:4:"29b1";s:54:"Classes/Persistence/QOM/EquiJoinConditionInterface.php";s:4:"b9f5";s:32:"Classes/Persistence/QOM/Join.php";s:4:"d270";s:50:"Classes/Persistence/QOM/JoinConditionInterface.php";s:4:"63c8";s:41:"Classes/Persistence/QOM/JoinInterface.php";s:4:"c3a5";s:38:"Classes/Persistence/QOM/LogicalAnd.php";s:4:"4b22";s:38:"Classes/Persistence/QOM/LogicalNot.php";s:4:"aba3";s:37:"Classes/Persistence/QOM/LogicalOr.php";s:4:"3476";s:37:"Classes/Persistence/QOM/LowerCase.php";s:4:"15a8";s:46:"Classes/Persistence/QOM/LowerCaseInterface.php";s:4:"215c";s:40:"Classes/Persistence/QOM/NotInterface.php";s:4:"8d10";s:35:"Classes/Persistence/QOM/Operand.php";s:4:"a635";s:44:"Classes/Persistence/QOM/OperandInterface.php";s:4:"39ff";s:39:"Classes/Persistence/QOM/OrInterface.php";s:4:"b109";s:36:"Classes/Persistence/QOM/Ordering.php";s:4:"e7f4";s:45:"Classes/Persistence/QOM/OrderingInterface.php";s:4:"b9f9";s:41:"Classes/Persistence/QOM/PropertyValue.php";s:4:"96fb";s:50:"Classes/Persistence/QOM/PropertyValueInterface.php";s:4:"6526";s:62:"Classes/Persistence/QOM/QueryObjectModelConstantsInterface.php";s:4:"33bb";s:51:"Classes/Persistence/QOM/QueryObjectModelFactory.php";s:4:"e6fc";s:60:"Classes/Persistence/QOM/QueryObjectModelFactoryInterface.php";s:4:"3b53";s:36:"Classes/Persistence/QOM/Selector.php";s:4:"59cb";s:45:"Classes/Persistence/QOM/SelectorInterface.php";s:4:"e75c";s:43:"Classes/Persistence/QOM/SourceInterface.php";s:4:"a397";s:37:"Classes/Persistence/QOM/Statement.php";s:4:"c978";s:41:"Classes/Persistence/QOM/StaticOperand.php";s:4:"69f4";s:50:"Classes/Persistence/QOM/StaticOperandInterface.php";s:4:"4e9b";s:37:"Classes/Persistence/QOM/UpperCase.php";s:4:"6646";s:46:"Classes/Persistence/QOM/UpperCaseInterface.php";s:4:"ea6b";s:48:"Classes/Persistence/Storage/BackendInterface.php";s:4:"ad7b";s:46:"Classes/Persistence/Storage/Typo3DbBackend.php";s:4:"ea9b";s:55:"Classes/Persistence/Storage/Exception/BadConstraint.php";s:4:"3a87";s:50:"Classes/Persistence/Storage/Exception/SqlError.php";s:4:"850b";s:30:"Classes/Property/Exception.php";s:4:"8465";s:27:"Classes/Property/Mapper.php";s:4:"4948";s:35:"Classes/Property/MappingResults.php";s:4:"8409";s:35:"Classes/Property/PropertyMapper.php";s:4:"bcf3";s:49:"Classes/Property/PropertyMappingConfiguration.php";s:4:"0d84";s:56:"Classes/Property/PropertyMappingConfigurationBuilder.php";s:4:"95e9";s:58:"Classes/Property/PropertyMappingConfigurationInterface.php";s:4:"7e48";s:43:"Classes/Property/TypeConverterInterface.php";s:4:"a78b";s:55:"Classes/Property/Exception/DuplicateObjectException.php";s:4:"34c9";s:62:"Classes/Property/Exception/DuplicateTypeConverterException.php";s:4:"a0d1";s:49:"Classes/Property/Exception/FormatNotSupported.php";s:4:"338e";s:58:"Classes/Property/Exception/FormatNotSupportedException.php";s:4:"d21d";s:46:"Classes/Property/Exception/InvalidDataType.php";s:4:"f280";s:55:"Classes/Property/Exception/InvalidDataTypeException.php";s:4:"60b7";s:44:"Classes/Property/Exception/InvalidFormat.php";s:4:"0bfa";s:53:"Classes/Property/Exception/InvalidFormatException.php";s:4:"0038";s:46:"Classes/Property/Exception/InvalidProperty.php";s:4:"e6fb";s:55:"Classes/Property/Exception/InvalidPropertyException.php";s:4:"6266";s:75:"Classes/Property/Exception/InvalidPropertyMappingConfigurationException.php";s:4:"3be5";s:44:"Classes/Property/Exception/InvalidSource.php";s:4:"ba13";s:53:"Classes/Property/Exception/InvalidSourceException.php";s:4:"a281";s:44:"Classes/Property/Exception/InvalidTarget.php";s:4:"6cd9";s:53:"Classes/Property/Exception/InvalidTargetException.php";s:4:"e666";s:54:"Classes/Property/Exception/TargetNotFoundException.php";s:4:"3a53";s:53:"Classes/Property/Exception/TypeConverterException.php";s:4:"9a90";s:56:"Classes/Property/TypeConverter/AbstractTypeConverter.php";s:4:"fbe4";s:49:"Classes/Property/TypeConverter/ArrayConverter.php";s:4:"9454";s:51:"Classes/Property/TypeConverter/BooleanConverter.php";s:4:"d59a";s:52:"Classes/Property/TypeConverter/DateTimeConverter.php";s:4:"eb93";s:49:"Classes/Property/TypeConverter/FloatConverter.php";s:4:"1c29";s:51:"Classes/Property/TypeConverter/IntegerConverter.php";s:4:"707d";s:57:"Classes/Property/TypeConverter/ObjectStorageConverter.php";s:4:"380e";s:60:"Classes/Property/TypeConverter/PersistentObjectConverter.php";s:4:"19f3";s:50:"Classes/Property/TypeConverter/StringConverter.php";s:4:"b8cd";s:38:"Classes/Reflection/ClassReflection.php";s:4:"913c";s:34:"Classes/Reflection/ClassSchema.php";s:4:"daf0";s:39:"Classes/Reflection/DocCommentParser.php";s:4:"6357";s:32:"Classes/Reflection/Exception.php";s:4:"8625";s:39:"Classes/Reflection/MethodReflection.php";s:4:"a739";s:35:"Classes/Reflection/ObjectAccess.php";s:4:"0f60";s:42:"Classes/Reflection/ParameterReflection.php";s:4:"284d";s:41:"Classes/Reflection/PropertyReflection.php";s:4:"95b8";s:30:"Classes/Reflection/Service.php";s:4:"234e";s:52:"Classes/Reflection/Exception/InvalidPropertyType.php";s:4:"02d6";s:63:"Classes/Reflection/Exception/PropertyNotAccessibleException.php";s:4:"0ddf";s:45:"Classes/Reflection/Exception/UnknownClass.php";s:4:"6184";s:30:"Classes/Security/Exception.php";s:4:"2d15";s:47:"Classes/Security/Channel/RequestHashService.php";s:4:"2f08";s:45:"Classes/Security/Cryptography/HashService.php";s:4:"e0aa";s:63:"Classes/Security/Exception/InvalidArgumentForHashGeneration.php";s:4:"856e";s:70:"Classes/Security/Exception/InvalidArgumentForRequestHashGeneration.php";s:4:"9f05";s:60:"Classes/Security/Exception/SyntacticallyWrongRequestHash.php";s:4:"89bd";s:32:"Classes/Service/CacheService.php";s:4:"7ce9";s:36:"Classes/Service/ExtensionService.php";s:4:"5a1b";s:35:"Classes/Service/FlexFormService.php";s:4:"f4d1";s:39:"Classes/Service/TypeHandlingService.php";s:4:"159b";s:37:"Classes/Service/TypoScriptService.php";s:4:"c1b5";s:33:"Classes/SignalSlot/Dispatcher.php";s:4:"2a92";s:53:"Classes/SignalSlot/Exception/InvalidSlotException.php";s:4:"a348";s:26:"Classes/Utility/Arrays.php";s:4:"5dfd";s:25:"Classes/Utility/Cache.php";s:4:"c94b";s:31:"Classes/Utility/ClassLoader.php";s:4:"bab2";s:44:"Classes/Utility/ExtbaseRequirementsCheck.php";s:4:"c9f2";s:29:"Classes/Utility/Extension.php";s:4:"100a";s:37:"Classes/Utility/FrontendSimulator.php";s:4:"bd64";s:32:"Classes/Utility/Localization.php";s:4:"e07b";s:32:"Classes/Utility/TypeHandling.php";s:4:"1b2c";s:30:"Classes/Utility/TypoScript.php";s:4:"951b";s:28:"Classes/Validation/Error.php";s:4:"247a";s:32:"Classes/Validation/Exception.php";s:4:"44f8";s:36:"Classes/Validation/PropertyError.php";s:4:"e6e2";s:40:"Classes/Validation/ValidatorResolver.php";s:4:"aaf2";s:47:"Classes/Validation/Exception/InvalidSubject.php";s:4:"5040";s:63:"Classes/Validation/Exception/InvalidValidationConfiguration.php";s:4:"9cfa";s:57:"Classes/Validation/Exception/InvalidValidationOptions.php";s:4:"a3a8";s:48:"Classes/Validation/Exception/NoSuchValidator.php";s:4:"64f4";s:49:"Classes/Validation/Exception/NoValidatorFound.php";s:4:"be96";s:59:"Classes/Validation/Validator/AbstractCompositeValidator.php";s:4:"957a";s:56:"Classes/Validation/Validator/AbstractObjectValidator.php";s:4:"c43e";s:50:"Classes/Validation/Validator/AbstractValidator.php";s:4:"5c28";s:54:"Classes/Validation/Validator/AlphanumericValidator.php";s:4:"ae25";s:53:"Classes/Validation/Validator/ConjunctionValidator.php";s:4:"6cb0";s:50:"Classes/Validation/Validator/DateTimeValidator.php";s:4:"3716";s:53:"Classes/Validation/Validator/DisjunctionValidator.php";s:4:"52b0";s:54:"Classes/Validation/Validator/EmailAddressValidator.php";s:4:"04bb";s:47:"Classes/Validation/Validator/FloatValidator.php";s:4:"6e44";s:55:"Classes/Validation/Validator/GenericObjectValidator.php";s:4:"27be";s:49:"Classes/Validation/Validator/IntegerValidator.php";s:4:"f70f";s:50:"Classes/Validation/Validator/NotEmptyValidator.php";s:4:"92fa";s:53:"Classes/Validation/Validator/NumberRangeValidator.php";s:4:"086a";s:48:"Classes/Validation/Validator/NumberValidator.php";s:4:"ab3d";s:57:"Classes/Validation/Validator/ObjectValidatorInterface.php";s:4:"3e83";s:45:"Classes/Validation/Validator/RawValidator.php";s:4:"f7ed";s:59:"Classes/Validation/Validator/RegularExpressionValidator.php";s:4:"95aa";s:54:"Classes/Validation/Validator/StringLengthValidator.php";s:4:"6885";s:48:"Classes/Validation/Validator/StringValidator.php";s:4:"4102";s:46:"Classes/Validation/Validator/TextValidator.php";s:4:"d641";s:51:"Classes/Validation/Validator/ValidatorInterface.php";s:4:"3cf9";s:24:"Documentation/README.txt";s:4:"35d4";s:43:"Resources/Private/Language/locallang_db.xlf";s:4:"e48d";s:48:"Resources/Private/MVC/NotFoundView_Template.html";s:4:"d5a3";s:31:"Scripts/CommandLineLauncher.php";s:4:"75e3";s:22:"Tests/BaseTestCase.php";s:4:"3ad3";s:30:"Tests/SeleniumBaseTestCase.php";s:4:"6769";s:27:"Tests/Unit/BaseTestCase.php";s:4:"9823";s:61:"Tests/Unit/Configuration/AbstractConfigurationManagerTest.php";s:4:"e977";s:60:"Tests/Unit/Configuration/BackendConfigurationManagerTest.php";s:4:"9c89";s:61:"Tests/Unit/Configuration/FrontendConfigurationManagerTest.php";s:4:"ca01";s:46:"Tests/Unit/DomainObject/AbstractEntityTest.php";s:4:"4539";s:30:"Tests/Unit/Error/ErrorTest.php";s:4:"6252";s:31:"Tests/Unit/Error/ResultTest.php";s:4:"31b4";s:40:"Tests/Unit/Fixtures/ClassWithSetters.php";s:4:"4a51";s:54:"Tests/Unit/Fixtures/ClassWithSettersAndConstructor.php";s:4:"414b";s:34:"Tests/Unit/Fixtures/DummyClass.php";s:4:"e122";s:30:"Tests/Unit/Fixtures/Entity.php";s:4:"09e9";s:40:"Tests/Unit/Fixtures/SecondDummyClass.php";s:4:"8758";s:33:"Tests/Unit/MVC/DispatcherTest.php";s:4:"843b";s:30:"Tests/Unit/MVC/RequestTest.php";s:4:"1e54";s:41:"Tests/Unit/MVC/CLI/CommandManagerTest.php";s:4:"138f";s:34:"Tests/Unit/MVC/CLI/CommandTest.php";s:4:"686d";s:41:"Tests/Unit/MVC/CLI/RequestBuilderTest.php";s:4:"def9";s:34:"Tests/Unit/MVC/CLI/RequestTest.php";s:4:"dbc0";s:52:"Tests/Unit/MVC/Controller/AbstractControllerTest.php";s:4:"7cd2";s:50:"Tests/Unit/MVC/Controller/ActionControllerTest.php";s:4:"cb24";s:65:"Tests/Unit/MVC/Controller/ArgumentBehaviorBeforeExtbase14Test.php";s:4:"014a";s:42:"Tests/Unit/MVC/Controller/ArgumentTest.php";s:4:"f226";s:43:"Tests/Unit/MVC/Controller/ArgumentsTest.php";s:4:"75e6";s:51:"Tests/Unit/MVC/Controller/CommandControllerTest.php";s:4:"5a6a";s:60:"Tests/Unit/MVC/Fixture/CLI/Command/MockCommandController.php";s:4:"9601";s:41:"Tests/Unit/MVC/Web/RequestBuilderTest.php";s:4:"88b2";s:34:"Tests/Unit/MVC/Web/RequestTest.php";s:4:"3ee0";s:45:"Tests/Unit/MVC/Web/Routing/UriBuilderTest.php";s:4:"8bbc";s:52:"Tests/Unit/Object/Container/ClassInfoFactoryTest.php";s:4:"e450";s:45:"Tests/Unit/Object/Container/ContainerTest.php";s:4:"f461";s:52:"Tests/Unit/Object/Container/Fixtures/Testclasses.php";s:4:"64ed";s:44:"Tests/Unit/Persistence/ObjectStorageTest.php";s:4:"f76b";s:42:"Tests/Unit/Persistence/QueryResultTest.php";s:4:"97e7";s:36:"Tests/Unit/Persistence/QueryTest.php";s:4:"7a11";s:41:"Tests/Unit/Persistence/RepositoryTest.php";s:4:"a786";s:38:"Tests/Unit/Persistence/SessionTest.php";s:4:"908a";s:52:"Tests/Unit/Persistence/Mapper/DataMapFactoryTest.php";s:4:"db40";s:53:"Tests/Unit/Persistence/Storage/Typo3DbBackendTest.php";s:4:"610a";s:42:"Tests/Unit/Property/PropertyMapperTest.php";s:4:"9f7d";s:63:"Tests/Unit/Property/PropertyMappingConfigurationBuilderTest.php";s:4:"703b";s:56:"Tests/Unit/Property/PropertyMappingConfigurationTest.php";s:4:"c5bf";s:56:"Tests/Unit/Property/TypeConverter/ArrayConverterTest.php";s:4:"f268";s:58:"Tests/Unit/Property/TypeConverter/BooleanConverterTest.php";s:4:"978d";s:59:"Tests/Unit/Property/TypeConverter/DateTimeConverterTest.php";s:4:"92a7";s:56:"Tests/Unit/Property/TypeConverter/FloatConverterTest.php";s:4:"ff29";s:58:"Tests/Unit/Property/TypeConverter/IntegerConverterTest.php";s:4:"1445";s:67:"Tests/Unit/Property/TypeConverter/PersistentObjectConverterTest.php";s:4:"750d";s:57:"Tests/Unit/Property/TypeConverter/StringConverterTest.php";s:4:"81ef";s:42:"Tests/Unit/Reflection/ObjectAccessTest.php";s:4:"3289";s:37:"Tests/Unit/Reflection/ServiceTest.php";s:4:"135c";s:50:"Tests/Unit/Reflection/Fixture/ArrayAccessClass.php";s:4:"9afd";s:65:"Tests/Unit/Reflection/Fixture/DummyClassWithGettersAndSetters.php";s:4:"fa70";s:54:"Tests/Unit/Security/Channel/RequestHashServiceTest.php";s:4:"acdc";s:52:"Tests/Unit/Security/Cryptography/HashServiceTest.php";s:4:"f27a";s:39:"Tests/Unit/Service/CacheServiceTest.php";s:4:"1393";s:43:"Tests/Unit/Service/ExtensionServiceTest.php";s:4:"c68a";s:42:"Tests/Unit/Service/FlexFormServiceTest.php";s:4:"d52f";s:46:"Tests/Unit/Service/TypeHandlingServiceTest.php";s:4:"11d2";s:44:"Tests/Unit/Service/TypoScriptServiceTest.php";s:4:"b628";s:40:"Tests/Unit/SignalSlot/DispatcherTest.php";s:4:"91cf";s:36:"Tests/Unit/Utility/ExtensionTest.php";s:4:"9d90";s:39:"Tests/Unit/Utility/LocalizationTest.php";s:4:"079a";s:47:"Tests/Unit/Validation/ValidatorResolverTest.php";s:4:"e62d";s:61:"Tests/Unit/Validation/Validator/AbstractValidatorTestcase.php";s:4:"41be";s:61:"Tests/Unit/Validation/Validator/AlphanumericValidatorTest.php";s:4:"8aca";s:60:"Tests/Unit/Validation/Validator/ConjunctionValidatorTest.php";s:4:"3736";s:60:"Tests/Unit/Validation/Validator/DisjunctionValidatorTest.php";s:4:"a7db";s:61:"Tests/Unit/Validation/Validator/EmailAddressValidatorTest.php";s:4:"e50e";s:54:"Tests/Unit/Validation/Validator/FloatValidatorTest.php";s:4:"cd99";s:62:"Tests/Unit/Validation/Validator/GenericObjectValidatorTest.php";s:4:"2b31";s:56:"Tests/Unit/Validation/Validator/IntegerValidatorTest.php";s:4:"3c47";s:57:"Tests/Unit/Validation/Validator/NotEmptyValidatorTest.php";s:4:"fed6";s:60:"Tests/Unit/Validation/Validator/NumberRangeValidatorTest.php";s:4:"21f4";s:55:"Tests/Unit/Validation/Validator/NumberValidatorTest.php";s:4:"adb1";s:52:"Tests/Unit/Validation/Validator/RawValidatorTest.php";s:4:"db3e";s:66:"Tests/Unit/Validation/Validator/RegularExpressionValidatorTest.php";s:4:"5092";s:61:"Tests/Unit/Validation/Validator/StringLengthValidatorTest.php";s:4:"3c81";s:55:"Tests/Unit/Validation/Validator/StringValidatorTest.php";s:4:"5cf0";s:53:"Tests/Unit/Validation/Validator/TextValidatorTest.php";s:4:"5d6e";s:77:"Tests/Unit/Validation/Validator/BeforeExtbase14/AlphanumericValidatorTest.php";s:4:"8dbc";s:76:"Tests/Unit/Validation/Validator/BeforeExtbase14/ConjunctionValidatorTest.php";s:4:"893c";s:73:"Tests/Unit/Validation/Validator/BeforeExtbase14/DateTimeValidatorTest.php";s:4:"a627";s:77:"Tests/Unit/Validation/Validator/BeforeExtbase14/EmailAddressValidatorTest.php";s:4:"e2b7";s:70:"Tests/Unit/Validation/Validator/BeforeExtbase14/FloatValidatorTest.php";s:4:"d9a3";s:78:"Tests/Unit/Validation/Validator/BeforeExtbase14/GenericObjectValidatorTest.php";s:4:"af4e";s:72:"Tests/Unit/Validation/Validator/BeforeExtbase14/IntegerValidatorTest.php";s:4:"a0c5";s:73:"Tests/Unit/Validation/Validator/BeforeExtbase14/NotEmptyValidatorTest.php";s:4:"6db5";s:76:"Tests/Unit/Validation/Validator/BeforeExtbase14/NumberRangeValidatorTest.php";s:4:"7eac";s:71:"Tests/Unit/Validation/Validator/BeforeExtbase14/NumberValidatorTest.php";s:4:"96f9";s:68:"Tests/Unit/Validation/Validator/BeforeExtbase14/RawValidatorTest.php";s:4:"a66b";s:82:"Tests/Unit/Validation/Validator/BeforeExtbase14/RegularExpressionValidatorTest.php";s:4:"74fd";s:77:"Tests/Unit/Validation/Validator/BeforeExtbase14/StringLengthValidatorTest.php";s:4:"ab58";s:69:"Tests/Unit/Validation/Validator/BeforeExtbase14/TextValidatorTest.php";s:4:"a246";}',
);

?>