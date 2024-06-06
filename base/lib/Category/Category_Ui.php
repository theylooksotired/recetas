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
        $recipeInit = (count($recipes->list) > 0) ? $recipes->list[0] : new Recipe();
        return '
            <div class="category">
                <a href="' . $this->object->url() . '">
                    <img src="' . ASTERION_BASE_URL . 'visual/img/icon_' . $this->object->get('name_url') . '.svg" alt="Recetas de '.$this->object->getBasicInfo().'" width="60" height="60"/>
                    <div class="category_title">' . $this->object->get('name') . '</div>
                </a>
                <div class="category_recipes">' . $recipes->showList(['function' => 'Link']) . '</div>
            </div>';
    }

    public function renderIntro()
    {
        $this->object->loadMultipleValues();
        $query = 'SELECT r.*
            FROM ' . (new Recipe)->tableName . ' r
            JOIN ' . (new CategoryRecipe)->tableName . ' rc ON r.id=rc.id_recipe
            WHERE rc.id_category=' . $this->object->id() . ' AND r.active="1" ORDER BY r.title_url';
        $recipes = (new Recipe)->readListQuery($query);
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
                        <img src="' . ASTERION_BASE_URL . 'visual/img/icon_' . $this->object->get('name_url') . '.svg" alt="Recetas de '.$this->object->getBasicInfo().'" width="60" height="60"/>
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
        $this->object->loadMultipleValues();
        $titleBestRecipes = ($this->object->get('best_recipes_title') != '') ? $this->object->get('best_recipes_title') : __('best_recipes');
        $titleRestRecipes = ($this->object->get('rest_recipes_title') != '') ? $this->object->get('rest_recipes_title') : __('rest_recipes');
        $bestRecipesIds = [];
        foreach ($this->object->get('main_recipes') as $bestRecipe) {
            $bestRecipesIds[] = $bestRecipe->get('id_recipe');
        }
        if (count($bestRecipesIds) > 0) {
            $bestRecipes = new ListObjects('Recipe', ['where' => 'id IN (' . implode(',', $bestRecipesIds) . ') AND active="1"', 'order' => 'title_url']);
            $restRecipes = new ListObjects('Recipe', ['where' => 'id_category="' . $this->object->id() . '" AND id NOT IN (' . implode(',', $bestRecipesIds) . ') AND active="1"', 'order' => 'title_url']);
            return '
                <h1>' . $this->object->get('title') . '</h1>
                <div class="recipes_description">'.$this->object->get('description').'</div>
                <h2>' . $titleBestRecipes . '</h2>
                <div class="recipes recipes_category">' . $bestRecipes->showList(['function' => 'Best']) . '</div>
                ' . Adsense::responsive() . '
                <h2>' . $titleRestRecipes . '</h2>
                <div class="recipes_minimal">' . $restRecipes->showList(['function' => 'Minimal']) . '</div>';
        } else {
            $restRecipes = new ListObjects('Recipe', ['where' => 'id_category="' . $this->object->id() . '" AND active="1"', 'order' => 'title_url']);
            return '
                <div class="recipes_description">'.$this->object->get('description').'</div>
                <h2>' . $titleRestRecipes . '</h2>
                <div class="recipes_minimal">' . $restRecipes->showList(['function' => 'Minimal']) . '</div>';
        }
    }

    public static function all()
    {
        $items = new ListObjects('Recipe', ['where' => 'active="1"', 'results' => '12']);
        return '<div class="recipes">' . $items->showListPager(['middle'=>Adsense::responsive(), 'middleRepetitions'=>2]) . '</div>';
    }

    public static function intro()
    {
        $categories = new ListObjects('Category', ['order' => 'ord']);
        return $categories->showList(['function' => 'Intro']);
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
