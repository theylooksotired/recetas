<?php
/**
 * @class PostForm
 *
 * This class manages the forms for the Post objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Post_Form extends Form
{

    public function createFormFields($options = [])
    {
        $buttonApi = ($this->object->id() != '') ? '
            <div class="button button_small button_api_posts" data-url="' . url('post/load-ai-data/' . $this->object->id(), true) . '">Cargar informacion de ChatGPT</div>
            <hr/>
            ' : '';
        return $buttonApi . parent::createFormFields($options);
    }

}
