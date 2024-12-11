<?php
/**
 * @class SearchPageUi
 *
 * This class manages the UI for the SearchPage objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class SearchPage_Ui extends Ui
{

    public function renderTag()
    {
        return '<a href="' . $this->object->url() . '" class="search_tag">' . $this->object->get('search') . '</a> ';
    }

    static public function tags()
    {
        $items = new ListObjects('SearchPage', ['limit' => '12', 'order' => '(views + 0) DESC']);
        if (!$items->isEmpty()) {
            return '
                <div class="search_pages_tags">
                    <h2 class="search_pages_tags_title">' . __('popular_search_pages') . '</h2>
                    ' . $items->showList(['function' => 'Tag']) . '
                </div>';
        }
    }

    public static function sitemapUrls()
    {
        $items = (new SearchPage)->readList(['where' => 'views > 1']);
        return Sitemap::getUrls($items);
    }

}
