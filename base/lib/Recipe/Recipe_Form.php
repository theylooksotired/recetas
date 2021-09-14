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

    public function create()
    {
        $fields = '
            ' . $this->field('image') . '
            ' . $this->field('title') . '
            ' . $this->field('short_description') . '
            <div class="recipe_form_line">
                ' . $this->field('id_category') . '
                ' . $this->field('rating') . '
                ' . $this->field('cook_time') . '
                ' . $this->field('cooking_method') . '
                ' . $this->field('servings') . '
                ' . $this->field('diet') . '
            </div>
            <div class="recipe_form_ingredients">' . $this->field('ingredients') . '</div>
            <div class="recipe_form_preparation">' . $this->field('preparation') . '</div>
            ' . $this->field('tags');
        return Form::createForm($fields, ['submit' => __('save'), 'action' => url('cuenta/subir-receta'), 'class'=>'form_site form_site_border']);
    }

    public function edit()
    {
        if ($this->object->id() == '') {
            return '';
        }
        $fields = '
            ' . $this->field('image') . '
            ' . $this->field('title') . '
            ' . $this->field('short_description') . '
            <div class="recipe_form_line">
                ' . $this->field('id_category') . '
                ' . $this->field('rating') . '
                ' . $this->field('cook_time') . '
                ' . $this->field('cooking_method') . '
                ' . $this->field('servings') . '
                ' . $this->field('diet') . '
            </div>
            <div class="recipe_form_ingredients">' . $this->field('ingredients') . '</div>
            <div class="recipe_form_preparation">' . $this->field('preparation') . '</div>
            ' . $this->field('tags');
        return Form::createForm($fields, ['submit' => __('save'), 'action' => url('cuenta/editar-receta/' . $this->object->id()), 'class'=>'form_site form_site_border']);
    }

}
