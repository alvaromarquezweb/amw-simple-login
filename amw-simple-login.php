<?php
/**
 * Plugin Name: AMW Simple Login
 * Plugin URI:  https://alvaromarquezweb.com
 * Description: Personalización del login de WordPress. Configurable desde Ajustes → AMW Login.
 * Version:     1.0.0
 * Author:      Álvaro Márquez Díaz
 * Author URI:  https://alvaromarquezweb.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: amw-simple-login
 */

// ── AUTOACTUALIZACIÓN DESDE GITHUB ──────────────────────────────────
require plugin_dir_path( __FILE__ ) . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$amwUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/alvaromarquezweb/amw-simple-login/',
    __FILE__,
    'amw-simple-login'                                  
);
$amwUpdateChecker->setBranch( 'main' );

// Repositorio público: PUC no necesita autenticación (sin setAuthentication).

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AMW_LOGIN_VERSION', '1.0.0' );
define( 'AMW_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AMW_LOGIN_URL', plugin_dir_url( __FILE__ ) );

// Ancho del logo en px. Se inyecta como variable CSS, así que basta con cambiarlo aquí.
define( 'AMW_LOGIN_LOGO_WIDTH', 160 );


// ─────────────────────────────────────────────────────────────
// 1. OPCIONES (siempre disponibles, sin hooks)
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
        'layout'                 => 'left', // left | center | right (solo aplica si hay imagen)
        'blur'                   => 0,      // desenfoque de la imagen en px (0-20)
        'overlay_color'          => '#0a0a0a',
        'overlay_opacity'        => 55,     // intensidad del oscurecido 0-100
    ];
}

function amw_login_color_keys() {
    return [
        'color_bg'          => 'Fondo de página',
        'color_surface'     => 'Fondo del formulario',
        'color_surface_alt' => 'Fondo de avisos y mensajes',
        'color_input_bg'    => 'Fondo de los campos',
        'color_border'      => 'Color de bordes',
        'color_text'        => 'Texto principal',
        'color_muted'       => 'Texto secundario / labels',
        'color_accent'      => 'Botón (fondo)',
        'color_accent_fg'   => 'Botón (texto)',
    ];
}

function amw_login_get_options() {
    return wp_parse_args( get_option( 'amw_login_options', [] ), amw_login_defaults() );
}

function amw_login_sanitize_options( $input ) {
    $defaults = amw_login_defaults();
    $output   = [];

    foreach ( array_keys( amw_login_color_keys() ) as $key ) {
        $val            = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
        $output[ $key ] = $val ?: $defaults[ $key ];
    }

    // Radio de curvatura: entero acotado, nunca un valor arbitrario en el CSS.
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

    return $output;
}

/**
 * Devuelve un color listo para imprimir dentro de <style>.
 * sanitize_hex_color es el escapado correcto en contexto CSS (esc_attr no lo es).
 */
function amw_login_css_color( $opts, $key ) {
    $defaults = amw_login_defaults();
    $color    = sanitize_hex_color( isset( $opts[ $key ] ) ? $opts[ $key ] : '' );
    return $color ?: $defaults[ $key ];
}

