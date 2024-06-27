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
        $this->mode = (Parameter::code('mode')!='') ? Parameter::code('mode') : 'amp';
        switch ($this->action) {
            default:
                $category = (new Category)->readFirst(['where' => 'name_url=:name_url'], ['name_url' => $this->action]);
                $recipe = ($this->id != '' && $category->id() != '') ? (new Recipe)->readFirst(['where' => 'title_url=:title_url AND id_category=:id_category AND active="1"'], ['title_url' => $this->id, 'id_category' => $category->id()]) : new Recipe();
                $item = ($category->id() != '') ? $category : new Category();
                $item = ($recipe->id() != '') ? $recipe : $item;
                if ($item->id() != '') {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . $item->url());
                } else {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url(''));
                }
                break;
            case 'intro':
                if (Parameter::code('meta_title_page_intro') != '') {
                    $this->title_page = Parameter::code('meta_title_page_intro');
                    $this->hide_title_page = true;
                    $this->hide_title_page_appendix = true;
                }
                $items = new ListObjects('Post', ['where' => 'publish_date<=NOW() AND active="1"', 'order' => 'publish_date DESC', 'limit' => '3']);
                $this->meta_url = url('');
                $this->content_top = '
                    ' . Adsense::responsive() . '
                    ' . HtmlSection::show('intro_top') . '
                    <h1>' . $this->title_page . '</h1>
                    ' . Category_Ui::intro();
                $this->content = '
                    ' . HtmlSection::show('intro') . '
                    ' . Post_Ui::intro();
                $this->head = $items->showList(['function' => 'PreloadImage']);
                return $this->ui->render();
                break;
            case 'recetas':
                $this->redirecLastSlash();
                $this->category = (new Category)->readFirst(['where' => 'name_url=:name_url'], ['name_url' => $this->id]);
                $this->recipe = ($this->extraId != '' && $this->category->id() != '') ? (new Recipe)->readFirst(['where' => 'title_url=:title_url AND id_category=:id_category AND active="1"'], ['title_url' => $this->extraId, 'id_category' => $this->category->id()]) : new Recipe();
                if ($this->recipe->id() != '') {
                    $this->recipe->loadCategoryManually($this->category);
                    if ($this->recipe->get('friend_links') == '') {
                        FriendSite::saveFriends($this->recipe);
                    }
                }
                if ($this->id != '' && $this->category->id() == '') {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url('recetas'));
                    exit();
                }
                if ($this->extraId != '' && $this->recipe->id() == '') {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . $this->category->url());
                    exit();
                }
                if (isset($this->parameters['pagina']) && $this->parameters['pagina'] !='') {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . $this->category->url());
                    exit();
                }
                $item = ($this->category->id() != '') ? $this->category : new Category();
                $item = ($this->recipe->id() != '') ? $this->recipe : $item;
                if ($item->id() != '') {
                    if ($item->get('title_page') != '') {
                        $this->title_page = $item->get('title_page');
                        $this->hide_title_page_appendix = true;
                    } else {
                        $this->title_page = $item->getTitlePage();
                    }
                    if ($this->recipe->id() != '') {
                        $this->facebookCommentsFooter = true;
                        $this->layout_page = 'recipe';
                        $this->recipe->persistSimple('views', $this->recipe->get('views') + 1);
                    } else {
                        $this->layout_page = 'recipe_category';
                        $this->hide_title_page_appendix = true;
                    }
                    $this->url_page = $item->url();
                    $this->meta_url = $item->url();
                    $this->meta_image = $item->getImageUrl('image', 'web');
                    $this->meta_description = ($item->get('meta_description') != '') ? $item->get('meta_description') : $item->get('short_description');
                    $this->bread_crumbs = ($this->recipe->id() != '') ? [$this->category->url() => $this->category->getBasicInfo(), $item->url() => $item->getBasicInfo()] : [$item->url() => $item->getBasicInfo()];
                    $this->content = $item->showUi('Complete');
                    if ($this->content == '') {
                        header("HTTP/1.1 301 Moved Permanently");
                        header('Location: ' . url(''));
                        exit();
                    }
                    if ($this->recipe->id() != '') {
                        $this->hide_side_recipes = true;
                        $this->head = $item->showUi('JsonHeader') . $item->showUi('PreloadImage');
                        $this->content_bottom = $this->recipe->showUi('Related');
                    }
                    return $this->ui->render();
                } else {
                    $this->title_page = __('recipes_list');
                    $this->layout_page = 'recipe_category';
                    $this->bread_crumbs = [url($this->action) => __('recipes')];
                    $this->content = Category_Ui::all();
                    $this->meta_url = url($this->action) . (isset($this->parameters['pagina']) ? '?pagina=' . $this->parameters['pagina'] : '');
                }
                return $this->ui->render();
                break;
            case 'articulos':
                $this->redirecLastSlash();
                $this->layout_page = 'posts';
                $post = (new Post)->readFirst(['where' => 'title_url="' . $this->id . '"']);
                if ($post->id() != '') {
                    if ($this->extraId != '') {
                        header("HTTP/1.1 301 Moved Permanently");
                        header('Location: ' . $post->url());
                        exit();
                    }
                    $post->persistSimple('views', $post->get('views') + 1);
                    $this->title_page = ($post->get('title_page') != '') ? $post->get('title_page') : $post->getBasicInfoTitle();
                    $this->meta_description = ($post->get('meta_description') != '') ? $post->get('meta_description') : $post->get('short_description');
                    $this->meta_url = $post->url();
                    $this->meta_image = $post->getImageUrl('image', 'web');
                    $this->head = $post->showUi('JsonHeader');
                    $this->bread_crumbs = [url($this->action) => __('posts'), $post->url() => $post->getBasicInfoTitle()];
                    $this->content = $post->showUi('Complete');
                    $this->content_bottom = $post->showUi('Related');
                    $this->facebookCommentsFooter = true;
                } else {
                    if ($this->id != '') {
                        header("HTTP/1.1 301 Moved Permanently");
                        header('Location: ' . url($this->action));
                        exit();
                    }
                    $this->meta_url = url($this->action);
                    $this->layout_page = 'simple';
                    $this->title_page = (Parameter::code('meta_title_page_posts') != '') ? Parameter::code('meta_title_page_posts') : __('posts_list');
                    $this->bread_crumbs = [url($this->action) => __('posts')];
                    $this->content = Post_Ui::all();
                }
                return $this->ui->render();
                break;
            case 'buscar':
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
                            ' . $items->showList(['middle' => Adsense::responsive(), 'middleRepetitions' => 2]) . '
                        </div>';
                    return $this->ui->render();
                } else {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url(''));
                    exit();
                }
                break;
            case 'sitemap':
            case 'sitemap.xml':
                $this->mode = 'xml';
                $urls = [url('')];
                $urls = array_merge($urls, Category_Ui::sitemapUrls());
                $urls = array_merge($urls, Recipe_Ui::sitemapUrls());
                $urls = array_merge($urls, Post_Ui::sitemapUrls());
                return Sitemap::generate($urls);
                break;


            // Other actions
            case 'rename-images':
                $this->mode = 'json';
                $this->checkAuthorization();
                $recipes = (new Recipe)->readList();
                $directoryBase = ASTERION_BASE_FILE . 'stock/Recipe/';
                $foldersToRetain = [];
                foreach ($recipes as $recipe) {
                    $foldersToRetain[] = $recipe->get('image');
                }
                $directories = glob($directoryBase . '*', GLOB_ONLYDIR);
                foreach ($directories as $directory) {
                    $folderName = basename($directory);
                    if (!in_array($folderName, $foldersToRetain)) {
                        $directoryToDelete = $directoryBase . $folderName;
                        if (is_dir($directoryToDelete)) {
                            $files = glob($directoryToDelete . '/*');
                            foreach ($files as $file) {
                                if (is_file($file)) {
                                    unlink($file);
                                }
                            }
                            rmdir($directoryToDelete);
                        }
                    }
                }
                foreach ($recipes as $recipe) {
                    $oldName = $recipe->get('image');
                    $toUpdate = (strpos($oldName, '-image') !== false) ? true : false;
                    if ($toUpdate) {
                        $image = $recipe->getImageUrl('image', 'web');
                        $recipe->saveImage($image, 'image');
                        Image_File::deleteImage('Recipe', $oldName);
                        dump($oldName);
                    }
                }
                dd('DONE');
                break;
            case 'refresh-db':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = [];
                foreach (Init::errorsDatabase() as $item) {
                    $result[] = $item['query'];
                    Db::execute($item['query']);
                }
                return json_encode($result);
                break;
            case 'recipes-fix-steps':
                $this->mode = 'ajax';
                $this->checkAuthorization();
                $recipe = (new Recipe)->read($this->id);
                if ($recipe->id() != '') {
                    if (isset($this->parameters['save'])) {
                        $recipe->fixSteps();
                        $recipe = (new Recipe)->read($this->id);
                        return $recipe->showUi('FixedSteps');
                    }
                    return $recipe->showUi('FixSteps');
                } else {
                    $items = (new Recipe)->readList(['order' => 'title_url']);
                    $results = [];
                    foreach ($items as $item) {
                        $results[] = $item->urlFixSteps();
                    }
                    return implode(' ', $results);
                }
                break;
            case 'json-mobile':
                $this->mode = 'json';
                $this->checkAuthorization();
                $countryCode = Parameter::code('country_code');
                $info = [
                    'site' => [
                        'title' => Parameter::code('meta_title_page'),
                        'titleCountry' => Parameter::code('meta_title_header_bottom'),
                        'description' => Parameter::code('meta_description'),
                        'country' => $countryCode,
                        'url' => url(''),
                        'version' => '14.0.0'
                    ],
                    'categories' => [],
                    'recipes' => []
                ];
                $items = (new Category)->readList(['order' => 'ord']);
                $categories = [];
                foreach($items as $item) {
                    $infoIns = (array)$item->values;
                    unset($infoIns['created']);
                    unset($infoIns['modified']);
                    unset($infoIns['image']);
                    unset($infoIns['description']);
                    unset($infoIns['name_url']);
                    unset($infoIns['title']);
                    unset($infoIns['ord']);
                    $info['categories'][] = $infoIns;
                    $categories[$item->id()] = $item->getBasicInfo();
                }
                $items = (new Recipe)->readList(['where' => 'active="1"', 'order' => 'title_url']);
                $errorStep = '';
                foreach($items as $item) {
                    $infoIns = (array)$item->values;
                    unset($infoIns['created']);
                    unset($infoIns['modified']);
                    unset($infoIns['image']);
                    unset($infoIns['title_url']);
                    unset($infoIns['active']);
                    unset($infoIns['ord']);
                    unset($infoIns['preparation_old']);
                    unset($infoIns['id_user']);
                    unset($infoIns['friend_links']);
                    unset($infoIns['description_bottom']);
                    
                    $infoIns['country'] = $countryCode;
                    $infoIns['url'] = $item->url();
                    $infoIns['id_category_name'] = $categories[$infoIns['id_category']];

                    $ingredients = (new RecipeIngredient)->readList(['where' => 'id_recipe=:id_recipe', 'order'=>'ord'], ['id_recipe' => $infoIns['id']]);
                    $infoIns['ingredients'] = [];
                    foreach ($ingredients as $ingredient) {
                        $infoIngredient = (array)$ingredient->values;
                        unset($infoIngredient['id']);
                        unset($infoIngredient['created']);
                        unset($infoIngredient['modified']);
                        unset($infoIngredient['id_recipe']);
                        unset($infoIngredient['ord']);
                        unset($infoIngredient['ingredient_old']);
                        $infoIngredient['label'] = $ingredient->labelSimple();
                        
                        $infoIns['ingredients'][] = $infoIngredient;
                    }

                    $preparation = (new RecipePreparation)->readList(['where' => 'id_recipe=:id_recipe', 'order'=>'ord'], ['id_recipe' => $infoIns['id']]);
                    $infoIns['preparation'] = [];
                    foreach ($preparation as $step) {
                        $infoStep = (array)$step->values;
                        unset($infoStep['id']);
                        unset($infoStep['created']);
                        unset($infoStep['modified']);
                        unset($infoStep['id_recipe']);
                        unset($infoStep['ord']);
                        unset($infoStep['image']);
                        unset($infoStep['description']);
                        unset($infoStep['description_old']);
                        unset($infoStep['image_old']);
                        $infoIns['preparation'][] = $infoStep;
                    }

                    $info['recipes'][] = $infoIns;
                }
                if ($errorStep!='') {
                    return "ERROR STEP - \n".$errorStep;
                }
                $content = json_encode($info, JSON_PRETTY_PRINT);
                return $content;
            break;
                // case 'friends':
                //     $this->mode = 'ajax';
                //     $sites = FriendSite::allLess();
                //     foreach ($sites as $key=>$site) {
                //         $sites[$key]['title'] = '';
                //         $sites[$key]['description'] = '';
                //         $sites[$key]['image'] = '';
                //         $dom = new DOMDocument();
                //         if (@$dom->loadHTMLFile($site['url'])) {
                //             $xpath = new DOMXPath($dom);
                //             $sites[$key]['title'] = $dom->getElementsByTagName("title")->item(0)->textContent;
                //             $sites[$key]['description'] = $xpath->evaluate('//meta[@name="description"]/@content')->item(0)->textContent;
                //             $sites[$key]['image'] = $dom->getElementsByTagName("amp-img")->item(0)->getAttribute('src');
                //         }
                //         // break;
                //     }
                //     //====
                //     // dd(FriendSite::random('sopas'));
                //     //=====
                //     // $sites = FriendSite::all();
                //     // dump(count($sites));
                //     // //576
                //     // // $categories = [];
                //     // foreach ($sites as $key => $site) {
                //     //     $info = explode('/', $site['url']);
                //     //     $category = $info[4];
                //     //     $sites[$key]['category'] = $info[4];
                //     //     // if (!in_array($info[4], $categories)) {
                //     //     //     $categories[] = ;
                //     //     // }
                //     // }
                //     //Echo as PHP
                //     foreach ($sites as $site) {
                //         echo "['site'=>'".$site['site']."', 'url'=>'".$site['url']."', 'category'=>'".$site['category']."', 'image'=>'".$site['image']."', 'title'=>'".$site['title']."', 'description'=>'".$site['description']."'],\n";
                //     }

                //     exit();
                //     break;
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

    public function redirecLastSlash()
    {
        $url = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (substr($url, -1) == '/') {
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: ' . substr($url, 0, -1));
            exit();
        }
        if (strpos($url, '?') !== false) {
            $url = explode('?', $url);
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: ' . $url[0]);
            exit();
        }
        
    }

}
