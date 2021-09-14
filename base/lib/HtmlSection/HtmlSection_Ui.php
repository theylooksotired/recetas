<?php
/**
* @class HtmlSectionUi
*
* This class manages the UI for the HtmlSection objects.
*
* @author Leano Martinet <info@asterion-cms.com>
* @package Asterion\Base
* @version 4.0.0
*/
class HtmlSection_Ui extends Ui{

	/**
	* Render a section.
	*/
    public function renderSection() {
        return '<div class="page_complete">'.$this->object->get('section').'</div>';
    }

}

?>