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

    /**
     * Check if a given taxonomy name belongs to any registered
     * webcomic
     * 
     * @param string $tax_name
     * @return boolean
     */
    public function is_webcomic_chapter($tax_name) {
        foreach ($this->get_all_components() as $component) {
            if ($component->is_webcomic_chapter($tax_name)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get a webcomic for a given taxonomy.
     * 
     * @param string $tax_name
     * @return Inky\Component\WebcomicComponent
     */
    public function get_webcomic_for_chapter($tax_name) {
        foreach ($this->get_all_components() as $component) {
            if ($component->is_webcomic_chapter($tax_name)) {
                return $component;
            }
        }
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
