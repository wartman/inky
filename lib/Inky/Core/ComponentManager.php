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

    protected $custom_actions = [];
    protected $id;
    protected $actions = [];
    protected $filters = [];
    protected $components = [];
    protected $in_init = false;

    public function __construct($id = null) {
        if ($id) $this->id = $id;
        $this->register_action('@register_post_types');
        $this->register_action('@after_register_post_types');
        $this->register_action('@run');
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
     * @param Inky\Core\Component $component
     * @return $this
     */
    public function add_component(Component $component) {
        $id = $this->format_id(
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
        $id = $this->format_id($type, $id);
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
        $id = $this->format_id($type, $id);
        return isset($this->components[$id]);
    }

    /**
     * Register a custom action. 
     * 
     * Registered actions will be prefixed with this manager's ID, ensuring there
     * won't be any conflicts.
     * 
     * @param string $name The name of the action.
     * @param string|false $during The name of the Wordpress action to fire this action during.
     *                             If `false`, will not be registered and you'll have to fire 
     *                             it yourself.
     * @return $this
     */
    public function register_action($name, $during = false) {
        $this->custom_actions[] = $name;
        if ($during === false) {
            return $this;
        }
        $this->add_action($during, function() use ($name) {
            $this->do_action($name);
        });
        return $this;
    }

    /** 
     * Register an action for the given component.
     *
     * Actions added this way will NOT be registered with wordpress until
     * `ComponentManager::run()` is called.
     * 
     * Actions prefixed with `@` are injected actions -- they are run where the normal
     * action would be (unless they are a custom action, registerd with `register_action`), 
     * but receive an instance of `ComponentManager` as their last argument.
     *
     * @param string $hook
     * @param Callable $callback
     * @param int $priority
     * @param int $accepted_args
     * @return $this
     */
    public function add_action($hook, Callable $callback, $priority = 10, $accepted_args = 1) {
        $is_injected = $this->should_inject($hook);
        $hook = $this->format_action_name($hook);
        $this->actions[] = compact('hook', 'callback', 'priority', 'accepted_args', 'is_injected');
        return $this;
    }

    /**
     * Register a filter for the given component.
     *
     * Filters prefixed with `@` are injected filters --They are run where
     * the un-scoped filter would be, but this `ComponentManager` is injected
     * as the last param.
     * 
     * @param string $name
     * @param Callable $callback
     * @return $this
     */
    public function add_filter($name, Callable $callback) {
        $is_injected = $this->should_inject($name);
        $name = $this->format_filter_name($name);
        $this->filters[] = compact('name', 'callback', 'is_injected');
        return $this;
    }

    /**
     * Commit all registered actions and filters.
     * 
     * @return $this
     */
    public function commit() {
        foreach ($this->actions as $action) {
            $callback = $action['callback'];
            if ($action['is_injected']) {
                add_action($action['hook'], function () use ($callback) {
                    $args = func_get_args();
                    $args[] = $this;
                    call_user_func_array($callback, $args);
                }, $action['priority'], $action['accepted_args'] - 1);
            } else {
                add_action($action['hook'], $callback, $action['priority'], $action['accepted_args']);
            }
        }
        $this->actions = [];
        
        foreach ($this->filters as $filter) {
            $callback = $filter['callback'];
            if ($filter['is_injected']) {
                add_filter($filter['name'], function () use ($callback) {
                    $args = func_get_args();
                    $args[] = $this;
                    return call_user_func_array($callback, $args);
                });
            } else {
                add_filter($filter['name'], $callback);
            }
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
            $this->do_action('@run');
            $this->do_action('@register_post_types');
            $this->do_action('@after_register_post_types');
        });

        return $this;
    }

    /**
     * Run actions.
     * 
     * Note that this is a good way to ensure that custom actions are run, as 
     * they will be prefixed with this manager's ID.
     * 
     * @param ...mixed $args
     * @return $this
     */
    public function do_action() {
        $args = func_get_args();
        $args[0] = $this->format_action_name($args[0]);
        call_user_func_array('do_action', $args);
        return $this;
    }

    /**
     * Check if a hook should be injected (that is, if it starts with `@`).
     * 
     * @param string $hook
     * @return boolean
     */
    protected function should_inject($hook) {
        return $hook[0] === '@';
    }

    /**
     * Prepare a hook's name, prefixing it with this manager's ID if it exists
     * as a custom action.
     * 
     * @param string $hook
     * @return string
     */
    protected function format_action_name($hook) {
        if ($hook[0] == '@' && in_array($hook, $this->custom_actions)) {
            return str_replace('@', $this->id . '_', $hook);
        } elseif (in_array($hook, $this->custom_actions)) {
            return $this->id . '_' . $hook;
        } elseif ($hook[0] == '@') {
            return str_replace('@', '', $hook);
        }
        return $hook;
    }

    /**
     * Prepare a filter's name.
     * 
     * @param string $name
     * @return string
     */
    protected function format_filter_name($name) {
        if ($name[0] == '@') {
            return str_replace('@', '', $name);
        }
        return $name;
    }

    /**
     * Format a component's id.
     * 
     * @param string $type
     * @return string
     */
    protected function format_id($type, $id = null) {
        if ($id != null) {
            return "$type#$id";
        }
        return $type;
    }

}
