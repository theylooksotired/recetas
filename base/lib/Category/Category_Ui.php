<?php
class Category_Ui extends Ui
{

    public function renderLink()
    {
        return $this->object->link() . ' ';
    }

    public function renderPublic()
    {
        $recipes = new ListObjects('Recipe', ['where' => 'id_category=:id_category AND active="1"', 'order' => 'rating DESC', 'limit'=>'3'], ['id_category' => $this->object->id()]);
        $recipeInit = (count($recipes->list) > 0) ? $recipes->list[0] : new Recipe();
        return '
            <div class="category">
                <a href="' . $this->object->url() . '">
                    <div class="category_image">
                        <div class="category_image_ins" style="background-image:url(' . $recipeInit->getImageUrl('image', 'web') . ')"></div>
                    </div>
                    <div class="category_title">' . $this->object->get('name') . '</div>
                </a>
                <div class="category_recipes">' . $recipes->showList(['function' => 'Link']) . '</div>
            </div>';
    }

    public function renderMedium()
    {
        return '
            <div class="itemMedium">
                <a href="' . $this->object->url() . '">
                    <i class="fa fa-mail-bulk"></i>
                    <div class="itemInfo">
                        <div class="itemTitle">' . $this->object->get('name') . '</div>
                        <div class="itemDescription">' . $this->object->get('shortDescription') . '</div>
                    </div>
                </a>
            </div>';
    }

    public function renderComplete()
    {
        $items = new ListObjects('Recipe', ['where' => 'id_category="' . $this->object->id() . '" AND active="1"', 'results' => '12']);
        return '<div class="recipes">' . $items->showListPager() . '</div>';
    }

    public static function getMenuItems()
    {
        return [];
    }

    public static function menuItems()
    {
        $action = (isset($_GET['action'])) ? $_GET['action'] : '';
        $items = Category_Ui::getMenuItems();
        $itemsHtml = '';
        foreach ($items as $itemValues) {
            $item = new Category($itemValues);
            $class = ($item->get('name_url') == $action) ? 'selected' : '';
            $classImportant = ($itemValues['numResults'] > 4) ? 'important' : '';
            $itemsHtml .= '
                <a href="' . $item->url() . '" class="' . $class . ' ' . $classImportant . '">
                    <i class="fa fa-angle-right"></i>
                    <span>' . $item->getBasicInfo() . ' <em>(' . $itemValues['numResults'] . ')</em></span>
                </a>';
        }
        return $itemsHtml;
    }

    public static function intro()
    {
        $categories = new ListObjects('Category', ['order' => 'ord']);
        return '
            <div class="categories">
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

    /**
     * @cache
     */
    public static function menuSide()
    {
        return '
            <nav class="menu_side">
                <div class="menu_side_title">' . __('categories') . '</div>
                <div class="menu_side_items">' . Category_Ui::menuItems() . '</div>
            </nav>';
    }

    /**
     * @cache
     */
    public static function menuAmp()
    {
        $itemsHtml = '';
        $items = Category_Ui::getMenuItems();
        foreach ($items as $itemValues) {
            $item = new Category($itemValues);
            $itemsHtml .= '
                <li>
                    <a href="' . $item->url() . '">
                        <i class="fa fa-angle-right"></i>
                        <span>' . $item->getBasicInfo() . ' <em>(' . $itemValues['numResults'] . ')</em></span>
                    </a>
                </li>';
        }
        return '
            <amp-nested-menu layout="fill">
                <ul>
                    <li>
                        <a href="' . url('') . '" class="menuHome">
                            <i class="fa fa-envelope-open-text"></i>
                            <span>' . __('home') . '</span>
                        </a>
                    </li>
                    <li>
                        <a href="' . url('articulos') . '" class="menuPosts">
                            <i class="fa fa-mail-bulk"></i>
                            <span>' . __('posts') . '</span>
                        </a>
                    </li>
                    ' . $itemsHtml . '
                </ul>
            </amp-nested-menu>';
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
