<?php
/**
 * @class Post
 *
 * This class defines the users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Post extends Db_Object
{

    public function getTitlePage()
    {
        return $this->getBasicInfo();
    }

    public function getBasicInfoTitle()
    {
        return str_replace('"', '', $this->getBasicInfo());
    }

    public function url()
    {
        return url('articulos/' . $this->get('title_url'));
    }

    public function urlEs()
    {
        return url('articulos/' . $this->values['title_url']);
    }
    
    public function urlEn()
    {
        return url('articulos/' . $this->values['title_url_en']);
    }

    public function urlUploadTempImagePublic()
    {
        return url('cuenta/subir-imagen-temporal');
    }

    public function urlDeleteImagePublic($valueFile = '')
    {
        return url('cuenta/borrar-imagen-articulo/' . $this->id());
    }

    public function deleteImagePublic()
    {
        $status = StatusCode::NOK;
        try {
            Image_File::deleteImage($this->className, $this->get('image'));
            $this->persistSimple('image', '');
            $status = StatusCode::OK;
        } catch (Exception $e) {dump($e);};
        return ['status' => $status];
    }

    public function createPublic($values)
    {
        $status = StatusCode::NOK;
        $content = (new Post_Form())->create();
        if (count($values) > 0) {
            $login = User_Login::getInstance();
            $values['active'] = 0;
            $values['id_user'] = $login->id();
            $post = new Post($values);
            $persist = $post->persist();
            if ($persist['status'] == StatusCode::OK) {
                Session::flashInfo(__('saved_post_message'));
                $status = StatusCode::OK;
                $form = Post_Form::fromObject($post);
                $content = $form->edit();
                return ['status' => $status, 'content' => $content, 'post' => $post];
            } else {
                Session::flashError(__('errors_form'));
                $content = (new Post_Form($persist['values'], $persist['errors']))->create();
            }
        }
        return ['status' => $status, 'content' => $content];
    }

    public function editPublic($values)
    {
        $status = StatusCode::NOK;
        $form = Post_Form::fromObject($this);
        $content = $form->edit();
        if (count($values) > 0) {
            $login = User_Login::getInstance();
            $values['active'] = 0;
            $values['id'] = $this->id();
            $values['id_user'] = $login->id();
            $post = new Post($values);
            $persist = $post->persist();
            if ($persist['status'] == StatusCode::OK) {
                Session::flashInfo(__('saved_post_message'));
                $form = Post_Form::fromObject($post);
                $content = $form->edit();
            } else {
                Session::flashError(__('errors_form'));
                $content = (new Post_Form($persist['values'], $persist['errors']))->edit();
            }
        }
        return ['status' => $status, 'content' => $content];
    }

    public function loadTranslation()
    {
        if (!isset($this->translation_url)) {
            $this->translation_url = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('//en.', '//www.', $this->urlEs()) : str_replace('//www.', '//en.', $this->urlEn());
        }
        return $this->translation_url;
    }

    public function toJson()
    {
        $infoIns = (array)$this->values;
        unset($infoIns['created']);
        unset($infoIns['modified']);
        unset($infoIns['ord']);

        $images = (new PostImage)->readList(['where' => 'id_post=:id_post', 'order'=>'ord'], ['id_post' => $infoIns['id']]);
        $infoIns['images'] = [];
        foreach ($images as $image) {
            $infoImage = (array)$image->values;
            unset($infoImage['created']);
            unset($infoImage['modified']);
            unset($infoImage['id_post']);
            unset($infoImage['ord']);
            $infoImage['image'] = $image->getImageUrl('image', 'web');
            $infoImage['image_small'] = $image->getImageUrl('image', 'small');
            $infoIns['images'][] = $infoImage;
        }

        return json_encode($infoIns);
    }

    public function translate()
    {
        $postInfoRaw = json_decode($this->toJson(), true);
        $postInfo = [];
        $itemsToSet = ['title', 'title_page', 'meta_description', 'short_description', 'description', 'images'];
        foreach ($itemsToSet as $item) {
            $postInfo[$item] = (isset($postInfoRaw[$item])) ? $postInfoRaw[$item] : '';
        }

        $cleanImages = [];
        foreach ($postInfo['images'] as $key => $image) {
            $cleanImages[$image['id']] = $image['title'];
        }
        $postInfo['images'] = $cleanImages;

        $postJson = json_encode($postInfo);
        $questionVersion = 'Translate this JSON file to english respecting all the key names and the same order, just translate the values: "' . $postJson . '"';
        $response = [];
        $maxAttempts = 3;
        $attempts = 0;
        while (empty($response['title']) && $attempts < $maxAttempts) {
            $response = ChatGPT::answerJson($questionVersion);
            $attempts++;
        }
        if (isset($response['title'])) {
            foreach ($itemsToSet as $item) {
                if ($item == 'images') {
                    continue;
                }
                $value = (isset($response[$item])) ? $response[$item] : '';
                $this->persistSimple($item . '_en', $value);
            }
            $titleUrlEn = Text::simpleUrl($response['title']);
            $this->persistSimple('title_url_en', $titleUrlEn);

            $images = (new PostImage)->readList(['where' => 'id_post=:id_post', 'order'=>'ord'], ['id_post' => $this->id()]);
            foreach ($images as $image) {
                $label = (isset($response['images'][$image->id()])) ? $response['images'][$image->id()] : '';
                $image->persistSimple('title_en', $label);
            }
        }
    }

    public function getImageUrl($attributeName, $version = '', $modified = false)
    {
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/enrec', '/rec', ASTERION_STOCK_FILE) : ASTERION_STOCK_FILE;
        $stockUrl = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('//en.', '//www.', ASTERION_STOCK_URL) : ASTERION_STOCK_URL;
        $version = ($version != '') ? '_' . strtolower($version) : '';
        $file = $stockFile . $this->className . '/' . $this->get($attributeName) . '/' . $this->get($attributeName) . $version . '.jpg';
        if (is_file($file)) {
            return str_replace($stockFile, $stockUrl, $file) . ($modified && ($this->get('modified') != '') ? '?v=' . Date::sqlInt($this->get('modified')) : '');
        }
    }

}
