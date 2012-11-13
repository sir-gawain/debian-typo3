// IIFE for faster access to $ and save $ use
(function ($) {

	$(document).ready(function() {
		manageExtensionListing();
		$('th[title]').tooltip({offset: [-10, -30], position: 'bottom right', tipClass: 'headerTooltip'});
		$('td[title]').tooltip({offset: [-10, -60], position: 'bottom right'});
		$("#typo3-extension-configuration-forms ul").tabs("div.category");

		$('#resetSearch').live('click', function (e) {
			datatable.fnFilter('');
		});

		resetSearchField();
	});

	function getUrlVars() {
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	}

	function manageExtensionListing() {
		datatable = $('#typo3-extension-list').dataTable({
			"sPaginationType":"full_numbers",
			"bJQueryUI":true,
			"bLengthChange":false,
			'iDisplayLength':15,
			"bStateSave":true,
			"fnDrawCallback": bindActions
		});

		var getVars = getUrlVars();

		// restore filter
		if(datatable.length && getVars['search']) {
			datatable.fnFilter(getVars['search']);
		}
	}

	function bindActions() {
		$('td[title], tr[title]').tooltip({offset: [-10, -60], position: 'bottom right'});
		$('.removeExtension').not('.transformed').each(function() {
			$(this).data('href', $(this).attr('href'));
			$(this).attr('href', '#');
			$(this).addClass('transformed');
			$(this).click(function() {
				if ($(this).hasClass('isLoadedWarning')) {
					TYPO3.Dialog.QuestionDialog({
						title: TYPO3.l10n.localize('extensionList.removalConfirmation.title'),
						msg: TYPO3.l10n.localize('extensionList.removalConfirmation.message'),
						url: $(this).data('href'),
						fn: function(button, dummy, dialog) {
							if (button == 'yes') {
								confirmDeletionAndDelete(dialog.url);
							}
						}
					});
				} else {
					confirmDeletionAndDelete($(this).data('href'));
				}
			});
		});

		$('.t3-icon-system-extension-update').parent().each(function() {
			$(this).data('href', $(this).attr('href'));
			$(this).attr('href', '#');
			$(this).addClass('transformed');
			$(this).click(function() {
				$('.typo3-extension-manager').mask();
				$.ajax({
					url: $(this).data('href'),
					dataType: 'json',
					success: updateExtension
				});
			});
		});

	}

	function updateExtension(data) {
		var message = '<h1>' + TYPO3.l10n.localize('extensionList.updateConfirmation.title') + '</h1>';
		message += '<h2>' + TYPO3.l10n.localize('extensionList.updateConfirmation.message') + '</h2>';
		$.each(data.updateComments, function(version, comment) {
			message += '<h3>' + version + '</h3>';
			message += '<div>' + comment + '</div>';
		});

		TYPO3.Dialog.QuestionDialog({
			title: TYPO3.l10n.localize('extensionList.updateConfirmation.questionVersionComments'),
			msg: message,
			width: 600,
			url: data.url,
			fn: function(button, dummy, dialog) {
				if (button == 'yes') {
					$.ajax({
						url: dialog.url,
						dataType: 'json',
						success: function(data) {
							$('.typo3-extension-manager').unmask();
							TYPO3.Flashmessage.display(TYPO3.Severity.information, TYPO3.l10n.localize('extensionList.updateFlashMessage.title'),
									TYPO3.l10n.localize('extensionList.updateFlashMessage.message').replace(/\{0\}/g, data.extension), 15);
						}
					});
				} else {
					$('.typo3-extension-manager').unmask();
				}
			}
		});
	}


	function confirmDeletionAndDelete(url) {
		TYPO3.Dialog.QuestionDialog({
			title: TYPO3.l10n.localize('extensionList.removalConfirmation.title'),
			msg: TYPO3.l10n.localize('extensionList.removalConfirmation.question'),
			url: url,
			fn: function(button, dummy, dialog) {
				if (button == 'yes') {
					$('.typo3-extension-manager').mask();
					$.ajax({
						url: dialog.url,
						dataType: 'json',
						success: removeExtension
					});
				}
			}
		});
	}

	function removeExtension(data) {
		$('.typo3-extension-manager').unmask();
		if (data.success) {
			datatable.fnDeleteRow(datatable.fnGetPosition(document.getElementById(data.extension)));
		} else {
			TYPO3.Flashmessage.display(TYPO3.Severity.error, TYPO3.l10n.localize('extensionList.removalConfirmation.title'), data.message, 15);
		}
	}

	function resetSearchField() {
		var dataTablesFilter = find(".dataTables_filter");
		$('.dataTables_wrapper').find('.dataTables_filter').append($('<span />', {
			'class':'t3-icon t3-icon-actions t3-icon-actions-input t3-icon-input-clear t3-tceforms-input-clearer',
			'id':'resetSearch',
			'style':'display:none'
		}));
		$('#typo3-extension-list_filter').mouseout(function() {
			$(this).find('#resetSearch').css('display', 'none');
		});
		$('#typo3-extension-list_filter').mouseover(function() {
			if ($(this).find('input').val()) {
				$(this).find('#resetSearch').css('display', 'inline-block');
			}
		});
	}
}(jQuery));
