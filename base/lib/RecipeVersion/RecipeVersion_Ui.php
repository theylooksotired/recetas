<?php
/**
 * @class RecipeVersionUi
 *
 * This class manages the UI for the RecipeVersion objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class RecipeVersion_Ui extends Ui
{

    public function renderLinkAdmin()
    {
        return '<a href="' . $this->object->urlModify() . '" target="_blank">' . $this->object->getBasicInfo() . '</a> ';
    }

    public function renderPreparationParagraph()
    {
        $this->object->loadMultipleValues();
        $html = '';
        foreach ($this->object->get('preparation') as $preparation) {
            $html .= $preparation->get('step') . ' ';
        }
        return $html;
    }

}
