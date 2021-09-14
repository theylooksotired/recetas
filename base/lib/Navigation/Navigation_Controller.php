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
                $recipe = ($this->id != '' && $category->id() != '') ? (new Recipe)->readFirst(['where' => 'title_url=:title_url AND id_category=:id_category'], ['title_url' => $this->id, 'id_category' => $category->id()]) : new Recipe();
                $item = ($category->id() != '') ? $category : Page::code($this->action);
                $item = ($recipe->id() != '') ? $recipe : $item;
                if ($item->id() != '') {
                    $this->title_page = $item->getBasicInfo();
                    $this->url_page = $item->url();
                    $this->meta_url = $item->url();
                    $this->meta_image = $item->getImageUrl('image', 'web');
                    $this->meta_description = $item->get('shortDescription');
                    $this->bread_crumbs = array($item->url() => $item->getBasicInfo());
                    $this->content = $item->showUi('Complete');
                    if ($recipe->id() != '') {
                        $this->head = '
                            ' . $item->showUi('JsonHeader') . '
                            ' . $this->ampFacebookCommentsHeader();
                    }
                    return $this->ui->render();
                } else {
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . url(''));
                }
                break;
            case 'intro':
                $this->content = '
                    ' . HtmlSection::show('intro') . '
                    ' . Category_Ui::intro() . '
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
                    $this->head = '
                        ' . $post->showUi('JsonHeader') . '
                        ' . $this->ampFacebookCommentsHeader();
                    $this->content = $post->showUi('Complete');
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
                        'where' => 'active="1" AND MATCH (title, title_url, short_description) AGAINST ("' . $search . '" IN BOOLEAN MODE)', 'order' => 'MATCH (title, title_url, short_description) AGAINST ("' . $search . '" IN BOOLEAN MODE) DESC',
                        'limit' => '20',
                    ]);
                    if ($items->isEmpty()) {
                        $items = new ListObjects('Recipe', [
                            'where' => 'active="1" AND CONCAT(title," ",title_url," ",short_description) LIKE ("%' . $search . '%")', 'order' => 'title_url',
                            'limit' => '20',
                        ]);
                    }
                    if ($items->isEmpty()) {
                        $this->titlePage = __('no_search_results');
                        //$items = new ListObjects('Recipe', ['where'=>'active="1"', 'order'=>'RAND()', 'limit'=>'20']);
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
            case 'conectarse':
            case 'registrarse':
            case 'activar':
            case 'contrasena-olvidada':
            case 'salir':
                $actions = [
                    'conectarse' => 'login',
                    'registrarse' => 'register',
                    'activar' => 'activate',
                    'contrasena-olvidada' => 'forgot',
                    'salir' => 'logout',
                ];
                $userController = new User_Controller($this->parameters, $this->values, $this->files);
                $userController->action = $actions[$this->action];
                $content = $userController->getContent();
                $this->head = $userController->getHead();
                return $content;
                break;
            case 'cuenta':
                if ($this->id == 'cambiar-contrasena-temporal') {
                    $this->login->checkLoginSimpleRedirect();
                } else {
                    $this->login->checkLoginRedirect();
                }
                $this->layout_page = 'connected';
                $userController = new User_Controller($this->parameters, $this->values, $this->files);
                $this->head = '
                    <link href="' . ASTERION_APP_URL . 'helpers/jquery-ui/jquery-ui.min.css" rel="stylesheet">
                    <script src="https://cdn.ckeditor.com/ckeditor5/25.0.0/classic/ckeditor.js"></script>
                    <script type="text/javascript" src="' . ASTERION_BASE_URL . 'libjs/public_connected.js"></script>
                    <script type="text/javascript" src="' . ASTERION_APP_URL . 'helpers/jquery-ui/jquery-ui.min.js"></script>';
                switch ($this->id) {
                    default:
                        $this->content = '
                            ' . HtmlSection::show('intro_connected');
                        break;
                    case 'perfil':
                    case 'contrasena':
                    case 'cambiar-email':
                    case 'confirmar-email':
                    case 'cambiar-contrasena-temporal':
                    case 'subir-imagen':
                    case 'borrar-imagen':
                        $actions = [
                            'perfil' => 'profile',
                            'contrasena' => 'update_password',
                            'cambiar-email' => 'update_email',
                            'confirmar-email' => 'update_email_confirm',
                            'cambiar-contrasena-temporal' => 'update_default_password',
                            'subir-imagen' => 'upload_profile_picture',
                            'borrar-imagen' => 'delete_profile_picture',
                        ];
                        $userController->action = $actions[$this->id];
                        $userController->layout_page = 'connected';
                        $content = $userController->getContent();
                        $this->mode = $userController->getMode();
                        $this->head .= $userController->getHead();
                        return $content;
                        break;
                    // RECIPES
                    case 'recetas':
                        $this->title_page = __('recipes');
                        $this->content = Recipe_Ui::menuConnected(['upload']) . Recipe_Ui::introConnected();
                        break;
                    case 'subir-receta':
                        $this->title_page = __('recipes');
                        $action = (new Recipe)->createPublic($this->values);
                        if ($action['status'] == StatusCode::OK) {
                            header('Location: ' . url('cuenta/editar-receta/' . $action['recipe']->id()));
                            exit();
                        }
                        $this->content = Recipe_Ui::menuConnected(['upload', 'list']) . $action['content'];
                        break;
                    case 'editar-receta':
                        $this->title_page = __('recipes');
                        $recipe = (new Recipe)->read($this->extraId);
                        if ($recipe->id() != '' && $recipe->get('id_user') == $this->login->id() && $recipe->get('active') != '1') {
                            $action = $recipe->editPublic($this->values);
                            $this->content = Recipe_Ui::menuConnected(['upload', 'list']) . $action['content'];
                        } else {
                            header('Location: ' . url('cuenta/recetas'));
                            exit();
                        }
                        break;
                    case 'borrar-receta':
                        $recipe = (new Recipe)->read($this->extraId);
                        if ($recipe->id() != '' && $recipe->get('id_user') == $this->login->id() && $recipe->get('active') != '1') {
                            $recipe->delete();
                        }
                        header('Location: ' . url('cuenta/recetas'));
                        exit();
                        break;
                    case 'borrar-imagen-receta':
                        $this->mode = 'json';
                        $response = ['status' => StatusCode::NOK];
                        $recipe = (new Recipe)->read($this->extraId);
                        if ($recipe->id() != '' && $recipe->get('id_user') == $this->login->id() && $recipe->get('active') != '1') {
                            $response = $recipe->deleteImagePublic();
                        }
                        return json_encode($response);
                        break;
                    case 'borrar-imagen-preparacion':
                        $this->mode = 'json';
                        $response = ['status' => StatusCode::NOK];
                        $recipePreparation = (new RecipePreparation)->read($this->extraId);
                        if ($recipePreparation->id() != '' && $recipePreparation->get('id_user') == $this->login->id() && $recipePreparation->get('active') != '1') {
                            $response = $recipePreparation->deleteImagePublic();
                        }
                        return json_encode($response);
                        break;
                    // POSTS
                    case 'articulos':
                        $this->title_page = __('posts');
                        $this->content = Post_Ui::menuConnected(['upload']) . Post_Ui::introConnected();
                        break;
                    case 'subir-articulo':
                        $this->title_page = __('posts');
                        $action = (new Post)->createPublic($this->values);
                        if ($action['status'] == StatusCode::OK) {
                            header('Location: ' . url('cuenta/editar-articulo/' . $action['post']->id()));
                            exit();
                        }
                        $this->content = Post_Ui::menuConnected(['upload', 'list']) . $action['content'];
                        break;
                    case 'editar-articulo':
                        $this->title_page = __('posts');
                        $post = (new Post)->read($this->extraId);
                        if ($post->id() != '' && $post->get('id_user') == $this->login->id() && $post->get('active') != '1') {
                            $action = $post->editPublic($this->values);
                            $this->content = Post_Ui::menuConnected(['upload', 'list']) . $action['content'];
                        } else {
                            header('Location: ' . url('cuenta/articulos'));
                            exit();
                        }
                        break;
                    case 'borrar-articulo':
                        $post = (new Post)->read($this->extraId);
                        if ($post->id() != '' && $post->get('id_user') == $this->login->id() && $post->get('active') != '1') {
                            $post->delete();
                        }
                        header('Location: ' . url('cuenta/articulos'));
                        exit();
                        break;
                    case 'borrar-imagen-articulo':
                        $this->mode = 'json';
                        $response = ['status' => StatusCode::NOK];
                        $post = (new Post)->read($this->extraId);
                        if ($post->id() != '' && $post->get('id_user') == $this->login->id() && $post->get('active') != '1') {
                            $response = $post->deleteImagePublic();
                        }
                        return json_encode($response);
                        break;
                    // Upload temporary images
                    case 'subir-imagen-temporal':
                        $this->mode = 'json';
                        $response = File::uploadTempImage($this->values);
                        return json_encode($response);
                        break;
                }
                return $this->ui->render();
                break;
        }
    }

    public function ampFacebookCommentsHeader()
    {
        return '<script async custom-element="amp-facebook-comments" src="https://cdn.ampproject.org/v0/amp-facebook-comments-0.1.js"></script>';
    }

    public function ampListHeader()
    {
        return '
            <script async custom-element="amp-list" src="https://cdn.ampproject.org/v0/amp-list-0.1.js"></script>
            <script async custom-template="amp-mustache" src="https://cdn.ampproject.org/v0/amp-mustache-0.2.js"></script>';
    }

}
