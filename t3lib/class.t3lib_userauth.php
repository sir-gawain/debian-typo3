<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2008 Kasper Skaarhoj (kasperYYYY@typo3.com)
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
 * Contains a base class for authentication of users in TYPO3, both frontend and backend.
 *
 * $Id: class.t3lib_userauth.php 4838 2009-01-23 20:50:05Z stucki $
 * Revised for TYPO3 3.6 July/2003 by Kasper Skaarhoj
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Ren� Fritz <r.fritz@colorcube.de>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *  111: class t3lib_userAuth
 *  195:     function start()
 *  329:     function checkAuthentication()
 *
 *              SECTION: User Sessions
 *  569:     function createUserSession ($tempuser)
 *  606:     function fetchUserSession()
 *  657:     function logoff()
 *
 *              SECTION: SQL Functions
 *  713:     function user_where_clause()
 *  727:     function ipLockClause()
 *  745:     function ipLockClause_remoteIPNumber($parts)
 *  766:     function hashLockClause()
 *  777:     function hashLockClause_getHashInt()
 *
 *              SECTION: Session and Configuration Handling
 *  809:     function writeUC($variable='')
 *  824:     function unpack_uc($theUC='')
 *  840:     function pushModuleData($module,$data,$noSave=0)
 *  853:     function getModuleData($module,$type='')
 *  866:     function getSessionData($key)
 *  879:     function setAndSaveSessionData($key,$data)
 *
 *              SECTION: Misc
 *  912:     function getLoginFormData()
 *  939:     function processLoginData($loginData, $security_level='')
 *  981:     function getAuthInfoArray()
 * 1011:     function compareUident($user, $loginData, $security_level='')
 * 1050:     function gc()
 * 1064:     function redirect()
 * 1086:     function writelog($type,$action,$error,$details_nr,$details,$data,$tablename,$recuid,$recpid)
 * 1095:     function checkLogFailures()
 * 1108:     function setBeUserByUid($uid)
 * 1120:     function setBeUserByName($name)
 * 1131:     function getRawUserByUid($uid)
 * 1149:     function getRawUserByName($name)
 *
 *              SECTION: Create/update user - EXPERIMENTAL
 * 1188:     function fetchUserRecord($dbUser, $username, $extraWhere='' )
 *
 * TOTAL FUNCTIONS: 29
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require_once(t3lib_extMgm::extPath('sv').'class.tx_sv_authbase.php');









/**
 * Authentication of users in TYPO3
 *
 * This class is used to authenticate a login user.
 * The class is used by both the frontend and backend. In both cases this class is a parent class to beuserauth and feuserauth
 *
 * See Inside TYPO3 for more information about the API of the class and internal variables.
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @author	Ren� Fritz <r.fritz@colorcube.de>
 * @package TYPO3
 * @subpackage t3lib
 */
class t3lib_userAuth {
	var $global_database = '';		// Which global database to connect to
	var $session_table = '';		// Table to use for session data.
	var $name = '';					// Session/Cookie name
	var $get_name = '';				// Session/GET-var name

	var $user_table = '';			// Table in database with userdata
	var $username_column = '';		// Column for login-name
	var $userident_column = '';		// Column for password
	var $userid_column = '';		// Column for user-id
	var $lastLogin_column = '';

	var $enablecolumns = Array (
		'rootLevel' => '',			// Boolean: If true, 'AND pid=0' will be a part of the query...
		'disabled' => '',
		'starttime' => '',
		'endtime' => '',
		'deleted' => ''
	);

	var $formfield_uname = ''; 			// formfield with login-name
	var $formfield_uident = ''; 		// formfield with password
	var $formfield_chalvalue = '';		// formfield with a unique value which is used to encrypt the password and username
	var $formfield_status = ''; 		// formfield with status: *'login', 'logout'. If empty login is not verified.
	var $security_level = 'normal';		// sets the level of security. *'normal' = clear-text. 'challenged' = hashed password/username from form in $formfield_uident. 'superchallenged' = hashed password hashed again with username.

	var $auth_include = '';			// this is the name of the include-file containing the login form. If not set, login CAN be anonymous. If set login IS needed.

	var $auth_timeout_field = 0;		// Server session lifetime. If > 0: session-timeout in seconds. If false or <0: no timeout. If string: The string is a fieldname from the usertable where the timeout can be found.
	var $lifetime = 0;			// Client session lifetime. 0 = Session-cookies. If session-cookies, the browser will stop the session when the browser is closed. Otherwise this specifies the lifetime of a cookie that keeps the session.
	var $gc_time = 0;			// GarbageCollection. Purge all server session data older than $gc_time seconds. 0 = default to $this->timeout or use 86400 seconds (1 day) if $this->lifetime is 0
	var $gc_probability = 1;		// Possibility (in percent) for GarbageCollection to be run.
	var $writeStdLog = FALSE;		// Decides if the writelog() function is called at login and logout
	var $writeAttemptLog = FALSE;		// If the writelog() functions is called if a login-attempt has be tried without success
	var $sendNoCacheHeaders = TRUE;		// If this is set, headers is sent to assure, caching is NOT done
	var $getFallBack = FALSE;			// If this is set, authentication is also accepted by the $_GET. Notice that the identification is NOT 128bit MD5 hash but reduced. This is done in order to minimize the size for mobile-devices, such as WAP-phones
	var $hash_length = 32;				// The ident-hash is normally 32 characters and should be! But if you are making sites for WAP-devices og other lowbandwidth stuff, you may shorten the length. Never let this value drop below 6. A length of 6 would give you more than 16 mio possibilities.
	var $getMethodEnabled = FALSE;		// Setting this flag true lets user-authetication happen from GET_VARS if POST_VARS are not set. Thus you may supply username/password from the URL.
	var $lockIP = 4;					// If set, will lock the session to the users IP address (all four numbers. Reducing to 1-3 means that only first, second or third part of the IP address is used).
	var $lockHashKeyWords = 'useragent';	// Keyword list (commalist with no spaces!): "useragent". Each keyword indicates some information that can be included in a integer hash made to lock down usersessions.

	var $warningEmail = '';				// warning -emailaddress:
	var $warningPeriod = 3600;			// Period back in time (in seconds) in which number of failed logins are collected
	var $warningMax = 3;				// The maximum accepted number of warnings before an email is sent
	var $checkPid = TRUE;				// If set, the user-record must $checkPid_value as pid
	var $checkPid_value=0;				// The pid, the user-record must have as page-id

