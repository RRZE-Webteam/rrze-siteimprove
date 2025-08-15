<?php

namespace RRZE\Siteimprove\Analytics;

defined('ABSPATH') || exit;

use RRZE\Siteimprove\Options;
use function RRZE\Siteimprove\plugin;

/**
 * Analytics class for the Siteimprove plugin.
 * 
 * This class handles the analytics functionality of the Siteimprove plugin.
 * It includes methods to enqueue scripts, handle shortcodes, and manage privacy policy content.
 * 
 * @package RRZE\Siteimprove\Analytics
 * @since 1.0.0
 */
class Analytics
{
    protected $options;

    public function __construct()
    {
        $this->options = Options::getOptions();

        add_action('wp_enqueue_scripts', [$this, 'wpEnqueueScripts']);
        add_shortcode('siteimprove_analytics_privacy_policy', [$this, 'shortcode']);
    }

    protected function isAnalyticEnabled()
    {
        return apply_filters('siteimprove_analytics_enabled', $this->options->analytics_enable);
    }

    public function wpEnqueueScripts()
    {
        if ($this->isAnalyticEnabled()) {
            $assetFile = include(plugin()->getPath('build') . 'analytics.asset.php');
            wp_enqueue_script(
                'rrze-siteimprove-analytics',
                plugins_url('build/analytics.js', plugin()->getBasename()),
                $assetFile['dependencies'] ?? [],
                $assetFile['version'] ?? plugin()->getVersion(),
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

        if ($this->isAnalyticEnabled() && $display) {
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
        $localeDefault = 'en_US';
        $localeDictionary = [
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

        if (isset($localeDictionary[$locale])) {
            return $localeDictionary[$locale];
        }

        return $localeDefault;
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
