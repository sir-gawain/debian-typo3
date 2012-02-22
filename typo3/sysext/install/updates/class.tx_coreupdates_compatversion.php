<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2009 Sebastian Kurfuerst <sebastian@garbage-group.de>
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
 * Contains the update class for the compatibility version. Used by the update wizard in the install tool.
 *
 * @author Sebastian Kurfuerst <sebastian@garbage-group.de
 * @version $Id: class.tx_coreupdates_compatversion.php 9769 2010-12-16 13:37:08Z ohader $
 */
class tx_coreupdates_compatversion {
	var $versionNumber;	// version number coming from t3lib_div::int_from_ver()

	/**
	 * parent object
	 *
	 * @var tx_install
	 */
	var $pObj;
	var $userInput;	// user input

	/**
	 * Function which checks if update is needed. Called in the beginning of an update process.
	 *
	 * @param	string		pointer to description for the update
	 * @return	boolean		true if update is needs to be performed, false otherwise.
	 */
	function checkForUpdate(&$description)	{
		global $TYPO3_CONF_VARS;

		if ($this->compatVersionIsCurrent())	{
			$description = '<strong>Up to date!</strong><br />If you do not use the wizard, your current TYPO3 installation is configured to use all the features included in the current release '.TYPO3_version.'.<br />
			There are two possibilities that you see this screen:<ol><li><b>You just updated from a previous version of TYPO3:</b>
			Because of some new features, the frontend output of your site might have changed. To emulate the "old" frontend behavior, change the compatibility version by continuing to step 2.
			This is <b>recommended</b> after every update to make sure the frontend output is not altered. When re-running the wizard, you will see the changes needed for using the new features.
			<i>Please continue to step two.</i></li>
			<li><b>You just made a fresh install of TYPO3:</b>
			Perfect! All new features will be used.
			<i>You can stop here and do not need this wizard now.</i></li></ol>';

			if (!$TYPO3_CONF_VARS['SYS']['compat_version'])	{
				$description .= '
				The compatibility version has been set to the current TYPO3 version. This is a stamp and has no impact for your installation.';
			}
		} else {
			$description = 'Your current TYPO3 installation is configured to <b>behave like version ' . htmlspecialchars($TYPO3_CONF_VARS['SYS']['compat_version']) . '</b> of TYPO3. If you just upgraded from this version, you most likely want to <b>use new features</b> as well.</p><p>In the next step, you will see the things that need to be adjusted to make your installation compatible with the new features.';
		}

		return 1;	// Return 1 in any case so user has possibility to switch back to a previous compat_version.
	}

	/**
	 * second step: get user input if needed
	 *
	 * @param	string		input prefix, all names of form fields have to start with this. Append custom name in [ ... ]
	 * @return	string		HTML output
	 */
	function getUserInput($inputPrefix)	{
		global $TYPO3_CONF_VARS;
		if ($this->compatVersionIsCurrent())	{
			$content = '<strong>You updated from an older version of TYPO3</strong>:<br />
			<label for="'.$inputPrefix.'[version]">Select the version where you have upgraded from:</label> <select name="'.$inputPrefix.'[version]" id="'.$inputPrefix.'[version]">';
			$versions = array(
				'3.8' => '<= 3.8',
				'4.1' => '<= 4.1',
				'4.2' => '<= 4.2',
			);
			foreach ($versions as $singleVersion => $caption)	{
				$content .= '<option value="'.$singleVersion.'">'.$caption.'</option>';
			}
			$content .= '</select>';
		} else {
			$content = 'TYPO3 output is currently compatible to version ' . htmlspecialchars($TYPO3_CONF_VARS['SYS']['compat_version']) . '. To use all the new features in the current TYPO3 version, make sure you follow the guidelines below to upgrade without problems.<br />
			<p><strong>Follow the steps below carefully and confirm every step!</strong><br />You will see this list again after you performed the update.</p>';

			$content .= $this->showChangesNeeded($inputPrefix);

			$content.= '<p><input type="checkbox" name="'.$inputPrefix.'[compatVersion][all]" id="'.$inputPrefix.'[compatVersion][all]" value="1">&nbsp;<strong><label for="'.$inputPrefix.'[compatVersion][all]">Check all (ignore selection above)<br />WARNING: this might break the output of your website.</label></strong></p><hr />';
		}
		return $content;
	}

