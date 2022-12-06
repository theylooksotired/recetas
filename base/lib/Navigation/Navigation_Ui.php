<?php
class Navigation_Ui extends Ui
{

    public function render()
    {
        $layout_page = (isset($this->object->layout_page)) ? $this->object->layout_page : '';
        $title_page = (isset($this->object->title_page)) ? '<h1>' . $this->object->title_page . '</h1>' : '';
        $title_page = (isset($this->object->hide_title_page)) ? '' : $title_page;
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
                                ' . ((isset($this->object->mode) && $this->object->mode == 'amp') ? Adsense::amp() : Adsense::responsive()) . '
                                <div class="content_ins">
                                    <div class="content_left">
                                        ' . $content . '
                                    </div>
                                    <div class="content_right">
                                        ' . ((isset($this->object->mode) && $this->object->mode == 'amp') ? Adsense::amp() : Adsense::responsive()) . '
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
            case 'connected':
                return '
                    <div class="content_format content_format_' . $layout_page . '">
                        ' . $this->header() . '
                        <div class="content">
                            <div class="contentConnected">
                                <div class="content_ins">
                                    <div class="content_left">
                                        ' . $this->menu_connected() . '
                                    </div>
                                    <div class="content_right">
                                        ' . $title_page . '
                                        ' . $message_error . '
                                        ' . $message_alert . '
                                        ' . $message . '
                                        ' . $content . '
                                        ' . $content_bottom . '
                                    </div>
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
        $login = User_Login::getInstance();
        $user = new User();
        return '
            <header class="header">
                <div class="headerIns">
                    <div class="headerLeft">
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
                    <div class="headerRight">
                        <div class="headerRightIns">
                            <div class="searchTop">' . Navigation_Ui::search() . '</div>
                        </div>
                    </div>
                </div>
                <div class="headerMenu">' . $this->menu() . '</div>
            </header>';
        // ' . (($login->isConnected()) ? '
        // <div class="menuUser">
        //     <a href="' . url('cuenta') . '" class="menuUserAccount">
        //         <i class="fa fa-user"></i>
        //         <span>' . __('my_account') . '</span>
        //     </a>
        //     <a href="' . $user->urlLogout . '" class="menuUserLogout">
        //         <i class="fa fa-power-off"></i>
        //         <span>' . __('logout') . '</span>
        //     </a>
        // </div>' : '
        // <div class="menuUser">
        //     <a href="' . $user->urlRegister . '" class="menuUserRegister">
        //         <i class="fa fa-user"></i>
        //         <span>' . __('register') . '</span>
        //     </a>
        //     <a href="' . $user->urlLogin . '" class="menuUserLogin">
        //         <i class="fa fa-user"></i>
        //         <span>' . __('login') . '</span>
        //     </a>
        // </div>') . '
    }

