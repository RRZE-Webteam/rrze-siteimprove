<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

/**
 * Options class
 * 
 * This class provides methods to manage the options for the Siteimprove plugin.
 * It includes methods to get default options, retrieve stored options, and get the option name.
 * 
 * @package RRZE\Siteimprove
 * @since 1.0.0
 */
class Options
{
    /**
     * Default options for the Siteimprove plugin.
     * 
     * This method returns the default options for the Siteimprove plugin.
     * It includes settings for integration and analytics, allowing users to configure the plugin's behavior.
     * 
     * @return array
     */
    protected static function defaultOptions()
    {
        return Config::get('default_options', []);
    }

    /**
     * Get the options for the Siteimprove plugin.
     * 
     * This method retrieves the options for the Siteimprove plugin from the WordPress database.
     * It merges the stored options with the default options and returns them as an object.
     * 
     * @return object \stdClass
     */
    public static function getOptions(): \stdClass
    {
        $defaults = self::defaultOptions();

        $options = (array) get_option(self::getOptionName());
        $options = wp_parse_args($options, $defaults);
        $options = array_intersect_key($options, $defaults);

        return (object) $options;
    }

    /**
     * Get the analytics code from the central RRZE settings option.
     *
     * @return string
     */
    public static function getServerAnalyticsCode(): string
    {
        $settings = get_site_option(Config::get('rrze_settings_option_name', 'rrze_settings'));
        $path = Config::get('rrze_settings_analytics_code_path', []);
        $value = $settings;
        $i;

        if (!is_array($path)) {
            return '';
        }

        for ($i = 0; $i < count($path); $i++) {
            if (is_object($value) && isset($value->{$path[$i]})) {
                $value = $value->{$path[$i]};
            } elseif (is_array($value) && isset($value[$path[$i]])) {
                $value = $value[$path[$i]];
            } else {
                return '';
            }
        }

        if (!is_scalar($value)) {
            return '';
        }

        return self::sanitizeAnalyticsCode((string) $value);
    }

    /**
     * Get the effective analytics code.
     *
     * @return string
     */
    public static function getAnalyticsCode(): string
    {
        $serverCode = self::getServerAnalyticsCode();

        if ($serverCode !== '') {
            return $serverCode;
        }

        return self::sanitizeAnalyticsCode(self::getOptions()->analytics_code);
    }

    /**
     * Sanitize the analytics code for use in the Siteimprove script URL.
     *
     * @param string $code Analytics code.
     * @return string
     */
    public static function sanitizeAnalyticsCode(string $code): string
    {
        $code = preg_replace('/[^A-Za-z0-9_-]/', '', sanitize_text_field($code));

        return is_string($code) ? $code : '';
    }

    /**
     * Get the name of the options.
     * 
     * This method returns the name of the options used by the Siteimprove plugin.
     * It is used to identify the options in the WordPress database.
     * 
     * @return string
     */
    public static function getOptionName(): string
    {
        return Config::get('option_name', 'rrze_siteimprove');
    }
}
