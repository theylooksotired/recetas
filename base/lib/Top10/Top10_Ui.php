<?php
class Top10_Ui extends Ui
{

    public function renderPublic($options = [])
    {
        $this->object->loadRecipe();
        $this->object->recipe->loadTranslated(true);
        return $this->object->recipe->showUi('Top10', $options);
    }

}
