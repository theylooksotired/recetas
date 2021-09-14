<?php
/**
 * @class TagUi
 *
 * This class manages the UI for the Tag objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Tag_Ui extends Ui
{

	public function renderComplete()
    {
    	$postTag = new PostTag();
        $items = new ListObjects('Post', ['join'=>'PostTag', 'where' => $postTag->tableName.'.id_tag=:id AND active="1"'], ['id'=>$this->object->id()]);
        return '<div class="posts">' . $items->showList() . '</div>';
    }

}