		// Internals
	var $id;							// Internal: Will contain session_id (MD5-hash)
	var $cookieId;						// Internal: Will contain the session_id gotten from cookie or GET method. This is used in statistics as a reliable cookie (one which is known to come from $_COOKIE).
	var $loginFailure = FALSE;			// Indicates if an authentication was started but failed
	var $loginSessionStarted = FALSE;	// Will be set to true if the login session is actually written during auth-check.

	var $user;							// Internal: Will contain user- AND session-data from database (joined tables)
	var $get_URL_ID = '';				// Internal: Will will be set to the url--ready (eg. '&login=ab7ef8d...') GET-auth-var if getFallBack is true. Should be inserted in links!

	var $newSessionID = FALSE;			// Will be set to true if a new session ID was created
	var $forceSetCookie = FALSE;		// Will force the session cookie to be set everytime (lifetime must be 0)
	var $dontSetCookie = FALSE;			// Will prevent the setting of the session cookie (takes precedence over forceSetCookie)
	var $challengeStoredInCookie = FALSE;	// If set, the challenge value will be stored in a session as well so the server can check that is was not forged.
	var $loginType = '';				// Login type, used for services.

	var $svConfig = array();			// "auth" services configuration array from $TYPO3_CONF_VARS['SVCONF']['auth']
	var $writeDevLog = FALSE; 			// write messages into the devlog?









