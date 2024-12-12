<?php
class SubCategory_Controller extends Controller {

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
                $subcategory = (new SubCategory)->read($this->id);
                $response = [];
                if ($subcategory->id() != '') {
                    $response = $subcategory->getContentChatGPT();
                }
                return json_encode($response);
                break;
        }
    }
}
?>