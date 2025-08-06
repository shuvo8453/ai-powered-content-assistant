<?php
namespace AIPoweredContentAssistant;

use AIPoweredContentAssistant\Traits\Singleton;

class Content_Generator {
    use Singleton;

    public function generate_outline( $topic ) {
        $prompt = "Generate a detailed blog post outline for: {$topic}";
        return Gemini_API::get_instance()->request( $prompt );
    }

    public function generate_full_post( $topic ) {
        $prompt = "Write a complete, engaging blog post based on this topic: {$topic}";
        return Gemini_API::get_instance()->request( $prompt, 2000 );
    }
}
