<?php
namespace AIPoweredContentAssistant;

use AIPoweredContentAssistant\Traits\Singleton;

class Admin {
    use Singleton;

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function add_menu_page() {
        add_menu_page(
            __( 'AI Content Assistant', 'ai-powered-content-assistant' ),
            __( 'AI Assistant', 'ai-powered-content-assistant' ),
            'manage_options',
            'ai-content-assistant',
            [ Content_Generator::get_instance(), 'render_page' ],
            'dashicons-edit',
            20
        );
    }

    public function enqueue_assets( $hook ) {
        // Only load assets on our plugin page
        if ( $hook !== 'toplevel_page_ai-content-assistant' ) {
            return;
        }

        // Bootstrap 5 CSS
        wp_enqueue_style(
            'aipca-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
            [],
            '5.3.2'
        );

        // Bootstrap JS Bundle (with Popper)
        wp_enqueue_script(
            'aipca-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
            [],
            '5.3.2',
            true
        );

        // Your custom admin styles
        wp_enqueue_style(
            'aipca-admin',
            plugin_dir_url( __FILE__ ) . '../assets/css/admin.css',
            [],
            '1.0.0'
        );

        // Your custom admin JS
        wp_enqueue_script(
            'aipca-admin',
            plugin_dir_url( __FILE__ ) . '../assets/js/admin.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );

        // Localize data for admin.js
        wp_localize_script( 'aipca-admin', 'AIPCA_Vars', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'aipca_nonce' ),
            'plugin_url' => plugin_dir_url( __FILE__ ),
            'rest_url' => esc_url_raw( rest_url( 'aipca/v1/' ) )
        ] );
    }

}
