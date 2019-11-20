<?php

namespace Inky\Component;

use Inky\Core\Action;
use Inky\Core\Component;
use Inky\Core\ComponentManager;
// use Inky\Core\OptionsAware;

class WebcomicPageGeneratorComponent implements Component {
    
    // use OptionsAware;

    private $subpage_name = 'inky_generate_pages';

    public function __construct() {}

    public function register(ComponentManager $manager) {
        // $this->initialize();

        $menu = new Action('admin_menu');
        $menu
            ->inject($manager)
            ->add([ $this, 'add_management_page' ]);
        $manager->add_action($menu);
    }

    // abstract function get_settings_id() {
    //     return 'inky';
    // }

    // abstract function get_options_id() {
    //     return 'inky';
    // }

    function add_management_page(ComponentManager $manager) {
        add_submenu_page(
            $manager->get_component(OptionsComponent::class)->get_page_name(),
            'Generate Pages',
            'Generate Pages',
            'manage_options',
            $this->subpage_name,
            [ $this, 'render' ],
            'dashicons-admin-customizer',
            100
        );
    }

    function render() {
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">Inky | Generate Pages</h1>
                <hr class="wp-header-end" />
                <p>Todo</p>
            </div>
        <?php
    }

}