<?php
/**
 * Plugin Name: AMW Simple Login
 * Plugin URI:  https://alvaromarquezweb.com
 * Description: Customises the WordPress login screen. Configurable from Settings → AMW Login.
 * Version:     1.1.0
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

define( 'AMW_LOGIN_VERSION', '1.1.0' );
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


// ─────────────────────────────────────────────────────────────
// 1. OPTIONS (always available, no hooks)
// ─────────────────────────────────────────────────────────────

function amw_login_defaults() {
    return [
        'color_bg'          => '#0a0a0a',
        'color_surface'     => '#141414',
        'color_surface_alt' => '#1c1c1c',
        'color_input_bg'    => '#1c1c1c',
        'color_border'      => '#2a2a2a',
        'color_text'        => '#fafafa',
        'color_muted'       => '#888888',
        'color_accent'      => '#fafafa',
        'color_accent_fg'   => '#0a0a0a',
        'radius'            => 8,
        'hide_backtoblog'   => '0',
        'hide_forgotpw'     => '0',
        'show_legal'        => '0',
        'legal_aviso'            => '/aviso-legal/',
        'legal_aviso_label'      => 'Aviso Legal',
        'legal_privacidad'       => '/politica-de-privacidad/',
        'legal_privacidad_label' => 'Política de Privacidad',
        'legal_cookies'          => '/politica-de-cookies/',
        'legal_cookies_label'    => 'Política de Cookies',
        'logo_id'                => '',
        'bg_id'                  => '',
        'layout'                 => 'left', // left | center | right (only applies when an image is set)
        'blur'                   => 0,      // image blur in px (0-20)
        'overlay_color'          => '#0a0a0a',
        'overlay_opacity'        => 55,     // darkening intensity 0-100
        // New in 1.1.0
        'no_image_bg'            => 'none', // none | solid | gradient (only when NO image is set)
        'solid_color'            => '#0a0a0a',
        'grad_start'             => '#0a0a0a',
        'grad_end'               => '#1c1c1c',
        'grad_angle'             => 135,    // 0-360
        'heading_text'           => '',     // optional text shown above the form
        'custom_css'             => '',      // extra CSS injected on the login screen
    ];
}

function amw_login_color_keys() {
    return [
        'color_bg'          => __( 'Page background', 'amw-simple-login' ),
        'color_surface'     => __( 'Form background', 'amw-simple-login' ),
        'color_surface_alt' => __( 'Notices and messages background', 'amw-simple-login' ),
        'color_input_bg'    => __( 'Fields background', 'amw-simple-login' ),
        'color_border'      => __( 'Border color', 'amw-simple-login' ),
        'color_text'        => __( 'Primary text', 'amw-simple-login' ),
        'color_muted'       => __( 'Secondary text / labels', 'amw-simple-login' ),
        'color_accent'      => __( 'Button (background)', 'amw-simple-login' ),
        'color_accent_fg'   => __( 'Button (text)', 'amw-simple-login' ),
    ];
}

/**
 * Colour palette presets exposed as one-click buttons in the settings page.
 * Each preset provides the nine palette colours; applied client-side.
 */
function amw_login_presets() {
    return [
        'dark' => [
            'label'  => __( 'Dark', 'amw-simple-login' ),
            'colors' => [
                'color_bg' => '#0a0a0a', 'color_surface' => '#141414', 'color_surface_alt' => '#1c1c1c',
                'color_input_bg' => '#1c1c1c', 'color_border' => '#2a2a2a', 'color_text' => '#fafafa',
                'color_muted' => '#888888', 'color_accent' => '#fafafa', 'color_accent_fg' => '#0a0a0a',
            ],
        ],
        'light' => [
            'label'  => __( 'Light', 'amw-simple-login' ),
            'colors' => [
                'color_bg' => '#f5f5f5', 'color_surface' => '#ffffff', 'color_surface_alt' => '#f0f0f0',
                'color_input_bg' => '#ffffff', 'color_border' => '#dcdcdc', 'color_text' => '#1a1a1a',
                'color_muted' => '#666666', 'color_accent' => '#1a1a1a', 'color_accent_fg' => '#ffffff',
            ],
        ],
        'slate' => [
            'label'  => __( 'Slate', 'amw-simple-login' ),
            'colors' => [
                'color_bg' => '#0f172a', 'color_surface' => '#1e293b', 'color_surface_alt' => '#334155',
                'color_input_bg' => '#1e293b', 'color_border' => '#334155', 'color_text' => '#f1f5f9',
                'color_muted' => '#94a3b8', 'color_accent' => '#38bdf8', 'color_accent_fg' => '#0f172a',
            ],
        ],
    ];
}

function amw_login_get_options() {
    return wp_parse_args( get_option( 'amw_login_options', [] ), amw_login_defaults() );
}

/**
 * Sanitises the extra CSS field. Admin-only (manage_options), so the goal is
 * only to keep it from breaking out of its own <style> block, not to police
 * the CSS itself. Preserves ">" and "<" so selectors keep working.
 */
function amw_login_sanitize_css( $css ) {
    $css = wp_unslash( (string) $css );
    $css = preg_replace( '#</?\s*style[^>]*>#i', '', $css );
    $css = preg_replace( '#<\s*/?\s*script[^>]*>#i', '', $css );
    return trim( $css );
}

