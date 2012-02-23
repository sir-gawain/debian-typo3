<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Steffen Ritter <typo3steffen-ritter.net>
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
 * Interface for collection class being sortable
 *
 * This interface allows you to either define a callback implementing
 * your own sorting method and explicitly move an item from one position
 * to another.
 *
 * This assumes that entries are sortable and therefore a index can be assigned
 *
 * @author Steffen Ritter <typo3steffen-ritter.net>
 * @package TYPO3
 * @subpackage t3lib
 */
interface t3lib_collection_Sortable {

	/**
	 * Sorts collection via given callBackFunction
	 *
	 * The comparison function given as must return an integer less than, equal to, or greater than
	 * zero if the first argument is considered to be respectively less than, equal to, or greater than the second.
	 *
	 * @abstract
	 * @param $callbackFunction
	 * @see http://www.php.net/manual/en/function.usort.php
	 * @return void
	 */
	public function usort($callbackFunction);

	/**
	 * Moves the item within the collection
	 *
	 * The item at $currentPosition will be moved to
	 * $newPosition. Ommiting $newPosition will move to top.
	 *
	 * @abstract
	 * @param int $currentPosition
	 * @param int $newPosition
	 * @return void
	 */
	public function moveItemAt($currentPosition, $newPosition = 0);
}

?>