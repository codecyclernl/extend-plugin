<?php namespace Codecycler\Extend\Classes;

use Event;
use System\Classes\PluginManager;
use October\Rain\Support\Traits\Singleton;

class ExtensionManager
{
    use Singleton;

    protected $extenders = [];

    public function load()
    {
        //
        $plugins = PluginManager::instance()
            ->getAllPlugins();

        //
        foreach ($plugins as $plugin) {
            if (method_exists($plugin, 'registerExtenders')) {
                $this->register($plugin->registerExtenders());
            }
        }
    }

    public function register($extenders)
    {
        if (!is_array($extenders)) {
            $extenders = [$extenders];
        }

        foreach ($extenders as $extender) {
            if (in_array($extender, $this->extenders)) {
                continue;
            }

            $this->extenders[] = $extender;
        }
    }

    public function bind()
    {
        foreach ($this->get() as $extender) {
            Event::subscribe($extender);
        }
    }

    public function get()
    {
        return $this->extenders;
    }
}