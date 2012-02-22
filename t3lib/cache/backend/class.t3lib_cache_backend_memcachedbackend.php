<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2011 Ingo Renner <ingo@typo3.org>
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
 * A caching backend which stores cache entries by using Memcached.
 *
 * This backend uses the following types of Memcache keys:
 * - tag_xxx
 *   xxx is tag name, value is array of associated identifiers identifier. This
 *   is "forward" tag index. It is mainly used for obtaining content by tag
 *   (get identifier by tag -> get content by identifier)
 * - ident_xxx
 *   xxx is identifier, value is array of associated tags. This is "reverse" tag
 *   index. It provides quick access for all tags associated with this identifier
 *   and used when removing the identifier
 *
 * Each key is prepended with a prefix. By default prefix consists from two parts
 * separated by underscore character and ends in yet another underscore character:
 * - "TYPO3"
 * - Current site path obtained from the PATH_site constant
 * This prefix makes sure that keys from the different installations do not
 * conflict.
 *
 * Note: When using the Memcached backend to store values of more than ~1 MB,
 * the data will be split into chunks to make them fit into the memcached limits.
 *
 * This file is a backport from FLOW3 by Ingo Renner.
 *
 * @package TYPO3
 * @subpackage t3lib_cache
 * @api
 * @version $Id$
 */
class t3lib_cache_backend_MemcachedBackend extends t3lib_cache_backend_AbstractBackend {

	/**
	 * Max bucket size, (1024*1024)-42 bytes
	 * @var int
	 */
	const MAX_BUCKET_SIZE = 1048534;

	/**
	 * Instance of the PHP Memcache class
	 *
	 * @var Memcache
	 */
	protected $memcache;

	/**
	 * Array of Memcache server configurations
	 *
	 * @var array
	 */
	protected $servers = array();

	/**
	 * Indicates whether the memcache uses compression or not (requires zlib),
	 * either 0 or MEMCACHE_COMPRESSED
	 *
	 * @var int
	 */
	protected $flags;

	/**
	 * A prefix to seperate stored data from other data possibly stored in the
	 * memcache. This prefix must be unique for each site in the tree. Default
	 * implementation uses MD5 of the current site path to make identifier prefix
	 * unique.
	 *
	 * @var	string
	 */
	protected $identifierPrefix;

	/**
	 * Indicates whther the server is connected
	 *
	 * @var	boolean
	 */
	protected $serverConnected = false;

	/**
	 * Constructs this backend
	 *
	 * @param array $options Configuration options - depends on the actual backend
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct(array $options = array()) {
		if (!extension_loaded('memcache')) {
			throw new t3lib_cache_Exception(
				'The PHP extension "memcache" must be installed and loaded in ' .
				'order to use the Memcached backend.',
				1213987706
			);
		}

		parent::__construct($options);

		$this->memcache = new Memcache();
		$defaultPort = ini_get('memcache.default_port');

		if (!count($this->servers)) {
			throw new t3lib_cache_Exception(
				'No servers were given to Memcache',
				1213115903
			);
		}

		foreach ($this->servers as $serverConfiguration) {
			if (substr($serverConfiguration, 0, 7) == 'unix://') {
				$host = $serverConfiguration;
				$port = 0;
			} else {
				if (substr($serverConfiguration, 0, 6) === 'tcp://') {
					$serverConfiguration = substr($serverConfiguration, 6);
				}
				if (strstr($serverConfiguration, ':') !== FALSE) {
					list($host, $port) = explode(':', $serverConfiguration, 2);
				} else {
					$host = $serverConfiguration;
					$port = $defaultPort;
				}
			}

			if ($this->serverConnected) {
				$this->memcache->addserver($host, $port);
			} else {
					// pconnect throws PHP warnings when it cannot connect!
				$this->serverConnected = @$this->memcache->pconnect($host, $port);
			}
		}

		if (!$this->serverConnected) {
			t3lib_div::sysLog('Unable to connect to any Memcached server', 'core', 3);
		}
	}

	/**
	 * Setter for servers to be used. Expects an array,  the values are expected
	 * to be formatted like "<host>[:<port>]" or "unix://<path>"
	 *
	 * @param	array	An array of servers to add.
	 * @return	void
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	protected function setServers(array $servers) {
		$this->servers = $servers;
	}

	/**
	 * Setter for compression flags bit
	 *
	 * @param boolean $useCompression
	 * @return void
	 * @author Christian Jul Jensen <julle@typo3.org>
	 */
	protected function setCompression($useCompression) {
		if ($useCompression === TRUE) {
			$this->flags ^= MEMCACHE_COMPRESSED;
		} else {
			$this->flags &= ~MEMCACHE_COMPRESSED;
		}
	}

	/**
	 * Initializes the identifier prefix when setting the cache.
	 *
	 * @param t3lib_cache_frontend_Frontend $cache The frontend for this backend
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Dmitry Dulepov
	 */
	public function setCache(t3lib_cache_frontend_Frontend $cache) {
		parent::setCache($cache);
		$this->identifierPrefix = 'TYPO3_' . md5(PATH_site) . '_';
	}

