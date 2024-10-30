<?php

namespace CisionBlock\Addon;

use CisionBlock\Backend\Backend;
use CisionBlock\GuzzleHttp\Client;
use CisionBlock\Plugin\Settings\Settings;

abstract class AbstractAddon implements AddonInterface
{
    /**
     * @var Settings
     */
    protected Settings $settings;

    /**
     * @var string
     */
    protected string $moduleName;

    /**
     * @var Client
     */
    protected Client $client;

    public function __construct()
    {
        $this->client = cb_get_container()->get(Client::class);

        $className = get_class($this);
        $this->settings = cb_get_container()->make(Settings::class, ['optionName' => $className::SETTINGS_NAME]);
        $this->moduleName = strtolower(substr($className, strrpos($className, '\\') + 1));

        add_action('admin_post_cision_block_' . $this->moduleName . '_save', function () {
            // Validate so user has correct privileges.
            if (!current_user_can('manage_options')) {
                die(__('You are not allowed to perform this action.', 'cision-block'));
            }
            $this->saveSettings($_POST);
            wp_safe_redirect(add_query_arg([
                'page' => Backend::MENU_SLUG,
                'tab' => $this->getModuleName(),
            ], Backend::PARENT_MENU_SLUG));
        });
    }

    public function delete(): void
    {
        $this->settings->delete();
    }

    /**
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * @return string
     */
    public function getBaseDir(): string
    {
        // include_once $module->getBaseDir() . '/templates/settings.php';
        $className = get_class($this);
        $moduleName = substr($className, strrpos($className, '\\') + 1);
        return trailingslashit(__DIR__) . $moduleName . '/' . $moduleName;
    }

    /**
     * @return Settings
     */
    public function getSettings(): Settings
    {
        return $this->settings;
    }

    public function saveSettings(array $args = []): void
    {
        static::saveSettings($_POST);
    }

    public function renderSettings(): void
    {
        // TODO: Implement renderSettings() method.
    }

    /**
     * @return array
     */
    public function getTabs(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getEndpoints(): array
    {
        return [];
    }
}
