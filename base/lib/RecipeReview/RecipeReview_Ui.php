<?php
/**
 * @class RecipeReview_Ui
 *
 * This class defines the ui for the project users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeReview_Ui extends Ui
{

    public function renderRecipe($options = [])
    {
        return '
            <div class="recipe_review">
                <p class="recipe_review_date">' . Date::sqlText($this->object->get('created')) . '</p>
                <p class="recipe_review_name">' . $this->object->get('name') . '</p>
                <p class="recipe_review_title">' . $this->object->get('title') . '</p>
                <p class="recipe_review_review">' . nl2br($this->object->get('review')) . '</p>
            </div>';
    }

}
