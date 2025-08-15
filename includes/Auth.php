<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

/**
 * Auth class
 * 
 * This class handles authentication with the Siteimprove API to retrieve a token.
 * 
 * @package RRZE\Siteimprove
 * @since 1.0.0
 */
class Auth
{
    /**
     * The URL to request the Siteimprove token.
     * 
     * This URL is used to request a token from the Siteimprove API.
     * 
     * @var string
     */
    const TOKEN_REQUEST_URL = 'https://my2.siteimprove.com/auth/token';

    /**
     * Request a new token from the Siteimprove API.
     * 
     * This method sends a request to the Siteimprove API to obtain a new token.
     * It checks the response code and returns the token if successful, or false if not.
     * 
     * @return string|false The token if successful, false otherwise.
     */
    public static function requestToken(): string|false
    {
        // Request new token.
        $response = wp_remote_get(
            self::TOKEN_REQUEST_URL . '?cms=wordpress_' . get_bloginfo('version'),
            [
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]
        );

        // Check the response code.
        $responseCode = wp_remote_retrieve_response_code($response);
        $data = wp_remote_retrieve_body($response);
        if ($responseCode == 200 && !empty($data)) {
            $json = json_decode($data);
            if (!empty($json->token)) {
                return $json->token;
            }
        }
        return false;
    }
}
