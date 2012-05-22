<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 - 2009 Jochen Rieger (j.rieger@connecta.ag)
 *  (c) 2010 - 2011 Michael Miousse (michael.miousse@infoglobe.ca)
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 * This class provides Check External Links plugin implementation.
 *
 * @author Dimitri König <dk@cabag.ch>
 * @author Michael Miousse <michael.miousse@infoglobe.ca>
 * @package TYPO3
 * @subpackage linkvalidator
 */
class tx_linkvalidator_linktype_External extends tx_linkvalidator_linktype_Abstract {

	/**
	 * Cached list of the URLs, which were already checked for the current processing.
	 *
	 * @var array
	 */
	protected $urlReports = array();

	/**
	 * Cached list of all error parameters of the URLs, which were already checked for the current processing.
	 *
	 * @var array
	 */
	protected $urlErrorParams = array();

	/**
	 * List of headers to be used for metching an URL for the current processing
	 *
	 * @var array
	 */
	protected $additionalHeaders = array();


	/**
	 * Checks a given URL + /path/filename.ext for validity
	 *
	 * @param	string		$url: url to check
	 * @param	 array	   $softRefEntry: the softref entry which builds the context of that url
	 * @param	object		$reference:  parent instance of tx_linkvalidator_Processor
	 * @return	string		TRUE on success or FALSE on error
	 */
	public function checkLink($url, $softRefEntry, $reference) {
		$errorParams = array();
		$report = array();
		$additionalHeaders['User-Agent'] = 'User-Agent: Mozilla/5.0 TYPO3-linkvalidator';

		if (isset($this->urlReports[$url])) {
			if(!$this->urlReports[$url]) {
				if(is_array($this->urlErrorParams[$url])) {
					$this->setErrorParams($this->urlErrorParams[$url]);
				}
			}
			return $this->urlReports[$url];
		}

			// remove possible anchor from the url
		if (strrpos($url, '#') !== FALSE) {
			$url = substr($url, 0, strrpos($url, '#'));
		}

			// try to fetch the content of the URL
		$content = t3lib_div::getURL($url, 1, $additionalHeaders, $report);

		$tries = 0;
		$lastUrl = $url;
		while (($report['http_code'] == 301 || $report['http_code'] == 302
				|| $report['http_code'] == 303 || $report['http_code'] == 307)
				&& ($tries < 5)) {

				// split header into lines and find Location:
			$responseHeaders = t3lib_div::trimExplode(chr(10), $content, TRUE);
			foreach ($responseHeaders as $line) {
					// construct new URL
				if ((preg_match('/Location: ([^\r\n]+)/', $line, $location))) {
					if (isset($location[1])) {
						$parsedUrl = parse_url($location[1]);
						if (!isset($parsedUrl['host'])) {
								// the location did not contain a complete URI, build it!
							$parsedUrl = parse_url($lastUrl);
							$newUrl = $parsedUrl['scheme'] . '://' . (isset($parsedUrl['user']) ?
								$parsedUrl['user'] . (isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass'] : '')
								: '') . $parsedUrl['host'] . (
							isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '') . $location[1];
						} else {
							$newUrl = $location[1];
						}

						if ($lastUrl === $newUrl) {
							break 2;
						}
					} else {
						break 2;
					}
				}
			}

				// now try to fetch again
			$content = t3lib_div::getURL($newUrl, 1, $additionalHeaders, $report);
			$lastUrl = $newUrl;
			$tries++;
		}


		$response = TRUE;

			// analyze the response
		if ($report['error']) {
				// More cURL error codes can be found here:
				// http://curl.haxx.se/libcurl/c/libcurl-errors.html
			if ($report['lib'] === 'cURL' && $report['error'] === 28) {
				$errorParams['errorType'] = 'cURL28';
			} elseif ($report['lib'] === 'cURL' && $report['error'] === 22) {
				if (strstr($report['message'], '404')) {
					$errorParams['errorType'] = 404;
				} elseif(strstr($report['message'], '403')) {
					$errorParams['errorType'] = 403;
				} elseif(strstr($report['message'], '500')) {
					$errorParams['errorType'] = 500;
				}
			} elseif ($report['lib'] === 'cURL' && $report['error'] === 6) {
				$errorParams['errorType'] = 'cURL6';
			} elseif ($report['lib'] === 'cURL' && $report['error'] === 56) {
				$errorParams['errorType'] = 'cURL56';
			}

			$response = FALSE;
		}


			// special handling for more information
		if (($report['http_code'] == 301) || ($report['http_code'] == 302)
			|| ($report['http_code'] == 303) || ($report['http_code'] == 307)) {
				$errorParams['errorType'] = $report['http_code'];
				$errorParams['location'] = $lastUrl;
				$response = FALSE;
		}

		if ($report['http_code'] >= 300 && $response) {
			$errorParams['errorType'] = $report['http_code'];
			$response = FALSE;
		}

		if(!$response) {
			$this->setErrorParams($errorParams);
		}

		$this->urlReports[$url] = $response;
		$this->urlErrorParams[$url] = $errorParams;

		return $response;
	}

	/**
	 * Generate the localized error message from the error params saved from the parsing.
	 *
	 * @param   array    all parameters needed for the rendering of the error message
	 * @return  string    validation error message
	 */
	public function getErrorMessage($errorParams) {
		$errorType = $errorParams['errorType'];
		switch ($errorType) {
			case 300:
				$response = sprintf($GLOBALS['LANG']->getLL('list.report.externalerror'), $errorType);
				break;

			case 301:
			case 302:
			case 303:
			case 307:
				$response = sprintf($GLOBALS['LANG']->getLL('list.report.redirectloop'), $errorType, $errorParams['location']);
				break;

			case 403:
				$response = $GLOBALS['LANG']->getLL('list.report.pageforbidden403');
				break;

			case 404:
				$response = $GLOBALS['LANG']->getLL('list.report.pagenotfound404');
				break;

			case 500:
				$response = $GLOBALS['LANG']->getLL('list.report.internalerror500');
				break;

			case 'cURL6':
				$response = $GLOBALS['LANG']->getLL('list.report.couldnotresolvehost');
				break;

			case 'cURL28':
				$response = $GLOBALS['LANG']->getLL('list.report.timeout');
				break;

			case 'cURL56':
				$response = $GLOBALS['LANG']->getLL('list.report.errornetworkdata');
				break;

			default:
				$response = $GLOBALS['LANG']->getLL('list.report.noresponse');
		}

		return $response;
	}

	/**
	 * get the external type from the softRefParserObj result.
	 *
	 * @param   array	  $value: reference properties
	 * @param   string	 $type: current type
	 * @param   string	 $key: validator hook name
	 * @return  string	 fetched type
	 */
	public function fetchType($value, $type, $key) {
		preg_match_all('/((?:http|https|ftp|ftps))(?::\/\/)(?:[^\s<>]+)/i', $value['tokenValue'], $urls, PREG_PATTERN_ORDER);

		if (!empty($urls[0][0])) {
			$type = "external";
		}

		return $type;
	}

}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/linkvalidator/classes/linktypes/class.tx_linkvalidator_linktypes_external.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/linkvalidator/classes/linktypes/class.tx_linkvalidator_linktypes_external.php']);
}

?>
