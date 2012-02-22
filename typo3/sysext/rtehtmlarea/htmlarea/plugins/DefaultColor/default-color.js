/***************************************************************
*  Copyright notice
*
*  (c) 2008-2009 Stanislas Rolland <typo3(arobas)sjbr.ca>
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
 * Default Color Plugin for TYPO3 htmlArea RTE
 *
 * TYPO3 SVN ID: $Id: default-color.js 6539 2009-11-25 14:49:14Z stucki $
 */
DefaultColor = HTMLArea.Plugin.extend({
	
	constructor : function(editor, pluginName) {
		this.base(editor, pluginName);
	},
	
	/*
	 * This function gets called by the class constructor
	 */
	configurePlugin : function(editor) {
		
		this.buttonsConfiguration = this.editorConfiguration.buttons;
		
		/*
		 * Registering plugin "About" information
		 */
		var pluginInformation = {
			version		: "1.0",
			developer	: "Stanislas Rolland",
			developerUrl	: "http://www.sjbr.ca/",
			copyrightOwner	: "Stanislas Rolland",
			sponsor		: "SJBR",
			sponsorUrl	: "http://www.sjbr.ca/",
			license		: "GPL"
		};
		this.registerPluginInformation(pluginInformation);
		
		/*
		 * Registering the buttons
		 */
		var buttonList = this.buttonList;
		for (var i = 0; i < buttonList.length; ++i) {
			var button = buttonList[i];
			buttonId = button[0];
			var buttonConfiguration = {
				id		: buttonId,
				tooltip		: this.localize(buttonId.toLowerCase()),
				action		: "onButtonPress",
				hotKey		: (this.buttonsConfiguration[button[1]] ? this.buttonsConfiguration[button[1]].hotKey : null),
				dialog		: true
			};
			this.registerButton(buttonConfiguration);
		}
		
		return true;
	 },
	 
	/*
	 * The list of buttons added by this plugin
	 */
	buttonList : [
		["ForeColor", "textcolor"],
		["HiliteColor", "bgcolor"]
	],
	
	/*
	 * Conversion object: button name or command name to corresponding style property name
	 */
	styleProperty : {
		ForeColor	: "color",
		HiliteColor	: "backgroundColor"
	},
	 
	/*
	 * This function gets called when the button was pressed.
	 *
	 * @param	object		editor: the editor instance
	 * @param	string		id: the button id or the key
	 * @param	object		target: the target element of the contextmenu event, when invoked from the context menu
	 *
	 * @return	boolean		false if action is completed
	 */
	onButtonPress : function(editor, id, target) {
			// Could be a button or its hotkey
		var buttonId = this.translateHotKey(id);
		buttonId = buttonId ? buttonId : id;
		this.commandId = buttonId;
		switch (buttonId) {
			case "HiliteColor":
				this.dialog = this.openDialog(buttonId, this.makeUrlFromPopupName("select_color"), "setColor", HTMLArea._colorToRgb(this.editor._doc.queryCommandValue(HTMLArea.is_ie ? "BackColor" : this.commandId)), {width:300, height:210});
				break;
			case "ForeColor":
				this.dialog = this.openDialog(buttonId, this.makeUrlFromPopupName("select_color"), "setColor", HTMLArea._colorToRgb(this.editor._doc.queryCommandValue(this.commandId)), {width:300, height:210});
				break;
			default:
				this.dialog = this.openDialog(buttonId, this.makeUrlFromPopupName("select_color"), "returnToCaller", HTMLArea._colorToRgb("000000"), {width:300, height:210});
				break;
		}
		return false;
	},
	
	/*
	 * Set the color
	 *
	 * @param	object		param: the returned color
	 *
	 * @return	boolean		false
	 */
	setColor : function(color) {
		var editor = this.editor;
		if (color && editor.endPointsInSameBlock()) {
			var selection = editor._getSelection();
			var range = editor._createRange(selection);
			var element = editor._doc.createElement("span");
			element.style[this.styleProperty[this.commandId]] = "#" + color;
			editor.wrapWithInlineElement(element, selection, range);
			if (HTMLArea.is_gecko) {
				range.detach();
			}
		}
		return false;
	},
	
	/*
	 * Return to caller
	 *
	 * @param	object		param: the returned color
	 *
	 * @return	boolean		false
	 */
	returnToCaller : function(color) {
		if (color && this.editor.plugins[this.commandId]
				&& this.editor.plugins[this.commandId].instance
				&& this.editor.plugins[this.commandId].instance.dialog
				&& this.editor.plugins[this.commandId].instance.dialog.dialogWindow) {
			this.editor.plugins[this.commandId].instance.dialog.dialogWindow.insertColor("#" + color);
		}
		return false;
	},
	
	/*
	 * This function gets called when the toolbar is updated
	 */
	onUpdateToolbar : function () {
		var editor = this.editor;
		if (editor.getMode() === "wysiwyg" && editor.isEditable()) {
			var buttonId;
			for (var i = 0, n = this.buttonList.length; i < n; ++i) {
				buttonId = this.buttonList[i][0];
				var obj = editor._toolbarObjects[buttonId];
				if ((typeof(obj) !== "undefined")) {
					obj.state("enabled", editor.endPointsInSameBlock());
				}
			}
		}
	}
});

