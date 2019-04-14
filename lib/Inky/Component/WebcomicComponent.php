<?php
namespace Inky\Component;

use WP_Query;
use Wp_Post;
use Inky\Core\Component;
use Inky\Core\OptionsAware;
use Inky\Core\ComponentManager;

class WebcomicComponent implements Component {

    use OptionsAware;

    protected $id;
    protected $chapters;
    protected $attachment;

    public function __construct($id) {
        $this->id = trim(strtolower($id));
        $this->chapters = new ChapterTaxonomyComponent($this);
        $this->attachment = new AttachmentComponent($this);
    }

    public function get_component_id() {
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
        $id = $this->attachment->get_attachment_id($post);
        return wp_get_attachment_image($id, $size);
    }

    public function register(ComponentManager $manager) {
        $this->initialize();
        $manager->add_action('init', $this, 'register_post_type', 10);
        $manager->add_action('admin_menu', $this, 'add_management_page');
        $manager->add_action('pre_get_posts', $this, 'include_posts');
        $manager->add_filter("sanitize_option_{$this->get_options_id()}", $this, 'filter_options');
        $manager->add_component($this->chapters);
        $manager->add_component($this->attachment);
    }

    public function register_post_type() {
        $singular_name = $this->get_option('singular_name', 'Comic');
        $plural_name = $this->get_option('plural_name', 'Comics');

        register_post_type($this->id, [
            'labels' => [
                'name' => $plural_name,
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
                $this->chapters->get_taxonomy_name()
            ],
            'has_archive' => true,
            'show_in_rest' => true
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

    // Todo: Replace "singular_name" and "pluarl_name" with "Title"
    public function filter_options($options) {
        $options['singular_name'] = sanitize_text_field($options['singular_name']);
        if (!$options['singular_name']) {
            $options['singular_name'] = $this->id;
        }
        $options['plural_name'] = sanitize_text_field($options['plural_name']);
        if (!$options['plural_name']) {
            $options['plural_name'] = $options['singular_name'] . 's';
        }
        return [
            'plural_name' => $options['plural_name'],
            'singular_name' => $options['singular_name']
        ];
    }

    public function add_management_page() {
        $id = $this->get_component_id();
        $page = "{$id}_settings";

        $this->register_setting();
        $this->add_settings_section($page, 'general', 'General', [
            'singular_name' => [ 
                'label' => 'Name',
                'description' => 'The name that will be displayed for this webcomic.'
            ],
            'plural_name' => [
                'label' => 'Plural Name',
                'description' => 'The plural name for this webcomic'
            ]
        ]);

        add_submenu_page(
            "edit.php?post_type={$this->get_post_type()}",
            $this->get_plural_name(),
            "Manage {$this->get_plural_name()}",
            'manage_options',
            $page,
            [ $this, 'render' ]
        );
    }

    public function render() {
        $id = $this->get_component_id();
        ?>
            <div class="wrap">
                <h2 class="wp-heading-inline"><?= esc_html($this->get_plural_name(), 'inky') ?></h2>
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
