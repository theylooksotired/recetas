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
        $typesHonduras = ['top'=>'4617928436', 'ingredients' => '5820147704', 'middle' => '4673768758'];
        $typesPeru = ['top'=>'2176577868', 'ingredients' => '1193850444', 'middle' => '8023994431'];
        $typesBolivia = ['top' => '2620490486', 'middle' => '2737052319', 'ingredients' => '1423970647'];
        $typesUruguay = ['top' => '2361647795', 'middle' => '1048566122', 'ingredients' => '4492987111'];
        $typesColombia = ['top' => '3179905443', 'middle' => '8008548530', 'ingredients' => '1866823771'];
        $typesParaguay = ['top' => '7422402789', 'middle' => '5055082130', 'ingredients' => '1942845397'];
        $typesChile = ['top' => '2428918795', 'middle' => '6096701069', 'ingredients' => '8240660430'];
        $typesMexico = ['top' => '1232398958', 'middle' => '6927578765', 'ingredients' => '5614497098'];
        $typesPuertoRico = ['top' => '2756221854', 'middle' => '8531292718', 'ingredients' => '4301415425'];
        $typesBrasil = ['top' => '4796239447', 'middle' => '3483157779', 'ingredients' => '5363856284'];
        $typesArabes = ['top' => '1751914337', 'middle' => '2737692947', 'ingredients' => '1424611273'];

        $adSlotPeru = (isset($typesPeru[$type])) ? $typesPeru[$type] : '2090660201';
        $adSlotHonduras = (isset($typesHonduras[$type])) ? $typesHonduras[$type] : '2090660201';
        $adSlotBolivia = (isset($typesBolivia[$type])) ? $typesBolivia[$type] : '2090660201';
        $adSlotUruguay = (isset($typesUruguay[$type])) ? $typesUruguay[$type] : '2090660201';
        $adSlotColombia = (isset($typesColombia[$type])) ? $typesColombia[$type] : '2090660201';
        $adSlotParaguay = (isset($typesParaguay[$type])) ? $typesParaguay[$type] : '2090660201';
        $adSlotChile = (isset($typesChile[$type])) ? $typesChile[$type] : '2090660201';
        $adSlotMexico = (isset($typesMexico[$type])) ? $typesMexico[$type] : '2090660201';
        $adSlotPuertoRico = (isset($typesPuertoRico[$type])) ? $typesPuertoRico[$type] : '2090660201';
        $adSlotBrasil = (isset($typesBrasil[$type])) ? $typesBrasil[$type] : '2090660201';
        $adSlotArabes = (isset($typesArabes[$type])) ? $typesArabes[$type] : '2090660201';

        if (Parameter::code('country_code') == 'honduras') {
            $adSlot = $adSlotHonduras;
        } elseif (Parameter::code('country_code') == 'peru') {
            $adSlot = $adSlotPeru;
        } elseif (Parameter::code('country_code') == 'bolivia') {
            $adSlot = $adSlotBolivia;
        } elseif (Parameter::code('country_code') == 'uruguay') {
            $adSlot = $adSlotUruguay;
        } elseif (Parameter::code('country_code') == 'colombia') {
            $adSlot = $adSlotColombia;
        } elseif (Parameter::code('country_code') == 'paraguay') {
            $adSlot = $adSlotParaguay;
        } elseif (Parameter::code('country_code') == 'chile') {
            $adSlot = $adSlotChile;
        } elseif (Parameter::code('country_code') == 'mexico') {
            $adSlot = $adSlotMexico;
        } elseif (Parameter::code('country_code') == 'puerto_rico') {
            $adSlot = $adSlotPuertoRico;
        } elseif (Parameter::code('country_code') == 'brasil') {
            $adSlot = $adSlotBrasil;
        } elseif (Parameter::code('country_code') == 'arabes') {
            $adSlot = $adSlotArabes;
        } else {
            $adSlot = '2090660201';
        }
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