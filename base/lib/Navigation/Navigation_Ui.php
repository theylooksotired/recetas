<?php
class Navigation_Ui extends Ui
{

    public function render()
    {
        $layout_page = (isset($this->object->layout_page)) ? $this->object->layout_page : '';
        $title_page = (isset($this->object->title_page)) ? '<h1>' . $this->object->title_page . '</h1>' : '';
        $title_page = (isset($this->object->hide_title_page)) ? '' : $title_page;
        $title_page = (isset($this->object->title_page_content)) ? '<h1>' . $this->object->title_page_content . '</h1>' : $title_page;
        $message = (isset($this->object->message) && $this->object->message != '') ? $this->object->message : Session::getFlashInfo();
        $message_alert = (isset($this->object->message_alert) && $this->object->message_alert != '') ? $this->object->message_alert : Session::getFlashAlert();
        $message_error = (isset($this->object->message_error) && $this->object->message_error != '') ? $this->object->message_error : Session::getFlashError();
        $message = ($message != '') ? '<div class="message">' . $message . '</div>' : '';
        $message_alert = ($message_alert != '') ? '<div class="message message_alert">' . $message_alert . '</div>' : '';
        $message_error = ($message_error != '') ? '<div class="message message_error">' . $message_error . '</div>' : '';
        $content = (isset($this->object->content)) ? $this->object->content : '';
        $content_top = (isset($this->object->content_top)) ? '<div class="content_top">' . $this->object->content_top . '</div>' : '';
        $content_bottom = (isset($this->object->content_bottom)) ? '<div class="content_bottom">' . $this->object->content_bottom . '</div>' : '';
        $contentExtra = (isset($this->object->contentExtra)) ? $this->object->contentExtra : '';
        $idInside = (isset($this->object->idInside)) ? $this->object->idInside : '';
        switch ($layout_page) {
            default:
                return '
                    <div class="content_format content_format_' . $layout_page . '">
                        ' . $this->header() . '
                        <div class="content">
                            <div class="content_all">
                                ' . $message_error . '
                                ' . $message_alert . '
                                ' . $message . '
                                ' . $content_top . '
                                ' . $this->breadCrumbs() . '
                                ' . $title_page . '
                                ' . Adsense::amp() . '
                                <div class="content_ins">
                                    <div class="content_left">
                                        ' . $content . '
                                    </div>
                                    <div class="content_right">
                                        ' . $this->menuSide() . '
                                    </div>
                                </div>
                                ' . $content_bottom . '
                            </div>
                        </div>
                        ' . $this->footer() . '
                    </div>';
                break;
            case 'simple':
                return '
                    <div class="content_format content_format_' . $layout_page . '">
                        ' . $this->header() . '
                        <div class="content">
                            <div class="content_all">
                                ' . $message_error . '
                                ' . $message_alert . '
                                ' . $message . '
                                ' . $content . '
                                ' . $content_bottom . '
                            </div>
                        </div>
                        ' . $this->footer() . '
                    </div>';
                break;
            case 'recipe':
                return '
                    <div class="content_format content_format_' . $layout_page . '">
                        ' . $this->header() . '
                        <div class="content">
                            <div class="content_all">
                                ' . $message_error . '
                                ' . $message_alert . '
                                ' . $message . '
                                ' . $content_top . '
                                ' . $this->breadCrumbs() . '
                                ' . $title_page . '
                                ' . Adsense::amp() . '
                                <div class="content_ins">
                                    ' . $content . '
                                    ' . $content_bottom . '
                                </div>
                            </div>
                        </div>
                        ' . $this->footer() . '
                    </div>';
                break;
        }
    }

    public function header()
    {
        return '
            <header class="header">
                <div class="header_top">
                    <div class="header_ins">
                        <div class="header_left">
                            <div class="logo">
                                <a href="' . url('') . '">
                                    <span class="logo_image"></span>
                                    <span class="logo_title">
                                        <span class="logo_title_top">' . Parameter::code('meta_title_header_top') . '</span>
                                        <span class="logo_title_bottom">' . Parameter::code('meta_title_header_bottom') . '</span>
                                    </span>
                                </a>
                            </div>
                        </div>
                        <div class="header_right">
                            <div class="header_rightIns">
                                <div class="searchTop">' . Navigation_Ui::search() . '</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="header_menu">' . $this->menu() . '</div>
            </header>';
    }

    public function header_simple()
    {
        return '
            <header class="header header_simple">
                <div class="header_ins">
                    <div class="logo">
                        <a href="' . url('') . '">' . Parameter::code('meta_title_page') . '</a>
                    </div>
                </div>
            </header>';
    }

