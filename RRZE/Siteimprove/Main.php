<?php

namespace RRZE\Siteimprove;

use RRZE\Siteimprove\Options;
use RRZE\Siteimprove\Settings;
use RRZE\Siteimprove\Integration;
use RRZE\Siteimprove\Robots;
use RRZE\Siteimprove\Analytics\Analytics;

defined('ABSPATH') || exit;

class Main
{
    public $plugin_file;

    public $plugin_basename;

    public $options;

    public $settings;

    public $integration;

    public $robots;

    public $analytics;

    public function __construct($plugin_file)
    {
        $this->plugin_file = $plugin_file;
        $this->plugin_basename = plugin_basename($plugin_file);

        $this->options = new Options();
        $this->settings = new Settings($this);
        $this->integration = new Integration($this);
        $this->robots = new Robots();
        $this->analytics = new Analytics($this);

        add_action('wp_head', [$this, 'head_meta']);
    }

    public function head_meta() {
        if (is_singular()) {
            $post = get_post();
            printf('<meta name="pageID" content="%d">%s', $post->ID, PHP_EOL);
        }
    }
}
