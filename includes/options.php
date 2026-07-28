<?php
/**
 * AMW Simple Login — options, sanitisation and shared data helpers.
 *
 * @package AMW_Simple_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
        'honeypot_on'       => '1', // anti-bot honeypot on the login form
        'show_legal'        => '0',
        'legal_aviso'            => '/legal-notice/',
        'legal_aviso_label'      => __( 'Legal notice', 'amw-simple-login' ),
        'legal_privacidad'       => '/privacy-policy/',
        'legal_privacidad_label' => __( 'Privacy policy', 'amw-simple-login' ),
        'legal_cookies'          => '/cookie-policy/',
        'legal_cookies_label'    => __( 'Cookie policy', 'amw-simple-login' ),
        'logo_id'                => '',
        'bg_id'                  => '',
        'layout'                 => 'center', // left | center | right (login position, always applies)
        // Background (v1.2.0): solid | gradient | image.
        'bg_type'                => 'solid',
        'bg_color1'              => '#0a0a0a',  // solid colour / gradient start
        'bg_color2'              => '#1c1c1c',  // gradient end
        'bg_angle'               => 135,        // gradient angle 0-360
        'blur'                   => 0,          // image blur in px (0-20), image only
        // Overlay over the image (only when bg_type is image).
        'ov_type'                => 'solid',    // solid | gradient
        'ov_color1'              => '#05070d',
        'ov_color2'              => '#0a1830',
        'ov_angle'               => 135,
        'ov_opacity'             => 55,         // 0-100
        // Solid panel behind the form (left/right positions only).
        'panel_color'            => '#0a0a0a',
        // Login box drop shadow.
        'shadow_on'              => '1',
        'shadow_amount'          => 45,         // 0-100
        'shadow_color'           => '#000000',
        // Selected (focused) field colour.
        'focus_color'            => '#fafafa',
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
                'bg_color1' => '#0a0a0a', 'bg_color2' => '#1c1c1c',
                'ov_color1' => '#05070d', 'ov_color2' => '#0a1830',
                'panel_color' => '#0a0a0a', 'shadow_color' => '#000000', 'focus_color' => '#fafafa',
            ],
        ],
        'light' => [
            'label'  => __( 'Light', 'amw-simple-login' ),
            'colors' => [
                'color_bg' => '#f5f5f5', 'color_surface' => '#ffffff', 'color_surface_alt' => '#f0f0f0',
                'color_input_bg' => '#ffffff', 'color_border' => '#dcdcdc', 'color_text' => '#1a1a1a',
                'color_muted' => '#666666', 'color_accent' => '#1a1a1a', 'color_accent_fg' => '#ffffff',
                'bg_color1' => '#f5f5f5', 'bg_color2' => '#e6e6e9',
                'ov_color1' => '#1a1a1a', 'ov_color2' => '#333333',
                'panel_color' => '#ffffff', 'shadow_color' => '#334155', 'focus_color' => '#1a1a1a',
            ],
        ],
        'slate' => [
            'label'  => __( 'Slate', 'amw-simple-login' ),
            'colors' => [
                'color_bg' => '#0f172a', 'color_surface' => '#1e293b', 'color_surface_alt' => '#334155',
                'color_input_bg' => '#1e293b', 'color_border' => '#334155', 'color_text' => '#f1f5f9',
                'color_muted' => '#94a3b8', 'color_accent' => '#38bdf8', 'color_accent_fg' => '#0f172a',
                'bg_color1' => '#0f172a', 'bg_color2' => '#1e293b',
                'ov_color1' => '#020617', 'ov_color2' => '#0f172a',
                'panel_color' => '#0f172a', 'shadow_color' => '#020617', 'focus_color' => '#38bdf8',
            ],
        ],
    ];
}

function amw_login_get_options() {
    $raw  = get_option( 'amw_login_options', [] );
    if ( ! is_array( $raw ) ) {
        $raw = [];
    }
    $opts = wp_parse_args( $raw, amw_login_defaults() );
    return amw_login_migrate_bg( $opts, $raw );
}

/**
 * Back-compat: maps older option layouts onto the v1.2.0 background model
 * (bg_type / bg_color* / ov_* / panel_color). Runs at read time only; once the
 * options are saved from the new UI, only the new keys persist. Covers both the
 * 1.1.0 layout (overlay_color, no_image_bg, solid_color, grad_*) and the short
 * interim "fill" layout (fill_type, fill_color1/2, fill_angle).
 */
