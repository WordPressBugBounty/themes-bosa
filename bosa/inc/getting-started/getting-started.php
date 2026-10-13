<?php
/**
 * Bosa Notice Handler
 *
 * @package Bosa
 * @since Bosa 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugins the theme needs to work as designed, in install order.
 * Demo specific plugins (Kirki, forms, breadcrumbs, header builders...) are
 * installed per starter site by the importer, so they are not listed here.
 *
 * @since Bosa 1.7.0
 * @return array slug => array( file, name )
 */
if( !function_exists( 'bosa_get_required_plugins' ) ){
    function bosa_get_required_plugins() {
        return array(
            'elementor' => array(
                'file' => 'elementor/elementor.php',
                'name' => 'Elementor',
            ),
            'advanced-import' => array(
                'file' => 'advanced-import/advanced-import.php',
                'name' => 'Advanced Import',
            ),
            'keon-toolset' => array(
                'file' => 'keon-toolset/keon-toolset.php',
                'name' => 'Bosa Starter Sites',
            ),
            'bosa-elementor-for-woocommerce' => array(
                'file' => 'bosa-elementor-for-woocommerce/bosa-elementor-for-woocommerce.php',
                'name' => 'BEW',
            ),
        );
    }
}

/**
 * Required plugins that are not active yet.
 *
 * @since Bosa 1.7.0
 * @return array slug => array( file, name )
 */
if( !function_exists( 'bosa_get_inactive_required_plugins' ) ){
    function bosa_get_inactive_required_plugins() {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $inactive = array();
        foreach ( bosa_get_required_plugins() as $slug => $plugin ) {
            if ( ! is_plugin_active( $plugin['file'] ) && ! is_plugin_active_for_network( $plugin['file'] ) ) {
                $inactive[ $slug ] = $plugin;
            }
        }
        return $inactive;
    }
}

/**
 * Enqueue getting started styles and scripts
 */
add_action( 'admin_enqueue_scripts', 'bosa_widgets_backend_enqueue' );
function bosa_widgets_backend_enqueue()
{
    wp_enqueue_style( 'bosa-getting-started', get_template_directory_uri() . '/inc/getting-started/getting-started.css', array(), wp_get_theme( get_template() )->get( 'Version' ) );
    wp_register_script( 'bosa-custom-widgets', get_template_directory_uri() . '/inc/getting-started/getting-started.js', array( 'jquery' ), wp_get_theme( get_template() )->get( 'Version' ), true );
    wp_localize_script( 'bosa-custom-widgets', 'bosa_adi_install', bosa_getting_started_script_data() );
    wp_enqueue_media();
    wp_enqueue_script( 'bosa-custom-widgets' );
}

/**
 * Data passed to getting-started.js (admin pages and Customizer).
 *
 * @since Bosa 1.7.0
 */
function bosa_getting_started_script_data() {
    return array(
        'btn_text' => esc_html__( 'Processing...', 'bosa' ),
        'nonce'    => wp_create_nonce( 'bosa_demo_import_nonce' ),
        'noncen'   => 'bosa_demo_import_nonce',
        'adminurl' => esc_url( admin_url() ),
        'plugins'  => array_keys( bosa_get_inactive_required_plugins() ),
        'redirect' => esc_url_raw( admin_url( 'themes.php?page=' . get_template() . '-info' ) ),
    );
}

/**
 * Class to handle notices and Advanced Demo Import
 *
 * Class Bosa_Notice_Handler
 */
class Bosa_Notice_Handler {

    public static $nonce;

	/**
	 * Empty Constructor
	 */
	public function __construct() {
        /* activation notice */
        add_action( 'switch_theme', array( $this, 'flush_dismiss_status' ) );
        add_action( 'admin_init', array($this,'getting_started_notice_dismissed' ) );
        add_action( 'admin_notices', array( $this, 'bosa_theme_info_welcome_admin_notice' ), 3 );
        add_action( 'wp_ajax_bosa_getting_started', array( $this, 'bosa_getting_started' ) );
    }

