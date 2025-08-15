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
    protected static $optionName = 'rrze_siteimprove';

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
        return [
            'integration_enable' => 0,
            'integration_token' => '',
            'integration_badge_role' => 'administrator',
            'analytics_enable' => 0,
            'analytics_code' => ''
        ];
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

        $options = (array) get_option(self::$optionName);
        $options = wp_parse_args($options, $defaults);
        $options = array_intersect_key($options, $defaults);

        return (object) $options;
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
        return self::$optionName;
    }
}
