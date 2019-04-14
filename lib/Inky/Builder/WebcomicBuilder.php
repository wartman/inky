<?php
namespace Inky\Builder;

use Inky\Core\Component;
use Inky\Core\ComponentManager;
use Inky\Component\WebcomicComponent;
use Inky\Component\OptionsComponent;

class WebcomicBuilder implements Component {

    public function __construct() {}

    public function get_component_id() {
        return static::class;
    }

    public function register(ComponentManager $manager) {
        $options = $manager->get_component(OptionsComponent::class);

        if (!$options) {
            $options = new OptionsComponent();
            $manager->add_component($options); // should auto-init
        }

        if ($options->is_installed()) {
            $this->build($manager);
        } else {
            $this->install($manager);
        }
    }

    private function install(ComponentManager $manager) {
        $options = $manager->get_component(OptionsComponent::class);

        $options->set_option('version', '0.0.1');
        $options->set_option('webcomics', [ 'webcomic' ]);
        $options->set_option('primary_webcomic', 'webcomic');
        $options->commit();

        $primary_webcomic = new WebcomicComponent('webcomic');
        $primary_webcomic->set_option('singular_name', 'Comic');
        $primary_webcomic->set_option('plural_name', 'Comics');
        $primary_webcomic->commit();

        $manager->add_component($primary_webcomic);
    }

    private function build(ComponentManager $manager) {
        $options = $manager->get_component(OptionsComponent::class);
        $webcomics = $options->get_option('webcomics', []);
        foreach ($webcomics as $webcomic) {
            $manager->add_component(new WebcomicComponent($webcomic));
        }
    }
    
}
