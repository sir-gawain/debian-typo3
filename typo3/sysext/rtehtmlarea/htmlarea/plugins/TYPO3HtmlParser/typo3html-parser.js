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
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * TYPO3HtmlParser Plugin for TYPO3 htmlArea RTE
 *
 * TYPO3 SVN ID: $Id: typo3html-parser.js 4670 2009-01-08 21:10:33Z stan $
 */
TYPO3HtmlParser = HTMLArea.Plugin.extend({
	
	constructor : function(editor, pluginName) {
		this.base(editor, pluginName);
	},
	
	/*
	 * This function gets called by the class constructor
	 */
	configurePlugin : function(editor) {
		
		this.pageTSConfiguration = this.editorConfiguration.buttons.cleanword;
		this.parseHtmlModulePath = this.pageTSConfiguration.pathParseHtmlModule;
		
		/*
		 * Registering plugin "About" information
		 */
		var pluginInformation = {
			version		: "1.8",
			developer	: "Stanislas Rolland",
			developerUrl	: "http://www.sjbr.ca/",
			copyrightOwner	: "Stanislas Rolland",
			sponsor		: "SJBR",
			sponsorUrl	: "http://www.sjbr.ca/",
			license		: "GPL"
		};
		this.registerPluginInformation(pluginInformation);
		
		/*
		 * Registering the (hidden) button
		 */
		var buttonId = "CleanWord";
		var buttonConfiguration = {
			id		: buttonId,
			tooltip		: this.localize(buttonId + "-Tooltip"),
			action		: "onButtonPress",
			hide		: true
		};
		this.registerButton(buttonConfiguration);
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
		var bookmark = this.editor.getBookmark(this.editor._createRange(this.editor._getSelection()));
		this.clean(this.editor._doc.body, bookmark);
		return false;
	},
	
	onGenerate : function () {
		var doc = this.editor._doc;
		var cleanFunctRef = this.makeFunctionReference("wordCleanHandler");
		HTMLArea._addEvents((HTMLArea.is_ie ? doc.body : doc), ["paste","dragdrop","drop"], TYPO3HtmlParser.wordCleanHandler, true);
	},
	
	clean : function(body, bookmark) {
		var editor = this.editor;
		var content = {
			editorNo : this.editorNumber,
			content : body.innerHTML
		};
		this.postData(	this.parseHtmlModulePath,
				content,
				function(response) {
					editor.setHTML(response);
					editor.selectRange(editor.moveToBookmark(bookmark));
				}
		);
	}
});

/*
 * Closure avoidance for IE
 */
TYPO3HtmlParser.cleanLater = function (editorNumber) {
	var editor = RTEarea[editorNumber].editor;
	var bookmark = editor.getBookmark(editor._createRange(editor._getSelection()));
	editor.plugins.TYPO3HtmlParser.instance.clean(editor._doc.body, bookmark);
};

/*
 * Handler for paste, dragdrop and drop events
 */
TYPO3HtmlParser.wordCleanHandler = function (ev) {
	if (!ev) var ev = window.event;
	var target = ev.target ? ev.target : ev.srcElement;
	var owner = target.ownerDocument ? target.ownerDocument : target;
	if (HTMLArea.is_ie) { // IE5.5 does not report any ownerDocument
		while (owner.parentElement) { owner = owner.parentElement; }
	}
	var editor = RTEarea[owner._editorNo].editor;
	
		// If we dropped an image dragged from the TYPO3 Image plugin, let's close the dialog window
	if (typeof(HTMLArea.Dialog) != "undefined" && HTMLArea.Dialog.TYPO3Image) {
		HTMLArea.Dialog.TYPO3Image.close();
	} else {
		window.setTimeout("TYPO3HtmlParser.cleanLater(\'" + editor._editorNumber + "\');", 250);
	}
};

