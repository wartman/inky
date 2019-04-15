<?php
namespace Inky\Collection;

use Inky\Core\Component;
use Inky\Core\ComponentManager;
use Inky\Core\HasSubComponents;
use Inky\Component\WebcomicComponent;
use Inky\Component\OptionsComponent;

class WebcomicCollection implements Component {

    use HasSubComponents;

    private $manager;
    private $active_webcomic;

    public function __construct() {}

    public function register(ComponentManager $manager) {
        $this->set_manager($manager);
        $this->build($manager);
    }

    private function resolve_id(Component $component) {
        return $component->get_post_type();
    }

    private function allow_component(Component $component) {
        return $component instanceof WebcomicComponent;
    }

    public function get_active_webcomic() {
        return $this->get_component($this->active_webcomic);
    }

    public function set_active_webcomic($id) {
        $this->active_webcomic = $id;
    }

    private function build(ComponentManager $manager) {
        $options = $manager->get_component(OptionsComponent::class);
        $webcomics = $options->get_option('webcomics', []);
        
        foreach ($webcomics as $webcomic) {
            if (!$this->has_component($webcomic)) {
                $this->add_component(new WebcomicComponent($webcomic));
            }
        }

        $this->active_webcomic = $options->get_option('primary_webcomic', 'webcomic');
    }
    
}
