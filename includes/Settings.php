<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

use RRZE\Siteimprove\Options;

/**
 * Settings class for the Siteimprove plugin.
 * 
 * This class handles the plugin settings, including the integration and analytics options.
 * 
 * @package RRZE\Siteimprove
 * @since 1.0.0
 */
class Settings
{
    /**
     * Option name
     * 
     * @var string
     */
    protected $optionName;

    /**
     * Options
     * 
     * @var \stdClass
     */
    protected $options;

    /**
     * Settings menu page
     * 
     * @var string
     */
    protected $settingsMenu;

    /**
     * Menu page slug
     * 
     * @var string
     */
    protected $menuSlug;

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->optionName = Options::getOptionName();
        $this->options = Options::getOptions();
        $this->menuSlug = Config::get('menu_slug', 'rrze-siteimprove');

        add_action('admin_menu', [$this, 'settingsMenu']);
        add_action('admin_init', [$this, 'settings']);
    }

    /**
     * Get the menu page slug.
     *
     * @return string
     */
    public function getMenuSlug(): string
    {
        return $this->menuSlug;
    }

    /**
     * Create the settings menu page.
     * 
     * @return void
     */
    public function settingsMenu()
    {
        $capability = $this->getSettingsCapability();

        $this->settingsMenu = add_submenu_page(
            Config::get('settings_parent_slug', 'options-general.php'),
            __('RRZE Siteimprove', 'rrze-siteimprove'),
            __('RRZE Siteimprove', 'rrze-siteimprove'),
            $capability,
            $this->menuSlug,
            [$this, 'settingsPage'],
            Config::get('settings_menu_position', 90)
        );

        // Add Contextual Help Menu
        add_action('load-' . $this->settingsMenu, [$this, 'adminHelpMenu']);
    }

    /**
     * Render the settings page.
     * 
     * This method outputs the HTML for the settings page, including the form for saving options.
     * It uses the WordPress settings API to handle form submission and validation.
     * 
     * @return void
     */
    public function settingsPage()
    {
?>
        <div class="wrap rrze-siteimprove rrze-siteimprove-admin">
            <h2><?php esc_html_e('Siteimprove', 'rrze-siteimprove'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields(Config::get('settings_group', 'rrze_siteimprove_options'));
                do_settings_sections(Config::get('settings_page', 'rrze_siteimprove_options'));
                submit_button(); ?>
            </form>
        </div>
    <?php
    }

    /**
     * Register the settings for the Siteimprove options.
     * 
     * This method sets up the settings sections and fields for the Siteimprove plugin.
     * It includes options for integration and analytics, allowing users to configure the plugin's behavior.
     * 
     * @return void
     */
    public function settings()
    {
        // Register the settings for the Siteimprove options.
        register_setting(
            Config::get('settings_group', 'rrze_siteimprove_options'),
            $this->optionName,
            [$this, 'optionsValidate']
        );

        // Add Siteimprove Overlay notice section.
        add_settings_section(
            'rrze_siteimprove_overlay_section',
            __('Siteimprove Overlay', 'rrze-siteimprove'),
            [$this, 'overlaySection'],
            Config::get('settings_page', 'rrze_siteimprove_options')
        );

        // Add Analytics section.
        add_settings_section(
            'rrze_siteimprove_analytics_section',
            __('Analytics', 'rrze-siteimprove'),
            [$this, 'analyticsSection'],
            Config::get('settings_page', 'rrze_siteimprove_options')
        );

        // Analytics Enable Field
        add_settings_field(
            'rrze_siteimprove_analytics_enable',
            __('Enable', 'rrze-siteimprove'),
            [$this, 'analyticsEnableField'],
            Config::get('settings_page', 'rrze_siteimprove_options'),
            'rrze_siteimprove_analytics_section'
        );

        // Analytics Code Field
        add_settings_field(
            'rrze_siteimprove_analytics_code',
            __('Code', 'rrze-siteimprove'),
            [$this, 'analyticsCodeField'],
            Config::get('settings_page', 'rrze_siteimprove_options'),
            'rrze_siteimprove_analytics_section'
        );
    }

    /**
     * Validate and sanitize the options input.
     * 
     * This method processes the input from the settings form, ensuring that the values are valid and sanitized.
     * It handles enabling/disabling Siteimprove Analytics and validating the analytics code.
     * 
     * @param array $input
     * @return array
     */
    public function optionsValidate($input)
    {
        $input = is_array($input) ? $input : [];

        $input['analytics_enable'] = !empty($input['analytics_enable']) ? 1 : 0;

        if (Options::getServerAnalyticsCode() !== '') {
            $input['analytics_code'] = '';
        } else {
            $input['analytics_code'] = !empty($input['analytics_code'])
                ? Options::sanitizeAnalyticsCode($input['analytics_code'])
                : '';
        }

        return $input;
    }

    /**
     * Siteimprove Overlay section description.
     * 
     * This method outputs a notice that the former overlay integration is no longer handled by this plugin.
     * 
     * @return void
     */
    public function overlaySection()
    {
        echo '<p>', esc_html__('The former Siteimprove Overlay integration, including token retrieval, recheck and recrawl functions, has been replaced by the official Siteimprove WordPress plugin. RRZE Siteimprove now only embeds the Siteimprove Analytics JavaScript.', 'rrze-siteimprove'), '</p>';
    }

    /**
     * Analytics section description.
     * 
     * This method outputs a description for the analytics section on the settings page.
     * It explains the purpose of Siteimprove Analytics and how to use the shortcode for privacy policy.
     * 
     * @return void
     */
    public function analyticsSection()
    {
        echo '<p>', esc_html__('Get insight into visitor behavior and optimize your website with powerful analytics that anyone can use.', 'rrze-siteimprove'), '</p>';
        echo '<p>', esc_html__('Use the shortcode [siteimprove_analytics_privacy_policy] to display the corresponding privacy policy with an opt-out button.', 'rrze-siteimprove'), '</p>';
    }

    /**
     * Analytics Enable Field
     * 
     * This method outputs the checkbox for enabling Siteimprove Analytics.
     * It allows users to enable or disable analytics tracking on their site.
     * 
     * @return void
     */
    public function analyticsEnableField()
    {
        $checked = $this->options->analytics_enable ? true : false;
    ?>
        <input id="siteimprove-analytics-enable" type="checkbox" <?php checked($checked); ?> name="<?php printf('%s[analytics_enable]', esc_attr($this->optionName)); ?>" value="1" />
        <p class="description"><?php esc_html_e('This integrates the Siteimprove AI Analytics script into the website. Note: This activates the display of a consent banner.', 'rrze-siteimprove'); ?></p>
    <?php
    }

    /**
     * Analytics Code Field
     * 
     * This method outputs the input field for the Siteimprove Analytics code.
     * It allows users to enter their specific analytics code for tracking purposes.
     * 
     * @return void
     */
    public function analyticsCodeField()
    {
        if (Options::getServerAnalyticsCode() !== '') {
?>
        <p class="description"><?php esc_html_e('The Siteimprove Analytics code is already configured server-side and is therefore not displayed here.', 'rrze-siteimprove'); ?></p>
<?php
            return;
        }

    ?>
        <input type="text" id="siteimprove-analytics-code" name="<?php printf('%s[analytics_code]', esc_attr($this->optionName)); ?>" value="<?php echo esc_attr($this->options->analytics_code); ?>" />
        <p class="description"><?php esc_html_e('The code that is specific to your account.', 'rrze-siteimprove'); ?></p>
<?php
    }

    /**
     * Add contextual help to the settings page.
     * 
     * This method adds help tabs to the settings page, providing information about the plugin's features and how to use them.
     * 
     * @return void
     */
    public function adminHelpMenu()
    {
        $helpTab = [
            'id' => $this->settingsMenu,
            'title' => __('Overview', 'rrze-siteimprove'),
            'content' => '
            <h2>' . esc_html__('Siteimprove Help', 'rrze-siteimprove') . '</h2>
            <p>' . esc_html__('RRZE Siteimprove embeds the Siteimprove Analytics JavaScript on your WordPress site.', 'rrze-siteimprove') . '</p>

            <h2>' . esc_html__('Screen Content', 'rrze-siteimprove') . '</h2>
            <ul>
                <li><strong>' . esc_html__('Siteimprove Overlay', 'rrze-siteimprove') . '</strong>: ' . esc_html__('The former overlay functionality is handled by the official Siteimprove WordPress plugin.', 'rrze-siteimprove') . '</li>
                <li><strong>' . esc_html__('Analytics', 'rrze-siteimprove') . '</strong>: ' . esc_html__('Enable Analytics, add your Siteimprove code, and use the shortcode [siteimprove_analytics_privacy_policy] to display a privacy policy with an opt-out option.', 'rrze-siteimprove') . '</li>
            </ul>

            <h2>' . esc_html__('Available Actions', 'rrze-siteimprove') . '</h2>
            <ul>
                <li>' . esc_html__('Enable or disable Analytics.', 'rrze-siteimprove') . '</li>
                <li>' . esc_html__('Add your Analytics code.', 'rrze-siteimprove') . '</li>
                <li>' . esc_html__('Save your changes.', 'rrze-siteimprove') . '</li>
            </ul>
        ',
        ];

        $helpSidebar = sprintf(
            '<p><strong>%1$s:</strong>
            </p><p><a href="%2$s">RRZE-Webworking</a></p>
            <p><a href="%3$s">%4$s</a></p>
            <p><a href="%5$s" target="_blank" rel="noopener noreferrer">%6$s</a></p>',
            esc_html__('For more information', 'rrze-siteimprove'),
            esc_url('http://blogs.fau.de/webworking'),
            esc_url('https://github.com/RRZE-Webteam/rrze-siteimprove'),
            esc_html__('RRZE Webteam on Github', 'rrze-siteimprove'),
            esc_url('https://siteimprove.com'),
            esc_html__('Visit Siteimprove', 'rrze-siteimprove')
        );

        $screen = get_current_screen();

        if ($screen->id != $this->settingsMenu) {
            return;
        }

        $screen->add_help_tab($helpTab);

        $screen->set_help_sidebar($helpSidebar);
    }

    private function getSettingsCapability(): string
    {
        return is_multisite() ? 'manage_network_options' : 'manage_options';
    }

}