    /**
     * Display an admin notice linking to the about page
     */
    public function bosa_theme_info_welcome_admin_notice() {
        $theme = wp_get_theme();
        if ( is_admin() && current_user_can( 'install_plugins' ) && !get_user_meta( get_current_user_id(), 'gs_notice_dismissed' ) ){
            $inactive = bosa_get_inactive_required_plugins();
            echo '<div class="updated notice notice-success is-dismissible getting-started">';
            if ( empty( $inactive ) ){
                echo ( '<p><strong>' . esc_html__( 'All required plugins are active.', 'bosa' ) . '</strong> ' . esc_html__( 'Pick a starter site to import, or dismiss this notice.', 'bosa' ) . '</p><p><a href="' . esc_url( admin_url( 'themes.php?page=advanced-import' ) ) . '" class="button button-primary">' . esc_html__( 'Import a Starter Site', 'bosa' ) . '</a> <a href="' . esc_url( admin_url( 'themes.php?page=' . get_template() . '-info' ) ) . '" class="button">' . sprintf( esc_html__( 'Go to %s Theme', 'bosa' ), esc_html( $theme->get( 'Name' ) ) ) . '</a></p>' );
            }else{
            echo ( '<p><strong>' . sprintf( esc_html__( 'Welcome! Thank you for choosing %1$s. To fully take advantage of the best our theme can offer, get started.', 'bosa' ), esc_attr( $theme->get( 'Name' ) ) ) . '</strong></p><p class="plugin-notice">' . sprintf( esc_html__( 'Clicking on get started will install and activate %s.', 'bosa' ), esc_html( wp_sprintf( '%l', wp_list_pluck( $inactive, 'name' ) ) ) ) . '</p><p><a href="#" class="bosa-install-plugins button button-primary">' . esc_html__( 'Get started', 'bosa' ) . '</a> <a href="' . esc_url( admin_url( 'themes.php?page=' . get_template() . '-info' ) ) . '" class="button">' . sprintf( esc_html__( 'Go to %s Theme', 'bosa' ), esc_html( $theme->get( 'Name' ) ) ) . '</a></p>' );
            }
            echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( 'gs-notice-dismissed', 'dismiss_admin_notices' ) ) ) . '" class="getting-started-notice-dismiss">' . esc_html__( 'Dismiss', 'bosa' ) . '</a>';
            echo '</div>';
        }
    }

    /**
     * Register dismissal of the getting started notification.
     * Acts on the dismiss link.
     * If clicked, the admin notice disappears and will no longer be visible to this user.
     */
    public function getting_started_notice_dismissed() {

        if ( isset( $_GET['gs-notice-dismissed'] ) ){
            add_user_meta( get_current_user_id(), 'gs_notice_dismissed', 'true' );
        }
    }

    /**
     * Deletes the getting started notice's dismiss status upon theme switch.
     */
    public function flush_dismiss_status(){
        delete_user_meta( get_current_user_id(), 'gs_notice_dismissed', 'true' );
    }

	/**
	 * Get Started Notice
	 * Active callback of wp_ajax
	 * return void
	 */
	public function bosa_getting_started() {

		check_ajax_referer( 'bosa_demo_import_nonce', 'security' );

        // Plugins activated here must not hijack the redirect back to the theme page.
        add_action( 'shutdown', 'bosa_clear_plugin_activation_redirects' );

        $required = bosa_get_required_plugins();
        $slug     = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';

        // Only the theme's own required plugins can be installed from here.
        if ( ! isset( $required[ $slug ] ) ) {
            wp_send_json_error();
        }
        $plugin = $required[ $slug ]['file'];

        $status = array(
            'install' => 'plugin',
            'slug'    => $slug,
        );
        $status['redirect'] = admin_url( '/themes.php?page=advanced-import&browse=all&at-gsm-hide-notice=welcome' );

        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ( is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin ) ) {
            // Plugin is activated
            wp_send_json_success( $status );
        }


        if ( ! current_user_can( 'install_plugins' ) ) {
            $status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.', 'bosa' );
            wp_send_json_error( $status );
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

        // Looks like a plugin is installed, but not active.
        if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
            $plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
            $status['plugin']     = $plugin;
            $status['pluginName'] = $plugin_data['Name'];

            if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
                $result = activate_plugin( $plugin );

                if ( is_wp_error( $result ) ) {
                    $status['errorCode']    = $result->get_error_code();
                    $status['errorMessage'] = $result->get_error_message();
                    wp_send_json_error( $status );
                }

                wp_send_json_success( $status );
            }
        }

        $api = plugins_api(
            'plugin_information',
            array(
                'slug'   => $slug,
                'fields' => array(
                    'sections' => false,
                ),
            )
        );

        if ( is_wp_error( $api ) ) {
            $status['errorMessage'] = $api->get_error_message();
            wp_send_json_error( $status );
        }

        $status['pluginName'] = $api->name;

        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );
        $result   = $upgrader->install( $api->download_link );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            $status['debug'] = $skin->get_upgrade_messages();
        }

        if ( is_wp_error( $result ) ) {
            $status['errorCode']    = $result->get_error_code();
            $status['errorMessage'] = $result->get_error_message();
            wp_send_json_error( $status );
        } elseif ( is_wp_error( $skin->result ) ) {
            $status['errorCode']    = $skin->result->get_error_code();
            $status['errorMessage'] = $skin->result->get_error_message();
            wp_send_json_error( $status );
        } elseif ( $skin->get_errors()->get_error_code() ) {
            $status['errorMessage'] = $skin->get_error_messages();
            wp_send_json_error( $status );
        } elseif ( is_null( $result ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            WP_Filesystem();
            global $wp_filesystem;

            $status['errorCode']    = 'unable_to_connect_to_filesystem';
            $status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'bosa' );

            // Pass through the error from WP_Filesystem if one was raised.
            if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
                $status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
            }

            wp_send_json_error( $status );
        }

        $install_status = install_plugin_install_status( $api );

        if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
            $result = activate_plugin( $install_status['file'] );

            if ( is_wp_error( $result ) ) {
                $status['errorCode']    = $result->get_error_code();
                $status['errorMessage'] = $result->get_error_message();
                wp_send_json_error( $status );
            }
        }

        wp_send_json_success( $status );
	}
}
new Bosa_Notice_Handler;

/**
 * Remember when Bosa was first activated, used to time upgrade notices.
 * Existing installs get the timestamp the first time they load wp-admin on 1.7.0.
 *
 * @since Bosa 1.7.0
 */
add_action( 'after_switch_theme', 'bosa_record_activation_time' );
add_action( 'admin_init', 'bosa_record_activation_time' );
function bosa_record_activation_time() {
    if ( ! get_option( 'bosa_theme_activated_time' ) ) {
        add_option( 'bosa_theme_activated_time', time() );
    }
}

/**
 * Clears the "redirect to my page after activation" flags set by required plugins,
 * so Get started always returns to the theme page.
 *
 * @since Bosa 1.7.0
 */
function bosa_clear_plugin_activation_redirects() {
    delete_transient( 'bew_activation_redirect' );
    delete_transient( 'elementor_activation_redirect' );
}
