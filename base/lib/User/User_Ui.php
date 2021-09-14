<?php
/**
 * @class UserUi
 *
 * This class manages the UI for the User objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\Base
 * @version 4.0.0
 */
class User_Ui extends Ui
{

    public function renderComplete()
    {
        $socialLinksHtml = '';
        $socialLinksArray = ['link_facebook', 'link_twitter', 'link_instagram', 'link_tumblr'];
        foreach ($socialLinksArray as $socialLink) {
            if ($this->object->get($socialLink) != '') {
                $socialLinksHtml .= '
                    <div class="item_info_link user_info_' . $socialLink . '">
                        <a href="' . Url::format($this->object->get($socialLink)) . '" target="_blank">
                            <i class="fa fa-' . str_replace('link_', '', $socialLink) . '"></i>
                        </a>
                    </div>';
            }
        }
        $socialLinksHtml = ($socialLinksHtml!='') ? '<div class="item_social">'.$socialLinksHtml.'</div>' : '';
        return '
            <div class="item_complete">
                <div class="item_info">
                    <div class="item_info_ins">
                        <div class="item_info_image">' . $this->object->getImageAmp('image', 'small') . '</div>
                        <div class="item_info_wrapper">
                            <h1>' . $this->object->getBasicInfo() . '</h1>
                            ' . (($this->object->get('short_description') != '') ? '
                            <div class="item_info_description">' . $this->object->get('short_description') . '</div>
                            ' : '') . '
                            ' . $socialLinksHtml . '
                        </div>
                    </div>
                </div>
            </div>';
    }

}
