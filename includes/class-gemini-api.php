<?php
namespace AIPoweredContentAssistant;

use AIPoweredContentAssistant\Traits\Singleton;

class Gemini_API {
    use Singleton;

    private $api_key;

    public function __construct() {
        $this->api_key = get_option( 'aipca_gemini_api_key', 'AIzaSyBQoFwXH4TSKpyjxpWfgPI9L3WfHornvF4' );
    }

    public function request( $prompt, $max_tokens = 800 ) {
        if ( empty( $this->api_key ) ) {
            return new \WP_Error( 'no_api_key', __( 'Gemini API key not set.', 'ai-powered-content-assistant' ) );
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$this->api_key}";

        $body = [
            'contents' => [[ 'parts' => [[ 'text' => $prompt ]] ]]
        ];

        $response = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 60
        ]);

        if ( is_wp_error( $response ) ) return $response;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }
}
