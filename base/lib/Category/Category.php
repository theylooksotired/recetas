<?php
class Category extends Db_Object
{

	public function getTitlePage()
    {
        return ($this->get('title')!='') ? $this->get('title') : $this->get('name');
    }

}
