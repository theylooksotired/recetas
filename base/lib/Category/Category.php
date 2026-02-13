<?php
class Category extends Db_Object
{

	public function getTitlePage()
    {
        return ($this->get('title')!='') ? $this->get('title') : $this->get('name');
    }

    public function toJson()
    {
        $infoIns = (array)$this->values;
        unset($infoIns['created']);
        unset($infoIns['modified']);
        unset($infoIns['ord']);
        return json_encode($infoIns);
    }

    static public function allToJson()
    {
        $categories = [];
        $items = (new Category)->readList(['order' => 'ord']);
        foreach($items as $item) {
            $infoIns = (array)$item->values;
            unset($infoIns['created']);
            unset($infoIns['modified']);
            unset($infoIns['image']);
            unset($infoIns['name_url']);
            unset($infoIns['ord']);
            $info['categories'][] = $infoIns;
            $categories[$item->id()] = $item->getBasicInfo();
        }
        return $categories;
    }

    static public function arrayCategories()
    {
        $categories = (new Category)->readList();
        $categoriesIds = [];
        foreach ($categories as $category) {
            $categoriesIds[$category->id()] = $category;
        }
        return $categoriesIds;
    }

}
