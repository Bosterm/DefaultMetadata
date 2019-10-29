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
});