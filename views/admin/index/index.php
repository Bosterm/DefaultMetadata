<?php
queue_js_file('vendor/tinymce/tinymce.min');
queue_js_file('form');
$title =  __('Default Metadata');
$head = array(
    'title' => $title,
    'bodyclass' => 'primary',
);
echo head($head);

$view = get_view();
?>

<?php echo flash(); ?>

<form enctype="application/x-www-form-urlencoded" method="post">
	<section class="seven columns alpha">
	<p>Assign values to any of the below metadata fields to set the default value for that metadata field. When users add or edit an item, if a default value has been set and that specific metadata field is blank, the default value will be inserted onto the item edit form. The value is then saved when the item is saved. Users can modify the values for individual items as needed.</p>
	<p>The plugin supports multiple default values for each metadata field using the "add input" feature of item editing. Please note that the plugin will only insert default values onto items that have a completely empty metadata field. For example, if an item has one DC:Subject but there are two default subjects set by the plugin, the plugin will not insert the second default value onto the item's subject field.</p>
	<p>Please note: values will not apply to items added automatically to Omeka, such as <a target="_BLANK" href="https://omeka.org/classic/plugins/CsvImport/">CSV imports</a> or through the <a target="_BLANK" href="https://omeka.readthedocs.io/en/latest/Reference/api/">Omeka API</a>. They will also not apply to previously added items unless users edit them manually after setting a default value.</p>
	<?php echo $form; ?>
	</section>
	<?php echo $csrf; ?>
	<section class="three columns omega">
		<div id="save" class="panel">
			<?php echo $this->formSubmit('save-changes', __('Save Changes'), array('class' => 'submit big green button')); ?>
		</div>
	</section>
	<script>
		jQuery( window ).load(function() {
			jQuery(window).off('beforeunload'); // this disables warnings about leaving the page without saving changes, which it will do even if you do nothing to the form
		});
		var changeItemTypeUrl = <?php echo js_escape(url("default-metadata/value/change-type")) ?>;
		var elementFormUrl = <?php echo js_escape(url("elements/element-form")) ?>;
	</script>
<?php echo foot();