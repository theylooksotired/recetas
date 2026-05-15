<?php
class Category_Ui extends Ui
{

    public function renderLink()
    {
        return $this->object->link() . ' ';
    }

    public function renderPublic()
    {
        $recipes = new ListObjects('Recipe', ['where' => 'id_category=:id_category AND active="1"', 'order' => 'rating DESC', 'limit' => '3'], ['id_category' => $this->object->id()]);
        $recipesHtml = '';
        foreach ($recipes->list as $recipe) {
            $recipe->category = $this->object;
            $recipe->loadTranslated(true);
            $recipesHtml .= $recipe->showUi('Link');
        }
        $recipeInit = (count($recipes->list) > 0) ? $recipes->list[0] : new Recipe();
        return '
            <div class="category">
                <a href="' . $this->object->url() . '">
                    <img src="' . $this->object->iconUrl() . '" alt="Recetas de '.$this->object->getBasicInfo().'" width="60" height="60"/>
                    <div class="category_title">' . $this->object->get('name') . '</div>
                </a>
                <div class="category_recipes">' . $recipesHtml . '</div>
            </div>';
    }

    public function renderIntroLink()
    {
        return '
            <div class="category_intro_link">
                <img src="' . $this->object->iconUrl() . '" alt="Recetas de '.$this->object->getBasicInfo().'" width="60" height="60"/>
                <a href="' . $this->object->url() . '">' . $this->object->get('name') . '</a>
            </div>';
    }

    public function renderIntro()
    {
        $this->object->loadMultipleValues();
        $query = 'SELECT r.*
            FROM ' . (new Recipe)->tableName . ' r
            JOIN ' . (new CategoryRecipe)->tableName . ' rc ON r.id=rc.id_recipe
            WHERE rc.id_category=' . $this->object->id() . ' AND r.active="1" ORDER BY r.title_url';
        $query = 'SELECT r.*
            FROM ' . (new Recipe)->tableName . ' r
            WHERE r.id_category=' . $this->object->id() . ' AND r.active="1" ORDER BY r.views LIMIT 6';
        $recipes = (new Recipe)->readListQuery($query);
        $categoriesIds = Category::arrayCategories();
        foreach ($recipes as $recipe) {
            $recipe->category = (isset($categoriesIds[$recipe->get('id_category')])) ? $categoriesIds[$recipe->get('id_category')] : new Category();
            $recipe->loadTranslated(true);
        }
        if (count($recipes) > 2) {
            $recipesTop = '';
            $recipesBottom = '';
            foreach ($recipes as $key => $recipe) {
                if ($key < 2) {
                    $recipesTop .= $recipe->showUi();
                } else {
                    $recipesBottom .= $recipe->showUi('Minimal');
                }
            }
            return '
                <div class="category_intro">
                    <div class="category_intro_title">
                        <img src="' . $this->object->iconUrl() . '" alt="Recetas de '.$this->object->getBasicInfo().'" width="60" height="60"/>
                        <h2 class="category_title">
                            <a href="' . $this->object->url() . '">' . $this->object->get('name') . '</a>
                        </h2>
                    </div>
                    <p class="category_intro_description">' . $this->object->get('short_description') . '</p>
                    <div class="recipes">' . $recipesTop . '</div>
                    <div class="recipes_minimal">' . $recipesBottom . '</div>
                </div>';
        }
    }

    public function renderComplete()
    {
        $titleBestRecipes = ($this->object->get('best_recipes_title') != '') ? $this->object->get('best_recipes_title') : __('best_recipes');
        $titleRestRecipes = ($this->object->get('rest_recipes_title') != '') ? $this->object->get('rest_recipes_title') : __('rest_recipes');
        $categoriesIds = Category::arrayCategories();
        $recipes = (new Recipe)->readList(['where' => 'id_category="' . $this->object->id() . '" AND active="1"', 'order' => 'views DESC', 'limit' => '34']);
        $bestRecipesHtml = '';
        $restRecipesHtml = '';
        $i = 0;
        foreach ($recipes as $recipe) {
            $recipe->category = (isset($categoriesIds[$recipe->get('id_category')])) ? $categoriesIds[$recipe->get('id_category')] : new Category();
            $recipe->loadTranslated(true);
            if ($i < 6) {
                $bestRecipesHtml .= $recipe->showUi('Best');
            } else {
                $restRecipesHtml .= $recipe->showUi('Minimal');
            }
            $i++;
        }
        $totalRecipes = (new Recipe)->countResults(['where' => 'id_category="' . $this->object->id() . '" AND active="1"']);
        $htmlIndex = ($totalRecipes > 34) ? '
            <div class="top_line">
                <a class="button" href="' . $this->object->urlIndex() . '">' . __('view_all_recipes_az') . '</a>
            </div>' : '';
        return '
            <h1>' . $this->object->get('title') . '</h1>
            <div class="recipes_description">'.$this->object->get('description').'</div>
            <h2>' . $titleBestRecipes . '</h2>
            <div class="recipes recipes_category">' . $bestRecipesHtml . '</div>
            ' . Adsense::responsive('middle') . '
            ' . (($restRecipesHtml != '') ? '
                <h2>' . $titleRestRecipes . '</h2>
                <div class="recipes_minimal">' . $restRecipesHtml . '</div>
            ' : '') . '
            ' . $htmlIndex;
    }

