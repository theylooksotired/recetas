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
                                ' . ((isset($this->object->mode) && $this->object->mode == 'amp') ? Adsense::amp() : Adsense::responsive())  . '
                                ' . $this->breadCrumbs() . '
                                <div class="content_ins">
                                    <div class="content_left">
                                        ' . $title_page . '
                                        ' . $content . '
                                    </div>
                                    <div class="content_right">
                                        ' . ((isset($this->object->mode) && $this->object->mode == 'amp') ? Adsense::amp() : Adsense::responsive())  . '
                                        ' . $this->menuSide() . '
                                    </div>
                                </div>
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
                            ' . (($login->isConnected()) ? '
                            <div class="menuUser">
                                <a href="' . url('cuenta') . '" class="menuUserAccount">
                                    <i class="fa fa-user"></i>
                                    <span>' . __('my_account') . '</span>
                                </a>
                                <a href="' . $user->urlLogout . '" class="menuUserLogout">
                                    <i class="fa fa-power-off"></i>
                                    <span>' . __('logout') . '</span>
                                </a>
                            </div>' : '
                            <div class="menuUser">
                                <a href="' . $user->urlRegister . '" class="menuUserRegister">
                                    <i class="fa fa-user"></i>
                                    <span>' . __('register') . '</span>
                                </a>
                                <a href="' . $user->urlLogin . '" class="menuUserLogin">
                                    <i class="fa fa-user"></i>
                                    <span>' . __('login') . '</span>
                                </a>
                            </div>') . '
                            <div class="searchTop">' . Navigation_Ui::search() . '</div>
                        </div>
                    </div>
                </div>
                <div class="headerMenu">' . $this->menu() . '</div>
            </header>';
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
                <div class="footerIns">
                    <div class="footerDown">
                        <div class="footerDownIns">
                            <p><strong>© '.date('Y').' '.Parameter::code('meta_title_page').'</strong></p>
                            <p>Diviértete cocinando con nuestros cientos de recetas con preparación paso a paso. No dudes en compartir tus preparaciones y críticas.</p>
                            <p>Escríbenos a <a href="mailto:recetas@plasticwebs.com">recetas@plasticwebs.com</a></p>
                        </div>
                    </div>
                </div>
            </footer>';
    }

    public function menuSide()
    {
        return Post_Ui::menuSide();
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
                        '.$this->menu_connectedSimple().'
                        '.Cache::show('Category', 'menuTop').'
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
        if (isset($this->object->breadCrumbs) && is_array($this->object->breadCrumbs)) {
            $html .= '
                <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                    <a href="' . url('') . '" itemprop="url">
                        <span itemprop="title">Inicio</span>
                    </a>
                </span> &raquo;';
            foreach ($this->object->breadCrumbs as $url => $title_page) {
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
