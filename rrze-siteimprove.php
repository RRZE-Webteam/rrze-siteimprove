<?php

/**
 * Plugin Name:     RRZE Siteimprove
 * Plugin URI:      https://github.com/RRZE-Webteam/rrze-siteimprove
 * Description:     Integration mit Siteimprove.
 * Version:         1.5.1
 * Author:          RRZE-Webteam
 * Author URI:      https://www.rrze,fau.de
 * License:         GNU General Public License v2
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:     /languages
 * Text Domain:     rrze-siteimprove
 */

namespace RRZE\Siteimprove;

use RRZE\Siteimprove\Main;

defined('ABSPATH') || exit;

const RRZE_PHP_VERSION = '7.1';
const RRZE_WP_VERSION = '5.0';

// Automatische Laden von Klassen.
require_once 'autoload.php';

register_activation_hook(__FILE__, 'RRZE\Siteimprove\activation');
register_deactivation_hook(__FILE__, 'RRZE\Siteimprove\deactivation');

add_action('plugins_loaded', 'RRZE\Siteimprove\loaded');

/*
 * Einbindung der Sprachdateien.
 * @return void
 */
function load_textdomain()
{
    load_plugin_textdomain('rrze-siteimprove', false, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
}

/*
 * Wird durchgeführt, nachdem das Plugin aktiviert wurde.
 * @return void
 */
function activation()
{
    // Sprachdateien werden eingebunden.
    load_textdomain();

    // Überprüft die minimal erforderliche PHP- u. WP-Version.
    system_requirements();
}

/*
 * Wird durchgeführt, nachdem das Plugin deaktiviert wurde.
 * @return void
 */
function deactivation()
{
}

/*
 * Überprüft die minimal erforderliche PHP- u. WP-Version.
 * @return void
 */
function system_requirements()
{
    $error = '';

    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        $error = sprintf(__('Your server is running PHP version %s. Please upgrade at least to PHP version %s.', 'rrze-siteimprove'), PHP_VERSION, RRZE_PHP_VERSION);
    }

    if (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        $error = sprintf(__('Your Wordpress version is %s. Please upgrade at least to Wordpress version %s.', 'rrze-siteimprove'), $GLOBALS['wp_version'], RRZE_WP_VERSION);
    }

    // Wenn die Überprüfung fehlschlägt, dann wird das Plugin automatisch deaktiviert.
    if (!empty($error)) {
        deactivate_plugins(plugin_basename(__FILE__), false, true);
        wp_die($error);
    }
}

/*
 * Wird durchgeführt, nachdem das WP-Grundsystem hochgefahren
 * und alle Plugins eingebunden wurden.
 * @return void
 */
function loaded()
{
    // Sprachdateien werden eingebunden.
    load_textdomain();
    // Hauptklasse (Main) wird instanziiert.
    return new Main(__FILE__);
}
