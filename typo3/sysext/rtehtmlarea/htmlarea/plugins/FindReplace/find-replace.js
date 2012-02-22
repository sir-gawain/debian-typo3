/***************************************************************
*  Copyright notice
*
*  (c) 2004 Cau guanabara <caugb@ibest.com.br>
*  (c) 2005-2008 Stanislas Rolland <typo3(arobas)sjbr.ca>
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
*  This script is a modified version of a script published under the htmlArea License.
*  A copy of the htmlArea License may be found in the textfile HTMLAREA_LICENSE.txt.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/*
 * Find and Replace Plugin for TYPO3 htmlArea RTE
 *
 * TYPO3 SVN ID: $Id: find-replace.js 4102 2008-09-13 23:04:59Z stan $
 */
FindReplace = HTMLArea.Plugin.extend({

	constructor : function(editor, pluginName) {
		this.base(editor, pluginName);
	},

	/*
	 * This function gets called by the class constructor
	 */
	configurePlugin : function(editor) {

		/*
		 * Registering plugin "About" information
		 */
		var pluginInformation = {
			version		: "1.2",
			developer	: "Cau Guanabara & Stanislas Rolland",
			developerUrl	: "mailto:caugb@ibest.com.br",
			copyrightOwner	: "Cau Guanabara & Stanislas Rolland",
			sponsor		: "Independent production & Fructifor Inc.",
			sponsorUrl	: "http://www.netflash.com.br/gb/HA3-rc1/examples/find-replace.html",
			license		: "GPL"
		};
		this.registerPluginInformation(pluginInformation);

		/*
		 * Registering the button
		 */
		var buttonId = "FindReplace";
		var buttonConfiguration = {
			id		: buttonId,
			tooltip		: this.localize("Find and Replace"),
			action		: "onButtonPress",
			dialog		: true
		};
		this.registerButton(buttonConfiguration);

		this.popupWidth = 420;
		this.popupHeight = 360;

		return true;
	},

	/*
	 * This function gets called when the button was pressed.
	 *
	 * @param	object		editor: the editor instance
	 * @param	string		id: the button id or the key
	 *
	 * @return	boolean		false if action is completed
	 */
	onButtonPress : function (editor, id, target) {
			// Could be a button or its hotkey
		var buttonId = this.translateHotKey(id);
		buttonId = buttonId ? buttonId : id;

		var sel = this.editor.getSelectedHTML(), param = null;
		if (/\w/.test(sel)) {
			sel = sel.replace(/<[^>]*>/g,"");
			sel = sel.replace(/&nbsp;/g,"");
		}
		if (/\w/.test(sel)) {
			param = { fr_pattern: sel };
		}

		this.dialog = this.openDialog("FindReplace", this.makeUrlFromPopupName("find_replace"), null, param, {width:this.popupWidth, height:this.popupHeight});
		return false;
	}
});
