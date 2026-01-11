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
        $typesEcuador = ['top' => '7347855944', 'middle' => '5108064787', 'ingredients' => '9641194404'];
        $typesCostaRica = ['top' => '8328112738', 'middle' => '4721692609', 'ingredients' => '7894010221'];
        $typesNicaragua = ['top' => '6580928553', 'middle' => '4808681889', 'ingredients' => '8855738103'];
        $typesVenezuela = ['top' => '9833777856', 'middle' => '3075786057', 'ingredients' => '3603411428'];
        $typesArgentina = ['top' => '4628540879', 'middle' => '2353731320', 'ingredients' => '9449622714'];
        $typesDominicana = ['top' => '3491730780', 'middle' => '9015520205', 'ingredients' => '7702438537'];
        $typesEspana = ['top' => '4788322973', 'middle' => '5750050855', 'ingredients' => '5076275194'];
        $typesItalia = ['top' => '4436969184', 'middle' => '3763193523', 'ingredients' => '1137030183'];
        $typesChina = ['top' => '3076797811', 'middle' => '6964712562', 'ingredients' => '3123887519'];
        $typesDiabetes = ['top' => '1057779767', 'middle' => '5909832956', 'ingredients' => '5926322438'];
        $typesJaponesas = ['top' => '9497724170', 'middle' => '6118534755', 'ingredients' => '4198307790'];
        $typesFrancesas = ['top' => '1712385883', 'middle' => '6871560834', 'ingredients' => '1987077428'];
        $typesVegetariana = ['top' => '8671727664', 'middle' => '1779016250', 'ingredients' => '2179289746'];
        $typesBrasilPt = ['top' => '3304028525', 'middle' => '7358645996', 'ingredients' => '6045564324'];
        $typesHaitianas = ['top' => '9677865185', 'middle' => '6631887687', 'ingredients' => '6839771246'];
        $typesVeganas = ['top' => '9067491094', 'middle' => '2932315823', 'ingredients' => '4213607902'];
        $typesIndias = ['top' => '4732482658', 'middle' => '4146977537', 'ingredients' => '7203745089'];
        $typesCuba = ['top' => '2011004445', 'middle' => '7657264556', 'ingredients' => '8018009593'];
        $typesGuatemala = ['top' => '1320098677', 'middle' => '5200274565', 'ingredients' => '8951176683'];
        $typesPanama = ['top' => '9007017007', 'middle' => '5118908739', 'ingredients' => '3531329040'];
        $typesSalvador = ['top' => '2218247370', 'middle' => '3805827060', 'ingredients' => '7638095012'];
        $typesJudias = ['top' => '2991255061', 'middle' => '9692394782', 'ingredients' => '6577284464'];

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
        $adSlotEcuador = (isset($typesEcuador[$type])) ? $typesEcuador[$type] : '2090660201';
        $adSlotCostaRica = (isset($typesCostaRica[$type])) ? $typesCostaRica[$type] : '2090660201';
        $adSlotNicaragua = (isset($typesNicaragua[$type])) ? $typesNicaragua[$type] : '2090660201';
        $adSlotVenezuela = (isset($typesVenezuela[$type])) ? $typesVenezuela[$type] : '2090660201';
        $adSlotArgentina = (isset($typesArgentina[$type])) ? $typesArgentina[$type] : '2090660201';
        $adSlotDominicana = (isset($typesDominicana[$type])) ? $typesDominicana[$type] : '2090660201';
        $adSlotEspana = (isset($typesEspana[$type])) ? $typesEspana[$type] : '2090660201';
        $adSlotItalia = (isset($typesItalia[$type])) ? $typesItalia[$type] : '2090660201';
        $adSlotChina = (isset($typesChina[$type])) ? $typesChina[$type] : '2090660201';
        $adSlotDiabetes = (isset($typesDiabetes[$type])) ? $typesDiabetes[$type] : '2090660201';
        $adSlotJaponesas = (isset($typesJaponesas[$type])) ? $typesJaponesas[$type] : '2090660201';
        $adSlotFrancesas = (isset($typesFrancesas[$type])) ? $typesFrancesas[$type] : '2090660201';
        $adSlotVegetariana = (isset($typesVegetariana[$type])) ? $typesVegetariana[$type] : '2090660201';
        $adSlotBrasilPt = (isset($typesBrasilPt[$type])) ? $typesBrasilPt[$type] : '2090660201';
        $adSlotHaitianas = (isset($typesHaitianas[$type])) ? $typesHaitianas[$type] : '2090660201';
        $adSlotVeganas = (isset($typesVeganas[$type])) ? $typesVeganas[$type] : '2090660201';
        $adSlotIndias = (isset($typesIndias[$type])) ? $typesIndias[$type] : '2090660201';
        $adSlotCuba = (isset($typesCuba[$type])) ? $typesCuba[$type] : '2090660201';
        $adSlotGuatemala = (isset($typesGuatemala[$type])) ? $typesGuatemala[$type] : '2090660201';
        $adSlotPanama = (isset($typesPanama[$type])) ? $typesPanama[$type] : '2090660201';
        $adSlotSalvador = (isset($typesSalvador[$type])) ? $typesSalvador[$type] : '2090660201';
        $adSlotJudias = (isset($typesJudias[$type])) ? $typesJudias[$type] : '2090660201';

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
        } elseif (Parameter::code('country_code') == 'puertorico') {
            $adSlot = $adSlotPuertoRico;
        } elseif (Parameter::code('country_code') == 'brasil') {
            $adSlot = $adSlotBrasil;
        } elseif (Parameter::code('country_code') == 'arabes') {
            $adSlot = $adSlotArabes;
        } elseif (Parameter::code('country_code') == 'ecuador') {
            $adSlot = $adSlotEcuador;
        } elseif (Parameter::code('country_code') == 'costarica') {
            $adSlot = $adSlotCostaRica;
        } elseif (Parameter::code('country_code') == 'nicaragua') {
            $adSlot = $adSlotNicaragua;
        } elseif (Parameter::code('country_code') == 'venezuela') {
            $adSlot = $adSlotVenezuela;
        } elseif (Parameter::code('country_code') == 'argentina') {
            $adSlot = $adSlotArgentina;
        } elseif (Parameter::code('country_code') == 'republicadominicana') {
            $adSlot = $adSlotDominicana;
        } elseif (Parameter::code('country_code') == 'espana') {
            $adSlot = $adSlotEspana;
        } elseif (Parameter::code('country_code') == 'italia') {
            $adSlot = $adSlotItalia;
        } elseif (Parameter::code('country_code') == 'china') {
            $adSlot = $adSlotChina;
        } elseif (Parameter::code('country_code') == 'diabetes') {
            $adSlot = $adSlotDiabetes;
        } elseif (Parameter::code('country_code') == 'japon') {
            $adSlot = $adSlotJaponesas;
        } elseif (Parameter::code('country_code') == 'francia') {
            $adSlot = $adSlotFrancesas;
        } elseif (Parameter::code('country_code') == 'vegetariana') {
            $adSlot = $adSlotVegetariana;
        } elseif (Parameter::code('country_code') == 'brasilpt') {
            $adSlot = $adSlotBrasilPt;
        } elseif (Parameter::code('country_code') == 'haiti') {
            $adSlot = $adSlotHaitianas;
        } elseif (Parameter::code('country_code') == 'veganas') {
            $adSlot = $adSlotVeganas;
        } elseif (Parameter::code('country_code') == 'india') {
            $adSlot = $adSlotIndias;
        } elseif (Parameter::code('country_code') == 'cuba') {
            $adSlot = $adSlotCuba;
        } elseif (Parameter::code('country_code') == 'guatemala') {
            $adSlot = $adSlotGuatemala;
        } elseif (Parameter::code('country_code') == 'panama') {
            $adSlot = $adSlotPanama;
        } elseif (Parameter::code('country_code') == 'salvador') {
            $adSlot = $adSlotSalvador;
        } elseif (Parameter::code('country_code') == 'judias') {
            $adSlot = $adSlotJudias;
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