<?php
namespace AIPoweredContentAssistant;

use AIPoweredContentAssistant\Traits\Singleton;

class Plugin {
    use Singleton;

    public function __construct() {
        $this->init_hooks();
        Content_Generator::get_instance();
    }

    private function init_hooks() {
        // Load admin features
        if ( is_admin() ) {
            Admin::get_instance();
        }
    }
}
