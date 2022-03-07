<?php

namespace RRZE\Siteimprove\Analytics;

defined('ABSPATH') || exit;

use function RRZE\Siteimprove\plugin;
use function RRZE\Siteimprove\settings;

class Analytics
{
    protected $options;

    public function __construct()
    {
        $this->options = settings()->getOptions();

        add_filter('siteimprove_analytics_enabled', function () {
            return $this->options->analytics_enable;
        });

        add_action('wp_enqueue_scripts', [$this, 'wpEnqueueScripts']);
        add_shortcode('siteimprove_analytics_privacy_policy', [$this, 'shortcode']);
    }

    public function wpEnqueueScripts()
    {
        if ($this->options->analytics_enable) {
            wp_enqueue_script(
                'rrze-siteimprove-analytics',
                plugins_url('dist/analytics.js', plugin()->getBasename()),
                [],
                plugin()->getVersion()
            );
            wp_localize_script(
                'rrze-siteimprove-analytics',
                'siteanalyze',
                [
                    'code' => $this->options->analytics_code
                ]
            );
        }
    }

    public function shortcode($atts)
    {
        $atts = shortcode_atts([
            'display' => 'true'
        ], $atts);

        $display = $atts['display'] == 'true' ? true : false;

        if ($this->options->analytics_enable && $display) {
            return $this->privacyPolicyContent();
        }

        return '';
    }

    protected function privacyPolicyContent()
    {
        $locale = $this->getLocale();
        $file = dirname(__FILE__) . '/privacy-policy-' . $locale . '.php';
        return $this->getFileContent($file);
    }

    protected function getLocale()
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

    protected function getFileContent($file = '')
    {
        if (file_exists($file)) {
            ob_start();
            include $file;
            return ob_get_clean();
        }

        return '';
    }
}
