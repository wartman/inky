<?php

namespace Inky\Core;

interface Component {

    /**
     * An id used by the component manager. If null, the 
     * ID registered with the Manager will be the component 
     * class name. If a string, it will be appended to the 
     * class name (which will allow you to register several
     * components of the same class with the manager).
     * 
     * @return string|null
     */
    public function get_component_id();

    /**
     * Registers the component with the ComponentManager.
     *
     * @param Inky\Core\ComponentManager $manager
     * @return void
     */
    public function register(ComponentManager $manager);

}
