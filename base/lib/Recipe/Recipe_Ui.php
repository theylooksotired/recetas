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
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        return '
            <article class="recipe">
                <div class="recipe_ins">
                    <div class="recipe_image">' . $image . '</div>
                    <div class="recipe_information">
                        <h3 class="recipe_title"><a href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitle() . '">' . $this->object->getBasicInfo() . '</a></h3>
                        <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                        <div class="recipe_rating">' . $this->renderRating() . '</div>
                        <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
                    </div>
                </div>
            </article>';
    }

    public function renderPublicSimple()
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        return '
            <article class="recipe_simple">
                <a class="recipe_ins" title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">
                    <div class="recipe_image">' . $image . '</div>
                    <div class="recipe_information">
                        <div class="recipe_rating">' . $this->renderRating() . '</div>
                        <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
                        <h2 class="recipe_title">' . $this->object->getBasicInfo() . '</h2>
                        <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                    </div>
                </a>
            </article>';
    }

    public function renderMinimal()
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        return '
            <div class="recipe_minimal">
                <div class="recipe_ins">
                    <div class="recipe_image">' . $image . '</div>
                    <h3><a title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">' . $this->object->getBasicInfo() . '</a></h3>
                </div>
            </div>';
    }

    public function renderSide($options = [])
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        return '
            <div class="post_minimal">
                <div class="post_minimal_ins">
                    <div class="post_image_amp">' . $image . '</div>
                    <h3 class="post_title"><a href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitle() . '">' . $this->object->getBasicInfo() . '</a></h3>
                </div>
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
                            ' . Adsense::amp() . '
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
        $friendSiteLink1 = '';
        if ($this->object->get('friend_links') != '') {
            $sites = @json_decode($this->object->get('friend_links'), true);
            if (is_array($sites) && count($sites) > 0) {
                $link = '<a href="' . $sites[0]['url'] . '" title="Receta de ' . $sites[0]['title'] . '">' . $sites[0]['title'] . '</a>';
                $friendSiteLink1 = '<div class="recipe_complete_link_friend">' . str_replace('#LINK', $link, __('link_friend_top')) . '</div>';
            }
            if (is_array($sites) && count($sites) > 1) {
                $link1 = '<a href="' . $sites[1]['url'] . '" title="Receta de ' . $sites[1]['title'] . '">' . $sites[1]['title'] . '</a>';
                $link2 = '<a href="' . $sites[2]['url'] . '" title="Receta de ' . $sites[2]['title'] . '">' . $sites[2]['title'] . '</a>';
                $friendSiteLink2 = '
                    <div class="recipe_complete_link_friend_bottom">
                        ' . str_replace('#LINK2', $link2, str_replace('#LINK1', $link1, __('link_friend_bottom'))) . '
                    </div>';
            }
        }
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'web') : $this->object->getImageWidth('image', 'web');
        return '
            <article class="recipe_complete">
                <div class="recipe_complete_ins post-content" id="post-container">
                    <div class="recipe_complete_info">
                        ' . $image . '
                        <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                    </div>
                    <div class="recipe_rating">' . $this->renderRating() . '</div>
                    <div class="recipe_extra_info">' . $this->renderInfo(true) . '</div>
                    ' . $this->object->get('description') . '
                    ' . $friendSiteLink1 . '
                    <div class="recipe_wrapper_all">
                        <div class="recipe_wrapper_all_left">' . Adsense::amp() . '</div>
                        <div class="recipe_wrapper_all_right">
                            <div class="recipe_wrapper">
                                <div class="recipe_ingredients">
                                    <h2>' . __('ingredients') . '</h2>
                                    <div class="recipe_ingredients_ins">' . $this->renderIngredients() . '</div>
                                </div>
                                <div class="recipe_preparation">
                                    <h2>' . __('preparation') . '</h2>
                                    <div class="recipe_preparation_ins">' . $this->renderPreparation() . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ' . Adsense::amp() . '
                    ' . $friendSiteLink2 . '
                </div>
            </article>
            ' . $otherVersions . '
            ' . (($this->object->get('description_bottom')!='') ? '<div class="recipe_complete_bottom">' . $this->object->get('description_bottom') . '</div>' : '') . '
            <div class="item_complete_share">
                <div class="item_complete_share_title">' . __('help_us_sharing') . '</div>
                ' . $this->share(['share' => [
            ['key' => 'facebook', 'icon' => '<i class="icon icon-facebook"></i>'],
            ['key' => 'twitter', 'icon' => '<i class="icon icon-twitter"></i>'],
        ]]) . '
                ' . Navigation_Ui::facebookComments($this->object->url()) . '
            </div>';
    }

    public function renderPreloadImage()
    {
        return '<link rel="preload" as="image" href="' . $this->object->getImageUrlWebp('image', 'web') . '">';
    }

    public function renderInfo($complete = false)
    {
        return '
            ' . (($complete) ? '
                <a class="recipe_cook_category" href="' . $this->object->get('id_category_object')->url() . '">
                    <i class="icon icon-cutlery"></i>
                    <span>' . $this->object->get('id_category_object')->getBasicInfo() . '</span>
                </a>
            ' : '
                <div class="recipe_cook_category">
                    <i class="icon icon-cutlery"></i>
                    <span>' . $this->object->get('id_category_object')->getBasicInfo() . '</span>
                </div>
            ') . '
            ' . (($this->object->get('cook_time') != '') ? '
            <div class="recipe_cook_time">
                <i class="icon icon-time"></i>
                <span>' . __($this->object->get('cook_time')) . '</span>
            </div>
            ' : '') . '
            ' . (($this->object->get('cooking_method') != '') ? '
            <div class="recipe_cooking_method">
                <i class="icon icon-cutlery"></i>
                <span>' . __($this->object->get('cooking_method')) . '</span>
            </div>
            ' : '') . '
            ' . (($this->object->get('servings') != '') ? '
            <div class="recipe_servings">
                <i class="icon icon-group"></i>
                <span>' . $this->object->getServings() . '</span>
            </div>
            ' : '') . '
            ' . (($this->object->get('diet') != '') ? '
            <div class="recipe_diet">
                <i class="icon icon-globe"></i>
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
        $posts = new ListObjects('Post', ['where' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE)', 'order' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE) DESC', 'limit' => '6'], ['match' => $this->object->getBasicInfo()]);
        $postsExtra = ($posts->count() < 6) ? new ListObjects('Post', array('order' => 'RAND()', 'limit' => 6 - $posts->count())) : null;
        $recipes = new ListObjects('Recipe', ['where' => 'id!=:id AND id_category=:id_category AND active="1"', 'limit' => 6], ['id' => $this->object->id(), 'id_category' => $this->object->get('id_category')]);
        return '<div class="related">
                    <div class="related_block">
                        <h2 class="related_title">' . __('other_recipes') . '</h2>
                        <div class="recipes_minimal">' . $recipes->showList(['function' => 'Minimal']) . '</div>
                    </div>
                    <div class="related_block">
                        <h2 class="related_title">' . __('other_posts_related') . ' <strong>' . $this->object->getBasicInfo() . '</strong></h2>
                        <div class="posts">
                            ' . $posts->showList(['function' => 'PublicSimple']) . '
                            ' . (($postsExtra) ? $postsExtra->showList(['function' => 'PublicSimple']) : '') . '
                        </div>
                    </div>
                </div>';
    }

    public function renderEdit()
    {
        $html = '
            <div class="itemEditInsWrapper">
                <div class="itemEditImage">' . $this->object->getImageWidth('image', 'small') . '</div>
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
                            <i class="icon icon-delete"></i>
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
                    <i class="icon icon-star"></i>
                    <i class="icon icon-star"></i>
                    <i class="icon icon-star"></i>
                    <i class="icon icon-star"></i>
                    <i class="icon icon-star"></i>
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
                        <i class="icon icon-plus"></i>
                        <span>' . __('add') . '</span>
                    </a>
                ' : '') . '
                ' . ((in_array('list', $options)) ? '
                    <a href="' . url('cuenta/recetas') . '">
                        <i class="icon icon-list"></i>
                        <span>' . __('view_list') . '</span>
                    </a>
                ' : '') . '
            </div>';
    }

    public static function menuSide($options = [])
    {
        $items = new ListObjects('Recipe', ['where' => 'active="1"', 'order' => 'RAND()', 'limit' => 3]);
        return '
            ' . Adsense::amp() . '
            <div class="items_side">
                <h2 class="items_side_title">' . __('popular_recipes') . '</h2>
                <div class="items_side_items">' . $items->showList(['function' => 'Side'], $options) . '</div>
                <div class="items_side_button"><a href="' . url('articulos') . '">' . __('view_all_recipes') . '</a></div>
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
            'image' => $this->object->getImageUrl('image', 'web'),
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
