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
            ' : '';
        return $buttonApi . parent::createFormFields($options);
    }

}
