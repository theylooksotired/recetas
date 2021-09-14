<?php
/**
 * @class RecipePreparation
 *
 * This class defines the relation between a project and its users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipePreparation extends Db_Object
{

    public function urlUploadTempImagePublic()
    {
        return url('cuenta/subir-imagen-temporal');
    }

    public function urlDeleteImagePublic($valueFile = '')
    {
        return url('cuenta/borrar-imagen-preparacion/' . $this->id());
    }

    public function deleteImagePublic()
    {
        $status = StatusCode::NOK;
        try {
            Image_File::deleteImage($this->className, $this->get('image'));
            $this->persistSimple('image', '');
            $status = StatusCode::OK;
        } catch (Exception $e) {};
        return ['status' => $status];
    }

}
