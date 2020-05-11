<?php
/**
 * Default Metadata
 * 
 * @copyright Copyright 2019, 2020 Ben Ostermeier and Eric C. Weig
 * @license http://opensource.org/licenses/MIT MIT
 */

/**
 * The Default Metadata index controller class.
 * 
 * @package Omeka\Plugins\DefaultMetadata
 */
class DefaultMetadata_IndexController extends Omeka_Controller_AbstractActionController
{
	protected $_autoCsrfProtection = true;
	
	/**
     * Set the model class
	**/
	public function init()
    {
        $this->_helper->db->setDefaultModelName('DefaultMetadataValue');
    }
	
	/**
     * Starting point when loading the /admin/default-values page
	**/
    public function indexAction() 
	{
		// render the form
		$this->view->form = $this->_getForm();
		
		// add csrf token
		$csrf = new Omeka_Form_SessionCsrf;
		$this->view->csrf = $csrf;
		
		// process the form, save the values if the user submitted it
		$this->_processPageForm($csrf);
		
		if ($this->getRequest()->isPost()) { // if the form was submitted, we want to render it again after it is saved
			$this->view->form = $this->_getForm();
		}
	}
	
	/**
     * Get all elements for items, including default values
	**/
	private function _getAllElementsInSet($elementSet) {
		return $this->_helper->db->getTable('DefaultMetadataValue')->findElementsInSet($elementSet);
	}
	
	/**
     * Get all element sets for items
	**/
	protected function _getAllElementSets() {
		return $this->_helper->db->getTable('ElementSet')->findByRecordType('Item');
	}
	
	/**
     * Get all default values in the database, uses the database record's id as the index
	**/
	private function _getAllValuesDatabaseIndex() {
		$defaultValues = $this->_helper->db->getTable('DefaultMetadataValue')->findAll();
		$indexed = array();
        foreach ($defaultValues as $defaultValue) {
            $indexed[$defaultValue->id] = $defaultValue;
        }
        return $indexed;
	}
	
	/**
     * Returns an instance of the value that corresponds to the new value to be saved. Returns false if the value is not found.
	**/
	private function _oldValue($value, $oldValues) {
		foreach($oldValues as $oldValue) {
			if(($value->element_id == $oldValue['element_id']) && ($value->input_order == $oldValue['input_order'])) {
				return $oldValue;
			}
		}		
		return false;
	}
	
	/**
     * Gets all values with a particular element ID, returns a new array
	**/
	private function _allElementValues($elementID, $elements) {
		$elementValues = array();
		
		foreach($elements as $element) {
			if($element['id'] == $elementID) {
				array_push($elementValues, $element);
			}
		}
		return $elementValues;
	}
	
	/**
     * Creates html input for the element field
	**/
	private function _field($elements, $isMultiInput) {
		$form = '<div class="field" id="element-' . $elements[0]['id'] . '">'; 
		$form .= '<div class="two columns alpha">';
		$form .= '<label>' . __($elements[0]['name']) . '</label>'; 
		$form .= '<button name="add_element_' . $elements[0]['id'] . '" id="add_element_' . $elements[0]['id'] . '" type="button" value="Add Input" class="add-element">Add Input</button>';
		$form .= '</div>';
		$form .= '<div class="inputs five columns omega">';
		$form .= '<p class="explanation">' . __($elements[0]['description']) . '</p>';
		$form .= $this->_inputBlock($elements, $isMultiInput);
		$form .= '</div>';
		$form .= '</div>';
		
		return $form;
	}
	
	/**
     * Creates html for each input
	**/
	private function _inputBlock($elements, $isMultiInput) {
		$form = "";
		foreach($elements as $element) {
			if(is_null($element['input_order'])) { // for elements without previous default metadata, the input order will be null. we need it to be zero.
				$element['input_order'] = 0;
			}
			$form .= '<div class="input-block">';
			$form .= '<div class="input">';
			$form .= '<textarea name="Elements[' . $element['id'] . '][' . $element['input_order'] . '][text]" id="Elements-' . $element['id'] . '-'. $element['input_order'] . '-text" rows="3" cols="50">' . $element['text'] . '</textarea>';
			$form .= '</div>';
			if($isMultiInput) {
				$form .= '<div class="controls">';
				$form .= '<input type="submit" name="" value="Remove" class="remove-element red button">';
				$form .= '</div>';
			}
			$form .= '<label class="use-html">Use HTML';
			$form .= '<input type="hidden" name="Elements[' . $element['id'] . '][' . $element['input_order'] . '][html]" value="0">';
			$form .= '<input type="checkbox" name="Elements[' . $element['id'] . '][' . $element['input_order'] . '][html]" id="Elements-' . $element['id'] . '-'. $element['input_order'] . '-html" value="1" class="use-html-checkbox" ';
			if($element['html']) { 
				$form .= 'checked="checked"';
			}
			$form .= '>';
			$form .= '</label>';
			$form .= '</div>';
		}
		return $form;
	}

