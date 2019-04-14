<?php
namespace Inky\Core;

trait OptionsAware {

    private $options = [];

    abstract function get_component_id();
    abstract function get_options_id();

    public function get_option($key, $default = null) {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return $default;
    }

    public function set_option($key, $value) {
        $this->options[$key] = $value;
    }

    public function initialize() {
        $this->options = get_option($this->get_options_id());
    }

    public function commit() {
        $key = $this->get_options_id();
        $value = $this->options;
        if (get_option($key) !== false) {
            return update_option($key, $value);
        } 
        return add_option($key, $value, null, 'yes');
    }

    public function register_setting() {
        register_setting(
            $this->get_component_id(),
            $this->get_options_id()
        );
    }
    
    public function add_settings_section($page, $section, $title, $fields) {
        $section = "{$page}_{$section}";
        add_settings_section(
            $section,
            $title,
            function () {},
            $page
        );
        foreach ($fields as $key => $options) {
            $this->add_settings_field_for($page, $section, $key, $options);
        }
    }

    // This actually makes things more confusing :V
    public function add_settings_field_for($page, $section, $key, $options) {
        $option = $this->get_options_id();
        add_settings_field(
            "{$page}_{$key}",
            $options['label'],
            function () use ($option, $options, $key) {
                $option_name = esc_attr("{$option}[{$key}]");
                $value = esc_attr($this->get_option($key, ''));
                ?>
                    <input
                        class="regular-text"
                        type="text"
                        name="<?= $option_name ?>"
                        id="<?= $option_name ?>"
                        value="<?= $value ?>"
                    />
                    <p class="description">
                        <?= esc_html($options['description']) ?>
                    </p>
                <?php 
            },
            $page,
            $section
        );
    }

    public function __call($name, $args) {
        if (strpos('get_', $name) == 0) {
            $key = str_replace('get_', '', $name);
            array_unshift($args, $key);
            return $this->get_option(...$args);
        } elseif (strpos('set_', $name) == 0) {
            $key = str_replace('set_', '', $name);
            array_unshift($args, $key);
            $this->set_option(...$args);
        }
        throw new \InvalidArgumentException();
    }

}