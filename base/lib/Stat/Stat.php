<?php
/**
 * @class Stat
 *
 * This class defines the relation between a project and its users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class Stat extends Db_Object
{

    public static function log() {
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $isRobot = preg_match('/(facebookexternalhit|googlebot|bingbot|slurp|duckduckbot|baiduspider|yandexbot|crawler)/i', $userAgent) > 0;
        
        $stat = new Stat([
            'url' => $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'robot' => $isRobot,
        ]);
        $stat->persist();
    }

}
