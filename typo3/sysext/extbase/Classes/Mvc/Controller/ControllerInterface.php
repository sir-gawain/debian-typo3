<?php
namespace TYPO3\CMS\Extbase\Mvc\Controller;

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
 * Interface for controllers
 *
 * @package Extbase
 * @version $ID:$
 * @api
 */
interface ControllerInterface
{
	/**
	 * Checks if the current request type is supported by the controller.
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request The current request
	 * @return boolean TRUE if this request type is supported, otherwise FALSE
	 * @api
	 */
	public function canProcessRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request);

	/**
	 * Processes a general request. The result can be returned by altering the given response.
	 *
	 * @param \TYPO3\CMS\Extbase\Mvc\RequestInterface $request The request object
	 * @param \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response The response, modified by the controller
	 * @return void
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException if the controller doesn't support the current request type
	 * @api
	 */
	public function processRequest(\TYPO3\CMS\Extbase\Mvc\RequestInterface $request, \TYPO3\CMS\Extbase\Mvc\ResponseInterface $response);

}

?>