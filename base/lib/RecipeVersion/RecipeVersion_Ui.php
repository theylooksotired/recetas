<?php
/**
 * @class RecipeVersionUi
 *
 * This class manages the UI for the RecipeVersion objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeVersion_Ui extends Ui
{

    public function renderLinkAdmin()
    {
        return '<a href="' . $this->object->urlModify() . '" target="_blank">' . $this->object->getBasicInfo() . '</a> ';
    }

    public function renderPreparationParagraph()
    {
        $this->object->loadMultipleValues();
        $html = '';
        foreach ($this->object->get('preparation') as $preparation) {
            $html .= $preparation->get('step') . ' ';
        }
        return $html;
    }

    public function renderJsonHeader($options = [])
    {
        if (!isset($options['recipe']) || !isset($options['category'])) {
            return '';
        }
        $this->object->loadMultipleValues();
        $recipe = $options['recipe'];
        $category = $options['category'];
        $ingredients = [];
        foreach ($this->object->get('ingredients') as $ingredient) {
            $ingredients[] = $ingredient->get('amount') . ' ' . __($ingredient->get('type')) . ' ' . $ingredient->get('ingredient');
        }
        $instructions = [];
        foreach ($this->object->get('preparation') as $preparation) {
            $instructions[] = ['@type' => 'HowToStep', 'text' => $preparation->get('step')];
        }
        $info = [
            '@context' => 'http://schema.org/',
            '@type' => 'Recipe',
            'name' => $recipe->getBasicInfo() . ' (' . $this->object->getBasicInfo() . ')',
            'image' => $recipe->getImageUrl('image', 'web'),
            'description' => $recipe->get('short_description'),
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $recipe->get('rating'),
                'ratingCount' => $recipe->get('rating') * 6,
            ],
            'author' => [
                '@type' => 'Organization',
                'name' => Parameter::code('meta_title_page'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => Parameter::code('meta_title_page'),
                'logo' => Parameter::code('meta_image'),
            ],
            'datePublished' => $this->object->get('created'),
            'dateModified' => $this->object->get('modified'),
            'recipeCuisine' => $recipe->label('diet'),
            'recipeCategory' => $category->getBasicInfo(),
            'recipeYield' => $this->object->getServings(),
            'recipeIngredient' => $ingredients,
            'recipeInstructions' => $instructions,
        ];
        if ($this->object->getCookTime() != '') {
            $info['cookTime'] = $this->object->getCookTime();
        }
        if ($this->object->get('cooking_method') != '') {
            $info['cookingMethod'] = $this->object->get('cooking_method');
        }
        return '<script type="application/ld+json">' . json_encode($info) . '</script>';
    }

}
