<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

class Auth
{
    /**
     * Token Request URL
     * @var string
     */
    const TOKEN_REQUEST_URL = 'https://my2.siteimprove.com/auth/token';

    /**
     * Return Siteimprove token.
     * @return mixed
     */
    public static function requestToken()
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
