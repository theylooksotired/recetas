<?php
/**
 * @class PostImage
 *
 * This class defines the relation between a project and its users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class PostImage extends Db_Object
{

    public function getImageUrl($attributeName, $version = '', $modified = false)
    {
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/enrechaiti', '', ASTERION_STOCK_FILE) : ASTERION_STOCK_FILE;
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/enrecvegetariana', '', ASTERION_STOCK_FILE) : $stockFile;
        $stockFile = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('/enrec', '/rec', $stockFile) : $stockFile;
        $stockUrl = (ASTERION_LANGUAGE_ID == 'en') ? str_replace('//en.', '//www.', ASTERION_STOCK_URL) : ASTERION_STOCK_URL;
        $version = ($version != '') ? '_' . strtolower($version) : '';
        $file = $stockFile . $this->className . '/' . $this->get($attributeName) . '/' . $this->get($attributeName) . $version . '.jpg';
        if (is_file($file)) {
            return str_replace($stockFile, $stockUrl, $file) . ($modified && ($this->get('modified') != '') ? '?v=' . Date::sqlInt($this->get('modified')) : '');
        }
    }

}
