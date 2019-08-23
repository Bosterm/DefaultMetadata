<?php
/**
 * Default Metadata
 * 
 * @copyright Copyright 2019 Ben Ostermeier and Eric C. Weig
 * @license http://opensource.org/licenses/MIT MIT
 */
 
/**
 * The Default Metadata value controller class.
 * 
 * @package Omeka\Plugins\DefaultMetadata
 */
class DefaultMetadata_ValueController extends Omeka_Controller_AbstractActionController
{
	public function changeTypeAction()
    {
		$defaultValues = $this->_helper->db->getTable('DefaultMetadataValue')->getAllValues();
		
		$item = new Item;
		$item->item_type_id = (int) $_POST['type_id'];
		$elements = $item->getItemTypeElements();
		$form = "";

		foreach ($elements as $element) { //traverse each element in the set to create a form input for each
			$form .= '<div class="field" id="element-' . $element['id'] . '">';
			$form .= '<div class="two columns alpha">';
			$form .= '<label>' . __($element['name']) . '</label>'; 
			// add input will go here
			$form .= '</div>';
			$form .= '<div class="inputs five columns omega">';
			$form .= '<p class="explanation">' . __($element['description']) . '</p>';
			$form .= '<div class="input-block">';
			$form .= '<div class="input">';
			$form .= '<textarea name="Elements[' . $element['id'] . '][0][text]" id="Elements-' . $element['id'] . '-0-text" rows="3" cols="50">';
			$valueExists = false;
			if (array_key_exists($element['id'], $defaultValues)) {
				$form .= $defaultValues[$element['id']]['text'];
				$valueExists = true;
			}
			$form .='</textarea>';
			$form .= '</div>';
			// remove input will go here
			$form .= '<label class="use-html">Use HTML';
			$form .= '<input type="hidden" name="Elements[' . $element['id'] . '][0][html]" value="0">';
			$form .= '<input type="checkbox" name="Elements[' . $element['id'] . '][0][html]" id="Elements-' . $element['id'] . '-0-html" value="1" class="use-html-checkbox" ';
			if($valueExists && $defaultValues[$element['id']]['html']) { 
				$form .= 'checked="checked"';
			}
			$form .= '>';
			$form .= '</label>';
			$form .= '</div>';
			$form .= '</div>';
			$form .= '</div>';
		}
		
        $this->view->assign(compact('item'));
		$this->view->form = $form;
    }
}