<?php

/**
 * Plugin Name: Cision Block
 * Description: Imports a news feed from Cision.
 * Version: 4.2.0
 * Author: Cyclonecode
 * Author URI: https://stackoverflow.com/users/1047662/cyclonecode?tab=profile
 * Copyright: Cyclonecode
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cision-block
 * Domain Path: /languages
 *
 * @package Cision Block
 * @author Cyclonecode
 */

if ( ! function_exists( 'cb_fs' ) ) {
    // Create a helper function for easy SDK access.
    function cb_fs() {
        global $cb_fs;

        if ( ! isset( $cb_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $cb_fs = fs_dynamic_init( array(
                'id'                  => '8329',
                'slug'                => 'cision-block',
                'premium_slug'        => 'cision-block-premium',
                'type'                => 'plugin',
                'public_key'          => 'pk_1c2e5707ce111a3a578a1fad57cc0',
                'is_premium'          => false,
                'has_addons'          => true,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'cision-block',
                    'override_exact' => true,
                    'parent'         => array(
                        'slug' => 'options-general.php',
                    ),
                ),
            ) );
        }

        return $cb_fs;
    }

    // Init Freemius.
    cb_fs();
    // Signal that SDK was initiated.
    do_action( 'cb_fs_loaded' );

    function cb_fs_settings_url() {
        return admin_url( 'options-general.php?page=cision-block' );
    }

    cb_fs()->add_filter('connect_url', 'cb_fs_settings_url');
    cb_fs()->add_filter('after_skip_url', 'cb_fs_settings_url');
    cb_fs()->add_filter('after_connect_url', 'cb_fs_settings_url');
    cb_fs()->add_filter('after_pending_connect_url', 'cb_fs_settings_url');
}

require_once __DIR__ . '/vendor/autoload.php';

use CisionBlock\Backend\Backend;
use CisionBlock\Frontend\Frontend;
use CisionBlock\DI\ContainerBuilder;

$builder = new ContainerBuilder();
$builder->addDefinitions(__DIR__ . '/config/config.php');
if (defined('CISION_BLOCK_COMPILE')) {
    $builder->enableCompilation(sys_get_temp_dir());
}

function cb_get_container()
{
    return $GLOBALS['cb_container'];
}

$GLOBALS['cb_container'] = $builder->build();

add_action('plugins_loaded', function () {
    if (is_admin()) {
        cb_get_container()->get(Backend::class);
    } else {
        cb_get_container()->get(Frontend::class);
    }
});

register_activation_hook(__FILE__, array('CisionBlock\Backend\Backend', 'activate'));
register_uninstall_hook(__FILE__, array('CisionBlock\Backend\Backend', 'delete'));
