<?php

namespace RRZE\Siteimprove\Analytics;

use RRZE\Siteimprove\Main;

defined('ABSPATH') || exit;

class Analytics
{
    protected $main;

    protected $options;

    public function __construct(Main $main)
    {
        $this->main = $main;
        $this->options = $this->main->options->get_options();

        add_action('wp_enqueue_scripts', [$this, 'wp_enqueue_scripts']);
        add_shortcode('siteimprove_analytics_privacy_policy', [$this, 'shortcode']);
    }

    public function wp_enqueue_scripts()
    {
        if ($this->options->analytics_enable) {
            wp_enqueue_script('rrze-siteimprove-analytics', plugins_url('js/siteimprove-analytics.min.js', $this->main->plugin_basename));
            wp_localize_script('rrze-siteimprove-analytics', 'siteanalyze', [
                'code' => $this->options->analytics_code
            ]);
        }
    }

    public function shortcode($atts)
    {
        $atts = shortcode_atts([
    		'display' => 'true'
    	], $atts);

        $display = $atts['display'] == 'true' ? true : false;

    	if ($this->options->analytics_enable && $display) {
            return $this->privacy_policy_content();
    	}

        return '';
    }

    protected function privacy_policy_content()
    {
        $locale = $this->get_locale();
        $file = dirname(__FILE__) . '/privacy-policy-' . $locale . '.php';
        return $this->get_file_content($file);
    }

    protected function get_locale()
    {
        $locale = get_locale();
        $locale_default = 'en_US';
        $locale_dictionary = [
            'de_DE' => 'de_DE',
            'de_DE_formal' => 'de_DE',
            'de_CH' => 'de_DE',
            'en_US' => 'en_US',
            'en_CA' => 'en_US',
            'en_AU' => 'en_US',
            'en_NZ' => 'en_US',
            'en_ZA' => 'en_US',
            'en_GB' => 'en_US'
        ];

        if (isset($locale_dictionary[$locale])) {
            return $locale_dictionary[$locale];
        }

        return $locale_default;
    }

    protected function get_file_content($file = '') {
        if (file_exists($file)) {
            ob_start();
            include $file;
            return ob_get_clean();
        }

        return '';
    }
}
