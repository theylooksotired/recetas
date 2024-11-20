<?php
/**
 * @class RecipeVersionController
 *
 * This class is the controller for the RecipeVersion objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeVersion_Controller extends Controller
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
            case 'steps-ai-data':
                $this->mode = 'json';
                $recipe = (new RecipeVersion)->read($this->id);
                $response = [];
                if ($recipe->id() != '') {
                    $response['steps'] = $recipe->getPreparationChatGPT();
                }
                return json_encode($response);
                break;
            case 'full-ai-recipe':
                $this->mode = 'json';
                $response = [];
                $content = (isset($this->values['content'])) ? $this->values['content'] : '';
                if ($content != '') {
                    $questionRecipe = 'Escribe un archivo JSON, sin ningun texto adicional, con los campos titulo, metaDescripcion (140 caracteres), descripcion (350 caracteres), descripcionHtml (descripcion sencilla del plato, debe ser distinta a la descripcion y debe contener mejor informacion sobre la receta, de maximo 1000 caracteres en codigo HTML), ingredientes (lista, separar cada ingrediente si una linea tiene varios), pasos (lista, que incluya a todos los ingredientes, siempre en tercera persona y con un lenguaje muy amigable, se debe tener una sola linea por paso). La receta es: "' . $content . '"';
                    $answerChatGPT = ChatGPT::answer($questionRecipe);
                    preg_match('/\{(?:[^{}]|(?R))*\}/', $answerChatGPT, $matches);
                    $jsonString = (isset($matches[0])) ? $matches[0] : '';
                    if ($jsonString != '') {
                        $response = json_decode($jsonString, true);
                        if (isset($response['titulo'])) {
                            $questionTitle = 'Corrige la sintaxis y ortografia de esta frase, sin usar comillas, sin poner punto final: "' . (new Recipe)->titleTest($response['titulo']) . '"';
                            $response['tituloPagina'] = str_replace('"', '', str_replace('.', '', ChatGPT::answer($questionTitle)));
                        }
                        if (isset($response['descripcionHtml'])) {
                            $response['descripcionHtml'] = str_replace('. ', '.</p><p>', $response['descripcionHtml']);
                        }
                        return json_encode($response);
                    }
                }
                return '';
                break;
        }
    }

}
