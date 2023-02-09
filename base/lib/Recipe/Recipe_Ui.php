<?php
/**
 * @class RecipeUi
 *
 * This class manages the UI for the Recipe objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Recipe_Ui extends Ui
{

    public function renderPublic()
    {
        return '
            <div class="recipe">
                <a class="recipe_ins" href="' . $this->object->url() . '">
                    <div class="recipe_image">' . $this->object->getImageAmpWebp('image', 'small') . '</div>
                    <div class="recipe_information">
                        <div class="recipe_title">' . $this->object->getBasicInfo() . '</div>
                        <div class="recipe_short_description">' . $this->object->get('short_description') . '</div>
                        <div class="recipe_rating">' . $this->renderRating() . '</div>
                        <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
                    </div>
                </a>
            </div>';
    }

    public function renderPublicSimple()
    {
        return '
            <div class="recipe_simple">
                <a class="recipe_ins" href="' . $this->object->url() . '">
                    <div class="recipe_image">' . $this->object->getImageAmpWebp('image', 'small') . '</div>
                    <div class="recipe_information">
                        <div class="recipe_rating">' . $this->renderRating() . '</div>
                        <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
                        <div class="recipe_title">' . $this->object->getBasicInfo() . '</div>
                        <div class="recipe_short_description">' . $this->object->get('short_description') . '</div>
                    </div>
                </a>
            </div>';
    }

    public function renderSide($options = [])
    {
        return '
            <div class="post_minimal">
                <a href="' . $this->object->url() . '" class="post_minimal_ins">
                    <div class="post_image_amp">'.$this->object->getImageAmpWebp('image', 'small').'</div>
                    <div class="post_title">' . $this->object->getBasicInfo() . '</div>
                </a>
            </div>';
    }

    public function renderComplete()
    {
        $this->object->loadMultipleValues();
        $otherVersions = '';
        if ($this->object->get('check_versions') == 1) {
            $versions = (new RecipeVersion)->readList(['where' => 'id_recipe="' . $this->object->id() . '"']);
            if (count($versions) > 0) {
                foreach ($versions as $version) {
                    $version->loadMultipleValuesSingleAttribute('ingredients');
                    $version->loadMultipleValuesSingleAttribute('preparation');
                    $versionUi = new Recipe_Ui($version);
                    $otherVersions .= '
                        <div class="recipe_wrapper recipe_version">
                            <h3>' . $version->getBasicInfo() . '</h3>
                            ' . (($version->get('short_description') != '') ? '<p>' . $version->get('short_description') . '</p>' : '') . '
                            <div class="recipe_ingredients">
                                <h4>' . __('ingredients') . '</h4>
                                <div class="recipe_ingredients_ins">' . $versionUi->renderIngredients() . '</div>
                            </div>
                            <div class="recipe_preparation">
                                <h4>' . __('preparation') . '</h4>
                                <div class="recipe_preparation_ins">' . $versionUi->renderPreparation() . '</div>
                            </div>
                        </div>';
                }
                $otherVersions = '
                    <div class="recipe_versions">
                        <h2>' . __('other_versions') . '</h2>
                        <p>' . __('other_versions_message') . '</p>
                        ' . $otherVersions . '
                    </div>';
            }
        }
        return '
            <div class="recipe_complete">
                <div class="recipe_complete_ins">
                    <div class="recipe_complete_info">
                        <div class="recipe_complete_info_left">
                            ' . $this->object->getImageAmpWebp('image', 'web') . '
                        </div>
                        <div class="recipe_complete_info_right">
                            <div class="recipe_short_description">' . $this->object->get('short_description') . '</div>
                            <div class="recipe_rating">' . $this->renderRating() . '</div>
                            <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
                        </div>
                    </div>
                    ' . Adsense::amp() . '
                    ' . $this->object->get('description') . '
                    <div class="recipe_wrapper">
                        <div class="recipe_ingredients">
                            <h2>' . __('ingredients') . '</h2>
                            <div class="recipe_ingredients_ins">' . $this->renderIngredients() . '</div>
                        </div>
                        ' . Adsense::amp() . '
                        <div class="recipe_preparation">
                            <h2>' . __('preparation') . '</h2>
                            <div class="recipe_preparation_ins">' . $this->renderPreparation() . '</div>
                        </div>
                    </div>
                </div>
            </div>
            ' . $otherVersions . '
            <div class="item_complete_share">
                <div class="item_complete_share_title">' . __('help_us_sharing') . '</div>
                ' . $this->share(['share' => ['facebook', 'twitter']]) . '
                ' . Navigation_Ui::facebookComments($this->object->url()) . '
            </div>';
    }

    public function renderInfo()
    {
        return '
            ' . (($this->object->get('cook_time') != '') ? '
            <div class="recipe_cook_time">
                <i class="fa fa-clock-o"></i>
                <span>' . __($this->object->get('cook_time')) . '</span>
            </div>
            ' : '') . '
            ' . (($this->object->get('cooking_method') != '') ? '
            <div class="recipe_cooking_method">
                <i class="fa fa-cutlery"></i>
                <span>' . __($this->object->get('cooking_method')) . '</span>
            </div>
            ' : '') . '
            ' . (($this->object->get('servings') != '') ? '
            <div class="recipe_servings">
                <i class="fa fa-users"></i>
                <span>' . $this->object->getServings() . '</span>
            </div>
            ' : '') . '
            ' . (($this->object->get('diet') != '') ? '
            <div class="recipe_diet">
                <i class="fa fa-globe"></i>
                <span>' . __($this->object->get('diet')) . '</span>
            </div>
            ' : '') . '';
    }

    public function renderIngredients()
    {
        $html = '';
        foreach ($this->object->get('ingredients') as $ingredient) {
            if ($ingredient->get('amount') != '') {
                $html .= '
                    <p class="ingredient">
                        <span class="ingredient_amount">' . $ingredient->get('amount') . '</span>
                        ' . (($ingredient->get('type') != 'unit' && $ingredient->get('type') != '') ? '
                        <span class="ingredient_type">' . strtolower((intval($ingredient->get('amount')) > 1) ? __($ingredient->get('type') . '_plural') : __($ingredient->get('type'))) . ' ' . __('of') . '</span>
                        ' : '') . '
                        <span class="ingredient_ingredient">' . $ingredient->get('ingredient') . '</span>
                    </p>';
            } else {
                $html .= '
                    <p class="ingredient">
                        <span class="ingredient_ingredient">' . $ingredient->get('ingredient') . '</span>
                    </p>';
            }
        }
        return $html;
    }

    public function renderPreparation()
    {
        $html = '';
        $i = 1;
        foreach ($this->object->get('preparation') as $preparation) {
            $html .= '
                <p class="preparation">
                    <span class="preparation_step_number">' . __('step') . ' ' . $i . ' :</span>
                    <span class="preparation_step">' . $preparation->get('step') . '</span>
                    ' . $preparation->getImageAmpWebp('image', 'web') . '
                </p>';
            $i++;
        }
        return $html;
    }

    public function renderRelated()
    {
        $posts = new ListObjects('Post', ['order' => 'MATCH (title, title_url, short_description) AGAINST ("' . $this->object->getBasicInfo() . '") DESC', 'limit' => '6']);
        $posts = (!$posts->isEmpty()) ? $posts : new ListObjects('Post', array('order' => 'RAND()', 'limit' => '5'));
        $recipes = (new Recipe)->readList(['where' => 'id!=:id AND id_category=:id_category AND active="1"', 'limit' => 6], ['id' => $this->object->id(), 'id_category' => $this->object->get('id_category')]);
        $recipesHtml = '';
        foreach ($recipes as $recipe) {
            $recipe->loadCategoryManually($this->object->get('id_category_object'));
            $recipesHtml .= $recipe->showUi('PublicSimple');
        }
        return '<div class="related">
                    <div class="related_block">
                        <div class="related_title">' . __('other_recipes') . '</div>
                        <div class="posts">' . $recipesHtml . '</div>
                    </div>
                    <div class="related_block">
                        <div class="related_title">' . __('other_posts') . '</div>
                        <div class="posts">' . $posts->showList(['function' => 'PublicSimple']) . '</div>
                    </div>
                </div>';
    }

    public function renderEdit()
    {
        $html = '
            <div class="itemEditInsWrapper">
                <div class="itemEditImage">' . $this->object->getImage('image', 'small') . '</div>
                <div class="itemEditInformation">
                    <div class="itemEditTitle">' . $this->object->getBasicInfo() . '</div>
                    <div class="itemEditCreated">' . __('created') . ' : ' . Date::sqlText($this->object->get('created')) . '</div>
                    <div class="itemEditModified">' . __('updated') . ' : ' . Date::sqlText($this->object->get('modified')) . '</div>
                </div>
            </div>';
        if ($this->object->get('active') == '1') {
            return '
                <div class="itemEdit">
                    <div class="itemEditIns">
                        <a href="' . $this->object->url() . '" target="_blank">' . $html . '</a>
                    </div>
                </div>';
        }
        return '
            <div class="itemEdit">
                <div class="itemEditIns">
                    <a href="' . url('cuenta/editar-receta/' . $this->object->id()) . '">' . $html . '</a>
                </div>
                    <div class="itemEditDelete">
                        <a href="' . url('cuenta/borrar-receta/' . $this->object->id()) . '" class="confirm" data-confirm="' . __('are_you_sure_delete') . '">
                            <i class="fa fa-times"></i>
                            <span>' . __('delete') . '</span>
                        </a>
                    </div>
            </div>';
    }

    public function renderRating()
    {
        return '
            <div class="rating rating_' . $this->object->get('rating') . '">
                <div class="rating_ins">
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                </div>
            </div>';
    }

    public static function introConnected()
    {
        $login = User_Login::getInstance();
        $itemsNotActive = new ListObjects('Recipe', ['where' => 'id_user=:id_user AND (active!="1" OR active IS NULL)', 'order' => 'created DESC'], ['id_user' => $login->id()]);
        $itemsActive = new ListObjects('Recipe', ['where' => 'id_user=:id_user AND active="1"', 'order' => 'created DESC'], ['id_user' => $login->id()]);
        if ($itemsNotActive->isEmpty() && $itemsActive->isEmpty()) {
            return '<div class="message">' . __('no_recipes_uploaded') . '</div>';
        }
        return '
            <div class="group_connected_wrapper">

                ' . ((!$itemsNotActive->isEmpty()) ? '
                <div class="group_connected">
                    <div class="group_connected_title">' . __('recipes_to_edit') . '</div>
                    <div class="group_connected_items">
                        ' . $itemsNotActive->showList(['function' => 'Edit']) . '
                    </div>
                </div>' : '') . '

                ' . ((!$itemsActive->isEmpty()) ? '
                <div class="group_connected">
                    <div class="group_connected_title">' . __('recipes_approved') . '</div>
                    <div class="group_connected_items">
                        ' . $itemsActive->showList(['function' => 'Edit']) . '
                    </div>
                </div>' : '') . '

            </div>';
    }

    public static function menuConnected($options = [])
    {
        return '
            <div class="group_connected_top">
                ' . ((in_array('upload', $options)) ? '
                    <a href="' . url('cuenta/subir-receta') . '" class="group_connected_insert">
                        <i class="fa fa-plus"></i>
                        <span>' . __('add') . '</span>
                    </a>
                ' : '') . '
                ' . ((in_array('list', $options)) ? '
                    <a href="' . url('cuenta/recetas') . '">
                        <i class="fa fa-list"></i>
                        <span>' . __('view_list') . '</span>
                    </a>
                ' : '') . '
            </div>';
    }

    public static function menuSide($options = [])
    {
        $items = new ListObjects('Recipe', ['where' => 'active="1"', 'order' => 'RAND()', 'limit' => 4]);
        return '
            <div class="items_side">
                <div class="items_side_title">' . __('popular_recipes') . '</div>
                <div class="items_side_items">' . $items->showList(['function' => 'Side'], $options) . '</div>
            </div>';
    }

    public static function sitemapUrls()
    {
        $items = (new Recipe)->readList(['where' => 'active="1"']);
        return Sitemap::getUrls($items);
    }

    public function renderJsonHeader()
    {
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
            'name' => $this->object->getBasicInfo(),
            'image' => $this->object->getImageUrlWebp('image', 'web'),
            'description' => $this->object->get('short_description'),
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $this->object->get('rating'),
                'ratingCount' => $this->object->get('rating') * 6,
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
            'recipeCuisine' => $this->object->label('diet'),
            'recipeCategory' => $this->object->get('id_category_object')->getBasicInfo(),
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
