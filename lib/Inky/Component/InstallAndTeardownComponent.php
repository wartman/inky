<?php
namespace Inky\Component;

use Inky\Core\Component;
use Inky\Core\ComponentManager;
use Inky\Collection\WebcomicCollection;

class InstallAndTeardownComponent implements Component {

    private $plugin_file;

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
    }

    public function register(ComponentManager $manager) {
        $manager->setup->add([$this, 'install']);
    }
    
    public function install(ComponentManager $manager) {
        register_activation_hook($this->plugin_file, function () use ($manager) {
            $options = $manager->get_component(OptionsComponent::class);
            if (!$options->is_installed()) {
                $options->set_option('version', '0.0.1');
                $options->set_option('webcomics', [ 'webcomic' ]);
                $options->set_option('primary_webcomic', 'webcomic');
                $options->commit();

                $primary_webcomic = new WebcomicComponent('webcomic');
                $primary_webcomic->set_option('title', 'Webcomic');
                $primary_webcomic->set_option('description', 'Just another webcomic');
                $primary_webcomic->set_option('slug', 'webcomic');
                $primary_webcomic->commit();

                $manager->get_component(WebcomicCollection::class)
                    ->add($primary_webcomic);

                $manager->get_component(NoticeComponent::class)
                    ->add_notice(NoticeComponent::INFO, 'Inky installed');
            } else {
                $manager->get_component(NoticeComponent::class)
                    ->add_notice(NoticeComponent::INFO, 'Inky activated');
            }
        });
    }

}
