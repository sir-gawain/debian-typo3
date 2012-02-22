/***************************************************************
*  Copyright notice
*
*  (c) 2002-2004, interactivetools.com, inc.
*  (c) 2003-2004 dynarch.com
*  (c) 2004-2008 Stanislas Rolland <typo3(arobas)sjbr.ca>
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
 * Main script of TYPO3 htmlArea RTE
 *
 * TYPO3 SVN ID: $Id: htmlarea.js 4670 2009-01-08 21:10:33Z stan $
 */

/***************************************************
 *  EDITOR INITIALIZATION AND CONFIGURATION
 ***************************************************/
	// Avoid re-starting on Ajax call
if (typeof(HTMLArea) != "function") {

/*
 * HTMLArea object constructor.
 */
HTMLArea = function(textarea, config) {
	if (HTMLArea.checkSupportedBrowser()) {
		if (typeof(config) == "undefined") this.config = new HTMLArea.Config();
			else this.config = config;
		this._htmlArea = null;
		this._textArea = textarea;
		this._editMode = "wysiwyg";
		this.plugins = {};
		this._timerToolbar = null;
		this._undoQueue = new Array();
		this._undoPos = -1;
		this._customUndo = true;
		this.doctype = '';
		this.eventHandlers = {};
	}
};

/*
 * Browser identification
 */
HTMLArea.agt = navigator.userAgent.toLowerCase();
HTMLArea.is_opera  = (HTMLArea.agt.indexOf("opera") != -1);
HTMLArea.is_ie = (HTMLArea.agt.indexOf("msie") != -1) && !HTMLArea.is_opera;
HTMLArea.is_safari = (HTMLArea.agt.indexOf("webkit") != -1);
HTMLArea.is_gecko  = (navigator.product == "Gecko") || HTMLArea.is_opera;
HTMLArea.is_chrome = HTMLArea.is_safari && (HTMLArea.agt.indexOf("chrome") != -1);
// Check on MacOS Wamcom version 1.3 but exclude Firefox rv 1.8.1.3
HTMLArea.is_wamcom = (HTMLArea.agt.indexOf("wamcom") != -1) || (HTMLArea.is_gecko && HTMLArea.agt.indexOf("1.3") != -1 && HTMLArea.agt.indexOf(".1.3") == -1);

/*
 * A log for troubleshooting
 */
HTMLArea._appendToLog = function(str){
	if(HTMLArea._debugMode) {
		var log = document.getElementById("HTMLAreaLog");
		if(log) {
			log.appendChild(document.createTextNode(str));
			log.appendChild(document.createElement("br"));
		}
	}
};

/*
 * Get a script using asynchronous XMLHttpRequest
 */
HTMLArea.MSXML_XMLHTTP_PROGIDS = new Array("Msxml2.XMLHTTP.5.0", "Msxml2.XMLHTTP.4.0", "Msxml2.XMLHTTP.3.0", "Msxml2.XMLHTTP", "Microsoft.XMLHTTP");
HTMLArea.XMLHTTPResponseHandler = function (i) {
	return (function() {
		var url = HTMLArea._scripts[i];
		if (HTMLArea._request[i].readyState != 4) return;
		if (HTMLArea._request[i].status == 200) {
			try {
				eval(HTMLArea._request[i].responseText);
				HTMLArea._scriptLoaded[i] = true;
				i = null;
			} catch (e) {
				HTMLArea._appendToLog("ERROR [HTMLArea::getScript]: Unable to get script " + url + ": " + e);
			}
		} else {
			HTMLArea._appendToLog("ERROR [HTMLArea::getScript]: Unable to get " + url + " . Server reported " + HTMLArea._request[i].status);
		}
	});
};
HTMLArea._getScript = function (i,asynchronous,url) {
	if (typeof(url) == "undefined") var url = HTMLArea._scripts[i];
	if (typeof(asynchronous) == "undefined") var asynchronous = true;
	if (window.XMLHttpRequest) HTMLArea._request[i] = new XMLHttpRequest();
		else if (window.ActiveXObject) {
			var success = false;
			for (var k = 0; k < HTMLArea.MSXML_XMLHTTP_PROGIDS.length && !success; k++) {
				try {
					HTMLArea._request[i] = new ActiveXObject(HTMLArea.MSXML_XMLHTTP_PROGIDS[k]);
					success = true;
				} catch (e) { }
			}
			if (!success) return false;
		}
	var request = HTMLArea._request[i];
	if (request) {
		HTMLArea._appendToLog("[HTMLArea::getScript]: Requesting script " + url);
		request.open("GET", url, asynchronous);
		if (asynchronous) request.onreadystatechange = HTMLArea.XMLHTTPResponseHandler(i);
		if (window.XMLHttpRequest) request.send(null);
			else if (window.ActiveXObject) request.send();
		if (!asynchronous) {
			if (request.status == 200) return request.responseText;
				else return '';
		}
		return true;
	} else {
		return false;
	}
};

/*
 * Wait for the loading process to complete
 */
HTMLArea.checkInitialLoad = function() {
	var scriptsLoaded = true;
	for (var i = HTMLArea._scripts.length; --i >= 0;) {
		scriptsLoaded = scriptsLoaded && HTMLArea._scriptLoaded[i];
	}
	if(HTMLArea.loadTimer) window.clearTimeout(HTMLArea.loadTimer);
	if (scriptsLoaded) {
		HTMLArea.is_loaded = true;
		HTMLArea._appendToLog("[HTMLArea::init]: All scripts successfully loaded.");
		HTMLArea._appendToLog("[HTMLArea::init]: Editor url set to: " + _editor_url);
		HTMLArea._appendToLog("[HTMLArea::init]: Editor skin CSS set to: " + _editor_CSS);
		HTMLArea._appendToLog("[HTMLArea::init]: Editor content skin CSS set to: " + _editor_edited_content_CSS);
		if (window.ActiveXObject) {
			for (var i = HTMLArea._scripts.length; --i >= 0;) {
				HTMLArea._request[i].onreadystatechange = new Function();
				HTMLArea._request[i] = null;
			}
		}
	} else {
		HTMLArea.loadTimer = window.setTimeout("HTMLArea.checkInitialLoad();", 200);
		return false;
	}
};
/*
 * Build stack of scripts to be loaded
 */
HTMLArea.loadScript = function(url, plugin) {
	if (plugin) url = _editor_url + "/plugins/" + plugin + '/' + url;
	if (HTMLArea.is_opera) url = _typo3_host_url + url;
	if (HTMLArea._compressedScripts && url.indexOf("compressed") == -1) url = url.replace(/\.js$/gi, "_compressed.js");
	HTMLArea._scripts.push(url);
};
/*
 * Initial load
 */
HTMLArea.init = function() {
	if (typeof(_editor_url) != "string") {
		window.setTimeout("HTMLArea.init();", 50);
	} else {
			// Set some basic paths
			// Leave exactly one backslash at the end of _editor_url
		_editor_url = _editor_url.replace(/\x2f*$/, '/');
		if (typeof(_editor_skin) == "string") _editor_skin = _editor_skin.replace(/\x2f*$/, '/');
			else _editor_skin = _editor_url + "skins/default/";
		if (typeof(_editor_CSS) != "string") _editor_CSS = _editor_url + "skins/default/htmlarea.css";
		if (typeof(_editor_edited_content_CSS) != "string") _editor_edited_content_CSS = _editor_skin + "htmlarea-edited-content.css";
		if (typeof(_editor_lang) == "string") _editor_lang = _editor_lang ? _editor_lang.toLowerCase() : "en";
		HTMLArea.editorCSS = _editor_CSS;
			// Initialize event cache
		HTMLArea._eventCache = HTMLArea._eventCacheConstructor();
			// Set troubleshooting mode
		HTMLArea._debugMode = false;
		if (typeof(_editor_debug_mode) != "undefined") HTMLArea._debugMode = _editor_debug_mode;
			// Using compressed scripts
		HTMLArea._compressedScripts = false;
		if (typeof(_editor_compressed_scripts) != "undefined") HTMLArea._compressedScripts = _editor_compressed_scripts;
			// Localization of core script
		HTMLArea.I18N = HTMLArea_langArray;
			// Build array of scripts to be loaded
		HTMLArea.is_loaded = false;
		HTMLArea.loadTimer;
		HTMLArea._scripts = [];
		HTMLArea._scriptLoaded = [];
		HTMLArea._request = [];
		if (HTMLArea.is_gecko) HTMLArea.loadScript(RTEarea[0]["htmlarea-gecko"] ? RTEarea[0]["htmlarea-gecko"] : _editor_url + "htmlarea-gecko.js");
		if (HTMLArea.is_ie) HTMLArea.loadScript(RTEarea[0]["htmlarea-ie"] ? RTEarea[0]["htmlarea-ie"] : _editor_url + "htmlarea-ie.js");
		for (var i = 0, n = HTMLArea_plugins.length; i < n; i++) {
			HTMLArea.loadScript(HTMLArea_plugins[i]);
		}
			// Get all the scripts
		if (window.XMLHttpRequest || window.ActiveXObject) {
			try {
				var success = true;
				for (var i = 0, n = HTMLArea._scripts.length; i < n && success; i++) {
					success = success && HTMLArea._getScript(i);
				}
			} catch (e) {
				HTMLArea._appendToLog("ERROR [HTMLArea::init]: Unable to use XMLHttpRequest: "+ e);
			}
			if (success) {
				HTMLArea.checkInitialLoad();
			} else {
				if (HTMLArea.is_ie) window.setTimeout('alert(HTMLArea.I18N.msg["ActiveX-required"]);', 200);
			}
		} else {
			if (HTMLArea.is_ie) alert(HTMLArea.I18N.msg["ActiveX-required"]);
		}
	}
};

/*
 * Compile some regular expressions
 */
HTMLArea.RE_tagName = /(<\/|<)\s*([^ \t\n>]+)/ig;
HTMLArea.RE_doctype = /(<!doctype((.|\n)*?)>)\n?/i;
HTMLArea.RE_head    = /<head>((.|\n)*?)<\/head>/i;
HTMLArea.RE_body    = /<body>((.|\n)*?)<\/body>/i;
HTMLArea.Reg_body = new RegExp("<\/?(body)[^>]*>", "gi");
HTMLArea.reservedClassNames = /htmlarea/;
HTMLArea.RE_email    = /([0-9a-z]+([a-z0-9_-]*[0-9a-z])*){1}(\.[0-9a-z]+([a-z0-9_-]*[0-9a-z])*)*@([0-9a-z]+([a-z0-9_-]*[0-9a-z])*\.)+[a-z]{2,9}/i;
HTMLArea.RE_url      = /(https?:\/\/)?(([a-z0-9_]+:[a-z0-9_]+@)?[a-z0-9_-]{2,}(\.[a-z0-9_-]{2,})+\.[a-z]{2,5}(:[0-9]+)?(\/\S+)*)/i;

/*
 * Editor configuration object constructor
 */
HTMLArea.Config = function () {
	this.width = "auto";
	this.height = "auto";
		// enable creation of a status bar?
	this.statusBar = true;
		// maximum size of the undo queue
	this.undoSteps = 20;
		// the time interval at which undo samples are taken: 1/2 sec.
	this.undoTimeout = 500;
		// whether the toolbar should be included in the size or not.
	this.sizeIncludesToolbar = true;
		// if true then HTMLArea will retrieve the full HTML, starting with the <HTML> tag.
	this.fullPage = false;
		// if the site is secure, create a secure iframe
	this.useHTTPS = false;
		// for Mozilla
	this.useCSS = false;
	this.enableMozillaExtension = true;
	this.disableEnterParagraphs = false;
	this.disableObjectResizing = false;
	this.removeTrailingBR = false;
		// style included in the iframe document
	this.editedContentStyle = _editor_edited_content_CSS;
		// content style
	this.pageStyle = "";
		// remove tags (these have to be a regexp, or null if this functionality is not desired)
	this.htmlRemoveTags = null;
		// remove tags and any contents (these have to be a regexp, or null if this functionality is not desired)
	this.htmlRemoveTagsAndContents = null;
		// remove comments
	this.htmlRemoveComments = false;
		// custom tags (these have to be a regexp, or null if this functionality is not desired)
	this.customTags = null;
		// BaseURL included in the iframe document
	this.baseURL = document.baseURI || document.URL;
	if(this.baseURL && this.baseURL.match(/(.*)\/([^\/]+)/)) this.baseURL = RegExp.$1 + "/";
		// URL-s
	this.imgURL = "images/";
	this.popupURL = "popups/";

	this.btnList = {
		InsertHorizontalRule:	["Horizontal Rule", "ed_hr.gif",false, function(editor) {editor.execCommand("InsertHorizontalRule");}],
		HtmlMode:		["Toggle HTML Source", "ed_html.gif", true, function(editor) {editor.execCommand("HtmlMode");}],
		SelectAll:		["SelectAll", "", true, function(editor) {editor.execCommand("SelectAll");}, null, true, false],
		Undo:			["Undo the last action", "ed_undo.gif", false, function(editor) {editor.execCommand("Undo");}],
		Redo:			["Redo the last action", "ed_redo.gif", false, function(editor) {editor.execCommand("Redo");}],
		Cut:			["Cut selection", "ed_cut.gif", false, function(editor) {editor.execCommand("Cut");}],
		Copy:			["Copy selection", "ed_copy.gif", false, function(editor) {editor.execCommand("Copy");}],
		Paste:			["Paste from clipboard", "ed_paste.gif", false, function(editor) {editor.execCommand("Paste");}]
	};
		// Default hotkeys
	this.hotKeyList = {
		a:	{ cmd:	"SelectAll", 	action:	null},
		v:	{ cmd:	"Paste", 	action:	null},
		z:	{ cmd:	"Undo", 	action:	null},
		y:	{ cmd:	"Redo", 	action:	null}
	};

		// Initialize tooltips from the I18N module, generate correct image path
	for (var buttonId in this.btnList) {
		if (this.btnList.hasOwnProperty(buttonId)) {
			var btn = this.btnList[buttonId];
			if (typeof(HTMLArea.I18N.tooltips[buttonId.toLowerCase()]) !== "undefined") {
				btn[0] = HTMLArea.I18N.tooltips[buttonId.toLowerCase()];
			}
			if (typeof(btn[1]) === "string") {
				btn[1] = _editor_skin + this.imgURL + btn[1];
			} else {
				btn[1][0] = _editor_skin + this.imgURL + btn[1][0];
			}
		}
	}
	this.customSelects = {};
};

/*
 * Register a new button with the configuration.
 * It can be called with all arguments, or with only one (first one).  When called with
 * only one argument it must be an object with the following properties:
 * id, tooltip, image, textMode, action, context.  Examples:
 *
 * 1. config.registerButton("my-hilite", "Hilite text", "my-hilite.gif", false, function(editor) {...}, context);
 * 2. config.registerButton({
 *	id		: "my-hilite",		// Unique id for the button
 *	tooltip		: "Hilite text",	// the tooltip
 *	image		: "my-hilite.gif",	// image to be displayed in the toolbar
 *	textMode	: false,		// disabled in text mode
 *	action		: function(editor) {	// called when the button is clicked
 *				editor.surroundHTML('<span class="hilite">', '</span>');
 *				},
 *	context		: "p"			// will be disabled if not inside a <p> element
 *	hide		: false			// hide in menu and show only in context menu
 *	selection	: false			// will be disabled if there is no selection
 *	dialog		: true			// the button opens a dialog
 *    });
 */
HTMLArea.Config.prototype.registerButton = function(id,tooltip,image,textMode,action,context,hide,selection, dialog) {
	var buttonId;
	switch (typeof(id)) {
		case "string": buttonId = id; break;
		case "object": buttonId = id.id; break;
		default: HTMLArea._appendToLog("[HTMLArea.Config::registerButton]: invalid arguments");
			 return false;
	}
	if (typeof(this.customSelects[buttonId]) !== "undefined") {
		HTMLArea._appendToLog("[HTMLArea.Config::registerButton]: A dropdown with the same Id: " + buttonId + " already exists.");
		return false;
	}
	if (typeof(this.btnList[buttonId]) !== "undefined") {
		HTMLArea._appendToLog("[HTMLArea.Config::registerButton]: A button with the same Id: " + buttonId + " already exists and will be overidden.");
	}
	switch (typeof(id)) {
		case "string":
			if (typeof(hide) === "undefined") var hide = false;
			if (typeof(selection) === "undefined") var selection = false;
			if (typeof(dialog) === "undefined") var dialog = true;
			this.btnList[id] = [tooltip, image, textMode, action, context, hide, selection, dialog];
			break;
		case "object":
			if (typeof(id.hide) === "undefined") id.hide = false;
			if (typeof(id.selection) === "undefined") id.selection = false;
			if (typeof(id.dialog) === "undefined") id.dialog = true;
			this.btnList[id.id] = [id.tooltip, id.image, id.textMode, id.action, id.context, id.hide, id.selection, id.dialog];
			break;
	}
	return true;
};

/*
 * Register a dropdown box with the editor configuration.
 */
HTMLArea.Config.prototype.registerDropdown = function(dropDownConfiguration) {
	if (typeof(this.customSelects[dropDownConfiguration.id]) != "undefined") {
		HTMLArea._appendToLog("[HTMLArea.Config::registerDropdown]: A dropdown with the same ID " + dropDownConfiguration.id + " already exists and will be overidden.");
	}
	if (typeof(this.btnList[dropDownConfiguration.id]) != "undefined") {
		HTMLArea._appendToLog("ERROR [HTMLArea.Config::registerDropdown]: A button with the same ID " + dropDownConfiguration.id + " already exists.");
		return false;
	}
	this.customSelects[dropDownConfiguration.id] = dropDownConfiguration;
	return true;
};

/*
 * Register a hotkey with the editor configuration.
 */
HTMLArea.Config.prototype.registerHotKey = function(hotKeyConfiguration) {
	if (typeof(this.hotKeyList[hotKeyConfiguration.id]) != "undefined") {
		HTMLArea._appendToLog("[HTMLArea.Config::registerHotKey]: A hotkey with the same key " + hotKeyConfiguration.id + " already exists and will be overidden.");
	}
	this.hotKeyList[hotKeyConfiguration.id] = hotKeyConfiguration;
	return true;
};

/***************************************************
 *  EDITOR FRAMEWORK
 ***************************************************/
/*
 * Update the state of a toolbar element.
 * This function is member of a toolbar element object, unnamed object created by createButton or createSelect functions.
 */
HTMLArea.setButtonStatus = function(id,newval) {
	var oldval = this[id];
	var el = document.getElementById(this.elementId);
	if (oldval != newval) {
		switch (id) {
			case "enabled":
				if (newval) {
					if (!HTMLArea.is_wamcom) {
						HTMLArea._removeClass(el, "buttonDisabled");
						HTMLArea._removeClass(el.parentNode, "buttonDisabled");
					}
					el.disabled = false;
				} else {
					if (!HTMLArea.is_wamcom) {
						HTMLArea._addClass(el, "buttonDisabled");
						HTMLArea._addClass(el.parentNode, "buttonDisabled");
					}
					el.disabled = true;
				}
				break;
			    case "active":
				if (newval) {
					HTMLArea._addClass(el, "buttonPressed");
					HTMLArea._addClass(el.parentNode, "buttonPressed");
				} else {
					HTMLArea._removeClass(el, "buttonPressed");
					HTMLArea._removeClass(el.parentNode, "buttonPressed");
				}
				break;
		}
		this[id] = newval;
	}
};

/*
 * Create a new line in the toolbar
 */
HTMLArea.newLine = function(toolbar) {
	tb_line = document.createElement("ul");
	tb_line.className = "tb-line";
	toolbar.appendChild(tb_line);
	return tb_line;
};

/*
 * Add a toolbar element to the current line or group
 */
HTMLArea.addTbElement = function(element, tb_line, first_cell_on_line) {
	var tb_cell = document.createElement("li");
	if (first_cell_on_line) tb_cell.className = "tb-first-cell";
		else tb_cell.className = "tb-cell";
	HTMLArea._addClass(tb_cell, element.className);
	tb_line.appendChild(tb_cell);
	tb_cell.appendChild(element);
	if(element.style.display == "none") {
		tb_cell.style.display = "none";
		if(HTMLArea._hasClass(tb_cell.previousSibling, "separator")) tb_cell.previousSibling.style.display = "none";
	}
	return false;
};

/*
 * Create a new group on the current line
 */
HTMLArea.addTbGroup = function(tb_line, first_cell_on_line) {
	var tb_group = document.createElement("ul");
	tb_group.className = "tb-group";
	HTMLArea.addTbElement(tb_group, tb_line, first_cell_on_line);
	return tb_group;
};

/*
 * Create a combo box and add it to the toolbar
 */
HTMLArea.prototype.createSelect = function(txt,tb_line,first_cell_on_line,labelObj) {
	var options = null,
		cmd = null,
		context = null,
		tooltip = "",
		newObj = {
			created : false,
			el : null,
			first : first_cell_on_line,
			labelUsed : false
		};

	cmd = txt;
	var dropdown = this.config.customSelects[cmd];
	if (typeof(dropdown) != "undefined") {
		options = dropdown.options;
		context = dropdown.context;
		if (typeof(dropdown.tooltip) != "undefined") tooltip = dropdown.tooltip;
	}
	if (options) {
		newObj["el"] = document.createElement("select");
		newObj["el"].className = "select";
		newObj["el"].title = tooltip;
		newObj["el"].id = this._editorNumber + "-" + txt;
		newObj["first"] = HTMLArea.addTbElement(newObj["el"], tb_line, first_cell_on_line);
		var obj = {
			name 		: txt,				// field name
			elementId 	: newObj["el"].id,		// unique id for the UI element
			enabled 	: true,				// is it enabled?
			text 		: false,			// enabled in text mode?
			cmd 		: cmd,				// command ID
			state		: HTMLArea.setButtonStatus,	// for changing state
			context 	: context,
			editorNumber	: this._editorNumber
		};
		this._toolbarObjects[txt] = obj;
		newObj["el"]._obj = obj;
		if (labelObj["labelRef"]) {
			labelObj["el"].htmlFor = newObj["el"].id;
			newObj["labelUsed"] = true;
		}
		HTMLArea._addEvent(newObj["el"], "change", HTMLArea.toolBarButtonHandler);

		for (var i in options) {
			if (options.hasOwnProperty(i)) {
				var op = document.createElement("option");
				op.innerHTML = i;
				op.value = options[i];
				newObj["el"].appendChild(op);
			}
		}

		newObj["created"] = true;
	}

	return newObj;
};

/*
 * Create a button and add it to the toolbar
 */
HTMLArea.prototype.createButton = function (txt,tb_line,first_cell_on_line,labelObj) {
	var btn = null,
		newObj = {
			created : false,
			el : null,
			first : first_cell_on_line,
			labelUsed : false
		};

	switch (txt) {
		case "separator":
			newObj["el"] = document.createElement("div");
			newObj["el"].className = "separator";
			newObj["first"] = HTMLArea.addTbElement(newObj["el"], tb_line, first_cell_on_line);
			newObj["created"] = true;
			break;
		case "space":
			newObj["el"] = document.createElement("div");
			newObj["el"].className = "space";
			newObj["el"].innerHTML = "&nbsp;";
			newObj["first"] = HTMLArea.addTbElement(newObj["el"], tb_line, first_cell_on_line);
			newObj["created"] = true;
			break;
		case "TextIndicator":
			newObj["el"] = document.createElement("div");
			newObj["el"].appendChild(document.createTextNode("A"));
			newObj["el"].className = "indicator";
			newObj["el"].title = HTMLArea.I18N.tooltips.textindicator;
			newObj["el"].id = this._editorNumber + "-" + txt;
			newObj["first"] = HTMLArea.addTbElement(newObj["el"], tb_line, first_cell_on_line);
			var obj = {
				name		: txt,
				elementId	: newObj["el"].id,
				enabled		: true,
				active		: false,
				text		: false,
				cmd		: "TextIndicator",
				state		: HTMLArea.setButtonStatus
			};
			this._toolbarObjects[txt] = obj;
			newObj["created"] = true;
			break;
		default:
			btn = this.config.btnList[txt];
	}
	if(!newObj["created"] && btn) {
		newObj["el"] = document.createElement("button");
		newObj["el"].title = btn[0];
		newObj["el"].className = "button";
		newObj["el"].id = this._editorNumber + "-" + txt;
		if (btn[5]) newObj["el"].style.display = "none";
		newObj["first"] = HTMLArea.addTbElement(newObj["el"], tb_line, first_cell_on_line);
		var obj = {
			name 		: txt, 				// the button name
			elementId	: newObj["el"].id, 		// unique id for the UI element
			enabled 	: true,				// is it enabled?
			active		: false,			// is it pressed?
			text 		: btn[2],			// enabled in text mode?
			cmd 		: btn[3],			// the function to be invoked
			state		: HTMLArea.setButtonStatus,	// for changing state
			context 	: btn[4] || null,		// enabled in a certain context?
			selection	: btn[6],			// disabled when no selection?
			editorNumber	: this._editorNumber
		};
		this._toolbarObjects[txt] = obj;
		newObj["el"]._obj = obj;
		if (labelObj["labelRef"]) {
			labelObj["el"].htmlFor = newObj["el"].id;
			newObj["labelUsed"] = true;
		}
		HTMLArea._addEvents(newObj["el"],["mouseover", "mouseout", "mousedown", "click"], HTMLArea.toolBarButtonHandler);
		newObj["el"].className += " " + txt;
		newObj["created"] = true;
	}
	return newObj;
};

/*
 * Create a label and add it to the toolbar
 */
HTMLArea.createLabel = function(txt,tb_line,first_cell_on_line) {
	var newObj = {
		created : false,
		el : null,
		labelRef : false,
		first : first_cell_on_line
	};
	if (/^([IT])\[(.*?)\]/.test(txt)) {
		var l7ed = RegExp.$1 == "I"; // localized?
		var label = RegExp.$2;
		if (l7ed) label = HTMLArea.I18N.dialogs[label];
		newObj["el"] = document.createElement("label");
		newObj["el"].className = "label";
		newObj["el"].innerHTML = label;
		newObj["labelRef"] = true;
		newObj["created"] = true;
		newObj["first"] = HTMLArea.addTbElement(newObj["el"], tb_line, first_cell_on_line);
	}
	return newObj;
};

/*
 * Create the toolbar and append it to the _htmlarea.
 */
HTMLArea.prototype._createToolbar = function () {
	var j, k, code, n = this.config.toolbar.length, m,
		tb_line = null, tb_group = null,
		first_cell_on_line = true,
		labelObj = new Object(),
		tbObj = new Object();

	var toolbar = document.createElement("div");
	this._toolbar = toolbar;
	toolbar.className = "toolbar";
	toolbar.unselectable = "1";
	this._toolbarObjects = new Object();

	for (j = 0; j < n; ++j) {
		tb_line = HTMLArea.newLine(toolbar);
		if(!this.config.keepButtonGroupTogether) HTMLArea._addClass(tb_line, "free-float");
		first_cell_on_line = true;
		tb_group = null;
		var group = this.config.toolbar[j];
		m = group.length;
		for (k = 0; k < m; ++k) {
			code = group[k];
			if (code == "linebreak") {
				tb_line = HTMLArea.newLine(toolbar);
				if(!this.config.keepButtonGroupTogether) HTMLArea._addClass(tb_line, "free-float");
				first_cell_on_line = true;
				tb_group = null;
			} else {
				if ((code == "separator" || first_cell_on_line) && this.config.keepButtonGroupTogether) {
					tb_group = HTMLArea.addTbGroup(tb_line, first_cell_on_line);
					first_cell_on_line = false;
				}
				created = false;
				if (/^([IT])\[(.*?)\]/.test(code)) {
					labelObj = HTMLArea.createLabel(code, (tb_group?tb_group:tb_line), first_cell_on_line);
					created = labelObj["created"] ;
					first_cell_on_line = labelObj["first"];
				}
				if (!created) {
					tbObj = this.createButton(code, (tb_group?tb_group:tb_line), first_cell_on_line, labelObj);
					created = tbObj["created"];
					first_cell_on_line = tbObj["first"];
					if(tbObj["labelUsed"]) labelObj["labelRef"] = false;
				}
				if (!created) {
					tbObj = this.createSelect(code, (tb_group?tb_group:tb_line), first_cell_on_line, labelObj);
					created = tbObj["created"];
					first_cell_on_line = tbObj["first"];
					if(tbObj["labelUsed"]) labelObj["labelRef"] = false;
				}
				if (!created) HTMLArea._appendToLog("ERROR [HTMLArea::createToolbar]: Unknown toolbar item: " + code);
			}
		}
	}

	tb_line = HTMLArea.newLine(toolbar);
	this._htmlArea.appendChild(toolbar);
};

/*
 * Handle toolbar element events handler
 */
HTMLArea.toolBarButtonHandler = function(ev) {
	if(!ev) var ev = window.event;
	var target = (ev.target) ? ev.target : ev.srcElement;
	while (target.tagName.toLowerCase() == "img" || target.tagName.toLowerCase() == "div") target = target.parentNode;
	var obj = target._obj;
	var editorNumber = obj["editorNumber"];
	var editor = RTEarea[editorNumber]["editor"];
	if (obj.enabled) {
		switch (ev.type) {
			case "mouseover":
				HTMLArea._addClass(target, "buttonHover");
				HTMLArea._addClass(target.parentNode, "buttonHover");
				break;
			case "mouseout":
				HTMLArea._removeClass(target, "buttonHover");
				HTMLArea._removeClass(target.parentNode, "buttonHover");
				HTMLArea._removeClass(target, "buttonActive");
				HTMLArea._removeClass(target.parentNode, "buttonActive");
				if (obj.active) {
					HTMLArea._addClass(target, "buttonPressed");
					HTMLArea._addClass(target.parentNode, "buttonPressed");
				}
				break;
			case "mousedown":
				HTMLArea._addClass(target, "buttonActive");
				HTMLArea._addClass(target.parentNode, "buttonActive");
				HTMLArea._removeClass(target, "buttonPressed");
				HTMLArea._removeClass(target.parentNode, "buttonPressed");
				HTMLArea._stopEvent(ev);
				break;
			case "click":
				HTMLArea._removeClass(target, "buttonActive");
				HTMLArea._removeClass(target.parentNode, "buttonActive");
				HTMLArea._removeClass(target, "buttonHover");
				HTMLArea._removeClass(target.parentNode, "buttonHover");
				obj.cmd(editor, obj.name);
				HTMLArea._stopEvent(ev);
				if (HTMLArea.is_opera) {
					editor._iframe.focus();
				}
				if (!editor.config.btnList[obj.name][7]) {
					editor.updateToolbar();
				}
				break;
			case "change":
				editor.focusEditor();
				var dropdown = editor.config.customSelects[obj.name];
				if (typeof(dropdown) !== "undefined") {
					dropdown.action(editor, obj.name);
					HTMLArea._stopEvent(ev);
					if (HTMLArea.is_opera) {
						editor._iframe.focus();
					}
					editor.updateToolbar();
				} else {
					HTMLArea._appendToLog("ERROR [HTMLArea::toolBarButtonHandler]: Combo box " + obj.name + " not registered.");
				}
		}
	}
};

/*
 * Create the status bar
 */
HTMLArea.prototype._createStatusBar = function() {
	var statusBar = document.createElement("div");
	this._statusBar = statusBar;
	statusBar.className = "statusBar";
	if (!this.config.statusBar) statusBar.style.display = "none";
	var statusBarTree = document.createElement("span");
	this._statusBarTree = statusBarTree;
	statusBarTree.className = "statusBarTree";
	statusBar.appendChild(statusBarTree);
	statusBarTree.appendChild(document.createTextNode(HTMLArea.I18N.msg["Path"] + ": "));
	this._htmlArea.appendChild(statusBar);
};

/*
 * Create the htmlArea iframe and replace the textarea with it.
 */
HTMLArea.prototype.generate = function () {

		// get the textarea and hide it
	var textarea = this._textArea;
	if (typeof(textarea) == "string") {
		textarea = HTMLArea.getElementById("textarea", textarea);
		this._textArea = textarea;
	}
	textarea.style.display = "none";

		// create the editor framework and insert the editor before the textarea
	var htmlarea = document.createElement("div");
	htmlarea.className = "htmlarea";
	htmlarea.style.width = textarea.style.width;
	this._htmlArea = htmlarea;
	textarea.parentNode.insertBefore(htmlarea, textarea);

	if(textarea.form) {
			// we have a form, on reset, re-initialize the HTMLArea content and update the toolbar
		var f = textarea.form;
		if (typeof(f.onreset) == "function") {
			var funcref = f.onreset;
			if (typeof(f.__msh_prevOnReset) == "undefined") f.__msh_prevOnReset = [];
			f.__msh_prevOnReset.push(funcref);
		}
		f._editorNumber = this._editorNumber;
		HTMLArea._addEvent(f, "reset", HTMLArea.resetHandler);
	}

		// create & append the toolbar
	this._createToolbar();
	HTMLArea._appendToLog("[HTMLArea::generate]: Toolbar successfully created.");

		// create and append the IFRAME
	var iframe = document.createElement("iframe");
	if (HTMLArea.is_ie || HTMLArea.is_safari || HTMLArea.is_wamcom) {
		iframe.setAttribute("src",_editor_url + "popups/blank.html");
	} else if (HTMLArea.is_opera) {
		iframe.setAttribute("src",_typo3_host_url + _editor_url + "popups/blank.html");
	} else {
		iframe.setAttribute("src","javascript:void(0);");
	}
	iframe.className = "editorIframe";
	if (!this.config.statusBar) iframe.className += " noStatusBar";
	htmlarea.appendChild(iframe);
	this._iframe = iframe;

		// create & append the status bar
	this._createStatusBar();

		// size the iframe
	this.sizeIframe(2);

	HTMLArea._appendToLog("[HTMLArea::generate]: Editor iframe successfully created.");
	this.initIframe();
	return this;
};

/*
 * Size the iframe according to user's prefs or initial textarea
 */
HTMLArea.prototype.sizeIframe = function(diff) {
	var height = (this.config.height == "auto" ? (this._textArea.style.height) : this.config.height);
	var textareaHeight = height;
		// All nested tabs and inline levels in the sorting order they were applied:
	this.nested = {};
	this.nested.all = RTEarea[this._editorNumber].tceformsNested;
	this.nested.sorted = HTMLArea.simplifyNested(this.nested.all);
		// Clone the array instead of using a reference (this.accessParentElements will change the array):
	var parentElements = (this.nested.sorted && this.nested.sorted.length ? [].concat(this.nested.sorted) : []);
		// Walk through all nested tabs and inline levels to make a correct positioning:
	var dimensions = this.accessParentElements(parentElements, 'this.getDimensions()');

	if(height.indexOf("%") == -1) {
		height = parseInt(height) - diff;
		if (this.config.sizeIncludesToolbar) {
			this._initialToolbarOffsetHeight = dimensions.toolbar.height;
			height -= dimensions.toolbar.height;
			height -= dimensions.statusbar.height;
		}
		if (height < 0) height = 0;
		textareaHeight = (height - 4);
		if (textareaHeight < 0) textareaHeight = 0;
		height += "px";
		textareaHeight += "px";
	}
	this._iframe.style.height = height;
	this._textArea.style.height = textareaHeight;
	var textareaWidth = (this.config.width == "auto" ? this._textArea.style.width : this.config.width);
	var iframeWidth = textareaWidth;
	if(textareaWidth.indexOf("%") == -1) {
		iframeWidth = parseInt(textareaWidth) + "px";
		textareaWidth = parseInt(textareaWidth) - diff;
		if (textareaWidth < 0) textareaWidth = 0;
		textareaWidth += 'px';
	}
	this._iframe.style.width = "100%";
	if (HTMLArea.is_opera) this._iframe.style.width = iframeWidth;
	this._textArea.style.width = textareaWidth;
};

/**
 * Get the dimensions of the toolbar and statusbar.
 *
 * @return	object		An object with width/height pairs for statusbar and toolbar.
 * @author	Oliver Hader <oh@inpublica.de>
 */
HTMLArea.prototype.getDimensions = function() {
	return {
		toolbar: {width: this._toolbar.offsetWidth, height: this._toolbar.offsetHeight},
		statusbar: {width: this._statusBar.offsetWidth, height: this._statusBar.offsetHeight}
	};
};

/**
 * Access an inline relational element or tab menu and make it "accesible".
 * If a parent object has the style "display: none", offsetWidth & offsetHeight are '0'.
 *
 * @params	object		callbackFunc: A function to be called, when the embedded objects are "accessible".
 * @return	object		An object returned by the callbackFunc.
 * @author	Oliver Hader <oh@inpublica.de>
 */
HTMLArea.prototype.accessParentElements = function(parentElements, callbackFunc) {
	var result = {};

	if (parentElements.length) {
		var currentElement = parentElements.pop();
		var elementStyle = document.getElementById(currentElement).style;
		var actionRequired = (elementStyle.display == 'none' ? true : false);

		if (actionRequired) {
			var originalVisibility = elementStyle.visibility;
			var originalPosition = elementStyle.position;
			elementStyle.visibility = 'hidden';
			elementStyle.position = 'absolute';
			elementStyle.display = '';
		}

		result = this.accessParentElements(parentElements, callbackFunc);

		if (actionRequired) {
			elementStyle.display = 'none';
			elementStyle.position = originalPosition;
			elementStyle.visibility = originalVisibility;
		}

	} else {
		result = eval(callbackFunc);

	}

	return result;
};

/**
 * Simplify the array of nested levels. Create an indexed array with the correct names of the elements.
 *
 * @param	object		nested: The array with the nested levels
 * @return	object		The simplified array
 * @author	Oliver Hader <oh@inpublica.de>
 */
HTMLArea.simplifyNested = function(nested) {
	var i, type, level, max, simplifiedNested=[];
	if (nested && nested.length) {
		if (nested[0][0]=='inline') {
			nested = inline.findContinuedNestedLevel(nested, nested[0][1]);
		}
		for (i=0, max=nested.length; i<max; i++) {
			type = nested[i][0];
			level = nested[i][1];
			if (type=='tab') {
				simplifiedNested.push(level+'-DIV');
			} else if (type=='inline') {
				simplifiedNested.push(level+'_fields');
			}
		}
	}
	return simplifiedNested;
};

/*
 * Initialize the iframe
 */
HTMLArea.initIframe = function(editorNumber) {
	var editor = RTEarea[editorNumber]["editor"];
	editor.initIframe();
};

HTMLArea.prototype.initIframe = function() {
	if (this._initIframeTimer) window.clearTimeout(this._initIframeTimer);
	if (!this._iframe || (!this._iframe.contentWindow && !this._iframe.contentDocument)) {
		this._initIframeTimer = window.setTimeout("HTMLArea.initIframe(\'" + this._editorNumber + "\');", 50);
		return false;
	} else if (this._iframe.contentWindow && !HTMLArea.is_safari) {
		if (!this._iframe.contentWindow.document || !this._iframe.contentWindow.document.documentElement) {
			this._initIframeTimer = window.setTimeout("HTMLArea.initIframe(\'" + this._editorNumber + "\');", 50);
			return false;
		}
	} else if (!this._iframe.contentDocument.documentElement || !this._iframe.contentDocument.body) {
		this._initIframeTimer = window.setTimeout("HTMLArea.initIframe(\'" + this._editorNumber + "\');", 50);
		return false;
	}
	var doc = this._iframe.contentWindow ? this._iframe.contentWindow.document : this._iframe.contentDocument;
	this._doc = doc;

	if (!this.config.fullPage) {
		var head = doc.getElementsByTagName("head")[0];
		if (!head) {
			head = doc.createElement("head");
			doc.documentElement.appendChild(head);
		}
		if (this.config.baseURL && !HTMLArea.is_opera) {
			var base = doc.getElementsByTagName("base")[0];
			if (!base) {
				base = doc.createElement("base");
				base.href = this.config.baseURL;
				head.appendChild(base);
			}
			HTMLArea._appendToLog("[HTMLArea::initIframe]: Iframe baseURL set to: " + this.config.baseURL);
		}
		var link0 = doc.getElementsByTagName("link")[0];
		if (!link0) {
 			link0 = doc.createElement("link");
			link0.rel = "stylesheet";
			link0.href = this.config.editedContentStyle;
			head.appendChild(link0);
			HTMLArea._appendToLog("[HTMLArea::initIframe]: Skin CSS set to: " + this.config.editedContentStyle);
		}
		if (this.config.defaultPageStyle) {
			var link = doc.getElementsByTagName("link")[1];
			if (!link) {
 				link = doc.createElement("link");
				link.rel = "stylesheet";
				link.href = this.config.defaultPageStyle;
				head.appendChild(link);
			}
			HTMLArea._appendToLog("[HTMLArea::initIframe]: Override CSS set to: " + this.config.defaultPageStyle);
		}
		if (this.config.pageStyle) {
			var link = doc.getElementsByTagName("link")[2];
			if (!link) {
 				link = doc.createElement("link");
				link.rel = "stylesheet";
				link.href = this.config.pageStyle;
				head.appendChild(link);
			}
			HTMLArea._appendToLog("[HTMLArea::initIframe]: Content CSS set to: " + this.config.pageStyle);
		}
	} else {
		var html = this._textArea.value;
		this.setFullHTML(html);
	}
	HTMLArea._appendToLog("[HTMLArea::initIframe]: Editor iframe head successfully initialized.");

	this.stylesLoaded();
};

/*
 * Finalize editor Iframe initialization after loading the style sheets
 */
HTMLArea.stylesLoaded = function(editorNumber) {
	var editor = RTEarea[editorNumber]["editor"];
	editor.stylesLoaded();
};

HTMLArea.prototype.stylesLoaded = function() {
	var doc = this._doc;
	var docWellFormed = true;

		// check if the stylesheets have been loaded

	if (this._stylesLoadedTimer) window.clearTimeout(this._stylesLoadedTimer);
	var stylesAreLoaded = true;
	var errorText = '';
	var rules;
	for (var rule = 0; rule < doc.styleSheets.length; rule++) {
		if (HTMLArea.is_gecko) try { rules = doc.styleSheets[rule].cssRules; } catch(e) { stylesAreLoaded = false; errorText = e; }
		if (HTMLArea.is_ie) try { rules = doc.styleSheets[rule].rules; } catch(e) { stylesAreLoaded = false; errorText = e; }
		if (HTMLArea.is_ie) try { rules = doc.styleSheets[rule].imports; } catch(e) { stylesAreLoaded = false; errorText = e; }
	}
	if (!stylesAreLoaded && !HTMLArea.is_wamcom) {
		HTMLArea._appendToLog("[HTMLArea::initIframe]: Failed attempt at loading stylesheets: " + errorText + " Retrying...");
		this._stylesLoadedTimer = window.setTimeout("HTMLArea.stylesLoaded(\'" + this._editorNumber + "\');", 100);
		return false;
	}
	HTMLArea._appendToLog("[HTMLArea::initIframe]: Stylesheets successfully loaded.");

	if (!this.config.fullPage) {
		doc.body.style.borderWidth = "0px";
		doc.body.className = "htmlarea-content-body";
		try {
			doc.body.innerHTML = this._textArea.value;
		} catch(e) {
			HTMLArea._appendToLog("[HTMLArea::initIframe]: The HTML document is not well-formed.");
			alert(HTMLArea.I18N.msg["HTML-document-not-well-formed"]);
			docWellFormed = false;
		}
	}

		// Set contents editable
	if (docWellFormed) {
		if (HTMLArea.is_gecko && !HTMLArea.is_safari && !HTMLArea.is_opera && !this._initEditMode()) {
			return false;
		}
		if (HTMLArea.is_ie || HTMLArea.is_safari) {
			doc.body.contentEditable = true;
		}
		if (HTMLArea.is_opera || HTMLArea.is_safari) {
			doc.designMode = "on";
			if (this._doc.queryCommandEnabled("insertbronreturn")) this._doc.execCommand("insertbronreturn", false, this.config.disableEnterParagraphs);
			if (this._doc.queryCommandEnabled("styleWithCSS")) this._doc.execCommand("styleWithCSS", false, this.config.useCSS);
		}
		if (HTMLArea.is_ie) doc.selection.empty();
		this._editMode = "wysiwyg";
		if (doc.body.contentEditable || doc.designMode == "on") HTMLArea._appendToLog("[HTMLArea::initIframe]: Design mode successfully set.");
	} else {
		this._editMode = "textmode";
		this.setMode("docnotwellformedmode");
		HTMLArea._appendToLog("[HTMLArea::initIframe]: Design mode could not be set.");
	}

		// set editor number in iframe and document for retrieval in event handlers
	doc._editorNo = this._editorNumber;
	if (HTMLArea.is_ie) doc.documentElement._editorNo = this._editorNumber;

		// Start undo snapshots
	if (this._customUndo) this._timerUndo = window.setInterval("HTMLArea.undoTakeSnapshot(\'" + this._editorNumber + "\');", this.config.undoTimeout);

		// intercept events for updating the toolbar & for keyboard handlers
	HTMLArea._addEvents((HTMLArea.is_ie ? doc.body : doc), ["keydown","keypress","mousedown","mouseup","drag"], HTMLArea._editorEvent, true);

		// add unload handler
	if (!HTMLArea.hasUnloadHandler) {
		HTMLArea.hasUnloadHandler = true;
		HTMLArea._addEvent((this._iframe.contentWindow ? this._iframe.contentWindow : this._iframe.contentDocument), "unload", HTMLArea.removeEditorEvents);
	}

	window.setTimeout("HTMLArea.generatePlugins(\'" + this._editorNumber + "\');", 100);
};

HTMLArea.generatePlugins = function(editorNumber) {
	var editor = RTEarea[editorNumber]["editor"];
		// check if any plugins have registered generate handlers
		// check also if any plugin has a onKeyPress handler
	editor._hasPluginWithOnKeyPressHandler = false;
	for (var pluginId in editor.plugins) {
		if (editor.plugins.hasOwnProperty(pluginId)) {
			var pluginInstance = editor.plugins[pluginId].instance;
			if (typeof(pluginInstance.onGenerate) === "function") {
				pluginInstance.onGenerate();
			}
			if (typeof(pluginInstance.onGenerateOnce) === "function") {
				pluginInstance.onGenerateOnce();
				pluginInstance.onGenerateOnce = null;
			}
			if (typeof(pluginInstance.onKeyPress) === "function") {
				editor._hasPluginWithOnKeyPressHandler = true;
			}
		}
	}
	if (typeof(editor.onGenerate) === "function") {
		editor.onGenerate();
		editor.onGenerate = null;
	}
	HTMLArea._appendToLog("[HTMLArea::initIframe]: All plugins successfully generated.");
	editor.focusEditor();
	editor.updateToolbar();
};

/*
 * When we have a form, on reset, re-initialize the HTMLArea content and update the toolbar
 */
HTMLArea.resetHandler = function(ev) {
	if(!ev) var ev = window.event;
	var form = (ev.target) ? ev.target : ev.srcElement;
	var editor = RTEarea[form._editorNumber]["editor"];
	editor.setHTML(editor._textArea.value);
	editor.updateToolbar();
	var a = form.__msh_prevOnReset;
		// call previous reset methods if they were there.
	if (typeof(a) != "undefined") {
		for (var i=a.length; --i >= 0; ) { a[i](); }
	}
};

/*
 * Clean up event handlers and object references, undo/redo snapshots, update the textarea for submission
 */
HTMLArea.removeEditorEvents = function(ev) {
	if (!ev) var ev = window.event;
	HTMLArea._stopEvent(ev);
	if (HTMLArea._eventCache) {
		HTMLArea._eventCache.flush();
	}
	for (var editorNumber in RTEarea) {
		if (RTEarea.hasOwnProperty(editorNumber)) {
			var editor = RTEarea[editorNumber].editor;
			if (editor) {
				RTEarea[editorNumber].editor = null;
					// save the HTML content into the original textarea for submit, back/forward, etc.
				editor._textArea.value = editor.getHTML();
					// release undo/redo snapshots
				window.clearInterval(editor._timerUndo);
				editor._undoQueue = null;
					// do final cleanup
				HTMLArea.cleanup(editor);
			}
		}
	}
};

/*
 * Clean up a bunch of references in order to avoid memory leakages mainly in IE, but also in Firefox and Opera
 */
HTMLArea.cleanup = function (editor) {
		// nullify envent handlers
	for (var handler in editor.eventHandlers) {
		if (editor.eventHandlers.hasOwnProperty(handler)) {
			editor.eventHandlers[handler] = null;
		}
	}
	for (var button in editor.btnList) {
		if (editor.btnList.hasOwnProperty(button)) {
			editor.btnList[button][3] = null;
		}
	}
	for (var dropdown in editor.config.customSelects) {
		if (editor.config.customSelects.hasOwnProperty(dropdown)) {
			editor.config.customSelects[dropdown].action = null;
			editor.config.customSelects[dropdown].refresh = null;
		}
	}
	for (var hotKey in editor.config.hotKeyList) {
		if (editor.config.customSelects.hasOwnProperty(hotKey)) {
			editor.config.hotKeyList[hotKey].action = null;
		}
	}
	editor.onGenerate = null;
	HTMLArea._editorEvent = null;
	if(editor._textArea.form) {
		editor._textArea.form.__msh_prevOnReset = null;
		editor._textArea.form._editorNumber = null;
	}
	HTMLArea.onload = null;

		// cleaning plugin handlers
	for (var plugin in editor.plugins) {
		if (editor.plugins.hasOwnProperty(plugin)) {
			var pluginInstance = editor.plugins[plugin].instance;
			pluginInstance.onChange = null;
			pluginInstance.onButtonPress = null;
			pluginInstance.onGenerate = null;
			pluginInstance.onGenerateOnce = null;
			pluginInstance.onMode = null;
			pluginInstance.onHotKey = null;
			pluginInstance.onKeyPress = null;
			pluginInstance.onSelect = null;
			pluginInstance.onUpdateTolbar = null;
		}
	}
		// cleaning the toolbar elements
	for (var txt in editor._toolbarObjects) {
		if (editor._toolbarObjects.hasOwnProperty(txt)) {
			var obj = editor._toolbarObjects[txt];
			obj.state = null;
			obj.cmd = null;
			var element = document.getElementById(obj.elementId);
			if (element) {
				element._obj = null;
			}
			editor._toolbarObjects[txt] = null;
		}
	}

		// cleaning the statusbar elements
	if (editor._statusBarTree.hasChildNodes()) {
		for (var i = editor._statusBarTree.firstChild; i; i = i.nextSibling) {
			if (i.nodeName.toLowerCase() == "a") {
				HTMLArea._removeEvents(i, ["click", "contextmenu"], HTMLArea.statusBarHandler);
				i.el = null;
				i.editor = null;
			}
		}
	}
		// final cleanup
	editor._toolbar = null;
	editor._statusBar = null;
	editor._statusBarTree =  null;
	editor._htmlArea = null;
	editor._iframe = null;
};

/*
 * Switch editor mode; parameter can be "textmode" or "wysiwyg".
 *  If no parameter was passed, toggle between modes.
 */
HTMLArea.prototype.setMode = function(mode) {
	if (typeof(mode) == "undefined") var mode = (this._editMode == "textmode") ? "wysiwyg" : "textmode";
	switch (mode) {
		case "textmode":
		case "docnotwellformedmode":
			this._textArea.value = this.getHTML();
			this._iframe.style.display = "none";
			this._textArea.style.display = "block";
			if(this.config.statusBar) {
				var statusBarTextMode = document.createElement("span");
				statusBarTextMode.className = "statusBarTextMode";
				statusBarTextMode.appendChild(document.createTextNode(HTMLArea.I18N.msg["TEXT_MODE"]));
				this._statusBar.innerHTML = '';
				this._statusBar.appendChild(statusBarTextMode);
			}
			this._editMode = "textmode";
			break;
		case "wysiwyg":
			if(HTMLArea.is_gecko && !HTMLArea.is_safari && !HTMLArea.is_opera) this._doc.designMode = "off";
			try {
				if(!this.config.fullPage) this._doc.body.innerHTML = this.getHTML();
					else this.setFullHTML(this.getHTML());
			} catch(e) {
				alert(HTMLArea.I18N.msg["HTML-document-not-well-formed"]);
				break;
			}
			this._textArea.style.display = "none";
			this._iframe.style.display = "block";
			if (HTMLArea.is_gecko && !HTMLArea.is_safari && !HTMLArea.is_opera) this._doc.designMode = "on";
			if(this.config.statusBar) {
				this._statusBar.innerHTML = "";
				this._statusBar.appendChild(this._statusBarTree);
			}
			this._editMode = "wysiwyg";
				//set gecko options (if we can... raises exception in Firefox 3)
			if (HTMLArea.is_gecko) {
				try {
					if (this._doc.queryCommandEnabled("insertbronreturn")) this._doc.execCommand("insertbronreturn", false, this.config.disableEnterParagraphs);
					if (this._doc.queryCommandEnabled("enableObjectResizing")) this._doc.execCommand("enableObjectResizing", false, !this.config.disableObjectResizing);
					if (this._doc.queryCommandEnabled("enableInlineTableEditing")) this._doc.execCommand("enableInlineTableEditing", false, (this.config.buttons.table && this.config.buttons.table.enableHandles) ? true : false);
					if (this._doc.queryCommandEnabled("styleWithCSS")) this._doc.execCommand("styleWithCSS", false, this.config.useCSS);
						else if (this._doc.queryCommandEnabled("useCSS")) this._doc.execCommand("useCSS", false, !this.config.useCSS);
				} catch(e) {}
			}
			break;
		default:
			return false;
	}
	if (mode !== "docnotwellformedmode") this.focusEditor();
	for (var pluginId in this.plugins) {
		if (this.plugins.hasOwnProperty(pluginId)) {
			var pluginInstance = this.plugins[pluginId].instance;
			if (typeof(pluginInstance.onMode) === "function") {
				pluginInstance.onMode(mode);
			}
		}
	}
};

/*
 * Get editor mode
 */
HTMLArea.prototype.getMode = function() {
	return this._editMode;
};

/*
 * Initialize iframe content when in full page mode
 */
HTMLArea.prototype.setFullHTML = function(html) {
	var save_multiline = RegExp.multiline;
	RegExp.multiline = true;
	if(html.match(HTMLArea.RE_doctype)) {
		this.setDoctype(RegExp.$1);
		html = html.replace(HTMLArea.RE_doctype, "");
	};
	RegExp.multiline = save_multiline;
	if(!HTMLArea.is_ie) {
		if(html.match(HTMLArea.RE_head)) this._doc.getElementsByTagName("head")[0].innerHTML = RegExp.$1;
		if(html.match(HTMLArea.RE_body)) this._doc.getElementsByTagName("body")[0].innerHTML = RegExp.$1;
	} else {
		var html_re = /<html>((.|\n)*?)<\/html>/i;
		html = html.replace(html_re, "$1");
		this._doc.open();
		this._doc.write(html);
		this._doc.close();
		this._doc.body.contentEditable = true;
		return true;
	};
};

/***************************************************
 *  PLUGINS, STYLESHEETS, AND IMAGE AND POPUP URL'S
 ***************************************************/

/*
 * Instantiate the specified plugin and register it with the editor
 *
 * @param	string		plugin: the name of the plugin
 *
 * @return	boolean		true if the plugin was successfully registered
 */
HTMLArea.prototype.registerPlugin = function(plugin) {
	var pluginName = plugin;
	if (typeof(plugin) === "string") {
		try {
			var plugin = eval(plugin);
		} catch(e) {
			HTMLArea._appendToLog("ERROR [HTMLArea::registerPlugin]: Cannot register invalid plugin: " + e);
			return false;
		}
	}
	if (typeof(plugin) !== "function") {
		HTMLArea._appendToLog("ERROR [HTMLArea::registerPlugin]: Cannot register undefined plugin.");
		return false;
	}
	var pluginInstance = new plugin(this, pluginName);
	if (pluginInstance) {
		var pluginInformation = plugin._pluginInfo;
		if(!pluginInformation) {
			pluginInformation = pluginInstance.getPluginInformation();
		}
		pluginInformation.instance = pluginInstance;
		this.plugins[pluginName] = pluginInformation;
		HTMLArea._appendToLog("[HTMLArea::registerPlugin]: Plugin " + pluginName + " was successfully registered.");
		return true;
	} else {
		HTMLArea._appendToLog("ERROR [HTMLArea::registerPlugin]: Can't register plugin " + pluginName + ".");
		return false;
	}
};

/*
 * Load the required plugin script and, unless not requested, the language file
 */
HTMLArea.loadPlugin = function(pluginName,noLangFile,url) {
	if (typeof(url) == "undefined") {
		var dir = _editor_url + "plugins/" + pluginName;
		var plugin = pluginName.replace(/([a-z])([A-Z])([a-z])/g, "$1" + "-" + "$2" + "$3").toLowerCase() + ".js";
		var plugin_file = dir + "/" + plugin;
		HTMLArea.loadScript(plugin_file);
		if (typeof(noLangFile) == "undefined" || !noLangFile) {
			var plugin_lang = dir + "/lang/" + _editor_lang + ".js";
			HTMLArea._scripts.push(plugin_lang);
		}
	} else {
		HTMLArea.loadScript(url);
	}
};

/*
 * Load a stylesheet file
 */
HTMLArea.loadStyle = function(style, plugin, url) {
	if (typeof(url) == "undefined") {
		var url = _editor_url || '';
		if (typeof(plugin) != "undefined") { url += "plugins/" + plugin + "/"; }
		url += style;
		if (/^\//.test(style)) { url = style; }
	}
	var head = document.getElementsByTagName("head")[0];
	var link = document.createElement("link");
	link.rel = "stylesheet";
	link.href = url;
	head.appendChild(link);
};

/*
 * Get the url of some image
 */
HTMLArea.prototype.imgURL = function(file, plugin) {
	if (typeof(plugin) == "undefined") return _editor_skin + this.config.imgURL + file;
		else return _editor_skin + this.config.imgURL + plugin + "/" + file;
};

/*
 * Get the url of some popup
 */
HTMLArea.prototype.popupURL = function(file) {
	var url = "";
	if(file.match(/^plugin:\/\/(.*?)\/(.*)/)) {
		var pluginId = RegExp.$1;
		var popup = RegExp.$2;
		if(!/\.html$/.test(popup)) popup += ".html";
		if (this.config.pathToPluginDirectory[pluginId]) {
			url = this.config.pathToPluginDirectory[pluginId] + "popups/" + popup;
		} else {
			url = _editor_url + "plugins/" + pluginId + "/popups/" + popup;
		}
	} else {
		url = _typo3_host_url + _editor_url + this.config.popupURL + file;
	}
	return url;
};

/***************************************************
 *  EDITOR UTILITIES
 ***************************************************/
HTMLArea.getInnerText = function(el) {
	var txt = '', i;
	if(el.firstChild) {
		for(i=el.firstChild;i;i =i.nextSibling) {
			if(i.nodeType == 3) txt += i.data;
			else if(i.nodeType == 1) txt += HTMLArea.getInnerText(i);
		}
	} else {
		if(el.nodeType == 3) txt = el.data;
	}
	return txt;
};

HTMLArea.prototype.forceRedraw = function() {
	this._doc.body.style.visibility = "hidden";
	this._doc.body.style.visibility = "visible";
};

/*
 * Focus the editor iframe document or the textarea.
 */
HTMLArea.prototype.focusEditor = function() {
	switch (this._editMode) {
		case "wysiwyg" :
			try {
				if (HTMLArea.is_safari) {
					this._iframe.focus();
				} else if (HTMLArea.is_opera) {
					this._doc.focus();
				} else {
					this._iframe.contentWindow.focus();
				}
			} catch(e) { }
			break;
		case "textmode":
			this._textArea.focus();
			break;
	}
	return this._doc;
};

HTMLArea.undoTakeSnapshot = function(editorNumber) {
	var editor = RTEarea[editorNumber].editor;
	if (editor._doc) {
		editor._undoTakeSnapshot();
	}
};

/*
 * Take a snapshot of the current contents for undo
 */
HTMLArea.prototype._undoTakeSnapshot = function () {
	var currentTime = (new Date()).getTime();
	var newSnapshot = false;
	if (this._undoPos >= this.config.undoSteps) {
			// Remove the first element
		this._undoQueue.shift();
		--this._undoPos;
	}
		// New undo slot should be used if this is first undoTakeSnapshot call or if undoTimeout is elapsed
	if (this._undoPos < 0 || this._undoQueue[this._undoPos].time < currentTime - this.config.undoTimeout) {
		++this._undoPos;
		newSnapshot = true;
	}
		// Get the html text
	var txt = this.getInnerHTML();
	
	if (newSnapshot) {
			// If previous slot contains the same text, a new one should not be used
		if (this._undoPos == 0  || this._undoQueue[this._undoPos - 1].text != txt) {
			this._undoQueue[this._undoPos] = this.buildUndoSnapshot();
			this._undoQueue[this._undoPos].time = currentTime;
			this._undoQueue.length = this._undoPos + 1;
			if (this._undoPos == 1) {
				this.updateToolbar();
			}
		} else {
			this._undoPos--;
		}
 	} else {
		if (this._undoQueue[this._undoPos].text != txt){
			var snapshot = this.buildUndoSnapshot();
			this._undoQueue[this._undoPos].text = snapshot.text;
			this._undoQueue[this._undoPos].bookmark = snapshot.bookmark;
			this._undoQueue[this._undoPos].bookmarkedText = snapshot.bookmarkedText;
			this._undoQueue.length = this._undoPos + 1;
		}
 	}
};

HTMLArea.prototype.buildUndoSnapshot = function () {
	var text, bookmark = null, bookmarkedText = null;
		// Insert a bookmark
	if (this.getMode() == "wysiwyg" && this.isEditable()) {
		var selection = this._getSelection();
		if ((HTMLArea.is_gecko && !HTMLArea.is_opera) || (HTMLArea.is_ie && selection.type.toLowerCase() != "control")) {
				// catch error in FF when the selection contains no usable range
			try {
				bookmark = this.getBookmark(this._createRange(selection));
			} catch (e) {
				bookmark = null;
			}
		}
			// Get the bookmarked html text and remove the bookmark
		if (bookmark) {
			bookmarkedText = this.getInnerHTML();
			var range = this.moveToBookmark(bookmark);
				// Restore Firefox selection
			if (HTMLArea.is_gecko && !HTMLArea.is_opera && !HTMLArea.is_safari) {
				this.emptySelection(selection);
				this.addRangeToSelection(selection, range);
			}
		}
	}
		// Get the html text
	var text = this.getInnerHTML();
	return {
		text		: text,
		bookmark	: bookmark,
		bookmarkedText	: bookmarkedText
	};
};

HTMLArea.prototype.undo = function () {
	if (this._undoPos > 0) {
			// Make sure we would not loose any changes
		this._undoTakeSnapshot();
		var bookmark = this._undoQueue[--this._undoPos].bookmark;
		if (bookmark && this._undoPos) {
			this.setHTML(this._undoQueue[this._undoPos].bookmarkedText);
			this.focusEditor();
			this.selectRange(this.moveToBookmark(bookmark));
			this.scrollToCaret();
		} else {
			this.setHTML(this._undoQueue[this._undoPos].text);
		}
	}
};

HTMLArea.prototype.redo = function () {
	if (this._undoPos < this._undoQueue.length - 1) {
			// Make sure we would not loose any changes
		this._undoTakeSnapshot();
			// Previous call could make undo queue shorter
		if (this._undoPos < this._undoQueue.length - 1) {
			var bookmark = this._undoQueue[++this._undoPos].bookmark;
			if (bookmark) {
				this.setHTML(this._undoQueue[this._undoPos].bookmarkedText);
				this.focusEditor();
				this.selectRange(this.moveToBookmark(bookmark));
				this.scrollToCaret();
			} else {
				this.setHTML(this._undoQueue[this._undoPos].text);
			}
		}
	}
};

/*
 * Update the enabled/disabled/active state of the toolbar elements
 */
HTMLArea.updateToolbar = function(editorNumber) {
	var editor = RTEarea[editorNumber]["editor"];
	editor.updateToolbar();
	editor._timerToolbar = null;
};

HTMLArea.prototype.updateToolbar = function(noStatus) {
	var doc = this._doc,
		text = (this._editMode == "textmode"),
		selection = false,
		ancestors = null, cls = new Array(),
		txt, txtClass, i, inContext, match, matchAny, k, j, n, commandState;
	if(!text) {
		selection = !this._selectionEmpty(this._getSelection());
		ancestors = this.getAllAncestors();
		if(this.config.statusBar && !noStatus) {
				// Unhook previous events handlers
			if(this._statusBarTree.hasChildNodes()) {
				for (i = this._statusBarTree.firstChild; i; i = i.nextSibling) {
					if(i.nodeName.toLowerCase() == "a") {
						HTMLArea._removeEvents(i,["click", "contextmenu, mousedown"], HTMLArea.statusBarHandler);
						i.el = null;
						i.editor = null;
					}
				}
			}
			this._statusBarTree.selected = null;
			this._statusBarTree.innerHTML = '';
			this._statusBarTree.appendChild(document.createTextNode(HTMLArea.I18N.msg["Path"] + ": ")); // clear
			for (i = ancestors.length; --i >= 0;) {
				var el = ancestors[i];
				if(!el) continue;
				var a = document.createElement("a");
				a.href = "#";
				a.el = el;
				a.editor = this;
				if (!HTMLArea.is_opera) {
					HTMLArea._addEvents(a, ["click", "contextmenu"], HTMLArea.statusBarHandler);
				} else {
					HTMLArea._addEvents(a, ["mousedown", "click"], HTMLArea.statusBarHandler);
				}
				txt = el.tagName.toLowerCase();
				a.title = el.style.cssText;
				if (el.id) { txt += "#" + el.id; }
				if (el.className) {
					txtClass = "";
					cls = el.className.trim().split(" ");
					for (j = 0; j < cls.length; ++j) {
						if (!HTMLArea.reservedClassNames.test(cls[j])) {
							txtClass += "." + cls[j];
						}
					}
					txt += txtClass;
				}
				a.appendChild(document.createTextNode(txt));
				this._statusBarTree.appendChild(a);
				if (i != 0) this._statusBarTree.appendChild(document.createTextNode(String.fromCharCode(0xbb)));
			}
		}
	}
	for (var cmd in this._toolbarObjects) {
		if (this._toolbarObjects.hasOwnProperty(cmd)) {
			var btn = this._toolbarObjects[cmd];

				// Determine if the button should be enabled
			inContext = true;
			if (btn.context && !text) {
				inContext = false;
				var attrs = [];
				var contexts = [];
				if (/(.*)\[(.*?)\]/.test(btn.context)) {
					contexts = RegExp.$1.split(",");
					attrs = RegExp.$2.split(",");
				} else {
					contexts = btn.context.split(",");
				}
				for (j = contexts.length; --j >= 0;) contexts[j] = contexts[j].toLowerCase();
				matchAny = (contexts[0] == "*");
				for (k = 0; k < ancestors.length; ++k) {
					if (!ancestors[k]) continue;
					match = false;
					for (j = contexts.length; --j >= 0;) match = match || (ancestors[k].tagName.toLowerCase() == contexts[j]);
					if (matchAny || match) {
						inContext = true;
						for (j = attrs.length; --j >= 0;) {
							if (!eval("ancestors[k]." + attrs[j])) {
								inContext = false;
								break;
							}
						}
						if (inContext) break;
					}
				}
			}

			if (cmd == "CreateLink") {
				btn.state("enabled", (!text || btn.text) && (inContext || selection));
			} else {
				btn.state("enabled", (!text || btn.text) && inContext && (selection || !btn.selection));
			}
			if (typeof(cmd) == "function") { continue; };
				// look-it-up in the custom dropdown boxes
			var dropdown = this.config.customSelects[cmd];
			if ((!text || btn.text) && (typeof(dropdown) !== "undefined") && (typeof(dropdown.refresh) === "function")) {
				dropdown.refresh(this, cmd);
				continue;
			}
			switch (cmd) {
				case "TextIndicator":
					if(!text) {
						try {with (document.getElementById(btn.elementId).style) {
							backgroundColor = HTMLArea._makeColor(doc.queryCommandValue((HTMLArea.is_ie || HTMLArea.is_safari) ? "BackColor" : "HiliteColor"));
								// Mozilla
							if(/transparent/i.test(backgroundColor)) { backgroundColor = HTMLArea._makeColor(doc.queryCommandValue("BackColor")); }
							color = HTMLArea._makeColor(doc.queryCommandValue("ForeColor"));
							fontFamily = doc.queryCommandValue("FontName");
								// Check if queryCommandState is available
							fontWeight = "normal";
							fontStyle = "normal";
							try { fontWeight = doc.queryCommandState("Bold") ? "bold" : "normal"; } catch(ex) { fontWeight = "normal"; };
							try { fontStyle = doc.queryCommandState("Italic") ? "italic" : "normal"; } catch(ex) { fontStyle = "normal"; };
						}} catch (e) {
							// alert(e + "\n\n" + cmd);
						}
					}
					break;
				case "HtmlMode":
					btn.state("active", text);
					break;
				case "Paste":
					if (!text) {
						try {
							btn.state("enabled", doc.queryCommandEnabled('Paste'));
						} catch(e) {
							btn.state("enabled", false);
						}
					}
					break;
				case "Undo":
					btn.state("enabled", !text && (!this._customUndo || this._undoPos > 0));
					break;
				case "Redo":
					btn.state("enabled", !text && (!this._customUndo || this._undoPos < this._undoQueue.length-1));
					break;
				default:
					break;
			}
		}
	}

	if (this._customUndo) {
		this._undoTakeSnapshot();
	}
	for (var pluginId in this.plugins) {
		if (this.plugins.hasOwnProperty(pluginId)) {
			var pluginInstance = this.plugins[pluginId].instance;
			if (typeof(pluginInstance.onUpdateToolbar) === "function") {
				pluginInstance.onUpdateToolbar();
			}
		}
	}
};

/***************************************************
 *  DOM TREE MANIPULATION
 ***************************************************/

/*
 * Surround the currently selected HTML source code with the given tags.
 * Delete the selection, if any.
 */
HTMLArea.prototype.surroundHTML = function(startTag,endTag) {
	this.insertHTML(startTag + this.getSelectedHTML().replace(HTMLArea.Reg_body, "") + endTag);
};

/*
 * Change the tag name of a node.
 */
HTMLArea.prototype.convertNode = function(el,newTagName) {
	var newel = this._doc.createElement(newTagName), p = el.parentNode;
	while (el.firstChild) newel.appendChild(el.firstChild);
	p.insertBefore(newel, el);
	p.removeChild(el);
	return newel;
};

/*
 * Find a parent of an element with a specified tag
 */
HTMLArea.getElementObject = function(el,tagName) {
	var oEl = el;
	while (oEl != null && oEl.nodeName.toLowerCase() != tagName) oEl = oEl.parentNode;
	return oEl;
};

/***************************************************
 *  SELECTIONS AND RANGES
 ***************************************************/

/*
 * Return true if we have some selected content
 */
HTMLArea.prototype.hasSelectedText = function() {
	return this.getSelectedHTML() != "";
};

/*
 * Get an array with all the ancestor nodes of the selection.
 */
HTMLArea.prototype.getAllAncestors = function() {
	var p = this.getParentElement();
	var a = [];
	while (p && (p.nodeType === 1) && (p.nodeName.toLowerCase() !== "body")) {
		a.push(p);
		p = p.parentNode;
	}
	a.push(this._doc.body);
	return a;
};

/*
 * Get the block elements containing the start and the end points of the selection
 */
HTMLArea.prototype.getEndBlocks = function(selection) {
	var range = this._createRange(selection);
	if (HTMLArea.is_gecko) {
		var parentStart = range.startContainer;
		var parentEnd = range.endContainer;
	} else {
		if (selection.type !== "Control" ) {
			var rangeEnd = range.duplicate();
			range.collapse(true);
			var parentStart = range.parentElement();
			rangeEnd.collapse(false);
			var parentEnd = rangeEnd.parentElement();
		} else {
			var parentStart = range.item(0);
			var parentEnd = parentStart;
		}
	}
	while (parentStart && !HTMLArea.isBlockElement(parentStart)) {
		parentStart = parentStart.parentNode;
	}
	while (parentEnd && !HTMLArea.isBlockElement(parentEnd)) {
		parentEnd = parentEnd.parentNode;
	}
	return {	start	: parentStart,
			end	: parentEnd
	};
};

/*
 * Get the deepest ancestor of the selection that is of the specified type
 * Borrowed from Xinha (is not htmlArea) - http://xinha.gogo.co.nz/
 */
HTMLArea.prototype._getFirstAncestor = function(sel,types) {
	var prnt = this._activeElement(sel);
	if (prnt == null) {
		try {
			prnt = (HTMLArea.is_ie ? this._createRange(sel).parentElement() : this._createRange(sel).commonAncestorContainer);
		} catch(e) {
			return null;
		}
	}
	if (typeof(types) == 'string') types = [types];

	while (prnt) {
		if (prnt.nodeType == 1) {
			if (types == null) return prnt;
			for (var i = 0; i < types.length; i++) {
				if(prnt.tagName.toLowerCase() == types[i]) return prnt;
			}
			if(prnt.tagName.toLowerCase() == 'body') break;
			if(prnt.tagName.toLowerCase() == 'table') break;
		}
		prnt = prnt.parentNode;
	}
	return null;
};

/***************************************************
 *  Category: EVENT HANDLERS
 ***************************************************/

/*
 * Intercept some commands and replace them with our own implementation
 */
HTMLArea.prototype.execCommand = function(cmdID, UI, param) {
	this.focusEditor();
	switch (cmdID) {
		case "HtmlMode"	:
			this.setMode();
			break;
		case "Undo"	:
		case "Redo"	:
			if(this._customUndo) this[cmdID.toLowerCase()]();
				else this._doc.execCommand(cmdID,UI,param);
			break;
		case "Cut"	:
		case "Copy"	:
		case "Paste"	:
			try {
				this._doc.execCommand(cmdID, false, null);
					// In FF3, the paste operation will indeed trigger the paste event
				if (HTMLArea.is_gecko && cmdID == "Paste" && this._toolbarObjects.CleanWord && navigator.productSub < 2008020514) {
					this._toolbarObjects.CleanWord.cmd(this, "CleanWord");
				}
			} catch (e) {
				if (HTMLArea.is_gecko && !HTMLArea.is_safari && !HTMLArea.is_opera) {
					this._mozillaPasteException(cmdID, UI, param);
				}
			}
			break;
		default		:
			try {
				this._doc.execCommand(cmdID, UI, param);
			} catch(e) {
				if (this.config.debug) alert(e + "\n\nby execCommand(" + cmdID + ");");
			}
	}
	this.updateToolbar();
	return false;
};

/*
* A generic event handler for things that happen in the IFRAME's document.
*/
HTMLArea._editorEvent = function(ev) {
	if(!ev) var ev = window.event;
	var target = (ev.target) ? ev.target : ev.srcElement;
	var owner = (target.ownerDocument) ? target.ownerDocument : target;
	if(HTMLArea.is_ie) { // IE5.5 does not report any ownerDocument
		while (owner.parentElement) { owner = owner.parentElement; }
	}
	var editor = RTEarea[owner._editorNo]["editor"];
	var keyEvent = (HTMLArea.is_ie && ev.type == "keydown") || (HTMLArea.is_gecko && ev.type == "keypress");
	editor.focusEditor();

	if(keyEvent) {
		if(editor._hasPluginWithOnKeyPressHandler) {
			for (var pluginId in editor.plugins) {
				if (editor.plugins.hasOwnProperty(pluginId)) {
					var pluginInstance = editor.plugins[pluginId].instance;
					if (typeof(pluginInstance.onKeyPress) === "function") {
						if (!pluginInstance.onKeyPress(ev)) {
							HTMLArea._stopEvent(ev);
							return false;
						}
					}
				}
			}
		}
		if(ev.ctrlKey && !ev.shiftKey) {
			if(!ev.altKey) {
					// execute hotkey command
				var key = String.fromCharCode((HTMLArea.is_ie || HTMLArea.is_safari || HTMLArea.is_opera) ? ev.keyCode : ev.charCode).toLowerCase();
				if (HTMLArea.is_gecko && ev.keyCode == 32) key = String.fromCharCode(ev.keyCode).toLowerCase();
				if (key == " ") {
					editor.insertHTML("&nbsp;");
					editor.updateToolbar();
					HTMLArea._stopEvent(ev);
					return false;
				}
				if (!editor.config.hotKeyList[key]) return true;
				var cmd = editor.config.hotKeyList[key].cmd;
				if (!cmd) return true;
				switch (cmd) {
					case "SelectAll":
					case "Undo"	:
					case "Redo"	:
						cmd = editor.config.hotKeyList[key].cmd;
						editor.execCommand(cmd, false, null);
						HTMLArea._stopEvent(ev);
						return false;
						break;
					case "Paste"	:
						if (HTMLArea.is_ie || HTMLArea.is_safari) {
							cmd = editor.config.hotKeyList[key].cmd;
							editor.execCommand(cmd, false, null);
							HTMLArea._stopEvent(ev);
							return false;
							// In FF3, the paste operation will indeed trigger the paste event
						} else if (HTMLArea.is_opera || (HTMLArea.is_gecko && navigator.productSub < 2008020514)) {
							if (editor._toolbarObjects.CleanWord) {
								var cleanLaterFunctRef = editor.plugins.DefaultClean ? editor.plugins.DefaultClean.instance.cleanLaterFunctRef : (editor.plugins.TYPO3HtmlParser ? editor.plugins.TYPO3HtmlParser.instance.cleanLaterFunctRef : null);
								if (cleanLaterFunctRef) {
									window.setTimeout(cleanLaterFunctRef, 50);
								}
							}
						}
						break;
					default:
						if (editor.config.hotKeyList[key] && editor.config.hotKeyList[key].action) {
							if (!editor.config.hotKeyList[key].action(editor, key)) {
								HTMLArea._stopEvent(ev);
								return false;
							}
						}
				}
			}
		} else if (ev.altKey) {
				// check if context menu is already handling this event
			if(editor.plugins["ContextMenu"] && editor.plugins["ContextMenu"].instance) {
				var keys = editor.plugins["ContextMenu"].instance.keys;
				if (keys.length > 0) {
					var k;
					for (var i = keys.length; --i >= 0;) {
						k = keys[i];
						if (k[0].toLowerCase() == key) {
							HTMLArea._stopEvent(ev);
							return false;
						}
					}
				}
			}
		} else if (keyEvent) {
			if (HTMLArea.is_gecko) editor._detectURL(ev);
			switch (ev.keyCode) {
				case 13	: // KEY enter
					if (HTMLArea.is_gecko) {
						if (!ev.shiftKey && !editor.config.disableEnterParagraphs) {
							if (editor._checkInsertP()) {
								HTMLArea._stopEvent(ev);
							}
						} else if (HTMLArea.is_safari) {
							var brNode = document.createElement("br");
							editor.insertNodeAtSelection(brNode);
							if (!brNode.nextSibling || !HTMLArea.getInnerText(brNode.nextSibling)) {
								var secondBrNode = document.createElement("br");
								secondBrNode = brNode.parentNode.appendChild(secondBrNode);
								editor.selectNode(secondBrNode, false);
							}
							HTMLArea._stopEvent(ev);
						}
							// update the toolbar state after some time
						if (editor._timerToolbar) window.clearTimeout(editor._timerToolbar);
						editor._timerToolbar = window.setTimeout("HTMLArea.updateToolbar(\'" + editor._editorNumber + "\');", 100);
						return false;
					}
					break;
				case 8	: // KEY backspace
				case 46	: // KEY delete
					if ((HTMLArea.is_gecko && !ev.shiftKey) || HTMLArea.is_ie) {
						if (editor._checkBackspace()) HTMLArea._stopEvent(ev);
					}
						// update the toolbar state after some time
					if (editor._timerToolbar) window.clearTimeout(editor._timerToolbar);
					editor._timerToolbar = window.setTimeout("HTMLArea.updateToolbar(\'" + editor._editorNumber + "\');", 50);
					break;
				case 9: // KEY horizontal tab
					var newkey = (ev.shiftKey ? "SHIFT-" : "") + "TAB";
					if (editor.config.hotKeyList[newkey] && editor.config.hotKeyList[newkey].action) {
						if (!editor.config.hotKeyList[newkey].action(editor, newkey)) {
							HTMLArea._stopEvent(ev);
							return false;
						}
					}
					break;
				case 37: // LEFT arrow key
				case 38: // UP arrow key
				case 39: // RIGHT arrow key
				case 40: // DOWN arrow key
					if (HTMLArea.is_ie) {
						if (editor._timerToolbar) window.clearTimeout(editor._timerToolbar);
						editor._timerToolbar = window.setTimeout("HTMLArea.updateToolbar(\'" + editor._editorNumber + "\');", 10);
						return true;
					}
			}
		}
	} else {
			// mouse event
		if (editor._timerToolbar) window.clearTimeout(editor._timerToolbar);
		if (ev.type == "mouseup") editor.updateToolbar();
			else editor._timerToolbar = window.setTimeout("HTMLArea.updateToolbar(\'" + editor._editorNumber + "\');", 50);
	}
};

HTMLArea.prototype.scrollToCaret = function() {
	if (HTMLArea.is_gecko) {
		var e = this.getParentElement(),
			w = this._iframe.contentWindow ? this._iframe.contentWindow : window,
			h = w.innerHeight || w.height,
			d = this._doc,
			t = d.documentElement.scrollTop || d.body.scrollTop;
		if (e.offsetTop > h+t || e.offsetTop < t) {
			this.getParentElement().scrollIntoView();
		}
	}
};

/*
 * Retrieve the HTML
 */
HTMLArea.prototype.getHTML = function() {
	switch (this._editMode) {
		case "wysiwyg":
			return HTMLArea.getHTML(this._doc.body, false, this);
		case "textmode":
			return this._textArea.value;
	}
	return false;
};

/*
 * Retrieve raw HTML
 */
HTMLArea.prototype.getInnerHTML = function() {
	switch (this._editMode) {
		case "wysiwyg":
			return this._doc.body.innerHTML;
		case "textmode":
			return this._textArea.value;
	}
	return false;
};

/*
 * Replace the HTML inside
 */
HTMLArea.prototype.setHTML = function(html) {
	switch (this._editMode) {
		case "wysiwyg":
			this._doc.body.innerHTML = html;
			break;
		case "textmode":
			this._textArea.value = html;
			break;
	}
	return false;
};

/*
 * Set the given doctype when config.fullPage is true
 */
HTMLArea.prototype.setDoctype = function(doctype) {
	this.doctype = doctype;
};

/***************************************************
 *  UTILITY FUNCTIONS
 ***************************************************/

// variable used to pass the object to the popup editor window.
HTMLArea._object = null;

/*
 * Check if the client agent is supported
 */
HTMLArea.checkSupportedBrowser = function() {
	if(HTMLArea.is_gecko && !HTMLArea.is_safari && !HTMLArea.is_opera) {
		if(navigator.productSub < 20030210) return false;
	}
	return HTMLArea.is_gecko || HTMLArea.is_ie;
};

/*	EventCache Version 1.0
 *	Copyright 2005 Mark Wubben
 *	Adaptation by Stanislas Rolland
 *	Provides a way for automatically removing events from nodes and thus preventing memory leakage.
 *	See <http://novemberborn.net/javascript/event-cache> for more information.
 *	This software is licensed under the CC-GNU LGPL <http://creativecommons.org/licenses/LGPL/2.1/>
 *	Event Cache uses an anonymous function to create a hidden scope chain. This is to prevent scoping issues.
 */
HTMLArea._eventCacheConstructor = function() {
	var listEvents = [];

	return ({
		listEvents : listEvents,

		add : function(node, sEventName, fHandler) {
			listEvents.push(arguments);
		},

		flush : function() {
			var item;
			for (var i = listEvents.length; --i >= 0;) {
				item = listEvents[i];
				try {
					HTMLArea._removeEvent(item[0], item[1], item[2]);
					item[0][item[1]] = null;
					item[0] = null;
					item[2] = null;
				} catch(e) { }
			}
			listEvents.length = 0;
		}
	});
};

/*
 * Register an event
 */
HTMLArea._addEvent = function(el,evname,func,useCapture) {
	if (typeof(useCapture) == "undefined") {
		var useCapture = false;
	}
	if (HTMLArea.is_gecko) {
		el.addEventListener(evname, func, !HTMLArea.is_opera || useCapture);
	} else {
		el.attachEvent("on" + evname, func);
	}
	HTMLArea._eventCache.add(el, evname, func);
};

/*
 * Register a list of events
 */
HTMLArea._addEvents = function(el,evs,func,useCapture) {
	if (typeof(useCapture) == "undefined") {
		var useCapture = false;
	}
	for (var i = evs.length; --i >= 0;) {
		HTMLArea._addEvent(el,evs[i], func, useCapture);
	}
};

/*
 * Remove an event listener
 */
HTMLArea._removeEvent = function(el,evname,func) {
	if (HTMLArea.is_gecko) {
			// Avoid Safari crash when removing events on some orphan documents
		if (!HTMLArea.is_safari || HTMLArea.is_chrome) {
			try {
				el.removeEventListener(evname, func, true);
				el.removeEventListener(evname, func, false);
			} catch(e) { }
		} else if (el.nodeType != 9 || el.defaultView) {
			try {
				el.removeEventListener(evname, func, true);
				el.removeEventListener(evname, func, false);
			} catch(e) { }
		}
	} else {
		try {
			el.detachEvent("on" + evname, func);
		} catch(e) { }
	}
};

/*
 * Remove a list of events
 */
HTMLArea._removeEvents = function(el,evs,func) {
	for (var i = evs.length; --i >= 0;) { HTMLArea._removeEvent(el, evs[i], func); }
};

/*
 * Stop event propagation
 */
HTMLArea._stopEvent = function(ev) {
	if(HTMLArea.is_gecko) {
		ev.stopPropagation();
		ev.preventDefault();
	} else {
		ev.cancelBubble = true;
		ev.returnValue = false;
	}
};

/*
 * Remove a class name from the class attribute
 */
HTMLArea._removeClass = function(el, removeClassName) {
	if(!(el && el.className)) return;
	var cls = el.className.trim().split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != removeClassName) ar[ar.length] = cls[i];
	}
	if (ar.length == 0) {
		if (!HTMLArea.is_opera) el.removeAttribute(HTMLArea.is_gecko ? "class" : "className");
			else el.className = '';

	} else el.className = ar.join(" ");
};

