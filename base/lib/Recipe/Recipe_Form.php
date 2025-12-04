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
        $versions = new ListObjects('RecipeVersion', ['where' => 'id_recipe="' . $this->object->id() . '"']);
        $buttonApi = ($this->object->id() != '') ? '
            <div class="button button_small button_api_recipes" data-url="' . url('recipe/load-ai-data/' . $this->object->id(), true) . '">Cargar informacion de ChatGPT</div>
            <div class="button button_small button_api_steps" data-url="' . url('recipe/steps-ai-data/' . $this->object->id(), true) . '">Corregir preparación con ChatGPT</div>
            <a class="button button_small" href="' . url('recipe/create-version/' . $this->object->id(), true) . '">Crear una version con AI</a>
            <a class="button button_small" target="_blank" href="' . url('recipe/prompts-images/' . $this->object->id(), true) . '">Prompts imagenes</a>
            <a class="button button_small" target="_blank" href="' . url('recipe/social-media/' . $this->object->id(), true) . '">Redes sociales</a>
            ' . ((!$versions->isEmpty()) ? '<div class="recipe_versions">' . $versions->showList(['function' => 'LinkAdmin']) . '</div>' : '') . '
            <hr/>
            ' : '
            <div class="textarea_resizable form_field  form_field_short_description required ">
                <div class="form_field_ins">
                    <textarea name="recipe_content" cols="80" rows="1"></textarea>
                </div>
            </div>
            <div class="button button_small button_api_content" data-url="' . url('recipe/full-ai-recipe', true) . '">Llenar todo con el portapapeles</div>
            <hr/>';
        return $buttonApi . parent::createFormFields($options);
    }

}
