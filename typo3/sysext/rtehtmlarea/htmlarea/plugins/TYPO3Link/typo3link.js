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
 * TYPO3Link plugin for htmlArea RTE
 *
 * TYPO3 SVN ID: $Id$
 */
TYPO3Link = HTMLArea.Plugin.extend({
	
	constructor : function(editor, pluginName) {
		this.base(editor, pluginName);
	},
	
	/*
	 * This function gets called by the class constructor
	 */
	configurePlugin : function(editor) {
		
		this.pageTSConfiguration = this.editorConfiguration.buttons.link;
		this.modulePath = this.pageTSConfiguration.pathLinkModule;
		this.classesAnchorUrl = this.pageTSConfiguration.classesAnchorUrl;
		
		/*
		 * Registering plugin "About" information
		 */
		var pluginInformation = {
			version		: "1.1",
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
		var buttonList = this.buttonList, buttonId;
		for (var i = 0; i < buttonList.length; ++i) {
			var button = buttonList[i];
			buttonId = button[0];
			var buttonConfiguration = {
				id		: buttonId,
				tooltip		: this.localize(buttonId.toLowerCase()),
				action		: "onButtonPress",
				hotKey		: (this.pageTSConfiguration ? this.pageTSConfiguration.hotKey : null),
				context		: button[1],
				selection	: button[2],
				dialog		: button[3]
			};
			this.registerButton(buttonConfiguration);
		}
		
		return true;
	 },
	 
	/*
	 * The list of buttons added by this plugin
	 */
	buttonList : [
		["CreateLink", "a,img", true, true],
		["UnLink", "a", false, false]
	],
	 
	/*
	 * This function gets called when the button was pressed
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
		
			// Download the definition of special anchor classes if not yet done
		if (this.classesAnchorUrl && (typeof(HTMLArea.classesAnchorSetup) === "undefined")) {
			this.getJavascriptFile(this.classesAnchorUrl);
		}
		
		if (buttonId === "UnLink") {
			this.unLink();
			return false;
		}
		
		var additionalParameter;
		var node = this.editor.getParentElement();
		var el = HTMLArea.getElementObject(node, "a");
		if (el != null && /^a$/i.test(el.nodeName)) node = el;
		if (node != null && /^a$/i.test(node.nodeName)) {
			additionalParameter = "&curUrl[href]=" + encodeURIComponent(node.getAttribute("href"));
			if (node.target) additionalParameter += "&curUrl[target]=" + encodeURIComponent(node.target);
			if (node.className) additionalParameter += "&curUrl[class]=" + encodeURIComponent(node.className);
			if (node.title) additionalParameter += "&curUrl[title]=" + encodeURIComponent(node.title);
		} else if (this.editor.hasSelectedText()) {
			var text = this.editor.getSelectedHTML();
			if (text && text != null) {
				var offset = text.toLowerCase().indexOf("<a");
				if (offset!=-1) {
					var ATagContent = text.substring(offset+2);
					offset = ATagContent.toUpperCase().indexOf(">");
					ATagContent = ATagContent.substring(0,offset);
					additionalParameter = "&curUrl[all]=" + encodeURIComponent(ATagContent);
				}
			}
		}
		this.dialog = this.openDialog("CreateLink", this.makeUrlFromModulePath(this.modulePath, additionalParameter), null, null, {width:550, height:350}, "yes");
		return false;
	},
	
	/*
	 * Add a link to the selection.
	 * This function is called from the TYPO3 link popup.
	 */
	createLink : function(theLink,cur_target,cur_class,cur_title) {
		var selection, range, anchorClass, imageNode = null, addIconAfterLink;
		this.editor.focusEditor();
		var node = this.editor.getParentElement();
		var el = HTMLArea.getElementObject(node, "a");
		if (el != null && /^a$/i.test(el.nodeName)) node = el;
		if (node != null && /^a$/i.test(node.nodeName)) this.editor.selectNode(node);
			// Clean images from existing anchors otherwise Mozilla may create nested anchors
		if (HTMLArea.classesAnchorSetup) {
			selection = this.editor._getSelection();
			range = this.editor._createRange(selection);
			this.cleanAllLinks(node, range, true);
		}
			// In FF, if the url is the same except for upper/lower case of a file name, the link is not updated.
			// Therefore, we remove the link before creating a new one.
		if (HTMLArea.is_gecko && node != null && /^a$/i.test(node.nodeName)) {
				// If the class attribute is not removed, UnLink folowed by CreateLink will create a span element inside the new link
			node.removeAttribute("class");
			this.editor._doc.execCommand("UnLink", false, null);
		}
		if (HTMLArea.is_gecko && !HTMLArea.is_safari && !HTMLArea.is_opera) {
			this.editor._doc.execCommand("CreateLink", false, encodeURIComponent(theLink));
		} else {
			this.editor._doc.execCommand("CreateLink", false, theLink);
		}
		
		selection = this.editor._getSelection();
		range = this.editor._createRange(selection);
		node = this.editor.getParentElement();
		el = HTMLArea.getElementObject(node, "a");
		if (el != null && /^a$/i.test(el.nodeName)) node = el;
		if (node) {
			if (HTMLArea.classesAnchorSetup && cur_class) {
				for (var i = HTMLArea.classesAnchorSetup.length; --i >= 0;) {
					anchorClass = HTMLArea.classesAnchorSetup[i];
					if (anchorClass.name == cur_class && anchorClass.image) {
						imageNode = this.editor._doc.createElement("img");
						imageNode.src = anchorClass.image;
						imageNode.alt = anchorClass.altText;
						addIconAfterLink = anchorClass.addIconAfterLink;
						break;
					}
				}
			}
				// We may have created multiple links in as many blocks
			this.setLinkAttributes(node, range, cur_target, cur_class, cur_title, imageNode, addIconAfterLink);
		}
		this.dialog.close();
	},
	
	/*
	* Unlink the selection.
	* This function is called from the TYPO3 link popup and from the context menu.
	*/
	unLink : function() {
		this.editor.focusEditor();
		var node = this.editor.getParentElement();
		var el = HTMLArea.getElementObject(node, "a");
		if (el != null && /^a$/i.test(el.nodeName)) node = el;
		if (node != null && /^a$/i.test(node.nodeName)) this.editor.selectNode(node);
		if (HTMLArea.classesAnchorSetup) {
			var selection = this.editor._getSelection();
			var range = this.editor._createRange(selection);
			if (HTMLArea.is_gecko) {
				this.cleanAllLinks(node, range, false);
			} else {
				this.cleanAllLinks(node, range, true);
				this.editor._doc.execCommand("Unlink", false, "");
			}
		} else {
			this.editor._doc.execCommand("Unlink", false, "");
		}
		if (this.dialog) {
			this.dialog.close();
		}
	},
	
	/*
	* Set attributes of anchors intersecting a range in the given node
	*/
	setLinkAttributes : function(node, range, cur_target, cur_class, cur_title, imageNode, addIconAfterLink) {
		if (/^a$/i.test(node.nodeName)) {
			var nodeInRange = false;
			if (HTMLArea.is_gecko) {
				nodeInRange = this.editor.rangeIntersectsNode(range, node);
			} else {
				if (this.editor._getSelection().type.toLowerCase() == "control") {
						// we assume an image is selected
					nodeInRange = true;
				} else {
					var nodeRange = this.editor._doc.body.createTextRange();
					nodeRange.moveToElementText(node);
					nodeInRange = range.inRange(nodeRange) || (range.compareEndPoints("StartToStart", nodeRange) == 0) || (range.compareEndPoints("EndToEnd", nodeRange) == 0);
				}
			}
			if (nodeInRange) {
				if (imageNode != null) {
					if (addIconAfterLink) {
						node.appendChild(imageNode.cloneNode(false));
					} else {
						node.insertBefore(imageNode.cloneNode(false), node.firstChild);
					}
				}
				if (HTMLArea.is_gecko && !HTMLArea.is_safari && !HTMLArea.is_opera) {
					node.href = decodeURIComponent(node.href);
				}
				if (cur_target.trim()) node.target = cur_target.trim();
					else node.removeAttribute("target");
				if (cur_class.trim()) {
					node.className = cur_class.trim();
				} else { 
					if (HTMLArea.is_gecko) {
						node.removeAttribute('class');
					} else {
						node.removeAttribute('className');
					}
				}
				if (cur_title.trim()) {
					node.title = cur_title.trim();
				} else {
					node.removeAttribute("title");
					node.removeAttribute("rtekeep");
				}
			}
		} else {
			for (var i = node.firstChild;i;i = i.nextSibling) {
				if (i.nodeType == 1 || i.nodeType == 11) {
					this.setLinkAttributes(i, range, cur_target, cur_class, cur_title, imageNode, addIconAfterLink);
				}
			}
		}
	},
	
	/*
	 * Clean up images in special anchor classes
	 */
	cleanClassesAnchorImages : function(node) {
		var nodeArray = [], splitArray1 = [], splitArray2 = [];
		for (var childNode = node.firstChild; childNode; childNode = childNode.nextSibling) {
			if (/^img$/i.test(childNode.nodeName)) {
				splitArray1 = childNode.src.split("/");
				for (var i = HTMLArea.classesAnchorSetup.length; --i >= 0;) {
					if (HTMLArea.classesAnchorSetup[i]["image"]) {
						splitArray2 = HTMLArea.classesAnchorSetup[i]["image"].split("/");
						if (splitArray1[splitArray1.length-1] == splitArray2[splitArray2.length-1]) {
							nodeArray.push(childNode);
							break;
						}
					}
				}
			}
		}
		for (i = nodeArray.length; --i >= 0;) {
			node.removeChild(nodeArray[i]);
		}
	},
	
	/*
	 * Clean up all anchors intesecting with the range in the given node
	 */
	cleanAllLinks : function(node, range, keepLinks) {
		if (/^a$/i.test(node.nodeName)) {
			var intersection = false;
			if (HTMLArea.is_gecko) {
				intersection = this.editor.rangeIntersectsNode(range, node);
			} else {
				if (this.editor._getSelection().type.toLowerCase() == "control") {
						// we assume an image is selected
					intersection = true;
				} else {
					var nodeRange = this.editor._doc.body.createTextRange();
					nodeRange.moveToElementText(node);
					intersection = range.inRange(nodeRange) || ((range.compareEndPoints("StartToStart", nodeRange) > 0) && (range.compareEndPoints("StartToEnd", nodeRange) < 0)) || ((range.compareEndPoints("EndToStart", nodeRange) > 0) && (range.compareEndPoints("EndToEnd", nodeRange) < 0));
				}
			}
			if (intersection) {
				this.cleanClassesAnchorImages(node);
				if (!keepLinks) {
					while(node.firstChild) node.parentNode.insertBefore(node.firstChild, node);
					node.parentNode.removeChild(node);
				}
			}
		} else {
			for (var i = node.firstChild;i;i = i.nextSibling) {
				if (i.nodeType == 1 || i.nodeType == 11) this.cleanAllLinks(i, range, keepLinks);
			}
		}
	}
});