	/**
     * Creates the page form
	**/
	protected function _getForm() 
	{ 
		$form = "";
		$allElementSets = $this->_getAllElementSets();
		$ignoreElements = array();
		foreach ($allElementSets as $elementSet) { //traverse each element set to create a form group for each
			if($elementSet['name'] != "Item Type Metadata") { // start with non item type metadata
				
				$form .= '<div id="' . text_to_id($elementSet['name']) . '-metadata">';
				$form .= '<fieldset class="set">';
				$form .= '<h2>' . __($elementSet['name']) . '</h2>';
				$form .= '<p class="element-set-description" id="';
				$form .= html_escape(text_to_id($elementSet['name']) . '-description') . '">';
				$form .= url_to_link(__($elementSet['description'])) . '</p>';
				
				$elements = $this->_getAllElementsInSet($elementSet['id']);
				foreach ($elements as $element) { //traverse each element in the set to create a form input for each in the form group
					$allElementValues = $this->_allElementValues($element['id'], $elements);
					if ((!in_array($element['id'], $ignoreElements)) && (count($allElementValues) > 1)) { // if the element has a value and has multiple inputs
						$form .= $this->_field($allElementValues, true);
						array_push($ignoreElements, $element['id']);
					} else if (!in_array($element['id'], $ignoreElements)) { 
						$form .= $this->_field($allElementValues, false);
					}
				}
				$form .= "</fieldset>";
				$form .= "</div>";
			} else { // if item type metadata
				$item_types = get_records('ItemType', array('sort_field' => 'name'), 1000);
				$defaultItemType = $this->_helper->db->getTable('DefaultMetadataValue')->getDefaultItemType();
				$defaultItemTypeId = 0;
				if (!empty($defaultItemType)) {
					$defaultItemTypeId = intval($defaultItemType[0]["text"]);
				}
				$form .= '<div id="item-type-metadata-metadata">';
				$form .= '<h2>' . __($elementSet['name']) . '</h2>';
				$form .= '<div class="field" id="type-select">';
				$form .= '<div class="two columns alpha">';
				$form .= '<label for="item-type">Item Type</label>    </div>';
				$form .= '<div class="inputs five columns omega">';
				$form .= '<select name="item_type_id" id="item-type">';
				$form .= '<option value="">Select Below </option>';
				foreach ($item_types as $item_type) {
					if($item_type["id"] == $defaultItemTypeId) {
						$form .= '<option value="' . $item_type['id'] . '" selected="selected">' . $item_type['name'] . '</option>';
					} else {
						$form .= '<option value="' . $item_type['id'] . '">' . $item_type['name'] . '</option>';
					}
				}
				$form .= '</select>    </div>';
				$form .= '<input type="submit" name="change_type" id="change_type" value="Pick this type" style="display: none;">';
				$form .= '</div>';
				$form .= '<div id="type-metadata-form">';
				$form .= '<div class="five columns offset-by-two omega">';
				$form .=  '<p class="element-set-description">';
				$form .= '</p>';
				$form .= '</div>';
				$form .= '</div>';
				$form .= '</div>';
				
			}
		}
		
        return $form;
    }

	/**
     * Process the form and save the values
	**/
    private function _processPageForm($csrf)
    {
		$oldValues = $this->_getAllValuesDatabaseIndex();
        if ($this->getRequest()->isPost()) { // if the user is submitting the form
			$values = $_POST['Elements'];
			if ($this->_autoCsrfProtection && !$csrf->isValid($_POST)) {
                $this->_helper->_flashMessenger(__('There was an error on the form. Please try again.'), 'error');
                return;
            }
			if (!("" == $_POST['item_type_id']) ) { // if the user assigned an item type, save it with the element id of 0. this will force an update if there is already a saved item type.
				$value = new DefaultMetadataValue;
				$value->element_id = 0;
				$value->input_order = 0;
				$value->text = $_POST['item_type_id'];
				$value->html = 0;
				
				try {
					$value->setPostData($_POST);
					$value->save();
				// Catch validation errors.
				} catch (Omeka_Validate_Exception $e) {
					$this->_helper->flashMessenger($e);
				}
			}
			foreach($values as $id => $texts) { //iterate over each textbox on the form and save them to the database
				
				$order = 0;
				foreach($texts as $text) {
					if (!("" == trim($text['text']))) { // if the input has a value
						
						$value = new DefaultMetadataValue;
						$value->element_id = $id;
						$value->input_order = $order;
						$value->text = $text['text'];
						$value->html = intval($text['html']);
						
						
						$oldValue = $this->_oldValue($value, $oldValues); // check for the corresponding old value
						
						if(!$oldValue) { // if the value's element does not have a value already in the database, save it
							try {
								$value->setPostData($_POST);
								$value->save();
							// Catch validation errors.
							} catch (Omeka_Validate_Exception $e) {
								$this->_helper->flashMessenger($e);
							}
						} else if (($value->text != $oldValue['text']) || ($value->html != $oldValue['html'])) { //if the value already in the database was changed or if the html boolean is different, update the database record.
							$value->id = $oldValue['id']; // having the id of an existing database record forces UPDATE instead of INSERT
							try {
								$value->setPostData($_POST);
								$value->save();
							// Catch validation errors.
							} catch (Omeka_Validate_Exception $e) {
								$this->_helper->flashMessenger($e);
							}
							unset($oldValues[$oldValue['id']]); // remove the old value from the array, so we don't delete it later
						} else { // otherwise, the value is unchanged and do not save it to the database
							unset($oldValues[$oldValue['id']]); // remove the old value from the array, so we don't delete it later
						}
					}
				$order++;
				}
			}
			// delete old values not found in the form
			foreach($oldValues as $oldValue) {
				$oldValue->delete();
			}
			$this->_helper->flashMessenger(__('Default metadata have been saved.'), 'success');
        }
    }
}