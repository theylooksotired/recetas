<?php
class Category extends Db_Object
{

    public function urlIndex()
    {
        return url('indice/' . $this->get('name_url'));
    }

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

    public function translate()
    {
        $categoryInfoRaw = json_decode($this->toJson(), true);
        $categoryInfo = [];
        $itemsToSet = ['name', 'short_description', 'description', 'title', 'meta_description', 'best_recipes_title', 'rest_recipes_title'];
        foreach ($itemsToSet as $item) {
            $categoryInfo[$item] = (isset($categoryInfoRaw[$item])) ? $categoryInfoRaw[$item] : '';
        }

        $categoryJson = json_encode($categoryInfo);
        $questionVersion = 'Translate this JSON file to english respecting all the key names and the same order, just translate the values: "' . $categoryJson . '"';
        $response = [];
        $maxAttempts = 3;
        $attempts = 0;
        while (empty($response['name']) && $attempts < $maxAttempts) {
            $response = ChatGPT::answerJson($questionVersion);
            $attempts++;
        }
        if (isset($response['name'])) {
            foreach ($itemsToSet as $item) {
                $value = (isset($response[$item])) ? $response[$item] : '';
                $this->persistSimple($item . '_en', $value);
            }
            $nameUrlEn = Text::simpleUrl($response['name']);
            $this->persistSimple('name_url_en', $nameUrlEn);
        }
    }

    public function iconUrl()
    {
        $iconUrl = ASTERION_BASE_FILE . 'visual/img/icon_' . $this->get('name_url') . '.svg';
        $iconDefault = ASTERION_BASE_URL . 'visual/img/icon_default.svg';
        return (file_exists($iconUrl)) ? str_replace(ASTERION_BASE_FILE, ASTERION_BASE_URL, $iconUrl) : $iconDefault;
    }

}
