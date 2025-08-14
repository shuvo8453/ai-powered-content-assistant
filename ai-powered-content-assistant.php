<?php
/**
 * Plugin Name: AI-Powered Content Assistant
 * Description: Class-based WordPress plugin scaffold for AI-powered blog generation using Google Gemini API.
 * Version:     1.0.0
 * Author:      Saiful Islam Shuvo
 * Text Domain: ai-powered-content-assistant
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once __DIR__ . '/includes/traits/trait-singleton.php';

define( 'AIPCA_PATH', plugin_dir_path( __FILE__ ) );
define( 'AIPCA_URL', plugin_dir_url( __FILE__ ) );
define( 'AIPCA_VERSION', '1.0.0' );

// Autoload classes
spl_autoload_register( function( $class ) {
    $prefix = 'AIPoweredContentAssistant\\';
    $base_dir = __DIR__ . '/includes/';

    if ( strpos( $class, $prefix ) === 0 ) {
        $relative_class = substr( $class, strlen( $prefix ) );
        $file = $base_dir . 'class-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';
        if ( file_exists( $file ) ) {
            require $file;
        }
    }
} );

// Initialize main plugin
add_action( 'plugins_loaded', function() {
    \AIPoweredContentAssistant\Plugin::get_instance();
});
