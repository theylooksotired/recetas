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
                    </div>
                </div>
            </div>';
    }

    public function renderPreloadImage()
    {
        return '<link rel="preload" as="image" href="' . $this->object->getImageUrlWebp('image', 'web') . '">';
    }

    public function renderAlternateUrl()
    {
        $this->object->loadTranslation();
        if (isset($this->object->translation_url) && $this->object->translation_url != '') {
            return '<link rel="alternate" hreflang="' . Translate_Controller::translateTo() . '" href="' . $this->object->translation_url . '"/>';
        }
    }

    public function renderComplete()
    {
        $this->object->loadMultipleValues();
        // Images
        $images = [];
        foreach ($this->object->get('images') as $item) {
            if ($item->get('id_recipe') != '') {
                $recipe = (new Recipe)->read($item->get('id_recipe'));
                $images[] = $recipe->showUi();
            } else {
                $images[] = '
                    <figure class="post_image">
                        ' . $item->getImageWidth('image', 'web', '', false, $item->get('title')) . '
                        ' . (($item->get('title') != '') ? '<figcaption>' . $item->get('title') . '</figcaption>' : '') . '
                    </figure>';
            }
        }
        $description = $this->object->get('description');
        foreach ($images as $key => $image) {
            if (strpos($description, '#IMAGE_' . ($key + 1) . '#') !== false) {
                $description = str_replace('#IMAGE_' . ($key + 1) . '#', $image, $description);
                unset($images[$key]);
            }
        }
        // Text
        $dom = new DOMDocument();
        @$dom->loadHTML('<html><body>' . mb_convert_encoding($description, 'HTML-ENTITIES', 'UTF-8') . '</body></html>');
        $xpath = new DOMXPath($dom);
        $paragraphs = [];
        foreach ($xpath->query('/html/body/*') as $node) {
            $paragraphs[] = $dom->saveHTML($node);
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
            <figure class="post_image">
                ' . $this->object->getImageWidth('image', 'web') . '
                <figcaption>' . $this->object->getBasicInfo() . '</figcaption>
            </figure>
            <article class="post_complete">
                <div class="post_complete_ins post-content" id="post-container">
                    <div class="post_short_info">
                        <div class="post_date">
                            <i class="icon icon-date"></i>
                            <span>' . Date::sqlText($this->object->get('publish_date')) . '</span>
                        </div>
                        <div class="post_reading_time">
                            <i class="icon icon-time"></i>
                            <span>' . Text::readingTime($this->object->get('description')) . '</span>
                        </div>
                    </div>
                    <div class="post_short_description">' . nl2br($this->object->get('short_description')) . '</div>
                    <div class="editorial">
                        ' . $text . '
                    </div>
                </div>
            </article>';
    }

    public function renderRelated()
    {
        $posts = new ListObjects('Post', ['where' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE) AND id != :id', 'order' => 'MATCH (title, title_url, short_description) AGAINST (:match IN BOOLEAN MODE)', 'limit' => '6'], ['match' => $this->object->getBasicInfo(), 'id' => $this->object->id()]);
        if ($posts->count() < 6) {
            $posts = new ListObjects('Post', ['where' => 'id!=:id AND publish_date<=NOW() AND active="1"', 'limit' => 6], ['id' => $this->object->id()]);
        }
        $search = Text::simpleUrl($this->object->getBasicInfo(), ' ');
        $recipesSearch = new ListObjects('Recipe', [
            'where' => 'active="1" AND MATCH (title, title_url, short_description) AGAINST ("' . $search . '" IN BOOLEAN MODE)',
            'order' => 'MATCH (title, title_url) AGAINST ("' . $search . '") DESC',
            'limit' => '8',
        ]);
        return '
            <div class="related">
                ' . ((!$recipesSearch->isEmpty()) ? '
                <div class="related_block">
                    <h2 class="related_title">' . __('other_recipes') . '</h2>
                    <div class="recipes_minimal">' . $recipesSearch->showList(['function' => 'Minimal']) . '</div>
                </div>
                ' : '') . '
                <div class="related_block">
                    <h2 class="related_title">' . __('other_posts') . '</h2>
                    <div class="posts">' . $posts->showList(['function' => 'PublicSimple']) . '</div>
                </div>
            </div>';
    }

    public static function intro($options = [])
    {
        $items = (isset($options['items'])) ? $options['items'] : new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'publish_date DESC', 'limit' => '6']);
        return '
            <div class="posts_intro">
                <h2 class="posts_intro_title">' . __('last_posts') . '</h2>
                <div class="posts_grid">' . $items->showList(['function' => 'PublicSimple']) . '</div>
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
            ' . HtmlSection::show('intro_posts') .'
            <h2>' . __('posts_list') . '</h2>
            <div class="posts_grid">' . $items->showList(['function' => 'PublicSimple']) . '</div>';
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
            'image' => $this->object->getImageUrl('image', 'web'),
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
