<?php
namespace Inky\Component;

use Wp_Post;
use Inky\Core\Component;
use Inky\Core\ComponentManager;

class AttachmentComponent implements Component {

    protected $parent;

    public function __construct(WebcomicComponent $parent) {
        $this->parent = $parent;
    }

    public function get_component_id() {
        $id = $this->parent->get_component_id();
        return "$id/attachment";
    }

    public function get_meta_key() {
        // $type = $this->parent->get_post_type();
        // return "inky_{$type}_attachment";

        // For now, so it works with JS
        return 'inky_webcomic';
    }

    public function register(ComponentManager $manager) {
        $manager->add_action('init', $this, 'register_meta');
    }

    public function get_attachment_id(Wp_Post $post) {
        $key = $this->get_meta_key();
        return get_post_meta($post->ID, $key, true);
    }

    public function register_meta() {
        // todo: expose meta key to the JS side of things.
        //       also, figure out why this breaks when the `post_type`
        //       is not `post`???
        register_meta(
            // $this->parent->get_post_type()
            'post',
            $this->get_meta_key(), 
            [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'integer'
            ]
        );
    }

}
