<?php
/**
 * @class SearchPage
 *
 * This class defines the users.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class SearchPage extends Db_Object
{

    public function getBasicInfoTitlePage()
    {
        return ($this->get('title_page') != '') ? $this->get('title_page') : str_replace('#SEARCH#', ucwords($this->getBasicInfo()), __('search_results_of'));
    }

}