function amw_login_sanitize_options( $input ) {
    $defaults = amw_login_defaults();
    $output   = [];

    foreach ( array_keys( amw_login_color_keys() ) as $key ) {
        $val            = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
        $output[ $key ] = $val ?: $defaults[ $key ];
    }

    // Corner radius: bounded integer, never an arbitrary value in the CSS.
    $output['radius'] = isset( $input['radius'] )
        ? max( 0, min( 24, absint( $input['radius'] ) ) )
        : $defaults['radius'];

    foreach ( [ 'hide_backtoblog', 'hide_forgotpw', 'show_legal' ] as $key ) {
        $output[ $key ] = ! empty( $input[ $key ] ) ? '1' : '0';
    }

    foreach ( [ 'legal_aviso', 'legal_privacidad', 'legal_cookies' ] as $key ) {
        $output[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( $input[ $key ] ) : $defaults[ $key ];
    }

    foreach ( [ 'legal_aviso_label', 'legal_privacidad_label', 'legal_cookies_label' ] as $key ) {
        $output[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : $defaults[ $key ];
    }

    $output['logo_id'] = ! empty( $input['logo_id'] ) ? absint( $input['logo_id'] ) : '';
    $output['bg_id']   = ! empty( $input['bg_id'] ) ? absint( $input['bg_id'] ) : '';

    $layout           = isset( $input['layout'] ) ? sanitize_key( $input['layout'] ) : '';
    $output['layout'] = in_array( $layout, [ 'left', 'center', 'right' ], true ) ? $layout : $defaults['layout'];

    $output['blur'] = isset( $input['blur'] ) ? max( 0, min( 20, absint( $input['blur'] ) ) ) : $defaults['blur'];

    $ov_color                  = isset( $input['overlay_color'] ) ? sanitize_hex_color( $input['overlay_color'] ) : '';
    $output['overlay_color']   = $ov_color ?: $defaults['overlay_color'];
    $output['overlay_opacity'] = isset( $input['overlay_opacity'] ) ? max( 0, min( 100, absint( $input['overlay_opacity'] ) ) ) : $defaults['overlay_opacity'];

    // ── New in 1.1.0 ──
    $no_bg                 = isset( $input['no_image_bg'] ) ? sanitize_key( $input['no_image_bg'] ) : '';
    $output['no_image_bg'] = in_array( $no_bg, [ 'none', 'solid', 'gradient' ], true ) ? $no_bg : $defaults['no_image_bg'];

    foreach ( [ 'solid_color', 'grad_start', 'grad_end' ] as $key ) {
        $val            = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
        $output[ $key ] = $val ?: $defaults[ $key ];
    }

    $output['grad_angle']   = isset( $input['grad_angle'] ) ? max( 0, min( 360, absint( $input['grad_angle'] ) ) ) : $defaults['grad_angle'];
    $output['heading_text'] = isset( $input['heading_text'] ) ? sanitize_text_field( $input['heading_text'] ) : $defaults['heading_text'];
    $output['custom_css']   = isset( $input['custom_css'] ) ? amw_login_sanitize_css( $input['custom_css'] ) : $defaults['custom_css'];

    return $output;
}

/**
 * Returns a colour ready to print inside <style>.
 * sanitize_hex_color is the correct escaping in a CSS context (esc_attr is not).
 */
function amw_login_css_color( $opts, $key ) {
    $defaults = amw_login_defaults();
    $color    = sanitize_hex_color( isset( $opts[ $key ] ) ? $opts[ $key ] : '' );
    return $color ?: $defaults[ $key ];
}

/**
 * Resolves the login logo and its dimensions.
 * Priority: 1) plugin option  2) Customizer  2b) Divi logo  3) inline fallback SVG.
 */
function amw_login_logo_data() {
    $width  = (int) AMW_LOGIN_LOGO_WIDTH;
    $data   = [ 'url' => '', 'width' => $width, 'height' => 60 ];
    $opts   = amw_login_get_options();

    $calc_height = function( $w, $h ) use ( $width ) {
        if ( $w <= 0 || $h <= 0 ) {
            return 60;
        }
        return max( 40, min( (int) round( $width * ( $h / $w ) ), 100 ) );
    };

    // 1. Logo configured in the plugin.
    if ( ! empty( $opts['logo_id'] ) ) {
        $src = wp_get_attachment_image_src( (int) $opts['logo_id'], 'full' );
        if ( $src ) {
            $data['url']    = $src[0];
            $data['height'] = $calc_height( $src[1], $src[2] );
            return $data;
        }
    }

    // 2. Customizer logo (Site Identity).
    $theme_logo_id = get_theme_mod( 'custom_logo' );
    if ( $theme_logo_id ) {
        $src = wp_get_attachment_image_src( $theme_logo_id, 'full' );
        if ( $src ) {
            $data['url']    = $src[0];
            $data['height'] = $calc_height( $src[1], $src[2] );
            return $data;
        }
    }

    // 2b. Divi logo (Theme Options). Divi does not use the core custom_logo:
    //     it stores the logo URL in the et_divi['divi_logo'] option.
    $divi_logo = '';
    if ( function_exists( 'et_get_option' ) ) {
        $divi_logo = et_get_option( 'divi_logo' );
    } else {
        $et_divi = get_option( 'et_divi' );
        if ( is_array( $et_divi ) && ! empty( $et_divi['divi_logo'] ) ) {
            $divi_logo = $et_divi['divi_logo'];
        }
    }
    if ( ! empty( $divi_logo ) ) {
        $data['url'] = $divi_logo;
        // It is a URL; if it lives in the media library we read the real
        // dimensions to compute the height, otherwise we keep the default.
        $att_id = attachment_url_to_postid( $divi_logo );
        if ( $att_id ) {
            $src = wp_get_attachment_image_src( $att_id, 'full' );
            if ( $src ) {
                $data['height'] = $calc_height( $src[1], $src[2] );
            }
        }
        return $data;
    }

    // 3. Fallback: inline SVG logo (no files, no assets folder).
    $data['url']    = amw_login_fallback_logo_uri();
    $data['height'] = $calc_height( 120, 60 ); // matches the SVG viewBox ratio

    return $data;
}

/**
 * Fallback logo as an inline SVG (data URI). No files, no assets folder.
 * Takes the configured text colour, so it adapts to the theme palette.
 * To use your own logo, replace the contents of $svg with your SVG
 * (exported from Affinity); use $color on the "fill" values you want to follow the theme.
 */
function amw_login_fallback_logo_uri() {
    $color = amw_login_css_color( amw_login_get_options(), 'color_text' );

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 60" role="img" aria-label="Logo">'
         . '<text x="60" y="43" text-anchor="middle" font-family="system-ui,-apple-system,Segoe UI,Roboto,sans-serif"'
         . ' font-size="34" font-weight="700" letter-spacing="1" fill="' . $color . '">AMW</text>'
         . '</svg>';

    return 'data:image/svg+xml;base64,' . base64_encode( $svg );
}

/**
 * Background image URL, or '' when none is configured.
 */
function amw_login_bg_url() {
    $opts = amw_login_get_options();
    if ( empty( $opts['bg_id'] ) ) {
        return '';
    }
    $src = wp_get_attachment_image_src( (int) $opts['bg_id'], 'full' );
    return $src ? $src[0] : '';
}


// ─────────────────────────────────────────────────────────────
// 2. SETTINGS SCREEN (dashboard only)
// ─────────────────────────────────────────────────────────────

if ( is_admin() ) {
    add_action( 'admin_menu', 'amw_login_menu' );
    add_action( 'admin_init', 'amw_login_register_settings' );
    add_action( 'admin_enqueue_scripts', 'amw_login_admin_scripts' );
    add_action( 'admin_post_amw_login_import', 'amw_login_handle_import' );
    add_action( 'admin_notices', 'amw_login_import_notice' );
    add_filter( 'plugin_action_links_' . AMW_LOGIN_BASENAME, 'amw_login_action_links' );
}

/**
 * Adds a "Settings" link on the plugins list row.
 */
function amw_login_action_links( $links ) {
    $url  = admin_url( 'options-general.php?page=amw-login-settings' );
    $link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'amw-simple-login' ) . '</a>';
    array_unshift( $links, $link );
    return $links;
}

function amw_login_menu() {
    add_options_page(
        __( 'AMW Login', 'amw-simple-login' ),
        __( 'AMW Login', 'amw-simple-login' ),
        'manage_options',
        'amw-login-settings',
        'amw_login_settings_page'
    );
}

function amw_login_register_settings() {
    register_setting( 'amw_login_group', 'amw_login_options', [
        'sanitize_callback' => 'amw_login_sanitize_options',
        'default'           => amw_login_defaults(),
    ] );
}

function amw_login_admin_scripts( $hook ) {
    if ( 'settings_page_amw-login-settings' !== $hook ) {
        return;
    }
    wp_enqueue_media();
}

/**
 * Handles the settings import (JSON) submitted to admin-post.php.
 */
function amw_login_handle_import() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You are not allowed to do this.', 'amw-simple-login' ) );
    }
    check_admin_referer( 'amw_login_import' );

    $raw     = isset( $_POST['amw_login_import_json'] ) ? wp_unslash( $_POST['amw_login_import_json'] ) : '';
    $decoded = json_decode( $raw, true );
    $status  = 'error';

    if ( is_array( $decoded ) ) {
        update_option( 'amw_login_options', amw_login_sanitize_options( $decoded ) );
        $status = 'imported';
    }

    wp_safe_redirect( add_query_arg(
        'amw_import',
        $status,
        admin_url( 'options-general.php?page=amw-login-settings' )
    ) );
    exit;
}

