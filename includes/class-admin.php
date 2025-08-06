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
            [ $this, 'render_admin_page' ],
            'dashicons-edit',
            20
        );
    }

    public function render_admin_page() {
    ?>
        <div class="wrap bootstrap-wrapper">
            <div class="container my-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><?php esc_html_e( 'AI-Powered Content Assistant', 'ai-powered-content-assistant' ); ?></h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <?php wp_nonce_field( 'aipca_generate_content', 'aipca_nonce' ); ?>

                            <div class="mb-3">
                                <label for="blog_topic" class="form-label fw-bold"><?php esc_html_e( 'Blog Topic', 'ai-powered-content-assistant' ); ?></label>
                                <textarea name="blog_topic" id="blog_topic" rows="3" class="form-control" placeholder="<?php esc_attr_e( 'Enter your blog topic...', 'ai-powered-content-assistant' ); ?>"></textarea>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="dashicons dashicons-edit"></i> <?php esc_html_e( 'Generate Outline', 'ai-powered-content-assistant' ); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
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
