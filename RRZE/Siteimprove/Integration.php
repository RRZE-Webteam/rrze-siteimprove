<?php

namespace RRZE\Siteimprove;

use RRZE\Siteimprove\Main;
use RRZE\Siteimprove\Auth;

defined('ABSPATH') || exit;

class Integration
{
    const JS_LIBRARY_URL = 'https://cdn.siteimprove.net/cms/overlay.js';

    const TRANSIENT_URL = 'rrze_siteimprove_url';

    protected $main;

    protected $option_name;

    protected $options;

    public function __construct(Main $main)
    {
        $this->main = $main;
        $this->option_name = $this->main->options->get_option_name();
        $this->options = $this->main->options->get_options();

        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        if (!$this->options->integration_enable) {
            return;
        }

        add_action('admin_init', [$this, 'siteimprove_init']);
        add_action('publish_page', [$this, 'siteimprove_save_session_url_post']);
        add_action('publish_post', [$this, 'siteimprove_save_session_url_post']);
        add_action('edit_term', [$this, 'siteimprove_save_session_url_term'], 10, 3);
        add_action('create_term', [$this, 'siteimprove_save_session_url_term'], 10, 3);
        add_action('transition_post_status', [$this, 'siteimprove_save_session_url_product'], 10, 3);
        add_action('wp_head', [$this, 'siteimprove_wp_head']);
    }

    public function admin_enqueue_scripts($hook)
    {
        if ($hook == 'tools_page_rrze-siteimprove') {
            $plugin_data = get_plugin_data($this->main->plugin_file);

            wp_enqueue_style('rrze-siteimprove-admin', plugins_url('css/admin.min.css', $this->main->plugin_basename), [], $plugin_data['Version']);
            wp_enqueue_script('rrze-siteimprove-admin', plugins_url('js/admin.min.js', $this->main->plugin_basename), ['jquery'], $plugin_data['Version']);
        }
    }

    public function siteimprove_init()
    {
        global $pagenow;

        $urls = get_transient('siteimprove_url_' . get_current_user_id());

        if (! wp_doing_ajax() && ! empty($urls)) {
            if (is_array($urls) && count($urls) > 1) {
                $url = esc_url(home_url());
                $method = 'siteimprove_recrawl';
            } else {
                $url = array_pop($urls);
                $method = 'siteimprove_recheck';
            }
            delete_transient('siteimprove_url_' . get_current_user_id());
            $this->siteimprove_add_js($url, $method);
        }

        switch ($pagenow) {
            case 'post.php':
                $post_id = ! empty($_GET['post']) ? (int) $_GET['post'] : 0;
                $permalink = get_permalink($post_id);

                if ($permalink) {
                    $this->siteimprove_add_js(get_permalink($post_id), 'siteimprove_input');
                    // Only display recheck button in published posts.
                    if (get_post_status($post_id) === 'publish') {
                        $this->siteimprove_add_js(get_permalink($post_id), 'siteimprove_recheck_button');
                    }
                }
                break;

            case 'term.php':
            case 'edit-tags.php':
                $tag_id = ! empty($_GET['tag_ID']) ? (int) $_GET['tag_ID'] : 0;
                $taxonomy = ! empty($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';

                if ($pagenow == 'term.php' || ($pagenow == 'edit-tags.php' && ! empty($_GET['action']) && $_GET['action'] === 'edit')) {
                    $this->siteimprove_add_js(get_term_link((int) $tag_id, $taxonomy), 'siteimprove_input');
                    $this->siteimprove_add_js(get_term_link((int) $tag_id, $taxonomy), 'siteimprove_recheck_button');
                }
                break;

            default:
                $this->siteimprove_add_js((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", 'siteimprove_domain');

        }
    }

    private function siteimprove_add_js($url, $type, $auto = true, $txt = false)
    {
        wp_enqueue_script('rrze-siteimprove', plugins_url('js/siteimprove.min.js', $this->main->plugin_basename), ['jquery']);
        wp_localize_script('rrze-siteimprove', esc_js($type), [
            'token' => $this->options->integration_token,
            'txt'   => __('Siteimprove Recheck', 'rrze-siteimprove'),
            'url'   => $url,
        ]);
        wp_enqueue_script('siteimprove_overlay', self::JS_LIBRARY_URL, [], false, true);
    }

    /**
     * Save in transient post url.
     */
    public function siteimprove_save_session_url_post($post_ID)
    {
        if (! wp_is_post_revision($post_ID) && ! wp_is_post_autosave($post_ID)) {
            $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());
            $urls[] = get_permalink($post_ID);
            set_transient(self::TRANSIENT_URL . get_current_user_id(), $urls, 900);
        }
    }

    /**
     * Save in transient term url.
     */
    public function siteimprove_save_session_url_term($term_id, $tt_id, $taxonomy)
    {
        $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());
        $urls[] = get_term_link((int) $term_id, $taxonomy);
        set_transient(self::TRANSIENT_URL . get_current_user_id(), $urls, 900);
    }

    /**
     * Save in transient product url (WooCommerce).
     */
    public function siteimprove_save_session_url_product($new_status, $old_status, $post)
    {
        if (
            $new_status == 'publish'
            && ! empty($post->ID)
            && in_array($post->post_type, ['product'])
        ) {
            $urls = get_transient(self::TRANSIENT_URL . get_current_user_id());
            $urls[] = get_permalink($post->ID);
            set_transient(self::TRANSIENT_URL . get_current_user_id(), $urls, 900);
        }
    }

    /**
     * Include js in frontend pages.
     */
    public function siteimprove_wp_head()
    {
        if (current_user_can($this->options->integration_badge_role)) {
            $type = $this->get_current_page_type();
            switch ($type) {
                case 'page':
                case 'single':
                case 'category':
                case 'tag':
                case 'tax':
                    $url = get_permalink();
                    $method = 'siteimprove_input';
                    $this->siteimprove_add_js($url, $method);
                    break;

                default:
                    $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $method = 'siteimprove_domain';
                    $this->siteimprove_add_js($url, $method);
            }
        }
    }

    /**
     * Return current page type.
     */
    protected function get_current_page_type()
    {
        global $wp_query;
        $page_type = 'notfound';

        if ($wp_query->is_page) {
            $page_type = is_front_page() ? 'front' : 'page';
        } elseif ($wp_query->is_home) {
            $page_type = 'home';
        } elseif ($wp_query->is_single) {
            $page_type = ($wp_query->is_attachment) ? 'attachment' : 'single';
        } elseif ($wp_query->is_category) {
            $page_type = 'category';
        } elseif ($wp_query->is_tag) {
            $page_type = 'tag';
        } elseif ($wp_query->is_tax) {
            $page_type = 'tax';
        } elseif ($wp_query->is_archive) {
            if ($wp_query->is_day) {
                $page_type = 'day';
            } elseif ($wp_query->is_month) {
                $page_type = 'month';
            } elseif ($wp_query->is_year) {
                $page_type = 'year';
            } elseif ($wp_query->is_author) {
                $page_type = 'author';
            } else {
                $page_type = 'archive';
            }
        } elseif ($wp_query->is_search) {
            $page_type = 'search';
        } elseif ($wp_query->is_404) {
            $page_type = 'notfound';
        }

        return $page_type;
    }
}
