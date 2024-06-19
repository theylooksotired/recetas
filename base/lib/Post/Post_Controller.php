<?php
/**
 * @class PostController
 *
 * This class is the controller for the Post objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Post_Controller extends Controller
{

    public function getContent()
    {
        $this->mode = 'admin';
        $this->object = new $this->objectType;
        $this->title_page = __((string) $this->object->info->info->form->title);
        $this->layout = (string) $this->object->info->info->form->layout;
        $this->layout_page = '';
        $this->menu_inside = $this->menuInside();
        $this->login = UserAdmin_Login::getInstance();
        $this->ui = new NavigationAdmin_Ui($this);
        LogAdmin::log($this->action . '_' . $this->objectType, $this->values);
        switch ($this->action) {
            default:
                return parent::getContent();
                break;
            case 'load-ai-data':
                $this->mode = 'json';
                $post = (new Post)->read($this->id);
                $response = [];
                if ($post->id() != '') {
                    $questionMetaDescription = 'Tienes que hacer la meta descripcion de una pagina. Para ello, resume el siguiente texto a 140 caracteres, sin usar signos de admiracion: ' . "\n\n" . strip_tags($post->get('description'));
                    $response['meta_description'] = ChatGPT::answer($questionMetaDescription);
                    $questionShortDescription = 'Haz este texto mas lindo, sin usar signos de admiracion: ' . $post->getBasicInfo() . ' ' . $post->get('short_description').'. Intenta que no sea de más de 4 líneas.';
                    $response['short_description'] = ChatGPT::answer($questionShortDescription);
                    $questionShortDescription = 'Escribe un titulo atractivo para un artículo. No uses dos puntos. Debe tener maximo 65 caracteres y se muy conciso. Usa el siguiente articulo de inspiracion: ' . "\n\n" . strip_tags($post->get('description'));
                    $response['title_page'] = ChatGPT::answer($questionShortDescription);
                }
                return json_encode($response);
                break;
        }
    }

}
