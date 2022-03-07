<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

class Robots
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('robots_txt', [$this, 'robotsTxtFilter'], 99, 2);
    }

    /**
     * Filter robots.txt
     * @param string $output
     * @param string $public
     * @return string
     */
    public function robotsTxtFilter($output, $public)
    {
        if ($public == 0) {
            $output = $this->robotsTxtDefault();
        }
        return $output;
    }

    /**
     * Get default robots.txt
     * @return string
     */
    protected function robotsTxtDefault()
    {
        $output = '';
        $output .= 'User-agent: Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0) SiteCheck-sitecrawl by Siteimprove.com' . PHP_EOL;
        $output .= 'Disallow:' . PHP_EOL;
        $output .= 'User-agent: Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0) LinkCheck by Siteimprove.com' . PHP_EOL;
        $output .= 'Disallow:' . PHP_EOL;
        $output .= 'User-agent: Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0) Image size by Siteimprove.com' . PHP_EOL;
        $output .= 'Disallow:' . PHP_EOL;
        $output .= 'User-agent: *' . PHP_EOL;
        $output .= 'Disallow: /' . PHP_EOL;
        return $output;
    }
}
