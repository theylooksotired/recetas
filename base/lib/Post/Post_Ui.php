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
        return '
            <article class="recipe">
                <a class="recipe_ins" href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitle() . '">
                    <div class="recipe_image">' . $this->object->getImageAmpWebp('image', 'small') . '</div>
                    <div class="recipe_information">
                        <h2 class="recipe_title">' . $this->object->getBasicInfo() . '</h2>
                        <p class="recipe_short_description">
                            <i class="icon icon-date"></i>
                            <span>' . Date::sqlText($this->object->get('publish_date')) . '</span>
                        </p>
                        <p class="recipe_short_description">' . $this->object->get('short_description') . '</p>
                    </div>
                </a>
            </article>';
    }

    public function renderMedium()
    {
        return '
            <article class="post">
                <a class="post_ins" title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">
                    <div class="post_image">' . $this->object->getImageAmpWebp('image', 'small') . '</div>
                    <div class="post_information">
                        <div class="post_title">' . $this->object->getBasicInfo() . '</div>
                        <div class="post_short_description">' . $this->object->get('short_description') . '</div>
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
            </article>';
    }

    public function renderPublicSimple()
    {
        return '
            <article class="post_simple">
                <a class="post_ins" title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">
                    <div class="post_image">' . $this->object->getImageAmpWebp('image', 'small') . '</div>
                    <div class="post_information">
                        <div class="post_title">' . $this->object->getBasicInfo() . '</div>
                        <div class="post_short_description">' . $this->object->get('short_description') . '</div>
                        <div class="post_date">
                            <i class="icon icon-date"></i>
                            <span>' . Date::sqlText($this->object->get('publish_date')) . '</span>
                        </div>
                    </div>
                </a>
            </article>';
    }

    public function renderIntro($options = [])
    {
        return '
            <article class="post">
                <a class="post_ins" title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">
                    <div class="post_image post_image_simple">' . $this->object->getImageAmpWebp('image', 'small') . '</div>
                    <div class="post_information">
                        <div class="post_title">' . $this->object->getBasicInfo() . '</div>
                        <div class="post_short_description">' . $this->object->get('short_description') . '</div>
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
            </article>';
    }

    public function renderIntroTop($options = [])
    {
        return '
            <div class="post_top">
                <a class="post_top_ins" title="' . $this->object->getBasicInfoTitle() . '" href="' . $this->object->url() . '">
                    <div class="post_image post_image_simple">' . $this->object->getImageAmpWebp('image', 'web') . '</div>
                    <div class="post_information">
                        <div class="post_title">' . $this->object->getBasicInfo() . '</div>
                        <div class="post_short_description">' . $this->object->get('short_description') . '</div>
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
        return '
            <div class="post_minimal">
                <a href="' . $this->object->url() . '" title="' . $this->object->getBasicInfoTitle() . '" class="post_minimal_ins">
                    <div class="post_image_amp">' . $this->object->getImageAmpWebp('image', 'small') . '</div>
                    <div class="post_title">' . $this->object->getBasicInfo() . '</div>
                </a>
            </div>';
    }

    public function renderComplete()
    {
        $this->object->loadMultipleValues();
        $tags = '';
        foreach ($this->object->get('tags') as $tag) {
            $tags .= $tag->link() . ' ';
        }
        $categories = '';
        foreach ($this->object->get('categories') as $category) {
            $categories .= $category->link() . ' ';
        }
        $share = $this->share(['share' => [
            ['key' => 'facebook', 'icon' => '<i class="icon icon-facebook"></i>'],
            ['key' => 'twitter', 'icon' => '<i class="icon icon-twitter"></i>'],
        ]]);
        return '
            <article class="post_complete">
                <div class="post_complete_ins"  id="post-container">
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
                    <div class="post_image_complete">' . $this->object->getImageAmpWebp('image', 'web') . '</div>
                    <div class="editorial">' . $this->object->get('description') . '</div>
                </div>
            </article>
            ' . Adsense::amp() . '
            <div class="item_complete_share">
                <div class="item_complete_share_title">' . __('help_us_sharing') . '</div>
                ' . $share . '
                ' . Navigation_Ui::facebookComments($this->object->url()) . '
            </div>';
    }

    public function renderRelated()
    {
        $items = new ListObjects('Post', ['where' => 'id!=:id AND publish_date<=NOW() AND active="1"', 'limit' => 6], ['id' => $this->object->id()]);
        return '<div class="related">
                    <div class="related_title">' . __('other_posts') . '</div>
                    <div class="posts">' . $items->showList(['function' => 'PublicSimple']) . '</div>
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
        $items = (isset($options['items'])) ? $options['items'] : new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'publish_date DESC', 'limit' => '3, 3']);
        return '
            <div class="posts_intro">
                <div class="posts_intro_title">' . __('last_posts') . '</div>
                <div class="posts_intro_items">' . $items->showList(['function' => 'Intro']) . '</div>
                <div class="posts_intro_button">
                    <a href="' . url('articulos') . '">' . __('view_all_posts') . '</a>
                </div>
            </div>';
    }

    public static function menuSide($options = [])
    {
        $items = new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'RAND()', 'limit' => 3]);
        return '
            ' . Adsense::amp() . '
            <div class="items_side">
                <div class="items_side_title">' . __('last_posts') . '</div>
                <div class="items_side_items">' . $items->showList(['function' => 'Side'], $options) . '</div>
                <div class="items_side_button"><a href="' . url('articulos') . '">' . __('view_all_posts') . '</a></div>
            </div>';
    }

    public function renderEdit()
    {
        $html = '
            <div class="itemEditInsWrapper">
                <div class="itemEditImage">' . $this->object->getImage('image', 'small') . '</div>
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
            'dateModified' => $this->object->get('publish_date'),
            'articleBody' => strip_tags($this->object->get('description')),
        ];
        return '<script type="application/ld+json">' . json_encode($info) . '</script>';
    }

}
