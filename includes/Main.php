<?php

namespace RRZE\Siteimprove;

use RRZE\Siteimprove\Analytics\Analytics;

defined('ABSPATH') || exit;

class Main
{
    public function __construct()
    {
        new Options();
        new Settings();
        new Integration();
        new Robots();
        new Analytics();

        add_action('wp_head', [$this, 'headMeta']);
    }

    public function headMeta()
    {
        if (is_singular()) {
            $post = get_post();
            printf('<meta name="pageID" content="%d">%s', $post->ID, PHP_EOL);
        }
    }
}
