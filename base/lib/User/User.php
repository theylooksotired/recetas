<?php
/**
 * @class User
 *
 * This class defines the users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class User extends User_Interface
{

    public $userClassName = 'User';
    public $userLoginClassName = 'User_Login';
    public $userFormClassName = 'User_Form';

    public function __construct($values = [])
    {
        parent::__construct($values);

        $this->urlLogin = url('conectarse');
        $this->urlRegister = url('registrarse');
        $this->urlActivate = url('activar');
        $this->urlForgot = url('contrasena-olvidada');
        $this->urlUpdateDefaultPassword = url('cuenta/cambiar-contrasena-temporal');
        $this->urlUpdatePassword = url('cuenta/contrasena');
        $this->urlUpdateEmail = url('cuenta/cambiar-email');
        $this->urlUpdateEmailConfirm = url('cuenta/confirmar-email');
        $this->urlProfile = url('cuenta/perfil');
        $this->urlLogout = url('salir');
        $this->urlDeleteImage = url('cuenta/borrar-imagen');
        $this->urlUploadTempImage = url('cuenta/subir-imagen');
        $this->urlHome = url('');
        $this->urlConnected = url('cuenta');
    }

}
