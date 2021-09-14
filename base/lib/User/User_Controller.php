<?php
/**
 * @class UserController
 *
 * This class is the controller for the User objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class User_Controller extends User_Controller_Interface
{

    public $userClassName = 'User';
    public $layout_page = 'simple';

    public function __construct($GET, $POST, $FILES)
    {
        parent::__construct($GET, $POST, $FILES);
        $this->ui = new Navigation_Ui($this);
    }

}
