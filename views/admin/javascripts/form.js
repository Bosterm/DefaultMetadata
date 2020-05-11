function activateWYSWYG() {
	var inputs = jQuery(document).find('div.inputs .use-html-checkbox');
	inputs.each(function () {
		var textarea = jQuery(this).parents('.input-block').find('textarea');
		if (textarea.length) {
			var textareaId = textarea.attr('id');
			var enableIfChecked = function () {
				if (this.checked) {
					tinyMCE.EditorManager.execCommand("mceAddEditor", true, textareaId);
				} else {
					tinyMCE.EditorManager.execCommand("mceRemoveEditor", true, textareaId);
				}
			};

			enableIfChecked.call(this);

			// Whenever the checkbox is toggled, toggle the WYSIWYG editor.
			jQuery(this).click(enableIfChecked);
		}
	});
}

jQuery( document ).ready(function() {
	Omeka.wysiwyg({
		selector: false,
        plugins: 'lists link code paste media autoresize image table charmap hr',
		forced_root_block : '',
        browser_spellcheck: true
    });
	activateWYSWYG();
	
	jQuery('#change_type').hide();
		var params = {
			type_id: jQuery('#item-type').val()
		};
		jQuery.ajax({
			url: changeItemTypeUrl,
			type: 'POST',
			dataType: 'html',
			data: params,
			success: function (response) {
				var form = jQuery('#type-metadata-form');
				form.hide();
				form.find('textarea').each(function () {
					tinyMCE.EditorManager.execCommand('mceRemoveEditor', true, this.id);
				});
				form.html(response);
				form.trigger('omeka:elementformload');
				form.show();
				activateWYSWYG();
			}
		});
	
	jQuery('#item-type').change(function () {
		var params = {
			type_id: jQuery('#item-type').val()
		};
		jQuery.ajax({
			url: changeItemTypeUrl,
			type: 'POST',
			dataType: 'html',
			data: params,
			success: function (response) {
				var form = jQuery('#type-metadata-form');
				form.hide();
				form.find('textarea').each(function () {
					tinyMCE.EditorManager.execCommand('mceRemoveEditor', true, this.id);
				});
				form.html(response);
				form.trigger('omeka:elementformload');
				form.slideDown(1000, function () {
					// Explicit show() call fixes IE7
					jQuery(this).show();
				});
				activateWYSWYG();
			}
		});
	});
	
	elementFormRequest = function (fieldDiv, params, elementFormPartialUri, recordType) {
		var elementId = fieldDiv.attr('id').replace(/element-/, '');
        
        fieldDiv.find('input, textarea, select').each(function () {
            var element = jQuery(this);
            // Workaround for annoying jQuery treatment of checkboxes.
            if (element.is('[type=checkbox]')) {
                params[this.name] = element.is(':checked') ? '1' : '0';
            } else {
                // Make sure TinyMCE saves to the textarea before we read
                // from it
                if (element.is('textarea')) {
                    var mce = tinyMCE.get(this.id);
                    if (mce) {
                        mce.save();
                    }
                }
                params[this.name] = element.val();
            }
        });
		
		recordId = typeof recordId !== 'undefined' ? recordId : 0;
        
        params.element_id = elementId;
        params.record_type = recordType;
		params.record_id = recordId;
		
		jQuery.ajax({
            url: elementFormPartialUri,
            type: 'POST',
            dataType: 'html',
            data: params,
			record_id: recordId,
            success: function (response) {
                fieldDiv.find('textarea').each(function () {
                    tinyMCE.EditorManager.execCommand('mceRemoveEditor', false, this.id);
                });
                fieldDiv.html(response);
                fieldDiv.trigger('omeka:elementformload');
				activateWYSWYG();
            }
        });
	}
	registerRemoveButtons = function() {
		// When a remove button is clicked, remove that input from the form.
		jQuery("#content").on("click", ".remove-element", function (event) {
			event.preventDefault();
			var removeButton = jQuery(this);

			// Don't delete the last input block for an element.
			if (removeButton.parents(".field").find(".input-block").length === 1) {
				return;
			}

			if (!confirm('Do you want to delete this input?')) {
				return;
			}

			var inputBlock = removeButton.parents(".input-block");
			inputBlock.find('textarea').each(function () {
				tinyMCE.EditorManager.execCommand('mceRemoveEditor', false, this.id);
			});
			inputBlock.remove();

			// Hide remove buttons for fields with one input.
			jQuery("div.field").each(function () {
				var removeButtons = jQuery(this).find(".remove-element");
				if (removeButtons.length === 1) {
					removeButtons.hide();
				}
			});
		});
	}
	registerAddButtons = function() {
		// When an add button is clicked, make an AJAX request to add another input.
		jQuery("#content").on("click", ".add-element", function (event) {
			event.preventDefault();
			var fieldDiv = jQuery(this).parents(".field");
			
			elementFormRequest(fieldDiv, {add: '1'}, elementFormUrl, "Item");
		});
	}
	registerAddButtons();
	registerRemoveButtons();
});