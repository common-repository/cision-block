<?php

$settings = $this->settings->toOptionsArray();
unset($settings['notes']);
$output = '## Settings ##' . PHP_EOL;
foreach ($settings as $key => $value) {
    if (is_array($value)) {
        $value = implode(', ', $value);
    } else {
        $value = is_bool($value) ? (string)$value : $value;
    }
    $output .= $key . ': ' . ((string)$value) . PHP_EOL;
}
$output .= PHP_EOL;

// Get theme information.
$theme = wp_get_theme();

$output .= '## Wordpress ##' . PHP_EOL;
$output .= 'Version: ' . get_bloginfo('version') . PHP_EOL;
$output .= 'Multisite: ' . (is_multisite() ? 'yes' : 'no') . PHP_EOL;
$output .= 'Site address: ' . get_bloginfo('url') . PHP_EOL;
$output .= 'Debug mode: ' . (WP_DEBUG ? 'yes' : 'no') . PHP_EOL;
$output .= 'Memory limit: ' . WP_MEMORY_LIMIT . PHP_EOL;
$output .= 'Cron: ' . (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? 'no' : 'yes') . PHP_EOL;
$output .= 'Language: ' . get_locale() . PHP_EOL;
$output .= 'Theme: ' . $theme['Name'] . ' ' . $theme['Version'] . PHP_EOL;
$output .= 'Timezone: ' . wp_timezone()->getName() . PHP_EOL;
$output .= PHP_EOL;

// Get plugin information.
$plugins = get_plugins();
uasort($plugins, function ($a, $b) {
    return strcmp(strtolower($a['Name']), strtolower($b['Name']));
});
$activePlugins = array_values(get_option('active_plugins'));
foreach ($plugins as $key => $plugin) {
    $plugins[$key]['Active'] = in_array($key, $activePlugins);
}
$output .= '### Active Plugins ###' . PHP_EOL;
foreach ($plugins as $plugin) {
    if ($plugin['Active']) {
        $output .= $plugin['Name'] . ' ' . $plugin['Version'] . PHP_EOL;
    }
}
$output .= PHP_EOL;
$output .= '### Inactive Plugins ###' . PHP_EOL;
foreach ($plugins as $plugin) {
    if (!$plugin['Active']) {
        $output .= $plugin['Name'] . ' ' . $plugin['Version'] . PHP_EOL;
    }
}
$output .= PHP_EOL;

$output .= '## PHP ##' . PHP_EOL;
$output .= 'System name: ' . php_uname() . PHP_EOL;
$output .= 'Architecture: ' . (PHP_INT_SIZE === 8 ? 'x64' : 'x86') . PHP_EOL;
$output .= 'Version: ' . phpversion() . PHP_EOL;
$output .= 'Debug build: ' . __(defined('ZEND_DEBUG_BUILD') && ZEND_DEBUG_BUILD ? 'yes' : 'no') . PHP_EOL;
$output .= 'Zend Engine version: ' . zend_version() . PHP_EOL;
$output .= 'Server Api: ' . php_sapi_name() . PHP_EOL;
$variables = [
    'memory_limit',
    'variables_order',
    'max_execution_time',
    'max_input_time',
    'upload_max_filesize',
    'post_max_size',
    'safe_mode',
    'enable_dl',
    'register_globals',
    'expose_php',
    'display_errors',
    'file_uploads',
    'allow_url_fopen',
    'allow_url_include',
    'magic_quotes_gpc',
];

$extensions = get_loaded_extensions();
natcasesort($extensions);

$output .= PHP_EOL . '### Extensions ###' . PHP_EOL;
$output .= implode("\n", $extensions) . PHP_EOL;

$output .= PHP_EOL . '### Variables ###' . PHP_EOL;
foreach ($variables as $variable) {
    $output .= $variable . ': ' . ini_get($variable) . PHP_EOL;
}

?>
<div class="wrap">
    <?php $this->displayTabs(); ?>
    <form id="status-form" action="<?php echo admin_url('admin-post.php'); ?>" method="POST">
        <?php wp_nonce_field('cision-block-settings-action', 'cision-block-settings-nonce'); ?>
        <input type="hidden" name="action" value="cision_block_save_settings" />
        <table class="form-table">
            <tr>
                <td>
                    <textarea name="settings" readonly><?php echo $output; ?></textarea>
                    <p class="description"><?php _e('If you need help, copy and paste the above information for faster support.', 'cision-block'); ?></p>
                </td>
            </tr>
        </table>
        <button type="button" class="button" onclick="document.querySelector('textarea').select(); document.execCommand('copy');"><?php _e('Copy for support', 'cision-block'); ?></button>
    </form>
</div>
