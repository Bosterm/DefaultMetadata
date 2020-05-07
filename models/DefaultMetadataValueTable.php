 <?php
/**
 * Default Metadata
 * 
 * @copyright Copyright 2019, 2020 Ben Ostermeier and Eric C. Weig
 * @license http://opensource.org/licenses/MIT MIT
 */
 
/**
 * The Default Metadata value table class
 * 
 * @package Omeka\Plugins\DefaultValues
 */
class DefaultMetadataValueTable extends Omeka_Db_Table
{
    /**
     * Get all elements for items
	**/
	public function findElementsInSet($elementSet) {
		$db = $this->getDb();
		
		//this query joins the elements table and the default values table to get the values already saved and the element name, description, and id to generate the form. records are ordered first by the order field from the elements table, then by the element id. This is the same ordering used on item edit pages.
		$sql = "SELECT elements.id, elements.name, elements.description, default_values.input_order, default_values.html, default_values.text FROM " . $db->Element . " as elements left outer JOIN " . $this->getTableName() . " as default_values on elements.id = default_values.element_id WHERE elements.element_set_id =" . $elementSet . " ORDER BY (CASE WHEN elements.order Is NULL THEN 1 ELSE 0 END), elements.order, elements.id ASC";
		
		return $db->fetchAll($sql);
	}
	
	public function getDefaultItemType() {
		$db = $this->getDb();
		
		$sql = "SELECT * FROM " . $this->getTableName() . " WHERE element_id = 0";
		
		return $db->fetchAll($sql);
	}
	
	public function getItemTypeElements() {
		$db = $this->getDb();
		
		$sql = "SELECT default_values.element_id, default_values.html, default_values.input_order, default_values.text FROM " . $this->getTableName() . " as default_values left outer JOIN " . $db->Element . " as elements ON default_values.element_id = elements.id WHERE elements.element_set_id = 3";
		
		return $db->fetchAll($sql);
	}
	
	public function getNonItemTypeElements() {
		$db = $this->getDb();
		
		$sql = "SELECT default_values.element_id, default_values.html, default_values.input_order, default_values.text FROM " . $this->getTableName() . " as default_values left outer JOIN " . $db->Element . " as elements ON default_values.element_id = elements.id WHERE elements.element_set_id <> 3";
		
		return $db->fetchAll($sql);
	}
	
	/**
     * Remove elements that were deleted by an uninstall
	**/
	public function removeUninstalledElements() {
		$db = $this->getDb();
		
		//this query finds values that no longer correspond to an element in the elements table, as the element(s) were uninstalled. we can then take those elements and delete them
		$sql = "SELECT * FROM " . $this->getTableName() . " WHERE element_id NOT IN (SELECT id FROM " . $db->Element . ")";
		
		$elementsToDelete = $db->fetchAll($sql);
		
		foreach ($elementsToDelete as $elementToDelete) {
			$value = new DefaultMetadataValue;
			$value->element_id = $elementToDelete['element_id'];
			$value->text = $elementToDelete['text'];
			$value->html = $elementToDelete['html'];
			$value->id = $elementToDelete['id'];
			$value->delete();
		}
	}
	
	/**
     * Get all default values in the database, uses the element id_input order as the array index
	**/
	public function getAllValues() {
		$defaultValues = $this->getDb()->getTable('DefaultMetadataValue')->findAll();
		$indexed = array();
        foreach ($defaultValues as $defaultValue) {
			$index = $defaultValue->element_id . "_" . $defaultValue->input_order;
            $indexed[$index] = $defaultValue;
        }
        return $indexed;
	}
}