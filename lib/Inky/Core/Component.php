<?php

namespace Inky\Core;

interface Component {

    /**
     * Registers the component with the ComponentManager.
     *
     * @param Inky\Core\ComponentManager $manager
     * @return void
     */
    public function register(ComponentManager $manager);

}
