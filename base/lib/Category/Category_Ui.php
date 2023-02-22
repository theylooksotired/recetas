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

    public function renderComplete()
    {
        $items = new ListObjects('Recipe', ['where' => 'id_category="' . $this->object->id() . '" AND active="1"', 'results' => '12']);
        return '<div class="recipes">' . $items->showListPager() . '</div>';
    }

    public static function all()
    {
        $items = new ListObjects('Recipe', ['where' => 'active="1"', 'results' => '12']);
        return '<div class="recipes">' . $items->showListPager() . '</div>';
    }

    public static function intro()
    {
        $categories = new ListObjects('Category', ['order' => 'ord']);
        return '
            <div class="categories">
                <div class="categories_title">' . __('check_our_categories') . '</div>
                <div class="categories_ins">' . $categories->showList() . '</div>
            </div>';
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