function amw_login_migrate_bg( $opts, $raw ) {
    if ( isset( $raw['bg_type'] ) ) {
        return $opts; // already on the new model
    }

    $has_old = isset( $raw['overlay_color'] ) || isset( $raw['no_image_bg'] )
        || isset( $raw['solid_color'] ) || isset( $raw['grad_start'] ) || isset( $raw['fill_type'] );
    if ( ! $has_old ) {
        return $opts; // clean install: defaults are fine
    }

    if ( ! empty( $raw['bg_id'] ) ) {
        // Image background: derive the overlay from whatever old layout is present.
        $opts['bg_type'] = 'image';
        if ( isset( $raw['fill_type'] ) ) {
            $opts['ov_type']   = ( 'gradient' === $raw['fill_type'] ) ? 'gradient' : 'solid';
            $opts['ov_color1'] = isset( $raw['fill_color1'] ) ? $raw['fill_color1'] : $opts['ov_color1'];
            $opts['ov_color2'] = isset( $raw['fill_color2'] ) ? $raw['fill_color2'] : $opts['ov_color2'];
            $opts['ov_angle']  = isset( $raw['fill_angle'] ) ? (int) $raw['fill_angle'] : $opts['ov_angle'];
        } elseif ( isset( $raw['overlay_color'] ) ) {
            $opts['ov_type']   = 'solid';
            $opts['ov_color1'] = $raw['overlay_color'];
        }
    } elseif ( isset( $raw['fill_type'] ) ) {
        // Interim "fill" used as the page background.
        $opts['bg_type']   = ( 'gradient' === $raw['fill_type'] ) ? 'gradient' : 'solid';
        $opts['bg_color1'] = isset( $raw['fill_color1'] ) ? $raw['fill_color1'] : $opts['bg_color1'];
        $opts['bg_color2'] = isset( $raw['fill_color2'] ) ? $raw['fill_color2'] : $opts['bg_color2'];
        $opts['bg_angle']  = isset( $raw['fill_angle'] ) ? (int) $raw['fill_angle'] : $opts['bg_angle'];
    } else {
        // 1.1.0 "no image" layout.
        $mode = isset( $raw['no_image_bg'] ) ? $raw['no_image_bg'] : 'none';
        if ( 'gradient' === $mode ) {
            $opts['bg_type']   = 'gradient';
            $opts['bg_color1'] = isset( $raw['grad_start'] ) ? $raw['grad_start'] : $opts['bg_color1'];
            $opts['bg_color2'] = isset( $raw['grad_end'] )   ? $raw['grad_end']   : $opts['bg_color2'];
            $opts['bg_angle']  = isset( $raw['grad_angle'] ) ? (int) $raw['grad_angle'] : $opts['bg_angle'];
        } elseif ( 'solid' === $mode ) {
            $opts['bg_type']   = 'solid';
            $opts['bg_color1'] = isset( $raw['solid_color'] ) ? $raw['solid_color'] : $opts['bg_color1'];
        } else {
            $opts['bg_type']   = 'solid';
            $opts['bg_color1'] = isset( $raw['color_bg'] ) ? $raw['color_bg'] : $opts['bg_color1'];
        }
    }

    // Shared: overlay opacity, and the panel colour (the old ::after used color_bg).
    if ( isset( $raw['overlay_opacity'] ) ) {
        $opts['ov_opacity'] = (int) $raw['overlay_opacity'];
    }
    if ( isset( $raw['color_bg'] ) ) {
        $opts['panel_color'] = $raw['color_bg'];
    }

    return $opts;
}

/**
 * Converts a #hex colour (3 or 6 digits) to an rgba() string with the given
 * alpha (0-1). Used to build the semi-transparent tint over the image.
 */
function amw_login_hex_to_rgba( $hex, $alpha ) {
    $hex = ltrim( (string) $hex, '#' );
    if ( 3 === strlen( $hex ) ) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if ( 6 !== strlen( $hex ) ) {
        $hex = '000000';
    }
    return sprintf(
        'rgba(%d, %d, %d, %.2f)',
        hexdec( substr( $hex, 0, 2 ) ),
        hexdec( substr( $hex, 2, 2 ) ),
        hexdec( substr( $hex, 4, 2 ) ),
        max( 0, min( 1, (float) $alpha ) )
    );
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

    foreach ( [ 'hide_backtoblog', 'hide_forgotpw', 'show_legal', 'honeypot_on' ] as $key ) {
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

    // ── Background (v1.2.0) ──
    $bg_type           = isset( $input['bg_type'] ) ? sanitize_key( $input['bg_type'] ) : '';
    $output['bg_type'] = in_array( $bg_type, [ 'solid', 'gradient', 'image' ], true ) ? $bg_type : $defaults['bg_type'];

    $ov_type           = isset( $input['ov_type'] ) ? sanitize_key( $input['ov_type'] ) : '';
    $output['ov_type'] = in_array( $ov_type, [ 'solid', 'gradient' ], true ) ? $ov_type : $defaults['ov_type'];

    foreach ( [ 'bg_color1', 'bg_color2', 'ov_color1', 'ov_color2', 'panel_color', 'shadow_color', 'focus_color' ] as $key ) {
        $val            = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
        $output[ $key ] = $val ?: $defaults[ $key ];
    }

    $output['bg_angle']   = isset( $input['bg_angle'] ) ? max( 0, min( 360, absint( $input['bg_angle'] ) ) ) : $defaults['bg_angle'];
    $output['ov_angle']   = isset( $input['ov_angle'] ) ? max( 0, min( 360, absint( $input['ov_angle'] ) ) ) : $defaults['ov_angle'];
    $output['ov_opacity'] = isset( $input['ov_opacity'] ) ? max( 0, min( 100, absint( $input['ov_opacity'] ) ) ) : $defaults['ov_opacity'];

    $output['shadow_on']     = ! empty( $input['shadow_on'] ) ? '1' : '0';
    $output['shadow_amount'] = isset( $input['shadow_amount'] ) ? max( 0, min( 100, absint( $input['shadow_amount'] ) ) ) : $defaults['shadow_amount'];

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
