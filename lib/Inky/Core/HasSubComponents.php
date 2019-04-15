<?php
namespace Inky\Core;

trait HasSubComponents {
    
    private $manager;
    private $components = [];
    
    /**
    * This should resolve a unique id for the given component.
    * 
    * @param Component $component
    * @return string
    */
    abstract protected function resolve_id(Component $component);
    
    /**
    * This should check if the given component is allowed to
    * be a sub-component of this parent.
    * 
    * @param Component $component
    * @return boolean
    */
    abstract protected function allow_component(Component $component);
    
    /**
    * Set the ComponentManager this class will use to register
    * sub-components.
    * 
    * @param ComponentManager $manager
    * @return void
    */
    public function set_manager(ComponentManager $manager) {
        $this->manager = $manager;
    }
    
    /**
    * Add a component.
    * 
    * A component will not be added if it has the same ID as an
    * existing one.
    * 
    * @throws InvalidArgumentException if no ComponentManager is registered.
    * 
    * @param Component $component
    * @return $this
    */
    public function add_component(Component $component) {
        if ($this->manager == null) {
            throw new \InvalidArgumentException();
        }
        
        if (!$this->allow_component($component)) {
            return $this;
        }
        
        $id = $this->resolve_id($component);
        if ($this->has_component($id)) {
            return $this;
        }
        $component->register($this->manager);
        $this->components[$id] = $component;
    }
    
    /**
    * Get a component.
    * 
    * @param string $id
    * @return Component|null
    */
    public function get_component($id) {
        return $this->has_component($id)
            ? $this->components[$id]
            : null;
    }
    
    /**
     * Get an array of all registered components.
     * 
     * @return Component[]
     */
    public function get_all_components() {
        return array_values($this->components);
    }
    
    /**
     * Get an array of all component IDs.
     * 
     * @return string[]
     */
    public function get_all_component_ids() {
        return array_keys($this->components);
    }
    
    /**
    * Check if a component exists.
    * 
    * @param string $id
    * @return boolean
    */
    public function has_component($id) {
        return isset($this->components[$id]);
    }
    
}