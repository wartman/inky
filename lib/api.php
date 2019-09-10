<?php
namespace Inky;

use Inky\Core\ComponentManager;
use Inky\Collection\WebcomicCollection;
use Inky\Component\OptionsComponent;

function get_component_manager() {
    return ComponentManager::get_instance();
}

function get_component($type, $id = null) {
    return get_component_manager()->get_component($type, $id);
}

function get_options() {
    return get_component(OptionsComponent::class);
}

function get_webcomic_collection() {
    return get_component(WebcomicCollection::class);
}

function get_all_webcomics() {
    return get_webcomic_collection()->get_all_components();
}

function get_webcomic($id) {
    return get_webcomic_collection()->get_component($id);
}

function get_active_webcomic() {
    return get_webcomic_collection()->get_active_webcomic();
}

function is_webcomic($id = null) {
    if ($id == null) {
        $id = get_post_type();
    }
    return get_webcomic($id) != null; 
}

function is_webcomic_post($id) {
    $webcomic = get_webcomic($id);
    if ($webcomic == null) { 
        return false;
    }
    return $webcomic->is_webcomic(get_post());
}
