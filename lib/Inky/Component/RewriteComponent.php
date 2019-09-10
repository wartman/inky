<?php
namespace Inky\Component;

use Inky\Core\Component;
use Inky\Core\ComponentManager;
use Inky\Core\Action;

class RewriteComponent implements Component {

    const REWRITE_TRANSIENT = 'inky_rewrite';

    public function __construct() {
        // noop
    }
        
    public function register(ComponentManager $manager) {
        register_deactivation_hook($manager->get_plugin_file(), [ $this, 'flush' ]);

        $request = new Action('request_rewrite', 10, 0);
        $request->add([$this, 'request_rewrite']);
        $manager->add_action($request);
        
        $manager->after_register_post_types
            ->add([ $this, 'maybe_flush_rewrite_rules' ]);
    }
        
    public function request_rewrite() {
        set_transient(self::REWRITE_TRANSIENT, 1);
    }

    public function maybe_flush_rewrite_rules(ComponentManager $manager) {
        if (get_transient(self::REWRITE_TRANSIENT) === false) {
            return;
        }
        delete_transient(self::REWRITE_TRANSIENT);
        $this->flush();
        $manager->do_action('add_notice', NoticeComponent::SUCCESS, 'Rewrite rules flushed');
    }
    
    public function flush() {
        flush_rewrite_rules();
    }
            
}