	/**
	 * Checks if user input is valid
	 *
	 * @param	string		pointer to output custom messages
	 * @return	boolean		true if user input is correct, then the update is performed. When false, return to getUserInput
	 */
	function checkUserInput(&$customMessages)	{
		global $TYPO3_CONF_VARS;

		if ($this->compatVersionIsCurrent())	{
			return 1;
		} else {
			if ($this->userInput['compatVersion']['all'])	{
				return 1;
			} else {
				$performUpdate = 1;
				$oldVersion = t3lib_div::int_from_ver($TYPO3_CONF_VARS['SYS']['compat_version']);
				$currentVersion = t3lib_div::int_from_ver(TYPO3_branch);

				foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['ext/install']['compat_version'] as $internalName => $details)	{
					if ($details['version'] > $oldVersion && $details['version'] <= $currentVersion)	{
						if (!$this->userInput['compatVersion'][$internalName])	{
							$performUpdate = 0;
							$customMessages = 'If you want to update the compatibility version, you need to confirm all checkboxes on the previous page.';
							break;
						}
					}
				}
				return $performUpdate;
			}
		}
	}

	/**
	 * Performs the update itself
	 *
	 * @param	array		pointer where to insert all DB queries made, so they can be shown to the user if wanted
	 * @param	string		pointer to output custom messages
	 * @return	boolean		true if update succeeded, false otherwise
	 */
	function performUpdate(&$dbQueries, &$customMessages)	{
		$customMessages = '';

			// if we just set it to an older version
		if ($this->userInput['version'])	{
			 $customMessages .= 'If you want to see what you need to do to use the new features, run the update wizard again!';
		}

		$linesArr = $this->pObj->writeToLocalconf_control();
		$version = $this->userInput['version'] ? $this->userInput['version'] : TYPO3_branch;
		$this->pObj->setValueInLocalconfFile($linesArr, '$TYPO3_CONF_VARS[\'SYS\'][\'compat_version\']', $version);
		$this->pObj->writeToLocalconf_control($linesArr,0);
		$customMessages.= '<br />The compatibility version has been set to '.$version.'.';

		return 1;
	}


	/**********************
	 *
	 * HELPER FUNCTIONS - just used in this update method
	 *
	 **********************/
	/**
	 * checks if compatibility version is set to current version
	 *
	 * @return	boolean		true if compat version is equal the current version
	 */
	function compatVersionIsCurrent()	{
		global $TYPO3_CONF_VARS;
		if (TYPO3_branch != $TYPO3_CONF_VARS['SYS']['compat_version'])	{
			return 0;
		} else {
			return 1;
		}
	}

	/**
	 * show changes needed
	 *
	 * @param	string		input prefix to prepend all form fields with.
	 * @return	string		HTML output
	 */
	function showChangesNeeded($inputPrefix = '')	{
		global $TYPO3_CONF_VARS;
		$oldVersion = t3lib_div::int_from_ver($TYPO3_CONF_VARS['SYS']['compat_version']);
		$currentVersion = t3lib_div::int_from_ver(TYPO3_branch);

		$tableContents = '';

		if (is_array($TYPO3_CONF_VARS['SC_OPTIONS']['ext/install']['compat_version'])) {
			$updateWizardBoxes = '';
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['ext/install']['compat_version'] as $internalName => $details)	{
				if ($details['version'] > $oldVersion && $details['version'] <= $currentVersion)	{
					$description = str_replace(chr(10),'<br />',$details['description']);
					$description_acknowledge = (isset($details['description_acknowledge']) ? str_replace(chr(10),'<br />',$details['description_acknowledge']) : '');

					$updateWizardBoxes.= '
						<div style="border: 1px solid; padding: 10px; margin: 10px; padding-top: 0px; width: 500px;">
							<h3>'.(isset($details['title'])?$details['title']:$internalName).'</h3>
							<p>'.$description.'</p>'.
							(strlen($description_acknowledge) ? '<p>'.$description_acknowledge.'</p>' : '').
							(strlen($inputPrefix) ? '<p><input type="checkbox" name="'.$inputPrefix.'[compatVersion]['.$internalName.']" id="'.$inputPrefix.'[compatVersion]['.$internalName.']" value="1">&nbsp;<strong><label for="'.$inputPrefix.'[compatVersion]['.$internalName.']">Acknowledged</label></strong></p>' : '').'
						</div>';
				}
			}
		}
		if (strlen($updateWizardBoxes))	{
			return '<table><tr><td>'.$updateWizardBoxes.'</td></tr></table>';
		}
		return '';
	}
}
?>