<?php
/**
 * Default Metadata
 * 
 * @copyright Copyright 2019, 2020 Ben Ostermeier and Eric C. Weig
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * The Default Metadata value record class.
 * 
 * @package Omeka\Plugins\DefaultMetadata
 */
class DefaultMetadataValue extends Omeka_Record_AbstractRecord implements Zend_Acl_Resource_Interface
{
    public $element_id;
    public $input_order = 0;
	public $html = 0;
    public $text = null;
	
	public function getResourceId() 
	{
		return 'DefaultMetadata_Value';
    }
}