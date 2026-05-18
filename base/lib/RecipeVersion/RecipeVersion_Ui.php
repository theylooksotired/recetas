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

    public function renderPublic($options = [])
    {
        $recipe = (isset($options['recipe'])) ? $options['recipe'] : null;
        $rating = ($recipe) ? $recipe->showUi('Rating') : null;
        return '
            <article class="recipe">
                <div class="recipe_ins">
                    <h3 class="recipe_title"><a href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitlePage() . '">' . $this->object->getBasicInfo() . '</a></h3>
                    <div class="recipe_rating">' . $rating . '</div>
                    <div class="recipe_information">
                        <div class="recipe_image">' . $this->object->getImageWidth('image', 'small') . '</div>
                        <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                    </div>
                    <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
                </div>
            </article>';
    }

    public function renderBest($options = [])
    {
        $recipe = (isset($options['recipe'])) ? $options['recipe'] : null;
        $rating = ($recipe) ? $recipe->showUi('Rating') : null;
        return '
            <article class="recipe recipe_best">
                <h3 class="recipe_title"><a href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitlePage() . '">' . $this->object->getBasicInfo() . '</a></h3>
                <div class="recipe_rating">' . $rating . '</div>
                <div class="recipe_information">
                    <div class="recipe_image">' . $this->object->getImageWidth('image', 'small') . '</div>
                    <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                </div>
                <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
            </article>';
    }

    public function renderPublicSimple($options = [])
    {
        $recipe = (isset($options['recipe'])) ? $options['recipe'] : null;
        $rating = ($recipe) ? $recipe->showUi('Rating') : null;
        return '
            <article class="recipe_simple">
                <a class="recipe_ins" title="' . $this->object->getBasicInfoTitlePage() . '" href="' . $this->object->url() . '">
                    <div class="recipe_image">' . $this->object->getImageWidth('image', 'small') . '</div>
                    <div class="recipe_information">
                        <div class="recipe_rating">' . $rating . '</div>
                        <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
                        <h2 class="recipe_title">' . $this->object->getBasicInfo() . '</h2>
                        <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                    </div>
                </a>
            </article>';
    }

    public function renderMinimal($options = [])
    {
        $recipe = (isset($options['recipe'])) ? $options['recipe'] : null;
        $rating = ($recipe) ? $recipe->showUi('Rating') : null;
        return '
            <div class="recipe_minimal">
                <div class="recipe_ins">
                    <div class="recipe_image">' . $this->object->getImageWidth('image', 'small') . '</div>
                    <div class="recipe_rating">' . $rating . '</div>
                    <h3><a title="' . $this->object->getBasicInfoTitlePage() . '" href="' . $this->object->url() . '">' . $this->object->getBasicInfo() . '</a></h3>
                </div>
            </div>';
    }

    public function renderTranslationLink()
    {
        if (isset($this->object->translation_url) && $this->object->translation_url != '') {
            return '
                <div class="language_link">
                    <a href="' . $this->object->translation_url . '" target="_blank">
                        <img src="' . ASTERION_BASE_URL . 'visual/img/flag_' . Translate_Controller::translateTo() . '.svg" loading="lazy" alt="' . __('read_in_' . Translate_Controller::translateTo()) . '"/>
                        <span>' . __('read_in_' . Translate_Controller::translateTo()) . '</span>
                    </a>
                </div>';
        }
    }

    public static function sitemapUrls()
    {
        $items = (new RecipeVersion)->readList(['where' => 'active="1"']);
        return Sitemap::getUrls($items);
    }

    public function renderComplete()
    {
        $this->object->loadRecipe();
        $this->object->loadCategory();
        $nameLinkBase = Text::simpleUrl($this->object->getBasicInfo());

        return '
            <main>
                <article class="recipe_complete">
                    <div class="recipe_complete_ins post-content" id="post-container">
                        <div class="recipe_complete_info">
                            ' . $this->object->getImageWidth('image', 'web') . '
                            <p class="recipe_short_description">
                                ' . $this->object->get('short_description') . '<br/><br/>
                                ' . __('original_recipe_in') . ' ' . $this->object->recipe->link() . '
                            </p>
                        </div>
                        ' . Adsense::responsive('middle') . '
                        ' . $this->object->get('description') . '
                        <div class="recipe_wrapper">
                            <h2 id="' . $nameLinkBase .'" name="' . $nameLinkBase .'" class="anchor_top">' . $this->object->getBasicInfo() . '</h2>
                            <div class="recipe_extra_info">' . $this->renderInfo(true) . '</div>
                            <div class="recipe_share_top">
                                <div class="recipe_share_top_title">' . __('share_recipe') . '</div>
                                ' . $this->share(['share' => [
                                    ['key' => 'whatsapp', 'icon' => '<i class="icon icon-whatsapp"></i>'],
                                    ['key' => 'facebook', 'icon' => '<i class="icon icon-facebook"></i>'],
                                    ['key' => 'print', 'icon' => '<i class="icon icon-print"></i>'],
                                ]]) . '
                            </div>
                            ' . Adsense::responsive('ingredients') . '
                            <div class="recipe_wrapper_ins">                            
                                <div class="recipe_ingredients">
                                    <h3>' . __('ingredients') . '</h3>
                                    <div class="recipe_ingredients_ins">' . $this->renderIngredients() . '</div>
                                </div>
                                <div class="recipe_preparation">
                                    <h3>' . __('preparation') . '</h3>
                                    <div class="recipe_preparation_ins">' . $this->renderPreparation() . '</div>
                                    ' . $this->object->recipe->showUi('Newsletter') . '
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </main>';
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
                <p class="preparation" id="paso' . $i . '">
                    <span class="preparation_step_number">' . __('step') . ' ' . $i . ' :</span>
                    <span class="preparation_step">' . $preparation->get('step') . '</span>
                    ' . $preparation->getImageAmpWebp('image', 'web') . '
                </p>';
            $i++;
            if ($i == 3) {
                $html .= Adsense::responsive('preparation');
            }
        }
        return $html;
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

    public function renderInfo($complete = false)
    {
        $this->object->loadCategory();
        return '
            ' . (($complete) ? '
                <a class="recipe_cook_category" href="' . $this->object->category->url() . '">
                    <i class="icon icon-cutlery"></i>
                    <span>' . $this->object->category->getBasicInfo() . '</span>
                </a>
            ' : '
                <div class="recipe_cook_category">
                    <i class="icon icon-cutlery"></i>
                    <span>' . $this->object->category->getBasicInfo() . '</span>
                </div>
            ') . '
            <div class="recipe_cook_time">
                <i class="icon icon-time"></i>
                <span>' . $this->object->getTotalTimeLabel() . '</span>
            </div>
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

    public function renderPreloadImage()
    {
        $imageUrl = $this->object->getImageUrlWebp('image', 'web');
        $imageUrl = ($imageUrl != '') ? $imageUrl : $this->object->getImageUrl('image', 'web');
        return '<link rel="preload" as="image" href="' . $imageUrl . '">';
    }

    public function renderAlternateUrl()
    {
        $this->object->loadTranslation();
        if (isset($this->object->translation_url) && $this->object->translation_url != '') {
            return '<link rel="alternate" hreflang="' . Translate_Controller::translateTo() . '" href="' . $this->object->translation_url . '"/>';
        }
    }

    public function renderRelated()
    {
        $posts = new ListObjects('Post', ['where' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE)', 'order' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE) DESC', 'limit' => '6'], ['match' => $this->object->getBasicInfo()]);
        $postsExtra = ($posts->count() < 6) ? new ListObjects('Post', ['order' => 'views DESC', 'limit' => 6 - $posts->count()]) : null;
        $categoriesIds = Category::arrayCategories();
        // First search for related
        $search = Text::simpleUrl($this->object->getBasicInfo(), ' ');
        $recipesSearch = new ListObjects('Recipe', [
            'where' => 'id!="' . $this->object->id() . '" AND active="1" AND MATCH (title, title_url, short_description) AGAINST ("' . $search . '" IN BOOLEAN MODE)',
            'order' => 'MATCH (title, title_url) AGAINST ("' . $search . '") DESC',
            'limit' => '8',
        ]);
        $recipesSearchHtml = '';
        foreach ($recipesSearch->list as $recipeSearch) {
            $recipeSearch->category = (isset($categoriesIds[$recipeSearch->get('id_category')])) ? $categoriesIds[$recipeSearch->get('id_category')] : null;
            $recipeSearch->loadTranslated(true);
            $recipesSearchHtml .= $recipeSearch->showUi('Minimal');
        }
        // Search more if there are less than 8
        $recipesSearchMoreHtml = '';
        if ($recipesSearch->count() < 8) {
            $idsNotIn = [$this->object->id()];
            foreach ($recipesSearch->list as $recipeSearch) {
                $idsNotIn[] = $recipeSearch->id();
            }
            $recipesSearchMore = new ListObjects('Recipe', [
                'where' => 'id_category=:id_category AND active="1" AND id NOT IN (' . implode(',', $idsNotIn) . ')',
                'limit' => (8 - $recipesSearch->count()),
                'order' => 'id'
            ], [
                'id_category' => $this->object->get('id_category')
            ]);
            foreach ($recipesSearchMore->list as $recipeSearchMore) {
                $recipeSearchMore->category = (isset($categoriesIds[$recipeSearchMore->get('id_category')])) ? $categoriesIds[$recipeSearchMore->get('id_category')] : null;
                $recipeSearchMore->loadTranslated(true);
                $recipesSearchMoreHtml .= $recipeSearchMore->showUi('Minimal');
            }
        }
        return '
            <div class="related">
                <div class="related_block">
                    <h2 class="related_title">' . __('similar_recipes') . '</h2>
                    <div class="recipes_minimal">
                        ' . $recipesSearchHtml . '
                        ' . $recipesSearchMoreHtml . '
                    </div>
                </div>
                <div class="related_block">
                    <h2 class="related_title">' . str_replace('#TITLE#', strtolower($this->object->getBasicInfo()), __('posts_related_to')) . '</h2>
                    <div class="posts">
                        ' . $posts->showList(['function' => 'PublicSimple']) . '
                        ' . (($postsExtra) ? $postsExtra->showList(['function' => 'PublicSimple']) : '') . '
                    </div>
                </div>
            </div>';
    }

    public function renderText()
    {
        $this->object->loadMultipleValues();
        $ingredients = [];
        foreach ($this->object->get('ingredients') as $ingredient) {
            if ($ingredient->get('amount') != '') {
                $ingredientType = (($ingredient->get('type') != 'unit' && $ingredient->get('type') != '') ? strtolower((intval($ingredient->get('amount')) > 1) ? __($ingredient->get('type') . '_plural') : __($ingredient->get('type'))) . ' ' . __('of') : '');
                $ingredients[] = $ingredient->get('amount') . ' ' . $ingredientType . ' ' . $ingredient->get('ingredient');
            } else {
                $ingredients[] = $ingredient->get('ingredient');
            }
        }
        $instructions = [];
        foreach ($this->object->get('preparation') as $preparation) {
            $instructions[] = $preparation->get('step') . "\n";
        }
        return $this->object->getBasicInfo() . '
            ' . __('ingredients') . '
            ' . implode("\n", $ingredients) . '
            ' . __('preparation') . '
            ' . implode("\n", $instructions);
    }

    public function renderJsonHeader()
    {
        $this->object->loadRecipe();
        $this->object->loadCategory();
        $dateModified = ($this->object->get('modified') != '') ? $this->object->get('modified') . 'T00:00:00-03:00' : date('Y-m-d\TH:i:sP');
        $dateCreated = ($this->object->get('created') != '') ? $this->object->get('created') . 'T00:00:00-03:00' : $dateModified;
        $ingredients = [];
        foreach ($this->object->get('ingredients') as $ingredient) {
            $ingredients[] = $ingredient->get('amount') . ' ' . __($ingredient->get('type')) . ' ' . $ingredient->get('ingredient');
        }
        $instructions = [];
        $i = 1;
        foreach ($this->object->get('preparation') as $preparation) {
            $instructions[] = [
                '@type' => 'HowToStep',
                'name' => __('step') . ' ' . $i,
                'text' => $preparation->get('step'),
                'url' => $this->object->url() . '#paso' . $i
            ];
            $i++;
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
                'ratingCount' => $this->object->get('rating_count'),
                'bestRating' => '5',
                'worstRating' => '1',
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
            'datePublished' => $dateCreated,
            'dateModified' => $dateModified,
            'recipeCuisine' => $this->object->label('diet'),
            'recipeCategory' => $this->object->category->getBasicInfo(),
            'recipeYield' => $this->object->getServings(),
            'recipeIngredient' => $ingredients,
            'recipeInstructions' => $instructions,
        ];
        $info['cookTime'] = $this->object->getCookTime();
        $info['prepTime'] = $this->object->getPrepTime();
        $info['totalTime'] = $this->object->getTotalTime();
        if ($this->object->get('cooking_method') != '') {
            $info['cookingMethod'] = $this->object->get('cooking_method');
        }
        return '<script type="application/ld+json">' . json_encode($info) . '</script>';
    }

}
