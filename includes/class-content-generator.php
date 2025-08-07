<?php
namespace AIPoweredContentAssistant;

use AIPoweredContentAssistant\Traits\Singleton;

class Content_Generator {
    use Singleton;

    public function __construct() {
        add_action( 'wp_ajax_aipca_generate_outline', [ $this, 'ajax_generate_outline' ] );
    }

    public function render_page() {
        ?>
        <div class="wrap bootstrap-wrapper">
            <div class="container my-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><?php esc_html_e( 'AI-Powered Content Assistant', 'ai-powered-content-assistant' ); ?></h4>
                    </div>
                    <div class="card-body">
                        <form id="aipca-blog-form">
                            <?php wp_nonce_field( 'aipca_generate_content', 'aipca_nonce' ); ?>

                            <div class="mb-3">
                                <label for="blog_topic" class="form-label fw-bold"><?php esc_html_e( 'Blog Topic', 'ai-powered-content-assistant' ); ?></label>
                                <textarea name="blog_topic" id="blog_topic" rows="3" class="form-control" placeholder="<?php esc_attr_e( 'Enter your blog topic...', 'ai-powered-content-assistant' ); ?>"></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" id="btn-generate-outline" class="btn btn-outline-primary">
                                    📝 <?php esc_html_e('Generate Post Outline', 'ai-powered-content-assistant'); ?>
                                </button>
                                <button type="button" id="btn-generate-full" class="btn btn-outline-success">
                                    📄 <?php esc_html_e('Generate Full Post', 'ai-powered-content-assistant'); ?>
                                </button>
                            </div>
                        </form>

                        <div id="aipca-loader" class="text-center mt-3" style="display:none;">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2"><?php esc_html_e( 'Generating content, please wait...', 'ai-powered-content-assistant' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outline Modal -->
        <div class="modal fade" id="aipcaOutlineModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php esc_html_e('Generated Outline', 'ai-powered-content-assistant'); ?></h5>
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-warning btn-sm" id="aipca-outline-remake" title="Remake Outline">♻</button>
                            <button type="button" class="btn btn-outline-success btn-sm" id="aipca-outline-to-full" title="Create Full Post">✍</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="aipca-outline-copy" title="Copy">📋</button>
                            <button type="button" class="border btn-close mt-0" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                <div class="modal-body" id="aipca-outline-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function ajax_generate_outline() {
        check_ajax_referer( 'aipca_nonce', 'security' );

        $topic = sanitize_text_field( $_POST['topic'] ?? '' );
        if ( empty( $topic ) ) {
            wp_send_json_error( [ 'message' => 'Please enter a topic.' ] );
        }

        $outline = $this->generate_outline( $topic );

        if ( is_wp_error( $outline ) ) {
            wp_send_json_error( [ 'message' => $outline->get_error_message() ] );
        }

        wp_send_json_success( [ 'content' => wpautop( esc_html( $outline ) ) ] );
    }

    public function generate_outline( $topic ) {
        $prompt = "Generate a detailed blog post outline with headings and subheadings for the topic: {$topic}";
        return Gemini_API::get_instance()->request( $prompt, 800 );
    }
}
