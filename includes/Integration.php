<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

use RRZE\Siteimprove\Options;
use function RRZE\Siteimprove\plugin;

/**
 * Integration class
 * 
 * This class handles the integration of the Siteimprove plugin with WordPress.
 * It includes methods for enqueuing scripts, saving session URLs, and adding JavaScript to the head section.
 * 
 * @package RRZE\Siteimprove
 * @since 1.0.0
 */
class Integration
{
    /**
     * JavaScript library URL for Siteimprove.
     * 
     * This URL points to the Siteimprove JavaScript library that is used for integration.
     * It is loaded in the admin area to provide functionality for the Siteimprove plugin.
     * 
     * @var string
     */
    const JS_LIBRARY_URL = 'https://cdn.siteimprove.net/cms/overlay.js';

    /**
     * Transient key for storing URLs.
     * 
     * This constant defines the transient key used to store URLs for the current user.
     * It is used to manage the URLs that need to be processed by the Siteimprove plugin.
     * The URLs are stored in a transient to avoid excessive API calls and improve performance.
     * 
     * @var string
     */
    const TRANSIENT_URL = 'siteimprove_url_';

    /**
     * Options
     * 
     * @var \stdClass
     */
    protected $options;

    /**
     * Constructor
     * 
     * This method initializes the Integration class, sets up the options, and registers hooks.
     * It adds actions for admin initialization, saving session URLs, and including JavaScript in the head.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->options = Options::getOptions();

        add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);

        if (!$this->options->integration_enable) {
            return;
        }

        add_action('admin_init', [$this, 'adminInit']);

        add_action('publish_page', [$this, 'saveSessionUrlPost']);

        add_action('publish_post', [$this, 'saveSessionUrlPost']);

        add_action('edit_term', [$this, 'saveSessionUrlTerm'], 10, 3);

        add_action('create_term', [$this, 'saveSessionUrlTerm'], 10, 3);

        add_action('transition_post_status', [$this, 'saveSessionUrlProduct'], 10, 3);

        add_action('wp_head', [$this, 'wpHead']);
    }

    /**
     * Admin Enqueue Scripts.
     * 
     * This method enqueues the necessary scripts and styles for the admin area of the Siteimprove plugin.
     * It checks if the current page is the settings page and loads the appropriate assets.
     * 
     * @param string $hook The current admin page hook.
     * @return void
     */
    public function adminEnqueueScripts($hook)
    {
        if ($hook == 'tools_page_rrze-siteimprove') {
            $assetFile = include(plugin()->getPath('build') . 'settings.asset.php');

            wp_enqueue_style(
                'rrze-siteimprove-admin',
                plugins_url('build/settings.css', plugin()->getBasename()),
                [],
                $assetFile['version'] ?? plugin()->getVersion(),
            );

            $assetFile = include(plugin()->getPath('build') . 'settings.asset.php');

            wp_enqueue_script(
                'rrze-siteimprove-admin',
                plugins_url('build/settings.js', plugin()->getBasename()),
                $assetFile['dependencies'] ?? [],
                $assetFile['version'] ?? plugin()->getVersion(),
            );
        }
    }

    /**
     * Admin Initialization.
     * 
     * This method is called during the admin initialization phase.
     * It checks if the user is not performing an AJAX request and if there are URLs stored in the transient.
     * If URLs are found, it adds JavaScript to handle Siteimprove actions based on the URLs.
     * 
     * @return void
     */
    public function adminInit()
    {
        global $pagenow;

        $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());

        if (!wp_doing_ajax() && !empty($urls)) {
            if (is_array($urls) && count($urls) > 1) {
                $url = esc_url(home_url());
                $method = 'siteimprove_recrawl';
            } else {
                $url = array_pop($urls);
                $method = 'siteimprove_recheck';
            }
            delete_transient(self::TRANSIENT_URL . get_current_user_id());
            $this->addJs($url, $method);
        }

