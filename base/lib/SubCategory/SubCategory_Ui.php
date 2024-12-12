<?php
class SubCategory_Ui extends Ui
{

    public function renderLink()
    {
        return '<a href="' . $this->object->url() . '" class="menu_subcategory">' . $this->object->getBasicInfo() . '</a> ';
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

    public function renderIntroLink()
    {
        $image = ASTERION_BASE_FILE . 'visual/img/icon_' . $this->object->get('icon') . '.svg';
        if (!file_exists($image)) {
            $image = ASTERION_BASE_FILE . 'visual/img/icon_default.svg';
        }
        return '
            <div class="category_intro_link">
                <img src="' . str_replace(ASTERION_BASE_FILE, ASTERION_BASE_URL, $image) . '" alt="Recetas de '.$this->object->getBasicInfo().'" width="60" height="60"/>
                <a href="' . $this->object->url() . '">' . $this->object->get('name') . '</a>
            </div>';
    }

    public function renderComplete()
    {
        $recipes = $this->object->getRecipes();
        $titleBestRecipes = ($this->object->get('best_recipes_title') != '') ? $this->object->get('best_recipes_title') : __('best_recipes');
        $titleRestRecipes = ($this->object->get('rest_recipes_title') != '') ? $this->object->get('rest_recipes_title') : __('rest_recipes');
        $recipesBestHtml = '';
        $recipesRestHtml = '';
        $recipesBest = array_slice($recipes, 0, 12);
        $recipesRest = array_slice($recipes, 8);
        foreach ($recipesBest as $recipe) {
            $recipesBestHtml .= $recipe->showUi('Best');
        }
        foreach ($recipesRest as $recipe) {
            $recipesRestHtml .= $recipe->showUi('minimal');
        }
        return '
            <h1>' . $this->object->get('title') . '</h1>
            <div class="recipes_description">'.$this->object->get('description').'</div>
            <h2>' . $titleBestRecipes . '</h2>
            <div class="recipes recipes_category">' . $recipesBestHtml . '</div>
            ' . (($recipesRestHtml != '') ? '
                <h2>' . $titleRestRecipes . '</h2>
                <div class="recipes_minimal">' . $recipesRestHtml . '</div>
            ' : '') . '';
    }

    public static function all()
    {
        $items = new ListObjects('Recipe', ['where' => 'active="1"', 'results' => '12']);
        return '<div class="recipes">' . $items->showListPager(['middle'=>Adsense::midContent(), 'middleRepetitions'=>2]) . '</div>';
    }

    public static function intro()
    {
        $categories = new ListObjects('SubCategory', ['order' => 'ord']);
        return $categories->showList(['function' => 'Intro']);
    }
    
    public static function introSimple()
    {
        $categories = new ListObjects('SubCategory', ['order' => 'ord']);
        return '
            <div class="categories">
                <div class="categories_title">' . __('check_our_categories') . '</div>
                <div class="categories_ins">' . $categories->showList() . '</div>
            </div>';
    }

    public static function sitemapUrls()
    {
        $items = (new SubCategory)->readList();
        return Sitemap::getUrls($items);
    }

    /**
     * @cache
     */
    public static function menuTop()
    {
        $categories = new ListObjects('SubCategory', ['order' => 'ord']);
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
