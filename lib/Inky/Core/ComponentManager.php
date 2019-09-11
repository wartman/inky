<?php

namespace Inky\Core;

/**
 * Handles the registration of Components and their interaction with the
 * WordPress action API.
 *
 * @since 0.0.1
 */
class ComponentManager {

    public static function get_instance() {
        static $manager;
        if ($manager == null) {
            $manager = new ComponentManager('inky');
        }
        return $manager;
    }

    public $init;
    public $setup;
    public $after_setup;
    public $register_post_types;
    public $after_register_post_types;

    protected $id;
    protected $actions = [];
    protected $filters = [];
    protected $components = [];

    public function __construct($id = null) {
        if ($id) $this->id = $id;

        $this->init = new Action('init');
        $this->add_action($this->init);

        $this->setup = new Action("{$this->id}_setup");
        $this->setup->inject($this);
        $this->add_action($this->setup);

        $this->after_setup = new Action("{$this->id}_after_setup");
        $this->after_setup->inject($this);
        $this->add_action($this->after_setup);

        $this->register_post_types = new Action("{$this->id}_register_post_types");
        $this->register_post_types->inject($this);
        $this->add_action($this->register_post_types);

        $this->after_register_post_types = new Action("{$this->id}_after_register_post_types");
        $this->after_register_post_types->inject($this);
        $this->add_action($this->after_register_post_types);
    }

    public function set_id($id) {
        $this->id = $id;
        return $this;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_plugin_file() {
        return dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'inky.php';
    }

    /**
     * Add a component to the manager, registering its events.
     *
     * Note that you cannot add more then one component with the same ID! If 
     * a component already exists, the manager will simply skip adding it.
     *
     * Look into using SubComponents if you need many similar components
     * (using the `Inky\Core\HasSubComponents` trait). `Inky\Collection\WebcomicCollection`
     * is a good example of this in action.
     * 
     * @param Inky\Core\Component $component
     * @return $this
     */
    public function add_component(Component $component) {
        $class_name = get_class($component);

        if ($this->has_component($class_name)) {
            return $this;
        }

        $this->components[$class_name] = $component;
        $component->register($this);
        
        return $this;
    }

    /**
     * Get a component by its class name.
     * 
     * @example
     * $manager->get_component(WebcomicBuilder::class);
     * 
     * @param string $class_name
     * @return Component|null
     */
    public function get_component($class_name) {
        return $this->has_component($class_name) 
            ? $this->components[$class_name] 
            : null;
    }

    /**
     * Check if a component has been registered.
     *
     * @param string $class_name
     * @return bool
     */
    public function has_component($class_name) {
        return isset($this->components[$class_name]);
    }

    public function add_action(Action $action) {
        $this->actions[] = $action;
        return $this;
    }

    public function add_filter(Filter $filter) {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Commit all registered actions and filters.
     * 
     * @return $this
     */
    public function commit() {
        foreach ($this->actions as $action) {
            $action->commit();
        }

        foreach ($this->filters as $filter) {
            $filter->commit();
        }

        return $this;
    }

    /**
     * Add all registered actions and filters to WordPress.
     *
     * @return $this
     */
    public function run() {
        $this->commit();
        
        $this->setup->trigger();

        add_action('init', function () {
            $this->register_post_types->trigger();
            $this->after_register_post_types->trigger();
        });

        return $this;
    }

}