    public function headerSimple()
    {
        return '
            <header class="header headerSimple">
                <div class="headerIns">
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
                            <h3>Otros sitios de cocina por países</h3>
                            <a href="https://www.recetas-argentinas.com" target="_blank" title="Recetas de Argentina">Argentina</a>
                            <a href="https://www.cocina-boliviana.com" target="_blank" title="Recetas de Bolivia">Bolivia</a>
                            <a href="https://www.cocina-brasilena.com" target="_blank" title="Recetas de Brasil">Brasil</a>
                            <a href="https://www.receitas-brasil.com" target="_blank" title="Receitas do Brasil">Brasil [PT]</a>
                            <a href="https://www.cocina-chilena.com" target="_blank" title="Recetas de Chile">Chile</a>
                            <a href="https://www.recetas-chinas.com" target="_blank" title="Recetas de China">China</a>
                            <a href="https://www.cocina-colombiana.com" target="_blank" title="Recetas de Colombia">Colombia</a>
                            <a href="https://www.recetascostarica.com" target="_blank" title="Recetas de Costa Rica">Costa Rica</a>
                            <a href="https://www.cocina-cubana.com" target="_blank" title="Recetas de Cuba">Cuba</a>
                            <a href="https://www.cocina-ecuatoriana.com" target="_blank" title="Recetas de Ecuador">Ecuador</a>
                            <a href="https://www.recetas-espana.com" target="_blank" title="Recetas de España">España</a>
                            <a href="https://www.recetassalvador.com" target="_blank" title="Recetas del Salvador">El Salvador</a>
                            <a href="https://www.recetas-guatemala.com" target="_blank" title="Recetas de Guatemala">Guatemala</a>
                            <a href="https://www.recetashonduras.com" target="_blank" title="Recetas de Honduras">Honduras</a>
                            <a href="https://www.recetas-italia.com" target="_blank" title="Recetas de Italia">Italia</a>
                            <a href="https://www.recetas-japonesas.com" target="_blank" title="Recetas de Japón">Japón</a>
                            <a href="https://www.la-cocina-mexicana.com" target="_blank" title="Recetas de México">México</a>
                            <a href="https://www.recetas-nicaragua.com" target="_blank" title="Recetas de Nicaragua">Nicaragua</a>
                            <a href="https://www.recetaspanama.com/" target="_blank" title="Recetas de Panamá">Panamá</a>
                            <a href="https://www.recetasparaguay.com/" target="_blank" title="Recetas de Paraguay">Paraguay</a>
                            <a href="https://www.comida-peruana.com" target="_blank" title="Recetas de Peru">Peru</a>
                            <a href="https://www.recetas-puertorico.com" target="_blank" title="Recetas de Puerto Rico">Puerto Rico</a>
                            <a href="https://www.cocina-uruguaya.com" target="_blank" title="Recetas de Uruguay">Uruguay</a>
                            <a href="https://www.recetas-venezolanas.com" target="_blank" title="Recetas de Venezuela">Venezuela</a>
                        </div>
                        <div class="footer_links">
                            <h3>Otros sitios de cocina por tipos</h3>
                            <a href="https://www.recetas-arabes.com" target="_blank" title="Recetas árabes">Árabes</a>
                            <a href="https://www.recetasdiabetes.com" target="_blank" title="Recetas para diabéticos">Diabetes</a>
                            <a href="https://www.recetas-judias.com" target="_blank" title="Recetas judías">Judías</a>
                            <a href="https://www.recetaspizzas.com" target="_blank" title="Recetas de Pizzas">Pizzas</a>
                            <a href="https://www.recetas-simples.com" target="_blank" title="Recetas simples">Simples</a>
                            <a href="https://www.receta-vegetariana.com" target="_blank" title="Recetas vegetariana">Vegetariana</a>
                            <a href="https://www.recetas-veganas.com" target="_blank" title="Recetas veganas">Veganas</a>
                        </div>
                    </div>
                    <div class="footer_down">
                        <div class="footer_down_ins">
                            <p><strong>© ' . date('Y') . ' ' . Parameter::code('meta_title_page') . '</strong></p>
                            <p>Diviértete cocinando con nuestros cientos de recetas con preparación paso a paso. No dudes en compartir tus preparaciones y críticas.</p>
                            <p>Escríbenos a <a href="mailto:recetas@plasticwebs.com">recetas@plasticwebs.com</a></p>
                        </div>
                    </div>
                </div>
            </footer>';
    }

    public function menuSide()
    {
        $showPosts = true;
        $showRecipes = true;
        $showPosts = ($this->object->action == 'intro') ? false : $showPosts;
        $showPosts = ($this->object->action == 'articulos') ? false : $showPosts;
        $showRecipes = ($this->object->action == 'recetas') ? false : $showRecipes;
        $showRecipes = (isset($this->object->hide_side_recipes)) ? false : $showRecipes;
        return '
            ' . (($showPosts) ? Post_Ui::menuSide(['amp' => (isset($this->object->mode) && $this->object->mode == 'amp')]) : '') . '
            ' . (($showRecipes) ? Recipe_Ui::menuSide(['amp' => (isset($this->object->mode) && $this->object->mode == 'amp')]) : '');
    }

