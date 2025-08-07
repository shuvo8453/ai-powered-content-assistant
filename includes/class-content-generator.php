<?php
namespace AIPoweredContentAssistant;

use AIPoweredContentAssistant\Traits\Singleton;

class Content_Generator {
    use Singleton;

    public function __construct() {
        add_action( 'wp_ajax_aipca_generate_outline', [ $this, 'ajax_generate_outline' ] );
        add_action( 'wp_ajax_aipca_generate_full_post', [ $this, 'ajax_generate_full_post' ] );
        add_action( 'wp_ajax_aipca_download_docx', [ $this, 'ajax_download_docx' ] );
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
                            <button type="button" class="border btn-close mt-0" data-bs-dismiss="modal" id="aipca-modal-remove"></button>
                        </div>
                    </div>

                    <div class="modal-body position-relative" id="aipca-outline-body">
                        <div id="aipca-outline-loader" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75 d-none" style="z-index: 10;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Regenerating outline...</p>
                            </div>
                        </div>

                        <div id="aipca-outline-content"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Post Modal -->
        <div class="modal fade" id="aipcaFullPostModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php esc_html_e('Generated Full Post', 'ai-powered-content-assistant'); ?></h5>
                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm" id="aipca-full-download" title="Download as DOCX">⬇ .docx</button>
                            <button type="button" class="border btn-close mt-0" data-bs-dismiss="modal" id="aipca-modal-full-close"></button>
                        </div>
                    </div>

                    <div class="modal-body position-relative" id="aipca-full-body">
                        <div id="aipca-full-loader" class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75 d-none" style="z-index: 10;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Generating full post...</p>
                            </div>
                        </div>

                        <div id="aipca-full-content"></div>
                    </div>
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

       wp_send_json_success( [ 'content' => wp_kses_post( $outline ) ] );
    }

    public function generate_outline( $topic ) {
        $prompt = "Generate a detailed blog post outline with headings and subheadings for the topic: {$topic}. 
        Format the response with HTML tags: 
        - Use <h3> for main sections 
        - Use <h4> for subsections 
        - Use <ul><li> for bullet points 
        - Wrap the entire response in <div class='aipca-outline'>";

        $outline = Gemini_API::get_instance()->request( $prompt, 800 );

        // Basic check: if API returned empty or failed
        if ( empty( $outline ) || ! is_string( $outline ) ) {
            return new \WP_Error( 'empty_response', 'Failed to generate outline. Please try again.' );
        }

        // Clean Markdown-style code fences
        $outline = preg_replace( '/^```html|```$/m', '', $outline );

        // Fallback if the outline doesn’t contain expected HTML structure
        if ( ! preg_match( '/<h[1-6]>/i', $outline ) ) {
            $outline = nl2br( esc_html( $outline ) ); // Escape just in case
            $outline = "<div class='aipca-outline'><h3>Outline</h3>{$outline}</div>";
        }

        return $outline;
    }

    public function ajax_generate_full_post() {
        check_ajax_referer( 'aipca_nonce', 'security' );

        $topic = sanitize_text_field( $_POST['topic'] ?? '' );
        if ( empty( $topic ) ) {
            wp_send_json_error( [ 'message' => 'Please enter a topic.' ] );
        }

        $prompt = "Write a full, detailed blog post for the topic: {$topic}. 
        Use HTML formatting: 
        - Use <h2> for section titles
        - Use <p> for paragraphs
        - Use <ul><li> for lists
        - Wrap the entire response in <div class='aipca-full-post'>";

        $post = Gemini_API::get_instance()->request( $prompt, 1500 );

        $post = preg_replace('/^```html|```$/m', '', $post);

        if ( ! preg_match( '/<h[1-6]>/i', $post ) ) {
            $post = nl2br( $post ); // fallback
            $post = "<div class='aipca-full-post'><h2>Blog Post</h2>{$post}</div>";
        }

        wp_send_json_success( [ 'content' => wp_kses_post( $post ) ] );
    }

    public function generate_full_post( $topic ) {
        $prompt = "Write a complete, high-quality blog post on the topic: \"{$topic}\". 
        The structure should include an introduction, several sections with headings and subheadings, and a conclusion. 
        Use HTML formatting:
        - <h2> for main headings
        - <h3> for subheadings
        - <p> for paragraphs
        - <strong> or <ul><li> where appropriate
        Wrap everything in: <div class='aipca-full-post'>...</div>";

        $content = Gemini_API::get_instance()->request( $prompt, 1500 );

        // Validate the response
        if ( empty( $content ) || ! is_string( $content ) ) {
            return new \WP_Error( 'empty_response', 'Failed to generate blog post. Please try again.' );
        }

        // Clean Markdown-style code fences
        $content = preg_replace( '/^```html|```$/m', '', $content );

        // Fallback if the content doesn't include expected HTML
        if ( ! preg_match( '/<h[1-6]>/i', $content ) ) {
            $content = nl2br( esc_html( $content ) ); // Escape just in case
            $content = "<div class='aipca-full-post'><h2>Blog Post</h2>{$content}</div>";
        }

        return $content;
    }

    public function ajax_download_docx() {
        check_ajax_referer( 'aipca_nonce', 'security' );

        $html = $_POST['html'] ?? '';
        if ( empty( $html ) ) {
            wp_die( 'No content provided.' );
        }

        // Convert HTML to simple Word-compatible format
        $html = wp_kses_post( $html );

        // Use basic .docx headers for download
        header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        header("Content-Disposition: attachment; filename=blog-post.doc");
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");

        echo '<html><body>' . $html . '</body></html>';
        exit;
    }

}