    public function renderIndex()
    {
        $categoriesIds = Category::arrayCategories();
        $recipes = (new Recipe)->readList(['where' => 'id_category="' . $this->object->id() . '" AND active="1"', 'order' => 'title_url']);
        $letters = [];
        foreach ($recipes as $recipe) {
            $recipe->category = (isset($categoriesIds[$recipe->get('id_category')])) ? $categoriesIds[$recipe->get('id_category')] : new Category();
            $recipe->loadTranslated(true);
            $letter = strtoupper(substr($recipe->get('title'), 0, 1));
            if (!isset($letters[$letter])) {
                $letters[$letter] = '';
            }
            $letters[$letter] .= '<li>' . $recipe->showUi('Link') . '</li>';
        }
        $html = '';
        foreach ($letters as $letter => $recipesHtml) {
            $html .= '
                <div class="recipes_letter">
                    <h2>' . $letter . '</h2>
                    <div class="recipes_links_grid">
                        <ul>' . $recipesHtml . '</ul>
                    </div>
                </div>';
        }
        return $html;
    }

    public function renderCompleteAll()
    {
        $this->object->loadMultipleValues();
        $titleBestRecipes = ($this->object->get('best_recipes_title') != '') ? $this->object->get('best_recipes_title') : __('best_recipes');
        $titleRestRecipes = ($this->object->get('rest_recipes_title') != '') ? $this->object->get('rest_recipes_title') : __('rest_recipes');
        $bestRecipesIds = [];
        foreach ($this->object->get('main_recipes') as $bestRecipe) {
            if ($bestRecipe->get('id_recipe') !='') {
                $bestRecipesIds[] = $bestRecipe->get('id_recipe');
            }
        }
        $categoriesIds = Category::arrayCategories();
        if (count($bestRecipesIds) > 0) {
            $bestRecipes = new ListObjects('Recipe', ['where' => 'id IN (' . implode(',', $bestRecipesIds) . ') AND active="1"', 'order' => 'title_url']);
            $restRecipes = new ListObjects('Recipe', ['where' => 'id_category="' . $this->object->id() . '" AND id NOT IN (' . implode(',', $bestRecipesIds) . ') AND active="1"', 'order' => 'title_url']);
            $bestRecipesHtml = '';
            foreach ($bestRecipes->list as $bestRecipe) {
                $bestRecipe->category = (isset($categoriesIds[$bestRecipe->get('id_category')])) ? $categoriesIds[$bestRecipe->get('id_category')] : new Category();
                $bestRecipe->loadTranslated(true);
                $bestRecipesHtml .= $bestRecipe->showUi('Best');
            }
            $restRecipesHtml = '';
            foreach ($restRecipes->list as $restRecipe) {
                $restRecipe->category = $this->object;
                $restRecipe->loadTranslated(true);
                $restRecipesHtml .= $restRecipe->showUi('Minimal');
            }
            return '
                <h1>' . $this->object->get('title') . '</h1>
                <div class="recipes_description">'.$this->object->get('description').'</div>
                <h2>' . $titleBestRecipes . '</h2>
                <div class="recipes recipes_category">' . $bestRecipesHtml . '</div>
                ' . Adsense::responsive('middle') . '
                <h2>' . $titleRestRecipes . '</h2>
                <div class="recipes_minimal">' . $restRecipesHtml . '</div>';
        } else {
            $restRecipes = new ListObjects('Recipe', ['where' => 'id_category="' . $this->object->id() . '" AND active="1"', 'order' => 'title_url']);
            $restRecipesHtml = '';
            foreach ($restRecipes->list as $restRecipe) {
                $restRecipe->category = $this->object;
                $restRecipe->loadTranslated(true);
                $restRecipesHtml .= $restRecipe->showUi('Minimal');
            }
            return '
                <div class="recipes_description">'.$this->object->get('description').'</div>
                <h2>' . $titleRestRecipes . '</h2>
                <div class="recipes_minimal">' . $restRecipesHtml . '</div>';
        }
    }

