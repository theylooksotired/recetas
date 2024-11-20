<?php
/**
 * @class RecipeVersionForm
 *
 * This class manages the forms for the RecipeVersion objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeVersion_Form extends Form
{

    public function createFormFields($options = [])
    {
        $buttonApi = ($this->object->id() != '') ? '
            <div class="button button_small button_api_steps" data-url="' . url('recipe_version/steps-ai-data/' . $this->object->id(), true) . '">Corregir preparación con ChatGPT</div>
            <hr/>
            ' : '
            <div class="textarea_resizable form_field  form_field_short_description required ">
                <div class="form_field_ins">
                    <textarea name="recipe_content" cols="80" rows="1"></textarea>
                </div>
            </div>
            <div class="button button_small button_api_content" data-url="' . url('recipe_version/full-ai-recipe', true) . '">Llenar todo con el portapapeles</div>
            <hr/>';
        return $buttonApi . parent::createFormFields($options);
    }

}