/**
 * Resuelve el logo del login y sus dimensiones.
 * Prioridad: 1) opción del plugin  2) Personalizador  2b) Logo de Divi  3) SVG de reserva en línea.
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

    // 1. Logo configurado en el plugin.
    if ( ! empty( $opts['logo_id'] ) ) {
        $src = wp_get_attachment_image_src( (int) $opts['logo_id'], 'full' );
        if ( $src ) {
            $data['url']    = $src[0];
            $data['height'] = $calc_height( $src[1], $src[2] );
            return $data;
        }
    }

    // 2. Logo del Personalizador (Identidad del sitio).
    $theme_logo_id = get_theme_mod( 'custom_logo' );
    if ( $theme_logo_id ) {
        $src = wp_get_attachment_image_src( $theme_logo_id, 'full' );
        if ( $src ) {
            $data['url']    = $src[0];
            $data['height'] = $calc_height( $src[1], $src[2] );
            return $data;
        }
    }

    // 2b. Logo de Divi (Opciones del tema). Divi no usa el custom_logo del core:
    //     guarda la URL del logo en la opción et_divi['divi_logo'].
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
        // Es una URL; si está en la biblioteca de medios sacamos las dimensiones
        // reales para calcular la altura, y si no dejamos la de por defecto.
        $att_id = attachment_url_to_postid( $divi_logo );
        if ( $att_id ) {
            $src = wp_get_attachment_image_src( $att_id, 'full' );
            if ( $src ) {
                $data['height'] = $calc_height( $src[1], $src[2] );
            }
        }
        return $data;
    }

    // 3. Reserva: logo SVG en línea (sin archivos ni carpeta assets).
    $data['url']    = amw_login_fallback_logo_uri();
    $data['height'] = $calc_height( 120, 60 ); // proporción del viewBox del SVG

    return $data;
}

/**
 * Logo de reserva como SVG en línea (data URI). Sin archivos, sin carpeta assets.
 * Toma el color del texto configurado, así se adapta a la paleta del tema.
 * Para usar tu propio logo, sustituye el contenido de $svg por tu SVG
 * (exportado desde Affinity); usa $color en los "fill" que quieras que sigan al tema.
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
 * URL de la imagen de fondo del login, o '' si no hay ninguna configurada.
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
// 2. PANTALLA DE AJUSTES (solo en el escritorio)
// ─────────────────────────────────────────────────────────────

if ( is_admin() ) {
    add_action( 'admin_menu', 'amw_login_menu' );
    add_action( 'admin_init', 'amw_login_register_settings' );
    add_action( 'admin_enqueue_scripts', 'amw_login_admin_scripts' );
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

function amw_login_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $opts     = amw_login_get_options();
    $logo_id  = ! empty( $opts['logo_id'] ) ? (int) $opts['logo_id'] : 0;
    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
    $bg_id    = ! empty( $opts['bg_id'] ) ? (int) $opts['bg_id'] : 0;
    $bg_url   = $bg_id ? wp_get_attachment_image_url( $bg_id, 'medium' ) : '';

    // Cadenas para el script: se calculan aquí para que sean traducibles de verdad
    // y se escapan con esc_js(), que es lo correcto en contexto JavaScript.
    $i18n = [
        'select'      => __( 'Seleccionar logo', 'amw-simple-login' ),
        'change'      => __( 'Cambiar logo', 'amw-simple-login' ),
        'remove'      => __( 'Eliminar', 'amw-simple-login' ),
        'frame_title' => __( 'Seleccionar logo', 'amw-simple-login' ),
        'frame_btn'   => __( 'Usar este logo', 'amw-simple-login' ),
    ];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'AMW Simple Login', 'amw-simple-login' ); ?></h1>
        <p>
            <a href="<?php echo esc_url( wp_login_url() ); ?>" target="_blank" rel="noopener">
                <?php esc_html_e( 'Ver la pantalla de acceso', 'amw-simple-login' ); ?>
            </a>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields( 'amw_login_group' ); ?>

            <h2><?php esc_html_e( 'Logo', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Logo del login', 'amw-simple-login' ); ?></th>
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
                            <?php esc_html_e( 'Si no seleccionas un logo, se usará el del Personalizador (Identidad del sitio). Si tampoco hay ninguno, se mostrará el logo de reserva del plugin.', 'amw-simple-login' ); ?>
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

                // Delegado: funciona igual si el botón se crea después.
                $(document).on('click', '#amw-logo-remove', function(e) {
                    e.preventDefault();
                    $('#amw_logo_id').val('');
                    $('#amw-logo-preview').empty();
                    $('#amw-logo-upload').text('<?php echo esc_js( $i18n['select'] ); ?>');
                    $(this).remove();
                });
            });
            </script>

            <h2><?php esc_html_e( 'Imagen de fondo', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Imagen', 'amw-simple-login' ); ?></th>
                    <td>
                        <input type="hidden" id="amw_bg_id" name="amw_login_options[bg_id]" value="<?php echo esc_attr( $bg_id ?: '' ); ?>">

                        <div id="amw-bg-preview" style="margin-bottom:10px;">
                            <?php if ( $bg_url ) : ?>
                                <img src="<?php echo esc_url( $bg_url ); ?>" style="max-width:280px; max-height:120px; display:block; border-radius:4px;">
                            <?php endif; ?>
                        </div>

                        <button type="button" class="button" id="amw-bg-upload">
                            <?php echo esc_html( $bg_id ? __( 'Cambiar imagen', 'amw-simple-login' ) : __( 'Seleccionar imagen', 'amw-simple-login' ) ); ?>
                        </button>
                        <?php if ( $bg_id ) : ?>
                            <button type="button" class="button" id="amw-bg-remove" style="margin-left:6px;">
                                <?php esc_html_e( 'Quitar', 'amw-simple-login' ); ?>
                            </button>
                        <?php endif; ?>

                        <p class="description" style="margin-top:8px;">
                            <?php esc_html_e( 'Sin imagen, el login se muestra centrado sobre el fondo oscuro de siempre. Usa una imagen ligera: la pantalla de acceso no se cachea y se carga entera en cada acceso.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Disposición del formulario', 'amw-simple-login' ); ?></th>
                    <td>
                        <?php
                        $layouts = [
                            'left'   => __( 'Izquierda (imagen a la derecha)', 'amw-simple-login' ),
                            'center' => __( 'Centro (imagen a pantalla completa)', 'amw-simple-login' ),
                            'right'  => __( 'Derecha (imagen a la izquierda)', 'amw-simple-login' ),
                        ];
                        foreach ( $layouts as $val => $label ) : ?>
                            <label style="display:block; margin-bottom:4px;">
                                <input type="radio" name="amw_login_options[layout]" value="<?php echo esc_attr( $val ); ?>" <?php checked( $opts['layout'], $val ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">
                            <?php esc_html_e( 'Solo tiene efecto cuando hay imagen. En móvil siempre se muestra el formulario centrado sobre la imagen.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="amw_blur"><?php esc_html_e( 'Desenfoque de la imagen', 'amw-simple-login' ); ?></label>
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
                            <?php esc_html_e( 'De 0 (sin desenfoque) a 20. Combinado con el oscurecido da un efecto cristal esmerilado y mejora la legibilidad sobre fotos con mucho detalle.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="amw_overlay_color"><?php esc_html_e( 'Color del oscurecido', 'amw-simple-login' ); ?></label>
                    </th>
                    <td>
                        <input type="color" id="amw_overlay_color" name="amw_login_options[overlay_color]" value="<?php echo esc_attr( $opts['overlay_color'] ); ?>">
                        <code style="margin-left:8px;"><?php echo esc_html( $opts['overlay_color'] ); ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="amw_overlay_opacity"><?php esc_html_e( 'Intensidad del oscurecido', 'amw-simple-login' ); ?></label>
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
                            <?php esc_html_e( 'Capa de color sobre la imagen. Sube la intensidad para oscurecer más la foto; el color puede ser tu tono de marca en vez de negro.', 'amw-simple-login' ); ?>
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
                        title: '<?php echo esc_js( __( 'Seleccionar imagen de fondo', 'amw-simple-login' ) ); ?>',
                        button: { text: '<?php echo esc_js( __( 'Usar esta imagen', 'amw-simple-login' ) ); ?>' },
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
                        $('#amw-bg-upload').text('<?php echo esc_js( __( 'Cambiar imagen', 'amw-simple-login' ) ); ?>');
                        if ( ! $('#amw-bg-remove').length ) {
                            $('#amw-bg-upload').after(
                                '<button type="button" class="button" id="amw-bg-remove" style="margin-left:6px;"><?php echo esc_js( __( 'Quitar', 'amw-simple-login' ) ); ?></button>'
                            );
                        }
                    });
                    bgFrame.open();
                });

                $(document).on('click', '#amw-bg-remove', function(e) {
                    e.preventDefault();
                    $('#amw_bg_id').val('');
                    $('#amw-bg-preview').empty();
                    $('#amw-bg-upload').text('<?php echo esc_js( __( 'Seleccionar imagen', 'amw-simple-login' ) ); ?>');
                    $(this).remove();
                });
            });
            </script>

            <h2><?php esc_html_e( 'Paleta de colores', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <?php foreach ( amw_login_color_keys() as $key => $label ) : ?>
                    <tr>
                        <th scope="row">
                            <label for="amw_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
                        </th>
                        <td>
                            <input type="color" id="amw_<?php echo esc_attr( $key ); ?>" name="amw_login_options[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $opts[ $key ] ); ?>">
                            <code style="margin-left:8px;"><?php echo esc_html( $opts[ $key ] ); ?></code>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h2><?php esc_html_e( 'Opciones de interfaz', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="amw_radius"><?php esc_html_e( 'Redondeo de esquinas', 'amw-simple-login' ); ?></label>
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
                            <?php esc_html_e( 'De 0 (esquinas rectas) a 24. Los campos y el botón usan un redondeo algo menor para que la caja no quede desproporcionada.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Ocultar "Volver a [sitio]"', 'amw-simple-login' ); ?></th>
                    <td><input type="checkbox" name="amw_login_options[hide_backtoblog]" value="1" <?php checked( $opts['hide_backtoblog'], '1' ); ?>></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Ocultar "¿Olvidaste tu contraseña?"', 'amw-simple-login' ); ?></th>
                    <td>
                        <input type="checkbox" name="amw_login_options[hide_forgotpw]" value="1" <?php checked( $opts['hide_forgotpw'], '1' ); ?>>
                        <p class="description">
                            <?php esc_html_e( 'Solo oculta el enlace en la pantalla de acceso. La recuperación de contraseña sigue funcionando por URL directa.', 'amw-simple-login' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Links legales al pie del login', 'amw-simple-login' ); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Mostrar links legales', 'amw-simple-login' ); ?></th>
                    <td><input type="checkbox" name="amw_login_options[show_legal]" value="1" <?php checked( $opts['show_legal'], '1' ); ?>></td>
                </tr>
                <?php
                $legal_fields = [
                    'aviso'      => 'Aviso Legal',
                    'privacidad' => 'Política de Privacidad',
                    'cookies'    => 'Política de Cookies',
                ];
                foreach ( $legal_fields as $key => $default_label ) :
                    $url_key   = 'legal_' . $key;
                    $label_key = 'legal_' . $key . '_label';
                    ?>
                    <tr>
                        <th scope="row"><?php echo esc_html( $default_label ); ?></th>
                        <td>
                            <input type="text" name="amw_login_options[<?php echo esc_attr( $label_key ); ?>]" value="<?php echo esc_attr( $opts[ $label_key ] ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $default_label ); ?>" style="width:180px;">
                            <input type="text" name="amw_login_options[<?php echo esc_attr( $url_key ); ?>]" value="<?php echo esc_attr( $opts[ $url_key ] ); ?>" class="regular-text" placeholder="/url-de-la-pagina/" style="width:260px; margin-left:8px;">
                            <p class="description"><?php esc_html_e( 'Texto del enlace · URL de la página', 'amw-simple-login' ); ?></p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


// ─────────────────────────────────────────────────────────────
// 3. LOGIN
//    Todo lo de abajo se registra dentro de login_init, así que
//    nada de esto llega al front ni al escritorio.
// ─────────────────────────────────────────────────────────────

add_action( 'login_init', 'amw_login_init' );

function amw_login_init() {
    add_action( 'login_enqueue_scripts', 'amw_login_estilos' );
    add_action( 'login_head', 'amw_login_dynamic_styles' );
    add_action( 'login_footer', 'amw_login_footer_legal' );

    add_filter( 'login_headerurl', function() {
        return home_url( '/' );
    } );

    add_filter( 'login_headertext', function() {
        return esc_html( get_bloginfo( 'name', 'display' ) );
    } );

    add_filter( 'login_display_language_dropdown', '__return_false' );

    add_filter( 'login_body_class', 'amw_login_body_class' );

    // Estos dos filtros son globales por naturaleza: registrados fuera de
    // login_init dejarían sin enlace la política de privacidad en todo el sitio.
    add_filter( 'the_privacy_policy_link', '__return_empty_string', PHP_INT_MAX );
    add_filter( 'privacy_policy_url', '__return_empty_string', PHP_INT_MAX ); // antes: wp_get_privacy_policy_url (no existe)

    add_filter( 'login_errors', 'amw_login_generic_error' );

    // Título de la pestaña sin el "— WordPress".
    add_filter( 'login_title', function( $login_title, $title ) {
        return $title . ' ‹ ' . get_bloginfo( 'name', 'display' );
    }, 10, 2 );
}

/**
 * Clases del <body> del login según haya imagen de fondo y su disposición.
 * Sin imagen no añade nada, así que se mantiene el login centrado de siempre.
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
 * Mensaje genérico solo para errores de credenciales.
 * El resto (cookies bloqueadas, 2FA, avisos de plugins de seguridad) pasa intacto.
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
        return __( 'Las credenciales introducidas no son correctas.', 'amw-simple-login' );
    }

    return $error;
}

function amw_login_estilos() {
    $css_file = AMW_LOGIN_DIR . 'login.css';
    $version  = file_exists( $css_file ) ? filemtime( $css_file ) : AMW_LOGIN_VERSION;
    wp_enqueue_style( 'amw-login', AMW_LOGIN_URL . 'login.css', [], $version );
}

/**
 * Acción actual de wp-login.php (login, lostpassword, resetpass, register...).
 */
