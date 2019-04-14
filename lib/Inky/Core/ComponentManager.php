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

    protected $id;
    protected $actions = [];
    protected $filters = [];
    protected $components = [];
    protected $in_init = false;

    public function __construct($id = null) {
        if ($id) $this->id = $id;
    }

    public function set_id($id) {
        $this->id = $id;
        return $this;
    }

    public function get_id() {
        return $this->id;
    }

    public function on_init(Component $component, $cb) {
        if ($this->in_init) {
            call_user_func_array([ $component, $cb ], [ $this ]);
            return;
        }
        $this->add_action($this->id . '_init', $component, $cb, 10, 1);
    }

    /**
     * Add a component to the manager, registering its events.
     *
     * Note that you cannot add more then one component with the same ID! If 
     * a component already exists, the manager will simply skip adding it.
     *
     * @param Inky\Core\Component $component
     * @return $this
     */
    public function add_component(Component $component) {
        $id = $this->resolve_id(
            get_class($component), $component->get_component_id()
        );

        if ($this->has_component($id)) {
            return $this;
        }

        $this->components[$id] = $component;
        $component->register($this);
        
        return $this;
    }

    /**
     * Get a component by its id.
     * 
     * @example
     * 
     * // Components that use ids:
     * $manager->get_component(WebcomicComponent::class, 'webcomic');
     *
     * // Components that use class names only:
     * $manager->get_component(WebcomicBuilder::class);
     * 
     * @param string $type
     * @param string|null $id
     * @return Component|null
     */
    public function get_component($type, $id = null) {
        $id = $this->resolve_id($type, $id);
        return $this->has_component($id) ? $this->components[$id] : null;
    }

    /**
     * Check if a component has been registered.
     *
     * @param string $id
     * @param string|null $suffix
     * @return bool
     */
    public function has_component($type, $id = null) {
        $id = $this->resolve_id($type, $id);
        return isset($this->components[$id]);
    }

    /** 
     * Register an action for the given component.
     *
     * Actions added this way will NOT be registered with wordpress until
     * `ComponentManager::run()` is called.
     *
     * @param string $hook
     * @param Component $component
     * @param string $callback This must be a method in the provided component.
     * @param int $priority
     * @param int $accepted_args
     * @return $this
     */
    public function add_action($hook, Component $component, $callback, $priority = 10, $accepted_args = 1) {
        if ($this->in_init && $hook == 'init') {
            call_user_func_array([ $component, $callback ], []);
            return $this;
        }
        $this->actions[] = compact('hook', 'component', 'callback', 'priority', 'accepted_args');
        return $this;
    }

    /**
     * Register a filter for the given component.
     *
     * @param string $name
     * @param Component $component
     * @param string $callback This must be a method in the provided component.
     * @return $this
     */
    public function add_filter($name, Component $component, $callback) {
        $this->filters[] = compact('name', 'component', 'callback');
        return $this;
    }

    /**
     * Commit all registered actions and filters.
     * 
     * @return $this
     */
    public function commit() {
        foreach ($this->actions as $action) {
            add_action($action['hook'], [$action['component'], $action['callback']], $action['priority'], $action['accepted_args']);
        }
        $this->actions = [];
        
        foreach ($this->filters as $filter) {
            add_filter($filter['name'], [$filter['component'], $filter['callback']]);
        }
        $this->filters = [];

        return $this;
    }

    /**
     * Add all registered actions and filters to WordPress.
     *
     * @return $this
     */
    public function run() {
        $this->commit();
        
        add_action('init', function () {
            $this->in_init = true;
            do_action($this->id . '_init', $this);
            $this->in_init = false;
        }, 15);

        return $this;
    }

    protected function resolve_id($type, $id = null) {
        if ($id != null) {
            return "$type#$id";
        }
        return $type;
    }

}