    public static function all()
    {
        $items = new ListObjects('Recipe', ['where' => 'active="1"', 'results' => '12']);
        return '<div class="recipes">' . $items->showListPager(['middle'=>Adsense::responsive('middle'), 'middleRepetitions'=>2]) . '</div>';
    }

    public static function intro($recipesAvoidIds = [])
    {
        $html = '';
        $categories = (new Category)->readList(['order' => 'ord']);
        $categoriesIds = Category::arrayCategories();
        foreach ($categories as $category) {
            $query = 'SELECT r.*
                FROM ' . (new Recipe)->tableName . ' r
                WHERE r.id_category=' . $category->id() . '
                AND r.active="1"
                AND r.id NOT IN (' . implode(',', $recipesAvoidIds) . ')
                ORDER BY r.views LIMIT 4';
            $recipes = (new Recipe)->readListQuery($query);
            foreach ($recipes as $recipe) {
                $recipe->category = (isset($categoriesIds[$recipe->get('id_category')])) ? $categoriesIds[$recipe->get('id_category')] : new Category();
                $recipe->loadTranslated(true);
            }
            $htmlRecipes = '';
            foreach ($recipes as $key => $recipe) {
                $htmlRecipes .= $recipe->showUi('Minimal');
            }
            $html .= '
                <div class="category_intro">
                    <div class="category_intro_title">
                        <img src="' . $category->iconUrl() . '" alt="Recetas de '.$category->getBasicInfo().'" width="60" height="60"/>
                        <h2 class="category_title">
                            <a href="' . $category->url() . '">' . $category->get('name') . '</a>
                        </h2>
                    </div>
                    <p class="category_intro_description">' . $category->get('short_description') . '</p>
                    <div class="recipes_minimal">' . $htmlRecipes . '</div>
                </div>';
        }
        return $html;
    }

    /**
     * @cache
     */
    public static function introTop()
    {
        $categories = (new Category)->readList(['order' => 'ord']);
        $categories = array_merge($categories, (new SubCategory)->readList(['order' => 'ord']));
        $categoriesHtml = '';
        foreach ($categories as $category) {
            $categoriesHtml .= $category->showUi('IntroLink');
        }
        return '<div class="categories_top">' . $categoriesHtml . '</div>';
    }
    
    public static function introSimple()
    {
        $categories = new ListObjects('Category', ['order' => 'ord']);
        return '
            <div class="categories">
                <div class="categories_title">' . __('check_our_categories') . '</div>
                <div class="categories_ins">' . $categories->showList() . '</div>
            </div>';
    }

    public static function sitemapUrls()
    {
        $items = (new Category)->readList();
        return Sitemap::getUrls($items);
    }

    /**
     * @cache
     */
    public static function menuTop()
    {
        $categories = new ListObjects('Category', ['order' => 'ord']);
        return $categories->showList(['function' => 'Link']);
    }

    public function renderJsonHeader()
    {
        $info = array("@context" => "http://schema.org/",
            "@type" => "Article",
            "mainEntityOfPage" => array("@type" => "WebPage", "@id" => $this->object->url()),
            "headline" => $this->object->getBasicInfo(),
            "image" => $this->object->getImageUrl('image', 'web'),
            "author" => array("@type" => "Organization", "name" => Parameter::code('titlePage')),
            "publisher" => array("@type" => "Organization", "name" => "Plasticwebs", "logo" => array("@type" => "ImageObject", "url" => "https://www.plasticwebs.com/plastic/visual/img/logo.jpg"), "url" => "https://www.plasticwebs.com"),
            "datePublished" => $this->object->get('publishDate'),
            "dateModified" => $this->object->get('publishDate'),
            "articleBody" => strip_tags($this->object->get('description')),
        );
        return '<script type="application/ld+json">' . json_encode($info) . '</script>';
    }

}