	/**
	 * Starts a user session
	 * Typical configurations will:
	 * a) check if session cookie was set and if not, set one,
	 * b) check if a password/username was sent and if so, try to authenticate the user
	 * c) Lookup a session attached to a user and check timeout etc.
	 * d) Garbage collection, setting of no-cache headers.
	 * If a user is authenticated the database record of the user (array) will be set in the ->user internal variable.
	 *
	 * @return	void
	 */
	function start() {
		global $TYPO3_CONF_VARS;

			// backend or frontend login - used for auth services
		$this->loginType = ($this->name=='fe_typo_user') ? 'FE' : 'BE';

			// set level to normal if not already set
		$this->security_level = $this->security_level ? $this->security_level : 'normal';

			// enable dev logging if set
		if ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['writeDevLog']) $this->writeDevLog = TRUE;
		if ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['writeDevLog'.$this->loginType]) $this->writeDevLog = TRUE;
		if (TYPO3_DLOG) $this->writeDevLog = TRUE;

		if ($this->writeDevLog) 	t3lib_div::devLog('## Beginning of auth logging.', 't3lib_userAuth');

			// Init vars.
		$mode = '';
		$this->newSessionID = FALSE;
			// $id is set to ses_id if cookie is present. Else set to false, which will start a new session
		$id = isset($_COOKIE[$this->name]) ? stripslashes($_COOKIE[$this->name]) : '';
		$this->hash_length = t3lib_div::intInRange($this->hash_length,6,32);
		$this->svConfig = $TYPO3_CONF_VARS['SVCONF']['auth'];


			// If fallback to get mode....
		if (!$id && $this->getFallBack && $this->get_name)	{
			$id = isset($_GET[$this->get_name]) ? t3lib_div::_GET($this->get_name) : '';
			if (strlen($id)!=$this->hash_length)	$id='';
			$mode='get';
		}
		$this->cookieId = $id;

			// If new session or client tries to fix session...
		if (!$id || !$this->isExistingSessionRecord($id))	{
				// New random session-$id is made
			$id = substr(md5(uniqid('').getmypid()),0,$this->hash_length);
				// New session
			$this->newSessionID = TRUE;
		}

			// Internal var 'id' is set
		$this->id = $id;

			// If fallback to get mode....
		if ($mode=='get' && $this->getFallBack && $this->get_name)	{
			$this->get_URL_ID = '&'.$this->get_name.'='.$id;
		}
			// Make certain that NO user is set initially
		$this->user = '';

			// Check to see if anyone has submitted login-information and if so register the user with the session. $this->user[uid] may be used to write log...
		$this->checkAuthentication();

			// Make certain that NO user is set initially. ->check_authentication may have set a session-record which will provide us with a user record in the next section:
		unset($this->user);

			// re-read user session
		$this->user = $this->fetchUserSession();

		if ($this->writeDevLog && is_array($this->user)) 	t3lib_div::devLog('User session finally read: '.t3lib_div::arrayToLogString($this->user, array($this->userid_column,$this->username_column)), 't3lib_userAuth', -1);
		if ($this->writeDevLog && !is_array($this->user)) t3lib_div::devLog('No user session found.', 't3lib_userAuth', 2);

			// Setting cookies
		if ($TYPO3_CONF_VARS['SYS']['cookieDomain'])	{
			if ($TYPO3_CONF_VARS['SYS']['cookieDomain']{0} == '/')	{
				$matchCnt = @preg_match($TYPO3_CONF_VARS['SYS']['cookieDomain'], t3lib_div::getIndpEnv('TYPO3_HOST_ONLY'), $match);
				if ($matchCnt === FALSE)	{
					t3lib_div::sysLog('The regular expression of $TYPO3_CONF_VARS[SYS][cookieDomain] contains errors. The session is not shared across sub-domains.', 'Core', 3);
				} elseif ($matchCnt)	{
					$cookieDomain = $match[0];
				}
			} else {
				$cookieDomain = $TYPO3_CONF_VARS['SYS']['cookieDomain'];
			}
		}

			// If new session and the cookie is a sessioncookie, we need to set it only once!
		if ($this->isSetSessionCookie())	{
			if (!$this->dontSetCookie)	{
				if ($cookieDomain)	{
					SetCookie($this->name, $id, 0, '/', $cookieDomain);
				} else {
					SetCookie($this->name, $id, 0, '/');
				}
				if ($this->writeDevLog) 	t3lib_div::devLog('Set new Cookie: '.$id.($cookieDomain ? ', '.$cookieDomain : ''), 't3lib_userAuth');
			}
		}

			// If it is NOT a session-cookie, we need to refresh it.
		if ($this->isRefreshTimeBasedCookie())	{
			if (!$this->dontSetCookie)	{
				if ($cookieDomain)	{
					SetCookie($this->name, $id, time()+$this->lifetime, '/', $cookieDomain);
				} else {
					SetCookie($this->name, $id, time()+$this->lifetime, '/');
				}
				if ($this->writeDevLog) 	t3lib_div::devLog('Update Cookie: '.$id.($cookieDomain ? ', '.$cookieDomain : ''), 't3lib_userAuth');
			}
		}

			// Hook for alternative ways of filling the $this->user array (is used by the "timtaw" extension)
		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp']))	{
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'] as $funcName)	{
				$_params = array(
					'pObj' => &$this,
				);
				t3lib_div::callUserFunction($funcName,$_params,$this);
			}
		}

			// If any redirection (inclusion of file) then it will happen in this function
		$this->redirect();

			// Set all posible headers that could ensure that the script is not cached on the client-side
		if ($this->sendNoCacheHeaders)	{
			header('Expires: 0');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
		}

			// Set $this->gc_time if not explicitely specified
		if ($this->gc_time==0)	{
			$this->gc_time = ($this->auth_timeout_field==0 ? 86400 : $this->auth_timeout_field);	// Default to 1 day if $this->auth_timeout_field is 0
		}

			// If we're lucky we'll get to clean up old sessions....
		if ((rand()%100) <= $this->gc_probability)	{
			$this->gc();
		}
	}

	/**
	 * Determine whether a session cookie needs to be set (lifetime=0)
	 *
	 * @return	boolean
	 * @internal
	 */
	function isSetSessionCookie() {
		return ($this->newSessionID || $this->forceSetCookie) && $this->lifetime==0;
	}

	/**
	 * Determine whether a non-session cookie needs to be set (lifetime>0)
	 *
	 * @return	boolean
	 * @internal
	 */
	function isRefreshTimeBasedCookie() {
		return $this->lifetime > 0;
	}

	/**
	 * Checks if a submission of username and password is present or use other authentication by auth services
	 *
	 * @return	void
	 * @internal
	 */
	function checkAuthentication() {

			// No user for now - will be searched by service below
		$tempuserArr = array();
		$tempuser = FALSE;

			// User is not authenticated by default
		$authenticated = FALSE;

			// User want to login with passed login data (name/password)
		$activeLogin = FALSE;

			// Indicates if an active authentication failed (not auto login)
		$this->loginFailure = FALSE;

		if ($this->writeDevLog) 	t3lib_div::devLog('Login type: '.$this->loginType, 't3lib_userAuth');

			// The info array provide additional information for auth services
		$authInfo = $this->getAuthInfoArray();

			// Get Login/Logout data submitted by a form or params
		$loginData = $this->getLoginFormData();

		if ($this->writeDevLog) 	t3lib_div::devLog('Login data: '.t3lib_div::arrayToLogString($loginData), 't3lib_userAuth');


			// active logout (eg. with "logout" button)
		if ($loginData['status']=='logout') {
			if ($this->writeStdLog) 	$this->writelog(255,2,0,2,'User %s logged out',Array($this->user['username']));	// Logout written to log
			if ($this->writeDevLog) 	t3lib_div::devLog('User logged out. Id: '.$this->id, 't3lib_userAuth', -1);

			$this->logoff();
		}

			// active login (eg. with login form)
		if ($loginData['status']=='login') {
			$activeLogin = TRUE;

			if ($this->writeDevLog) 	t3lib_div::devLog('Active login (eg. with login form)', 't3lib_userAuth');

				// check referer for submitted login values
			if ($this->formfield_status && $loginData['uident'] && $loginData['uname'])	{
				$httpHost = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
				if (!$this->getMethodEnabled && ($httpHost!=$authInfo['refInfo']['host'] && !$GLOBALS['TYPO3_CONF_VARS']['SYS']['doNotCheckReferer']))	{
					die('Error: This host address ("'.$httpHost.'") and the referer host ("'.$authInfo['refInfo']['host'].'") mismatches!<br />
						It\'s possible that the environment variable HTTP_REFERER is not passed to the script because of a proxy.<br />
						The site administrator can disable this check in the "All Configuration" section of the Install Tool (flag: TYPO3_CONF_VARS[SYS][doNotCheckReferer]).');
				}

					// delete old user session if any
				$this->logoff();
			}

				// Refuse login for _CLI users (used by commandline scripts)
			if ((strtoupper(substr($loginData['uname'],0,5))=='_CLI_') && (!defined('TYPO3_cliMode') || !TYPO3_cliMode))	{	// although TYPO3_cliMode should never be set when using active login...
				die('Error: You have tried to login using a CLI user. Access prohibited!');
			}
		}


		// the following code makes auto-login possible (if configured). No submitted data needed

			// re-read user session
		$authInfo['userSession'] = $this->fetchUserSession();
		$haveSession = is_array($authInfo['userSession']) ? TRUE : FALSE;

		if ($this->writeDevLog)	{
			if ($haveSession)	{
				t3lib_div::devLog('User session found: '.t3lib_div::arrayToLogString($authInfo['userSession'], array($this->userid_column,$this->username_column)), 't3lib_userAuth', 0);
			}
			if (is_array($this->svConfig['setup']))	{
				t3lib_div::devLog('SV setup: '.t3lib_div::arrayToLogString($this->svConfig['setup']), 't3lib_userAuth', 0);
			}
		}

			// fetch user if ...
		if ($activeLogin
				|| (!$haveSession && $this->svConfig['setup'][$this->loginType.'_fetchUserIfNoSession'])
				|| $this->svConfig['setup'][$this->loginType.'_alwaysFetchUser']) {

				// use 'auth' service to find the user
				// first found user will be used
			$serviceChain = '';
			$subType = 'getUser'.$this->loginType;
			while (is_object($serviceObj = t3lib_div::makeInstanceService('auth', $subType, $serviceChain))) {
				$serviceChain.=','.$serviceObj->getServiceKey();
				$serviceObj->initAuth($subType, $loginData, $authInfo, $this);
				if ($row=$serviceObj->getUser()) {
					$tempuserArr[] = $row;

					if ($this->writeDevLog) 	t3lib_div::devLog('User found: '.t3lib_div::arrayToLogString($row, array($this->userid_column,$this->username_column)), 't3lib_userAuth', 0);

						// user found, just stop to search for more if not configured to go on
					if(!$this->svConfig['setup'][$this->loginType.'_fetchAllUsers']) {
						break;
					}
				}
				unset($serviceObj);
			}
			unset($serviceObj);

			if ($this->writeDevLog && $this->svConfig['setup'][$this->loginType.'_alwaysFetchUser']) 	t3lib_div::devLog($this->loginType.'_alwaysFetchUser option is enabled', 't3lib_userAuth');
			if ($this->writeDevLog && $serviceChain) 	t3lib_div::devLog($subType.' auth services called: '.$serviceChain, 't3lib_userAuth');
			if ($this->writeDevLog && !count($tempuserArr)) 	t3lib_div::devLog('No user found by services', 't3lib_userAuth');
			if ($this->writeDevLog && count($tempuserArr)) 	t3lib_div::devLog(count($tempuserArr).' user records found by services', 't3lib_userAuth');
		}


			// If no new user was set we use the already found user session
		if (!count($tempuserArr) && $haveSession)	{
			$tempuserArr[] = $authInfo['userSession'];
			$tempuser = $authInfo['userSession'];
				// User is authenticated because we found a user session
			$authenticated = TRUE;

			if ($this->writeDevLog) 	t3lib_div::devLog('User session used: '.t3lib_div::arrayToLogString($authInfo['userSession'], array($this->userid_column,$this->username_column)), 't3lib_userAuth');
		}


			// Re-auth user when 'auth'-service option is set
		if ($this->svConfig['setup'][$this->loginType.'_alwaysAuthUser']) {
			$authenticated = FALSE;
			if ($this->writeDevLog) 	t3lib_div::devLog('alwaysAuthUser option is enabled', 't3lib_userAuth');
		}


			// Authenticate the user if needed
		if (count($tempuserArr) && !$authenticated)	{

			foreach ($tempuserArr as $tempuser)	{

				// use 'auth' service to authenticate the user
				// if one service returns FALSE then authentication failed
				// a service might return 100 which means there's no reason to stop but the user can't be authenticated by that service

				if ($this->writeDevLog) 	t3lib_div::devLog('Auth user: '.t3lib_div::arrayToLogString($tempuser), 't3lib_userAuth');

				$serviceChain='';
				$subType = 'authUser'.$this->loginType;
				while (is_object($serviceObj = t3lib_div::makeInstanceService('auth', $subType, $serviceChain))) {
					$serviceChain.=','.$serviceObj->getServiceKey();
					$serviceObj->initAuth($subType, $loginData, $authInfo, $this);
					if (($ret=$serviceObj->authUser($tempuser)) > 0) {

							// if the service returns >=200 then no more checking is needed - useful for IP checking without password
						if (intval($ret) >= 200)	{
							$authenticated = TRUE;
							break;
						} elseif (intval($ret) >= 100) {
							// Just go on. User is still not authenticated but there's no reason to stop now.
						} else {
							$authenticated = TRUE;
						}

					} else {
						$authenticated = FALSE;
						break;
					}
					unset($serviceObj);
				}
				unset($serviceObj);

				if ($this->writeDevLog && $serviceChain) 	t3lib_div::devLog($subType.' auth services called: '.$serviceChain, 't3lib_userAuth');

				if ($authenticated) {
						// leave foreach() because a user is authenticated
					break;
				}
			}
		}

			// If user is authenticated a valid user is in $tempuser
		if ($authenticated)	{
				// reset failure flag
			$this->loginFailure = FALSE;


				// Insert session record if needed:
			if (!($haveSession && (
				$tempuser['ses_id']==$this->id || 	// check if the tempuser has the current session id
				$tempuser['uid']==$authInfo['userSession']['ses_userid'] 	// check if the tempuser has the uid of the fetched session user
				))) {
				$this->createUserSession($tempuser);

					// The login session is started.
				$this->loginSessionStarted = TRUE;
			}

				// User logged in - write that to the log!
			if ($this->writeStdLog && $activeLogin) {
				$this->writelog(255,1,0,1,
					'User %s logged in from %s (%s)',
					Array($tempuser[$this->username_column], t3lib_div::getIndpEnv('REMOTE_ADDR'), t3lib_div::getIndpEnv('REMOTE_HOST')),
					'','','',-1,'',$tempuser['uid']
				);
			}

			if ($this->writeDevLog && $activeLogin) 	t3lib_div::devLog('User '.$tempuser[$this->username_column].' logged in from '.t3lib_div::getIndpEnv('REMOTE_ADDR').' ('.t3lib_div::getIndpEnv('REMOTE_HOST').')', 't3lib_userAuth', -1);
			if ($this->writeDevLog && !$activeLogin) 	t3lib_div::devLog('User '.$tempuser[$this->username_column].' authenticated from '.t3lib_div::getIndpEnv('REMOTE_ADDR').' ('.t3lib_div::getIndpEnv('REMOTE_HOST').')', 't3lib_userAuth', -1);

			if($GLOBALS['TYPO3_CONF_VARS']['BE']['lockSSL'] == 3 && $this->user_table == 'be_users')	{
				$requestStr = substr(t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT'), strlen(t3lib_div::getIndpEnv('TYPO3_SITE_URL').TYPO3_mainDir));
				$backendScript = t3lib_BEfunc::getBackendScript();
				if($requestStr == $backendScript && t3lib_div::getIndpEnv('TYPO3_SSL'))	{
					list(,$url) = explode('://',t3lib_div::getIndpEnv('TYPO3_SITE_URL'),2);
					list($server,$address) = explode('/',$url,2);
					if (intval($TYPO3_CONF_VARS['BE']['lockSSLPort'])) {
						$sslPortSuffix = ':'.intval($TYPO3_CONF_VARS['BE']['lockSSLPort']);
						$server = str_replace($sslPortSuffix,'',$server);	// strip port from server
					}
					header('Location: http://'.$server.'/'.$address.TYPO3_mainDir.$backendScript);
					exit;
				}
			}

		} elseif ($activeLogin || count($tempuserArr)) {
			$this->loginFailure = TRUE;

			if ($this->writeDevLog && !count($tempuserArr) && $activeLogin) 	t3lib_div::devLog('Login failed: '.t3lib_div::arrayToLogString($loginData), 't3lib_userAuth', 2);
			if ($this->writeDevLog && count($tempuserArr)) 	t3lib_div::devLog('Login failed: '.t3lib_div::arrayToLogString($tempuser, array($this->userid_column,$this->username_column)), 't3lib_userAuth', 2);
		}


			// If there were a login failure, check to see if a warning email should be sent:
		if ($this->loginFailure && $activeLogin)	{
			if ($this->writeDevLog) 	t3lib_div::devLog('Call checkLogFailures: '.t3lib_div::arrayToLogString(array('warningEmail'=>$this->warningEmail,'warningPeriod'=>$this->warningPeriod,'warningMax'=>$this->warningMax,)), 't3lib_userAuth', -1);

			$this->checkLogFailures($this->warningEmail, $this->warningPeriod, $this->warningMax);
		}
	}












	/*************************
	 *
	 * User Sessions
	 *
	 *************************/


	/**
	 * Creates a user session record.
	 *
	 * @param	array		user data array
	 * @return	void
	 */
	function createUserSession ($tempuser) {

		if ($this->writeDevLog) 	t3lib_div::devLog('Create session ses_id = '.$this->id, 't3lib_userAuth');

			// delete session entry first
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
					$this->session_table,
					'ses_id = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->id, $this->session_table).'
						AND ses_name = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->name, $this->session_table)
				);

			// re-create session entry
		$insertFields = $this->getNewSessionRecord($tempuser);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->session_table, $insertFields);

			// Updating lastLogin_column carrying information about last login.
		if ($this->lastLogin_column)	{
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
									$this->user_table,
									$this->userid_column.'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($tempuser[$this->userid_column], $this->user_table),
									array($this->lastLogin_column => $GLOBALS['EXEC_TIME'])
								);
		}
	}

	/**
	 * Returns a new session record for the current user for insertion into the DB.
	 * This function is mainly there as a wrapper for inheriting classes to override it.
	 *
	 * @return	array		user session record
	 */
	function getNewSessionRecord($tempuser) {
		return array(
			'ses_id' => $this->id,
			'ses_name' => $this->name,
			'ses_iplock' => $tempuser['disableIPlock'] ? '[DISABLED]' : $this->ipLockClause_remoteIPNumber($this->lockIP),
			'ses_hashlock' => $this->hashLockClause_getHashInt(),
			'ses_userid' => $tempuser[$this->userid_column],
			'ses_tstamp' => $GLOBALS['EXEC_TIME']
		);
	}

	/**
	 * Read the user session from db.
	 *
	 * @return	array		user session data
	 */
	function fetchUserSession() {

		$user = '';

		if ($this->writeDevLog) 	t3lib_div::devLog('Fetch session ses_id = '.$this->id, 't3lib_userAuth');

			// The session_id is used to find user in the database. Two tables are joined: The session-table with user_id of the session and the usertable with its primary key
		$dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						$this->session_table.','.$this->user_table,
						$this->session_table.'.ses_id = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->id, $this->session_table).'
							AND '.$this->session_table.'.ses_name = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->name, $this->session_table).'
							AND '.$this->session_table.'.ses_userid = '.$this->user_table.'.'.$this->userid_column.'
							'.$this->ipLockClause().'
							'.$this->hashLockClause().'
							'.$this->user_where_clause()
					);


		if ($user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres))	{
				// A user was found
			if (is_string($this->auth_timeout_field))	{
				$timeout = intval($user[$this->auth_timeout_field]);		// Get timeout-time from usertable
			} else {
				$timeout = intval($this->auth_timeout_field);					// Get timeout from object
			}
				// If timeout > 0 (true) and currenttime has not exceeded the latest sessions-time plus the timeout in seconds then accept user
				// Option later on: We could check that last update was at least x seconds ago in order not to update twice in a row if one script redirects to another...
			if ($timeout>0 && ($GLOBALS['EXEC_TIME'] < ($user['ses_tstamp']+$timeout)))	{
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
											$this->session_table,
											'ses_id='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->id, $this->session_table).'
												AND ses_name='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->name, $this->session_table),
											array('ses_tstamp' => $GLOBALS['EXEC_TIME'])
										);
					$user['ses_tstamp'] = $GLOBALS['EXEC_TIME'];	// Make sure that the timestamp is also updated in the array
			} else {
				$this->logoff();		// delete any user set...
			}
		} else {
			$this->logoff();		// delete any user set...
		}
		return $user;
	}

	/**
	 * Log out current user!
	 * Removes the current session record, sets the internal ->user array to a blank string; Thereby the current user (if any) is effectively logged out!
	 *
	 * @return	void
	 */
	function logoff() {
		if ($this->writeDevLog) 	t3lib_div::devLog('logoff: ses_id = '.$this->id, 't3lib_userAuth');

			// Hook for pre-processing the logoff() method, requested and implemented by andreas.otto@dkd.de:
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing']))	{
			$_params = array();
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'] as $_funcRef)	{
				if ($_funcRef)	{
					t3lib_div::callUserFunction($_funcRef,$_params,$this);
				}
			}
		}

		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
					$this->session_table,
					'ses_id = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->id, $this->session_table).'
						AND ses_name = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->name, $this->session_table)
				);

		$this->user = '';

			// Hook for post-processing the logoff() method, requested and implemented by andreas.otto@dkd.de:
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing']))	{
			$_params = array();
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'] as $_funcRef)	{
				if ($_funcRef)	{
					t3lib_div::callUserFunction($_funcRef,$_params,$this);
				}
			}
		}
	}

	/**
	 * Determine whether there's an according session record to a given session_id
	 * in the database. Don't care if session record is still valid or not.
	 *
	 * @param	integer		Claimed Session ID
	 * @return	boolean		Returns true if a corresponding session was found in the database
	 */
	function isExistingSessionRecord($id) {
		$count = false;
		$dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'COUNT(ses_id)',
						$this->session_table,
						'ses_id=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($id, $this->session_table)
					);
		if ($dbres !== false) {
			list($count) = $GLOBALS['TYPO3_DB']->sql_fetch_row($dbres);
			$GLOBALS['TYPO3_DB']->sql_free_result($dbres);
		}
		return (($count ? true : false));
	}













	/*************************
	 *
	 * SQL Functions
	 *
	 *************************/

	/**
	 * This returns the where-clause needed to select the user with respect flags like deleted, hidden, starttime, endtime
	 *
	 * @return	string
	 * @access private
	 */
	function user_where_clause()	{
		return  (($this->enablecolumns['rootLevel']) ? 'AND '.$this->user_table.'.pid=0 ' : '').
				(($this->enablecolumns['disabled']) ? ' AND '.$this->user_table.'.'.$this->enablecolumns['disabled'].'=0' : '').
				(($this->enablecolumns['deleted']) ? ' AND '.$this->user_table.'.'.$this->enablecolumns['deleted'].'=0' : '').
				(($this->enablecolumns['starttime']) ? ' AND ('.$this->user_table.'.'.$this->enablecolumns['starttime'].'<='.time().')' : '').
				(($this->enablecolumns['endtime']) ? ' AND ('.$this->user_table.'.'.$this->enablecolumns['endtime'].'=0 OR '.$this->user_table.'.'.$this->enablecolumns['endtime'].'>'.time().')' : '');
	}

	/**
	 * This returns the where-clause needed to lock a user to the IP address
	 *
	 * @return	string
	 * @access private
	 */
	function ipLockClause()	{
		if ($this->lockIP)	{
			$wherePart = 'AND (
				'.$this->session_table.'.ses_iplock='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->ipLockClause_remoteIPNumber($this->lockIP),$this->session_table).'
				OR '.$this->session_table.'.ses_iplock=\'[DISABLED]\'
				)';
			return $wherePart;
		}
	}

	/**
	 * Returns the IP address to lock to.
	 * The IP address may be partial based on $parts.
	 *
	 * @param	integer		1-4: Indicates how many parts of the IP address to return. 4 means all, 1 means only first number.
	 * @return	string		(Partial) IP address for REMOTE_ADDR
	 * @access private
	 */
	function ipLockClause_remoteIPNumber($parts)	{
		$IP = t3lib_div::getIndpEnv('REMOTE_ADDR');

		if ($parts>=4)	{
			return $IP;
		} else {
			$parts = t3lib_div::intInRange($parts,1,3);
			$IPparts = explode('.',$IP);
			for($a=4;$a>$parts;$a--)	{
				unset($IPparts[$a-1]);
			}
			return implode('.',$IPparts);
		}
	}

	/**
	 * This returns the where-clause needed to lock a user to a hash integer
	 *
	 * @return	string
	 * @access private
	 */
	function hashLockClause()	{
		$wherePart = 'AND '.$this->session_table.'.ses_hashlock='.intval($this->hashLockClause_getHashInt());
		return $wherePart;
	}

	/**
	 * Creates hash integer to lock user to. Depends on configured keywords
	 *
	 * @return	integer		Hash integer
	 * @access private
	 */
	function hashLockClause_getHashInt()	{
		$hashStr = '';

		if (t3lib_div::inList($this->lockHashKeyWords,'useragent'))	$hashStr.=':'.t3lib_div::getIndpEnv('HTTP_USER_AGENT');

		return t3lib_div::md5int($hashStr);
	}











	/*************************
	 *
	 * Session and Configuration Handling
	 *
	 *************************/

	/**
	 * This writes $variable to the user-record. This is a way of providing session-data.
	 * You can fetch the data again through $this->uc in this class!
	 * If $variable is not an array, $this->uc is saved!
	 *
	 * @param	array		An array you want to store for the user as session data. If $variable is not supplied (is blank string), the internal variable, ->uc, is stored by default
	 * @return	void
	 */
	function writeUC($variable='')	{
		if (is_array($this->user) && $this->user[$this->userid_column])	{
			if (!is_array($variable)) { $variable = $this->uc; }

			if ($this->writeDevLog) 	t3lib_div::devLog('writeUC: '.$this->userid_column.'='.intval($this->user[$this->userid_column]), 't3lib_userAuth');
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->user_table, $this->userid_column.'='.intval($this->user[$this->userid_column]), array('uc' => serialize($variable)));
		}
	}

	/**
	 * Sets $theUC as the internal variable ->uc IF $theUC is an array. If $theUC is false, the 'uc' content from the ->user array will be unserialized and restored in ->uc
	 *
	 * @param	mixed		If an array, then set as ->uc, otherwise load from user record
	 * @return	void
	 */
	function unpack_uc($theUC='') {
		if (!$theUC) 	$theUC=unserialize($this->user['uc']);
		if (is_array($theUC))	{
			$this->uc=$theUC;
		}
	}

	/**
	 * Stores data for a module.
	 * The data is stored with the session id so you can even check upon retrieval if the module data is from a previous session or from the current session.
	 *
	 * @param	string		$module is the name of the module ($MCONF['name'])
	 * @param	mixed		$data is the data you want to store for that module (array, string, ...)
	 * @param	boolean		If $noSave is set, then the ->uc array (which carries all kinds of user data) is NOT written immediately, but must be written by some subsequent call.
	 * @return	void
	 */
	function pushModuleData($module,$data,$noSave=0)	{
		$this->uc['moduleData'][$module] = $data;
		$this->uc['moduleSessionID'][$module] = $this->id;
		if (!$noSave) $this->writeUC();
	}

	/**
	 * Gets module data for a module (from a loaded ->uc array)
	 *
	 * @param	string		$module is the name of the module ($MCONF['name'])
	 * @param	string		If $type = 'ses' then module data is returned only if it was stored in the current session, otherwise data from a previous session will be returned (if available).
	 * @return	mixed		The module data if available: $this->uc['moduleData'][$module];
	 */
	function getModuleData($module,$type='')	{
		if ($type!='ses' || $this->uc['moduleSessionID'][$module]==$this->id) {
			return $this->uc['moduleData'][$module];
		}
	}

	/**
	 * Returns the session data stored for $key.
	 * The data will last only for this login session since it is stored in the session table.
	 *
	 * @param	string		Pointer to an associative key in the session data array which is stored serialized in the field "ses_data" of the session table.
	 * @return	mixed
	 */
	function getSessionData($key)	{
		$sesDat = unserialize($this->user['ses_data']);
		return $sesDat[$key];
	}

	/**
	 * Sets the session data ($data) for $key and writes all session data (from ->user['ses_data']) to the database.
	 * The data will last only for this login session since it is stored in the session table.
	 *
	 * @param	string		Pointer to an associative key in the session data array which is stored serialized in the field "ses_data" of the session table.
	 * @param	mixed		The variable to store in index $key
	 * @return	void
	 */
	function setAndSaveSessionData($key,$data)	{
		$sesDat = unserialize($this->user['ses_data']);
		$sesDat[$key] = $data;
		$this->user['ses_data'] = serialize($sesDat);

		if ($this->writeDevLog) 	t3lib_div::devLog('setAndSaveSessionData: ses_id = '.$this->user['ses_id'], 't3lib_userAuth');
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->session_table, 'ses_id='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->user['ses_id'], $this->session_table), array('ses_data' => $this->user['ses_data']));
	}













	/*************************
	 *
	 * Misc
	 *
	 *************************/

	/**
	 * Returns an info array with Login/Logout data submitted by a form or params
	 *
	 * @return	array
	 * @internal
	 */
	function getLoginFormData() {
		$loginData=array();
		if ($this->getMethodEnabled)	{
			$loginData['status'] = t3lib_div::_GP($this->formfield_status);
			$loginData['uname'] = t3lib_div::_GP($this->formfield_uname);
			$loginData['uident'] = t3lib_div::_GP($this->formfield_uident);
			$loginData['chalvalue'] = t3lib_div::_GP($this->formfield_chalvalue);
		} else {
			$loginData['status'] = t3lib_div::_POST($this->formfield_status);
			$loginData['uname'] = t3lib_div::_POST($this->formfield_uname);
			$loginData['uident'] = t3lib_div::_POST($this->formfield_uident);
			$loginData['chalvalue'] = t3lib_div::_POST($this->formfield_chalvalue);
		}
		$loginData = $this->processLoginData($loginData);

		return $loginData;
	}

	/**
	 * Processes Login data submitted by a form or params depending on the
	 * security_level
	 *
	 * @param	array		login data array
	 * @param	string		Alternative security_level. Used when authentication services wants to override the default.
	 * @return	array		processed login data array
	 * @internal
	 */
	function processLoginData($loginData, $security_level='') {
		global $TYPO3_CONF_VARS;

		$loginSecurityLevel = $security_level ? $security_level : ($TYPO3_CONF_VARS[$this->loginType]['loginSecurityLevel'] ? $TYPO3_CONF_VARS[$this->loginType]['loginSecurityLevel'] : $this->security_level);

			// Processing data according to the state it was submitted in.
			// ($loginSecurityLevel should reflect the security level used on the data being submitted in the login form)
		if ($loginSecurityLevel=='normal') {
			$loginData['uident_text'] = $loginData['uident'];
			$loginData['uident_challenged'] = (string)md5($loginData['uname'].':'.$loginData['uident'].':'.$loginData['chalvalue']);
			$loginData['uident_superchallenged'] = (string)md5($loginData['uname'].':'.(md5($loginData['uident'])).':'.$loginData['chalvalue']);
		} elseif ($loginSecurityLevel=='challenged') {
			$loginData['uident_text'] = '';
			$loginData['uident_challenged'] = $loginData['uident'];
			$loginData['uident_superchallenged'] = '';
		} elseif ($loginSecurityLevel=='superchallenged') {
			$loginData['uident_text'] = '';
			$loginData['uident_challenged'] = '';
			$loginData['uident_superchallenged'] = $loginData['uident'];
		}

			// The password "uident" is set based on the internal security setting of TYPO3
			// Example:
			// $this->security_level for the backend must be "superchallenged" because passwords are stored as md5-hashes in the be_users table
			// $this->security_level for the frontend must be "normal" or "challenged" because passwords are stored as clear-text in the fe_users tables
		if ($this->security_level=='normal') {
			$loginData['uident'] = $loginData['uident_text'];
		} elseif ($this->security_level=='challenged') {
			$loginData['uident'] = $loginData['uident_challenged'];
		} elseif ($this->security_level=='superchallenged') {
			$loginData['uident'] = $loginData['uident_superchallenged'];
		}

		return $loginData;
	}

	/**
	 * Returns an info array which provides additional information for auth services
	 *
	 * @return	array
	 * @internal
	 */
	function getAuthInfoArray() {
		$authInfo = array();
		$authInfo['loginType'] = $this->loginType;
		$authInfo['refInfo'] = parse_url(t3lib_div::getIndpEnv('HTTP_REFERER'));
		$authInfo['HTTP_HOST'] = t3lib_div::getIndpEnv('HTTP_HOST');
		$authInfo['REMOTE_ADDR'] = t3lib_div::getIndpEnv('REMOTE_ADDR');
		$authInfo['REMOTE_HOST'] = t3lib_div::getIndpEnv('REMOTE_HOST');
		$authInfo['security_level'] = $this->security_level;
		$authInfo['showHiddenRecords'] = $this->showHiddenRecords;
			// can be overidden in localconf by SVCONF:
		$authInfo['db_user']['table'] = $this->user_table;
		$authInfo['db_user']['userid_column'] = $this->userid_column;
		$authInfo['db_user']['username_column'] = $this->username_column;
		$authInfo['db_user']['userident_column'] = $this->userident_column;
		$authInfo['db_user']['usergroup_column'] = $this->usergroup_column;
		$authInfo['db_user']['enable_clause'] = $this->user_where_clause();
		$authInfo['db_user']['checkPidList'] = $this->checkPid ? $this->checkPid_value : '';
		$authInfo['db_user']['check_pid_clause'] = $this->checkPid ? ' AND pid IN ('.$GLOBALS['TYPO3_DB']->cleanIntList($authInfo['db_user']['checkPidList']).')' : '';
		$authInfo['db_groups']['table'] = $this->usergroup_table;
		return $authInfo;
	}

	/**
	 * Check the login data with the user record data for builtin login methods
	 *
	 * @param	array		user data array
	 * @param	array		login data array
	 * @param	string		Alternative security_level. Used when authentication services wants to override the default.
	 * @return	boolean		true if login data matched
	 */
	function compareUident($user, $loginData, $security_level='') {

		$OK = FALSE;
		$security_level = $security_level ? $security_level : $this->security_level;

		switch ($security_level)	{
			case 'superchallenged':		// If superchallenged the password in the database ($user[$this->userident_column]) must be a md5-hash of the original password.
			case 'challenged':

					// Check challenge stored in cookie:
				if ($this->challengeStoredInCookie)	{
					session_start();
					if ($_SESSION['login_challenge'] !== $loginData['chalvalue']) {
						if ($this->writeDevLog) 	t3lib_div::devLog('PHP Session stored challenge "'.$_SESSION['login_challenge'].'" and submitted challenge "'.$loginData['chalvalue'].'" did not match, so authentication failed!', 't3lib_userAuth', 2);
						$this->logoff();
						return FALSE;
					}
				}

				if ((string)$loginData['uident'] === (string)md5($user[$this->username_column].':'.$user[$this->userident_column].':'.$loginData['chalvalue']))	{
					$OK = TRUE;
				}
			break;
			default:	// normal
				if ((string)$loginData['uident'] === (string)$user[$this->userident_column])	{
					$OK = TRUE;
				}
			break;
		}

		return $OK;
	}

	/**
	 * Garbage collector, removing old expired sessions.
	 *
	 * @return	void
	 * @internal
	 */
	function gc() {
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
					$this->session_table,
					'ses_tstamp < '.intval(time()-($this->gc_time)).'
						AND ses_name = '.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->name, $this->session_table)
				);
	}

	/**
	 * Redirect to somewhere. Obsolete, deprecated etc.
	 *
	 * @return	void
	 * @ignore
	 */
	function redirect() {
		if (!$this->userid && $this->auth_url)	{	 // if no userid AND an include-document for login is given
			include ($this->auth_include);
			exit;
		}
	}

	/**
	 * DUMMY: Writes to log database table (in some extension classes)
	 *
	 * @param	integer		$type: denotes which module that has submitted the entry. This is the current list:  1=tce_db; 2=tce_file; 3=system (eg. sys_history save); 4=modules; 254=Personal settings changed; 255=login / out action: 1=login, 2=logout, 3=failed login (+ errorcode 3), 4=failure_warning_email sent
	 * @param	integer		$action: denotes which specific operation that wrote the entry (eg. 'delete', 'upload', 'update' and so on...). Specific for each $type. Also used to trigger update of the interface. (see the log-module for the meaning of each number !!)
	 * @param	integer		$error: flag. 0 = message, 1 = error (user problem), 2 = System Error (which should not happen), 3 = security notice (admin)
	 * @param	integer		$details_nr: The message number. Specific for each $type and $action. in the future this will make it possible to translate errormessages to other languages
	 * @param	string		$details: Default text that follows the message
	 * @param	array		$data: Data that follows the log. Might be used to carry special information. If an array the first 5 entries (0-4) will be sprintf'ed the details-text...
	 * @param	string		$tablename: Special field used by tce_main.php. These ($tablename, $recuid, $recpid) holds the reference to the record which the log-entry is about. (Was used in attic status.php to update the interface.)
	 * @param	integer		$recuid: Special field used by tce_main.php. These ($tablename, $recuid, $recpid) holds the reference to the record which the log-entry is about. (Was used in attic status.php to update the interface.)
	 * @param	integer		$recpid: Special field used by tce_main.php. These ($tablename, $recuid, $recpid) holds the reference to the record which the log-entry is about. (Was used in attic status.php to update the interface.)
	 * @return	void
	 * @see t3lib_userauthgroup::writelog()
	 */
	function writelog($type,$action,$error,$details_nr,$details,$data,$tablename,$recuid,$recpid)	{
	}

	/**
	 * DUMMY: Check login failures (in some extension classes)
	 *
	 * @return	void
	 * @ignore
	 */
	function checkLogFailures()	{
	}

	/**
	 * Raw initialization of the be_user with uid=$uid
	 * This will circumvent all login procedures and select a be_users record from the database and set the content of ->user to the record selected. Thus the BE_USER object will appear like if a user was authenticated - however without a session id and the fields from the session table of course.
	 * Will check the users for disabled, start/endtime, etc. ($this->user_where_clause())
	 *
	 * @param	integer		The UID of the backend user to set in ->user
	 * @return	void
	 * @internal
	 * @see SC_mod_tools_be_user_index::compareUsers(), SC_mod_user_setup_index::simulateUser(), freesite_admin::startCreate()
	 */
	function setBeUserByUid($uid)	{
		$this->user = $this->getRawUserByUid($uid);
	}

	/**
	 * Raw initialization of the be_user with username=$name
	 *
	 * @param	string		The username to look up.
	 * @return	void
	 * @see	t3lib_userAuth::setBeUserByUid()
	 * @internal
	 */
	function setBeUserByName($name)	{
		$this->user = $this->getRawUserByName($name);
	}

	/**
	 * Fetching raw user record with uid=$uid
	 *
	 * @param	integer		The UID of the backend user to set in ->user
	 * @return	array		user record or FALSE
	 * @internal
	 */
	function getRawUserByUid($uid)	{
		$user = FALSE;
		$dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->user_table, 'uid='.intval($uid).' '.$this->user_where_clause());
		if ($dbres)	{
			$user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres);
			$GLOBALS['TYPO3_DB']->sql_free_result($dbres);
		}
		return $user;
	}

	/**
	 * Fetching raw user record with username=$name
	 *
	 * @param	string		The username to look up.
	 * @return	array		user record or FALSE
	 * @see	t3lib_userAuth::getUserByUid()
	 * @internal
	 */
	function getRawUserByName($name)	{
		$user = FALSE;
		$dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->user_table, 'username='.$GLOBALS['TYPO3_DB']->fullQuoteStr($name, $this->user_table).' '.$this->user_where_clause());
		if ($dbres)	{
			$user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres);
			$GLOBALS['TYPO3_DB']->sql_free_result($dbres);
		}
		return $user;
	}















	/*************************
	 *
	 * Create/update user - EXPERIMENTAL
	 *
	 *************************/

	/**
	 * Get a user from DB by username
	 * provided for usage from services
	 *
	 * @param	array		User db table definition: $this->db_user
	 * @param	string		user name
	 * @param	string		additional WHERE clause: " AND ...
	 * @return	mixed		user array or FALSE
	 */
	function fetchUserRecord($dbUser, $username, $extraWhere='' )	{
		$user = FALSE;

		$usernameClause = $username ? ($dbUser['username_column'].'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($username, $dbUser['table'])) : '';

		if ($username || $extraWhere)	{

				// Look up the user by the username and/or extraWhere:
			$dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						$dbUser['table'],
							$usernameClause.
							$dbUser['check_pid_clause'].
							$dbUser['enable_clause'].
							$extraWhere
					);

			if ($dbres)	{
				$user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres);
				$GLOBALS['TYPO3_DB']->sql_free_result($dbres);
			}
		}
		return $user;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_userauth.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['t3lib/class.t3lib_userauth.php']);
}
?>