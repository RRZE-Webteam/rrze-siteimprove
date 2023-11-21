<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

class Integration
{
    /**
     * Siteimprove JS Library URL.
     * @var string
     */
    const JS_LIBRARY_URL = 'https://cdn.siteimprove.net/cms/overlay.js';

    /**
     * Transient URL Prefix.
     * @var string
     */
    const TRANSIENT_URL = 'siteimprove_url_';

    /**
     * Settings Options.
     * @var string
     */
    protected $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = settings()->getOptions();

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
     * Enqueue scripts and styles.
     */
    public function adminEnqueueScripts($hook)
    {
        if ($hook == 'tools_page_rrze-siteimprove') {
            wp_enqueue_style(
                'rrze-siteimprove-admin',
                plugins_url('dist/settings.css', plugin()->getBasename()),
                [],
                plugin()->getVersion()
            );
            wp_enqueue_script(
                'rrze-siteimprove-admin',
                plugins_url('dist/settings.js', plugin()->getBasename()),
                ['jquery'],
                plugin()->getVersion()
            );
        }
    }

    /**
     * Admin Init.
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
     * Add JS.
     * @param string $url
     * @param string $type
     * @param bool $auto
     * @param bool $text
     * @return void
     */
    private function addJs($url, $type, $auto = true, $txt = false)
    {
        wp_enqueue_script(
            'rrze-siteimprove',
            plugins_url('dist/siteimprove.js', plugin()->getBasename()),
            ['jquery'],
            plugin()->getVersion()
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
     * Save in transient post url.
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
     * Save in transient term url.
     * @param int $term_id
     * @param int $tt_id
     * @param string $taxonomy
     */
    public function saveSessionUrlTerm($term_id, $tt_id, $taxonomy)
    {
        $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());
        $urls[] = get_term_link($term_id, $taxonomy);
        set_transient(self::TRANSIENT_URL . get_current_user_id(), $urls, 900);
    }

    /**
     * Save in transient product url (WooCommerce).
     * @param string $new_status
     * @param string $old_status
     * @param object $post
     */
    public function saveSessionUrlProduct($new_status, $old_status, $post)
    {
        if (
            $new_status == 'publish'
            && !empty($post->ID)
            && in_array($post->post_type, ['product'])
        ) {
            $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());
            $urls[] = get_permalink($post->ID);
            set_transient(self::TRANSIENT_URL . get_current_user_id(), $urls, 900);
        }
    }

    /**
     * Include js in frontend pages.
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
     * Return current page type.
     * @return string
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
