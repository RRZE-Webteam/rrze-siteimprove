<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

use RRZE\Siteimprove\Integration;
use RRZE\Siteimprove\Settings;
use RRZE\Siteimprove\Analytics\Analytics;
use function RRZE\Siteimprove\plugin;

/**
 * Main class for the Siteimprove plugin.
 * 
 * This class initializes the plugin, sets up the settings, and registers hooks.
 * 
 * @package RRZE\Siteimprove
 * @since 1.0.0
 */
class Main
{
    /**
     * Settings instance
     * 
     * @var Settings
     */
    protected $settings;

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->settings = new Settings();

        new Integration();

        new Analytics();

        add_filter('plugin_action_links_' . plugin()->getBaseName(), [$this, 'settingsLink']);

        add_action('wp_head', [$this, 'headMeta']);
    }

    /**
     * Add a settings link to the plugin action links.
     * 
     * @param array $links
     * @return array
     */
    public function settingsLink($links): array
    {
        $settingsLink = sprintf(
            '<a href="%s">%s</a>',
            admin_url('tools.php?page=' . $this->settings->getMenuSlug()),
            __('Settings', 'rrze-jobspreader-analytics')
        );
        array_unshift($links, $settingsLink);
        return $links;
    }

    /**
     * Add a meta tag with the page ID to the head section.
     * 
     * This is useful for analytics or tracking purposes.
     * 
     * @return void
     */
    public function headMeta()
    {
        if (is_singular() && $post = get_queried_object()) {
            printf('<meta name="pageID" content="%d">%s', $post->ID, PHP_EOL);
        }
    }
}
