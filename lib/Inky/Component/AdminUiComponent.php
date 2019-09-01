<?php
namespace Inky\Component;

use Inky\Core\Component;
use Inky\Core\ComponentManager;

class AdminUiComponent implements Component {

    private $root;

    public function __construct($root) {
        $this->root = $root;
    }

    public function get_name($suffix) {
        return "inky-plugin-$suffix";
    }

    public function register(ComponentManager $manager) {
        $manager->add_action('init', [ $this, 'add_scripts' ]);
        $manager->add_action('enqueue_block_editor_assets', [ $this, 'enqueue_scripts' ]);
        // todo: css?
    }

    public function add_scripts() {
        wp_register_script(
            $this->get_name('sidebar'),
            plugins_url( 'build/index.js', $this->root ),
            json_decode( file_get_contents(plugins_url( 'build/index.deps.json', $this->root ) ))
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script($this->get_name('sidebar'));
    }

}
