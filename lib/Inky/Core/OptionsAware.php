<?php
namespace Inky\Core;

trait OptionsAware {

    private $options = [];

    abstract function get_settings_id();
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
        register_setting($this->get_settings_id(), $this->get_options_id());
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

    public function add_settings_field_for($page, $section, $key, $options) {
        $option = $this->get_options_id();
        $callback = function () {};
        
        if (!isset($options['kind'])) {
            $options['kind'] = 'text';
        }

        switch ($options['kind']) {
            case 'textarea':
                $callback = function () use ($option, $options, $key) {
                    $option_name = esc_attr("{$option}[{$key}]");
                    $value = esc_attr($this->get_option($key, ''));
                    ?>
                        <textarea 
                            name="<?= $option_name ?>"
                            id="<?= $option_name ?>"
                        ><?= $value ?></textarea>
                        <p class="description">
                            <?= esc_html($options['description']) ?>
                        </p>
                    <?php
                };
                break;
            case 'select':
                $callback = function () use ($option, $options, $key) {
                    $option_name = esc_attr("{$option}[{$key}]");
                    $selected = $this->get_option($key);
                    $values = $this->get_option($options['options'], []);
                    ?>
                        <select
                            name="<?= $option_name ?>" 
                            id="<?= $option_name ?>"
                        >
                            <?php foreach ($values as $value): ?>
                                <option
                                    value="<?= esc_attr($value) ?>"
                                    <?php if ($value === $selected) echo 'selected="selected"' ?>
                                >
                                    <?= esc_html($value) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                        <p class="description">
                            <?= esc_html($options['description']) ?>
                        </p>
                    <?php
                };
                break;
            default:
                $callback = function () use ($option, $options, $key) {
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
                };
        }

        add_settings_field(
            "{$page}_{$key}",
            $options['label'],
            $callback,
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