	/**
	 * Saves data in the cache.
	 *
	 * @param string An identifier for this specific cache entry
	 * @param string The data to be stored
	 * @param array Tags to associate with this cache entry
	 * @param integer Lifetime of this cache entry in seconds. If NULL is specified, the default lifetime is used. "0" means unlimited liftime.
	 * @return void
	 * @throws t3lib_cache_Exception if no cache frontend has been set.
	 * @throws InvalidArgumentException if the identifier is not valid or the final memcached key is longer than 250 characters
	 * @throws t3lib_cache_exception_InvalidData if $data is not a string
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function set($entryIdentifier, $data, array $tags = array(), $lifetime = NULL) {
		if (strlen($this->identifierPrefix . $entryIdentifier) > 250) {
			throw new InvalidArgumentException(
				'Could not set value. Key more than 250 characters (' . $this->identifierPrefix . $entryIdentifier . ').',
				1235839340
			);
		}

		if (!$this->cache instanceof t3lib_cache_frontend_Frontend) {
			throw new t3lib_cache_Exception(
				'No cache frontend has been set yet via setCache().',
				1207149215
			);
		}

		if (!is_string($data)) {
			throw new t3lib_cache_Exception_InvalidData(
				'The specified data is of type "' . gettype($data) .
				'" but a string is expected.',
				1207149231
			);
		}

		$tags[] = '%MEMCACHEBE%' . $this->cacheIdentifier;
		$expiration = $lifetime !== NULL ? $lifetime : $this->defaultLifetime;

			// Memcached consideres values over 2592000 sec (30 days) as UNIX timestamp
			// thus $expiration should be converted from lifetime to UNIX timestamp
		if ($expiration > 2592000) {
			$expiration += $GLOBALS['EXEC_TIME'];
		}

		try {
			if (strlen($data) > self::MAX_BUCKET_SIZE) {
				$data = str_split($data, 1024 * 1000);
				$success = TRUE;
				$chunkNumber = 1;

				foreach ($data as $chunk) {
					$success = $success && $this->memcache->set(
						$this->identifierPrefix . $entryIdentifier . '_chunk_' . $chunkNumber,
						$chunk,
						$this->flags,
						$expiration
					);
					$chunkNumber++;
				}
				$success = $success && $this->memcache->set(
					$this->identifierPrefix . $entryIdentifier,
					'TYPO3*chunked:' . $chunkNumber,
					$this->flags,
					$expiration
				);
			} else {
				$success = $this->memcache->set(
					$this->identifierPrefix . $entryIdentifier,
					$data,
					$this->flags,
					$expiration
				);
			}

			if ($success === TRUE) {
				$this->removeIdentifierFromAllTags($entryIdentifier);
				$this->addIdentifierToTags($entryIdentifier, $tags);
			} else {
				throw new t3lib_cache_Exception(
					'Could not set data to memcache server.',
					1275830266
				);
			}
		} catch (Exception $exception) {
			throw new t3lib_cache_Exception(
				'Could not set value. ' .
				$exception->getMessage(),
				1207208100
			);
		}
	}

	/**
	 * Loads data from the cache.
	 *
	 * @param string An identifier which describes the cache entry to load
	 * @return mixed The cache entry's content as a string or FALSE if the cache entry could not be loaded
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function get($entryIdentifier) {
		$value = $this->memcache->get($this->identifierPrefix . $entryIdentifier);

		if (substr($value, 0, 14) === 'TYPO3*chunked:') {
			list(, $chunkCount) = explode(':', $value);
			$value = '';

			for ($chunkNumber = 1; $chunkNumber < $chunkCount; $chunkNumber++) {
				$value .= $this->memcache->get($this->identifierPrefix . $entryIdentifier . '_chunk_' . $chunkNumber);
			}
		}

		return $value;
	}

	/**
	 * Checks if a cache entry with the specified identifier exists.
	 *
	 * @param string An identifier specifying the cache entry
	 * @return boolean TRUE if such an entry exists, FALSE if not
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function has($entryIdentifier) {
		return $this->serverConnected && $this->memcache->get($this->identifierPrefix . $entryIdentifier) !== false;
	}

	/**
	 * Removes all cache entries matching the specified identifier.
	 * Usually this only affects one entry but if - for what reason ever -
	 * old entries for the identifier still exist, they are removed as well.
	 *
	 * @param string Specifies the cache entry to remove
	 * @return boolean TRUE if (at least) an entry could be removed or FALSE if no entry was found
	 * @author Christian Jul Jensen <julle@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function remove($entryIdentifier) {
		$this->removeIdentifierFromAllTags($entryIdentifier);
		return $this->memcache->delete($this->identifierPrefix . $entryIdentifier, 0);
	}

	/**
	 * Finds and returns all cache entry identifiers which are tagged by the
	 * specified tag.
	 *
	 * @param string The tag to search for
	 * @return array An array of entries with all matching entries. An empty array if no entries matched
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function findIdentifiersByTag($tag) {
		$identifiers = $this->memcache->get($this->identifierPrefix . 'tag_' . $tag);

		if ($identifiers !== FALSE) {
			return (array) $identifiers;
		} else {
			return array();
		}
	}


	/**
	 * Finds and returns all cache entry identifiers which are tagged by the
	 * specified tags.
	 *
	 * @param array Array of tags to search for
	 * @return array An array with identifiers of all matching entries. An empty array if no entries matched
	 * @author Ingo Renner <ingo@typo3.org>
	 */
	public function findIdentifiersByTags(array $tags) {
		$taggedEntries = array();
		$foundEntries = array();

		foreach ($tags as $tag) {
			$taggedEntries[$tag] = $this->findIdentifiersByTag($tag);
		}

		$intersectedTaggedEntries = call_user_func_array('array_intersect', $taggedEntries);

		foreach ($intersectedTaggedEntries as $entryIdentifier) {
			$foundEntries[$entryIdentifier] = $entryIdentifier;
		}

		return $foundEntries;
	}