    public function footer()
    {
        return '
            <footer class="footer">
                <div class="footer_ins">
                    <div class="footer_links_wrapper">
                        <div class="footer_links">
                            <div class="footer_links_title">Otros sitios de cocina por países</div>
                            <a href="https://www.recetas-argentinas.com" title="Recetas de Argentina">Argentina</a>
                            <a href="https://www.cocina-boliviana.com" title="Recetas de Bolivia">Bolivia</a>
                            <a href="https://www.cocina-brasilena.com" title="Recetas de Brasil">Brasil</a>
                            <a href="https://www.receitas-brasil.com" title="Receitas do Brasil">Brasil [PT]</a>
                            <a href="https://www.cocina-chilena.com" title="Recetas de Chile">Chile</a>
                            <a href="https://www.recetas-chilenas.com" title="Recetas chilenas">Chilenas</a>
                            <a href="https://www.recetas-chinas.com" title="Recetas de China">China</a>
                            <a href="https://www.cocina-colombiana.com" title="Recetas de Colombia">Colombia</a>
                            <a href="https://www.recetascostarica.com" title="Recetas de Costa Rica">Costa Rica</a>
                            <a href="https://www.cocina-cubana.com" title="Recetas de Cuba">Cuba</a>
                            <a href="https://www.cocina-ecuatoriana.com" title="Recetas de Ecuador">Ecuador</a>
                            <a href="https://www.recetas-ecuatorianas.com" title="Recetas ecuatorianas">Ecuatorianas</a>
                            <a href="https://www.recetas-espana.com" title="Recetas de España">España</a>
                            <a href="https://www.recetassalvador.com" title="Recetas del Salvador">El Salvador</a>
                            <a href="https://www.recetas-francesas.com" title="Recetas de Francia">Francia</a>
                            <a href="https://www.recetas-guatemala.com" title="Recetas de Guatemala">Guatemala</a>
                            <a href="https://www.recetas-haitianas.com" title="Recetas de Haití">Haití</a>
                            <a href="https://www.recetashonduras.com" title="Recetas de Honduras">Honduras</a>
                            <a href="https://www.recetas-indias.com" title="Recetas de India">India</a>
                            <a href="https://www.recetas-italia.com" title="Recetas de Italia">Italia</a>
                            <a href="https://www.recetas-japonesas.com" title="Recetas de Japón">Japón</a>
                            <a href="https://www.la-cocina-mexicana.com" title="Recetas de México">México</a>
                            <a href="https://www.recetas-nicaragua.com" title="Recetas de Nicaragua">Nicaragua</a>
                            <a href="https://www.recetaspanama.com/" title="Recetas de Panamá">Panamá</a>
                            <a href="https://www.recetasparaguay.com/" title="Recetas de Paraguay">Paraguay</a>
                            <a href="https://www.comida-peruana.com" title="Recetas de Peru">Peru</a>
                            <a href="https://www.recetas-puertorico.com" title="Recetas de Puerto Rico">Puerto Rico</a>
                            <a href="https://www.recetas-dominicanas.com" title="Recetas de República Dominicana">República Dominicana</a>
                            <a href="https://www.cocina-uruguaya.com" title="Recetas de Uruguay">Uruguay</a>
                            <a href="https://www.recetas-venezolanas.com" title="Recetas de Venezuela">Venezuela</a>
                        </div>
                        <div class="footer_links">
                            <div class="footer_links_title">Otros sitios de cocina por tipos</div>
                            <a href="https://www.recetas-arabes.com" title="Recetas árabes">Árabes</a>
                            <a href="https://www.recetasdiabetes.com" title="Recetas para diabéticos">Diabetes</a>
                            <a href="https://www.recetas-sin-gluten.com" title="Recetas sin gluten">Sin gluten</a>
                            <a href="https://www.recetas-judias.com" title="Recetas judías">Judías</a>
                            <a href="https://www.recetaspizzas.com" title="Recetas de Pizzas">Pizzas</a>
                            <a href="https://www.recetas-simples.com" title="Recetas simples">Simples</a>
                            <a href="https://www.receta-vegetariana.com" title="Recetas vegetariana">Vegetariana</a>
                            <a href="https://www.recetas-veganas.com" title="Recetas veganas">Veganas</a>
                        </div>
                    </div>
                    <div class="footer_down">
                        <div class="footer_down_ins">
                            <p><strong>© ' . date('Y') . ' ' . Parameter::code('meta_title_page') . '</strong></p>
                            <p>' . Parameter::code('meta_description') . ' Diviértete cocinando y no dudes en compartir tus preparaciones y críticas.</p>
                            <p>Escríbenos a <a href="mailto:recetas@plasticwebs.com">recetas@plasticwebs.com</a></p>
                        </div>
                    </div>
                </div>
            </footer>';
    }