        switch ($pagenow) {
            case 'post.php':
                $post_id = !empty($_GET['post']) ? absint($_GET['post']) : 0;
                $permalink = get_permalink($post_id);

                if ($permalink) {
                    $this->addJs(get_permalink($post_id), 'siteimprove_input');
                    // Only display recheck button in published posts.
                    if (get_post_status($post_id) === 'publish') {
                        $this->addJs(get_permalink($post_id), 'siteimprove_recheck_button');
                    }
                }
                break;

            case 'term.php':
            case 'edit-tags.php':
                $tagId = !empty($_GET['tag_ID']) ? absint($_GET['tag_ID']) : 0;
                $taxonomy = !empty($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';

                if ($pagenow == 'term.php' || ($pagenow == 'edit-tags.php' && !empty($_GET['action']) && $_GET['action'] === 'edit')) {
                    $this->addJs(get_term_link($tagId, $taxonomy), 'siteimprove_input');
                    $this->addJs(get_term_link($tagId, $taxonomy), 'siteimprove_recheck_button');
                }
                break;

            default:
                $this->addJs((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", 'siteimprove_domain');
        }
    }

    /**
     * Add JavaScript to the page.
     * 
     * This method enqueues the Siteimprove JavaScript library and localizes it with the necessary data.
     * It sets up the token, text, and URL for the Siteimprove integration.
     * 
     * @param string $url The URL to be processed by Siteimprove.
     * @param string $type The type of action to be performed by Siteimprove (e.g., 'siteimprove_input', 'siteimprove_domain').
     * @param bool $auto Whether to automatically include the JavaScript (default is true).
     * @param string|false $txt Optional text for the recheck button (default is false).
     * 
     * @return void
     */
    private function addJs($url, $type, $auto = true, $txt = false)
    {
        $assetFile = include(plugin()->getPath('build') . 'siteimprove.asset.php');

        wp_enqueue_script(
            'rrze-siteimprove',
            plugins_url('build/siteimprove.js', plugin()->getBasename()),
            $assetFile['dependencies'] ?? [],
            $assetFile['version'] ?? plugin()->getVersion(),
        );

        wp_localize_script(
            'rrze-siteimprove',
            esc_js($type),
            [
                'token' => $this->options->integration_token,
                'txt'   => __('Siteimprove Recheck', 'rrze-siteimprove'),
                'url'   => $url,
            ]
        );

        wp_enqueue_script(
            'siteimprove_overlay',
            self::JS_LIBRARY_URL,
            [],
            false,
            true
        );
    }

    /**
     * Save the current post URL in a transient for later processing.
     * 
     * This method saves the current post URL in a transient for the current user.
     * It is used to store URLs that need to be processed by Siteimprove when the user visits the admin area.
     * 
     * @param int $post_ID The ID of the post being saved.
     * 
     * @return void
     */
    public function saveSessionUrlPost($post_ID)
    {
        if (!wp_is_post_revision($post_ID) && !wp_is_post_autosave($post_ID)) {
            $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());
            $urls[] = get_permalink($post_ID);
            set_transient(self::TRANSIENT_URL . get_current_user_id(), $urls, 900);
        }
    }

    /**
     * Save the current term URL in a transient for later processing.
     * 
     * This method saves the current term URL in a transient for the current user.
     * It is used to store URLs that need to be processed by Siteimprove when the user visits the admin area.
     * 
     * @param int $termId The ID of the term being saved.
     * @param int $tTId The term taxonomy ID.
     * @param string $taxonomy The taxonomy of the term.
     * 
     * @return void
     */
    public function saveSessionUrlTerm($termId, $tTId, $taxonomy)
    {
        $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());
        $urls[] = get_term_link($termId, $taxonomy);
        set_transient(self::TRANSIENT_URL . get_current_user_id(), $urls, 900);
    }

    /**
     * Save the current product URL in a transient for later processing.
     * 
     * This method saves the current product URL in a transient for the current user.
     * It is used to store URLs that need to be processed by Siteimprove when the user visits the admin area.
     * 
     * @param string $newStatus The new status of the post (e.g., 'publish').
     * @param string $oldStatus The old status of the post.
     * @param object $post The post object being saved.
     * 
     * @return void
     */
    public function saveSessionUrlProduct($newStatus, $oldStatus, $post)
    {
        if (
            $newStatus == 'publish'
            && !empty($post->ID)
            && in_array($post->post_type, ['product'])
        ) {
            $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());
            $urls[] = get_permalink($post->ID);
            set_transient(self::TRANSIENT_URL . get_current_user_id(), $urls, 900);
        }
    }

    /**
     * Add JavaScript to the head section.
     * 
     * This method checks the current user's role and adds the appropriate JavaScript for Siteimprove integration.
     * It determines the current page type and sets the URL and method for Siteimprove actions.
     * 
     * @return void
     */
    public function wpHead()
    {
        if (current_user_can($this->options->integration_badge_role)) {
            $type = $this->getCurrentPageType();
            switch ($type) {
                case 'page':
                case 'single':
                case 'category':
                case 'tag':
                case 'tax':
                    $url = get_permalink();
                    $method = 'siteimprove_input';
                    $this->addJs($url, $method);
                    break;

                default:
                    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $method = 'siteimprove_domain';
                    $this->addJs($url, $method);
            }
        }
    }

    /**
     * Get the current page type.
     * 
     * This method determines the type of the current page based on the global `$wp_query` object.
     * It returns a string representing the page type, such as 'home', 'page', 'single', 'category', etc.
     * 
     * @return string The type of the current page.
     */
    protected function getCurrentPageType()
    {
        global $wp_query;
        $pageType = 'notfound';

        if ($wp_query->is_page) {
            $pageType = is_front_page() ? 'front' : 'page';
        } elseif ($wp_query->is_home) {
            $pageType = 'home';
        } elseif ($wp_query->is_single) {
            $pageType = ($wp_query->is_attachment) ? 'attachment' : 'single';
        } elseif ($wp_query->is_category) {
            $pageType = 'category';
        } elseif ($wp_query->is_tag) {
            $pageType = 'tag';
        } elseif ($wp_query->is_tax) {
            $pageType = 'tax';
        } elseif ($wp_query->is_archive) {
            if ($wp_query->is_day) {
                $pageType = 'day';
            } elseif ($wp_query->is_month) {
                $pageType = 'month';
            } elseif ($wp_query->is_year) {
                $pageType = 'year';
            } elseif ($wp_query->is_author) {
                $pageType = 'author';
            } else {
                $pageType = 'archive';
            }
        } elseif ($wp_query->is_search) {
            $pageType = 'search';
        } elseif ($wp_query->is_404) {
            $pageType = 'notfound';
        }

        return $pageType;
    }
}
