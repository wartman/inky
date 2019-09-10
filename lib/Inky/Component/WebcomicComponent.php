<?php
namespace Inky\Component;

use WP_Query;
use Wp_Post;
use Inky\Core\Component;
use Inky\Core\OptionsAware;
use Inky\Core\HasSubComponents;
use Inky\Core\ComponentManager;
use Inky\Core\Action;
use Inky\Core\Filter;

class WebcomicComponent implements Component {

    use OptionsAware;
    use HasSubComponents;

    protected $id;

    public function __construct($id) {
        $this->id = trim(strtolower($id));
    }
    
    private function resolve_id(Component $component) {
        return get_class($component);
    }

    private function allow_component(Component $component) {
        return true;
    }

    public function get_settings_id() {
        return $this->id;
    }

    public function get_options_id() {
        return "inky_{$this->id}";
    }

    public function get_post_type() {
        return $this->id;
    }

    public function get_post_type_object() {
        return get_post_type($this->get_post_type());
    }

    public function get_query(array $options) {
        $options['post_type'] = $this->get_post_type();
        return new Wp_Query($options);
    }

    public function get_posts(array $options) {
        $options['post_type'] = $this->get_post_type();
        return get_posts($options);
    }

    public function get_chapters() {
        return $this->get_component(ChapterTaxonomyComponent::class);
    }

    public function get_attachment() {
        return $this->get_component(AttachmentComponent::class);
    }

    public function loop(callable $cb) {
        $query = $this->get_query();
        while($query->have_posts()) {
            $query->the_post();
            $cb(get_post());
        }
        wp_reset_postdata();
    }

    public function is_webcomic(Wp_Post $post) {
        return $post->post_type == $this->get_post_type();
    }

    public function get_attachment_image(Wp_Post $post, $size = 'full') {
        return $this->get_attachment()->get_attachment_image($post, $size);
    }

    public function get_attachment_image_src(Wp_Post $post, $size = 'full') {
        return $this->get_attachment()->get_attachment_image_src($post, $size);
    }

    public function register(ComponentManager $manager) {
        $this->set_manager($manager);
        $this->initialize();

        $this->add_component(new ChapterTaxonomyComponent($this));
        $this->add_component(new AttachmentComponent($this));

        $manager->register_post_types->add([ $this, 'register_post_type' ]);
        
        $menu = new Action('admin_menu');
        $menu->add([ $this, 'add_management_page' ]);
        $manager->add_action($menu);

        $pre = new Action('pre_get_posts');
        $pre->add([ $this, 'include_posts' ]);
        $manager->add_action($pre);

        $sanitize = new Filter("sanitize_option_{$this->get_options_id()}");
        $sanitize
            ->inject($manager)
            ->add([ $this, 'filter_options' ]);
        $manager->add_filter($sanitize);
    }

    public function register_post_type(ComponentManager $manager) {
        $title = $this->get_option('title', 'Webcomic');
        $plural_name = "$title Comics";
        $singular_name = "$title Comic";

        register_post_type($this->id, [
            'labels' => [
                'name' => $title,
                'singular_name' => $singular_name,
                'add_new_item' => "Add New $singular_name",
                'edit_item' => "Edit $singular_name",
                'new_item' => "New $singular_name",
                'view_item' => "View $singular_name",
                'view_items' => "View $plural_name",
                'item_published' => "$singular_name Published"
            ],
            'supports' => [
                'title',
                'editor',
                'thumbnails',
                'comments',
                'custom-fields', // required for metadata???
                'revisions'
            ],
            'public' => true,
            'show_ui' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-admin-customizer',
            'taxonomies' => [ 
                'category',
                $this->get_chapters()->get_taxonomy_name()
            ],
            'has_archive' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => $this->get_slug(),
                'with_front' => true,
                'pages' => true
            ]
        ]);
    }

    public function include_posts($query) {
        if ((boolean) $this->get_option('include_in_main_query', false)) {
            if ( is_home() && $query->is_main_query() ) {
                if (
                    (is_home() || is_category() || is_search()) 
                    && !is_admin()
                    && $query->is_main_query()
                ) {
                    $post_types = [ 'post' ];
                    // Ensure current `post_type`s are not overwritten.
                    if (isset($query->query_vars['post_type'])) {
                        $post_types = $query->query_vars['post_type'];
                    }
                    $post_types[] = $this->get_post_type();
                    $query->set('post_type', $post_types);
                }
            }
        }
    }

    public function filter_options(ComponentManager $manager, $options) {
        $sanatize = function ($key, $def) use ($options) {
            $value = sanitize_text_field($options[$key]);
            if (!$value) {
                return $def;
            }
            return $value;
        };

        $title = $sanatize('title', $this->id);
        $description = $sanatize('description', '');
        $slug = $sanatize('slug', $this->get_post_type());

        if ($slug != $this->get_slug()) {
            $manager->do_action('request_rewrite');
        }

        $manager->do_action('add_notice', NoticeComponent::SUCCESS, "$title options updated");

        return compact('title', 'description', 'slug');
    }

    public function add_management_page() {
        $id = $this->get_post_type();
        $page = "{$id}_settings";

        $this->register_setting();
        $this->add_settings_section($page, 'general', 'General', [
            'title' => [ 
                'label' => 'Title',
                'description' => 'The name of this webcomic.'
            ],
            'slug' => [ 
                'label' => 'Slug',
                'description' => 'The slug for this webcomic. Will flush rewrite rules if changed.'
            ],
            'description' => [
                'label' => 'Description',
                'description' => 'The description of this webcomic',
                'kind' => 'textarea'
            ]
        ]);

        add_submenu_page(
            "edit.php?post_type={$this->get_post_type()}",
            $this->get_title(),
            "Manage {$this->get_title()}",
            'manage_options',
            $page,
            [ $this, 'render' ]
        );
    }

    public function render() {
        $id = $this->get_post_type();
        ?>
            <div class="wrap">
                <h2 class="wp-heading-inline"><?= esc_html($this->get_title(), 'inky') ?></h2>
                <hr class="wp-header-end">
                <form action="options.php" method="post">
                    <?php settings_fields($id) ?>
                    <?php do_settings_sections("{$id}_settings") ?>
                    <?php submit_button() ?>
                </form>
            </div>
        <?php
    }

}
