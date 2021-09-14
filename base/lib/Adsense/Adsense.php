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

    static public function amp() {
        if (ASTERION_DEBUG) return '<div class="adsense adsenseInline adsenseTest">ad</div>';
        if (!isMobile()) return Adsense::ampDesktop();
        return '<div class="adsense">
                    <amp-ad width="100vw" height=320
                          type="adsense"
                          data-ad-client="ca-pub-7429223453905389"
                          data-ad-slot="3066154144"
                          data-auto-format="rspv"
                          data-full-width>
                            <div overflow></div>
                        </amp-ad>
                </div>';
    }

    static public function ampDesktop() {
        if (ASTERION_DEBUG) return '<div class="adsense adsenseInline adsenseTest">ad</div>';
        return '<div class="adsense">
                    <amp-ad
                        layout="fixed-height"
                        height=100
                        type="adsense"
                        data-ad-client="ca-pub-7429223453905389"
                        data-ad-slot="3066154144">
                    </amp-ad>
                </div>';
    }

    static public function ampInline() {
        if (ASTERION_DEBUG) return '<div class="adsense adsenseInline adsenseTest">ad</div>';
        if (isMobile()) return Adsense::amp();
        return '<div class="adsense ">
                    <amp-ad
                        layout="fixed-height"
                        height=250
                        type="adsense"
                        data-ad-client="ca-pub-7429223453905389"
                        data-ad-slot="3066154144">
                    </amp-ad>
                </div>';
    }

    static public function responsive() {
        if (ASTERION_DEBUG) return '<div class="adsense adsenseInline adsenseTest">ad</div>';
        return '
            <ins class="adsbygoogle"
                style="display:block"
                data-ad-client="ca-pub-7429223453905389"
                data-ad-slot="2090660201"
                data-ad-format="auto"
                data-full-width-responsive="true"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>';
    }

}
?>