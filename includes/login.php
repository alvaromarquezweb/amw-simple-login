<?php
/**
 * AMW Simple Login — login screen output (styles, honeypot, legal footer).
 *
 * @package AMW_Simple_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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

    // Shorten the username field label to just "User" / "Usuario".
    add_filter( 'gettext', 'amw_login_username_label', 20, 3 );

    // Honeypot: hidden bait field on the login form + rejection on the auth chain.
    add_action( 'login_form', 'amw_login_honeypot_field' );
    add_filter( 'authenticate', 'amw_login_honeypot_check', 30, 3 );

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
 * <body> classes for the login: always the position, plus flags for an image
 * background and for the side panel (left/right only).
 */
function amw_login_body_class( $classes ) {
    $opts = amw_login_get_options();
    $pos  = in_array( $opts['layout'], [ 'left', 'center', 'right' ], true ) ? $opts['layout'] : 'center';

    $classes[] = 'amw-pos-' . $pos;

    if ( 'image' === $opts['bg_type'] && amw_login_bg_url() ) {
        $classes[] = 'amw-has-image';
    }
    if ( 'center' !== $pos ) {
        $classes[] = 'amw-has-panel';
    }
    if ( '1' === $opts['hide_backtoblog'] ) {
        $classes[] = 'amw-hide-backtoblog';
    }
    // Only on the login screen: on lostpassword, #nav is the "back to login" link.
    if ( '1' === $opts['hide_forgotpw'] && 'login' === amw_login_current_action() ) {
        $classes[] = 'amw-hide-forgotpw';
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

/**
 * Whether the honeypot is active. Driven by the "Security" setting and still
 * filterable so it can be forced on/off site-wide in code, overriding the UI
 * (e.g. add_filter( 'amw_login_honeypot_enabled', '__return_false' );).
 */
function amw_login_honeypot_enabled() {
    $opts = amw_login_get_options();
    return (bool) apply_filters( 'amw_login_honeypot_enabled', '1' === $opts['honeypot_on'] );
}

/**
 * Prints a hidden bait field on the main login form. Real users never see it
 * (off-screen, aria-hidden, not tab-focusable, autocomplete off); bots that
 * autofill every field tend to fill it. The name/label carry "website" so
 * heuristic autofillers take the bait, while staying namespaced to avoid
 * clashing with core or other plugins.
 */
function amw_login_honeypot_field() {
    if ( ! amw_login_honeypot_enabled() ) {
        return;
    }
    ?>
    <div aria-hidden="true" style="position:absolute; left:-9999px; top:auto; width:1px; height:1px; overflow:hidden;">
        <label for="amw_hp_website">Website</label>
        <input type="text" name="amw_website" id="amw_hp_website" tabindex="-1" autocomplete="off" value="">
    </div>
    <?php
}

/**
 * Rejects the login when the honeypot field arrives filled. The field only
 * exists on our rendered login form, so API/XML-RPC auth (where it is absent)
 * is never affected. Reuses the generic credentials message so a caught bot
 * learns nothing.
 */
function amw_login_honeypot_check( $user, $username, $password ) {
    if ( ! amw_login_honeypot_enabled() ) {
        return $user;
    }
    if ( isset( $_POST['amw_website'] ) && '' !== trim( wp_unslash( $_POST['amw_website'] ) ) ) {
        return new WP_Error( 'amw_honeypot', __( 'The credentials you entered are not correct.', 'amw-simple-login' ) );
    }
    return $user;
}

/**
 * Replaces the default "Username or Email Address" login label with a short
 * "User" (translated as "Usuario" in es_ES). Registered inside login_init, so
 * it only runs on the login screen. The field still accepts an email address
 * for login; only the label changes.
 */
function amw_login_username_label( $translated, $text, $domain ) {
    if ( 'default' === $domain && 'Username or Email Address' === $text ) {
        return __( 'User', 'amw-simple-login' );
    }
    return $translated;
}

function amw_login_estilos() {
    $css_file = AMW_LOGIN_DIR . 'assets/css/login.css';
    $version  = file_exists( $css_file ) ? filemtime( $css_file ) : AMW_LOGIN_VERSION;
    wp_enqueue_style( 'amw-login', AMW_LOGIN_URL . 'assets/css/login.css', [], $version );
}

/**
 * Current wp-login.php action (login, lostpassword, resetpass, register...).
 */
function amw_login_current_action() {
    $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
    return $action ?: 'login';
}

/**
 * Injects ONLY the per-site values: the :root CSS variables (colours, radius,
 * page background, panel, shadow, focus, and the image overlay/blur) plus the
 * logo rule. All structure lives in login.css and reads these variables.
 * Printed in login_head (priority 10), after login.css, so the values win.
 */
function amw_login_dynamic_styles() {
    $opts = amw_login_get_options();
    $logo = amw_login_logo_data();

    $bg_type   = in_array( $opts['bg_type'], [ 'solid', 'gradient', 'image' ], true ) ? $opts['bg_type'] : 'solid';
    $img_url   = ( 'image' === $bg_type ) ? amw_login_bg_url() : '';
    $has_image = ( '' !== $img_url );

    $bg1   = amw_login_css_color( $opts, 'bg_color1' );
    $bg2   = amw_login_css_color( $opts, 'bg_color2' );
    $bgang = max( 0, min( 360, (int) $opts['bg_angle'] ) );
    $panel = amw_login_css_color( $opts, 'panel_color' );
    $focus = amw_login_css_color( $opts, 'focus_color' );

    // Base page background: solid colour, gradient, or (for an image) the panel colour.
    if ( 'gradient' === $bg_type ) {
        $page_bg = sprintf( 'linear-gradient(%ddeg, %s, %s)', $bgang, $bg1, $bg2 );
    } else {
        $page_bg = $bg1;
    }
    if ( $has_image ) {
        $page_bg = $panel;
    }

    // Login box drop shadow.
    if ( '1' === $opts['shadow_on'] ) {
        $amt    = max( 0, min( 100, (int) $opts['shadow_amount'] ) );
        $shadow = sprintf(
            '0 %dpx %dpx %s',
            (int) round( $amt * 0.5 ),
            (int) round( $amt * 1.4 ),
            amw_login_hex_to_rgba( amw_login_css_color( $opts, 'shadow_color' ), 0.35 + $amt / 300 )
        );
    } else {
        $shadow = 'none';
    }

    // Overlay over the image (only emitted in image mode).
    if ( $has_image ) {
        $ov_type = ( 'gradient' === $opts['ov_type'] ) ? 'gradient' : 'solid';
        $ov1     = amw_login_css_color( $opts, 'ov_color1' );
        $ov2     = amw_login_css_color( $opts, 'ov_color2' );
        $ovang   = max( 0, min( 360, (int) $opts['ov_angle'] ) );
        $ovop    = max( 0, min( 100, (int) $opts['ov_opacity'] ) ) / 100;
        if ( 'gradient' === $ov_type ) {
            $overlay = sprintf(
                'linear-gradient(%ddeg, %s, %s)',
                $ovang,
                amw_login_hex_to_rgba( $ov1, $ovop ),
                amw_login_hex_to_rgba( $ov2, $ovop )
            );
        } else {
            $r       = amw_login_hex_to_rgba( $ov1, $ovop );
            $overlay = sprintf( 'linear-gradient(%s, %s)', $r, $r );
        }
    }
    ?>
    <style id="amw-login-dynamic">
        :root {
            --amw-bg:          <?php echo amw_login_css_color( $opts, 'color_bg' ); ?>;
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
            --amw-page-bg:     <?php echo $page_bg; ?>;
            --amw-panel:       <?php echo $panel; ?>;
            --amw-shadow:      <?php echo $shadow; ?>;
            --amw-focus:       <?php echo $focus; ?>;
            --amw-focus-ring:  <?php echo amw_login_hex_to_rgba( $focus, 0.25 ); ?>;
            <?php if ( $has_image ) : ?>
            --amw-bg-image:    url('<?php echo esc_url( $img_url ); ?>');
            --amw-overlay:     <?php echo $overlay; ?>;
            --amw-blur:        <?php echo (int) $opts['blur']; ?>px;
            <?php endif; ?>
        }
        <?php if ( $logo['url'] ) : ?>
        .login h1 a {
            background-image: url("<?php echo ( 0 === strpos( $logo['url'], 'data:' ) ) ? $logo['url'] : esc_url( $logo['url'] ); ?>") !important;
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

