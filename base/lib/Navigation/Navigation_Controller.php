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
class Navigation_Controller extends Controller
{

    /**
     * Main function to control the public actions.
     */
    public function getContent()
    {
        $this->login = User_Login::getInstance();
        $this->ui = new Navigation_Ui($this);
        switch ($this->action) {
            default:
                $this->mode = 'amp';
                $category = (new Category)->readFirst(['where' => 'name_url=:name_url'], ['name_url' => $this->action]);
                $recipe = ($this->id != '' && $category->id() != '') ? (new Recipe)->readFirst(['where' => 'title_url=:title_url AND id_category=:id_category AND active="1"'], ['title_url' => $this->id, 'id_category' => $category->id()]) : new Recipe();
                $item = ($category->id() != '') ? $category : new Category();
                $item = ($recipe->id() != '') ? $recipe : $item;
                if ($item->id() != '') {
                    $this->title_page = $item->getBasicInfo();
                    $this->url_page = $item->url();
                    $this->meta_url = $item->url();
                    $this->meta_image = $item->getImageUrl('image', 'web');
                    $this->meta_description = $item->get('shortDescription');
                    $this->bread_crumbs = ($recipe->id() != '') ? [url('recetas') => __('recipes'), $category->url() => $category->getBasicInfo(), $item->url() => $item->getBasicInfo()] : [url('recetas') => __('recipes'), $item->url() => $item->getBasicInfo()];
                    $this->content = $item->showUi('Complete');
                    if ($recipe->id() != '') {
                        $this->hide_side_recipes = true;
                        $this->head = $this->ampFacebookCommentsHeader() . $item->showUi('JsonHeader');
                        $this->content_bottom = $recipe->showUi('Related');
                    }
                    return $this->ui->render();
                } else {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url('intro'));
                }
                break;
            case 'recetas':
                $this->mode = 'amp';
                $category = (new Category)->readFirst(['where' => 'name_url=:name_url'], ['name_url' => $this->id]);
                $recipe = ($this->extraId != '' && $category->id() != '') ? (new Recipe)->readFirst(['where' => 'title_url=:title_url AND id_category=:id_category AND active="1"'], ['title_url' => $this->extraId, 'id_category' => $category->id()]) : new Recipe();
                if ($recipe->id()!='') {
                    $recipe->loadCategoryManually($category);
                }
                $item = ($category->id() != '') ? $category : new Category();
                $item = ($recipe->id() != '') ? $recipe : $item;
                if ($item->id() != '') {
                    $this->title_page = $item->getBasicInfo();
                    $this->url_page = $item->url();
                    $this->meta_url = $item->url() . (isset($this->parameters['pagina']) ? '?pagina=' . $this->parameters['pagina'] : '');
                    $this->meta_image = $item->getImageUrl('image', 'web');
                    $this->meta_description = $item->get('shortDescription');
                    $this->bread_crumbs = ($recipe->id() != '') ? [url('recetas') => __('recipes'), $category->url() => $category->getBasicInfo(), $item->url() => $item->getBasicInfo()] : [url('recetas') => __('recipes'), $item->url() => $item->getBasicInfo()];
                    $this->content = $item->showUi('Complete');
                    if ($recipe->id() != '') {
                        $this->hide_side_recipes = true;
                        $this->head = $this->ampFacebookCommentsHeader() . $item->showUi('JsonHeader');
                        $this->content_bottom = $recipe->showUi('Related');
                    }
                    return $this->ui->render();
                } else {
                    $this->title_page = __('recipes');
                    $this->bread_crumbs = [url($this->action) => __('recipes')];
                    $this->content = Category_Ui::all();
                    $this->meta_url = url($this->action) . (isset($this->parameters['pagina']) ? '?pagina=' . $this->parameters['pagina'] : '');
                }
                return $this->ui->render();
                break;
            case 'intro':
                $this->mode = 'amp';
                $this->content_top = '
                    ' . Post_Ui::introTop() . '
                    ' . HtmlSection::show('intro_top') . '
                    ' . Category_Ui::intro();
                $this->content = '
                    ' . HtmlSection::show('intro') . '
                    ' . Post_Ui::intro();
                return $this->ui->render();
                break;
            case 'articulos':
                $this->mode = 'amp';
                $this->layout_page = 'posts';
                $post = (new Post)->readFirst(['where' => 'title_url="' . $this->id . '"']);
                if ($post->id() != '') {
                    $this->title_page = $post->getBasicInfo();
                    $this->meta_description = $post->get('short_description');
                    $this->meta_url = $post->url();
                    $this->meta_image = $post->getImageUrl('image', 'huge');
                    $this->head = $this->ampFacebookCommentsHeader() . $post->showUi('JsonHeader');
                    $this->content = $post->showUi('Complete');
                    $this->content_bottom = $post->showUi('Related');
                } else {
                    $this->title_page = __('posts');
                    $items = new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'publish_date DESC', 'results' => '10']);
                    $this->content = '<div class="posts">' . $items->showListPager() . '</div>';
                }
                return $this->ui->render();
                break;
            case 'buscar':
                $this->mode = 'amp';
                if (isset($_GET['search']) && $_GET['search'] != '') {
                    $search = Text::simpleUrl($_GET['search']);
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url('buscar/' . $search));
                    exit();
                }
                if ($this->id != '') {
                    $search = str_replace('-', ' ', Text::simpleUrl($this->id));
                    $this->title_page = __('search_results') . ' "' . ucwords($search) . '"';
                    $this->url_page = url('buscar/' . $search);
                    $items = new ListObjects('Recipe', [
                        'where' => 'active="1" AND MATCH (title, title_url, short_description) AGAINST ("' . $search . '" IN BOOLEAN MODE)',
                        'order' => 'MATCH (title, title_url, short_description) AGAINST ("' . $search . '" IN BOOLEAN MODE) DESC',
                        'limit' => '20',
                    ]);
                    if ($items->isEmpty()) {
                        $items = new ListObjects('Recipe', [
                            'where' => 'active="1" AND CONCAT(title," ",title_url," ",short_description) LIKE ("%' . $search . '%")',
                            'order' => 'title_url',
                            'limit' => '20',
                        ]);
                    }
                    if ($items->isEmpty()) {
                        $this->title_page = __('no_search_results');
                        $items = new ListObjects('Recipe', ['where' => 'active="1"', 'order' => 'RAND()', 'limit' => '20']);
                    }
                    $this->content = '
                        <div class="items_all">
                            ' . Adsense::amp() . '
                            ' . $items->showList(['middle' => Adsense::amp(), 'middleRepetitions' => 2]) . '
                        </div>';
                    return $this->ui->render();
                } else {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url(''));
                }
                break;
            case 'sitemap':
                $this->mode = 'xml';
                $urls = [url('')];
                $urls = array_merge($urls, Post_Ui::sitemapUrls());
                $urls = array_merge($urls, Recipe_Ui::sitemapUrls());
                return Sitemap::generate($urls);
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

    public function ampFacebookCommentsHeader()
    {
        if (Parameter::code('facebook_comments') == 'true') {
            return '<script async custom-element="amp-facebook-comments" src="https://cdn.ampproject.org/v0/amp-facebook-comments-0.1.js"></script>';
        }
    }

    public function ampListHeader()
    {
        return '
            <script async custom-element="amp-list" src="https://cdn.ampproject.org/v0/amp-list-0.1.js"></script>
            <script async custom-template="amp-mustache" src="https://cdn.ampproject.org/v0/amp-mustache-0.2.js"></script>';
    }

}
