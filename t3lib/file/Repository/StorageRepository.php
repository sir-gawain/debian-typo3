<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Andreas Wolf <andreas.wolf@ikt-werk.de>
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
 * Repository for accessing the file mounts
 *
 * @author Andreas Wolf <andreas.wolf@ikt-werk.de>
 * @author Ingmar Schlecht <ingmar@typo3.org>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_file_Repository_StorageRepository extends t3lib_file_Repository_AbstractRepository {

	/**
	 * @var string
	 */
	protected $objectType = 't3lib_file_Storage';

	/**
	 * @var string
	 */
	protected $table = 'sys_file_storage';

	/**
	 * @var string
	 */
	protected $typeField = 'type';

	/**
	 * Finds storages by type.
	 *
	 * @param string $storageType
	 * @return t3lib_file_Storage[]
	 */
	public function findByStorageType($storageType) {
		$storageObjects = array();

		$whereClause = 'deleted=0 AND hidden=0';
		$whereClause .= ' AND ' . $this->typeField . ' = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($storageType, $this->table);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->table, $whereClause);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$storageObjects[] = $this->createDomainObject($row);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $storageObjects;
	}

	/**
	 * Returns a list of mountpoints that are available in the VFS.
	 * In case no storage exists this automatically created a storage for fileadmin/
	 *
	 * @return t3lib_file_Storage[]
	 */
	public function findAll() {
		$storageObjects = array();

		$whereClause = 'deleted=0 AND hidden=0';
		if ($this->type != '') {
			$whereClause .= ' AND ' . $this->typeField . ' = ' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->type, $this->table);
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->table, $whereClause);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$storageObjects[] = $this->createDomainObject($row);
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		if (count($storageObjects) === 0) {
			$this->createLocalStorage(
				'fileadmin/ (auto-created)',
				$GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'],
				'relative',
				'This is the local fileadmin/ directory. This storage mount has been created automatically by TYPO3.'
			);
			$storageObjects = self::findAll();
		}

		return $storageObjects;
	}

	/**
	 * Create the initial local storage base e.g. for the fileadmin/ directory.
	 *
	 * @param string $name
	 * @param string $basePath
	 * @param string $pathType
	 * @param string $description
	 * @return integer uid of the inserted record
	 */
	public function createLocalStorage($name, $basePath, $pathType, $description = '') {
		$field_values = array(
			'pid' => 0,
			'tstamp' => $GLOBALS['EXEC_TIME'],
			'crdate' => $GLOBALS['EXEC_TIME'],
			'name' => $name,
			'description' => $description,
			'driver' => 'Local',
			'configuration' => '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
				<T3FlexForms>
					<data>
						<sheet index="sDEF">
							<language index="lDEF">
								<field index="basePath">
									<value index="vDEF">' . rtrim($basePath, '/') . '/</value>
								</field>
								<field index="pathType">
									<value index="vDEF">' . $pathType . '</value>
								</field>
							</language>
						</sheet>
					</data>
				</T3FlexForms>',
			'is_online' => 1,
			'is_browsable' => 1,
			'is_public' => 1,
			'is_writable' => 1
		);

		$GLOBALS['TYPO3_DB']->exec_INSERTquery('sys_file_storage', $field_values);
		return (int)$GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	/**
	 * Creates an object managed by this repository.
	 *
	 * @param array $databaseRow
	 * @return t3lib_file_Storage
	 */
	protected function createDomainObject(array $databaseRow) {
		return $this->factory->getStorageObject($databaseRow['uid'], $databaseRow);
	}
}

?>