<?php
namespace AIPoweredContentAssistant;

use AIPoweredContentAssistant\Traits\Singleton;

class Admin {
    use Singleton;

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
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
        <div class="wrap">
            <h1><?php esc_html_e( 'AI-Powered Content Assistant', 'ai-powered-content-assistant' ); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field( 'aipca_generate_content', 'aipca_nonce' ); ?>
                <textarea name="blog_topic" rows="3" style="width:100%;" placeholder="<?php esc_attr_e( 'Enter your blog topic...', 'ai-powered-content-assistant' ); ?>"></textarea>
                <p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Generate Outline', 'ai-powered-content-assistant' ); ?>"></p>
            </form>
        </div>
        <?php
    }
}