/*
 * Add a class name to the class attribute
 */
HTMLArea._addClass = function(el, addClassName) {
	HTMLArea._removeClass(el, addClassName);
	if (el.className && HTMLArea.classesXOR) {
		var classNames = el.className.trim().split(" ");
		for (var i = classNames.length; --i >= 0;) {
			if (HTMLArea.classesXOR[addClassName] && HTMLArea.classesXOR[addClassName].test(classNames[i])) {
				HTMLArea._removeClass(el, classNames[i]);
			}
		}
	}
	if (el.className) el.className += " " + addClassName;
		else el.className = addClassName;
};

/*
 * Check if a class name is in the class attribute
 */
HTMLArea._hasClass = function(el, className) {
	if (!el || !el.className) return false;
	var cls = el.className.split(" ");
	for (var i = cls.length; i > 0;) {
		if(cls[--i] == className) return true;
	}
	return false;
};

/*
 * Select a value in a select element
 *
 * @param	object		select: the select object
 * @param	string		value: the value
 * @return	void
 */
HTMLArea.selectValue = function(select, value) {
	var options = select.getElementsByTagName("option");
	for (var i = options.length; --i >= 0;) {
		var option = options[i];
		option.selected = (option.value == value);
		select.selectedIndex = i;
	}
};

HTMLArea.RE_blockTags = /^(body|p|h1|h2|h3|h4|h5|h6|ul|ol|pre|dl|dt|dd|div|noscript|blockquote|form|hr|table|caption|fieldset|address|td|tr|th|li|tbody|thead|tfoot|iframe)$/;
HTMLArea.isBlockElement = function(el) { return el && el.nodeType == 1 && HTMLArea.RE_blockTags.test(el.nodeName.toLowerCase()); };
HTMLArea.RE_closingTags = /^(p|blockquote|a|li|ol|ul|dl|dt|td|th|tr|tbody|thead|tfoot|caption|colgroup|table|div|b|bdo|big|cite|code|del|dfn|em|i|ins|kbd|label|q|samp|small|span|strike|strong|sub|sup|tt|u|var|abbr|acronym|font|center|object|embed|style|script|title|head)$/;
HTMLArea.RE_noClosingTag = /^(img|br|hr|col|input|area|base|link|meta|param)$/;
HTMLArea.needsClosingTag = function(el) { return el && el.nodeType == 1 && !HTMLArea.RE_noClosingTag.test(el.tagName.toLowerCase()); };

