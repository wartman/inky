<?php
/**
 * Plugin Name: Inky: A simple webcomic plugin
 */

require_once __DIR__ . '/lib/Inky/Core/Component.php';
require_once __DIR__ . '/lib/Inky/Core/OptionsAware.php';
require_once __DIR__ . '/lib/Inky/Core/ComponentManager.php';

require_once __DIR__ . '/lib/Inky/Component/AdminUiComponent.php';
require_once __DIR__ . '/lib/Inky/Component/OptionsComponent.php';
require_once __DIR__ . '/lib/Inky/Component/NoticeComponent.php';
require_once __DIR__ . '/lib/Inky/Component/RewriteComponent.php';
require_once __DIR__ . '/lib/Inky/Component/AttachmentComponent.php';
require_once __DIR__ . '/lib/Inky/Component/ChapterTaxonomyComponent.php';
require_once __DIR__ . '/lib/Inky/Component/WebcomicComponent.php';

require_once __DIR__ . '/lib/Inky/Builder/WebcomicBuilder.php';

require_once __DIR__ . '/lib/api.php';

use Inky\Core\ComponentManager;
use Inky\Builder\WebcomicBuilder;
use Inky\Component\OptionsComponent;
use Inky\Component\NoticeComponent;
use Inky\Component\RewriteComponent;
use Inky\Component\AdminUiComponent;

$manager = ComponentManager::get_instance();
$manager->add_component(new NoticeComponent());
$manager->add_component(new RewriteComponent());
$manager->add_component(new OptionsComponent());
$manager->add_component(new WebcomicBuilder());
$manager->add_component(new AdminUiComponent(__FILE__));
$manager->run();
