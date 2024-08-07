<?php
/**
 * @file
 *
 * The index.php file is one of the main files on the Asterion framework.
 * It is in charge of loading the configuration, intializing the site,
 * loading the content variables and handling the HTML response to the user.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion
 * @version 3.0.1
 */

/**
 * The APP_FOLDER constant defines the public folder of the application.
 * It can be used to load different versions of it like test, develop or production.
 * Then, Asterion loads the proper configuration depending on that version.
 */

define('APP_FOLDER', 'base');
require_once APP_FOLDER . '/config/config.php';

try {

    /**
     * If the ASTERION_DEBUG mode is activated on the configuration file, Asterion allows
     * error reporting and runs the script to create the basic tables.
     * It also saves the default data for the administration system.
     */
    if (ASTERION_DEBUG) {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        Debug::intializeSession();
    }

    /**
     * Asterion initializes the request.
     * It will check the database, load languages and parameters.
     */
    Debug::startRecordingMainFunction('Initialization');
    Init::initSite();
    Debug::stopRecordingMainFunction('Initialization');

    /**
     * Asterion loads the controller according to the "type" variable
     * defined in the URL, by default it will use the Navigation controller.
     * Then it loads the content and some extra informations for the template.
     */
    Debug::startRecordingMainFunction('Content');
    $control = Controller_Factory::factory($_GET, $_POST, $_FILES);
    $content = $control->getContent();
    $title = $control->getTitle();
    $head = $control->getHead();
    $metaKeywords = $control->getMetaKeywords();
    $metaDescription = $control->getMetaDescription();
    $metaImage = $control->getMetaImage();
    $metaUrl = $control->getMetaUrl();
    $mode = $control->getMode();
    Debug::stopRecordingMainFunction('Content');
} catch (Exception $e) {
    $mode = 'ajax';
    $content = (ASTERION_DEBUG) ? '<pre>' . $e->getMessage() . '</pre><pre>' . $e->getTraceAsString() . '</pre>' : '';
}
/**
 * Asterion checks the "mode" variable to return the response.
 * By default it uses the public.php template, however it is possible to
 * create or add customized headers to the response.
 */
$mode = (isset($mode)) ? $mode : 'public';
switch ($mode) {
    default:
        $file = ASTERION_BASE_FILE . 'visual/templates/' . $mode . '.php';
        if (file_exists($file)) {
            include $file;
            if (ASTERION_DEBUG) {
                echo Debug_Ui::showInformation();
            }
        }
        break;
    case 'admin':
        include ASTERION_APP_FILE . 'visual/templates/admin.php';
        break;
    case 'ajax':
        echo $content;
        break;
    case 'plain':
        header('Content-Type: text/plain');
        echo $content;
        break;
    case 'json':
        header('Content-Type: application/json');
        echo $content;
        break;
    case 'xml':
        header('Content-Type: text/xml');
        echo $content;
        break;
    case 'js':
        header('Content-Type: application/javascript');
        echo $content;
        break;
    case 'zip':
        header('Content-Type: application/zip');
        echo $content;
        break;
}