    public function menuSide()
    {
        return '
            ' . Recipe_Ui::menuSide(['amp' => (isset($this->object->mode) && $this->object->mode == 'amp')]) . '
            ' . Post_Ui::menuSide(['amp' => (isset($this->object->mode) && $this->object->mode == 'amp')]) . '
            ';
    }

    public function menu()
    {
        $menu = Cache::show('Category', 'menuTop');
        return '
            <div class="menuWrapper">
                ' . ((isset($this->object->mode) && $this->object->mode == 'amp') ? '
                <div class="menu_trigger" role="button" tabindex="0" aria-label="' . __('menu') . '" on="tap:AMP.setState({menuVisible: !menuVisible})">
                    <i [class]="menuVisibleButton ? \'icon icon-close\' : \'icon icon-menu\'" class="icon icon-menu"></i>
                </div>
                <nav [class]="menuVisible ? \'menu_all menu_all_open\' : \'menu_all\'" class="menu_all">
                ' : '
                <div class="menu_trigger">
                    <i class="icon icon-menu"></i>
                </div>
                <nav class="menu_all">
                ') . '
                    <div class="menu_all_ins">
                        <a href="' . url('') . '">' . __('home') . '</a>
                        ' . $menu . '
                        <a class="menu_posts" href="' . url('articulos') . '">' . __('posts') . '</a>
                    </div>
                </nav>
            </div>';
    }

    public function breadCrumbs()
    {
        $html = '';
        if (isset($this->object->bread_crumbs) && is_array($this->object->bread_crumbs)) {
            $html .= '
                <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a href="' . url('') . '" itemprop="item"><span itemprop="name">' . __('home') . '</span></a>
                    <meta itemprop="position" content="1" />
                </span> &raquo;';
            $i = 2;
            foreach ($this->object->bread_crumbs as $url => $title) {
                $html .= '
                    <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="' . $url . '" itemprop="item"><span itemprop="name">' . $title . '</span></a>
                        <meta itemprop="position" content="' . $i . '" />
                    </span> &raquo;';
                $i++;
            }
            return '<div class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">' . substr($html, 0, -8) . '</div>';
        }
    }

    public static function search()
    {
        return '
            <form accept-charset="UTF-8" class="formSearchSimple" action="' . url('buscar') . '" method="GET" target="_top">
                <fieldset>
                    <div class="text formField ">
                        <input type="text" size="50" name="search" placeholder="' . __('search') . '">
                    </div>
                    <button type="submit" class="formSubmit" role="button" aria-label="' . __('search') . '"><i class="icon icon-search"></i></button>
                </fieldset>
            </form>';
    }

    public static function analyticsAmp()
    {
        return '
            ' . ((Parameter::code('gtag') != '') ? '
            <amp-analytics type="gtag" data-credentials="include">
                <script type="application/json"> { "vars": { "gtag_id": "' . Parameter::code('gtag') . '", "config": { "AW-1035000466": { "groups": "default" } } }, "triggers": {
                        "C_CQ6RLtKBSQQ": {
                          "on": "visible",
                          "vars": {
                            "event_name": "conversion",
                            "value": 0.01,
                            "currency": "USD",
                            "send_to": ["AW-1035000466/KPllCIyJ250YEJK1w-0D"]
                          }
                        }
                    } }
                </script>
            </amp-analytics>
            ' : '') . '
            <amp-analytics type="googleanalytics" config="' . ASTERION_BASE_URL . 'libjs/ga4.json" data-credentials="include">
                <script type="application/json">
                {
                    "vars": {
                                "GA4_MEASUREMENT_ID": "' . Parameter::code('google_analytics_code_g4') . '",
                                "GA4_ENDPOINT_HOSTNAME": "www.google-analytics.com",
                                "DEFAULT_PAGEVIEW_ENABLED": true,
                                "GOOGLE_CONSENT_ENABLED": false,
                                "WEBVITALS_TRACKING": false,
                                "PERFORMANCE_TIMING_TRACKING": false,
                                "SEND_DOUBLECLICK_BEACON": false
                    }
                }
                </script>
            </amp-analytics>';
    }

    public static function autoadsAmp()
    {
        return '<amp-auto-ads type="adsense" data-ad-client="ca-pub-7429223453905389"></amp-auto-ads>';
    }

    public static function facebookComments($url)
    {
        $mode = (Parameter::code('mode') != '') ? Parameter::code('mode') : 'amp';
        if ($mode == 'amp' && Parameter::code('facebook_comments') == 'true') {
            return '<amp-facebook-comments layout="responsive" height="300" width="600" data-href="' . $url . '"></amp-facebook-comments>';
        }
    }

}
