<?php
/**
 * @class HtmlSection
 *
 * This class represents a simple HTML section on a website.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class HtmlSection extends Db_Object
{

    /**
     * Load an object using its code
     */
    public static function code($code)
    {
        HtmlSection::loadHtmlSections();
        return (isset($GLOBALS['html_sections'][$code])) ? $GLOBALS['html_sections'][$code] : new HtmlSection();
    }

    /**
     * Show directly the content of a page just using its code
     */
    public static function show($code)
    {
        HtmlSection::loadHtmlSections();
        $html = HtmlSection::code($code);
        return $html->showUi('Section');
    }

    public static function showFromCode($code)
    {
        return file_get_contents(ASTERION_BASE_FILE . 'lib/HtmlSection/data/' . $code . '.html');
    }

    public static function loadHtmlSections()
    {
        if (!isset($GLOBALS['html_sections'])) {
            $htmlSections = (new HtmlSection)->readList();
            $GLOBALS['html_sections'] = [];
            foreach ($htmlSections as $htmlSection) {
                $GLOBALS['html_sections'][$htmlSection->get('code')] = $htmlSection;
            }
        }
    }

}
