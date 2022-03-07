<?php

namespace RRZE\Siteimprove;

defined('ABSPATH') || exit;

class Settings
{
    protected $optionName;
    protected $options;

    public function __construct()
    {
        $this->optionName = settings()->getOptionName();
        $this->options = settings()->getOptions();

        add_action('admin_menu', [$this, 'settingsMenu']);
        add_action('admin_init', [$this, 'settings']);
        add_action('wp_ajax_siteimproveRequestToken', [$this, 'siteimproveRequestToken']);
    }

    /*
     * Füge eine Optionsseite in das Menü "Einstellungen" hinzu.
     * @return void
     */
    public function settingsMenu()
    {
        // In general, the required capability is "manage_options" however, for RRZE CMS purposes "manage_network_options" is used.
        $capability = is_multisite() ? 'manage_network_options' : 'manage_options';
        // Add sub menu page to the Tools main menu
        $this->settingsMenu = add_management_page(__('Siteimprove', 'rrze-siteimprove'), __('Siteimprove', 'rrze-siteimprove'), $capability, 'rrze-siteimprove', [$this, 'settingsPage']);
        // Add Contextual Help Menu
        //add_action('load-' . $this->settingsMenu, [$this, 'adminHelpMenu']);
    }

    /*
     * Die Ausgabe der Optionsseite.
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

    /*
     * Legt die Einstellungen der Optionsseite fest.
     * @return void
     */
    public function settings()
    {
        register_setting(
            'rrze_siteimprove_options',
            $this->optionName,
            [$this, 'optionsValidate']
        );

        add_settings_section(
            'rrze_siteimprove_integrationSection',
            __('Integration', 'rrze-siteimprove'),
            [$this, 'integrationSection'],
            'rrze_siteimprove_options'
        );
        add_settings_field(
            'rrze_siteimprove_integration_enable',
            __('Enable', 'rrze-siteimprove'),
            [$this, 'integrationEnableField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_integrationSection'
        );
        add_settings_field(
            'rrze_siteimprove_integration_token',
            __('Token', 'rrze-siteimprove'),
            [$this, 'integrationTokenField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_integrationSection'
        );
        add_settings_field(
            'rrze_siteimprove_integration_badge_role',
            __('Badge Role', 'rrze-siteimprove'),
            [$this, 'integrationBadgeRoleField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_integrationSection'
        );
        add_settings_section(
            'rrze_siteimprove_analytics_section',
            __('Analytics', 'rrze-siteimprove'),
            [$this, 'analytics_section'],
            'rrze_siteimprove_options'
        );
        add_settings_field(
            'rrze_siteimprove_analytics_enable',
            __('Enable', 'rrze-siteimprove'),
            [$this, 'analyticsEnableField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_analytics_section'
        );
        add_settings_field(
            'rrze_siteimprove_analytics_code',
            __('Code', 'rrze-siteimprove'),
            [$this, 'analyticsCodeField'],
            'rrze_siteimprove_options',
            'rrze_siteimprove_analytics_section'
        );
    }

    /*
     * Validiert die Eingabe der Optionsseite.
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

    public function integrationSection()
    {
        echo '<p>', __('You can scan your website for errors as soon as a page is published, allowing you to fix mistakes, optimize content, and manage your site more efficiently.', 'rrze-siteimprove'), '</p>';
    }

    public function analytics_section()
    {
        echo '<p>', __('Get insight into visitor behavior and optimize your website with powerful analytics that anyone can use.', 'rrze-siteimprove'), '</p>';
        echo '<p>', __('Use the shortcode [siteimprove_analytics_privacy_policy] to display the corresponding privacy policy with an opt-out button.', 'rrze-siteimprove'), '</p>';
    }

    /*
     * Integration-Enable-Feld der Optionsseite
     * @return void
     */
    public function integrationEnableField()
    {
        $checked = $this->options->integration_enable ? true : false;
    ?>
        <input id="siteimprove-integration-enable" type="checkbox" <?php checked($checked); ?> name="<?php printf('%s[integration_enable]', $this->optionName); ?>" value="1" />
    <?php
    }

    /*
     * Integration-Token-Feld der Optionsseite
     * @return void
     */
    public function integrationTokenField()
    {
    ?>
        <input type="text" id="siteimprove-integration-token" name="<?php printf('%s[integration_token]', $this->optionName); ?>" value="<?php echo $this->options->integration_token; ?>" maxlength="50" size="50" />
        <input class="button" id="siteimprove-integration-token-request" type="button" value="<?php _e('Request new token', 'rrze-siteimprove'); ?>" />
    <?php
    }

    /*
     * Integration-Badge-Role-Feld der Optionsseite
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

    /*
     * Analytics-Enable-Feld der Optionsseite
     * @return void
     */
    public function analyticsEnableField()
    {
        $checked = $this->options->analytics_enable ? true : false;
    ?>
        <input id="siteimprove-analytics-enable" type="checkbox" <?php checked($checked); ?> name="<?php printf('%s[analytics_enable]', $this->optionName); ?>" value="1" />
    <?php
    }

    /*
     * Analytics-Code-Feld der Optionsseite
     * @return void
     */
    public function analyticsCodeField()
    {
    ?>
        <input type="text" id="siteimprove-analytics-code" name="<?php printf('%s[analytics_code]', $this->optionName); ?>" value="<?php echo $this->options->analytics_code; ?>" />
        <p class="description"><?php _e('The code that is specific to your account.', 'rrze-siteimprove'); ?></p>
<?php
    }

    /*
     * Erstellt die Kontexthilfe der Optionsseite.
     * @return void
     */
    public function adminHelpMenu()
    {
        $content = [
            '<p>' . __('Here comes the Context Help content.', 'rrze-siteimprove') . '</p>',
        ];


        $help_tab = [
            'id' => $this->settingsMenu,
            'title' => __('Overview', 'rrze-siteimprove'),
            'content' => implode(PHP_EOL, $content),
        ];

        $help_sidebar = sprintf('<p><strong>%1$s:</strong></p><p><a href="http://blogs.fau.de/webworking">RRZE-Webworking</a></p><p><a href="https://github.com/RRZE-Webteam">%2$s</a></p>', __('For more information', 'rrze-siteimprove'), __('RRZE Webteam on Github', 'rrze-siteimprove'));

        $screen = get_current_screen();

        if ($screen->id != $this->settingsMenu) {
            return;
        }

        $screen->add_help_tab($help_tab);

        $screen->set_help_sidebar($help_sidebar);
    }

    /**
     * Return new token for ajax requests.
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
