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
            <article class="recipe">
                <div class="recipe_ins">
                    <h3 class="recipe_title"><a href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitlePage() . '">' . $this->object->getBasicInfo() . '</a></h3>
                    <div class="recipe_rating">' . $this->renderRating() . '</div>
                    <div class="recipe_information">
                        <div class="recipe_image">' . $this->object->getImageWidth('image', 'small') . '</div>
                        <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                    </div>
                    <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
                </div>
            </article>';
    }

    public function renderBest()
    {
        return '
            <article class="recipe recipe_best">
                <h3 class="recipe_title"><a href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitlePage() . '">' . $this->object->getBasicInfo() . '</a></h3>
                <div class="recipe_rating">' . $this->renderRating() . '</div>
                <div class="recipe_information">
                    <div class="recipe_image">' . $this->object->getImageWidth('image', 'small') . '</div>
                    <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                </div>
                <div class="recipe_extra_info">' . $this->renderInfo() . '</div>
            </article>';
    }

    public function renderPublicSimple()
    {
        return '
            <article class="recipe_simple">
                <a class="recipe_ins" title="' . $this->object->getBasicInfoTitlePage() . '" href="' . $this->object->url() . '">
                    <div class="recipe_image">' . $this->object->getImageWidth('image', 'small') . '</div>
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
        return '
            <div class="recipe_minimal">
                <div class="recipe_ins">
                    <div class="recipe_image">' . $this->object->getImageWidth('image', 'small') . '</div>
                    <div class="recipe_rating">' . $this->renderRating() . '</div>
                    <h3><a title="' . $this->object->getBasicInfoTitlePage() . '" href="' . $this->object->url() . '">' . $this->object->getBasicInfo() . '</a></h3>
                </div>
            </div>';
    }

    public function renderTop10($options = [])
    {
        $recipe = (isset($options['recipe'])) ? $options['recipe'] : $this->object;
        $counter = (isset($options['counter'])) ? $options['counter'] : '';
        return '
            <div class="top10">
                <div class="top10_number">' . $counter  . '.</div>
                <div class="top10_recipe">' . $recipe->showUi('Best')  . '</div>
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

    public function renderComplete()
    {
        $this->object->loadTranslated();
        $nameLinkBase = Text::simpleUrl($this->object->getBasicInfo());

        // Versions
        $versions = (new RecipeVersion)->readList(['where' => 'active="1" AND id_recipe="' . $this->object->id() . '"']);
        $versionsHtml = '';
        foreach ($versions as $version) {
            $version->category = $this->object->category;
            $version->loadTranslated(true);
            $versionsHtml .= $version->showUi('Best', ['recipe' => $this->object]);
        }
        $versionsHtml = ($versionsHtml != '') ? '
            <div class="versions_list">
                <h2>' . __('other_versions_recipe') . '</h2>
                <div class="versions_list_items">' . $versionsHtml . '</div>
            </div>' : '';

        // Images
        $imageIngredients = $this->object->getImageWidth('image_ingredients', 'web');
        $imagesPreparation = '';
        $i = 1;
        foreach ($this->object->get('images') as $imagePreparation) {
            $imageAlt = $this->object->getBasicInfo() . ' - ' . __('preparation') . ' - ' . __('step') . ' ' . $i;
            $imagesPreparation .= '
                <div class="recipe_inside_image recipe_ingredients_image" id="paso' . $i . '">
                    <span class="preparation_step_number">' . __('step') . ' ' . $i . ' :</span>
                    <span class="preparation_step">' . $imagePreparation->get('label') . '</span>
                    ' . $imagePreparation->getImageWidth('image', 'web', '', false, $imageAlt) . '
                </div>';
            if ($i == 2) {
                $imagesPreparation .= Adsense::responsive('preparation');
            }
            $i++;
        }
        $imagesPreparation = ($imagesPreparation != '') ? '<div class="recipe_inside_images">' . $imagesPreparation . '</div>' : '';

        // Questions
        $questions = new ListObjects('Question', ['where' => 'published="1" AND id_recipe=:id_recipe AND language=:language', 'limit' => '30', 'order' => 'created DESC'], ['id_recipe' => $this->object->id(), 'language' => Language::active()]);
        $questionsHtml = ($questions->isEmpty()) ? '' : '
            <div class="questions_recipe_list">
                <div class="questions_recipe_list_items">' . $questions->showList(['function' => 'Recipe']) . '</div>
                ' . (($questions->count() > 10) ? '<div class="button questions_recipe_more">' . __('view_more_questions') . '</div>' : '') . '
            </div>';
        $lastAnswer = '';
        if (Session::get('answered_recipe') == $this->object->id() ) {
            $question = (new Question)->read(Session::get('answered_question'));
            $lastAnswer = ($question->id() != '') ? $question->showUi() : '';
        }
        $reviewsHtml = '';

        // Reviews
        $reviews = new ListObjects('RecipeReview', ['where' => 'active="1" AND id_recipe=:id_recipe AND language=:language', 'limit' => '5', 'order' => 'created DESC'], ['id_recipe' => $this->object->id(), 'language' => Language::active()]);
        if (!$reviews->isEmpty()) {
            $reviewsHtml = '
                <div class="reviews_recipe_list">
                    <div class="reviews_recipe_list_items">' . $reviews->showList(['function' => 'Recipe']) . '</div>
                </div>';
        }
        $reviewsThanks = '';
        if (Session::get('reviewed_recipe') == $this->object->id() ) {
            $reviewsThanks = '<div class="message message_info">' . __('thank_you_for_your_review') . '</div>';
        }
        // Video
        $videoHtml = ($this->object->get('youtube_url') != '') ? '
            <div class="recipe_video">
                <h3>' . __('video') . '</h3>
                ' . VideoHelper::show($this->object->get('youtube_url'), ['width' => '100%', 'height' => '300']) . '
            </div>' : '';
        
        // Sessions
        Session::delete('reviewed_recipe');
        Session::delete('answered_recipe');
        Session::delete('answered_question');

        return '
            <main>
                <article class="recipe_complete">
                    <div class="recipe_complete_ins post-content" id="post-container">
                        <div class="recipe_complete_info">
                            ' . $this->object->getImageWidth('image', 'web') . '
                            <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
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
                                    ' . (($imageIngredients != '') ? '<div class="recipe_inside_image recipe_ingredients_image">' . $imageIngredients . '</div>' : '') . '
                                    <div class="recipe_ingredients_ins">' . $this->renderIngredients() . '</div>
                                </div>
                                <div class="recipe_preparation">
                                    ' . (($imagesPreparation != '') ? '
                                        <h3>' . __('preparation_simple') . '</h3>
                                        ' . $imagesPreparation . '
                                    ' : '') . '
                                    <h3>' . __('preparation') . '</h3>
                                    <div class="recipe_preparation_ins">' . $this->renderPreparation() . '</div>
                                    ' . $videoHtml . '
                                    ' . $this->renderNewsletter() . '
                                </div>
                            </div>
                            <div class="rating_wrapper">
                                <h2>' . __('rating_of_recipe') . '</h2>
                                <div class="recipe_rating">' . $this->renderRating('complete') . '</div>
                            </div>
                            <div class="review_wrapper" id="review_' . $this->object->id() . '">
                                <h2>' . __('reviews_recipe') . '</h2>
                                ' . $reviewsThanks . '
                                ' . RecipeReview_Form::showPublic() . '
                                ' . $reviewsHtml . '
                            </div>
                            <div class="question_wrapper" id="question_' . $this->object->id() . '">
                                <h2>' . __('questions_recipe') . '</h2>
                                ' . Question_Form::showPublic() . '
                                ' . $lastAnswer . '
                                ' . $questionsHtml . '
                            </div>
                        </div>
                    </div>
                </article>
                ' . $versionsHtml . '
            </main>';
    }

    public function renderNewsletter()
    {
        return '
            <div class="newsletter_recipe">
                <p><strong>' . __('recipe_newsletter_title') . '</strong></p>
                <div class="newsletter_recipe_ins">
                    <p>
                        <a href="https://whatsapp.com/channel/0029Vb2gbns7YSd5Vf9Jaj0r" target="_blank" title="WhatsApp" rel="nofollow">
                            <img src="' . ASTERION_BASE_URL . 'visual/img/button_whatsapp.svg" loading="lazy" alt="WhatsApp"/>
                            <span>' . __('recipe_whatsapp_ad_simple') . '</span>
                        </a>
                    </p>
                    <p>
                        <span class="trigger_newsletter">
                            <img src="' . ASTERION_BASE_URL . 'visual/img/button_newsletter.svg" loading="lazy" alt="Newsletter"/>
                            <span>' . __('recipe_newsletter_ad_simple') . '</span>
                        </span>
                    </p>
                </div>
            </div>';
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

    public function renderInfoVersion($version, $complete = false)
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
                <span>' . $version->getTotalTimeLabel() . '</span>
            </div>
            ' . (($version->get('cooking_method') != '') ? '
            <div class="recipe_cooking_method">
                <i class="icon icon-cutlery"></i>
                <span>' . __($version->get('cooking_method')) . '</span>
            </div>
            ' : '') . '
            ' . (($version->get('servings') != '') ? '
            <div class="recipe_servings">
                <i class="icon icon-group"></i>
                <span>' . $version->getServings() . '</span>
            </div>
            ' : '') . '
            ' . (($version->get('diet') != '') ? '
            <div class="recipe_diet">
                <i class="icon icon-globe"></i>
                <span>' . __($version->get('diet')) . '</span>
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

    public function renderFixSteps()
    {
        $preparation = $this->renderPreparationParagraph();
        $content = '';
        $content .= "\n\033[1m" . $this->object->getBasicInfo() . "\033[0m\n\n";
        $content .= $preparation . "\n\n";
        $content .= "\033[1m-- Corrección --\033[0m\n\n";
        $content .= $this->object->getPreparationChatGPT() . "\n\n";
        $content .= $this->object->urlAdmin();
        return $content;
    }

    public function renderFixedSteps()
    {
        $preparation = $this->renderPreparationParagraph();
        $content = '';
        $content .= "\n\033[1m" . $this->object->getBasicInfo() . "\033[0m\n";
        $content .= "\033[1m" . $this->object->url() . "\033[0m\n\n";
        $content .= str_replace('. ', ".\n", $preparation) . "\n\n";
        $content .= $this->object->urlAdmin();
        return $content;
    }

    public function renderFixedContent()
    {
        $content = '';
        $content .= "\n\033[1m" . $this->object->getBasicInfo() . "\033[0m\n";
        $content .= "\033[1m" . $this->object->url() . "\033[0m\n\n";
        $content .= "\n\033[0;31m" . $this->object->get('title_page') . "\033[0m\n\n";
        $content .= "\n\033[0;32m" . $this->object->get('meta_description') . "\033[0m\n\n";
        $content .= $this->object->get('short_description') . "\n\n";
        $content .= $this->object->get('description') . "\n\n";
        $content .= $this->object->urlAdmin();
        return $content;
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

    public function renderRating($layout = 'simple')
    {
        $count = $this->object->get('rating_count');
        if ($count == '' || $count == 0) {
            $count = rand(30, 90);
            $this->object->persistSimple('rating_count', $count);
        }
        $ratingValue = $this->object->get('rating');
        $oldRatings = ['', '0', '1', '2', '3', '4', '5'];
        if (in_array($ratingValue, $oldRatings)) {
            $ratingValue = round(mt_rand(39, 50) / 10, 1);
            $this->object->persistSimple('rating', $ratingValue);
        }
        $ratingValueCeil = ceil($ratingValue);
        $ratingHtml = '
            <div class="rating_ins">
                <i class="icon icon-star"></i>
                <i class="icon icon-star"></i>
                <i class="icon icon-star"></i>
                <i class="icon icon-star"></i>
                <i class="icon icon-star"></i>
            </div>';
        if ($layout == 'complete') {
            return '
                <div class="rating rating_complete rating_' . $ratingValueCeil . '" data-url="' . $this->object->urlRatingModal() . '">
                    ' . $ratingHtml . '
                    <div class="rating_label">' . __('rating') . ' : ' . $ratingValue . '/5 (' . $this->object->get('rating_count') . ')</div>
                    <div class="rating_votes">
                        <div class="rating_vote rating_vote_1" data-rating="1">
                            <i class="icon icon-vote_1"></i>
                            <span class="rating_vote_label">' . __('vote_button_1') . '</span>
                        </div>
                        <div class="rating_vote rating_vote_2" data-rating="2">
                            <i class="icon icon-vote_2"></i>
                            <span class="rating_vote_label">' . __('vote_button_2') . '</span>
                        </div>
                        <div class="rating_vote rating_vote_3" data-rating="3">
                            <i class="icon icon-vote_3"></i>
                            <span class="rating_vote_label">' . __('vote_button_3') . '</span>
                        </div>
                        <div class="rating_vote rating_vote_4" data-rating="4">
                            <i class="icon icon-vote_4"></i>
                            <span class="rating_vote_label">' . __('vote_button_4') . '</span>
                        </div>
                        <div class="rating_vote rating_vote_5" data-rating="5">
                            <i class="icon icon-vote_5"></i>
                            <span class="rating_vote_label">' . __('vote_button_5') . '</span>
                        </div>
                    </div>
                </div>';
        } else {
            return '<div class="rating rating_' . $ratingValueCeil . '">' . $ratingHtml . '</div>';
        }
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

    public function renderTextSocial()
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
        $i = 1;
        foreach ($this->object->get('preparation') as $preparation) {
            $instructions[] = $i . '. ' . $preparation->get('step');
            $i++;
        }
        $content = "👩‍🍳 🍽️ 🍽️ 🍽️ 🍽️ 🍽️ 👩‍🍳\n";
        $content = mb_strtoupper($this->object->getBasicInfo(), 'UTF-8') . "\n\n";
        $content .= $this->object->get('short_description') . "\n";
        $content .= "\n\n";
        $content .= "🥒 - " . mb_strtoupper(__('ingredients'), 'UTF-8') . " - 🥒\n";
        $content .= "\n";
        $content .= implode("\n", $ingredients) . "\n";
        $content .= "\n";
        $content .= "\n\n";
        $content .= "📋 - " . mb_strtoupper(__('preparation'), 'UTF-8') . " - 📋\n";
        $content .= "\n";
        $content .= implode("\n", $instructions);
        $content .= "\n";
        $content .= "\n";
        $content .= $this->object->url();
        return $content;
    }

    public static function introTop10()
    {
        $categoriesIds = Category::arrayCategories();
        $recipes = (new Recipe)->readList(['where' => 'active="1"', 'order' => 'views DESC', 'limit' => '10']);
        $recipesAvoidIds = [];
        $htmlRecipes = '';
        foreach ($recipes as $recipe) {
            $recipesAvoidIds[] = $recipe->id();
            $recipe->category = (isset($categoriesIds[$recipe->get('id_category')])) ? $categoriesIds[$recipe->get('id_category')] : null;
            $htmlRecipes .= $recipe->showUi('Best');
        }
        $titleTop10 = (Parameter::code('meta_title_top10') != '') ? Parameter::code('meta_title_top10') : __('top10');
        return '
            <div class="top_line"></div>
            <h2>' . $titleTop10 . '</h2>
            <div class="category_intro">
                <div class="recipes">' . $htmlRecipes . '</div>
            </div>
            ' . Category_Ui::intro($recipesAvoidIds);
    }

    public static function sitemapUrls()
    {
        $items = (new Recipe)->readList(['where' => 'active="1"']);
        return Sitemap::getUrls($items);
    }

    public function label($canModify = false)
    {
        $versions = new ListObjects('RecipeVersion', ['where' => 'active="1" AND id_recipe="' . $this->object->id() . '"']);
        $numberQuestions = (new Question)->countResults(['where' => 'id_recipe="' . $this->object->id() . '"']);
        return '
            ' . parent::label($canModify) . '
            ' . (($this->object->get('redirect_force_url') != '') ? '<div class="error tiny"><strong>Redirigido a ' . $this->object->get('redirect_force_url') . '</strong></div>' : '')  . '
            ' . (($this->object->getImageUrl('image_ingredients') != '') ? '<div class="error tiny">Tiene imagenes</div>' : '')  . '
            ' . (($this->object->get('youtube_url') != '') ? '<div class="error tiny">Tiene video</div>' : '')  . '
            ' . (($numberQuestions > 0) ? '<div class="accent_alt tiny">' . $numberQuestions . ' preguntas</div>' : '') . '
            ' . ((!$versions->isEmpty()) ? '<div class="recipe_versions">' . $versions->showList(['function' => 'LinkAdmin']) . '</div>' : '');
    }

    public function renderJsonHeader()
    {
        $this->object->loadCategory();
        $dateModified = ($this->object->get('modified') != '') ? $this->object->get('modified') . 'T00:00:00-03:00' : date('Y-m-d\TH:i:sP');
        $dateCreated = ($this->object->get('created') != '') ? $this->object->get('created') . 'T00:00:00-03:00' : $dateModified;
        $ingredients = [];
        foreach ($this->object->get('ingredients') as $ingredient) {
            $ingredients[] = $ingredient->get('amount') . ' ' . __($ingredient->get('type')) . ' ' . $ingredient->get('ingredient');
        }
        $instructions = [];
        if (is_array($this->object->get('images')) && count($this->object->get('images')) > 0) {
            $i = 1;
            foreach ($this->object->get('images') as $imagePreparation) {
                $instructions[] = [
                    '@type' => 'HowToStep',
                    'name' => __('step') . ' ' . $i,
                    'text' => $imagePreparation->get('label'),
                    'image' => $imagePreparation->getImageUrl('image', 'web'),
                    'url' => $this->object->url() . '#paso' . $i
                ];
                $i++;
            }
        } else {
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
        }
        $reviews = [];
        if (is_array($this->object->get('reviews')) && count($this->object->get('reviews')) > 0) {
            $reviewsActive = [];
            foreach ($this->object->get('reviews') as $review) {
                if ($review->get('active') == '1') {
                    $reviewsActive[] = $review;
                }
            }
            foreach ($reviewsActive as $review) {
                $reviews[] = [
                    '@type' => 'Review',
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review->get('author'),
                    ],
                    'datePublished' => $review->get('created'),
                    'reviewBody' => $review->get('review'),
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review->get('rating'),
                        'bestRating' => '5',
                        'worstRating' => '1',
                    ],
                ];
            }
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
        if (count($reviews) > 0) {
            $info['review'] = $reviews;
        }
        $idYoutube = VideoHelper::idYoutube($this->object->get('youtube_url'));
        if ($idYoutube != '') {
            $info['video'] = [
                '@type' => 'VideoObject',
                'name' => $this->object->getBasicInfo(),
                'description' => $this->object->get('short_description'),
                'thumbnailUrl' => [
                    'https://img.youtube.com/vi/' . $idYoutube . '/maxresdefault.jpg'
                ],
                'contentUrl' => 'https://www.youtube.com/watch?v=' . $idYoutube,
                'embedUrl' => 'https://www.youtube.com/embed/' . $idYoutube,
                'uploadDate' => $dateCreated
            ];
        }
        return '<script type="application/ld+json">' . json_encode($info) . '</script>';
    }

}
