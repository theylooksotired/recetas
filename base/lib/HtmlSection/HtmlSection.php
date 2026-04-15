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

    public function toJson()
    {
        $infoIns = (array)$this->values;
        unset($infoIns['created']);
        unset($infoIns['modified']);
        unset($infoIns['ord']);
        return json_encode($infoIns);
    }

    public function translate()
    {
        $htmlSectionInfoRaw = json_decode($this->toJson(), true);
        $htmlSectionInfo = [];
        $itemsToSet = ['code', 'title_es', 'section_es'];
        foreach ($itemsToSet as $item) {
            $htmlSectionInfo[str_replace('_es', '', $item)] = (isset($htmlSectionInfoRaw[$item])) ? $htmlSectionInfoRaw[$item] : '';
        }

        $htmlSectionJson = json_encode($htmlSectionInfo);
        $questionVersion = 'Translate this JSON file to english respecting all the key names and the same order, just translate the values: "' . $htmlSectionJson . '"';
        $response = [];
        $maxAttempts = 3;
        $attempts = 0;
        while (empty($response['title']) && $attempts < $maxAttempts) {
            $response = ChatGPT::answerJson($questionVersion);
            $attempts++;
        }
        if (isset($response['title'])) {
            $this->persistSimple('code_en', $response['code']);
            $this->persistSimple('title_en', $response['title']);
            $this->persistSimple('section_en', $response['section']);
            $titleUrlEn = Text::simpleUrl($response['title']);
            $this->persistSimple('title_url_en', $titleUrlEn);
        }
    }

}
