<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

class Options
{
    protected $option_name = 'rrze_siteimprove';

    public function __construct()
    {
    }

    /*
     * Standard Einstellungen werden definiert
     * @return array
     */
    protected function default_options()
    {
        $options = [
            'integration_enable' => 0,
            'integration_token' => '',
            'integration_badge_role' => 'administrator',
            'analytics_enable' => 0,
            'analytics_code' => ''
        ];

        return $options;
    }

    /*
     * Gibt die Einstellungen zurück.
     * @return object
     */
    public function get_options()
    {
        $defaults = $this->default_options();

        $options = (array) get_option($this->option_name);
        $options = wp_parse_args($options, $defaults);
        $options = array_intersect_key($options, $defaults);

        return (object) $options;
    }

    public function get_option_name()
    {
        return $this->option_name;
    }
}
