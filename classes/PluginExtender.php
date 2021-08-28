<?php namespace Codecycler\Extend\Classes;

use Yaml;
use Event;

class PluginExtender
{
    private $controller;

    protected $model;

    protected $modelObj;

    public function __construct()
    {
        $this->load();
    }

    /**
     * This method reloads the extension config
     */
    public function load()
    {
        $this->model        = $this->model();
        $this->controller   = $this->controller();
    }

    public function model()
    {
        return null;
    }

    public function controller()
    {
        return null;
    }

    public function extendSubscribe()
    {
    }

    public function subscribe()
    {
        $this->extendFormFields();
        $this->extendListColumns();
        $this->extendRelationConfig();
        $this->extendRelations();
        $this->extendSubscribe();
        $this->extendMethods();
        $this->extendProperties();
    }

    public function addFields()
    {
        return [];
    }

    public function addTabFields()
    {
        return [];
    }

    public function addColumns()
    {
        return [];
    }

    public function addRelationConfig()
    {
        return [];
    }

    public function belongsToMany()
    {
        return [];
    }

    public function hasMany()
    {
        return [];
    }

    public function belongsTo()
    {
        return [];
    }

    public function methods()
    {
        return [];
    }

    public function properties()
    {
        return [];
    }

    /**
     * Private functions required by the extender
     */

    private function extendRelationConfig()
    {
        if (count($this->addRelationConfig()) < 1) {
            return;
        }

        $controller = new $this->controller;

        $addRelationController = false;
        $configExists = false;
        $configEmpty = true;
        $relationConfig = $this->addRelationConfig();

        if (!in_array('Backend\Behaviors\RelationController', $controller->implement) && !in_array('Backend.Behaviors.RelationController', $controller->implement)) {
            $addRelationController = true;
        }

        if (property_exists($controller, 'relationConfig')) {
            $configExists = true;
        }

        if ($configExists && $controller->relationConfig) {
            $configEmpty = false;
        }

        $controller::extend(function ($c) use ($addRelationController, $configExists, $configEmpty, $relationConfig, $controller) {
            if ($addRelationController) {
                array_push($c->implement, 'Backend\Behaviors\RelationController');
            }

            if (!$configExists) {
                $c->addDynamicProperty('relationConfig', $relationConfig);
            } else {
                if ($configEmpty) {
                    $c->relationConfig = $relationConfig;
                } else {
                    if (is_array($c->relationConfig)) {
                        $c->relationConfig = array_merge($relationConfig, $c->relationConfig);
                    }

                    if (is_string($c->relationConfig)) {
                        $controllerPath = plugins_path(strtolower(str_replace('\\', '/', get_class($controller)))) . '/' . $c->relationConfig;

                        $currentRelationConfig = Yaml::parseFile($controllerPath);

                        $c->relationConfig = array_merge($currentRelationConfig, $relationConfig);
                    }
                }
            }
        });
    }

    private function extendFormFields()
    {
        Event::listen('backend.form.extendFields', function ($formWidget, $formData) {
            if (!$formWidget->getController() instanceof $this->controller) {
                return;
            }

            if (!$formWidget->model instanceof $this->model) {
                return;
            }

            if ($formWidget->isNested) {
                return;
            }

            $formWidget->addFields($this->addFields());
            $formWidget->addTabFields($this->addTabFields());
        });
    }

    private function extendListColumns()
    {
        Event::listen('backend.list.extendColumns', function ($listWidget) {
            if (!$listWidget->getController() instanceof $this->controller) {
                return;
            }

            if (!$listWidget->model instanceof $this->model) {
                return;
            }

            if ($listWidget->isNested) {
                return;
            }

            $listWidget->addColumns($this->addColumns());
        });
    }

    private function extendRelations()
    {
        $model = $this->model;
        $belongsToMany = $this->belongsToMany();
        $hasMany = $this->hasMany();
        $belongsTo = $this->belongsTo();

        $model::extend(function ($model) use ($belongsToMany, $hasMany, $belongsTo) {
            $model->belongsToMany = array_merge($model->belongsToMany, $belongsToMany);
            $model->hasMany = array_merge($model->hasMany, $hasMany);
            $model->belongsTo = array_merge($model->belongsTo, $belongsTo);
        });
    }

    private function extendMethods()
    {
        $model = $this->model;
        $methods = $this->methods();

        $model::extend(function ($model) use ($methods) {
            foreach ($methods as $functionName => $method) {
                $model->addDynamicMethod($functionName, $method);
            }
        });
    }

    private function extendProperties()
    {
        $model = $this->model;
        $properties = $this->properties();

        $model::extend(function ($model) use ($properties) {
            $this->modelObj = $model;

            foreach ($properties as $propertyName => $method) {
                $model->addDynamicProperty($propertyName, $method);
            }
        });
    }
}
