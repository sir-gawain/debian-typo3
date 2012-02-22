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
 * The Extbase Persistence Manager interface
 *
 * @package Extbase
 * @subpackage Persistence
 * @version $Id: ManagerInterface.php 1729 2009-11-25 21:37:20Z stucki $
 * @api
 */
interface Tx_Extbase_Persistence_ManagerInterface {

	/**
	 * Returns the current persistence session
	 *
	 * @return Tx_Extbase_Persistence_Session
	 */
	public function getSession();

	/**
	 * Returns the persistence backend
	 *
	 * @return Tx_Extbase_Persistence_BackendInterface
	 */
	public function getBackend();

	/**
	 * Commits new objects and changes to objects in the current persistence
	 * session into the backend
	 *
	 * @return void
	 * @api
	 */
	public function persistAll();
}
?>