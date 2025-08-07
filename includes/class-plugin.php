<?php
namespace AIPoweredContentAssistant;

use AIPoweredContentAssistant\Traits\Singleton;

class Plugin {
    use Singleton;

    public function __construct() {
        $this->define_constants();
        $this->init_hooks();
        Content_Generator::get_instance();
    }

    private function define_constants() {
        define( 'AIPCA_PATH', plugin_dir_path( __FILE__ ) );
        define( 'AIPCA_URL', plugin_dir_url( __FILE__ ) );
        define( 'AIPCA_VERSION', '1.0.0' );
    }

    private function init_hooks() {
        // Load admin features
        if ( is_admin() ) {
            Admin::get_instance();
        }
    }
}