	/**
	 * Removes all cache entries of this cache.
	 *
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function flush() {
		if (!$this->cache instanceof t3lib_cache_frontend_Frontend) {
			throw new t3lib_cache_Exception('No cache frontend has been set via setCache() yet.', 1204111376);
		}

		$this->flushByTag('%MEMCACHEBE%' . $this->cacheIdentifier);
	}

	/**
	 * Removes all cache entries of this cache which are tagged by the specified tag.
	 *
	 * @param string $tag The tag the entries must have
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function flushByTag($tag) {
		$identifiers = $this->findIdentifiersByTag($tag);

		foreach ($identifiers as $identifier) {
			$this->remove($identifier);
		}
	}


	/**
	 * Removes all cache entries of this cache which are tagged by the specified tag.
	 *
	 * @param array	The tags the entries must have
	 * @return void
	 * @author Ingo Renner <ingo@typo3.org>
	 */
	public function flushByTags(array $tags) {
		foreach ($tags as $tag) {
			$this->flushByTag($tag);
		}
	}

	/**
	 * Associates the identifier with the given tags
	 *
	 * @param string $entryIdentifier
	 * @param array Array of tags
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author	Dmitry Dulepov <dmitry@typo3.org>
	 * @internal
	 */
	protected function addIdentifierToTags($entryIdentifier, array $tags) {
		if ($this->serverConnected) {
			foreach ($tags as $tag) {
					// Update tag-to-identifier index
				$identifiers = $this->findIdentifiersByTag($tag);
				if (array_search($entryIdentifier, $identifiers) === false) {
					$identifiers[] = $entryIdentifier;
					$this->memcache->set($this->identifierPrefix . 'tag_' . $tag,
										 $identifiers);
				}

					// Update identifier-to-tag index
				$existingTags = $this->findTagsByIdentifier($entryIdentifier);
				if (array_search($tag, $existingTags) === FALSE) {
					$this->memcache->set($this->identifierPrefix . 'ident_' . $entryIdentifier,
										 array_merge($existingTags, $tags));
				}
			}
		}
	}

	/**
	 * Removes association of the identifier with the given tags
	 *
	 * @param string $entryIdentifier
	 * @param array Array of tags
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @author	Dmitry Dulepov <dmitry@typo3.org>
	 * @internal
	 */
	protected function removeIdentifierFromAllTags($entryIdentifier) {
		if ($this->serverConnected) {
				// Get tags for this identifier
			$tags = $this->findTagsByIdentifier($entryIdentifier);
				// Deassociate tags with this identifier
			foreach ($tags as $tag) {
				$identifiers = $this->findIdentifiersByTag($tag);
					// Formally array_search() below should never return false
					// due to the behavior of findTagsForIdentifier(). But if
					// reverse index is corrupted, we still can get 'false' from
					// array_search(). This is not a problem because we are
					// removing this identifier from anywhere.
				if (($key = array_search($entryIdentifier, $identifiers)) !== false) {
					unset($identifiers[$key]);

					if (count($identifiers)) {
						$this->memcache->set(
							$this->identifierPrefix . 'tag_' . $tag,
							$identifiers
						);
					} else {
						$this->memcache->delete($this->identifierPrefix . 'tag_' . $tag, 0);
					}
				}
			}

				// Clear reverse tag index for this identifier
			$this->memcache->delete($this->identifierPrefix . 'ident_' . $entryIdentifier, 0);
		}
	}

	/**
	 * Finds all tags for the given identifier. This function uses reverse tag
	 * index to search for tags.
	 *
	 * @param	string	Identifier to find tags by
	 * @return	array	Array with tags
	 * @author Dmitry Dulepov <dmitry@typo3.org>
	 * @internal
	 */
	protected function findTagsByIdentifier($identifier) {
		$tags = $this->memcache->get($this->identifierPrefix . 'ident_' . $identifier);
		return ($tags === FALSE ? array() : (array) $tags);
	}

	/**
	 * Does nothing, as memcached does GC itself
	 *
	 * @return void
	 */
	public function collectGarbage() {
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/cache/backend/class.t3lib_cache_backend_memcachedbackend.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['t3lib/cache/backend/class.t3lib_cache_backend_memcachedbackend.php']);
}

?>