function amw_login_current_action() {
    $action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
    return $action ?: 'login';
}

/**
 * Variables CSS + logo + reglas condicionales, en un único bloque.
 * Se imprime en login_head (prioridad 10), después de login.css, así que gana.
 */
function amw_login_dynamic_styles() {
    $opts = amw_login_get_options();
    $logo = amw_login_logo_data();
    $bg   = amw_login_bg_url();

    // Overlay como rgba (más compatible que color-mix): color y opacidad configurables.
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
        <?php if ( '1' === $opts['hide_backtoblog'] ) : ?>
        #backtoblog { display: none !important; }
        <?php endif; ?>
        <?php if ( '1' === $opts['hide_forgotpw'] && 'login' === amw_login_current_action() ) : ?>
        /* Solo en la pantalla de acceso: en lostpassword, #nav contiene la vuelta al login. */
        #nav { display: none !important; }
        <?php endif; ?>

        /* Enlaces legales: inline y autosuficientes, para que la optimización de
           CSS de plugins de caché (LiteSpeed UCSS/async) no los deje sin estilo.
           Posicionados en absoluto porque el <p> lo pinta login_footer FUERA de #login. */
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
        /* ── Imagen de fondo / disposición (inline: inmune a la poda de CSS) ──
           Capa 1 (::before): imagen + oscurecido juntos, con desenfoque.
           Capa 2 (::after): color de fondo sólido de la columna del formulario,
           POR ENCIMA de la imagen para que el borde quede siempre limpio.
           La caja del login (#login) queda por encima de ambas. */
        body.login.amw-has-bg::before {
            content: ""; position: fixed; top: 0; bottom: 0; z-index: -2;
            background:
                linear-gradient(var(--amw-overlay), var(--amw-overlay)),
                var(--amw-bg-image);
            background-size: cover;
            background-position: center;
            filter: blur(var(--amw-blur, 0px));
            /* Sobredimensiona un pelín para que el desenfoque no deje halo en los bordes. */
            transform: scale(1.06);
        }
        body.login.amw-has-bg::after {
            content: ""; position: fixed; top: 0; bottom: 0; z-index: -1;
            background: var(--amw-bg);
        }

        /* Izquierda: imagen a la derecha, panel sólido del formulario a la izquierda. */
        body.login.amw-layout-left.amw-has-bg::before { left: var(--amw-col); right: 0; }
        body.login.amw-layout-left.amw-has-bg::after  { left: 0; width: var(--amw-col); }
        body.login.amw-layout-left.amw-has-bg #login,
        body.login.amw-layout-left.amw-has-bg .amw-legal-links { left: calc(var(--amw-col) / 2); }

        /* Derecha: espejo. */
        body.login.amw-layout-right.amw-has-bg::before { left: 0; right: var(--amw-col); }
        body.login.amw-layout-right.amw-has-bg::after  { right: 0; left: auto; width: var(--amw-col); }
        body.login.amw-layout-right.amw-has-bg #login,
        body.login.amw-layout-right.amw-has-bg .amw-legal-links { left: calc(100% - var(--amw-col) / 2); }

        /* Centro: imagen a pantalla completa, sin panel de columna. */
        body.login.amw-layout-center.amw-has-bg::before { left: 0; right: 0; }
        body.login.amw-layout-center.amw-has-bg::after  { display: none; }

        /* Móvil: una sola columna, imagen a pantalla completa, sin panel. */
        @media (max-width: 782px) {
            body.login.amw-has-bg::before { left: 0 !important; right: 0 !important; }
            body.login.amw-has-bg::after  { display: none !important; }
            body.login.amw-has-bg #login,
            body.login.amw-has-bg .amw-legal-links { left: 50% !important; }
        }
        <?php endif; ?>
    </style>
    <?php
}


// ─────────────────────────────────────────────────────────────
// 4. FOOTER LEGAL
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