<?php
/**
 * @class RecipeRating_Form
 *
 * This class defines the forms for the project users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeRating_Form extends Form
{

    static public function formPublic($recipe) {
        if ($recipe->id() != '') {
            $recipeRatingForm = new RecipeRating_Form(['id_recipe' => $recipe->id()]);
            $formFields = '
                <h2>' . __('vote') . '</h2>
                <p>' . $recipe->getBasicInfo() . '</p>
                <div class="rating rating_select">
                    <div class="rating_ins">
                        <i class="icon icon-star"></i>
                        <i class="icon icon-star"></i>
                        <i class="icon icon-star"></i>
                        <i class="icon icon-star"></i>
                        <i class="icon icon-star"></i>
                    </div>
                    ' . FormField::show('hidden', ['name' => 'rating']) . '
                    ' . FormField::show('hidden', ['name' => 'id_recipe', 'value' => $recipe->id()]) . '
                    ' . $recipeRatingForm->field('message') . '
                    ' . $recipeRatingForm->field('id') . '
                </div>';
            return Form::createForm($formFields, ['submit' => __('send_message'), 'class' => 'recipe_vote']);
        }
    }

}
