<?php
namespace AIPoweredContentAssistant\Traits;

trait Singleton {
    private static $instance = null;

    public static function get_instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __clone() {}
    private function __wakeup() {}
}
