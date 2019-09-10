<?php
namespace Inky\Component;

use Inky\Core\Component;
use Inky\Core\ComponentManager;

class ChapterTaxonomyComponent implements Component {

    protected $parent;

    public function __construct(WebcomicComponent $parent) {
        $this->parent = $parent;
    }

    public function get_taxonomy_name() {
        $post_type = $this->parent->get_post_type();
        return "inky_{$post_type}_chapter";
    }

    public function register(ComponentManager $manager) {
        $manager->init->add([ $this, 'register_taxonomy' ]);
    }

    public function register_taxonomy() {
        $name = $this->parent->get_title();
        register_taxonomy(
            $this->get_taxonomy_name(),
            $this->parent->get_post_type(), 
            [
                'labels' => [
                    'name' => "$name Chapters",
                    'singular_name' => 'Chapter',
                    'edit_item' => 'Edit Chapter',
                    'view_item' => 'View Chapter',
                    'update_item' => 'Update Chapter',
                    'add_new_item' => 'Add New Chapter',
                    'new_item_name' => 'New Chapter Name',
                    'search_items' => 'Search Chapters',
                    'parent_item' => 'Parent Chapter',
                    'parent_item_colon' => 'Parent Chapter:',
                    'add_or_remove_items' => 'Add or Remove Chapters',
                    'not_found' => 'No chapters found',
                    'back_to_items' => '← Back to chapters'
                ],
                'public' => true,
                'show_in_rest' => true,
                'show_admin_column' => true,
                'hierarchical' => true,
                'rewrite' => [
                    'slug' => $this->parent->get_slug() . '_chapter'
                ]
            ]
        );
    }

}
