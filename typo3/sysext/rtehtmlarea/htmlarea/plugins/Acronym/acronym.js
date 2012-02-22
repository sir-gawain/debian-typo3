/***************************************************************
*  Copyright notice
*
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
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/*
 * Acronym plugin for htmlArea RTE
 *
 * TYPO3 SVN ID: $Id: acronym.js 4102 2008-09-13 23:04:59Z stan $
 */
Acronym = HTMLArea.Plugin.extend({
	
	constructor : function(editor, pluginName) {
		this.base(editor, pluginName);
	},
	
	/*
	 * This function gets called by the class constructor
	 */
	configurePlugin : function(editor) {
		
		this.pageTSConfiguration = this.editorConfiguration.buttons.acronym;
		this.acronymUrl = this.pageTSConfiguration.acronymUrl;
		this.acronymModulePath = this.pageTSConfiguration.pathAcronymModule;
		
		/*
		 * Registering plugin "About" information
		 */
		var pluginInformation = {
			version		: "1.6",
			developer	: "Stanislas Rolland",
			developerUrl	: "http://www.fructifor.ca/",
			copyrightOwner	: "Stanislas Rolland",
			sponsor		: "Fructifor Inc.",
			sponsorUrl	: "http://www.fructifor.ca/",
			license		: "GPL"
		};
		this.registerPluginInformation(pluginInformation);
		
		/*
		 * Registering the button
		 */
		var buttonId = "Acronym";
		var buttonConfiguration = {
			id		: buttonId,
			tooltip		: this.localize("Insert/Modify Acronym"),
			action		: "onButtonPress",
			hide		: (this.pageTSConfiguration.noAcronym && this.pageTSConfiguration.noAbbr),
			dialog		: true
		};
		this.registerButton(buttonConfiguration);
		
		return true;
	 },
	 
	/*
	 * This function gets called when the button was pressed
	 *
	 * @param	object		editor: the editor instance
	 * @param	string		id: the button id or the key
	 *
	 * @return	boolean		false if action is completed
	 */
	onButtonPress : function(editor, id) {
		this.dialog = this.openDialog("Acronym", this.makeUrlFromModulePath(this.acronymModulePath), null, null, {width:580, height:280});
		return false;
	},
	
	/*
	 * This function removes the given markup element
	 */
	removeMarkup : function(element) {
		var bookmark = this.editor.getBookmark(this.editor._createRange(this.editor._getSelection()));
		var parent = element.parentNode;
		while (element.firstChild) {
			parent.insertBefore(element.firstChild, element);
		}
		parent.removeChild(element);
		this.editor.selectRange(this.editor.moveToBookmark(bookmark));
	},
	
	/*
	 * This function gets called when the toolbar is updated
	 */
	onUpdateToolbar : function () {
		if (this.editor.getMode() === "wysiwyg" && this.editor.isEditable()) {
			var buttonId = "Acronym";
			if (this.isButtonInToolbar(buttonId)) {
				var el = this.editor.getParentElement();
				if (el) {
					this.editor._toolbarObjects[buttonId].state("enabled", !((el.nodeName.toLowerCase() == "acronym" && this.pageTSConfiguration.noAcronym) || (el.nodeName.toLowerCase() == "abbr" && this.pageTSConfiguration.noAbbr)));
					this.editor._toolbarObjects[buttonId].state("active", ((el.nodeName.toLowerCase() == "acronym" && !this.pageTSConfiguration.noAcronym) || (el.nodeName.toLowerCase() == "abbr" && !this.pageTSConfiguration.noAbbr)));
				}
			}
		}
	}
});