    public function menu()
    {
        return '
            <div class="menuWrapper">
                ' . ((isset($this->object->mode) && $this->object->mode == 'amp') ? '
                <div class="menu_trigger" role="button" tabindex="1" on="tap:AMP.setState({menuVisible: !menuVisible})">
                    <i [class]="menuVisibleButton ? \'fa fa-times\' : \'fa fa-bars\'" class="fa fa-bars"></i>
                </div>
                <nav [class]="menuVisible ? \'menu_all menu_all_open\' : \'menu_all\'" class="menu_all">
                ' : '
                <div class="menu_trigger">
                    <i class="fa fa-bars"></i>
                </div>
                <nav class="menu_all">
                ') . '
                    <div class="menu_all_ins">
                        ' . $this->menu_connectedSimple() . '
                        ' . Cache::show('Category', 'menuTop') . '
                        <a class="menu_posts" href="' . url('articulos') . '">' . __('posts') . '</a>
                    </div>
                </nav>
            </div>';
    }

    public function menu_connected()
    {
        return '
            <div class="menu_connected">
                <a href="' . url('cuenta/recetas') . '" class="menu_connected_item">
                    <i class="fa fa-cutlery"></i>
                    <span>' . __('recipes') . '</span>
                </a>
                <a href="' . url('cuenta/articulos') . '" class="menu_connected_item">
                    <i class="fa fa-folder"></i>
                    <span>' . __('posts') . '</span>
                </a>
                <hr/>
                <a href="' . url('cuenta/perfil') . '" class="menu_connected_item">
                    <i class="fa fa-user"></i>
                    <span>' . __('profile') . '</span>
                </a>
                <a href="' . url('cuenta/cambiar-email') . '" class="menu_connected_item">
                    <i class="fa fa-envelope"></i>
                    <span>' . __('update_email') . '</span>
                </a>
                <a href="' . url('cuenta/contrasena') . '" class="menu_connected_item">
                    <i class="fa fa-lock"></i>
                    <span>' . __('update_password') . '</span>
                </a>
                <hr/>
                <a href="' . url('salir') . '" class="menu_connected_item menu_connected_logout">
                    <i class="fa fa-power-off"></i>
                    <span>' . __('logout') . '</span>
                </a>
            </div>';
    }

    public function menu_connectedSimple()
    {
        $login = User_Login::getInstance();
        if ($login->isConnected()) {
            return '
                <a class="menu_account" href="' . url('cuenta/perfil') . '">' . __('profile') . '</a>
                <a class="menu_account" href="' . url('cuenta/cambiar-email') . '">' . __('update_email') . '</a>
                <a class="menu_account" href="' . url('cuenta/contrasena') . '">' . __('update_password') . '</a>
                <a class="menu_account menu_account_logout" href="' . url('cuenta/salir') . '">' . __('logout') . '</a>';
        }
    }

    public function breadCrumbs()
    {
        $html = '';
        if (isset($this->object->bread_crumbs) && is_array($this->object->bread_crumbs)) {
            $html .= '
                <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                    <a href="' . url('') . '" itemprop="url">
                        <span itemprop="title">Inicio</span>
                    </a>
                </span> &raquo;';
            foreach ($this->object->bread_crumbs as $url => $title_page) {
                $html .= '
                    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                            <a href="' . $url . '" itemprop="url">
                                <span itemprop="title">' . $title_page . '</span>
                            </a>
                        </span> &raquo;';
            }
            $html = '<div class="breadcrumbs">' . substr($html, 0, -8) . '</div>';
        }
        return $html;
    }

    public static function search()
    {
        return '
            <form accept-charset="UTF-8" class="formSearchSimple" action="' . url('buscar') . '" method="GET" target="_top">
                <fieldset>
                    <div class="text formField ">
                        <input type="text" size="50" name="search" placeholder="' . __('search') . '">
                    </div>
                    <button type="submit" class="formSubmit"><i class="fa fa-search"></i></button>
                </fieldset>
            </form>';
    }

    public static function analyticsAmp()
    {
        return '
            <amp-analytics type="googleanalytics">
                <script type="application/json">{"vars": {"account": "' . Parameter::code('google_analytics_id') . '"}, "triggers": { "trackPageview": { "on": "visible", "request": "pageview"}}}</script>
            </amp-analytics>';
    }

    public static function autoadsAmp()
    {
        return '<amp-auto-ads type="adsense" data-ad-client="ca-pub-7429223453905389"></amp-auto-ads>';
    }

    public static function facebookComments($url)
    {
        return '<amp-facebook-comments layout="responsive" height="300" width="600" data-href="' . $url . '"></amp-facebook-comments>';
    }

}
