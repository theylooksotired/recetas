<?php
/**
 * @class RecipeReview_Form
 *
 * This class defines the forms for the project users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeReview_Form extends Form
{

    static public function showPublic() {
        $fields = '' . '
        ' . FormField::show('text', ['name' => 'author', 'label' => __('your_name'), 'id' => 'author', 'required' => true]) . '
        ' . FormField::show('text', ['name' => 'title', 'label' => __('title_review'), 'id' => 'title', 'required' => true]) . '
        ' . FormField::show('textarea', ['name' => 'review', 'label' => __('your_review'), 'id' => 'review', 'required' => true]) . '
        ' . FormField::show('hidden', ['name' => 'type', 'value' => 'review']);
        return Form::createForm($fields, ['submit' => __('send'), 'id' => 'recipe_review_form', 'recaptchav3' => true]);
    }


}
