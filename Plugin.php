<?php namespace Codecycler\Extend;

use Backend;
use System\Classes\PluginBase;
use Codecycler\Extend\Classes\ExtensionManager;

/**
 * Extend Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Extend',
            'description' => 'Extending October CMS plugins tool',
            'author'      => 'Codecycler',
            'icon'        => 'icon-external-link-square'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
        ExtensionManager::instance()
            ->load();
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        ExtensionManager::instance()
            ->bind();
    }
}
