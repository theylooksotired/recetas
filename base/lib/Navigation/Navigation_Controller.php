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

    public $hide_title_page;
    public $hide_title_page_appendix;
    public $hide_side_recipes;
    public $subcategory;
    public $category;
    public $recipe;

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
                $subcategory = (new SubCategory)->readFirst(['where' => 'name_url=:name_url'], ['name_url' => $this->action]);
                $recipe = ($this->id != '' && $category->id() != '') ? (new Recipe)->readFirst(['where' => 'title_url=:title_url AND id_category=:id_category AND active="1"'], ['title_url' => $this->id, 'id_category' => $category->id()]) : new Recipe();
                $item = ($subcategory->id() != '') ? $subcategory : new SubCategory();
                $item = ($category->id() != '') ? $category : $item;
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
                $this->layout_page = 'simple';
                $this->content = '
                    ' . Category_Ui::introTop() . '
                    ' . HtmlSection::show('intro_top') . '
                    ' . $this->ui->adApp('ad_intro') . '
                    <h1>' . $this->title_page . '</h1>
                    ' . Category_Ui::intro() .'
                    ' . HtmlSection::show('intro') . '
                    ' . Post_Ui::intro() . '
                    ' . SearchPage_Ui::tags();
                $this->head = $items->showList(['function' => 'PreloadImage']);
                return $this->ui->render();
                break;
            case 'recetas':
                $this->redirecLastSlash();
                $this->subcategory = (new SubCategory)->readFirst(['where' => 'name_url=:name_url'], ['name_url' => $this->id]);
                $this->category = (new Category)->readFirst(['where' => 'name_url=:name_url'], ['name_url' => $this->id]);
                $this->recipe = ($this->extraId != '' && $this->category->id() != '') ? (new Recipe)->readFirst(['where' => 'title_url=:title_url AND id_category=:id_category AND active="1"'], ['title_url' => $this->extraId, 'id_category' => $this->category->id()]) : new Recipe();
                if ($this->recipe->id() != '') {
                    $this->recipe->loadCategoryManually($this->category);
                    if ($this->recipe->get('friend_links') == '') {
                        FriendSite::saveFriends($this->recipe);
                    }
                }
                if ($this->id != '' && $this->category->id() == '' && $this->subcategory->id() == '') {
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
                $item = ($this->subcategory->id() != '') ? $this->subcategory : new SubCategory();
                $item = ($this->category->id() != '') ? $this->category : $item;
                $item = ($this->recipe->id() != '') ? $this->recipe : $item;
                if ($item->id() != '') {
                    if ($item->get('title_page') != '') {
                        $this->title_page = $item->get('title_page');
                        $this->hide_title_page_appendix = true;
                    } else {
                        $this->title_page = $item->getTitlePage();
                    }
                    if ($this->recipe->id() != '') {
                        $this->layout_page = 'recipe';
                        $this->recipe->persistSimple('views', $this->recipe->get('views') + 1);
                    } else {
                        $this->layout_page = 'recipe_category';
                        $this->hide_title_page_appendix = true;
                    }
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
                        if (Parameter::code('questions') == 'true') $this->head .= Recaptcha::head();
                        $this->content_bottom = $this->recipe->showUi('Related');
                        if (Parameter::code('questions') == 'true' && isset($this->values['question']) && strlen($this->values['question']) > 10) {
                            $question = 'Usando un lenguaje formal y evitando mencionar el sexo de la persona pues no sabemos si es hombre o mujer. Escribe un archivo JSON que tenga tres campos: "original_question" que es el texto original de la pregunta, "formatted_question" que es la pregunta reformulada y bien escrita, "answer" que es una respuesta a la pregunta: "' . $this->values['question'] . '" que ha formulado una persona que acaba de leer la siguiente receta de cocina: "' . $this->recipe->showUi('Text') . '"';
                            $answerChatGPT = ChatGPT::answer($question);
                            preg_match('/\{(?:[^{}]|(?R))*\}/', $answerChatGPT, $matches);
                            $jsonString = (isset($matches[0])) ? $matches[0] : '';
                            if ($jsonString != '') {
                                $json = json_decode($jsonString, true);
                                $values = $this->values;
                                $values['question'] = (isset($json['original_question'])) ? $json['original_question'] : '';
                                $values['question_formatted'] = (isset($json['formatted_question'])) ? $json['formatted_question'] : '';
                                $values['answer'] = (isset($json['answer'])) ? $json['answer'] : '';
                                $values['id_recipe'] = $this->recipe->id();
                                $values['published'] = false;
                                $question = new Question($values);
                                $question->validate();
                                $question->validateReCaptchaV3();
                                if (count($question->errors) == 0) {
                                    $question->persist();
                                    Session::set('answered_recipe', $this->recipe->id());
                                    Session::set('answered_question', $question->id());
                                    header('Location: ' . $this->recipe->url() . '#question_' . $this->recipe->id());
                                    exit();
                                }
                            }
                        }
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
                $this->layout_page = 'simple';
                $post = (new Post)->readFirst(['where' => 'title_url="' . $this->id . '"']);
                if ($post->id() != '') {
                    if ($this->extraId != '') {
                        header("HTTP/1.1 301 Moved Permanently");
                        header('Location: ' . $post->url());
                        exit();
                    }
                    $this->layout_page = 'recipe';
                    $post->persistSimple('views', $post->get('views') + 1);
                    $this->title_page = ($post->get('title_page') != '') ? $post->get('title_page') : $post->getBasicInfoTitle();
                    $this->meta_description = ($post->get('meta_description') != '') ? $post->get('meta_description') : $post->get('short_description');
                    $this->meta_url = $post->url();
                    $this->meta_image = $post->getImageUrl('image', 'web');
                    $this->head = $post->showUi('JsonHeader');
                    $this->bread_crumbs = [url($this->action) => __('posts'), $post->url() => $post->getBasicInfoTitle()];
                    $this->content = $post->showUi('Complete');
                    $this->content_bottom = $post->showUi('Related');
                } else {
                    if ($this->id != '') {
                        header("HTTP/1.1 301 Moved Permanently");
                        header('Location: ' . url($this->action));
                        exit();
                    }
                    $this->meta_url = url($this->action);
                    $this->title_page = (Parameter::code('meta_title_page_posts') != '') ? Parameter::code('meta_title_page_posts') : __('posts_list');
                    $this->bread_crumbs = [url($this->action) => __('posts')];
                    $this->content = Post_Ui::all();
                }
                return $this->ui->render();
                break;
            case 'top-10':
                $top10s = new ListObjects('Top10', ['order' => 'ord']);
                $recipesMostViewed = new ListObjects('Recipe', ['order' => 'views DESC', 'limit' => '10']);
                $this->title_page = Parameter::code('meta_title_page_top10');
                $this->meta_url = url($this->action);
                preg_match('/<p>(.*?)<\/p>/', HtmlSection::show('top_10_intro'), $matches);
                if (isset($matches[1])) {
                    $this->meta_description = strip_tags($matches[1]);
                }
                $this->content = '
                    ' . HtmlSection::show('top_10_intro') . '
                    <div class="top10_wrapper">
                        <h2>' . __('top10_recipes') . '</h2>
                        ' . $top10s->showList() . '
                    </div>
                    <div class="top10_wrapper">
                        <h2>' . __('most_viewed_recipes') . '</h2>
                        <p>' . __('most_viewed_recipes_disclaimer') . '</p>
                        ' . $recipesMostViewed->showList(['function' => 'Top10']) . '
                    </div>
                    ' . SearchPage_Ui::tags();
                return $this->ui->render();
                break;
            case 'buscar':
            case 'pesquisar':
                if (isset($_GET['search']) && $_GET['search'] != '') {
                    $search = Text::simpleUrl($_GET['search']);
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url($this->action . '/' . $search));
                    exit();
                }
                if ($this->id != '') {
                    $this->content = '';
                    $titleRecipes = __('recipes');
                    $search = str_replace('-', ' ', Text::simpleUrl($this->id));
                    $items = new ListObjects('Recipe', [
                        'where' => 'active="1" AND MATCH (title, title_url, short_description) AGAINST ("' . $search . '" IN BOOLEAN MODE)',
                        'order' => 'MATCH (title, title_url) AGAINST ("' . $search . '") DESC',
                        'limit' => '20',
                    ]);
                    if ($items->isEmpty()) {
                        $items = new ListObjects('Recipe', [
                            'where' => 'active="1" AND CONCAT(title," ",title_url," ",short_description) LIKE ("%' . $search . '%")',
                            'order' => 'title_url',
                            'limit' => '20',
                        ]);
                    } else {
                        $searchPage = (new SearchPage)->readFirst(['where' => 'search=:search'], ['search' => $search]);
                        if ($searchPage->id() == '') {
                            $values = [
                                'search' => $search,
                                'views' => 1
                            ];
                            $questionSearch = 'Escribe un archivo JSON, sin ningun texto adicional, con los campos titulo (que sea una correcion del titulo original), metaDescripcion (140 caracteres), descripcion (350 caracteres). El titulo original de la pagina es: "Resultados de la búsqueda ' . $search . ' en ' . Parameter::code('meta_title_page') . '"';
                            $questionSearch = (ASTERION_LANGUAGE_ID == 'pt') ? 'Escreva um arquivo JSON, sem nenhum texto adicional, com os campos titulo (que seja uma correção do título original), metaDescripcion (140 caracteres), descripcion (350 caracteres). O título original da página é: "Resultados da busca ' . $search . ' em ' . Parameter::code('meta_title_page') . '"' : $questionSearch;
                            $answerChatGPT = ChatGPT::answer($questionSearch);
                            preg_match('/\{(?:[^{}]|(?R))*\}/', $answerChatGPT, $matches);
                            $jsonString = (isset($matches[0])) ? $matches[0] : '';
                            if ($jsonString != '') {
                                $json = json_decode($jsonString, true);
                                $values['title_page'] = (isset($json['titulo'])) ? str_replace('"', '', $json['titulo']) : '';
                                $values['meta_description'] = (isset($json['metaDescripcion'])) ? $json['metaDescripcion'] : '';
                                $values['short_description'] = (isset($json['descripcion'])) ? $json['descripcion'] : '';
                                $values['meta_description'] = str_replace(['!', '?', '¡', '¿'], ['.', '.', '', ''], $values['meta_description']);
                                $values['short_description'] = str_replace(['!', '?', '¡', '¿'], ['.', '.', '', ''], $values['short_description']);
                            }
                            $searchPage = new SearchPage($values);
                            $searchPage->persist();
                        } else {
                            $searchPage->persistSimple('views', $searchPage->get('views') + 1);
                        }
                        $this->title_page = $searchPage->getBasicInfoTitlePage();
                        $this->meta_description = $searchPage->get('meta_description');
                        $this->meta_url = url($this->action . '/' . Text::simpleUrl($this->id));
                        $this->meta_image = $searchPage->getImageUrl('image', 'web');
                        $this->content .= ($searchPage->get('short_description') != '') ? '<p class="search_short_description">' . $searchPage->get('short_description') . '</p>' : '';
                    }
                    if ($items->isEmpty()) {
                        $this->title_page = __('no_search_results');
                        $this->content = '<div class="message message_error">' . __('no_search_results_disclaimer') . '</div>';
                        $titleRecipes = __('recipes_might_like');
                        $items = new ListObjects('Recipe', ['where' => 'active="1"', 'order' => 'RAND()', 'limit' => '20']);
                    }
                    $this->content .= '
                        <h2>' . $titleRecipes . '</h2>
                        <div class="recipes recipes_category">' . $items->showList(['middle' => Adsense::midContent(), 'middleRepetitions' => 2]) . '</div>';
                    return $this->ui->render();
                } else {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url(''));
                    exit();
                }
                break;
            case 'politicas-privacidad':
            case 'terminos-condiciones':
                $this->redirecLastSlash();
                $this->layout_page = 'simple';
                $this->no_ads = true;
                $this->title_page = ($this->action == 'politicas-privacidad') ? 'Políticas de Privacidad' : 'Términos y Condiciones';
                $this->meta_description = str_replace('#SITE#', Parameter::code('meta_title_page'), HtmlSection::showFromCode($this->action . '-meta-description'));
                $this->meta_url = url($this->action);
                $this->content = str_replace('#SITE#', Parameter::code('meta_title_page'), HtmlSection::showFromCode($this->action));
                $this->content = str_replace('#SITE_URL#', preg_replace("(^https?://www.)", "", substr(url(''), 0, -1)), $this->content);
                return $this->ui->render();
                break;
            case 'sitemap':
            case 'sitemap.xml':
                $this->mode = 'xml';
                $urls = [url(''), url('top-10')];
                $urls = array_merge($urls, Category_Ui::sitemapUrls());
                $urls = array_merge($urls, SubCategory_Ui::sitemapUrls());
                $urls = array_merge($urls, Recipe_Ui::sitemapUrls());
                $urls = array_merge($urls, Post_Ui::sitemapUrls());
                $urls = array_merge($urls, SearchPage_Ui::sitemapUrls());
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
            case 'refresh-translations':
                $this->mode = 'json';
                $this->checkAuthorization();
                $translationsLink = Parameter::code('link_translations');
                $contents = json_decode(Url::getContents($translationsLink), true);
                $result = Translation::import($contents);
                return json_encode($result);
                break;
            case 'recipes-fix-steps':
                $this->mode = 'plain';
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
            case 'recipes-fix-content':
                $this->mode = 'plain';
                $this->checkAuthorization();
                $recipe = (new Recipe)->read($this->id);
                if ($recipe->id() != '') {
                    if (isset($this->parameters['save'])) {
                        $recipe->fixContent();
                        $recipe = (new Recipe)->read($this->id);
                        return $recipe->showUi('FixedContent');
                    }
                    return $recipe->showUi('FixedContent');
                } else {
                    $items = (new Recipe)->readList(['where' => 'title_page = "" OR title_page IS NULL', 'order' => 'title_url']);
                    $results = [];
                    foreach ($items as $item) {
                        $results[] = $item->urlFixContent();
                    }
                    return implode(' ', $results);
                }
                break;
            case 'add-recipe-ai':
                $this->mode = 'json';
                $this->checkAuthorization();
                $result = ['status' => 'NOK'];
                $content = (isset($this->values['content'])) ? $this->values['content'] : '';
                $categories = (new Category)->readList(['order' => 'ord']);
                $categoriesString = '';
                foreach ($categories as $category) {
                    $categoriesString .= $category->id() . '=' . $category->get('name') . ', ';
                }
                if ($content != '') {
                    $questionRecipe = 'Escribe un archivo JSON, sin ningun texto adicional, con los campos titulo, metaDescripcion (140 caracteres), descripcion (350 caracteres), descripcionHtml (descripcion sencilla del platodebe ser distinta a la descripcion, como la forma de preparacion la receta u algun dato interesante de la misma, de maximo 1000 caracteres en codigo HTML), idCategoria (' . $categoriesString . '), numeroPorciones (el número estimado de porciones), tiempoPreparacion (debe ser uno de estos valores: "5_minutes", "30_minutes", "45_minutes", "1_hour", "2_hours", "3_hours", "4_hours", "5_hours", "1_day", "2_days"), metodoPreparacion (debe ser uno de estos valores: "", "fried", "steamed", "boiled", "baked", "grilled"), ingredientes (lista, separar cada ingrediente si una linea tiene varios), pasos (lista, que incluya a todos los ingredientes, siempre en tercera persona y con un lenguaje muy amigable, se debe tener una sola linea por paso). La receta es: "' . $content . '"';
                    $questionRecipes = (ASTERION_LANGUAGE_ID == 'pt') ? 'Escreva um arquivo JSON, sem texto adicional, com os campos titulo, metaDescripcion (140 caracteres), descripcion (350 caracteres), descripcionHtml (descrição simples do prato, deve ser diferente da descrição e deve conter informações melhores sobre a receita, com no máximo 1000 caracteres em HTML), idCategoria (' . $categoriesString . '), numeroPorciones (o número estimado de porções), ingredientes (lista, separar cada ingrediente se uma linha tiver vários), pasos (lista, que inclua todos os ingredientes, sempre em terceira pessoa e com uma linguagem muito amigável, deve haver apenas uma linha por passo). A receita é: "' . $content . '"' : $questionRecipe;
                    $answerChatGPT = ChatGPT::answer($questionRecipe);
                    preg_match('/\{(?:[^{}]|(?R))*\}/', $answerChatGPT, $matches);
                    $jsonString = (isset($matches[0])) ? $matches[0] : '';
                    if ($jsonString != '') {
                        $response = json_decode($jsonString, true);
                        if (isset($response['titulo'])) {
                            $questionTitle = 'Corrige la sintaxis y ortografia de esta frase, sin usar comillas, sin poner punto final: "' . (new Recipe)->titleTest($response['titulo']) . '"';
                            $questionTitle = (ASTERION_LANGUAGE_ID == 'pt') ? 'Corrija a sintaxe e a ortografia desta frase, sem usar aspas, sem colocar ponto final: "' . (new Recipe)->titleTest($response['titulo']) . '"' : $questionTitle;
                            $response['tituloPagina'] = str_replace('"', '', str_replace('.', '', ChatGPT::answer($questionTitle)));
                        }
                        if (isset($response['descripcionHtml'])) {
                            $response['descripcionHtml'] = str_replace('. ', '.</p><p>', $response['descripcionHtml']);
                        }
                        $values = [
                            'title' => $response['titulo'],
                            'title_url' => Text::simpleUrl($response['titulo']),
                            'title_page' => $response['tituloPagina'],
                            'meta_description' => $response['metaDescripcion'],
                            'short_description' => $response['descripcion'],
                            'description' => $response['descripcionHtml'],
                            'id_category' => intval($response['idCategoria']),
                            'servings' => intval($response['numeroPorciones']),
                            'cooking_method' => $response['metodoPreparacion'],
                            'cook_time' => $response['tiempoPreparacion'],
                            'rating' => rand(4, 5),
                            'ingredients_raw' => implode("\n", $response['ingredientes']),
                            'preparation_raw' => implode("\n", $response['pasos']),
                            'active' => 0
                        ];
                        $oldRecipe = (new Recipe)->readFirst(['where' => 'title_url=:title_url'], ['title_url' => Text::simpleUrl($response['titulo'])]);
                        if ($oldRecipe->id() == '') {
                            $recipe = new Recipe($values);
                            $recipe->persist();
                            $result = ['status' => 'OK', 'recipe' => $recipe->getBasicInfo()];
                        } else {
                            $values['id_recipe'] = $oldRecipe->id();
                            $questionTitle = 'Escribe un nombre alternativo para la receta "' . $oldRecipe->getBasicInfo() . '", talvez añadiendo "tipico", "alternativo" o "tradicional". Responde solamente con el nuevo titulo, sin ningun mensaje adicional y sin signos de admiracion ni interrogacion';
                            $values['title'] = ChatGPT::answer($questionTitle);
                            $recipeVersion = new RecipeVersion($values);
                            $recipeVersion->persist();
                            $result = ['status' => 'OK', 'recipe' => $oldRecipe->getBasicInfo(), 'recipe_version' => $recipeVersion->getBasicInfo()];
                        }
                        return json_encode($result);
                    }
                }
                return json_encode($result);
                break;
            case 'json-mobile':
                $this->mode = 'json';
                $this->checkAuthorization();
                $countryCode = Parameter::code('country_code');
                $info = [
                    'site' => [
                        'title' => Parameter::code('meta_title_page'),
                        'title_page' => Parameter::code('meta_title_page_intro'),
                        'title_country' => Parameter::code('meta_title_header_bottom'),
                        'description' => Parameter::code('meta_description'),
                        'intro' => HtmlSection::show('intro_top'),
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
                    unset($infoIns['name_url']);
                    unset($infoIns['ord']);
                    $info['categories'][] = $infoIns;
                    $categories[$item->id()] = $item->getBasicInfo();
                }
                $noRecipes = (isset($this->parameters['noRecipes'])) ? true : false;
                if (!$noRecipes) {
                    $items = (new Recipe)->readList(['where' => 'active="1"', 'order' => 'title_url']);
                    $errorStep = '';
                    foreach($items as $item) {
                        $infoIns = (array)$item->values;
                        $infoIns['image'] = $item->getImageUrl('image', 'web');
                        $infoIns['image_small'] = $item->getImageUrl('image', 'small');
                        unset($infoIns['created']);
                        unset($infoIns['modified']);
                        unset($infoIns['title_url']);
                        unset($infoIns['active']);
                        unset($infoIns['ord']);
                        unset($infoIns['preparation_old']);
                        unset($infoIns['id_user']);
                        unset($infoIns['friend_links']);
                        unset($infoIns['description_bottom']);
                        
                        $infoIns['country'] = $countryCode;
                        $infoIns['url'] = $item->url();
                        $infoIns['cook_time'] = __($infoIns['cook_time']);
                        $infoIns['cooking_method'] = __($infoIns['cooking_method']);
                        $infoIns['diet'] = __($infoIns['diet']);
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
                }
                if ($errorStep!='') {
                    return "ERROR STEP - \n".$errorStep;
                }
                $content = json_encode($info, JSON_PRETTY_PRINT);
                return $content;
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
