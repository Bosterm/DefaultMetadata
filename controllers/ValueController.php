<?php
/**
 * Default Metadata
 * 
 * @copyright Copyright 2019, 2020 Ben Ostermeier and Eric C. Weig
 * @license http://opensource.org/licenses/MIT MIT
 */
 
/**
 * The Default Metadata value controller class.
 * 
 * @package Omeka\Plugins\DefaultMetadata
 */
class DefaultMetadata_ValueController extends Omeka_Controller_AbstractActionController
{
	/**
     * Gets all values with a particular element ID, returns a new array. if there are no values, returns with an array with one empty value
	**/
	public function allValuesForElement($elementID, $elements) {
		$elementValues = array();
		
		foreach($elements as $element) {
			if($element['element_id'] == $elementID) {
				array_push($elementValues, $element);
			}
		}
		if (sizeof($elementValues) == 0) {
			$value = new DefaultMetadataValue;
			$value->element_id = $elementID;
			$value->input_order = 0;
			$value->text = "";
			$value->html = 0;
			array_push($elementValues, $value);
		}
		return $elementValues;
	}
	
	public function changeTypeAction()
    {
		$defaultValues = $this->_helper->db->getTable('DefaultMetadataValue')->getAllValues();
		
		$item = new Item;
		$item->item_type_id = (int) $_POST['type_id'];
		$elements = $item->getItemTypeElements();
		$form = "";
		foreach ($elements as $element) { //traverse each element in the set to create a form input for each
			$isMultiInput = false;
			if(array_key_exists($element['id'] . '_' . "1", $defaultValues)) { // if an item type element with the array order 1 exists, there are at least two inputs
				$isMultiInput = true;
			}
			
			$form .= '<div class="field" id="element-' . $element['id'] . '">';
			$form .= '<div class="two columns alpha">';
			$form .= '<label>' . __($element['name']) . '</label>'; 
			$form .= '<button name="add_element_' . $element['id'] . '" id="add_element_' . $element['id'] . '" type="button" value="Add Input" class="add-element">Add Input</button>';
			$form .= '</div>';
			$form .= '<div class="inputs five columns omega">';
			$form .= '<p class="explanation">' . __($element['description']) . '</p>';
			
			// get all default values for this element as an array, iterate over each value in array
			$allValuesForElement = $this->allValuesForElement($element['id'], $defaultValues);
			foreach($allValuesForElement as $elementValue) {
				$form .= '<div class="input-block">';
				$form .= '<div class="input">';
				$form .= '<textarea name="Elements[' . $element['id'] . '][' . $elementValue['input_order'] . '][text]" id="Elements-' . $element['id'] . '-' . $elementValue['input_order'] . '-text" rows="3" cols="50">';
				$form .= $elementValue['text'];
				$form .='</textarea>';
				$form .= '</div>';
				if($isMultiInput) {
					$form .= '<div class="controls">';
					$form .= '<input type="submit" name="" value="Remove" class="remove-element red button">';
					$form .= '</div>';
				}
				$form .= '<label class="use-html">Use HTML';
				$form .= '<input type="hidden" name="Elements[' . $element['id'] . '][' . $elementValue['input_order'] . '][html]" value="0">';
				$form .= '<input type="checkbox" name="Elements[' . $element['id'] . '][' . $elementValue['input_order'] . '][html]" id="Elements-' . $element['id'] . '-' . $elementValue['input_order'] . '-html" value="1" class="use-html-checkbox" ';
				if($elementValue['html']) { 
					$form .= 'checked="checked"';
				}
				$form .= '>';
				$form .= '</div>';
			}
			$form .= '</label>';
			$form .= '</div>';
			$form .= '</div>';
			$form .= '</div>';
		}
		
        $this->view->assign(compact('item'));
		$this->view->form = $form;
    }
}