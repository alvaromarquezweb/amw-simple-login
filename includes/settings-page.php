<?php
/**
 * AMW Simple Login — admin settings page (Settings → AMW Login).
 *
 * @package AMW_Simple_Login
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
    wp_enqueue_style( 'wp-color-picker' );

    $css = AMW_LOGIN_DIR . 'assets/css/admin-settings.css';
    wp_enqueue_style(
        'amw-login-admin',
        AMW_LOGIN_URL . 'assets/css/admin-settings.css',
        [],
        file_exists( $css ) ? filemtime( $css ) : AMW_LOGIN_VERSION
    );

    $js = AMW_LOGIN_DIR . 'assets/js/admin-settings.js';
    wp_enqueue_script(
        'amw-login-admin',
        AMW_LOGIN_URL . 'assets/js/admin-settings.js',
        [ 'jquery', 'wp-color-picker' ],
        file_exists( $js ) ? filemtime( $js ) : AMW_LOGIN_VERSION,
        true
    );

    // Translated strings for the script (kept translatable and JS-safe).
    wp_localize_script( 'amw-login-admin', 'amwLoginAdmin', [
        'logo' => [
            'frameTitle' => __( 'Select logo', 'amw-simple-login' ),
            'frameBtn'   => __( 'Use this logo', 'amw-simple-login' ),
            'select'     => __( 'Select logo', 'amw-simple-login' ),
            'change'     => __( 'Change logo', 'amw-simple-login' ),
            'remove'     => __( 'Remove', 'amw-simple-login' ),
        ],
        'bg' => [
            'frameTitle' => __( 'Select background image', 'amw-simple-login' ),
            'frameBtn'   => __( 'Use this image', 'amw-simple-login' ),
            'select'     => __( 'Select image', 'amw-simple-login' ),
            'change'     => __( 'Change image', 'amw-simple-login' ),
            'remove'     => __( 'Remove', 'amw-simple-login' ),
        ],
        'downloadName' => 'amw-simple-login-settings.json',
    ] );
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

    // Initial labels for the logo buttons rendered on load. The rest of the
    // script strings are passed to admin-settings.js via wp_localize_script.
    $i18n = [
        'select' => __( 'Select logo', 'amw-simple-login' ),
        'change' => __( 'Change logo', 'amw-simple-login' ),
        'remove' => __( 'Remove', 'amw-simple-login' ),
    ];
    ?>
    <div class="wrap amw-settings">
        <div class="amw-admin-header">
            <a class="amw-brand-chip" href="https://alvaromarquezweb.com" target="_blank" rel="noopener noreferrer" aria-label="alvaromarquezweb.com">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 156 198" style="height:34px; width:auto; display:block;" aria-hidden="true" focusable="false"><path d="M90,0h-24L0,129.119995v68.880005l18-18v-24l24,24,27.120003-27.119995v45.119995l44.879997-44.880005,42,44.880005v-68.880005L90,0ZM78,24l27.120003,72s-8.872002,3.120003-26.879997,3.120003-27.120003-3.120003-27.120003-3.120003l26.879997-72ZM138,156l-24-26.880005-27.120003,26.880005v-44.880005l-44.880001,44.880005-24-24,30-30s11.916,3.120003,30.000004,3.120003,30-3.120003,30-3.120003l30,30v24Z" style="fill:#f7f7f7;fill-rule:evenodd"/></svg>
            </a>
            <div class="amw-brand-meta">
                <h1>
                    <?php esc_html_e( 'AMW Simple Login', 'amw-simple-login' ); ?>
                    <span class="amw-ver">v<?php echo esc_html( AMW_LOGIN_VERSION ); ?></span>
                </h1>
                <p>
                    <a href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open the login screen', 'amw-simple-login' ); ?></a>
                    <span class="amw-dot"> &middot; </span>
                    <a href="https://alvaromarquezweb.com" target="_blank" rel="noopener noreferrer">alvaromarquezweb.com</a>
                    <span class="amw-dot"> &middot; </span>
                    <a href="https://buymeacoffee.com/alvaromarquezweb" target="_blank" rel="noopener noreferrer">&#9749; <?php esc_html_e( 'Buy me a coffee', 'amw-simple-login' ); ?></a>
                </p>
            </div>
        </div>

        <nav class="nav-tab-wrapper amw-tabs">
            <a href="#" class="nav-tab nav-tab-active" data-amw-tab="diseno"><?php esc_html_e( 'Design', 'amw-simple-login' ); ?></a>
            <a href="#" class="nav-tab" data-amw-tab="fondo"><?php esc_html_e( 'Background', 'amw-simple-login' ); ?></a>
            <a href="#" class="nav-tab" data-amw-tab="contenido"><?php esc_html_e( 'Content', 'amw-simple-login' ); ?></a>
            <a href="#" class="nav-tab" data-amw-tab="seguridad"><?php esc_html_e( 'Security', 'amw-simple-login' ); ?></a>
            <a href="#" class="nav-tab" data-amw-tab="herramientas"><?php esc_html_e( 'Tools', 'amw-simple-login' ); ?></a>
        </nav>

        <form method="post" action="options.php">
            <?php settings_fields( 'amw_login_group' ); ?>

            <div class="amw-tab-panel amw-active" data-amw-panel="diseno">
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
                            <input type="text" class="amw-color-field" id="amw_<?php echo esc_attr( $key ); ?>" name="amw_login_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $opts[ $key ] ); ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h2><?php esc_html_e( 'Login box', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Shadow', 'amw-simple-login' ); ?></th>
                    <td>
                        <label><input type="checkbox" id="amw_shadow_on" name="amw_login_options[shadow_on]" value="1" <?php checked( $opts['shadow_on'], '1' ); ?>> <?php esc_html_e( 'Enable', 'amw-simple-login' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="amw_shadow_amount"><?php esc_html_e( 'Shadow amount', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="range" id="amw_shadow_amount" name="amw_login_options[shadow_amount]" min="0" max="100" step="1" value="<?php echo esc_attr( (int) $opts['shadow_amount'] ); ?>" style="vertical-align:middle; width:220px;" oninput="document.getElementById('amw_shadow_val').textContent = this.value;">
                        <code id="amw_shadow_val" style="margin-left:8px;"><?php echo esc_html( (int) $opts['shadow_amount'] ); ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="amw_shadow_color"><?php esc_html_e( 'Shadow color', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="text" class="amw-color-field" id="amw_shadow_color" name="amw_login_options[shadow_color]" value="<?php echo esc_attr( $opts['shadow_color'] ); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="amw_focus_color"><?php esc_html_e( 'Selected field color', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="text" class="amw-color-field" id="amw_focus_color" name="amw_login_options[focus_color]" value="<?php echo esc_attr( $opts['focus_color'] ); ?>">
                        <p class="description"><?php esc_html_e( 'Border and glow color of the focused input.', 'amw-simple-login' ); ?></p>
                    </td>
                </tr>
            </table>

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
            </div>

            <div class="amw-tab-panel" data-amw-panel="fondo">
            <h2><?php esc_html_e( 'Form position', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Position', 'amw-simple-login' ); ?></th>
                    <td>
                        <?php
                        $layouts = [
                            'left'   => __( 'Left', 'amw-simple-login' ),
                            'center' => __( 'Center', 'amw-simple-login' ),
                            'right'  => __( 'Right', 'amw-simple-login' ),
                        ];
                        foreach ( $layouts as $val => $label ) : ?>
                            <label style="display:inline-block; margin-right:16px;">
                                <input type="radio" class="amw-layout" name="amw_login_options[layout]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $opts['layout'], $val ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">
                            <?php esc_html_e( 'Where the login sits. In left and right, an image (if set) shows on the opposite side and a solid panel sits behind the form. Center has no panel. On mobile the form is always centered.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Background', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Type', 'amw-simple-login' ); ?></th>
                    <td>
                        <?php
                        $bg_types = [
                            'solid'    => __( 'Solid color', 'amw-simple-login' ),
                            'gradient' => __( 'Gradient', 'amw-simple-login' ),
                            'image'    => __( 'Image', 'amw-simple-login' ),
                        ];
                        foreach ( $bg_types as $val => $label ) : ?>
                            <label style="display:inline-block; margin-right:16px;">
                                <input type="radio" class="amw-bg-type" name="amw_login_options[bg_type]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $opts['bg_type'], $val ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>

                <tr class="amw-bg-fill">
                    <th scope="row"><label for="amw_bg_color1"><?php esc_html_e( 'Color / start', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="text" class="amw-color-field" id="amw_bg_color1" name="amw_login_options[bg_color1]" value="<?php echo esc_attr( $opts['bg_color1'] ); ?>">
                    </td>
                </tr>
                <tr class="amw-bg-grad">
                    <th scope="row"><?php esc_html_e( 'Gradient end & angle', 'amw-simple-login' ); ?></th>
                    <td>
                        <span style="margin-right:16px;"><?php esc_html_e( 'End', 'amw-simple-login' ); ?>
                            <input type="text" class="amw-color-field" id="amw_bg_color2" name="amw_login_options[bg_color2]" value="<?php echo esc_attr( $opts['bg_color2'] ); ?>"></span>
                        <label><?php esc_html_e( 'Angle', 'amw-simple-login' ); ?>
                            <input type="number" name="amw_login_options[bg_angle]" min="0" max="360" step="1" value="<?php echo esc_attr( (int) $opts['bg_angle'] ); ?>" style="width:80px;">&deg;</label>
                    </td>
                </tr>

                <tr class="amw-bg-image">
                    <th scope="row"><?php esc_html_e( 'Image', 'amw-simple-login' ); ?></th>
                    <td>
                        <input type="hidden" id="amw_bg_id" name="amw_login_options[bg_id]" value="<?php echo esc_attr( $bg_id ?: '' ); ?>">
                        <div id="amw-bg-preview" style="margin-bottom:10px;">
                            <?php if ( $bg_url ) : ?>
                                <img src="<?php echo esc_url( $bg_url ); ?>" style="max-width:280px; max-height:120px; display:block; border-radius:4px;">
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button" id="amw-bg-upload"><?php echo esc_html( $bg_id ? __( 'Change image', 'amw-simple-login' ) : __( 'Select image', 'amw-simple-login' ) ); ?></button>
                        <?php if ( $bg_id ) : ?>
                            <button type="button" class="button" id="amw-bg-remove" style="margin-left:6px;"><?php esc_html_e( 'Remove', 'amw-simple-login' ); ?></button>
                        <?php endif; ?>
                        <p class="description" style="margin-top:8px;"><?php esc_html_e( 'Use a light image: the login screen is not cached and loads fully on every visit.', 'amw-simple-login' ); ?></p>
                    </td>
                </tr>
                <tr class="amw-bg-image">
                    <th scope="row"><label for="amw_blur"><?php esc_html_e( 'Image blur', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="range" id="amw_blur" name="amw_login_options[blur]" min="0" max="20" step="1" value="<?php echo esc_attr( (int) $opts['blur'] ); ?>" style="vertical-align:middle; width:220px;" oninput="document.getElementById('amw_blur_val').textContent = this.value + ' px';">
                        <code id="amw_blur_val" style="margin-left:8px;"><?php echo esc_html( (int) $opts['blur'] ); ?> px</code>
                        <p class="description"><?php esc_html_e( 'From 0 (no blur) to 20. Combined with the overlay it gives a frosted-glass effect and improves legibility over busy photos.', 'amw-simple-login' ); ?></p>
                    </td>
                </tr>
                <tr class="amw-bg-image">
                    <th scope="row"><?php esc_html_e( 'Overlay', 'amw-simple-login' ); ?></th>
                    <td>
                        <?php
                        $ov_types = [ 'solid' => __( 'Solid color', 'amw-simple-login' ), 'gradient' => __( 'Gradient', 'amw-simple-login' ) ];
                        foreach ( $ov_types as $val => $label ) : ?>
                            <label style="display:inline-block; margin-right:16px;">
                                <input type="radio" class="amw-ov-type" name="amw_login_options[ov_type]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $opts['ov_type'], $val ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr class="amw-bg-image">
                    <th scope="row"><label for="amw_ov_color1"><?php esc_html_e( 'Color / start', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="text" class="amw-color-field" id="amw_ov_color1" name="amw_login_options[ov_color1]" value="<?php echo esc_attr( $opts['ov_color1'] ); ?>">
                    </td>
                </tr>
                <tr class="amw-bg-image amw-ov-grad">
                    <th scope="row"><?php esc_html_e( 'Gradient end & angle', 'amw-simple-login' ); ?></th>
                    <td>
                        <span style="margin-right:16px;"><?php esc_html_e( 'End', 'amw-simple-login' ); ?>
                            <input type="text" class="amw-color-field" id="amw_ov_color2" name="amw_login_options[ov_color2]" value="<?php echo esc_attr( $opts['ov_color2'] ); ?>"></span>
                        <label><?php esc_html_e( 'Angle', 'amw-simple-login' ); ?>
                            <input type="number" name="amw_login_options[ov_angle]" min="0" max="360" step="1" value="<?php echo esc_attr( (int) $opts['ov_angle'] ); ?>" style="width:80px;">&deg;</label>
                    </td>
                </tr>
                <tr class="amw-bg-image">
                    <th scope="row"><label for="amw_ov_opacity"><?php esc_html_e( 'Overlay opacity', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="range" id="amw_ov_opacity" name="amw_login_options[ov_opacity]" min="0" max="100" step="1" value="<?php echo esc_attr( (int) $opts['ov_opacity'] ); ?>" style="vertical-align:middle; width:220px;" oninput="document.getElementById('amw_ov_val').textContent = this.value + ' %';">
                        <code id="amw_ov_val" style="margin-left:8px;"><?php echo esc_html( (int) $opts['ov_opacity'] ); ?> %</code>
                    </td>
                </tr>
            </table>

            <div id="amw-panel-section">

            <h2><?php esc_html_e( 'Panel behind the login', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="amw_panel_color"><?php esc_html_e( 'Color', 'amw-simple-login' ); ?></label></th>
                    <td>
                        <input type="text" class="amw-color-field" id="amw_panel_color" name="amw_login_options[panel_color]" value="<?php echo esc_attr( $opts['panel_color'] ); ?>">
                        <p class="description"><?php esc_html_e( 'Solid color behind the form. Shown only in the left and right positions.', 'amw-simple-login' ); ?></p>
                    </td>
                </tr>
            </table>
            </div>
            </div>

            <div class="amw-tab-panel" data-amw-panel="contenido">
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
            </div>

            <div class="amw-tab-panel" data-amw-panel="seguridad">
            <h2><?php esc_html_e( 'Security', 'amw-simple-login' ); ?></h2>
            <p class="description" style="max-width:640px; margin-bottom:8px;">
                <?php esc_html_e( 'This plugin is not a security plugin and does not replace one. It only adds two lightweight measures on the login screen: a honeypot (a hidden bait field that bots fill in and real users never see, which rejects the attempt when filled) and a generic error message that never reveals whether the username or the password was wrong. The honeypot never affects XML-RPC or application-password logins.', 'amw-simple-login' ); ?>
            </p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Honeypot', 'amw-simple-login' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="amw_login_options[honeypot_on]" value="1" <?php checked( $opts['honeypot_on'], '1' ); ?>>
                            <?php esc_html_e( 'Enable the honeypot', 'amw-simple-login' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'On by default. It can also be forced on or off from code with the amw_login_honeypot_enabled filter.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            </div>

            <div class="amw-submit"><?php submit_button(); ?></div>
        </form>

        <div class="amw-tab-panel" data-amw-panel="herramientas">
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
        </div>
    </div>
    <?php
}
