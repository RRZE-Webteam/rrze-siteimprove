<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

class Options
{
    protected $optionName = 'rrze_siteimprove';

    /*
     * Standard Einstellungen werden definiert
     * @return array
     */
    protected function defaultOptions()
    {
        return [
            'integration_enable' => 0,
            'integration_token' => '',
            'integration_badge_role' => 'administrator',
            'analytics_enable' => 0,
            'analytics_code' => ''
        ];
    }

    /*
     * Gibt die Einstellungen zurück.
     * @return object
     */
    public function getOptions()
    {
        $defaults = $this->defaultOptions();

        $options = (array) get_option($this->optionName);
        $options = wp_parse_args($options, $defaults);
        $options = array_intersect_key($options, $defaults);

        return (object) $options;
    }

    public function getOptionName()
    {
        return $this->optionName;
    }
}