function amw_login_import_notice() {
    if ( empty( $_GET['amw_import'] ) || 'settings_page_amw-login-settings' !== get_current_screen()->id ) {
        return;
    }
    if ( 'imported' === $_GET['amw_import'] ) {
        echo '<div class="notice notice-success is-dismissible"><p>'
            . esc_html__( 'Settings imported successfully.', 'amw-simple-login' )
            . '</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>'
            . esc_html__( 'Import failed: the pasted text is not valid JSON.', 'amw-simple-login' )
            . '</p></div>';
    }
}

function amw_login_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $opts     = amw_login_get_options();
    $logo_id  = ! empty( $opts['logo_id'] ) ? (int) $opts['logo_id'] : 0;
    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
    $bg_id    = ! empty( $opts['bg_id'] ) ? (int) $opts['bg_id'] : 0;
    $bg_url   = $bg_id ? wp_get_attachment_image_url( $bg_id, 'medium' ) : '';
    $presets  = amw_login_presets();

    // Strings for the script: computed here so they are truly translatable
    // and escaped with esc_js(), which is correct in a JavaScript context.
    $i18n = [
        'select'      => __( 'Select logo', 'amw-simple-login' ),
        'change'      => __( 'Change logo', 'amw-simple-login' ),
        'remove'      => __( 'Remove', 'amw-simple-login' ),
        'frame_title' => __( 'Select logo', 'amw-simple-login' ),
        'frame_btn'   => __( 'Use this logo', 'amw-simple-login' ),
    ];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'AMW Simple Login', 'amw-simple-login' ); ?></h1>
        <p>
            <a href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" rel="noopener">
                <?php esc_html_e( 'Open the login screen', 'amw-simple-login' ); ?>
            </a>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields( 'amw_login_group' ); ?>

            <h2><?php esc_html_e( 'Logo', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Login logo', 'amw-simple-login' ); ?></th>
                    <td>
                        <input type="hidden" id="amw_logo_id" name="amw_login_options[logo_id]" value="<?php echo esc_attr( $logo_id ?: '' ); ?>">

                        <div id="amw-logo-preview" style="margin-bottom:10px;">
                            <?php if ( $logo_url ) : ?>
                                <img src="<?php echo esc_url( $logo_url ); ?>" style="max-width:200px; max-height:80px; display:block; background:#111; padding:8px; border-radius:4px;">
                            <?php endif; ?>
                        </div>

                        <button type="button" class="button" id="amw-logo-upload">
                            <?php echo esc_html( $logo_id ? $i18n['change'] : $i18n['select'] ); ?>
                        </button>
                        <?php if ( $logo_id ) : ?>
                            <button type="button" class="button" id="amw-logo-remove" style="margin-left:6px;">
                                <?php echo esc_html( $i18n['remove'] ); ?>
                            </button>
                        <?php endif; ?>

                        <p class="description" style="margin-top:8px;">
                            <?php esc_html_e( 'If you do not select a logo, the Customizer logo (Site Identity) is used. If there is none either, the plugin fallback logo is shown.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <script>
            jQuery(function($) {
                var frame;

                $('#amw-logo-upload').on('click', function(e) {
                    e.preventDefault();
                    if ( frame ) { frame.open(); return; }
                    frame = wp.media({
                        title: '<?php echo esc_js( $i18n['frame_title'] ); ?>',
                        button: { text: '<?php echo esc_js( $i18n['frame_btn'] ); ?>' },
                        multiple: false,
                        library: { type: 'image' }
                    });
                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        $('#amw_logo_id').val(attachment.id);
                        $('#amw-logo-preview').html(
                            $('<img>').attr('src', attachment.url).attr(
                                'style',
                                'max-width:200px;max-height:80px;display:block;background:#111;padding:8px;border-radius:4px;'
                            )
                        );
                        $('#amw-logo-upload').text('<?php echo esc_js( $i18n['change'] ); ?>');
                        if ( ! $('#amw-logo-remove').length ) {
                            $('#amw-logo-upload').after(
                                '<button type="button" class="button" id="amw-logo-remove" style="margin-left:6px;"><?php echo esc_js( $i18n['remove'] ); ?></button>'
                            );
                        }
                    });
                    frame.open();
                });

                // Delegated: works even if the button is created later.
                $(document).on('click', '#amw-logo-remove', function(e) {
                    e.preventDefault();
                    $('#amw_logo_id').val('');
                    $('#amw-logo-preview').empty();
                    $('#amw-logo-upload').text('<?php echo esc_js( $i18n['select'] ); ?>');
                    $(this).remove();
                });
            });
            </script>

            <h2><?php esc_html_e( 'Background image', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Image', 'amw-simple-login' ); ?></th>
                    <td>
                        <input type="hidden" id="amw_bg_id" name="amw_login_options[bg_id]" value="<?php echo esc_attr( $bg_id ?: '' ); ?>">

                        <div id="amw-bg-preview" style="margin-bottom:10px;">
                            <?php if ( $bg_url ) : ?>
                                <img src="<?php echo esc_url( $bg_url ); ?>" style="max-width:280px; max-height:120px; display:block; border-radius:4px;">
                            <?php endif; ?>
                        </div>

                        <button type="button" class="button" id="amw-bg-upload">
                            <?php echo esc_html( $bg_id ? __( 'Change image', 'amw-simple-login' ) : __( 'Select image', 'amw-simple-login' ) ); ?>
                        </button>
                        <?php if ( $bg_id ) : ?>
                            <button type="button" class="button" id="amw-bg-remove" style="margin-left:6px;">
                                <?php esc_html_e( 'Remove', 'amw-simple-login' ); ?>
                            </button>
                        <?php endif; ?>

                        <p class="description" style="margin-top:8px;">
                            <?php esc_html_e( 'With no image, the login uses the "No image" style below. Use a light image: the login screen is not cached and loads fully on every visit.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Form position', 'amw-simple-login' ); ?></th>
                    <td>
                        <?php
                        $layouts = [
                            'left'   => __( 'Left (image on the right)', 'amw-simple-login' ),
                            'center' => __( 'Center (full-screen image)', 'amw-simple-login' ),
                            'right'  => __( 'Right (image on the left)', 'amw-simple-login' ),
                        ];
                        foreach ( $layouts as $val => $label ) : ?>
                            <label style="display:block; margin-bottom:4px;">
                                <input type="radio" name="amw_login_options[layout]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $opts['layout'], $val ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">
                            <?php esc_html_e( 'Only applies when an image is set. On mobile the form is always centered over the image.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="amw_blur"><?php esc_html_e( 'Image blur', 'amw-simple-login' ); ?></label>
                    </th>
                    <td>
                        <input
                            type="range"
                            id="amw_blur"
                            name="amw_login_options[blur]"
                            min="0"
                            max="20"
                            step="1"
                            value="<?php echo esc_attr( (int) $opts['blur'] ); ?>"
                            style="vertical-align:middle; width:220px;"
                            oninput="document.getElementById('amw_blur_val').textContent = this.value + ' px';"
                        >
                        <code id="amw_blur_val" style="margin-left:8px;"><?php echo esc_html( (int) $opts['blur'] ); ?> px</code>
                        <p class="description">
                            <?php esc_html_e( 'From 0 (no blur) to 20. Combined with the darkening it gives a frosted-glass effect and improves legibility over busy photos.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="amw_overlay_color"><?php esc_html_e( 'Darkening color', 'amw-simple-login' ); ?></label>
                    </th>
                    <td>
                        <input type="color" id="amw_overlay_color" name="amw_login_options[overlay_color]" value="<?php echo esc_attr( $opts['overlay_color'] ); ?>">
                        <code style="margin-left:8px;"><?php echo esc_html( $opts['overlay_color'] ); ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="amw_overlay_opacity"><?php esc_html_e( 'Darkening intensity', 'amw-simple-login' ); ?></label>
                    </th>
                    <td>
                        <input
                            type="range"
                            id="amw_overlay_opacity"
                            name="amw_login_options[overlay_opacity]"
                            min="0"
                            max="100"
                            step="1"
                            value="<?php echo esc_attr( (int) $opts['overlay_opacity'] ); ?>"
                            style="vertical-align:middle; width:220px;"
                            oninput="document.getElementById('amw_overlay_val').textContent = this.value + ' %';"
                        >
                        <code id="amw_overlay_val" style="margin-left:8px;"><?php echo esc_html( (int) $opts['overlay_opacity'] ); ?> %</code>
                        <p class="description">
                            <?php esc_html_e( 'Color layer over the image. Raise the intensity to darken the photo; the color can be your brand tone instead of black.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <script>
            jQuery(function($) {
                var bgFrame;

                $('#amw-bg-upload').on('click', function(e) {
                    e.preventDefault();
                    if ( bgFrame ) { bgFrame.open(); return; }
                    bgFrame = wp.media({
                        title: '<?php echo esc_js( __( 'Select background image', 'amw-simple-login' ) ); ?>',
                        button: { text: '<?php echo esc_js( __( 'Use this image', 'amw-simple-login' ) ); ?>' },
                        multiple: false,
                        library: { type: 'image' }
                    });
                    bgFrame.on('select', function() {
                        var attachment = bgFrame.state().get('selection').first().toJSON();
                        $('#amw_bg_id').val(attachment.id);
                        $('#amw-bg-preview').html(
                            $('<img>').attr('src', attachment.url).attr(
                                'style',
                                'max-width:280px;max-height:120px;display:block;border-radius:4px;'
                            )
                        );
                        $('#amw-bg-upload').text('<?php echo esc_js( __( 'Change image', 'amw-simple-login' ) ); ?>');
                        if ( ! $('#amw-bg-remove').length ) {
                            $('#amw-bg-upload').after(
                                '<button type="button" class="button" id="amw-bg-remove" style="margin-left:6px;"><?php echo esc_js( __( 'Remove', 'amw-simple-login' ) ); ?></button>'
                            );
                        }
                    });
                    bgFrame.open();
                });

                $(document).on('click', '#amw-bg-remove', function(e) {
                    e.preventDefault();
                    $('#amw_bg_id').val('');
                    $('#amw-bg-preview').empty();
                    $('#amw-bg-upload').text('<?php echo esc_js( __( 'Select image', 'amw-simple-login' ) ); ?>');
                    $(this).remove();
                });
            });
            </script>

            <h2><?php esc_html_e( 'Background when no image is set', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Style', 'amw-simple-login' ); ?></th>
                    <td>
                        <?php
                        $no_bg_styles = [
                            'none'     => __( 'Default (uses the palette page color)', 'amw-simple-login' ),
                            'solid'    => __( 'Solid color', 'amw-simple-login' ),
                            'gradient' => __( 'Gradient', 'amw-simple-login' ),
                        ];
                        foreach ( $no_bg_styles as $val => $label ) : ?>
                            <label style="display:block; margin-bottom:4px;">
                                <input type="radio" name="amw_login_options[no_image_bg]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $opts['no_image_bg'], $val ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">
                            <?php esc_html_e( 'Only applies when there is no background image. The form stays centered.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="amw_solid_color"><?php esc_html_e( 'Solid color', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="color" id="amw_solid_color" name="amw_login_options[solid_color]" value="<?php echo esc_attr( $opts['solid_color'] ); ?>">
                        <code style="margin-left:8px;"><?php echo esc_html( $opts['solid_color'] ); ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Gradient', 'amw-simple-login' ); ?></th>
                    <td>
                        <label style="margin-right:16px;">
                            <?php esc_html_e( 'Start', 'amw-simple-login' ); ?>
                            <input type="color" name="amw_login_options[grad_start]" value="<?php echo esc_attr( $opts['grad_start'] ); ?>">
                        </label>
                        <label style="margin-right:16px;">
                            <?php esc_html_e( 'End', 'amw-simple-login' ); ?>
                            <input type="color" name="amw_login_options[grad_end]" value="<?php echo esc_attr( $opts['grad_end'] ); ?>">
                        </label>
                        <label>
                            <?php esc_html_e( 'Angle', 'amw-simple-login' ); ?>
                            <input type="number" name="amw_login_options[grad_angle]" min="0" max="360" step="1" value="<?php echo esc_attr( (int) $opts['grad_angle'] ); ?>" style="width:80px;">°
                        </label>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Color palette', 'amw-simple-login' ); ?></h2>
            <p>
                <strong><?php esc_html_e( 'Presets:', 'amw-simple-login' ); ?></strong>
                <?php foreach ( $presets as $id => $preset ) : ?>
                    <button type="button" class="button amw-preset" data-colors="<?php echo esc_attr( wp_json_encode( $preset['colors'] ) ); ?>">
                        <?php echo esc_html( $preset['label'] ); ?>
                    </button>
                <?php endforeach; ?>
                <span class="description" style="margin-left:8px;"><?php esc_html_e( 'Fills the colors below. Remember to save.', 'amw-simple-login' ); ?></span>
            </p>
            <table class="form-table" role="presentation">
                <?php foreach ( amw_login_color_keys() as $key => $label ) : ?>
                    <tr>
                        <th scope="row">
                            <label for="amw_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
                        </th>
                        <td>
                            <input type="color" id="amw_<?php echo esc_attr( $key ); ?>" name="amw_login_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $opts[ $key ] ); ?>">
                            <code id="amw_<?php echo esc_attr( $key ); ?>_val" style="margin-left:8px;"><?php echo esc_html( $opts[ $key ] ); ?></code>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <script>
            jQuery(function($){
                $('.amw-preset').on('click', function(e){
                    e.preventDefault();
                    var colors = $(this).data('colors');
                    $.each(colors, function(key, hex){
                        $('#amw_' + key).val(hex);
                        $('#amw_' + key + '_val').text(hex);
                    });
                });
            });
            </script>

            <h2><?php esc_html_e( 'Interface options', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="amw_radius"><?php esc_html_e( 'Corner radius', 'amw-simple-login' ); ?></label>
                    </th>
                    <td>
                        <input
                            type="range"
                            id="amw_radius"
                            name="amw_login_options[radius]"
                            min="0"
                            max="24"
                            step="1"
                            value="<?php echo esc_attr( $opts['radius'] ); ?>"
                            style="vertical-align:middle; width:220px;"
                            oninput="document.getElementById('amw_radius_val').textContent = this.value + ' px';"
                        >
                        <code id="amw_radius_val" style="margin-left:8px;"><?php echo esc_html( $opts['radius'] ); ?> px</code>
                        <p class="description">
                            <?php esc_html_e( 'From 0 (square corners) to 24. Fields and the button use a slightly smaller radius so the box stays balanced.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Hide "Go to [site]"', 'amw-simple-login' ); ?></th>
                    <td><input type="checkbox" name="amw_login_options[hide_backtoblog]" value="1" <?php checked( $opts['hide_backtoblog'], '1' ); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Hide "Lost your password?"', 'amw-simple-login' ); ?></th>
                    <td>
                        <input type="checkbox" name="amw_login_options[hide_forgotpw]" value="1" <?php checked( $opts['hide_forgotpw'], '1' ); ?>>
                        <p class="description">
                            <?php esc_html_e( 'Only hides the link on the login screen. Password recovery still works via direct URL.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Heading text', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="amw_heading_text"><?php esc_html_e( 'Text above the form', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="text" id="amw_heading_text" name="amw_login_options[heading_text]" value="<?php echo esc_attr( $opts['heading_text'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Welcome to Client Name', 'amw-simple-login' ); ?>" style="width:100%; max-width:420px;">
                        <p class="description"><?php esc_html_e( 'Optional. Shown between the logo and the form. Leave empty to hide it.', 'amw-simple-login' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Extra CSS', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="amw_custom_css"><?php esc_html_e( 'Custom CSS for the login', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <textarea id="amw_custom_css" name="amw_login_options[custom_css]" rows="8" class="large-text code" placeholder=".login h1 a { ... }"><?php echo esc_textarea( $opts['custom_css'] ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Injected only on the login screen, after the plugin styles. For per-site tweaks without editing files.', 'amw-simple-login' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Legal links in the login footer', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Show legal links', 'amw-simple-login' ); ?></th>
                    <td><input type="checkbox" name="amw_login_options[show_legal]" value="1" <?php checked( $opts['show_legal'], '1' ); ?>></td>
                </tr>
                <?php
                $legal_fields = [
                    'aviso'      => __( 'Legal notice', 'amw-simple-login' ),
                    'privacidad' => __( 'Privacy policy', 'amw-simple-login' ),
                    'cookies'    => __( 'Cookie policy', 'amw-simple-login' ),
                ];
                foreach ( $legal_fields as $key => $default_label ) :
                    $url_key   = 'legal_' . $key;
                    $label_key = 'legal_' . $key . '_label';
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html( $default_label ); ?></th>
                        <td>
                            <input type="text" name="amw_login_options[<?php echo esc_attr( $label_key ); ?>]" value="<?php echo esc_attr( $opts[ $label_key ] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $default_label ); ?>" style="width:180px;">
                            <input type="text" name="amw_login_options[<?php echo esc_attr( $url_key ); ?>]" value="<?php echo esc_attr( $opts[ $url_key ] ); ?>" class="regular-text" placeholder="/page-url/" style="width:260px; margin-left:8px;">
                            <p class="description"><?php esc_html_e( 'Link text · Page URL', 'amw-simple-login' ); ?></p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php submit_button(); ?>
        </form>

        <hr>

        <h2><?php esc_html_e( 'Export / Import settings', 'amw-simple-login' ); ?></h2>
        <p class="description">
            <?php esc_html_e( 'Copy the JSON to replicate this configuration on another site, or paste a saved configuration to import it.', 'amw-simple-login' ); ?>
        </p>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e( 'Export', 'amw-simple-login' ); ?></th>
                <td>
                    <textarea id="amw_export_json" rows="6" class="large-text code" readonly><?php echo esc_textarea( wp_json_encode( $opts, JSON_PRETTY_PRINT ) ); ?></textarea>
                    <p>
                        <button type="button" class="button" id="amw-export-copy"><?php esc_html_e( 'Copy', 'amw-simple-login' ); ?></button>
                        <button type="button" class="button" id="amw-export-download"><?php esc_html_e( 'Download .json', 'amw-simple-login' ); ?></button>
                    </p>
                </td>
            </tr>
        </table>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="amw_login_import">
            <?php wp_nonce_field( 'amw_login_import' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="amw_login_import_json"><?php esc_html_e( 'Import', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <textarea id="amw_login_import_json" name="amw_login_import_json" rows="6" class="large-text code" placeholder='{ "color_bg": "#0a0a0a", ... }'></textarea>
                        <p>
                            <button type="submit" class="button button-secondary"><?php esc_html_e( 'Import settings', 'amw-simple-login' ); ?></button>
                            <span class="description"><?php esc_html_e( 'Overwrites the current configuration on this site.', 'amw-simple-login' ); ?></span>
                        </p>
                    </td>
                </tr>
            </table>
        </form>

        <script>
        jQuery(function($){
            $('#amw-export-copy').on('click', function(e){
                e.preventDefault();
                var ta = document.getElementById('amw_export_json');
                ta.select();
                document.execCommand('copy');
            });
            $('#amw-export-download').on('click', function(e){
                e.preventDefault();
                var data = document.getElementById('amw_export_json').value;
                var blob = new Blob([data], { type: 'application/json' });
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'amw-simple-login-settings.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        });
        </script>
    </div>
    <?php
}


// ─────────────────────────────────────────────────────────────
// 3. LOGIN
//    Everything below is registered inside login_init, so none of
//    it reaches the front end or the dashboard.
// ─────────────────────────────────────────────────────────────

add_action( 'login_init', 'amw_login_init' );

function amw_login_init() {
    add_action( 'login_enqueue_scripts', 'amw_login_estilos' );
    add_action( 'login_head', 'amw_login_dynamic_styles' );
    add_action( 'login_footer', 'amw_login_footer_legal' );
    add_filter( 'login_message', 'amw_login_heading' );

    add_filter( 'login_headerurl', function() {
        return home_url( '/' );
    } );

    add_filter( 'login_headertext', function() {
        return esc_html( get_bloginfo( 'name', 'display' ) );
    } );

    add_filter( 'login_display_language_dropdown', '__return_false' );

    add_filter( 'login_body_class', 'amw_login_body_class' );

    // These two filters are global by nature: registered outside login_init they
    // would strip the privacy policy link across the whole site.
    add_filter( 'the_privacy_policy_link', '__return_empty_string', PHP_INT_MAX );
    add_filter( 'privacy_policy_url', '__return_empty_string', PHP_INT_MAX );

    add_filter( 'login_errors', 'amw_login_generic_error' );

    // Tab title without the "— WordPress" suffix.
    add_filter( 'login_title', function( $login_title, $title ) {
        return $title . ' ‹ ' . get_bloginfo( 'name', 'display' );
    }, 10, 2 );
}

/**
 * Optional heading shown above the form (via the login_message filter).
 */
function amw_login_heading( $message ) {
    $opts = amw_login_get_options();
    $text = trim( $opts['heading_text'] );
    if ( '' === $text ) {
        return $message;
    }
    return '<p class="amw-heading">' . esc_html( $text ) . '</p>' . $message;
}

/**
 * <body> classes for the login depending on whether there is a background
 * image and its layout. With no image nothing is added, so the classic
 * centered login is preserved.
 */
function amw_login_body_class( $classes ) {
    if ( amw_login_bg_url() ) {
        $opts      = amw_login_get_options();
        $layout    = in_array( $opts['layout'], [ 'left', 'center', 'right' ], true ) ? $opts['layout'] : 'left';
        $classes[] = 'amw-has-bg';
        $classes[] = 'amw-layout-' . $layout;
    }
    return $classes;
}

/**
 * Generic message for credential errors only.
 * Everything else (blocked cookies, 2FA, security-plugin notices) passes through.
 */
function amw_login_generic_error( $error ) {
    global $errors;

    if ( ! is_wp_error( $errors ) ) {
        return $error;
    }

    $credenciales = [
        'invalid_username',
        'invalid_email',
        'incorrect_password',
        'invalidcombo',
        'empty_username',
        'empty_password',
    ];

    if ( array_intersect( $errors->get_error_codes(), $credenciales ) ) {
        return __( 'The credentials you entered are not correct.', 'amw-simple-login' );
    }

    return $error;
}

function amw_login_estilos() {
    $css_file = AMW_LOGIN_DIR . 'login.css';
    $version  = file_exists( $css_file ) ? filemtime( $css_file ) : AMW_LOGIN_VERSION;
    wp_enqueue_style( 'amw-login', AMW_LOGIN_URL . 'login.css', [], $version );
}

/**
 * Current wp-login.php action (login, lostpassword, resetpass, register...).
 */
function amw_login_current_action() {
    $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
    return $action ?: 'login';
}

/**
 * CSS variables + logo + conditional rules, in a single block.
 * Printed in login_head (priority 10), after login.css, so it wins.
 */
function amw_login_dynamic_styles() {
    $opts = amw_login_get_options();
    $logo = amw_login_logo_data();
    $bg   = amw_login_bg_url();

    // Page background color: solid override when configured, otherwise palette color.
    $page_bg = ( ! $bg && 'solid' === $opts['no_image_bg'] )
        ? amw_login_css_color( $opts, 'solid_color' )
        : amw_login_css_color( $opts, 'color_bg' );

    // Overlay as rgba (more compatible than color-mix): configurable color and opacity.
    $ov_hex = amw_login_css_color( $opts, 'overlay_color' );
    $ov     = ltrim( $ov_hex, '#' );
    if ( 3 === strlen( $ov ) ) {
        $ov = $ov[0] . $ov[0] . $ov[1] . $ov[1] . $ov[2] . $ov[2];
    }
    $ov_rgba = sprintf(
        'rgba(%d, %d, %d, %.2f)',
        hexdec( substr( $ov, 0, 2 ) ),
        hexdec( substr( $ov, 2, 2 ) ),
        hexdec( substr( $ov, 4, 2 ) ),
        max( 0, min( 100, (int) $opts['overlay_opacity'] ) ) / 100
    );
    ?>
    <style id="amw-login-dynamic">
        :root {
            --amw-bg:          <?php echo $page_bg; ?>;
            --amw-surface:     <?php echo amw_login_css_color( $opts, 'color_surface' ); ?>;
            --amw-surface-alt: <?php echo amw_login_css_color( $opts, 'color_surface_alt' ); ?>;
            --amw-input-bg:    <?php echo amw_login_css_color( $opts, 'color_input_bg' ); ?>;
            --amw-border:      <?php echo amw_login_css_color( $opts, 'color_border' ); ?>;
            --amw-text:        <?php echo amw_login_css_color( $opts, 'color_text' ); ?>;
            --amw-text-muted:  <?php echo amw_login_css_color( $opts, 'color_muted' ); ?>;
            --amw-accent:      <?php echo amw_login_css_color( $opts, 'color_accent' ); ?>;
            --amw-accent-fg:   <?php echo amw_login_css_color( $opts, 'color_accent_fg' ); ?>;
            --amw-radius:      <?php echo (int) $opts['radius']; ?>px;
            --amw-logo-width:  <?php echo (int) $logo['width']; ?>px;
            --amw-logo-height: <?php echo (int) $logo['height']; ?>px;
            --amw-font:        system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            <?php if ( $bg ) : ?>
            --amw-col:         480px;
            --amw-bg-image:    url('<?php echo esc_url( $bg ); ?>');
            --amw-blur:        <?php echo (int) $opts['blur']; ?>px;
            --amw-overlay:     <?php echo $ov_rgba; ?>;
            <?php endif; ?>
        }
        <?php if ( $logo['url'] ) : ?>
        .login h1 a {
            background-image: url("<?php echo ( 0 === strpos( $logo['url'], 'data:' ) ) ? $logo['url'] : esc_url( $logo['url'] ); ?>") !important;
        }
        <?php endif; ?>
        <?php if ( ! $bg && 'gradient' === $opts['no_image_bg'] ) : ?>
        /* Full-page gradient (only when no image is set). */
        body.login {
            background-image: linear-gradient(
                <?php echo (int) $opts['grad_angle']; ?>deg,
                <?php echo amw_login_css_color( $opts, 'grad_start' ); ?>,
                <?php echo amw_login_css_color( $opts, 'grad_end' ); ?>
            ) !important;
        }
        <?php elseif ( ! $bg && 'solid' === $opts['no_image_bg'] ) : ?>
        /* Flat solid page background: drop the default radial tint. */
        body.login { background-image: none !important; }
        <?php endif; ?>
        <?php if ( '1' === $opts['hide_backtoblog'] ) : ?>
        #backtoblog { display: none !important; }
        <?php endif; ?>
        <?php if ( '1' === $opts['hide_forgotpw'] && 'login' === amw_login_current_action() ) : ?>
        /* Login screen only: on lostpassword, #nav holds the "back to login" link. */
        #nav { display: none !important; }
        <?php endif; ?>

        /* Optional heading above the form. */
        .login .amw-heading {
            text-align: center;
            color: var(--amw-text);
            font-family: var(--amw-font);
            font-size: 15px;
            font-weight: 600;
            margin: 0 0 16px;
            padding: 0;
        }

        /* Legal links: inline and self-contained, so cache-plugin CSS
           optimization (LiteSpeed UCSS/async) does not leave them unstyled.
           Absolutely positioned because login_footer prints the <p> OUTSIDE #login. */
        .amw-legal-links {
            position: absolute;
            left: 50%;
            bottom: 24px;
            transform: translateX(-50%);
            width: 340px;
            max-width: calc(100vw - 32px);
            margin: 0;
            text-align: center;
            font-family: var(--amw-font);
            font-size: 11px;
            color: var(--amw-text-muted);
        }
        .amw-legal-links a {
            color: var(--amw-text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .amw-legal-links a:hover { color: var(--amw-text); }
        .amw-legal-links a:focus-visible {
            outline: 2px solid var(--amw-accent);
            outline-offset: 3px;
            border-radius: 2px;
        }

        <?php if ( $bg ) : ?>
        /* ── Background image / layout (inline: immune to CSS pruning) ──
           Layer 1 (::before): image + darkening together, with blur.
           Layer 2 (::after): solid background color of the form column,
           ABOVE the image so the edge always stays clean.
           The login box (#login) sits above both. */
        body.login.amw-has-bg::before {
            content: ""; position: fixed; top: 0; bottom: 0; z-index: -2;
            background:
                linear-gradient(var(--amw-overlay), var(--amw-overlay)),
                var(--amw-bg-image);
            background-size: cover;
            background-position: center;
            filter: blur(var(--amw-blur, 0px));
            /* Slight oversize so the blur does not leave a halo at the edges. */
            transform: scale(1.06);
        }
        body.login.amw-has-bg::after {
            content: ""; position: fixed; top: 0; bottom: 0; z-index: -1;
            background: var(--amw-bg);
        }

        /* Left: image on the right, solid form panel on the left. */
        body.login.amw-layout-left.amw-has-bg::before { left: var(--amw-col); right: 0; }
        body.login.amw-layout-left.amw-has-bg::after  { left: 0; width: var(--amw-col); }
        body.login.amw-layout-left.amw-has-bg #login,
        body.login.amw-layout-left.amw-has-bg .amw-legal-links { left: calc(var(--amw-col) / 2); }

        /* Right: mirrored. */
        body.login.amw-layout-right.amw-has-bg::before { left: 0; right: var(--amw-col); }
        body.login.amw-layout-right.amw-has-bg::after  { right: 0; left: auto; width: var(--amw-col); }
        body.login.amw-layout-right.amw-has-bg #login,
        body.login.amw-layout-right.amw-has-bg .amw-legal-links { left: calc(100% - var(--amw-col) / 2); }

        /* Center: full-screen image, no column panel. */
        body.login.amw-layout-center.amw-has-bg::before { left: 0; right: 0; }
        body.login.amw-layout-center.amw-has-bg::after  { display: none; }

        /* Mobile: single column, full-screen image, no panel. */
        @media (max-width: 782px) {
            body.login.amw-has-bg::before { left: 0 !important; right: 0 !important; }
            body.login.amw-has-bg::after  { display: none !important; }
            body.login.amw-has-bg #login,
            body.login.amw-has-bg .amw-legal-links { left: 50% !important; }
        }
        <?php endif; ?>
    </style>
    <?php
    // Extra CSS from the settings (admin-only, sanitized on save).
    $custom = trim( $opts['custom_css'] );
    if ( '' !== $custom ) {
        echo "\n<style id=\"amw-login-custom\">\n" . $custom . "\n</style>\n";
    }
}


// ─────────────────────────────────────────────────────────────
// 4. LEGAL FOOTER
// ─────────────────────────────────────────────────────────────

function amw_login_footer_legal() {
    $opts = amw_login_get_options();
    if ( '1' !== $opts['show_legal'] ) {
        return;
    }

    $links = [
        [ 'label' => $opts['legal_aviso_label'],      'url' => $opts['legal_aviso'] ],
        [ 'label' => $opts['legal_privacidad_label'], 'url' => $opts['legal_privacidad'] ],
        [ 'label' => $opts['legal_cookies_label'],    'url' => $opts['legal_cookies'] ],
    ];

    $items = [];
    foreach ( $links as $link ) {
        if ( ! $link['url'] || ! $link['label'] ) {
            continue;
        }
        $href    = ( 0 === strpos( $link['url'], 'http' ) ) ? $link['url'] : home_url( $link['url'] );
        $items[] = '<a href="' . esc_url( $href ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $link['label'] ) . '</a>';
    }

    if ( empty( $items ) ) {
        return;
    }

    echo '<p class="amw-legal-links">' . implode( ' · ', $items ) . '</p>';
}
