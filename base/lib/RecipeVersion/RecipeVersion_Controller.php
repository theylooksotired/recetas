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
                    $steps = $recipe->showUi('PreparationParagraph');
                    $questionSteps = 'Escribe estos pasos de forma mas clara, ordenada y en tercera persona. No uses titulos, ni la lista de ingredientes, ni ennumeres los pasos. La preparacion es: "' . $steps . '"';
                    $response['steps'] = str_replace('. ', ".\n\n", ChatGPT::answer($questionSteps));
                }
                return json_encode($response);
                break;
        }
    }

}