/*
 * Perform HTML encoding of some given string
 * Borrowed in part from Xinha (is not htmlArea) - http://xinha.gogo.co.nz/
 */
HTMLArea.htmlDecode = function(str) {
	str = str.replace(/&lt;/g, "<").replace(/&gt;/g, ">");
	str = str.replace(/&nbsp;/g, "\xA0"); // Decimal 160, non-breaking-space
	str = str.replace(/&quot;/g, "\x22");
	str = str.replace(/&#39;/g, "'") ;
	str = str.replace(/&amp;/g, "&");
	return str;
};
HTMLArea.htmlEncode = function(str) {
	if (typeof(str) != 'string') str = str.toString(); // we don't need regexp for that, but.. so be it for now.
	str = str.replace(/&/g, "&amp;");
	str = str.replace(/</g, "&lt;").replace(/>/g, "&gt;");
	str = str.replace(/\xA0/g, "&nbsp;"); // Decimal 160, non-breaking-space
	str = str.replace(/\x22/g, "&quot;"); // \x22 means '"'
	return str;
};

/*
 * Retrieve the HTML code from the given node.
 * This is a replacement for getting innerHTML, using standard DOM calls.
 * Wrapper catches a Mozilla-Exception with non well-formed html source code.
 */
HTMLArea.getHTML = function(root, outputRoot, editor){
	try {
		return HTMLArea.getHTMLWrapper(root,outputRoot,editor);
	} catch(e) {
		HTMLArea._appendToLog("The HTML document is not well-formed.");
		if(!HTMLArea._debugMode) alert(HTMLArea.I18N.msg["HTML-document-not-well-formed"]);
			else return HTMLArea.getHTMLWrapper(root,outputRoot,editor);
		return editor._doc.body.innerHTML;
	}
};

HTMLArea.getHTMLWrapper = function(root, outputRoot, editor) {
	var html = "";
	if(!root) return html;
	switch (root.nodeType) {
	   case 1:	// ELEMENT_NODE
	   case 11:	// DOCUMENT_FRAGMENT_NODE
	   case 9:	// DOCUMENT_NODE
		var closed, i, config = editor.config;
		var root_tag = (root.nodeType == 1) ? root.tagName.toLowerCase() : '';
		if (root_tag == "br" && config.removeTrailingBR && !root.nextSibling && HTMLArea.isBlockElement(root.parentNode) && (!root.previousSibling || root.previousSibling.nodeName.toLowerCase() != "br")) {
			if (!root.previousSibling && root.parentNode && root.parentNode.nodeName.toLowerCase() == "p" && root.parentNode.className) html += "&nbsp;";
			break;
		}
		if (config.htmlRemoveTagsAndContents && config.htmlRemoveTagsAndContents.test(root_tag)) break;
		var custom_tag = (config.customTags && config.customTags.test(root_tag));
		if (outputRoot) outputRoot = !(config.htmlRemoveTags && config.htmlRemoveTags.test(root_tag));
		if ((HTMLArea.is_ie || HTMLArea.is_safari) && root_tag == "head") {
			if(outputRoot) html += "<head>";
			var save_multiline = RegExp.multiline;
			RegExp.multiline = true;
			var txt = root.innerHTML.replace(HTMLArea.RE_tagName, function(str, p1, p2) {
				return p1 + p2.toLowerCase();
			});
			RegExp.multiline = save_multiline;
			html += txt;
			if(outputRoot) html += "</head>";
			break;
		} else if (outputRoot) {
			if (HTMLArea.is_gecko && root.hasAttribute('_moz_editor_bogus_node')) break;
			closed = (!(root.hasChildNodes() || HTMLArea.needsClosingTag(root) || custom_tag));
			html = "<" + root_tag;
			var a, name, value, attrs = root.attributes;
			var n = attrs.length;
			for (i = attrs.length; --i >= 0 ;) {
				a = attrs.item(i);
				name = a.nodeName.toLowerCase();
				if ((!a.specified && name != 'value') || /_moz|contenteditable|_msh/.test(name)) continue;
				if (!HTMLArea.is_ie || name != "style") {
						// IE5.5 reports wrong values. For this reason we extract the values directly from the root node.
						// Using Gecko the values of href and src are converted to absolute links unless we get them using nodeValue()
					if (typeof(root[a.nodeName]) != "undefined" && name != "href" && name != "src" && name != "style" && !/^on/.test(name)) {
						value = root[a.nodeName];
					} else {
						value = a.nodeValue;
						if (HTMLArea.is_ie && (name == "href" || name == "src") && editor.plugins.link && editor.plugins.link.instance && editor.plugins.link.instance.stripBaseURL) {
							value = editor.plugins.link.instance.stripBaseURL(value);
						}
					}
				} else { // IE fails to put style in attributes list.
					value = root.style.cssText;
				}
					// Mozilla reports some special values; we don't need them.
				if(/(_moz|^$)/.test(value)) continue;
					// Strip value="0" reported by IE on all li tags
				if(HTMLArea.is_ie && root_tag == "li" && name == "value" && a.nodeValue == 0) continue;
				html += " " + name + '="' + HTMLArea.htmlEncode(value) + '"';
			}
			if (html != "") html += closed ? " />" : ">";
		}
		for (i = root.firstChild; i; i = i.nextSibling) {
			if (/^li$/i.test(i.tagName) && !/^[ou]l$/i.test(root.tagName)) html += "<ul>" + HTMLArea.getHTMLWrapper(i, true, editor) + "</ul>";
				 else html += HTMLArea.getHTMLWrapper(i, true, editor);
		}
		if (outputRoot && !closed) html += "</" + root_tag + ">";
		break;
	    case 3:	// TEXT_NODE
		html = /^(script|style)$/i.test(root.parentNode.tagName) ? root.data : HTMLArea.htmlEncode(root.data);
		break;
	    case 8:	// COMMENT_NODE
		if (!editor.config.htmlRemoveComments) html = "<!--" + root.data + "-->";
		break;
	    case 4:	// Node.CDATA_SECTION_NODE
			// Mozilla seems to convert CDATA into a comment when going into wysiwyg mode, don't know about IE
		html += '<![CDATA[' + root.data + ']]>';
		break;
	    case 5:	// Node.ENTITY_REFERENCE_NODE
		html += '&' + root.nodeValue + ';';
		break;
	    case 7:	// Node.PROCESSING_INSTRUCTION_NODE
			// PI's don't seem to survive going into the wysiwyg mode, (at least in moz) so this is purely academic
		html += '<?' + root.target + ' ' + root.data + ' ?>';
		break;
	    default:
	    	break;
	}
	return html;
};

HTMLArea.getPrevNode = function(node) {
	if(!node)                return null;
	if(node.previousSibling) return node.previousSibling;
	if(node.parentNode)      return node.parentNode;
	return null;
};

HTMLArea.getNextNode = function(node) {
	if(!node)            return null;
	if(node.nextSibling) return node.nextSibling;
	if(node.parentNode)  return node.parentNode;
	return null;
};

HTMLArea.removeFromParent = function(el) {
	if(!el.parentNode) return;
	var pN = el.parentNode;
	pN.removeChild(el);
	return el;
};

String.prototype.trim = function() {
	return this.replace(/^\s+/, '').replace(/\s+$/, '');
};

// creates a rgb-style color from a number
HTMLArea._makeColor = function(v) {
	if (typeof(v) != "number") {
		// already in rgb (hopefully); IE doesn't get here.
		return v;
	}
	// IE sends number; convert to rgb.
	var r = v & 0xFF;
	var g = (v >> 8) & 0xFF;
	var b = (v >> 16) & 0xFF;
	return "rgb(" + r + "," + g + "," + b + ")";
};

// returns hexadecimal color representation from a number or a rgb-style color.
HTMLArea._colorToRgb = function(v) {
	if (!v)
		return '';

	// returns the hex representation of one byte (2 digits)
	function hex(d) {
		return (d < 16) ? ("0" + d.toString(16)) : d.toString(16);
	};

	if (typeof(v) == "number") {
		// we're talking to IE here
		var r = v & 0xFF;
		var g = (v >> 8) & 0xFF;
		var b = (v >> 16) & 0xFF;
		return "#" + hex(r) + hex(g) + hex(b);
	}

	if (v.substr(0, 3) == "rgb") {
		// in rgb(...) form -- Mozilla
		var re = /rgb\s*\(\s*([0-9]+)\s*,\s*([0-9]+)\s*,\s*([0-9]+)\s*\)/;
		if (v.match(re)) {
			var r = parseInt(RegExp.$1);
			var g = parseInt(RegExp.$2);
			var b = parseInt(RegExp.$3);
			return "#" + hex(r) + hex(g) + hex(b);
		}
		// doesn't match RE?!  maybe uses percentages or float numbers
		// -- FIXME: not yet implemented.
		return null;
	}

	if (v.substr(0, 1) == "#") {
		// already hex rgb (hopefully :D )
		return v;
	}

	// if everything else fails ;)
	return null;
};

/** Use XML HTTPRequest to post some data back to the server and do something
 * with the response (asyncronously!), this is used by such things as the spellchecker update personal dict function
 */
HTMLArea._postback = function(url, data, handler, addParams, charset) {
	if (typeof(charset) == "undefined") var charset = "utf-8";
	var req = null;
	if (window.XMLHttpRequest) req = new XMLHttpRequest();
		else if (window.ActiveXObject) {
			var success = false;
			for (var k = 0; k < HTMLArea.MSXML_XMLHTTP_PROGIDS.length && !success; k++) {
				try {
					req = new ActiveXObject(HTMLArea.MSXML_XMLHTTP_PROGIDS[k]);
					success = true;
				} catch (e) { }
			}
		}

	if(req) {
		var content = '';
		for (var i in data) {
			content += (content.length ? '&' : '') + i + '=' + encodeURIComponent(data[i]);
		}
		content += (content.length ? '&' : '') + 'charset=' + charset;
		if (typeof(addParams) != "undefined") content += addParams;
		if (url.substring(0,1) == '/') {
			var postUrl = _typo3_host_url + url;
		} else {
			var postUrl = _typo3_host_url + _editor_url + url;
		}

		function callBack() {
			if(req.readyState == 4) {
				if (req.status == 200) {
					if (typeof(handler) == 'function') handler(req.responseText, req);
					HTMLArea._appendToLog("[HTMLArea::_postback]: Server response: " + req.responseText);
				} else {
					HTMLArea._appendToLog("ERROR [HTMLArea::_postback]: Unable to post " + postUrl + " . Server reported " + req.statusText);
				}
			}
		}
		req.onreadystatechange = callBack;
		function sendRequest() {
			HTMLArea._appendToLog("[HTMLArea::_postback]: Request: " + content);
			req.send(content);
		}

		req.open('POST', postUrl, true);
		req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		window.setTimeout(sendRequest, 500);
	}
};

/**
 * Internet Explorer returns an item having the _name_ equal to the given id, even if it's not having any id.
 * This way it can return a different form field even if it's not a textarea.  This works around the problem by
 * specifically looking to search only elements having a certain tag name.
 */
HTMLArea.getElementById = function(tag, id) {
	var el, i, objs = document.getElementsByTagName(tag);
	for (i = objs.length; --i >= 0 && (el = objs[i]);) {
		if (el.id == id) return el;
	}
	return null;
};

/***************************************************
 * TYPO3-SPECIFIC FUNCTIONS
 ***************************************************/
/*
 * Set the size of textarea with the RTE. It's called, if we are in fullscreen-mode.
 */
var setRTEsizeByJS = function(divId, height, width) {
	if (HTMLArea.is_gecko) height = height - 25;
		else height = height - 60;
	if (height > 0) document.getElementById(divId).style.height =  height + "px";
	if (HTMLArea.is_gecko) width = "99%";
		else width = "97%";
	document.getElementById(divId).style.width = width;
};

/*
 * Extending the TYPO3 Lorem Ipsum extension
 */
var lorem_ipsum = function(element,text) {
	if (element.tagName.toLowerCase() == "textarea" && element.id && element.id.substr(0,7) == "RTEarea") {
		var editor = RTEarea[element.id.substr(7, element.id.length)]["editor"];
		editor.insertHTML(text);
		editor.updateToolbar();
	}
};

/*
 * Initialize the editor, configure the toolbar, setup the plugins, etc.
 */
HTMLArea.initTimer = new Object();

HTMLArea.onGenerateHandler = function(editorNumber) {
	return (function() {
		document.getElementById('pleasewait' + editorNumber).style.display = 'none';
		document.getElementById('editorWrap' + editorNumber).style.visibility = 'visible';
		editorNumber = null;
	});
};

HTMLArea.initEditor = function(editorNumber) {
	if(HTMLArea.checkSupportedBrowser()) {
		document.getElementById('pleasewait' + editorNumber).style.display = 'block';
		document.getElementById('editorWrap' + editorNumber).style.visibility = 'hidden';
		if(HTMLArea.initTimer[editorNumber]) window.clearTimeout(HTMLArea.initTimer[editorNumber]);
		if(!HTMLArea.is_loaded) {
			HTMLArea.initTimer[editorNumber] = window.setTimeout("HTMLArea.initEditor(\'" + editorNumber + "\');", 150);
		} else {
			var RTE = RTEarea[editorNumber];
			HTMLArea._appendToLog("[HTMLArea::initEditor]: Initializing editor with editor Id: " + editorNumber + ".");

				// Get the configuration properties
			var config = new HTMLArea.Config();
			for (var property in RTE) {
				if (RTE.hasOwnProperty(property)) {
					config[property] = RTE[property] ? RTE[property] : false;
				}
			}
				// Create an editor for the textarea
			var editor = new HTMLArea(RTE.id, config);
			RTE.editor = editor;

				// Save the editornumber in the object
			editor._typo3EditerNumber = editorNumber;
			editor._editorNumber = editorNumber;

				// Override these settings if they were ever modified
			editor.config.width = "auto";
			editor.config.height = "auto";
			editor.config.sizeIncludesToolbar = true;
			editor.config.fullPage = false;

				// Register the plugins included in the configuration
			for (var plugin in editor.config.plugin) {
				if (editor.config.plugin.hasOwnProperty(plugin) && editor.config.plugin[plugin]) {
					editor.registerPlugin(plugin);
				}
			}

			editor.onGenerate = HTMLArea.onGenerateHandler(editorNumber);

			editor.generate();
			return false;
		}
	} else {
		document.getElementById('pleasewait' + editorNumber).style.display = 'none';
		document.getElementById('editorWrap' + editorNumber).style.visibility = 'visible';
	}
};

HTMLArea.allElementsAreDisplayed = function(elements) {
	for (var i=0, length=elements.length; i < length; i++) {
		if (document.getElementById(elements[i]).style.display == 'none') {
			return false;
		}
	}
	return true;
};

/**
 *	Base, version 1.0.2
 *	Copyright 2006, Dean Edwards
 *	License: http://creativecommons.org/licenses/LGPL/2.1/
 */

HTMLArea.Base = function() {
	if (arguments.length) {
		if (this == window) { // cast an object to this class
			HTMLArea.Base.prototype.extend.call(arguments[0], arguments.callee.prototype);
		} else {
			this.extend(arguments[0]);
		}
	}
};

HTMLArea.Base.version = "1.0.2";

HTMLArea.Base.prototype = {
	extend: function(source, value) {
		var extend = HTMLArea.Base.prototype.extend;
		if (arguments.length == 2) {
			var ancestor = this[source];
			// overriding?
			if ((ancestor instanceof Function) && (value instanceof Function) &&
				ancestor.valueOf() != value.valueOf() && /\bbase\b/.test(value)) {
				var method = value;
			//	var _prototype = this.constructor.prototype;
			//	var fromPrototype = !Base._prototyping && _prototype[source] == ancestor;
				value = function() {
					var previous = this.base;
				//	this.base = fromPrototype ? _prototype[source] : ancestor;
					this.base = ancestor;
					var returnValue = method.apply(this, arguments);
					this.base = previous;
					return returnValue;
				};
				// point to the underlying method
				value.valueOf = function() {
					return method;
				};
				value.toString = function() {
					return String(method);
				};
			}
			return this[source] = value;
		} else if (source) {
			var _prototype = {toSource: null};
			// do the "toString" and other methods manually
			var _protected = ["toString", "valueOf"];
			// if we are prototyping then include the constructor
			if (HTMLArea.Base._prototyping) _protected[2] = "constructor";
			for (var i = 0; (name = _protected[i]); i++) {
				if (source[name] != _prototype[name]) {
					extend.call(this, name, source[name]);
				}
			}
			// copy each of the source object's properties to this object
			for (var name in source) {
				if (!_prototype[name]) {
					extend.call(this, name, source[name]);
				}
			}
		}
		return this;
	},

	base: function() {
		// call this method from any other method to invoke that method's ancestor
	}
};

HTMLArea.Base.extend = function(_instance, _static) {
	var extend = HTMLArea.Base.prototype.extend;
	if (!_instance) _instance = {};
	// build the prototype
	HTMLArea.Base._prototyping = true;
	var _prototype = new this;
	extend.call(_prototype, _instance);
	var constructor = _prototype.constructor;
	_prototype.constructor = this;
	delete HTMLArea.Base._prototyping;
	// create the wrapper for the constructor function
	var klass = function() {
		if (!HTMLArea.Base._prototyping) constructor.apply(this, arguments);
		this.constructor = klass;
	};
	klass.prototype = _prototype;
	// build the class interface
	klass.extend = this.extend;
	klass.implement = this.implement;
	klass.toString = function() {
		return String(constructor);
	};
	extend.call(klass, _static);
	// single instance
	var object = constructor ? klass : _prototype;
	// class initialisation
	if (object.init instanceof Function) object.init();
	return object;
};

HTMLArea.Base.implement = function(_interface) {
	if (_interface instanceof Function) _interface = _interface.prototype;
	this.prototype.extend(_interface);
};

/**
 * HTMLArea.plugin class
 *
 * Every plugin should be a subclass of this class
 *
 */
HTMLArea.Plugin = HTMLArea.Base.extend({

	/**
	 * HTMLArea.plugin constructor
	 *
	 * @param	object		editor: instance of RTE
	 * @param	string		pluginName: name of the plugin
	 *
	 * @return	boolean		true if the plugin was configured
	 */
	constructor : function(editor, pluginName) {
		this.editor = editor;
		this.editorNumber = editor._editorNumber;
		this.editorConfiguration = editor.config;
		this.name = pluginName;
		try {
			HTMLArea.I18N[this.name] = eval(this.name + "_langArray");
			this.I18N = HTMLArea.I18N[this.name];
		} catch(e) {
			this.appendToLog("initialize", "The localization array for plugin " + this.name + "  could not be assigned.");
		}
		return this.configurePlugin(editor);
	},

	/**
	 * Configures the plugin
	 * This function is invoked by the class constructor.
	 * This function should be redefined by the plugin subclass. Normal steps would be:
	 *	- registering plugin ingormation with method registerPluginInformation;
	 *	- registering any buttons with method registerButton;
	 *	- registering any drop-down lists with method registerDropDown.
	 *
	 * @param	object		editor: instance of RTE
	 *
	 * @return	boolean		true if the plugin was configured
	 */
	configurePlugin : function(editor) {
		return false;
	},

	/**
	 * Registers the plugin "About" information
	 *
	 * @param	object		pluginInformation:
	 *					version		: the version,
	 *					developer	: the name of the developer,
	 *					developerUrl	: the url of the developer,
	 *					copyrightOwner	: the name of the copyright owner,
	 *					sponsor		: the name of the sponsor,
	 *					sponsorUrl	: the url of the sponsor,
	 *					license		: the type of license (should be "GPL")
	 *
	 * @return	boolean		true if the information was registered
	 */
	registerPluginInformation : function(pluginInformation) {
		if (typeof(pluginInformation) !== "object") {
			this.appendToLog("registerPluginInformation", "Plugin information was not provided");
			return false;
		} else {
			this.pluginInformation = pluginInformation;
			this.pluginInformation.name = this.name;
				/* Ensure backwards compatibility */
			this.pluginInformation.developer_url = this.pluginInformation.developerUrl;
			this.pluginInformation.c_owner = this.pluginInformation.copyrightOwner;
			this.pluginInformation.sponsor_url = this.pluginInformation.sponsorUrl;
			return true;
		}
	},

	/**
	 * Returns the plugin information
	 *
	 * @return	object		the plugin information object
	 */
	getPluginInformation : function() {
		return this.pluginInformation;
	},

	/**
	 * Returns true if the button is enabled in the toolbar configuration
	 *
	 * @param	string		buttonId: identification of the button
	 *
	 * @return	boolean		true if the button is enabled in the toolbar configuration
	 */
	isButtonInToolbar : function(buttonId) {
		var toolbar = this.editorConfiguration.toolbar;
		var n = toolbar.length;
		for ( var i = 0; i < n; ++i ) {
			var buttonInToolbar = new RegExp( "^(" + toolbar[i].join("|") + ")$", "i");
			if (buttonInToolbar.test(buttonId)) {
				return true;
			}
		}
		return false;
	},

	/**
	 * Registors a button for inclusion in the toolbar
	 *
	 * @param	object		buttonConfiguration: the configuration object of the button:
	 *					id		: unique id for the button
	 *					tooltip		: tooltip for the button
	 *					textMode	: enable in text mode
	 *					action		: name of the function invoked when the button is pressed
	 *					context		: will be disabled if not inside one of listed elements
	 *					hide		: hide in menu and show only in context menu?
	 *					selection	: will be disabled if there is no selection?
	 *					hotkey		: hotkey character
	 *					dialog		: if true, the button opens a dialog
	 *
	 * @return	boolean		true if the button was successfully registered
	 */
	registerButton : function (buttonConfiguration) {
		if (this.isButtonInToolbar(buttonConfiguration.id)) {
			if ((typeof(buttonConfiguration.action) === "string") && (typeof(this[buttonConfiguration.action]) === "function")) {
				var hotKeyAction = buttonConfiguration.action;
				var actionFunctionReference = this.makeFunctionReference(buttonConfiguration.action);
				buttonConfiguration.action = actionFunctionReference;
				if (!buttonConfiguration.textMode) {
					buttonConfiguration.textMode = false;
				}
				if (!buttonConfiguration.dialog) {
					buttonConfiguration.dialog = false;
				}
				if (this.editorConfiguration.registerButton(buttonConfiguration)) {
					var hotKey = buttonConfiguration.hotKey ? buttonConfiguration.hotKey : ((this.editorConfiguration.buttons[buttonConfiguration.id.toLowerCase()] && this.editorConfiguration.buttons[buttonConfiguration.id.toLowerCase()].hotKey) ? this.editorConfiguration.buttons[buttonConfiguration.id.toLowerCase()].hotKey : null);
					if (hotKey) {
						var hotKeyConfiguration = {
							id	: hotKey,
							cmd	: buttonConfiguration.id,
							action	: hotKeyAction
						};
						return this.registerHotKey(hotKeyConfiguration);
					}
					return true;
				}
			} else {
				this.appendToLog("registerButton", "Function " + buttonConfiguration.action + " was not defined when registering button " + buttonConfiguration.id);
			}
		}
		return false;
	},

	/**
	 * Registors a drop-down list for inclusion in the toolbar
	 *
	 * @param	object		dropDownConfiguration: the configuration object of the drop-down:
	 *					id		: unique id for the drop-down
	 *					tooltip		: tooltip for the drop-down
	 *					textMode	: enable in text mode
	 *					action		: name of the function invoked when a new option is selected
	 *					refresh		: name of the function invoked in order to refresh the drop-down when the toolbar is updated
	 *					context		: will be disabled if not inside one of listed elements
	 *
	 * @return	boolean		true if the drop-down list was successfully registered
	 */
	registerDropDown : function (dropDownConfiguration) {
		if (this.isButtonInToolbar(dropDownConfiguration.id)) {
			if (typeof((dropDownConfiguration.action) === "string") && (typeof(this[dropDownConfiguration.action]) === "function")) {
				var actionFunctionReference = this.makeFunctionReference(dropDownConfiguration.action);
				dropDownConfiguration.action = actionFunctionReference;
				if (!dropDownConfiguration.textMode) {
					dropDownConfiguration.textMode = false;
				}
				if (typeof(dropDownConfiguration.refresh) === "string") {
					if (typeof(this[dropDownConfiguration.refresh]) === "function") {
						var refreshFunctionReference = this.makeFunctionReference(dropDownConfiguration.refresh);
						dropDownConfiguration.refresh = refreshFunctionReference;
					} else {
						this.appendToLog("registerDropDown", "Function " + dropDownConfiguration.refresh + " was not defined when registering drop-down " + dropDownConfiguration.id);
						return false;
					}
				}
				return this.editorConfiguration.registerDropdown(dropDownConfiguration);
			} else {
				this.appendToLog("registerDropDown", "Function " + dropDownConfiguration.action + " was not defined when registering drop-down " + dropDownConfiguration.id);
			}
		}
		return false;
	},

	/**
	 * Returns the drop-down configuration
	 *
	 * @param	string		dropDownId: the unique id of the drop-down
	 *
	 * @return	object		the drop-down configuration object
	 */
	getDropDownConfiguration : function(dropDownId) {
		return this.editorConfiguration.customSelects[dropDownId];
	},

	/**
	 * Registors a hotkey
	 *
	 * @param	object		hotKeyConfiguration: the configuration object of the hotkey:
	 *					id		: the key
	 *					action		: name of the function invoked when a hotkey is pressed
	 *
	 * @return	boolean		true if the hotkey was successfully registered
	 */
	registerHotKey : function (hotKeyConfiguration) {
		if (typeof((hotKeyConfiguration.action) === "string") && (typeof(this[hotKeyConfiguration.action]) === "function")) {
			var actionFunctionReference = this.makeFunctionReference(hotKeyConfiguration.action);
			hotKeyConfiguration.action = actionFunctionReference;
			return this.editorConfiguration.registerHotKey(hotKeyConfiguration);
		} else {
			this.appendToLog("registerHotKey", "Function " + hotKeyConfiguration.action + " was not defined when registering hotkey " + hotKeyConfiguration.id);
			return false;
		}
	},

	/**
	 * Returns the buttonId corresponding to the hotkey, if any
	 *
	 * @param	string		key: the hotkey
	 *
	 * @return	string		the buttonId or ""
	 */
	translateHotKey : function(key) {
		if (typeof(this.editorConfiguration.hotKeyList[key]) !== "undefined") {
			var buttonId = this.editorConfiguration.hotKeyList[key].cmd;
			if (typeof(buttonId) !== "undefined") {
				return buttonId;
			} else {
				return "";
			}
		}
		return "";
	},

	/**
	 * Returns the hotkey configuration
	 *
	 * @param	string		key: the hotkey
	 *
	 * @return	object		the hotkey configuration object
	 */
	getHotKeyConfiguration : function(key) {
		if (typeof(this.editorConfiguration.hotKeyList[key]) !== "undefined") {
			return this.editorConfiguration.hotKeyList[key];
		} else {
			return null;
		}
	},

	/**
	 * The toolbar refresh handler of the plugin
	 * This function may be defined by the plugin subclass.
	 * If defined, the function will be invoked whenever the toolbar state is refreshed.
	 *
	 * @return	boolean
	 */
	onUpdateToolbar : null,

	/**
	 * The keyPress event handler
	 * This function may be defined by the plugin subclass.
	 * If defined, the function is invoked whenever a key is pressed.
	 *
	 * @param	event		keyEvent: the event that was triggered when a key was pressed
	 *
	 * @return	boolean
	 */
	onKeyPress : null,

	/**
	 * The hotKey event handler
	 * This function may be defined by the plugin subclass.
	 * If defined, the function is invoked whenever a hot key is pressed.
	 *
	 * @param	event		key: the hot key that was pressed
	 *
	 * @return	boolean
	 */
	onHotKey : null,

	/**
	 * The onMode event handler
	 * This function may be redefined by the plugin subclass.
	 * The function is invoked whenever the editor changes mode.
	 *
	 * @param	string		mode: "wysiwyg" or "textmode"
	 *
	 * @return	boolean
	 */
	onMode : function(mode) {
		if (mode === "textmode" && this.dialog && HTMLArea.Dialog[this.name] == this.dialog && !(this.dialog.buttonId && this.editorConfiguration.btnList[this.dialog.buttonId] && this.editorConfiguration.btnList[this.dialog.buttonId].textMode)) {
			this.dialog.close();
		}
	},

	/**
	 * The onGenerate event handler
	 * This function may be defined by the plugin subclass.
	 * If defined, the function is invoked when the editor is initialized
	 *
	 * @return	boolean
	 */
	onGenerate : null,

	/**
	 * Make function reference in order to avoid memory leakage in IE
	 *
	 * @param	string		functionName: the name of the plugin function to be invoked
	 *
	 * @return	function	function definition invoking the specified function of the plugin
	 */
	makeFunctionReference : function (functionName) {
		var self = this;
		return (function(arg1, arg2) {
			self[functionName](arg1, arg2);});
	},

	/**
	 * Localize a string
	 *
	 * @param	string		label: the name of the label to localize
	 *
	 * @return	string		the localization of the label
	 */
	localize : function (label) {
		return this.I18N[label] || HTMLArea.I18N.dialogs[label] || HTMLArea.I18N.tooltips[label];
	},

	/**
	 * Load a Javascript file synchronously
	 *
	 * @param	string		url: url of the file to load
	 *
	 * @return	boolean		true on success
	 */
	getJavascriptFile : function (url, noEval) {
		var script = HTMLArea._getScript(0, false, url);
		if (script) {
			if (noEval) {
				return script;
			} else {
				try {
					eval(script);
					return true;
				} catch(e) {
					this.appendToLog("getJavascriptFile", "Error evaluating contents of Javascript file: " + url);
					return false;
				}
			}
		} else {
			return false;
		}
	},

	/**
	 * Post data to the server
	 *
	 * @param	string		url: url to post data to
	 * @param	object		data: data to be posted
	 * @param	function	handler: function that will handle the response returned by the server
	 *
	 * @return	boolean		true on success
	 */
	 postData : function (url, data, handler) {
		 HTMLArea._postback(url, data, handler, this.editorConfiguration.RTEtsConfigParams, (this.editorConfiguration.typo3ContentCharset ? this.editorConfiguration.typo3ContentCharset : "utf-8"));
	 },

	/**
	 * Open a dialog window or bring focus to it if is already opened
	 *
	 * @param	string		buttonId: buttonId requesting the opening of the dialog
	 * @param	string		url: name, without extension, of the html file to be loaded into the dialog window
	 * @param	string		action: name of the plugin function to be invoked when the dialog ends
	 * @param	object		arguments: object of variable type to be passed to the dialog
	 * @param	object		dimensions: object giving the width and height of the dialog window
	 * @param	string		showScrollbars: specifies by "yes" or "no" whether or not the dialog window should have scrollbars
	 * @param	object		dialogOpener: reference to the opener window
	 *
	 * @return	object		the dialogue object
	 */
	openDialog : function (buttonId, url, action, arguments, dimensions, showScrollbars, dialogOpener) {
		if (this.dialog && this.dialog.hasOpenedWindow() && this.dialog.buttonId === buttonId) {
			this.dialog.focus();
			return this.dialog;
		} else {
			var actionFunctionReference = action;
			if (typeof(action) === "string") {
				if (typeof(this[action]) === "function") {
					var actionFunctionReference = this.makeFunctionReference(action);
				} else {
					this.appendToLog("openDialog", "Function " + action + " was not defined when opening dialog for " + buttonId);
				}
			}
			return new HTMLArea.Dialog(
					this,
					buttonId,
					url,
					actionFunctionReference,
					arguments,
					{width: ((dimensions && dimensions.width)?dimensions.width:100), height: ((dimensions && dimensions.height)?dimensions.height:100)},
					(showScrollbars?showScrollbars:"no"),
					dialogOpener
				);
		}
	},

	/**
	 * Make url from the name of a popup of the plugin
	 *
	 * @param	string		popupName: name, without extension, of the html file to be loaded into the dialog window
	 *
	 * @return	string		the url
	 */
	makeUrlFromPopupName : function(popupName) {
		return this.editor.popupURL("plugin://" + this.name + "/" + popupName);
	},

	/**
	 * Make url from module path
	 *
	 * @param	string		modulePath: module path
	 * @param	string		parameters: additional parameters
	 *
	 * @return	string		the url
	 */
	makeUrlFromModulePath : function(modulePath, parameters) {
		return this.editor.popupURL(modulePath + "?" + this.editorConfiguration.RTEtsConfigParams + "&editorNo=" + this.editorNumber + "&sys_language_content=" + this.editorConfiguration.sys_language_content + "&contentTypo3Language=" + this.editorConfiguration.typo3ContentLanguage + "&contentTypo3Charset=" + encodeURIComponent(this.editorConfiguration.typo3ContentCharset) + (parameters?parameters:''));
	},

	/**
	 * Append an entry at the end of the troubleshooting log
	 *
	 * @param	string		functionName: the name of the plugin function writing to the log
	 * @param	string		text: the text of the message
	 *
	 * @return	void
	 */
	appendToLog : function (functionName, text) {
		HTMLArea._appendToLog("[" + this.name + "::" + functionName + "]: " + text);
	}
});

/**
 * HTMLArea.Dialog class
 *
 * Every dialog should be an instance of this class
 *
 */
HTMLArea.Dialog = HTMLArea.Base.extend({

	/**
	 * HTMLArea.Dialog constructor
	 *
	 * @param	object		plugin: reference to the invoking plugin
	 * @param	string		buttonId: buttonId triggering the opening of the dialog
	 * @param	string		url: url of the html document to load into the dialog window
	 * @param	function	action: function to be executed when the the dialog ends
	 * @param	object		arguments: object of variable type to be passed to the dialog
	 * @param	object		dimensions: object giving the width and height of the dialog window
	 * @param	string		showScrollbars: specifies by "yes" or "no" whether or not the dialog window should have scrollbars
	 * @param	object		dialogOpener: reference to the opener window
	 *
	 * @return	boolean		true if the dialog window was opened
	 */
	constructor : function (plugin, buttonId, url, action, arguments, dimensions, showScrollbars, dialogOpener) {
		this.window = window.window ? window.window : window.self;
		this.plugin = plugin;
		this.buttonId = buttonId;
		this.action = action;
		if (typeof(arguments) !== "undefined") {
			this.arguments = arguments;
		}
		this.plugin.dialog = this;

		if (HTMLArea.Dialog[this.plugin.name] && HTMLArea.Dialog[this.plugin.name].hasOpenedWindow() && HTMLArea.Dialog[this.plugin.name].plugin != this.plugin) {
			HTMLArea.Dialog[this.plugin.name].close();
		}
		HTMLArea.Dialog[this.plugin.name] = this;
		this.dialogWindow = window.open(url, this.plugin.name + "Dialog", "toolbar=no,location=no,directories=no,menubar=no,resizable=yes,top=100,left=100,dependent=yes,dialog=yes,chrome=no,width=" + dimensions.width + ",height=" + dimensions.height + ",scrollbars=" + showScrollbars);

		if (!this.dialogWindow) {
			this.plugin.appendToLog("openDialog", "Dialog window could not be opened with url " + url);
			return false;
		}

		if (typeof(dialogOpener) !== "undefined") {
			this.dialogWindow.opener = dialogOpener;
			this.dialogWindow.opener.openedDialog = this;
		}
		if (!this.dialogWindow.opener) {
			this.dialogWindow.opener = this.window;
		}

		if (!url) this.createForm();
		return true;
	},

	/**
	 * Creates the document and the dialogue form of the dialogue window
	 *
	 * @return	void
	 */
	createForm : function () {

		this.document = this.dialogWindow.document;
		this.editor = this.plugin.editor;

		if (HTMLArea.is_ie) {
			this.document.open();
			var html = "<html><head></head><body></body></html>\n";
			this.document.write(html);
			this.document.close();
			this.loadStyle();
		}
		var html = this.document.documentElement;
		html.className = "popupwin";
		var head = this.document.getElementsByTagName("head")[0];
		if (!head) {
			var head = this.document.createElement("head");
			html.appendChild(head);
		}
		var title = this.document.getElementsByTagName("title")[0];
		if (!title) {
			var title = this.document.createElement("title");
			head.appendChild(title);
		}
		this.document.title = this.arguments.title;
		var body = this.document.body;
		if (!body) {
			var body = this.document.createElement("body");
		}
		body.className = "popupwin dialog";
		body.id = "--HA-body";
		var content = this.document.createElement("div");
		content.className = "content";
		content.id = "content";
		this.content = content;
		body.appendChild(content);
		if (HTMLArea.is_gecko) {
			html.appendChild(body);
		}
			// Create the form
			// Localize, resize and initiate capture of events
			// Catch errors for IE loosing control in case the window is closed while being initialized
		if (HTMLArea.is_ie) {
			try {
				this.arguments.initialize(this);
				this.initialize(false, false, HTMLArea.is_ie);
				this.focus();
			} catch(e) { }
		} else {
			this.arguments.initialize(this);
			this.initialize(false, false, HTMLArea.is_ie);
			this.focus();
		}
	},

	/**
	 * Adds OK and Cancel buttons to the dialogue window
	 *
	 * @return	void
	 */
	addButtons : function() {
		var self = this;
		var div = this.document.createElement("div");
		this.content.appendChild(div);
		div.className = "buttons";
		for (var i = 0; i < arguments.length; ++i) {
			var btn = arguments[i];
			var button = this.document.createElement("button");
			div.appendChild(button);
			switch (btn) {
				case "ok":
					button.innerHTML = this.plugin.localize("OK");
					button.onclick = function() {
						try {
							self.callFormInputHandler();
						} catch(e) { };
						return false;
					};
					break;
				case "cancel":
					button.innerHTML = this.plugin.localize("Cancel");
					button.onclick = function() {
						self.close();
						return false;
					};
					break;
			}
		}
	},

	/**
	 * Call the form input handler
	 *
	 * @return	boolean		false
	 */
	callFormInputHandler : function() {
		var tags = ["input", "textarea", "select"];
		var params = new Object();
		for (var ti = tags.length; --ti >= 0;) {
			var tag = tags[ti];
			var els = this.content.getElementsByTagName(tag);
			for (var j = 0; j < els.length; ++j) {
				var el = els[j];
				var val = el.value;
				if (el.nodeName.toLowerCase() == "input") {
					if (el.type == "checkbox") {
						val = el.checked;
					}
				}
				params[el.name] = val;
			}
		}
		this.action(this, params);
		return false;
	},

	/**
	 * Cheks if the dialogue has an open dialogue window
	 *
	 * @return	boolean		true if the dialogue has an open window
	 */
	hasOpenedWindow : function () {
		return this.dialogWindow && !this.dialogWindow.closed;
	},

	/**
	 * Initialize the dialog window: load the stylesheets, localize labels, resize if required, etc.
	 * This function MUST be invoked from the dialog window in the onLoad event handler
	 *
	 * @param	boolean		noResize: if true the window in not resized, but may be centered
	 *
	 * @return	void
	 */
	initialize : function (noLocalize, noResize, noStyle) {
		this.dialogWindow.HTMLArea = HTMLArea;
		this.dialogWindow.dialog = this;
			// Capture unload and escape events
		this.captureEvents();
			// Get stylesheets for the dialog window
		if (!noStyle) this.loadStyle();
			// Localize the labels of the popup window
		if (!noLocalize) this.localize();
			// Resize the dialog window to its contents
		if (!noResize) this.resize(noResize);
	},

	/**
	 * Load the stylesheets in the dialog window
	 *
	 * @return	void
	 */
	loadStyle : function () {
		var head = this.dialogWindow.document.getElementsByTagName("head")[0];
		var link = this.dialogWindow.document.createElement("link");
		link.rel = "stylesheet";
		link.type = "text/css";
		link.href = HTMLArea.editorCSS;
		if (link.href.indexOf("http") == -1 && HTMLArea.is_gecko) link.href = _typo3_host_url + link.href;
		head.appendChild(link);
	},

	/**
	 * Localize the labels contained in the dialog window
	 *
	 * @return	void
	 */
	localize : function () {
		var label;
		var types = ["input", "label", "option", "select", "legend", "span", "td", "button", "div", "h1", "h2", "a"];
		for (var type = 0; type < types.length; ++type) {
			var elements = this.dialogWindow.document.getElementsByTagName(types[type]);
			for (var i = elements.length; --i >= 0;) {
				var element = elements[i];
				if (element.firstChild && element.firstChild.data) {
					label = this.plugin.localize(element.firstChild.data);
					if (label) element.firstChild.data = label;
				}
				if (element.title) {
					label = this.plugin.localize(element.title);
					if (label) element.title = label;
				}
					// resetting the selected option for Mozilla
				if (types[type] == "option" && element.selected ) {
					element.selected = false;
					element.selected = true;
				}
			}
		}
		label = this.plugin.localize(this.dialogWindow.document.title);
		if (label) this.dialogWindow.document.title = label;
	},

	/**
	 * Resize the dialog window to its contents
	 *
	 * @param	boolean		noResize: if true the window in not resized, but may be centered
	 *
	 * @return	void
	 */
	resize : function (noResize) {
			// Resize if allowed
		var dialogWindow = this.dialogWindow;
		var doc = dialogWindow.document;
		var content = doc.getElementById("content");
			// As of Google Chrome build 1798, window resizeTo and resizeBy are completely erratic: do nothing
		if ((HTMLArea.is_gecko && !HTMLArea.is_opera && !HTMLArea.is_safari) || ((HTMLArea.is_ie || HTMLArea.is_opera || (HTMLArea.is_safari && !HTMLArea.is_chrome)) && content)) {
			var self = this;
			setTimeout( function() {
				if (!noResize) {
					if (content) {
						self.resizeToContent(content);
					} else if (dialogWindow.sizeToContent) {
						dialogWindow.sizeToContent();
					}
				}
				self.centerOnParent();
			}, 25);
		} else if (!noResize) {
			var body = doc.body;
			if (HTMLArea.is_ie) {
				var innerX = (doc.documentElement && doc.documentElement.clientWidth) ? doc.documentElement.clientWidth : body.clientWidth;
				var innerY = (doc.documentElement && doc.documentElement.clientHeight) ? doc.documentElement.clientHeight : body.clientHeight;
				var pageY = Math.max(body.scrollHeight, body.offsetHeight);
				if (innerY == pageY) {
					dialogWindow.resizeTo(body.scrollWidth, body.scrollHeight + 80);
				} else {
					dialogWindow.resizeBy((innerX < body.scrollWidth) ? (Math.max(body.scrollWidth, body.offsetWidth) - innerX) : 0, (body.scrollHeight - body.offsetHeight));
				}
				// As of Google Chrome build 1798, window resizeTo and resizeBy are completely erratic: do nothing
			} else if ((HTMLArea.is_safari && !HTMLArea.is_chrome) || HTMLArea.is_opera) {
				dialogWindow.resizeTo(dialogWindow.innerWidth, body.offsetHeight + 10);
				if (dialogWindow.innerHeight < body.scrollHeight) {
					dialogWindow.resizeBy(0, (body.scrollHeight - dialogWindow.innerHeight) + 10);
				}
			}
			this.centerOnParent();
		} else {
			this.centerOnParent();
		}
	},

	/**
	 * Resize the Opera dialog window to its contents, based on size of content div
	 *
	 * @param	object		content: reference to the div (may also be form) section containing the contents of the dialog window
	 *
	 * @return	void
	 */
	resizeToContent : function(content) {
		var dialogWindow = this.dialogWindow;
		var doc = dialogWindow.document;
		var docElement = doc.documentElement;
		var body = doc.body;
		var width = 0, height = 0;

		var contentWidth = content.offsetWidth;
		var contentHeight = content.offsetHeight;
		dialogWindow.resizeTo( contentWidth + 200, contentHeight + 200 );

		if (dialogWindow.innerWidth) {
			width = dialogWindow.innerWidth;
			height = dialogWindow.innerHeight;
		} else if (docElement && docElement.clientWidth) {
			width = docElement.clientWidth;
			height = docElement.clientHeight;
		} else if (body && body.clientWidth) {
			width = body.clientWidth;
			height = body.clientHeight;
		}
		dialogWindow.resizeTo( contentWidth + ( ( contentWidth + 200 ) - width ), contentHeight + ( (contentHeight + 200 ) - (height - 16) ) );
	},

	/**
	 * Center the dialogue window on the parent window
	 *
	 * @return	void
	 */
	centerOnParent : function () {
		var dialogWindow = this.dialogWindow;
		var doc = dialogWindow.document;
		var body = doc.body;
			// Center on parent if allowed
		if (HTMLArea.is_gecko) {
			var x = dialogWindow.opener.screenX + (dialogWindow.opener.outerWidth - dialogWindow.outerWidth) / 2;
			var y = dialogWindow.opener.screenY + (dialogWindow.opener.outerHeight - dialogWindow.outerHeight) / 2;
		} else {
			var W = body.offsetWidth;
			var H = body.offsetHeight;
			var x = (screen.availWidth - W) / 2;
			var y = (screen.availHeight - H) / 2;
		}
			// As of build 1798, Google Chrome moveTo breaks the window dimensions: do nothing
		if (!HTMLArea.is_chrome) {
			try {
				dialogWindow.moveTo(x, y);
			} catch(e) { }
		}
	},

	/**
	 * Perform the action function when the dialog end
	 *
	 * @return	void
	 */
	performAction : function (val) {
		if (val && this.action) {
			this.action(val);
		}
	},

	/**
	 * Bring the focus on the dialog window
	 *
	 * @return	void
	 */
	focus : function () {
		this.dialogWindow.focus();
	},

	/**
	 * Recover focus from the parent window
	 *
	 * @return	void
	 */
	recoverFocus : function(ev) {
		if (this.dialogWindow && !this.dialogWindow.closed) {
			if (!ev) var ev = window.event;
			HTMLArea._stopEvent(ev);
			this.focus();
		}
		return false;
	},

	/**
	 * Close the dialog window
	 *
	 * @return	void
	 */
	close : function () {
		if (this.dialogWindow) {
			try {
				if (this.dialogWindow.openedDialog) {
					this.dialogWindow.openedDialog.close();
				}
			} catch(e) { }
			this.releaseEvents();
			HTMLArea.Dialog[this.plugin.name] = null;
			if (!this.dialogWindow.closed) {
				this.dialogWindow.dialog = null;
				if (HTMLArea.is_safari || HTMLArea.is_ie) {
					this.dialogWindow.blur();
				}
				this.dialogWindow.close();
					// Safari 3.1.2 does not set the closed flag
				if (!this.dialogWindow.closed) {
					this.dialogWindow = null;
				}
			}
				// Opera unload event may be triggered after the editor iframe is gone
			if (this.plugin.editor._iframe) {
				this.plugin.editor.updateToolbar();
			}
		}
		return false;
	},

	/**
	 * Make function reference in order to avoid memory leakage in IE
	 *
	 * @param	string		functionName: the name of the dialog function to be invoked
	 *
	 * @return	function	function definition invoking the specified function of the dialog
	 */
	makeFunctionReference : function (functionName) {
		var self = this;
		return (function(arg1, arg2) {
			self[functionName](arg1, arg2);});
	},

	/**
	 * Escape event handler
	 *
	 * @param	object		ev: the event
	 *
	 * @return	boolean		false if the event was handled
	 */
	closeOnEscape : function(ev) {
		if (!ev) var ev = window.event;
		if (ev.keyCode == 27) {
			if (HTMLArea.is_gecko) {
				var parentWindow = ev.currentTarget.defaultView;
			} else {
				var parentWindow = ev.srcElement.parentNode.parentNode.parentWindow;
			}
			if (parentWindow && parentWindow.dialog) {
				parentWindow.dialog.close();
				return false;
			}
		}
		return true;
	},

	/**
	 * Capture unload, escape and focus events
	 *
	 * @return	void
	 */
	captureEvents : function (skipUnload) {
			// Capture unload events on the dialogue window, the opener window and the editor frame
		this.unloadFunctionReference = this.makeFunctionReference("close");
		HTMLArea._addEvent(this.dialogWindow.opener, "unload", this.unloadFunctionReference);
		if (HTMLArea.is_gecko && this.plugin.editor._iframe.contentWindow) {
			HTMLArea._addEvent(this.plugin.editor._iframe.contentWindow, "unload", this.unloadFunctionReference);
		}
		if (!skipUnload) HTMLArea._addEvent(this.dialogWindow, "unload", this.unloadFunctionReference);
			// Capture escape key on the dialogue window
		this.escapeFunctionReference = this.makeFunctionReference("closeOnEscape");
		HTMLArea._addEvent(this.dialogWindow.document, "keypress", this.escapeFunctionReference);
			// Capture focus events on the opener window and its frames
		if (HTMLArea.is_gecko) {
			this.recoverFocusFunctionReference = this.makeFunctionReference("recoverFocus");
			this.captureFocus(this.dialogWindow.opener);
		}
	 },

	/**
	 * Capture focus events
	 *
	 * @return	void
	 */
	captureFocus : function (w) {
		if (HTMLArea.is_gecko) {
			w.addEventListener("focus", this.recoverFocusFunctionReference, true);
		} else {
			HTMLArea._addEvent(w, "focus", this.recoverFocusFunctionReference);
		}
		for (var i = w.frames.length; --i >= 0;) {
			this.captureFocus(w.frames[i]);
		}
	},

	/**
	 * Release all event handlers that were set when the dialogue window was opened
	 *
	 * @return	void
	 */
	releaseEvents : function() {
		if (this.dialogWindow) {
			HTMLArea._removeEvent(this.dialogWindow, "unload", this.unloadFunctionReference);
			try {
				if (this.dialogWindow.document) {
					HTMLArea._removeEvent(this.dialogWindow.document, "keypress", this.escapeFunctionReference);
				}
			} catch(e) { }
			try {
				if (this.dialogWindow.opener && !this.dialogWindow.opener.closed) {
					HTMLArea._removeEvent(this.dialogWindow.opener, "unload", this.unloadFunctionReference);
					if (HTMLArea.is_gecko) {
						this.releaseFocus(this.dialogWindow.opener);
					}
				}
			} catch(e) { }
		}
		if ((HTMLArea.is_gecko && !HTMLArea.is_opera) && this.plugin.editor._iframe.contentWindow) {
			HTMLArea._removeEvent(this.plugin.editor._iframe.contentWindow, "unload", this.unloadFunctionReference);
		}
	},

	/**
	 * Release focus capturing events that were set when the dialogue window was opened
	 *
	 * @return	void
	 */
	releaseFocus : function(w) {
		HTMLArea._removeEvent(w, "focus", this.recoverFocusFunctionReference);
		for (var i = w.frames.length; --i >= 0;) {
			this.releaseFocus(w.frames[i]);
		}
	}
});
};

