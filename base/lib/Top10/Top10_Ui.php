<?php
class Top10_Ui extends Ui
{

    public function renderPublic($options = [])
    {
        $this->object->loadRecipe();
        return $this->object->recipe->showUi('Top10', $options);
    }

}
