<?php
/**
 * Bosa - Theme Info Admin Menu
 * @package Bosa Themes
 * @subpackage Admin
 */
if ( ! class_exists( 'Bosa_Theme_Info' ) ) {
    class Bosa_Theme_Info{

        private $config;
        private $theme_name;
        private $theme_slug;
        private $theme_version;
        private $page_title;
        private $menu_title;
        private $tabs;

        /**
         * Constructor.
         */
        public function __construct( $config ) {
            $this->config = $config;
            $this->prepare_class();

            /*admin menu*/
            add_action( 'admin_menu', array( $this, 'kt_admin_menu' ) );

            /* enqueue script and style for about page */
            add_action( 'admin_enqueue_scripts', array( $this, 'style_and_scripts' ) );

            /* keep the theme dashboard page free of admin notices */
            add_action( 'in_admin_header', array( $this, 'hide_admin_notices' ), 1000 );

            /* ajax callback for dismissable required actions */
            add_action( 'wp_ajax_kt_theme_info_update_recommended_action', array( $this, 'update_recommended_action_callback' ) );
        }

        /**
         * Prepare and setup class properties.
         */
        public function prepare_class() {
            $theme = wp_get_theme();
            $this->theme_name    = esc_attr( $theme->get( 'Name' ) );
            $this->theme_slug    = $theme->get_template();
            $this->theme_version = $theme->get( 'Version' );
            $this->page_title    = $this->theme_name . esc_html__( ' Info', 'bosa' );
            $this->menu_title    = $this->theme_name . esc_html__( ' Theme', 'bosa' );
            $this->tabs          = isset( $this->config['tabs'] ) ? $this->config['tabs'] : array();
        }

        /**
         * Return the valid array of recommended actions.
         * @return array The valid array of recommended actions.
         */
        /**
         * Dismiss required actions
         */
        public function update_recommended_action_callback() {

            /*getting for provided array*/
            $recommended_actions = isset( $this->config[ 'recommended_actions' ] ) ? $this->config[ 'recommended_actions' ] : array();

            /*from js action*/
            $action_id = esc_attr( ( isset( $_GET[ 'id' ] ) ) ? $_GET[ 'id' ] : 0 );
            $todo = esc_attr( ( isset( $_GET[ 'todo' ] ) ) ? $_GET[ 'todo'] : '' );

            /*getting saved actions*/
            $saved_actions = get_option( $this->theme_slug . '_recommended_actions' );

            echo esc_html( wp_unslash( $action_id ) ); /* this is needed and it's the id of the dismissable required action */

            if ( ! empty( $action_id ) ) {

                if( 'reset' == $todo ){
                    $saved_actions_new = array();
                    if ( ! empty( $recommended_actions ) ) {

                        foreach ( $recommended_actions as $recommended_action ) {
                            $saved_actions[ $recommended_action['id'] ] = true;
                        }
                        update_option( $this->theme_slug . '_recommended_actions', $saved_actions_new );
                    }
                }
                /* if the option exists, update the record for the specified id */
                elseif ( !empty( $saved_actions) and is_array( $saved_actions ) ) {

                    switch ( esc_html( $todo ) ) {
                        case 'add':
                            $saved_actions[ $action_id ] = true;
                            break;
                        case 'dismiss':
                            $saved_actions[ $action_id ] = false;
                            break;
                    }
                    update_option( $this->theme_slug . '_recommended_actions', $saved_actions );

                    /* create the new option,with false for the specified id */
                }
                else {
                    $saved_actions_new = array();
                    if ( ! empty( $recommended_actions ) ) {

                        foreach ( $recommended_actions as $recommended_action ) {
                            echo esc_html($recommended_action['id']);
                            echo " ". esc_html($todo);
                            if ( $recommended_action['id'] == $action_id ) {
                                switch ( esc_html( $todo ) ) {
                                    case 'add':
                                        $saved_actions_new[ $action_id ] = true;
                                        break;
                                    case 'dismiss':
                                        $saved_actions_new[ $action_id ] = false;
                                        break;
                                }
                            }
                        }
                    }
                    update_option( $this->theme_slug . '_recommended_actions', $saved_actions_new );
                }
            }
            exit;
        }

        private function get_recommended_actions() {
            $saved_actions = get_option( $this->theme_slug . '_recommended_actions' );
            if ( ! is_array( $saved_actions ) ) {
                $saved_actions = array();
            }
            $recommended_actions = isset( $this->config['recommended_actions'] ) ? $this->config['recommended_actions'] : array();
            $valid       = array();
            if( isset( $recommended_actions ) && is_array( $recommended_actions ) ){
                foreach ( $recommended_actions as $recommended_action ) {
                    if (
                        (
                            ! isset( $recommended_action['check'] ) ||
                            ( isset( $recommended_action['check'] ) && ( $recommended_action['check'] == false ) )
                        )
                        &&
                        ( ! isset( $saved_actions[ $recommended_action['id'] ] ) ||
                            ( isset( $saved_actions[ $recommended_action['id']] ) && ($saved_actions[ $recommended_action['id']] == true ) )
                        )
                    ) {
                        $valid[] = $recommended_action;
                    }
                }
            }
            return $valid;
        }

        private function count_recommended_actions() {
            $count = 0;
            $actions_count = $this->get_recommended_actions();
            if ( ! empty( $actions_count ) ) {
                $count = count( $actions_count );
            }
            return $count;
        }
        
        /**
         * Adding Theme Info Menu under Appearance.
         */
        function kt_admin_menu() {

            if ( ! empty( $this->page_title ) && ! empty( $this->menu_title ) ) {
                $count = $this->count_recommended_actions();
                $menu_title = $count > 0 ? $this->menu_title . '<span class="badge-action-count">' . esc_html( $count ) . '</span>' : $this->menu_title;
                /* Example
                 * add_theme_page('My Plugin Theme', 'My Plugin', 'edit_theme_options', 'my-unique-identifier', 'my_plugin_function');
                 * */
                add_theme_page( $this->page_title, $menu_title, 'edit_theme_options', $this->theme_slug . '-info', array(
                    $this,
                    'kt_theme_info_screen',
                ) );
            }
        }

        /**
         * Render the info content screen.
         */
        public function kt_theme_info_screen() {

            if ( ! empty( $this->config[ 'info_title' ] ) ) {
                $welcome_title = $this->config[ 'info_title' ];
            }
            if ( ! empty( $this->config[ 'info_content' ] ) ) {
                $welcome_content = $this->config[ 'info_content' ];
            }
            if ( ! empty( $this->config[ 'quick_links' ] ) ) {
                $quick_links = $this->config[ 'quick_links' ];
            }

            if (
                ! empty( $welcome_title ) ||
                ! empty( $welcome_content ) ||
                ! empty( $quick_links ) ||
                ! empty( $this->tabs )
            ) {
                echo '<div class="wrap about-wrap info-wrap epsilon-wrap">';

                if ( ! empty( $welcome_title ) ) {
                    echo '<h1>';
                    echo esc_html( $welcome_title );
                    if ( ! empty( $this->theme_version ) ) {
                        echo ' <span class="theme-version-badge">' . esc_html( $this->theme_version ) . '</span>';
                    }
                    echo '</h1>';
                }
                if ( ! empty( $welcome_content ) ) {
                    echo '<div class="about-text">' . wp_kses_post( $welcome_content ) . '</div>';
                }

                /*quick links*/
                if( !empty( $quick_links ) && is_array( $quick_links ) ){
                    echo '<p class="quick-links">';
                    foreach ( $quick_links as $quick_key => $quick_link ) {
                        $button = 'button-secondary';
                        if( 'pro_url' == $quick_key ){
                            $button = 'button-primary';
                        }
                        echo '<a href="'.esc_url( $quick_link['url'] ).'" class="button '.esc_attr( $button ).'" target="_blank">'.$quick_link['text'].'</a>';
                    }
                    echo "</p>";
                }
                /* Display tabs */
                if ( ! empty( $this->tabs ) ) {
                    $current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'getting_started';
                    if ( ! isset( $this->tabs[ $current_tab ] ) ) {
                        $current_tab = 'getting_started';
                    }

                    echo '<h2 class="nav-tab-wrapper wp-clearfix">';
                    $count = $this->count_recommended_actions();
                    foreach ( $this->tabs as $tab_key => $tab_name ) {

                        echo '<a href="' . esc_url( admin_url( 'themes.php?page=' . $this->theme_slug . '-info' ) ) . '&tab=' . esc_attr( $tab_key ) . '" class="nav-tab ' . ( $current_tab == $tab_key ? 'nav-tab-active' : '' ) . '" role="tab" data-tab="' . esc_attr( $tab_key ) . '">';
                        echo esc_html( $tab_name );
                        if ( $tab_key == 'recommended_actions' ) {
                            if ( $count > 0 ) {
                                echo '<span class="badge-action-count">' . esc_html( $count ) . '</span>';
                            }
                        }
                        echo '</a>';
                    }

                    echo '</h2>';

                    /* All tab panels are printed once; theme-info.js switches them without a page reload. */
                    foreach ( $this->tabs as $tab_key => $tab_name ) {
                        if ( ! method_exists( $this, $tab_key ) ) {
                            continue;
                        }
                        echo '<div class="changelog point-releases theme-info-panel" data-tab="' . esc_attr( $tab_key ) . '"' . ( $current_tab === $tab_key ? '' : ' hidden' ) . '>';
                        $this->$tab_key();
                        echo '</div>';
                    }

                    $this->pro_banner();
                }
                echo '</div><!--/.wrap.about-wrap-->';
            }
        }

        /**
         * Getting started tab
         */
        public function getting_started() {
            echo '<div class="feature-section col-wrap">';
            if ( ! empty( $this->config['gs_steps'] ) ) {
                $gs_steps = $this->config['gs_steps'];
                if ( ! empty( $gs_steps ) ) {

                    /*defaults values for gs_steps array */
                    $defaults = array(
                        'title'     => '',
                        'desc'       => '',
                        'button_label'   => '',
                        'button_link'   => '',
                        'is_button' => true,
                        'is_new_tab' => false,
                        'is_gs' => false,
                    );
                    foreach ( $gs_steps as $gs_step ) {
                        $instance = wp_parse_args( (array) $gs_step, $defaults );

                        /*allowed 7 value in array */
                        $title = $instance[ 'title' ];
                        $desc = $instance[ 'desc'];
                        $button_label = $instance[ 'button_label'];
                        $button_link = $instance[ 'button_link'];
                        $is_button = $instance[ 'is_button'];
                        $is_new_tab = $instance[ 'is_new_tab'];
                        $is_gs = $instance[ 'is_gs' ];
                        
                        echo '<div class="col"><div class="col-items">';
                        if ( ! empty( $gs_step['tag'] ) ) {
                            echo '<span class="theme-info-tag">' . esc_html( $gs_step['tag'] ) . '</span>';
                        }

                        if( $is_gs ) {
                            $inactive_plugins = bosa_get_inactive_required_plugins();
                            if ( empty( $inactive_plugins ) ) {
                                echo '<h3>' . esc_html__( 'You are all set! Import a starter site', 'bosa' ) . '</h3>';
                                echo '<p>' . esc_html__( 'All required plugins are active. Pick a starter site and import it in one click, or start building your own design.', 'bosa' ) . '</p>';
                                echo '<p><a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=advanced-import' ) ) . '">' . esc_html__( 'Import a Starter Site', 'bosa' ) . '</a></p>';
                                echo '</div></div>';
                            }else {
                                if ( ! empty( $title ) ) {
                                    echo '<h3>';
                                    echo esc_html($title);
                                    echo '</h3>';
                                }

                                if ( ! empty( $desc ) ) {
                                    echo '<p>' . $desc . '</p>';
                                }
                                echo '<p class="plugin-notice">' . sprintf( esc_html__( 'This will install and activate %s.', 'bosa' ), esc_html( wp_sprintf( '%l', wp_list_pluck( $inactive_plugins, 'name' ) ) ) ) . '</p>';

                                if ( ! empty( $button_label ) ) {

                                    echo '<p>';
                                    $button_class = '';
                                    if ( $is_button ) {
                                        $button_class = 'button button-primary bosa-install-plugins';
                                    }

                                    $button_new_tab = '_self';
                                    if ( isset( $is_new_tab ) ) {
                                        if ( $is_new_tab ) {
                                        $button_new_tab = '_blank';
                                        }
                                    }
                                    echo '<a target="' . $button_new_tab . '" class="' . $button_class . '">' . $button_label . '</a>';
                                    echo '</p>';
                                }
                                echo '</div></div>';
                            }
                        }else{
                            if ( ! empty( $title ) ) {
                                echo '<h3>';
                                echo esc_html($title);
                                echo '</h3>';
                            }

                            if ( ! empty( $desc ) ) {
                                echo '<p>' . $desc . '</p>';
                            }

                            if ( ! empty( $button_link ) && ! empty( $button_label ) ) {

                                echo '<p>';
                                $button_class = '';
                                if ( $is_button ) {
                                    $button_class = 'button button-primary button-hero';
                                }

                                $button_new_tab = '_self';
                                if ( isset( $is_new_tab ) ) {
                                    if ( $is_new_tab ) {
                                        $button_new_tab = '_blank';
                                    }
                                }
                                echo '<a target="' . $button_new_tab . '" href="' . $button_link . '" class="' . $button_class . '">' . $button_label . '</a>';
                                echo '</p>';
                            }
                            echo '</div></div>';
                        }
                    }
                }
            }
            echo '</div><!-- .feature-section col-wrap -->';
        }


        /**
         * Recommended Actions tab
         */
        public function check_plugin_status( $slug ) {

            $path = WPMU_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
            if ( ! file_exists( $path ) ) {
                $path = WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
                if ( ! file_exists( $path ) ) {
                    $path = false;
                }
            }

            if ( file_exists( $path ) ) {
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

                $needs = is_plugin_active( $slug . '/' . $slug . '.php' ) ? 'deactivate' : 'activate';

                return array( 'status' => is_plugin_active( $slug . '/' . $slug . '.php' ), 'needs' => $needs );
            }

            return array( 'status' => false, 'needs' => 'install' );
        }

        public function create_action_link( $state, $slug ) {

            switch ( $state ) {
                case 'install':
                    return wp_nonce_url(
                        add_query_arg(
                            array(
                                'action' => 'install-plugin',
                                'plugin' => $slug
                            ),
                            network_admin_url( 'update.php' )
                        ),
                        'install-plugin_' . $slug
                    );
                    break;

                case 'deactivate':
                    return add_query_arg(
                            array(
                                'action'        => 'deactivate',
                                'plugin'        => rawurlencode( $slug . '/' . $slug . '.php' ),
                                'plugin_status' => 'all',
                                'paged'         => '1',
                                '_wpnonce'      => wp_create_nonce( 'deactivate-plugin_' . $slug . '/' . $slug . '.php' )
                                ),
                            network_admin_url( 'plugins.php' )
                    );
                    break;

                case 'activate':
                    return add_query_arg(
                            array(
                                'action'        => 'activate',
                                'plugin'        => rawurlencode( $slug . '/' . $slug . '.php' ),
                                'plugin_status' => 'all',
                                'paged'         => '1',
                                '_wpnonce'      => wp_create_nonce( 'activate-plugin_' . $slug . '/' . $slug . '.php' )
                            ),
                            network_admin_url( 'plugins.php' )
                    );
                    break;
            }
        }

        public function recommended_actions() {

            $recommended_actions = isset( $this->config['recommended_actions'] ) ? $this->config['recommended_actions'] : array();
            $hooray = true;
            if ( ! empty( $recommended_actions ) ) {

                echo '<div class="feature-section action-recommended demo-import-boxed" id="plugin-filter">';

                if ( ! empty( $recommended_actions ) && is_array( $recommended_actions ) ) {

                    /*get saved recommend actions*/
                    $saved_recommended_actions = get_option( $this->theme_slug . '_recommended_actions' );

                    /*defaults values for getting_started array */
                    $defaults = array(
                        'title'         => '',
                        'desc'          => '',
                        'check'         => false,
                        'plugin_slug'   => '',
                        'id'            => ''
                    );
                    foreach ( $recommended_actions as $action_key => $action_value ) {
                        $instance = wp_parse_args( (array) $action_value, $defaults );

                        /*allowed 5 value in array */
                        $title = $instance[ 'title' ];
                        $desc = $instance[ 'desc' ];
                        $check = $instance[ 'check' ];
                        $plugin_slug = $instance[ 'plugin_slug' ];
                        $id = $instance[ 'id' ];

                        $hidden = false;

                        /*magic check for recommended actions*/
                        if (
                            isset( $saved_recommended_actions[ $id ] ) &&
                            $saved_recommended_actions[ $id ] == false ) {
                            $hidden = true;
                        }
                        if ( $hidden ) {
                            echo '';
                        }
                        $done = '';
                        if ( $check ) {
                           $done = 'done';
                        }

                        echo "<div class='kt-theme-info-action-recommended-box {$done}'>";

                        if ( ! $hidden ) {
                            echo '<span data-action="dismiss" class="dashicons dashicons-visibility kt-theme-info-recommended-action-button" id="' . esc_attr( $action_value['id'] ) . '"></span>';
                        } else {
                            echo '<span data-action="add" class="dashicons dashicons-hidden kt-theme-info-recommended-action-button" id="' . esc_attr( $action_value['id'] ) .'"></span>';
                        }

                        if ( ! empty( $title) ) {
                            echo '<h3>' . wp_kses_post( $title ) . '</h3>';
                        }

                        if ( ! empty( $desc ) ) {
                            echo '<p>' . wp_kses_post( $desc ) . '</p>';
                        }

                        if ( ! empty( $plugin_slug ) ) {

                            $active = $this->check_plugin_status( $action_value['plugin_slug'] );
                            $url    = $this->create_action_link( $active['needs'], $action_value['plugin_slug'] );
                            $label  = '';
                            $class  = '';
                            switch ( $active['needs'] ) {

                                case 'install':
                                    $class = 'install-now button';
                                    $label = esc_html__( 'Install', 'bosa' );
                                    break;

                                case 'activate':
                                    $class = 'activate-now button button-primary';
                                    $label = esc_html__( 'Activate', 'bosa' );

                                    break;

                                case 'deactivate':
                                    $class = 'deactivate-now button';
                                    $label = esc_html__( 'Deactivate', 'bosa' );

                                    break;
                            }

                            ?>
                            <p class="plugin-card-<?php echo esc_attr( $action_value['plugin_slug'] ) ?> action_button <?php echo ( $active['needs'] !== 'install' && $active['status'] ) ? 'active' : '' ?>">
                                <a data-slug="<?php echo esc_attr( $action_value['plugin_slug'] ) ?>"
                                   class="<?php echo esc_attr( $class ); ?>"
                                   href="<?php echo esc_url( $url ) ?>"> <?php echo esc_html( $label ) ?> </a>
                            </p>

                            <?php

                        }
                        echo '</div>';
                        $hooray = false;
                    }
                }
                if ( $hooray ){
                    echo '<span class="hooray">' . esc_html__( 'Hooray! There are no recommended actions for you right now.', 'bosa' ) . '</span>';
                    echo '<a data-action="reset" id="reset" class="reset-all" href="#">'.esc_html__('Show All Recommended Actions', 'bosa').'</a>';
                }
                echo '</div>';
            }
        }

        /**
         * Recommended plugins tab
         */
        /*
         * Call plugin api
         */
        public function call_plugin_api( $slug ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

            if ( false === ( $call_api = get_transient( 'kt_theme_info_plugin_information_transient_' . $slug ) ) ) {
                $call_api = plugins_api( 'plugin_information', array(
                    'slug'   => $slug,
                    'fields' => array(
                        'downloaded'        => false,
                        'rating'            => false,
                        'description'       => false,
                        'short_description' => true,
                        'donate_link'       => false,
                        'tags'              => false,
                        'sections'          => true,
                        'homepage'          => true,
                        'added'             => false,
                        'last_updated'      => false,
                        'compatibility'     => false,
                        'tested'            => false,
                        'requires'          => false,
                        'downloadlink'      => false,
                        'icons'             => true
                    )
                ) );
                set_transient( 'kt_theme_info_plugin_information_transient_' . $slug, $call_api, 30 * MINUTE_IN_SECONDS );
            }

            return $call_api;
        }
        public function get_plugin_icon( $arr ) {

            if ( ! empty( $arr['svg'] ) ) {
                $plugin_icon_url = $arr['svg'];
            } elseif ( ! empty( $arr['2x'] ) ) {
                $plugin_icon_url = $arr['2x'];
            } elseif ( ! empty( $arr['1x'] ) ) {
                $plugin_icon_url = $arr['1x'];
            } else {
                $plugin_icon_url = get_template_directory_uri() . '/assets/placeholder_plugin.png';
            }

            return $plugin_icon_url;
        }
        public function recommended_plugins() {
            $recommended_plugins = $this->config['recommended_plugins'];

            if ( ! empty( $recommended_plugins ) ) {
                if ( ! empty( $recommended_plugins ) && is_array( $recommended_plugins ) ) {

                    echo '<div class="feature-section recommended-plugins col-wrap demo-import-boxed" id="plugin-filter">';

                    foreach ( $recommended_plugins as $recommended_plugins_item ) {

                        if ( ! empty( $recommended_plugins_item['slug'] ) ) {
                            $info   = $this->call_plugin_api( $recommended_plugins_item['slug'] );
                            if ( ! empty( $info->icons ) ) {
                                $icon = $this->get_plugin_icon( $info->icons );
                            }

                            $active = $this->check_plugin_status( $recommended_plugins_item['slug'] );

                            if ( ! empty( $active['needs'] ) ) {
                                $url = $this->create_action_link( $active['needs'], $recommended_plugins_item['slug'] );
                            }

                            echo '<div class="col"><div class="col-items plugin_box">';
                            if ( ! empty( $icon ) ) {
                                echo '<img src="'.esc_url( $icon ).'" alt="plugin box image">';
                            }
                            if ( ! empty(  $info->version ) ) {
                                echo '<span class="version">'. ( ! empty( $this->config['recommended_plugins']['version_label'] ) ? esc_html( $this->config['recommended_plugins']['version_label'] ) : '' ) . esc_html( $info->version ).'</span>';
                            }
                            if ( ! empty( $info->author ) ) {
                                echo '<span class="separator"> | </span>' . wp_kses_post( $info->author );
                            }

                            if ( ! empty( $info->name ) && ! empty( $active ) ) {
                                echo '<div class="action_bar ' . ( ( $active['needs'] !== 'install' && $active['status'] ) ? 'active' : '' ) . '">';
                                echo '<span class="plugin_name">' . ( ( $active['needs'] !== 'install' && $active['status'] ) ? 'Active: ' : '' ) . esc_html( $info->name ) . '</span>';
                                echo '</div>';

                                $label = '';

                                switch ( $active['needs'] ) {

                                    case 'install':
                                        $class = 'install-now button';
                                        $label = esc_html__( 'Install', 'bosa' );
                                        break;

                                    case 'activate':
                                        $class = 'activate-now button button-primary';
                                        $label = esc_html__( 'Activate', 'bosa' );

                                        break;

                                    case 'deactivate':
                                        $class = 'deactivate-now button';
                                        $label = esc_html__( 'Deactivate', 'bosa' );

                                        break;
                                }

                                echo '<span class="plugin-card-' . esc_attr( $recommended_plugins_item['slug'] ) . ' action_button ' . ( ( $active['needs'] !== 'install' && $active['status'] ) ? 'active' : '' ) . '">';
                                echo '<a data-slug="' . esc_attr( $recommended_plugins_item['slug'] ) . '" class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
                                echo '</span>';
                            }
                            echo '</div></div><!-- .col.plugin_box -->';
                        }
                    }
                    echo '</div><!-- .recommended-plugins -->';
                }
            }
        }

        /**
         * Child themes
         */
        public function child_themes() {
            echo '<div id="child-themes" class="kt-theme-info-tab-pane">';
            $child_themes = isset( $this->config['child_themes'] ) ? $this->config['child_themes'] : array();
            if ( ! empty( $child_themes ) ) {

                /*defaults values for child theme array */
                $defaults = array(
                    'title'        => '',
                    'screenshot'   => '',
                    'download_link'=> '',
                    'preview_link' => ''
                );
                if ( ! empty( $child_themes ) && is_array( $child_themes ) ) {
                    echo '<div class="kt-about-row">';
                    $i = 0;
                    foreach ( $child_themes as $child_theme ){
                        $instance = wp_parse_args( (array) $child_theme, $defaults );

                        /*allowed 5 value in array */
                        $title = $instance[ 'title' ];
                        $screenshot = $instance[ 'screenshot'];
                        $download_link = $instance[ 'download_link'];
                        $preview_link = $instance[ 'preview_link'];

                        if( !empty( $screenshot) ){
                            echo '<div class="kt-about-child-theme">';
                            echo '<div class="kt-theme-info-child-theme-image">';

                            echo '<img src="' . esc_url( $screenshot ) . '" alt="' . ( ! empty( $title ) ? esc_attr( $title ) : '' ) . '" />';

                            if ( ! empty( $title ) ) {
                                echo '<div class="kt-theme-info-child-theme-details">';
                                echo '<div class="theme-details">';
                                echo '<span class="theme-name">' . esc_html( $title  ). '</span>';
                                if ( ! empty( $download_link ) ) {
                                    echo '<a href="' . esc_url( $download_link ) . '" class="button button-primary install right">' . esc_html__( 'Download', 'bosa' ) . '</a>';
                                }
                                if ( ! empty( $preview_link ) ) {
                                    echo '<a class="button button-secondary preview right" target="_blank" href="' . $preview_link . '">' . esc_html__( 'Live Preview', 'bosa' ). '</a>';
                                }
                                echo '</div>';
                                echo '</div>';
                            }

                            echo "</div>";
                            echo "</div>";
                            $i++;
                        }
                        if( 0 == $i % 3 ){
                            echo "</div><div class='kt-about-row'>";/*.kt-about-row end-start*/
                        }
                    }

                    echo '</div>';/*.kt-about-row end*/
                }// End if().
            }// End if().
            echo '</div>';
        }

        /**
         * Support tab
         */
        public function support() {
            echo '<div class="feature-section col-wrap">';

            if ( ! empty( $this->config['support_content'] ) ) {

                $supports = $this->config['support_content'];

                if ( ! empty( $supports ) ) {

                    /*defaults values for child theme array */
                    $defaults = array(
                        'title' => '',
                        'icon' => '',
                        'tag' => '',
                        'desc' => '',
                        'button_label' => '',
                        'button_link' => '',
                        'is_button' => true,
                        'is_new_tab' => true
                    );

                    foreach ( $supports as $support ) {
                        $instance = wp_parse_args( (array) $support, $defaults );

                        /*allowed 7 value in array */
                        $title = $instance[ 'title' ];
                        $icon = $instance[ 'icon'];
                        $desc = $instance[ 'desc'];
                        $button_label = $instance[ 'button_label'];
                        $button_link = $instance[ 'button_link'];
                        $is_button = $instance[ 'is_button'];
                        $is_new_tab = $instance[ 'is_new_tab'];
                        
                        echo '<div class="col"><div class="col-items">';

                        if ( ! empty( $instance['tag'] ) ) {
                            echo '<span class="theme-info-tag">' . esc_html( $instance['tag'] ) . '</span>';
                        }

                        if ( ! empty( $title ) ) {
                            echo '<h3>';
                            if ( ! empty( $icon ) ) {
                                echo '<i class="' . $icon . '"></i>';
                            }
                            echo esc_html($title);
                            echo '</h3>';
                        }

                        if ( ! empty( $desc ) ) {
                            echo '<p>' . $desc . '</p>';
                        }

                        if ( ! empty( $button_link ) && ! empty( $button_label ) ) {

                            echo '<p>';
                            $button_class = '';
                            if ( $is_button ) {
                                $button_class = 'button';
                            }

                            $button_new_tab = '_self';
                            if ( isset( $is_new_tab ) ) {
                                if ( $is_new_tab ) {
                                    $button_new_tab = '_blank';
                                }
                            }
                            echo '<a target="' . $button_new_tab . '" href="' . $button_link . '" class="' . $button_class . '">' . $button_label . '</a>';
                            echo '</p>';
                        }
                        echo '</div></div>';
                    }
                }
            }
            echo '</div>';
        }

        /**
         * Changelog tab
         *
         * Reads the "== Changelog ==" section of the theme's readme.txt, so the
         * dashboard always matches what WordPress.org shows. A "= x.y.z =" heading
         * starts a release; any other "= ... =" heading (Added, Fixed...) starts a
         * group inside it.
         *
         * @return array list of array( 'version' => string, 'groups' => array( array( 'label', 'items' ) ) )
         */
        private function parse_changelog() {
            $readme = trailingslashit( is_child_theme() ? get_stylesheet_directory() : get_template_directory() ) . 'readme.txt';
            if ( ! is_readable( $readme ) ) {
                return array();
            }

            $releases   = array();
            $in_section = false;
            $release    = -1;
            $group      = -1;
            $lines      = preg_split( '/\r\n|\r|\n/', (string) file_get_contents( $readme ) );
            foreach ( $lines as $line ) {
                $line = trim( $line );
                if ( preg_match( '/^==\s*(.+?)\s*==$/', $line, $m ) ) {
                    // Section heading: only the Changelog section is read.
                    $in_section = ( 'changelog' === strtolower( $m[1] ) );
                    continue;
                }
                if ( ! $in_section || '' === $line ) {
                    continue;
                }
                if ( preg_match( '/^=\s*(.+?)\s*=$/', $line, $m ) ) {
                    if ( preg_match( '/^v?(\d+(?:\.\d+)+)/i', $m[1], $v ) ) {
                        $releases[] = array( 'version' => $v[1], 'groups' => array() );
                        $release    = count( $releases ) - 1;
                        $group      = -1;
                    } elseif ( $release >= 0 ) {
                        $releases[ $release ]['groups'][] = array( 'label' => $m[1], 'items' => array() );
                        $group = count( $releases[ $release ]['groups'] ) - 1;
                    }
                    continue;
                }
                if ( $release < 0 ) {
                    continue;
                }
                if ( $group < 0 ) {
                    // Bullets before any group label: an unlabeled group.
                    $releases[ $release ]['groups'][] = array( 'label' => '', 'items' => array() );
                    $group = count( $releases[ $release ]['groups'] ) - 1;
                }
                $releases[ $release ]['groups'][ $group ]['items'][] = ltrim( $line, "*- \t" );
            }
            return $releases;
        }

        public function changelog() {
            $releases = $this->parse_changelog();
            if ( empty( $releases ) ) {
                return;
            }
            echo '<div class="feature-section theme-changelog">';
            foreach ( $releases as $index => $release ) {
                // Newest release starts open, older ones collapsed (native <details>, no JS).
                echo '<details class="theme-changelog-release"' . ( 0 === $index ? ' open' : '' ) . '>';
                echo '<summary><span class="theme-changelog-version">v' . esc_html( $release['version'] ) . '</span>';
                if ( version_compare( $release['version'], $this->theme_version, '==' ) ) {
                    echo ' <span class="theme-changelog-tag">' . esc_html__( 'Current', 'bosa' ) . '</span>';
                }
                echo '</summary><div class="theme-changelog-body">';
                foreach ( $release['groups'] as $group ) {
                    if ( '' !== $group['label'] ) {
                        echo '<span class="theme-changelog-group theme-changelog-group-' . esc_attr( sanitize_html_class( strtolower( $group['label'] ) ) ) . '">' . esc_html( $group['label'] ) . '</span>';
                    }
                    if ( $group['items'] ) {
                        echo '<ul>';
                        foreach ( $group['items'] as $item ) {
                            echo '<li>' . esc_html( $item ) . '</li>';
                        }
                        echo '</ul>';
                    }
                }
                echo '</div></details>';
            }
            echo '</div><!-- .theme-changelog -->';
        }

        /**
         * Bosa Pro upgrade banner shown under the tab content.
         */
        public function pro_banner() {
            if ( empty( $this->config['pro_banner'] ) ) {
                return;
            }
            $banner = $this->config['pro_banner'];
            echo '<div class="bosa-pro-banner">';
            echo '<a href="' . esc_url( $banner['link'] ) . '" target="_blank" rel="noopener noreferrer">';
            echo '<img src="' . esc_url( $banner['image'] ) . '" alt="' . esc_attr( $banner['alt'] ) . '" width="1544" height="90">';
            echo '</a>';
            echo '</div>';
        }

        /**
        * Free vs Pro tab
        */
        public function free_pro(){
            $rows = isset( $this->config['free_pro_table'] ) ? $this->config['free_pro_table'] : array();
            $cta  = isset( $this->config['free_pro_cta'] ) ? $this->config['free_pro_cta'] : array();
            if ( empty( $rows ) ) {
                return;
            }

            echo '<div id="free_pro" class="feature-section">';
            if ( ! empty( $cta['intro'] ) ) {
                echo '<p class="free-pro-intro">' . wp_kses_post( $cta['intro'] ) . '</p>';
            }
            if ( ! empty( $cta['button_label'] ) && ! empty( $cta['button_link'] ) ) {
                echo '<p class="free-pro-cta"><a class="button button-primary" target="_blank" href="' . esc_url( $cta['button_link'] ) . '">' . esc_html( $cta['button_label'] ) . '</a>';
                if ( ! empty( $cta['link_label'] ) && ! empty( $cta['link_url'] ) ) {
                    echo ' <a class="button button-secondary" target="_blank" href="' . esc_url( $cta['link_url'] ) . '">' . esc_html( $cta['link_label'] ) . '</a>';
                }
                echo '</p>';
            }
            echo '<div class="bosa-table"><table>';
            echo '<thead><tr><th></th><th>' . esc_html__( 'Free Theme', 'bosa' ) . '</th><th class="pro">' . esc_html__( 'Pro Theme', 'bosa' ) . '</th></tr></thead><tbody>';
            foreach ( $rows as $index => $row ) {
                // The first row is the headline feature, highlighted like on bosathemes.com.
                echo '<tr' . ( 0 === $index ? ' class="highlited"' : '' ) . '><th>' . esc_html( $row[0] ) . '</th>';
                foreach ( array( $row[1], $row[2] ) as $i => $value ) {
                    if ( true === $value ) {
                        $cell = '<span class="dashicons dashicons-yes" aria-label="' . esc_attr__( 'Yes', 'bosa' ) . '"></span>';
                    } elseif ( false === $value ) {
                        $cell = '<span class="dashicons dashicons-no-alt" aria-label="' . esc_attr__( 'No', 'bosa' ) . '"></span>';
                    } else {
                        $cell = esc_html( $value );
                    }
                    $is_feature = ( 1 === $i && ! empty( $row[3] ) );
                    if ( $is_feature ) {
                        $cell .= '<span>' . esc_html( $row[3] ) . '</span>';
                    }
                    echo '<td' . ( $is_feature ? ' class="feature"' : '' ) . '>' . $cell . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table></div>';
            echo '</div><!-- #free_pro -->';
        }

         /**
         * Demos tab
         */
        public function demos(){
            if( ! empty( $this->config['demos'] ) ){
                $demos= $this->config['demos'];
                    if( ! empty($demos) ){

                    /*defaults values for demos array */
                        $defaults = array(
                            'title'     => '',
                            'desc'       => '',
                            'recommended_actions'=> '',
                            'link_title'   => '',
                            'link_url'   => '',
                            'is_button' => false,
                            'is_new_tab' => false
                        );

                         echo '<div class="feature-section col-wrap">';

                        foreach ( $demos as $demos_item ) {

                            /*allowed 6 value in array */
                            $instance = wp_parse_args( (array) $demos_item, $defaults );
                            /*default values*/
                            $title = esc_html( $instance[ 'title' ] );
                            $desc = wp_kses_post( $instance[ 'desc' ] );
                            $link_title = esc_html( $instance[ 'link_title' ] );
                            $link_url = esc_url( $instance[ 'link_url' ] );
                            $is_button = $instance[ 'is_button' ];
                            $is_new_tab = $instance[ 'is_new_tab' ];


                            echo '<div class="col"><div class="col-items">';
                            if ( ! empty( $title ) ) {
                                echo '<h3>' . $title . '</h3>';
                            }
                            if ( ! empty( $desc ) ) {
                                echo '<p>' . $desc . '</p>';
                            }
                            if ( ! empty( $link_title ) && ! empty( $link_url ) ) {

                                echo '<p>';
                                $button_class = '';
                                if ( $is_button ) {
                                    $button_class = 'button';
                                }

                                $count = $this->count_recommended_actions();

                                if ( $demos_item['recommended_actions'] && isset( $count ) ) {
                                    if ( $count == 0 ) {
                                        echo '<span class="dashicons dashicons-yes"></span>';
                                    } else {
                                        echo '<span class="dashicons dashicons-no-alt"></span>';
                                    }
                                }

                                $button_new_tab = '_self';
                                if ( $is_new_tab ) {
                                    $button_new_tab = '_blank';
                                }

                                echo '<a target="' . $button_new_tab . '" href="' . $demos_item['link_url'] . '"class="' . $button_class . '">' . $demos_item['link_title'] . '</a>';
                                echo '</p>';
                            }
                            echo '</div></div><!-- .col -->';
                        }
                        echo '</div><!-- .feature-section three-col -->';
                    }
             }
        }

        /**
         * Rating tab
         */
        public function rating(){
            if( ! empty( $this->config['rating'] ) ){
                $rating= $this->config['rating'];
                if( ! empty($rating) ){

                    /*defaults values for demos array */
                    $defaults = array(
                            'title'     => '',
                            'desc'       => '',
                            'link_title'   => '',
                            'link_url'   => '',
                            'is_button' => false,
                            'is_new_tab' => false
                    );
                    echo '<div class="feature-section col-wrap">';
                    foreach ( $rating as $rating_item ) {

                            /*allowed 6 value in array */
                            $instance = wp_parse_args( (array) $rating_item, $defaults );
                            /*default values*/
                            $title = esc_html( $instance[ 'title' ] );
                            $desc = wp_kses_post( $instance[ 'desc' ] );
                            $link_title = esc_html( $instance[ 'link_title' ] );
                            $link_url = esc_url( $instance[ 'link_url' ] );
                            $is_button = $instance[ 'is_button' ];
                            $is_new_tab = $instance[ 'is_new_tab' ];


                            echo '<div class="col"><div class="col-items">';
                            if ( ! empty( $rating_item['tag'] ) ) {
                                echo '<span class="theme-info-tag">' . esc_html( $rating_item['tag'] ) . '</span>';
                            }
                            if ( ! empty( $title ) ) {
                                echo '<h3>' . $title . '</h3>';
                            }
                            if ( ! empty( $desc ) ) {
                                echo '<p>' . $desc . '</p>';
                            }
                            if ( ! empty( $link_title ) && ! empty( $link_url ) ) {

                                echo '<p>';
                                $button_class = '';
                                if ( $is_button ) {
                                    $button_class = 'button';
                                }

                                $button_new_tab = '_self';
                                if ( $is_new_tab ) {
                                    $button_new_tab = '_blank';
                                }

                                echo '<a target="' . $button_new_tab . '" href="' . $rating_item['link_url'] . '"class="' . $button_class . '">' . $rating_item['link_title'] . '</a>';
                                echo '</p>';
                            }
                            echo '</div></div><!-- .col -->';
                    }
                        echo '</div><!-- .feature-section three-col -->';
                }
            }
        }

        /**
         * Hide admin notices (from the theme, plugins or core) on the theme dashboard page only.
         */
        public function hide_admin_notices() {
            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
            if ( ! $screen || 'appearance_page_' . $this->theme_slug . '-info' !== $screen->id ) {
                return;
            }
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'all_admin_notices' );
            remove_all_actions( 'network_admin_notices' );
            remove_all_actions( 'user_admin_notices' );
        }
        /**
         * Load css and scripts for the about page
         */
        public function style_and_scripts( $hook_suffix ) {

            // this is needed on all admin pages, not just the about page, for the badge action count in the WordPress main sidebar
            wp_enqueue_style( 'kt-theme-info-css', get_template_directory_uri() . '/inc/theme-info/assets/css/theme-info.css', array(), wp_get_theme( get_template() )->get( 'Version' ) );

            if ( 'appearance_page_' . $this->theme_slug . '-info' == $hook_suffix ) {

                wp_enqueue_script( 'kt-theme-info-js', get_template_directory_uri() . '/inc/theme-info/assets/js/theme-info.js', array( 'jquery' ), wp_get_theme( get_template() )->get( 'Version' ), true );

                wp_enqueue_style( 'plugin-install' );
                wp_enqueue_script( 'plugin-install' );
                wp_enqueue_script( 'updates' );

                $count = $this->count_recommended_actions();
                wp_localize_script( 'kt-theme-info-js', 'kt_theme_info_object', array(
                    'nr_actions_recommended'   => $count,
                    'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
                    'template_directory'       => get_template_directory_uri()
                ) );

            }

        }
    }
}

