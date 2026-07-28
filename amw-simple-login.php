<?php
/**
 * Plugin Name: AMW Simple Login
 * Plugin URI:  https://alvaromarquezweb.com
 * Description: Customises the WordPress login screen. Configurable from Settings → AMW Login.
 * Version:     1.3.0
 * Author:      Álvaro Márquez Díaz
 * Author URI:  https://alvaromarquezweb.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: amw-simple-login
 * Domain Path: /languages
 */

// ── SELF-UPDATE FROM GITHUB ─────────────────────────────────────────
require plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$amwUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/alvaromarquezweb/amw-simple-login/',
    __FILE__,
    'amw-simple-login'
);
$amwUpdateChecker->setBranch( 'main' );

// Public repository: PUC needs no authentication (no setAuthentication).

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Single source of truth: read the version from the plugin header above, so the
// constant can never drift from it. To release, bump ONLY the header line.
define( 'AMW_LOGIN_VERSION', get_file_data( __FILE__, [ 'Version' => 'Version' ] )['Version'] ?: '0.0.0' );
define( 'AMW_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMW_LOGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AMW_LOGIN_BASENAME', plugin_basename( __FILE__ ) );

// Logo width in px. Injected as a CSS variable, so changing it here is enough.
define( 'AMW_LOGIN_LOGO_WIDTH', 160 );


// ─────────────────────────────────────────────────────────────
// 0. TEXT DOMAIN
// ─────────────────────────────────────────────────────────────

add_action( 'init', 'amw_login_load_textdomain' );

function amw_login_load_textdomain() {
    load_plugin_textdomain(
        'amw-simple-login',
        false,
        dirname( AMW_LOGIN_BASENAME ) . '/languages'
    );
}

// ── Modules ─────────────────────────────────────────────────────────
require_once AMW_LOGIN_DIR . 'includes/options.php';
require_once AMW_LOGIN_DIR . 'includes/login.php';
require_once AMW_LOGIN_DIR . 'includes/settings-page.php';
