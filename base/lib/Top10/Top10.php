<?php
class Top10 extends Db_Object
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

}