$theme_name_config = esc_attr ( wp_get_theme()->get('Name') );
$theme_name_config_url = strtolower( str_replace( ' ', '-', $theme_name_config ) );

$config = array(

    // Main welcome title
    'info_title' => sprintf( esc_html__( 'Welcome to %s', 'bosa' ), $theme_name_config ),

    // Main welcome content
    'info_content' => sprintf( esc_html__( 'Thank you for choosing %s! Install the required plugins, import a starter site and launch your website in minutes, no coding needed.', 'bosa' ), '<b>'.$theme_name_config.'</b>' ),

    /**
     * Quick links
     */
    'quick_links' => array(
        'theme_url'  => array(
            'text' => __( 'Theme Details', 'bosa' ),
            'url' => 'https://bosathemes.com/' .$theme_name_config_url
        ),
        'pro_url'  => array(
            'text' => __( 'Upgrade Now', 'bosa' ),
            'url' => 'https://bosathemes.com/bosa-pro'
        ),
    ),

    'tabs' => array(
        'getting_started'      => esc_html__( 'Getting Started', 'bosa' ),
        'support'              => esc_html__( 'Support', 'bosa' ),
        'free_pro'             => esc_html__( 'Free VS Pro', 'bosa' ),
        'changelog'            => esc_html__( 'Changelog', 'bosa' ),
        'rating'               => esc_html__( 'Rate Theme', 'bosa' )
    ),

    /*Getting started tab*/
    'gs_steps' => array(
        'first' => array(
            'tag' => esc_html__( 'Setup', 'bosa' ),
            'title' => esc_html__( 'Install required plugins & import a starter site', 'bosa' ),
            'desc' => esc_html__( 'Get the plugins Bosa needs, then pick a starter site and import it in one click. Prefer your own design? You can start building from scratch right after.', 'bosa' ),
            'button_label' => esc_html__( 'Get started', 'bosa' ),
            'is_button' => true,
            'recommended_actions' => false,
            'is_new_tab' => true,
            'is_gs' => true,
        ),
    ),

    // recommended actions array.
    'recommended_actions' => array(

    ),

    // Generic Plugins array.
    'recommended_plugins' => array(),

    // Support content tab.
    'support_content' => array(
        'first' => array(
            'tag' => esc_html__( 'Guides', 'bosa' ),
            'title' => esc_html__( 'Documentation', 'bosa' ),
            'desc' => esc_html__( 'Please check our full documentation for detailed information on how to Setup and Use the theme.', 'bosa' ),
            'button_label' => esc_html__( 'Read full documentation', 'bosa' ),
            'button_link' => esc_url( 'https://bosathemes.com/docs/bosa' ),
            'is_button' => true,
            'is_new_tab' => true
        ),
        'second' => array (
            'tag' => esc_html__( 'Community', 'bosa' ),
            'title' => esc_html__( 'Support Forum', 'bosa' ),
            'desc' => esc_html__( 'Free users can get community support on the theme\'s WordPress.org forum. Ask a question or report a bug there.', 'bosa' ),
            'button_label' => esc_html__( 'Support Forum', 'bosa' ),
            'button_link' => esc_url( 'https://wordpress.org/support/theme/' . get_stylesheet() . '/' ),
            'is_button' => true,
            'is_new_tab' => true
        ),
        'third' => array(
            'tag' => esc_html__( 'Contact', 'bosa' ),
            'title' => esc_html__( 'Contact Us', 'bosa' ),
            'desc' => esc_html__( 'General inquiries? We’re here to help. Send us your question and our team will get back to you soon.', 'bosa' ),
            'button_label' => esc_html__( 'Ask Us Now', 'bosa' ),
            'button_link' => esc_url( 'https://bosathemes.com/contact/' ),
            'is_button' => true,
            'is_new_tab' => true
        )
    ),

    // Upgrade banner under the tabs (image banner, like BEW's dashboard).
    'pro_banner' => array(
        'image' => get_template_directory_uri() . '/inc/theme-info/assets/images/bosa-pro-banner.jpg',
        'alt' => esc_html__( 'Bosa Pro unlocks 200+ starter sites, full customization control, and priority support — all included.', 'bosa' ),
        'link' => 'https://bosathemes.com/bosa-pro/#pricing',
    ),

    // Free vs Pro, same as bosathemes.com/bosa-pro: feature, Free, Pro (true / false / text), optional Pro note.
    'free_pro_table' => array(
        array( esc_html__( 'Pre-Built Starter Websites', 'bosa' ), '1', '200+', esc_html__( '( Most themes offer far fewer )', 'bosa' ) ),
        array( esc_html__( 'Priority Support Access', 'bosa' ), esc_html__( 'Community Forum', 'bosa' ), true ),
        array( esc_html__( 'Featured Posts Section (Advanced)', 'bosa' ), false, true ),
        array( esc_html__( 'Featured Pages Section', 'bosa' ), false, true ),
        array( esc_html__( 'Blog Advertisement Banner', 'bosa' ), false, true ),
        array( esc_html__( 'Header Notification Bar', 'bosa' ), false, true ),
        array( esc_html__( 'Footer Instagram Feed', 'bosa' ), false, true ),
        array( esc_html__( 'Coming Soon / Maintenance Mode', 'bosa' ), false, true ),
    ),
    'free_pro_cta' => array(
        'intro' => esc_html__( 'Bosa Pro unlocks 200+ starter sites, full customization control, and priority support — all included.', 'bosa' ),
        'button_label' => esc_html__( 'Buy Now', 'bosa' ),
        'button_link' => 'https://bosathemes.com/bosa-pro/#pricing',
        'link_label' => esc_html__( 'View Full Comparison', 'bosa' ),
        'link_url' => 'https://bosathemes.com/free-vs-pro',
    ),

    // rating
    'rating' => array(
        'first'=> array(
            'tag' => esc_html__( 'Feedback', 'bosa' ),
            'title' => esc_html__( 'Show us how much you like our theme.', 'bosa' ),
            'desc' => esc_html__( 'If you like our work, please give us a moment of your time to rate our theme 5 stars on ', 'bosa' ) .'<a target="_blank" href="https://wordpress.org/support/theme/' .$theme_name_config_url. '/reviews">' . 'wordpress.org' . '</a>' . esc_html__('. It will give us more energy to work on this theme.', 'bosa' ),
            'link_title' => esc_html__( 'Rate Theme', 'bosa' ),
            'link_url' => esc_url( 'https://wordpress.org/support/theme/' .$theme_name_config_url. '/reviews' ),
            'is_button' => true,
            'is_new_tab' => true
        )
    )
);
return new Bosa_Theme_Info( $config );
