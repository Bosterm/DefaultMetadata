<?php
/**
 * Default Metadata
 * 
 * @copyright Copyright 2019 Ben Ostermeier and Eric C. Weig
 * @license http://opensource.org/licenses/MIT MIT
 */
 
/**
 * The Default Metadata plugin.
 * 
 * @package Omeka\Plugins\DefaultMetadata
 */
class DefaultMetadataPlugin extends Omeka_Plugin_AbstractPlugin
{
    
    // Define Hooks
    protected $_hooks = array(
        'install',
        'uninstall',
	'admin_footer',
	'define_routes',
	'config',
	'config_form',
	'define_acl',
	'after_delete_element'
	);
	
	// Define filter to add to the admin menu
	protected $_filters = array('admin_navigation_main');
	
	/**
     * Creates database table on install
    **/
    public function hookInstall()
	{
		set_option("can_admins_edit", 0);
		$db = $this->_db;
        $sql = "            
            CREATE TABLE IF NOT EXISTS `$db->DefaultMetadataValues` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`element_id` int(10) UNSIGNED NOT NULL,
		`html` tinyint(4) NOT NULL DEFAULT '0',
		`text` mediumtext COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB ; ";
        $db->query($sql);
	}
	
    /**
     * Drops database table on uninstall
    **/
    public function hookUninstall()
    {
	$db = $this->_db;
        $sql = "DROP TABLE IF EXISTS `$db->DefaultMetadataValues`; ";
        $db->query($sql);
    }
    
	/**
     * Inserts javascript in the footer on item edit and add pages. 
	 * The script inserts the default values from the database onto form inputs if the element for the item is empty, so the values are saved to the item when it is saved.
    **/
    public function hookAdminFooter()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
		
		if (($controller == 'items' && ($action == 'edit' || $action == 'add'))) {	//if on the item edit or add page
		
		$table = $this->_db->getTable('DefaultMetadataValue');
		$defaultValues = $table->getNonItemTypeElements(); //non item type elements
		$defaultItemTypeValues = $table->getItemTypeElements(); // item type elements
		$defaultType = $table->getTable('DefaultMetadataValue')->getDefaultItemType();
		
	?>
    <script>
		itemTypeSelect = document.getElementById("item-type");
		if ((itemTypeSelect.value === "") && <?php echo js_escape(isset($defaultType[0])) ?>) { // if the item has no item type and there is a default item type
			document.getElementById("item-type").value = <?php echo intval($defaultType[0]["text"]); ?>;
			// we have to do the standard Omeka ajax that generates the item type form.
			var changeItemTypeUrl = <?php echo js_escape(url("items/change-type")) ?>;
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
					<?php foreach ($defaultItemTypeValues as $key => $defaultValue) : // traverse each default item type metadata value to insert them on the corresponding form input. the key ensures javascript variables have different names for each default value  ?>
						var element_id_<?php echo js_escape($key); ?> = <?php echo js_escape($defaultValue['element_id']); ?>;
						var textInput_<?php echo js_escape($key); ?> = document.getElementById("Elements-" + element_id_<?php echo js_escape($key) ?> + "-0-text");
						if(!(textInput_<?php echo js_escape($key); ?> && textInput_<?php echo js_escape($key); ?>.value)) { // if the element's form input is blank
							<?php if ($defaultValue['html']) : // if the value uses html, check the box ?>
								document.getElementById("Elements-" + element_id_<?php echo js_escape($key); ?> + "-0-html").checked = true;
								Omeka.Elements.enableWysiwyg(document);
							<?php endif; ?>
							textInput_<?php echo js_escape($key); ?>.value = <?php echo js_escape($defaultValue['text']); ?>;
						}
					<?php endforeach; ?>
				}
			});
		}
		<?php foreach ($defaultValues as $key => $defaultValue) : // traverse each default value to insert them on the corresponding form input. the key ensures javascript variables have different names for each default value  ?>
		var element_id_<?php echo js_escape($key); ?> = <?php echo js_escape($defaultValue['element_id']); ?>;
		var textInput_<?php echo js_escape($key); ?> = document.getElementById("Elements-" + element_id_<?php echo js_escape($key); ?> + "-0-text");
		if(!(textInput_<?php echo js_escape($key); ?> && textInput_<?php echo js_escape($key); ?>.value)) { // if the element's form input is blank
			window.addEventListener('load', function() { // on window load, add the value to the form input
				<?php if ($defaultValue['html']) : // if the value uses html, check the box ?>
					document.getElementById("Elements-" + element_id_<?php echo js_escape($key); ?> + "-0-html").checked = true;
					Omeka.Elements.enableWysiwyg(document);
				<?php endif; ?>
				textInput_<?php echo js_escape($key); ?>.value = <?php echo js_escape($defaultValue['text']); ?>;
			});
		}
		<?php endforeach; ?>
    </script>
	<?php
		}
	}
	
	/**
     * Define routes
    **/
	function hookDefineRoutes($args)
    {
        $router = $args['router'];
    }
	
	/**
     * When an element is deleted from the database, check for elements that were deleted and remove them from the default values table. in future, could see what is in $args and remove it that way
    **/
	function hookAfterDeleteElement($args)
	{		
		$this->_db->getTable('DefaultMetadataValue')->removeUninstalledElements();
	}
	
	public function hookConfigForm() 
    {
        include 'config_form.php';
    }
    
    public function hookConfig($args)
    {
        $post = $args['post'];
		set_option("can_admins_edit", $post['can_admins_edit']);
    }
    /**
     * Define this plugin's ACL to only allow super users to edit default values, unless the plugin is configed to also allow admins
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
		
		$indexResource = new Zend_Acl_Resource('DefaultMetadata_Index');
		$acl->add($indexResource);

		if (get_option('can_admins_edit')) {
			$acl->allow(array('super','admin'), 'DefaultMetadata_Index');
		} else {
			$acl->allow('super', 'DefaultMetadata_Index');
			$acl->deny('admin', 'DefaultMetadata_Index');
		}
		
    }
	
	/**
     * Add the Default Metadata admin nav link
    **/
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Default Metadata'),
            'uri' => url('default-metadata'),
			'resource' => 'DefaultMetadata_Index'
        );
        return $nav;
    }
}
