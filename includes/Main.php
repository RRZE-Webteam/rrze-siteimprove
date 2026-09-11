<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

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
            esc_url(admin_url('options-general.php?page=' . $this->settings->getMenuSlug())),
            esc_html__('Settings', 'rrze-siteimprove')
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
        $postId = get_queried_object_id();

        if (is_singular() && $postId) {
            printf('<meta name="pageID" content="%d">%s', absint($postId), PHP_EOL);
        }
    }
}
