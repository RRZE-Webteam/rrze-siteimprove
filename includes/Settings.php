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
    protected $menuSlug = 'rrze-siteimprove';

    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct()
    {
        $this->optionName = Options::getOptionName();
        $this->options = Options::getOptions();

        add_action('admin_menu', [$this, 'settingsMenu']);
        add_action('admin_init', [$this, 'settings']);
        add_action('wp_ajax_siteimproveRequestToken', [$this, 'siteimproveRequestToken']);
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
        // In general, the required capability is "manage_options" however, for RRZE CMS purposes "manage_network_options" is used.
        $capability = is_multisite() ? 'manage_network_options' : 'manage_options';

        // Add sub menu page to the Tools main menu
        $this->settingsMenu = add_management_page(
            __('Siteimprove', 'rrze-siteimprove'),
            __('Siteimprove', 'rrze-siteimprove'),
            $capability,
            'rrze-siteimprove',
            [$this, 'settingsPage']
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
        <div class="wrap">
            <h2><?php echo __('Siteimprove', 'rrze-siteimprove'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('rrze_siteimprove_options');
                do_settings_sections('rrze_siteimprove_options');
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
            'rrze_siteimprove_options',
            $this->optionName,
            [$this, 'optionsValidate']
        );

        // Add Integration section
        add_settings_section(
            'rrze_siteimprove_integrationSection',
            __('Integration', 'rrze-siteimprove'),
            [$this, 'integrationSection'],
            'rrze_siteimprove_options'
        );

        // Add Integration Enable Field
        add_settings_field(
            'rrze_siteimprove_integration_enable',
            __('Enable', 'rrze-siteimprove'),
            [$this, 'integrationEnableField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_integrationSection'
        );

        // Add Integration Token Field
        add_settings_field(
            'rrze_siteimprove_integration_token',
            __('Token', 'rrze-siteimprove'),
            [$this, 'integrationTokenField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_integrationSection'
        );

        // Add Badge Role Field
        add_settings_field(
            'rrze_siteimprove_integration_badge_role',
            __('Badge Role', 'rrze-siteimprove'),
            [$this, 'integrationBadgeRoleField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_integrationSection'
        );

        // Add Analytics section
        add_settings_section(
            'rrze_siteimprove_analytics_section',
            __('Analytics', 'rrze-siteimprove'),
            [$this, 'analyticsSection'],
            'rrze_siteimprove_options'
        );

        // Analytics Enable Field
        add_settings_field(
            'rrze_siteimprove_analytics_enable',
            __('Enable', 'rrze-siteimprove'),
            [$this, 'analyticsEnableField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_analytics_section'
        );

        // Analytics Code Field
        add_settings_field(
            'rrze_siteimprove_analytics_code',
            __('Code', 'rrze-siteimprove'),
            [$this, 'analyticsCodeField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_analytics_section'
        );
    }

    /**
     * Validate and sanitize the options input.
     * 
     * This method processes the input from the settings form, ensuring that the values are valid and sanitized.
     * It handles enabling/disabling features, setting tokens, and validating user roles.
     * 
     * @param array $input
     * @return array
     */
    public function optionsValidate($input)
    {
        $input['integration_enable'] = !empty($input['integration_enable']) ? 1 : 0;

        $input['integration_token'] = !empty($input['integration_token']) ? $input['integration_token'] : '';
        if (!$input['integration_token'] || !$this->options->integration_token) {
            $token = Auth::requestToken();
            if ($token !== false) {
                $input['integration_token'] = $token;
            }
        }

        $roles = array_reverse(get_editable_roles());
        $input['integration_badge_role'] = isset($roles[$input['integration_badge_role']]) ? $input['integration_badge_role'] : 'administrator';

        $input['analytics_enable'] = !empty($input['analytics_enable']) ? 1 : 0;

        $input['analytics_code'] = !empty($input['analytics_code']) ? $input['analytics_code'] : '';

        return $input;
    }

    /**
     * Integration section description.
     * 
     * This method outputs a description for the integration section on the settings page.
     * It explains the purpose of the Siteimprove integration and how it can help manage website content.
     * 
     * @return void
     */
    public function integrationSection()
    {
        echo '<p>', __('You can scan your website for errors as soon as a page is published, allowing you to fix mistakes, optimize content, and manage your site more efficiently.', 'rrze-siteimprove'), '</p>';
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
        echo '<p>', __('Get insight into visitor behavior and optimize your website with powerful analytics that anyone can use.', 'rrze-siteimprove'), '</p>';
        echo '<p>', __('Use the shortcode [siteimprove_analytics_privacy_policy] to display the corresponding privacy policy with an opt-out button.', 'rrze-siteimprove'), '</p>';
    }

    /**
     * Integration Enable Field
     * 
     * This method outputs the checkbox for enabling the Siteimprove integration.
     * It allows users to enable or disable the integration with Siteimprove.
     * 
     * @return void
     */
    public function integrationEnableField()
    {
        $checked = $this->options->integration_enable ? true : false;
    ?>
        <input id="siteimprove-integration-enable" type="checkbox" <?php checked($checked); ?> name="<?php printf('%s[integration_enable]', $this->optionName); ?>" value="1" />
    <?php
    }

    /**
     * Integration Token Field
     * 
     * This method outputs the input field for the Siteimprove integration token.
     * It allows users to enter their specific integration token for Siteimprove.
     * 
     * @return void
     */
    public function integrationTokenField()
    {
    ?>
        <input type="text" id="siteimprove-integration-token" name="<?php printf('%s[integration_token]', $this->optionName); ?>" value="<?php echo $this->options->integration_token; ?>" maxlength="50" size="50" />
        <input class="button" id="siteimprove-integration-token-request" type="button" value="<?php _e('Request new token', 'rrze-siteimprove'); ?>" />
    <?php
    }

    /**
     * Integration Badge Role Field
     * 
     * This method outputs a dropdown for selecting the user role that can see the Siteimprove badge.
     * It allows administrators to control which users can access the Siteimprove features.
     * 
     * @return void
     */
    public function integrationBadgeRoleField()
    {
    ?>
        <select name="<?php printf('%s[integration_badge_role]', $this->optionName); ?>">
            <?php wp_dropdown_roles($this->options->integration_badge_role); ?>
        </select>
        <p class="description"><?php _e('User role required to display the Siteimprove Badge.', 'rrze-siteimprove'); ?></p>
    <?php
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
        <input id="siteimprove-analytics-enable" type="checkbox" <?php checked($checked); ?> name="<?php printf('%s[analytics_enable]', $this->optionName); ?>" value="1" />
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
    ?>
        <input type="text" id="siteimprove-analytics-code" name="<?php printf('%s[analytics_code]', $this->optionName); ?>" value="<?php echo $this->options->analytics_code; ?>" />
        <p class="description"><?php _e('The code that is specific to your account.', 'rrze-siteimprove'); ?></p>
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
            <h2>' . __('Siteimprove Help', 'rrze-siteimprove') . '</h2>
            <p>' . __('The Siteimprove plugin integrates your WordPress site with your Siteimprove account. It lets you scan content for errors, monitor quality and accessibility, and optionally enable Siteimprove Analytics.', 'rrze-siteimprove') . '</p>

            <h2>' . __('Screen Content', 'rrze-siteimprove') . '</h2>
            <ul>
                <li><strong>' . __('Integration', 'rrze-siteimprove') . '</strong>: ' . __('Enable Siteimprove, enter your token, and choose which user role can see the Siteimprove badge.', 'rrze-siteimprove') . '</li>
                <li><strong>' . __('Analytics', 'rrze-siteimprove') . '</strong>: ' . __('Enable Analytics, add your Siteimprove code, and use the shortcode [siteimprove_analytics_privacy_policy] to display a privacy policy with an opt-out option.', 'rrze-siteimprove') . '</li>
            </ul>

            <h2>' . __('Available Actions', 'rrze-siteimprove') . '</h2>
            <ul>
                <li>' . __('Enable or disable the Siteimprove integration.', 'rrze-siteimprove') . '</li>
                <li>' . __('Request a new token if needed.', 'rrze-siteimprove') . '</li>
                <li>' . __('Choose the minimum role for the badge.', 'rrze-siteimprove') . '</li>
                <li>' . __('Enable or disable Analytics.', 'rrze-siteimprove') . '</li>
                <li>' . __('Add your Analytics code.', 'rrze-siteimprove') . '</li>
                <li>' . __('Save your changes.', 'rrze-siteimprove') . '</li>
            </ul>
        ',
        ];

        $helpSidebar = sprintf(
            '<p><strong>%1$s:</strong>
            </p><p><a href="http://blogs.fau.de/webworking">RRZE-Webworking</a></p>
            <p><a href="https://github.com/RRZE-Webteam/rrze-siteimprove">%2$s</a></p>
            <p><a href="https://siteimprove.com" target="_blank">%3$s</a></p>',
            __('For more information', 'rrze-siteimprove'),
            __('RRZE Webteam on Github', 'rrze-siteimprove'),
            __('Visit Siteimprove', 'rrze-siteimprove')
        );

        $screen = get_current_screen();

        if ($screen->id != $this->settingsMenu) {
            return;
        }

        $screen->add_help_tab($helpTab);

        $screen->set_help_sidebar($helpSidebar);
    }

    /**
     * Request a new Siteimprove token via AJAX.
     * 
     * This method checks if the user has the required capability to manage options,
     * and if so, it calls the Auth::requestToken() method to get a new token.
     * 
     * @return void
     */
    public function siteimproveRequestToken()
    {
        // Check access.
        if (!current_user_can('manage_options')) {
            return;
        }
        echo Auth::requestToken();
        wp_die();
    }
}
