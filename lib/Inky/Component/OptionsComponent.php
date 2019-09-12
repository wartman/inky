<?php
namespace Inky\Component;

use Inky\Core\Component;
use Inky\Core\ComponentManager;
use Inky\Core\OptionsAware;
use Inky\Core\Action;
use Inky\Core\Filter;

class OptionsComponent implements Component {

    use OptionsAware;

    public function __construct() {}

    public function get_settings_id() {
        return 'inky';
    }

    public function get_options_id() {
        return 'inky';
    }

    public function is_installed() {
        return $this->get_option('version', null) != null;
    }

    public function register(ComponentManager $manager) {
        $this->initialize();

        $manager->init->add([ $this, 'on_init' ]);

        $menu = new Action('admin_menu');
        $menu->add([ $this, 'add_management_page' ]);
        $manager->add_action($menu);

        $sanatize = new Filter("sanitize_option_{$this->get_options_id()}");
        $sanatize
            ->inject($manager)
            ->add([ $this, 'filter_options' ]);
        $manager->add_filter($sanatize);
    }

    public function filter_options(ComponentManager $manager, $options) {
        $existing = $this->options;
        $notices = $manager->get_component(NoticeComponent::class);
        
        if (isset($options['new_comic']) && $options['new_comic'] != '') {
            if (!in_array($existing['webcomics'], $options['new_comic'])) {
                $existing['webcomics'][] = sanitize_text_field($options['new_comic']);
                $notices->add_notice(NoticeComponent::SUCCESS, 'New webcomic created.');
            } else {
                $notices->add_notice(NoticeComponent::ERROR, 'That webcomic already exists.');
            }
        }

        $notices->add_notice(NoticeComponent::SUCCESS, 'Options updated');

        return [
            'version' => isset($existing['version']) ? $existing['version'] : '0.0.1',
            'webcomics' => isset($existing['webcomics']) ? $existing['webcomics'] : [ 'webcomic' ],
            'primary_webcomic' => sanitize_text_field($options['primary_webcomic'])
        ];
    }

    public function add_management_page() {
        $page = "inky_settings";
        $this->register_setting();
        $this->add_settings_section($page, 'general', 'General Settings', [
            'primary_comic' => [
                'label' => 'Primary Comic',
                'kind' => 'select',
                'description' => 'The default comic. Will be displayed on the main page.',
                'options' => 'webcomics'
            ]
        ]);
        add_menu_page(
            'Inky',
            'Inky',
            'manage_options',
            $page,
            [ $this, 'render' ],
            'dashicons-admin-customizer',
            100
        );

        // -------------------------------

        // Just a proof of concept
        $subpage = "{$page}_list";

        add_settings_section(
            "{$page}_list_add",
            'Add Webcomic',
            function () {},
            $subpage
        );

        add_settings_field(
            "{$page}_list_new_webcomic",
            'Name',
            function () {
                $option = $this->get_options_id();
                ?>
                    <input
                        type="text"
                        name="<?= $option ?>[new_comic]" 
                        id="<?= $option ?>[new_comic]"
                        value=""
                    />
                    <p class="description">
                        The name of the new webcomic post type.
                    </p>
                <?php
            },
            $subpage,
            "{$page}_list_add"
        );

        add_submenu_page(
            $page,
            'Webcomics',
            'Webcomics',
            'manage_options',
            $subpage,
            function () use ($subpage) {
                ?>
                    <div class="wrap">
                        <h1 class="wp-heading-inline">Inky | Webcomics</h1>
                        <hr class="wp-header-end">
                        <form action="options.php" method="post">
                            <?php settings_fields('inky') ?>
                            <?php do_settings_sections($subpage) ?>
                            <?php submit_button() ?>
                        </form>
                    </div>
                <?php
            }
        );
    }

    public function render() {
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">Inky</h1>
                <hr class="wp-header-end">
                <form action="options.php" method="post">
                    <?php settings_fields('inky') ?>
                    <?php do_settings_sections("inky_settings") ?>
                    <?php submit_button() ?>
                </form>
            </div>
        <?php
    }

}
