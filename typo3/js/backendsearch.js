/***************************************************************
*  Copyright notice
*
*  (c) 2007 Ingo Renner <ingo@typo3.org>
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
 * class to handle the backend search
 *
 * $Id$
 */
var BackendSearch = Class.create({

	/**
	 * registers for resize event listener and executes on DOM ready
	 */
	initialize: function() {
		Event.observe(window, 'resize', this.positionMenu);

		Event.observe(window, 'load', function(){
			this.positionMenu();
			this.toolbarItemIcon = $$('#backend-search-menu .toolbar-item img')[0].src;

			$('search-query').observe('keypress', function(event) {
				var keyCode;

				if(!event) {
					var event = window.event;
				}

				if(event.keyCode) {
					keyCode = event.keyCode;
				} else if(event.which) {
					keyCode = event.which;
				}

				if(keyCode == Event.KEY_RETURN) {
					this.invokeSearch();
				}
			}.bindAsEventListener(this));

			$$('#backend-search-menu .toolbar-item')[0].observe('click', this.toggleMenu)
		}.bindAsEventListener(this));
	},

	/**
	 * positions the menu below the toolbar icon, let's do some math!
	 */
	positionMenu: function() {
		var calculatedOffset = 0;
		var parentWidth      = $('backend-search-menu').getWidth();
		var ownWidth         = $$('#backend-search-menu div')[0].getWidth();
		var parentSiblings   = $('backend-search-menu').previousSiblings();

		parentSiblings.each(function(toolbarItem) {
			calculatedOffset += toolbarItem.getWidth() - 1;
			// -1 to compensate for the margin-right -1px of the list items,
			// which itself is necessary for overlaying the separator with the active state background

			if(toolbarItem.down().hasClassName('no-separator')) {
				calculatedOffset -= 1;
			}
		});
		calculatedOffset = calculatedOffset - ownWidth + parentWidth;


		$$('#backend-search-menu div')[0].setStyle({
			left: calculatedOffset + 'px'
		});
	},

	/**
	 * toggles the visibility of the menu and places it under the toolbar icon
	 */
	toggleMenu: function(event) {
		var toolbarItem = $$('#backend-search-menu > a')[0];
		var menu        = $$('#backend-search-menu .toolbar-item-menu')[0];
		toolbarItem.blur();

		if(!toolbarItem.hasClassName('toolbar-item-active')) {
			toolbarItem.addClassName('toolbar-item-active');
			Effect.Appear(menu, {duration: 0.2});
			TYPO3BackendToolbarManager.hideOthers(toolbarItem);

			setTimeout(function() {
				$('search-query').activate();
			}, 200);
		} else {
			toolbarItem.removeClassName('toolbar-item-active');
			Effect.Fade(menu, {duration: 0.1});

			setTimeout(function() {
				$('search-query').clear();
			}, 100);
		}

		if (event) {
			Event.stop(event);
		}
	},

	/**
	 * calls the actual clear cache URL using an asynchronious HTTP request
	 */
	invokeSearch: function() {
		new Ajax.Request('alt_shortcut.php?ajax=1&editPage=' + top.rawurlencodeAndRemoveSiteUrl($F('search-query')), {
			method: 'get',
			requestHeaders: {Accept: 'application/json'},
			onSuccess: function(transport) {
				var jsonResponse = transport.responseText.evalJSON(true);

				switch(jsonResponse.type) {
					case 'page':
						top.loadEditId(jsonResponse.editRecord);
						break;
					case 'alternative':
						top.content.window.location.href = 'alt_doc.php?returnUrl=dummy.php&edit[' + jsonResponse.alternativeTable + '][' + jsonResponse.alternativeUid + ']=edit'
						break;
					case 'search':
						this.jump(
							unescape('db_list.php?id=' + jsonResponse.firstMountPoint + '&search_field=' + jsonResponse.searchFor + '&search_levels=4'),
							'web_list',
							'web'
						);
						break;
				}
			}.bind(this)
		});

		$('search-query').clear();
		this.toggleMenu();
	},

	/**
	 * jumps to a given URL in the content iframe, taken from alt_shortcut.php
	 *
	 * @param	string		the URL to jump to
	 * @param	string		module name
	 * @param	string		main module name
	 */
	jump: function(url, modName, mainModName) {
			// Clear information about which entry in nav. tree that might have been highlighted.
		top.fsMod.navFrameHighlightedID = new Array();
		if(top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav) {
			top.content.nav_frame.refresh_nav();
		}

		top.nextLoadModuleUrl = url;
		top.goToModule(modName);
	}

});

var TYPO3BackendSearchMenu = new BackendSearch();
