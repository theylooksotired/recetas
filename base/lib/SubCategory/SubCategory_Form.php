<?php
class SubCategory_Form extends Form{

    public function createFormFields($options = [])
    {
        $buttonApi = ($this->object->id() != '') ? '
            <div class="button button_small button_api_recipes" data-url="' . url('sub_category/load-ai-data/' . $this->object->id(), true) . '">Cargar informacion de ChatGPT</div>
            <hr/>
            ' : '';
        return $buttonApi . parent::createFormFields($options);
    }

}
?>