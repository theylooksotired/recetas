<?php
/**
 * @class PostUi
 *
 * This class manages the UI for the Post objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Post_Ui extends Ui
{

    public function renderPublic()
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        $shortDescription = ($this->object->get('meta_description') != '') ? $this->object->get('meta_description') : $this->object->get('short_description');
        return '
            <div class="recipe">
                <a class="recipe_ins" href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitle() . '">
                    <div class="recipe_image">' . $image . '</div>
                    <div class="recipe_information">
                        <h2 class="recipe_title">' . $this->object->getBasicInfo() . '</h2>
                        <p class="recipe_short_description">
                            <i class="icon icon-date"></i>
                            <span>' . Date::sqlText($this->object->get('publish_date')) . '</span>
                        </p>
                        <p class="recipe_short_description">' . $shortDescription . '</p>
                    </div>
                </a>
            </div>';
    }

    public function renderMedium()
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        $shortDescription = ($this->object->get('meta_description') != '') ? $this->object->get('meta_description') : $this->object->get('short_description');
        return '
            <div class="post">
                <a class="post_ins" title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">
                    <div class="post_image">' . $image . '</div>
                    <div class="post_information">
                        <div class="post_title">' . $this->object->getBasicInfo() . '</div>
                        <div class="post_short_description">' . $shortDescription . '</div>
                        <div class="post_date">
                            <i class="icon icon-date"></i>
                            <span>' . Date::sqlText($this->object->get('publish_date')) . '</span>
                        </div>
                        <div class="post_reading_time">
                            <i class="icon icon-time"></i>
                            <span>' . Text::readingTime($this->object->get('description')) . '</span>
                        </div>
                    </div>
                </a>
            </div>';
    }

    public function renderPublicSimple()
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        $shortDescription = ($this->object->get('meta_description') != '') ? $this->object->get('meta_description') : $this->object->get('short_description');
        return '
            <div class="post_simple">
                <div class="post_ins">
                    <div class="post_image">' . $image . '</div>
                    <div class="post_information">
                        <h3 class="post_title">
                            <a title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">' . $this->object->getBasicInfo() . '</a>
                        </h3>
                        <div class="post_short_description">' . $shortDescription . '</div>
                    </div>
                </div>
            </div>';
    }

    public function renderIntro($options = [])
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        $shortDescription = ($this->object->get('meta_description') != '') ? $this->object->get('meta_description') : $this->object->get('short_description');
        return '
            <div class="post">
                <div class="post_ins">
                    <div class="post_image post_image_simple">' . $image . '</div>
                    <div class="post_information">
                        <h3 class="post_title">' . $this->object->link() . '</h3>
                        <div class="post_short_description">' . $shortDescription . '</div>
                        <div class="post_date">
                            <i class="icon icon-date"></i>
                            <span>' . Date::sqlText($this->object->get('publish_date')) . '</span>
                        </div>
                    </div>
                </div>
            </div>';
    }

    public function renderIntroTop($options = [])
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'web') : $this->object->getImageWidth('image', 'web');
        $shortDescription = ($this->object->get('meta_description') != '') ? $this->object->get('meta_description') : $this->object->get('short_description');
        return '
            <div class="post_top">
                <a class="post_top_ins" title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">
                    <div class="post_image post_image_simple">' . $image . '</div>
                    <div class="post_information">
                        <div class="post_title">' . $this->object->getBasicInfo() . '</div>
                        <div class="post_short_description">' . $shortDescription . '</div>
                    </div>
                </a>
            </div>';
    }

    public function renderPreloadImage()
    {
        return '<link rel="preload" as="image" href="' . $this->object->getImageUrlWebp('image', 'web') . '">';
    }

    public function renderSide($options = [])
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        $image = ($mode == 'amp') ? $this->object->getImageAmpWebp('image', 'small') : $this->object->getImageWidth('image', 'small');
        return '
            <div class="post_minimal">
                <div class="post_minimal_ins">
                    <div class="post_image">' . $image . '</div>
                    <h3 class="post_title">
                        <a href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitle() . '">' . $this->object->getBasicInfo() . '</a>
                    </h3>
                </div>
            </div>';
    }

    public function renderComplete()
    {
        $this->object->loadMultipleValues();
        $share = $this->share(['share' => [
            ['key' => 'facebook', 'icon' => '<i class="icon icon-facebook"></i>'],
            ['key' => 'twitter', 'icon' => '<i class="icon icon-twitter"></i>'],
        ]]);
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        // Text
        $dom = new DOMDocument();
        $dom->loadHTML('<html><body>' . mb_convert_encoding($this->object->get('description'), 'HTML-ENTITIES', 'UTF-8') . '</body></html>');
        $xpath = new DOMXPath($dom);
        $paragraphs = [];
        foreach ($xpath->query('/html/body/*') as $node) {
            $paragraphs[] = $dom->saveHTML($node);
        }
        $images = [];
        foreach ($this->object->get('images') as $item) {
            if ($item->get('id_recipe') != '') {
                $recipe = (new Recipe)->read($item->get('id_recipe'));
                $images[] = $recipe->showUi();
            } else {
                $images[] = '
                    <figure class="post_image">
                        ' . $item->getImageWidth('image', 'web') . '
                        ' . (($item->get('title') != '') ? '<figcaption>' . $item->get('title') . '</figcaption>' : '') . '
                    </figure>';
            }
        }
        $middleItems = (count($images) > 0) ? ceil(count($paragraphs) / count($images)) : 0;
        $paragraphsResult = [];
        foreach ($paragraphs as $index => $paragraph) {            
            if ($middleItems > 0 && ($index + 1) % $middleItems == 0) {
                $paragraphsResult[] = array_shift($images);
            }
            $paragraphsResult[] = $paragraph;
        }
        $paragraphsResult = array_merge($paragraphsResult, $images);
        $text = implode('', $paragraphsResult);
        return '
            <article class="post_complete">
                <div class="post_complete_ins post-content" id="post-container">
                    <div class="post_short_description">' . nl2br($this->object->get('short_description')) . '</div>
                    <div class="post_short_info">
                        <div class="post_short_info_left">
                            <div class="post_date">
                                <i class="icon icon-date"></i>
                                <span>' . Date::sqlText($this->object->get('publish_date')) . '</span>
                            </div>
                            <div class="post_reading_time">
                                <i class="icon icon-time"></i>
                                <span>' . Text::readingTime($this->object->get('description')) . '</span>
                            </div>
                        </div>
                        <div class="post_short_info_right">
                        ' . $share . '
                        </div>
                    </div>
                    <div class="editorial">
                        <figure class="post_image">
                            ' . $this->object->getImageWidth('image', 'web') . '
                            <figcaption>' . $this->object->getBasicInfo() . '</figcaption>
                        </figure>
                        ' . $text . '
                    </div>
                </div>
            </article>
            ' . Adsense::responsive() . '
            <div class="item_complete_share">
                <h2 class="item_complete_share_title">' . __('help_us_sharing') . '</h2>
                ' . $share . '
                ' . Navigation_Ui::facebookComments($this->object->url()) . '
            </div>';
    }

    public function renderRelated()
    {
        $posts = new ListObjects('Post', ['where' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE)', 'order' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE) DESC AND id != :id', 'limit' => '6'], ['match' => $this->object->getBasicInfo(), 'id' => $this->object->id()]);
        if ($posts->count() < 6) {
            $posts = new ListObjects('Post', ['where' => 'id!=:id AND publish_date<=NOW() AND active="1"', 'limit' => 6], ['id' => $this->object->id()]);
        }
        $recipes = new ListObjects('Recipe', ['where' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE)', 'order' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE) DESC', 'limit' => '6'], ['match' => $this->object->getBasicInfo()]);
        return '
            <div class="related">
                ' . ((!$recipes->isEmpty()) ? '
                <div class="related_block">
                    <h2 class="related_title">' . __('other_recipes') . '</h2>
                    <div class="recipes_minimal">' . $recipes->showList(['function' => 'Minimal']) . '</div>
                </div>
                ' : '') . '
                <div class="related_block">
                    <h2 class="related_title">' . __('other_posts') . '</h2>
                    <div class="posts">' . $posts->showList(['function' => 'PublicSimple']) . '</div>
                </div>
            </div>';
    }

    // Overwrite this function for the public form
    public function linkDeleteImage($valueFile)
    {
        return url('cuenta/borrar-imagen-articulo/' . $this->object->id());
    }

    public static function introTop($options = [])
    {
        $items = (isset($options['items'])) ? $options['items'] : new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'publish_date DESC', 'limit' => '3']);
        return '<div class="posts_top_wrapper">' . $items->showList(['function' => 'IntroTop']) . '</div>';
    }

    public static function intro($options = [])
    {
        $items = (isset($options['items'])) ? $options['items'] : new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'publish_date DESC', 'limit' => '6']);
        return '
            <div class="posts_intro">
                <h2 class="posts_intro_title">' . __('last_posts') . '</h2>
                <div class="posts_intro_items">' . $items->showList(['function' => 'Intro']) . '</div>
                <div class="posts_intro_button">
                    <a href="' . url('articulos') . '">' . __('view_all_posts') . '</a>
                </div>
            </div>';
    }

    public static function all()
    {
        $title_page = (Parameter::code('meta_title_page_posts') != '') ? Parameter::code('meta_title_page_posts') : __('posts_list');
        $items = new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'publish_date DESC']);
        return '
            <h1>' . $title_page . '</h1>
            <div class="posts_grid">' . $items->showList(['function' => 'PublicSimple']) . '</div>';
    }

    public static function menuSide($options = [])
    {
        $items = new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'RAND()', 'limit' => 3]);
        return '
            ' . Adsense::responsive() . '
            <div class="items_side">
                <h2 class="items_side_title">' . __('last_posts') . '</h2>
                <div class="items_side_items">' . $items->showList(['function' => 'Side'], $options) . '</div>
                <div class="items_side_button"><a href="' . url('articulos') . '">' . __('view_all_posts') . '</a></div>
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
                    <a href="' . url('cuenta/editar-articulo/' . $this->object->id()) . '">' . $html . '</a>
                </div>
                    <div class="itemEditDelete">
                        <a href="' . url('cuenta/borrar-articulo/' . $this->object->id()) . '" class="confirm" data-confirm="' . __('are_you_sure_delete') . '">
                            <i class="icon icon-delete"></i>
                            <span>' . __('delete') . '</span>
                        </a>
                    </div>
            </div>';
    }

    public static function introConnected()
    {
        $login = User_Login::getInstance();
        $itemsNotActive = new ListObjects('Post', ['where' => 'id_user=:id_user AND (active!="1" OR active IS NULL)', 'order' => 'created DESC'], ['id_user' => $login->id()]);
        $itemsActive = new ListObjects('Post', ['where' => 'id_user=:id_user AND active="1"', 'order' => 'created DESC'], ['id_user' => $login->id()]);
        if ($itemsNotActive->isEmpty() && $itemsActive->isEmpty()) {
            return '<div class="message">' . __('no_posts_uploaded') . '</div>';
        }
        return '
            <div class="group_connected_wrapper">

                ' . ((!$itemsNotActive->isEmpty()) ? '
                <div class="group_connected">
                    <div class="group_connected_title">' . __('posts_to_edit') . '</div>
                    <div class="group_connected_items">
                        ' . $itemsNotActive->showList(['function' => 'Edit']) . '
                    </div>
                </div>' : '') . '

                ' . ((!$itemsActive->isEmpty()) ? '
                <div class="group_connected">
                    <div class="group_connected_title">' . __('post_approved') . '</div>
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
                    <a href="' . url('cuenta/subir-articulo') . '" class="group_connected_insert">
                        <i class="icon icon-plus"></i>
                        <span>' . __('add') . '</span>
                    </a>
                ' : '') . '
                ' . ((in_array('list', $options)) ? '
                    <a href="' . url('cuenta/articulos') . '">
                        <i class="icon icon-list"></i>
                        <span>' . __('view_list') . '</span>
                    </a>
                ' : '') . '
            </div>';
    }

    public static function sitemapUrls()
    {
        $items = (new Post)->readList(['where' => 'publish_date<=NOW() AND active="1"']);
        return Sitemap::getUrls($items);
    }

    public function renderJsonHeader()
    {
        $info = [
            '@context' => 'http://schema.org/',
            '@type' => 'Article',
            'headline' => $this->object->getBasicInfo(),
            'mainEntityOfPage' => $this->object->url(),
            'image' => $this->object->getImageUrl('image', 'huge'),
            'author' => [
                '@type' => 'Organization',
                'name' => Parameter::code('meta_title_page'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => Parameter::code('meta_title_page'),
                'logo' => Parameter::code('meta_image'),
            ],
            'datePublished' => $this->object->get('publish_date'),
            'dateModified' => $this->object->get('modified'),
            'articleBody' => strip_tags($this->object->get('description')),
        ];
        return '<script type="application/ld+json">' . json_encode($info) . '</script>';
    }

}
