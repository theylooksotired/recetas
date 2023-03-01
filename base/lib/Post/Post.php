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

    public function getBasicInfoTitle()
    {
        return str_replace('"', '', $this->getBasicInfo());
    }

    public function url()
    {
        return url('articulos/' . $this->get('title_url'));
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

}
