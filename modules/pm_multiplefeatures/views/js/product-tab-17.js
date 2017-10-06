var pm_featureItemsCount = 0;
function pmTransformSelect() {
	var pm_featureSelectList = $('div#features-content select[id^="form_step1_features_"][id$="_value"]:not(:disabled)');
	var pm_featureNewItemsCount = pm_featureSelectList.size();
	if (pm_featureNewItemsCount != pm_featureItemsCount) {
		// Process new features
		pm_featureItemsCount = pm_featureNewItemsCount;
		prototypeHtml = $('<div />').html($('div.feature-collection').attr('data-prototype'));
		prototypeSelect = $('select.feature-selector', prototypeHtml);
		$('option', prototypeHtml).prop('disabled', false);
		if (pm_featureSelectList.size() == 0) {
			$('div.feature-collection').attr('data-prototype', prototypeHtml.html());
		}
	} else {
		// Hide opened "select2" items
		if (pm_featureNewItemsCount > 0) {
			$('div#features-content select[multiple].select2-hidden-accessible').each(function() {
				$(this).next('.select2-container:visible').hide();
			});
		}
		return;
	}

	// At least one feature to process
	if (pm_featureSelectList.size() > 0) {
		pm_featureSelectList.each(function() {
			$select = $(this);
			$select.attr('multiple', 'multiple').prop('multiple', true);

			doTransformSelect = false;
			if ($select.data('pmTransformSelectDone') != true) {
				doTransformSelect = true;
			}
			$select.data('pmTransformSelectDone', true);

			idFeatureElementSelector = '#' + $select.attr('id').replace('_value', '_feature');
			idFeatureElement = $(idFeatureElementSelector);
			id_feature = idFeatureElement.val();
			featureName = idFeatureElement.find('option[value='+ id_feature +']').text();

			if (id_feature > 0) {
				idFeatureElement.parent().parent().addClass('hide').hide();
				// Disable already used features
				prototypeSelect.find('option[value=' + id_feature + ']').prop('disabled', true);
			}

			// Hide all select2
			$select.parent().find('.select2-container').addClass('hide').hide();

			// Edit label name
			if (doTransformSelect) {
				// Disable id_feature <select> to avoid undefined value exception
				idFeatureElement.prop('disabled', true);

				labelElement = $select.parent().find('label:eq(0)');
				labelElement.html('<h2>' + featureName + '</h2><p>' + labelElement.html() + '</p>');

				// Edit select name
				$select.attr('name', 'pm_multiplefeatures_feature_' + id_feature + '_value[]');

				// Change container classes
				$select.parent().parent().attr('class', 'col-xs-12');

				// Rename custom label input
				$select.parent().parent().parent().find('input[id*="custom_value"]').each(function() {
					idSplit = $(this).attr('id').split('_');
					idLang = idSplit[idSplit.length-1];
					$(this).attr('name', 'pm_multiplefeatures_feature_' + id_feature + '_custom_value_' + idLang);
				});

				// Remove feature = 0 or undefined values (when there is no translation)
				$('option[value="0"], option[value=""]', $select).remove();

				// Set selected to option, and reorder them (add to the end for each selected feature)
				if (typeof(pm_FeatureList[id_feature]) != 'undefined' && pm_FeatureList[id_feature].length > 0) {
					for (var key in pm_FeatureList[id_feature]) {
						if ($('option', $select).size() > 1) {
							$('option[value="' + pm_FeatureList[id_feature][key] + '"]', $select)
							.attr('selected', 'selected')
							.prop('selected', true)
							.detach()
							.insertAfter($('option:last-child', $select));
						} else {
							$('option[value="' + pm_FeatureList[id_feature][key] + '"]', $select)
							.attr('selected', 'selected')
							.prop('selected', true);
						}
					}
				}
			}
		});
		$('div.feature-collection').attr('data-prototype', prototypeHtml.html());

		pm_featureSelectList.pmConnectedList({
			availableListTitle: pm_FeatureAvailableListTitle,
			availableListSearchTitle: pm_FeatureAvailableListSearchTitle,
			searchInputPlaceHolder: pm_FeatureSearchInputPlaceHolder,
			selectedListTitle: pm_FeatureSelectedListTitle,
			addAllButtonLabel: '<i class="material-icons">add</i> ' + pm_FeatureAddAllButtonLabel,
			removeAllButtonLabel: '<i class="material-icons">delete</i> ' + pm_FeatureRemoveAllButtonLabel,
			addAllButtonClasses: 'btn btn-success',
			removeAllButtonClasses: 'btn btn-danger',
			removeAllCallback: function($selectSource) {
				$selectSource.parent().parent().parent().remove();
			},
		});
	}
}

$(document).ready(function() {
	// Init select
	if (typeof(pm_FeatureList) != 'undefined') {
		for (var id_feature in pm_FeatureList) {
			if (pm_FeatureList[id_feature].length > 1) {
				// We have to remove extra features group entries
				var foundOne = false;
				$('div#features-content select.feature-selector').each(function() {
					if ($(this).val() == id_feature) {
						if (!foundOne) {
							foundOne = true;
						} else {
							$(this).parent().parent().parent().remove();
						}
					}
				});
			}
		}
	}
	// Then, watch for changes
	setInterval('pmTransformSelect();', 250);
});