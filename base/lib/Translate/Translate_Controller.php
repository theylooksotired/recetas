<?php
/**
 * @class NavigationController
 *
 * This is the controller for all the public actions of the website.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Translate_Controller extends Controller
{

    public function getContent()
    {
        switch ($this->action) {
            case 'all':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];

                // Categories
                $itemsProcessed = [];
                $categories = (new Category)->readList(['where' => 'name_url_en IS NULL OR name_url_en=""']);
                foreach ($categories as $category) {
                    $category->translate();
                    $itemsProcessed[] = 'OK - ' . $category->getBasicInfo();
                }
                $result['categories'] = $itemsProcessed;

                // Subcategories
                $itemsProcessed = [];
                $subCategories = (new SubCategory)->readList(['where' => 'name_url_en IS NULL OR name_url_en=""']);
                foreach ($subCategories as $subCategory) {
                    $subCategory->translate();
                    $itemsProcessed[] = 'OK - ' . $subCategory->getBasicInfo();
                }
                $result['subcategories'] = $itemsProcessed;

                // Posts
                $itemsProcessed = [];
                $posts = (new Post)->readList(['where' => 'title_url_en IS NULL OR title_url_en=""', 'limit' => '10']);
                foreach ($posts as $post) {
                    $post->translate();
                    $itemsProcessed[] = 'OK - ' . $post->getBasicInfo();
                }
                $result['posts'] = $itemsProcessed;

                // Recipes
                $itemsProcessed = [];
                $recipes = (new Recipe)->readList(['where' => 'translation IS NULL OR translation=""', 'limit' => '10']);
                foreach ($recipes as $recipe) {
                    $recipe->translate();
                    $itemsProcessed[] = 'OK - ' . $recipe->getBasicInfo();
                }
                $result['recipes'] = $itemsProcessed;

                // HtmlSections
                $itemsProcessed = [];
                $htmlSections = (new HtmlSection)->readList(['where' => 'title_url_en IS NULL OR title_url_en=""']);
                foreach ($htmlSections as $htmlSection) {
                    $htmlSection->translate();
                    $itemsProcessed[] = 'OK - ' . $htmlSection->getBasicInfo();
                }
                $result['html_sections'] = $itemsProcessed;

                return json_encode($result, JSON_PRETTY_PRINT);
                break;
            }
    }

    public function checkAuthorization()
    {
        $headers = apache_request_headers();
        if (!isset($headers) || !isset($headers['Authorization']) || $headers['Authorization'] != 'plastic') {
            header('Location: ' . url(''));
            exit();
        }
    }

    static public function translateTo()
    {
        return (Language::active() == 'es') ? 'en' : 'es';
    }

    static public function alternateUrl($code = 'main')
    {
        // return '<link rel="alternate" hreflang="' . Translate_Controller::translateTo() . '" href="' . $translations[$code] . '"/>';
    }

}
