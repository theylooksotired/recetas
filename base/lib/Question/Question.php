<?php
class Question extends Db_Object
{

    public $recipe;

    public function loadRecipe()
    {
        if (!$this->recipe) {
            $this->recipe = (new Recipe)->read($this->get('id_recipe'));
        }
        return $this->recipe;
    }

    public function getBasicInfo()
    {
        $this->loadRecipe();
        return $this->recipe->getBasicInfo();
    }

    public function toJson()
    {
        $this->loadRecipe();
        $json = json_decode(parent::toJson(), true);
        $json['recipe_name'] = $this->recipe->getBasicInfo();
        $json['recipe_url'] = $this->recipe->url();
        return json_encode($json);
    }

}
