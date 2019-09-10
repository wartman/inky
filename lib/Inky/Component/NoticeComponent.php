<?php
namespace Inky\Component;

use Inky\Core\Component;
use Inky\Core\ComponentManager;
use Inky\Core\Action;

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
        $notice = new Action('add_notice', 10, 2);
        $notice->add([ $this, 'render' ]);
        $manager->add_action($notice);

        $admin_notices = new Action('admin_notices');
        $admin_notices->add([ $this, 'render' ]);
        $manager->add_action($admin_notices);
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