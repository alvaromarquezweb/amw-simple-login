<?php
/**
 * Desinstalación de AMW Simple Login.
 * Borra la opción del plugin para no dejar residuos en la base de datos.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

if ( is_multisite() ) {
    $sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
    foreach ( $sites as $site_id ) {
        switch_to_blog( $site_id );
        delete_option( 'amw_login_options' );
        restore_current_blog();
    }
} else {
    delete_option( 'amw_login_options' );
}