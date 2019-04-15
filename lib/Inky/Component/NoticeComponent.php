<?php
namespace Inky\Component;

use Inky\Core\Component;
use Inky\Core\ComponentManager;

class NoticeComponent implements Component {

    const SUCCESS = 'success'; 
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';
    const TRANSITENT_ID = 'inky_notification';

    public function __construct() {
        // noop   
    }

    public function register(ComponentManager $manager) {
        $manager
            ->register_action('add_notice')
            ->add_action('add_notice', [ $this, 'add_notice' ], 10, 2);
        $manager->add_action('admin_notices', [ $this, 'render' ]);
    }

    public function add_notice($type, $message) {
        $data = get_transient(self::TRANSITENT_ID);
        if ($data === false) {
            $data = [];
        }
        $data[] = compact('type', 'message');
        set_transient(self::TRANSITENT_ID, $data, 0);
    }

    public function render() {
        $data = get_transient(self::TRANSITENT_ID);
        if ($data === false) {
            return;
        }
        delete_transient(self::TRANSITENT_ID);
        foreach ($data as $info) {
            $this->renderMessage($info['type'], $info['message']);
        }
    }

    protected function renderMessage($type, $message) {
        ?>
            <div class="notice notice-<?php _e($type, 'inky') ?> is-dismissible">
                <p><?php _e($message, 'inky') ?></p>
            </div>
        <?php
    }

}