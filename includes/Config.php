<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

class Config
{
    private static array $config = [
        'version' => '1.7.1',
        'wprequires' => '6.8',
        'wptestedup' => '7.1',
        'phprequires' => '8.2',
        'plugin_slug' => 'rrze-siteimprove',
        'textdomain' => 'rrze-siteimprove',
        'option_name' => 'rrze_siteimprove',
        'settings_group' => 'rrze_siteimprove_options',
        'settings_page' => 'rrze_siteimprove_options',
        'menu_slug' => 'rrze-siteimprove',
        'settings_parent_slug' => 'options-general.php',
        'settings_menu_position' => 90,
        'analytics_asset_handle' => 'rrze-siteimprove',
        'analytics_script_object' => 'siteanalyze',
        'analytics_enabled_filter' => 'siteimprove_analytics_enabled',
        'privacy_policy_shortcode' => 'siteimprove_analytics_privacy_policy',
        'siteimprove_analytics_base_url' => 'https://siteimproveanalytics.com/js/siteanalyze_',
        'rrze_settings_option_name' => 'rrze_settings',
        'rrze_settings_analytics_code_path' => ['plugins', 'siteimprove', 'analytics_jscode'],
        'default_options' => [
            'analytics_enable' => 0,
            'analytics_code' => ''
        ],
    ];

    public static function get(string $key = '', mixed $default = null): mixed
    {
        if ($key === '') {
            return self::$config;
        }

        return self::$config[$key] ?? $default;
    }
}
