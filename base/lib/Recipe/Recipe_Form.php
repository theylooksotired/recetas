<?php
/**
 * @class RecipeForm
 *
 * This class manages the forms for the Recipe objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Recipe_Form extends Form
{

    public function createFormFields($options = [])
    {
        $buttonApi = ($this->object->id() != '') ? '
            <div class="button button_small button_api_recipes" data-url="' . url('recipe/load-ai-data/' . $this->object->id(), true) . '">Cargar informacion de ChatGPT</div>
            <div class="button button_small button_api_steps" data-url="' . url('recipe/steps-ai-data/' . $this->object->id(), true) . '">Corregir preparación con ChatGPT</div>
            <hr/>
            ' : '';
        return $buttonApi . parent::createFormFields($options);
    }

}
