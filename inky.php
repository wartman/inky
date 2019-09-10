<?php
/**
 * Plugin Name: Inky
 * Plugin URI: https://github.com/wartman/inky
 * Description: A simple Webcomic plugin
 * Version: 0.0.1
 * Author: wartman
 * Author URI: https://github.com/wartman
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * @package Inky
 */

require_once __DIR__ . '/lib/Inky/Core/Filter.php';
require_once __DIR__ . '/lib/Inky/Core/Action.php';
require_once __DIR__ . '/lib/Inky/Core/Component.php';
require_once __DIR__ . '/lib/Inky/Core/OptionsAware.php';
require_once __DIR__ . '/lib/Inky/Core/HasSubComponents.php';
require_once __DIR__ . '/lib/Inky/Core/ComponentManager.php';

require_once __DIR__ . '/lib/Inky/Component/InstallAndTeardownComponent.php';
require_once __DIR__ . '/lib/Inky/Component/AdminUiComponent.php';
require_once __DIR__ . '/lib/Inky/Component/OptionsComponent.php';
require_once __DIR__ . '/lib/Inky/Component/NoticeComponent.php';
require_once __DIR__ . '/lib/Inky/Component/RewriteComponent.php';
require_once __DIR__ . '/lib/Inky/Component/AttachmentComponent.php';
require_once __DIR__ . '/lib/Inky/Component/ChapterTaxonomyComponent.php';
require_once __DIR__ . '/lib/Inky/Component/WebcomicComponent.php';

require_once __DIR__ . '/lib/Inky/Collection/WebcomicCollection.php';

require_once __DIR__ . '/lib/api.php';

use Inky\Core\ComponentManager;
use Inky\Collection\WebcomicCollection;
use Inky\Component\InstallAndTeardownComponent;
use Inky\Component\OptionsComponent;
use Inky\Component\NoticeComponent;
use Inky\Component\RewriteComponent;
use Inky\Component\AdminUiComponent;

$manager = ComponentManager::get_instance();
$manager->add_component(new OptionsComponent());
$manager->add_component(new NoticeComponent());
$manager->add_component(new InstallAndTeardownComponent(__FILE__));
$manager->add_component(new RewriteComponent());
$manager->add_component(new WebcomicCollection());
$manager->add_component(new AdminUiComponent(__FILE__));
$manager->run();
