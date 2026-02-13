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
                                ' . Adsense::responsive('top') . '
                                <div class="content_ins">' . $content . '</div>
                                ' . $content_bottom . '
                                ' . $this->ad() . '
                            </div>
                        </div>
                        ' . $this->footer() . '
                    </div>';
                break;
            case 'simple':
            case 'recipe_category':
                return '
                    <div class="content_format content_format_' . $layout_page . '">
                        ' . $this->header() . '
                        <div class="content">
                            <div class="content_all">
                                ' . $this->breadCrumbs() . '
                                ' . (!(isset($this->object->no_ads)) ? Adsense::responsive('top') : '') . '
                                ' . $message_error . '
                                ' . $message_alert . '
                                ' . $message . '
                                ' . $content . '
                                ' . $content_bottom . '
                                ' . $this->ad() . '
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
                                ' . Adsense::responsive('top') . '
                                <div class="content_ins">
                                    ' . $content . '
                                    ' . $content_bottom . '
                                    ' . $this->ad() . '
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
                            ' . Navigation_Ui::search() . '
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
        // <a href="https://www.recetario-de-cocina.com/publicar-articulos">Publicar en nuestro sitio web</a>
        return '
            <footer class="footer">
                <div class="footer_ins">
                    <div class="footer_links_wrapper">
                        <div class="footer_links">
                            <div class="footer_links_title">Otros sitios de cocina por países</div>
                            <a href="https://www.recetas-argentinas.com" rel="nofollow" title="Recetas de Argentina">Argentina</a>
                            <a href="https://www.cocina-boliviana.com" rel="nofollow" title="Recetas de Bolivia">Bolivia</a>
                            <a href="https://www.cocina-brasilena.com" rel="nofollow" title="Recetas de Brasil">Brasil</a>
                            <a href="https://www.receitas-brasil.com" rel="nofollow" title="Receitas do Brasil">Brasil [PT]</a>
                            <a href="https://www.cocina-chilena.com" rel="nofollow" title="Recetas de Chile">Chile</a>
                            <a href="https://www.recetas-chinas.com" rel="nofollow" title="Recetas de China">China</a>
                            <a href="https://www.cocina-colombiana.com" rel="nofollow" title="Recetas de Colombia">Colombia</a>
                            <a href="https://www.recetascostarica.com" rel="nofollow" title="Recetas de Costa Rica">Costa Rica</a>
                            <a href="https://www.cocina-cubana.com" rel="nofollow" title="Recetas de Cuba">Cuba</a>
                            <a href="https://www.cocina-ecuatoriana.com" rel="nofollow" title="Recetas de Ecuador">Ecuador</a>
                            <a href="https://www.recetas-espana.com" rel="nofollow" title="Recetas de España">España</a>
                            <a href="https://www.recetassalvador.com" rel="nofollow" title="Recetas del Salvador">El Salvador</a>
                            <a href="https://www.recetas-francesas.com" rel="nofollow" title="Recetas de Francia">Francia</a>
                            <a href="https://www.recetas-guatemala.com" rel="nofollow" title="Recetas de Guatemala">Guatemala</a>
                            <a href="https://www.recetas-haitianas.com" rel="nofollow" title="Recetas de Haití">Haití</a>
                            <a href="https://www.recetashonduras.com" rel="nofollow" title="Recetas de Honduras">Honduras</a>
                            <a href="https://www.recetas-indias.com" rel="nofollow" title="Recetas de India">India</a>
                            <a href="https://www.recetas-italia.com" rel="nofollow" title="Recetas de Italia">Italia</a>
                            <a href="https://www.recetas-japonesas.com" rel="nofollow" title="Recetas de Japón">Japón</a>
                            <a href="https://www.la-cocina-mexicana.com" rel="nofollow" title="Recetas de México">México</a>
                            <a href="https://www.recetas-nicaragua.com" rel="nofollow" title="Recetas de Nicaragua">Nicaragua</a>
                            <a href="https://www.recetaspanama.com/" rel="nofollow" title="Recetas de Panamá">Panamá</a>
                            <a href="https://www.recetasparaguay.com/" rel="nofollow" title="Recetas de Paraguay">Paraguay</a>
                            <a href="https://www.comida-peruana.com" rel="nofollow" title="Recetas de Peru">Peru</a>
                            <a href="https://www.recetas-puertorico.com" rel="nofollow" title="Recetas de Puerto Rico">Puerto Rico</a>
                            <a href="https://www.recetas-dominicanas.com" rel="nofollow" title="Recetas de República Dominicana">República Dominicana</a>
                            <a href="https://www.cocina-uruguaya.com" rel="nofollow" title="Recetas de Uruguay">Uruguay</a>
                            <a href="https://www.recetas-venezolanas.com" rel="nofollow" title="Recetas de Venezuela">Venezuela</a>
                        </div>
                        <div class="footer_links">
                            <div class="footer_links_title">Otros sitios de cocina por tipos</div>
                            <a href="https://www.recetas-arabes.com" rel="nofollow" title="Recetas árabes">Árabes</a>
                            <a href="https://www.recetasdiabetes.com" rel="nofollow" title="Recetas para diabéticos">Diabetes</a>
                            <a href="https://www.recetas-judias.com" rel="nofollow" title="Recetas judías">Judías</a>
                            <a href="https://www.receta-vegetariana.com" rel="nofollow" title="Recetas vegetariana">Vegetariana</a>
                            <a href="https://www.recetas-veganas.com" rel="nofollow" title="Recetas veganas">Veganas</a>
                        </div>
                    </div>
                    <div class="footer_down">
                        <div class="footer_down_ins">
                            <p>
                                <a href="' . url('politicas-privacidad') . '">Políticas de Privacidad</a> |
                                <a href="' . url('terminos-condiciones') . '">Términos y condiciones</a>
                            </p>
                            <p><strong>© ' . date('Y') . ' ' . Parameter::code('meta_title_page') . '</strong></p>
                            <p>' . Parameter::code('meta_description') . ' Diviértete cocinando y no dudes en compartir tus preparaciones y críticas.</p>
                            <p>Escríbenos a <a href="mailto:info@plasticwebs.com">info@plasticwebs.com</a></p>
                        </div>
                    </div>
                </div>
            </footer>';
    }

    public function menu()
    {
        $menu = Cache::show('Category', 'menuTop');
        $menu .= Cache::show('SubCategory', 'menuTop');
        return '
            <div class="menu_wrapper">
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
                        <a href="' . url('top-10') . '">' . __('top10') . '</a>
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
        $searchAction = (ASTERION_LANGUAGE_ID == 'pt') ? 'pesquisar' : 'buscar';
        return '
            <div class="search_top">
                <div class="search_top_trigger"><i class="icon icon-search"></i></div>
                <form accept-charset="UTF-8" class="form_search_simple" action="' . url($searchAction) . '" method="GET" target="_top">
                    <fieldset>
                        <div class="text form_field ">
                            <input type="text" size="50" name="search" placeholder="' . __('search') . '">
                        </div>
                        <button type="submit" class="form_submit" role="button" aria-label="' . __('search') . '"><i class="icon icon-search"></i></button>
                    </fieldset>
                </form>
            </div>';
    }

    public function ad()
    {
        return '
            <div class="ad_app">
                <div class="ad_app_left">
                    <p><a href="https://whatsapp.com/channel/0029Vb2gbns7YSd5Vf9Jaj0r" target="_blank" title="WhatsApp" rel="nofollow">' . __('whatsapp_ad_title') . '</a></p>
                    <p>' . __('whatsapp_ad_message') . '</p>
                </div>
                <div class="ad_app_right">
                    <img src="' . ASTERION_BASE_URL . 'visual/img/button_whatsapp.svg" loading="lazy" alt="WhatsApp"/>
                </div>
            </div>
            <div class="ad_app ad_app_newsletter trigger_newsletter">
                <div class="ad_app_left">
                    <p><strong>' . __('newsletter_ad_title') . '</strong></p>
                    <p>' . __('newsletter_ad_message') . '</p>
                </div>
                <div class="ad_app_right">
                    <img src="' . ASTERION_BASE_URL . 'visual/img/button_newsletter.svg" loading="lazy" alt="Newsletter"/>
                </div>
            </div>
            ' . $this->modalNewsletter();
    }

    public function modalNewsletter()
    {
        $idRecipe = (isset($this->object->recipe)) ? $this->object->recipe->id() : null;
        return '
            <div class="modal modal_newsletter">
                <div class="modal_background"></div>
                <div class="modal_inside">
                    <div class="modal_close">
                        <i class="icon icon-close"></i>
                    </div>
                    <p><img src="' . ASTERION_BASE_URL . 'visual/img/button_newsletter.svg" loading="lazy" alt="Newsletter"/></p>
                    <p>' . __('newsletter_form') . '</p>
                    <form accept-charset="UTF-8" class="form_newsletter" action="' . url('newsletter') . '" method="POST">
                        <fieldset>
                            <div class="text form_field ">
                                <label for="name">' . __('name') . '</label>
                                <input type="text" size="50" name="name">
                            </div>
                            <div class="text form_field required">
                                <label for="email">' . __('email') . '</label>
                                <input type="email" size="50" name="email" required="required">
                            </div>
                            ' . ($idRecipe ? '<input type="hidden" name="id_recipe" value="' . $idRecipe . '">' : '') . '
                            <button type="submit" class="button form_submit" role="button" aria-label="' . __('subscribe') . '">' . __('subscribe') . '</button>
                        </fieldset>
                    </form>
                </div>
            </div>';
    }

}
