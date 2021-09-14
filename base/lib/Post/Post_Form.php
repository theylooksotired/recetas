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

    public function create()
    {
        $fields = '
            ' . $this->field('image') . '
            ' . $this->field('title') . '
            ' . $this->field('short_description') . '
            <div class="textarea_ck_editor">' . $this->field('description') . '</div>
            ' . $this->field('categories');
        return Form::createForm($fields, ['submit' => __('save'), 'action' => url('cuenta/subir-articulo'), 'class'=>'form_site form_site_border']);
    }

    public function edit()
    {
        if ($this->object->id() == '') {
            return '';
        }
        $fields = '
            ' . $this->field('image') . '
            ' . $this->field('title') . '
            ' . $this->field('short_description') . '
            <div class="textarea_ck_editor">' . $this->field('description') . '</div>
            ' . $this->field('categories');
        return Form::createForm($fields, ['submit' => __('save'), 'action' => url('cuenta/editar-articulo/' . $this->object->id()), 'class'=>'form_site form_site_border']);
    }

}
