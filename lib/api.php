<?php
namespace Inky;

use Inky\Core\ComponentManager;
use Inky\Component\WebcomicComponent;
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

function get_registered_webcomics() {
    $webcomics = get_options()->get_webcomics();
    $out = [];
    foreach ($webcomics as $id) {
        $out[] = get_webcomic($id);
    }
    return $out;
}

function get_webcomic($id) {
    return get_component(WebcomicComponent::class, $id);
}

function is_webcomic($id) {
    $webcomic = get_webcomic($id);
    if ($webcomic == null) { 
        return false;
    }
    return $webcomic->is_webcomic(get_post());
}
