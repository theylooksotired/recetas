<?php
/**
* @class Adsense
*
* This class is used to render adsense blocks.
*
* @author Leano Martinet <info@asterion-cms.com>
* @package Asterion
* @version 3.0.1
*/
class Adsense {

    static public function responsive($type = '') {
        if ($type == 'bottom') return ''; // Disable bottom ads for now
        if ($type == 'preparation') return ''; // Disable preparation ads for now
        $types = ['top'=>'2176577868', 'ingredients' => '1193850444', 'preparation' => '8717935483', 'bottom' => '5599999310', 'middle' => '8023994431'];
        $adSlot = (isset($types[$type])) ? $types[$type] : '2090660201';
        $adSlot = (Parameter::code('country_code') == 'peru') ? $adSlot : '2090660201';
        if (ASTERION_DEBUG) return '<div class="adsense adsenseInline adsenseTest">Ad - ' . (($type!='') ? $type : 'default') . ' - ' . $adSlot . '</div>';
        return '
            <div class="adsense">
                <ins class="adsbygoogle"
                    style="display:block"
                    data-ad-client="ca-pub-7429223453905389"
                    data-ad-slot="' . $adSlot . '"
                    data-ad-format="auto"
                    data-full-width-responsive="true"></ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            </div>';
    }

}
?>