<?php
/**
 * Plugin Name: HTML Plus Recent Posts by Category Widget
 * Description: A recent posts by category widget with simple text or HTML.
 * Version: 2.0.0
 * Author: linux4me
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: html-plus-recent-posts-by-category-widget
 * Domain Path: /languages
 */
 
defined('ABSPATH') or die('No script kiddies please!');

class HTML_Plus_Recent_Posts_by_Category_Widget extends WP_Widget
{

    /**
     * Whether or not the widget has been registered yet.
     *
     * @since 2.0.0
     * @var   bool
     */
    protected $registered = false;

    /**
     * Default instance.
     *
     * @since 2.0.0
     * @var   array
     */
    protected $default_instance = array(
        'title'   => '',
        'text' => '', 
        'category' => '', 
        'number' => 5
    );
    
    /**
     * Sets up a new HTML + Recent Posts by Category widget instance.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        load_plugin_textdomain(
            'html-plus-recent-posts-by-category-widget', 
            false, 
            basename(dirname(__FILE__)) . '/languages/'
        );
        $widget_ops = array(
        'classname' => 'html_plus_recent_posts_by_category_widget',
        'description' => __(
            'Add text or HTML preceding a list of recent posts by category.', 
            'html-plus-recent-posts-by-category-widget'
        ),
        'customize_selective_refresh' => true,
        );
        $control_ops = array('width' => 400, 'height' => 350);
        parent::__construct(
            'html_plus_recent_posts_by_category_widget', 
            __(
                'HTML Plus Recent Posts by Category', 
                'html-plus-recent-posts-by-category-widget'
            ), 
            $widget_ops, 
            $control_ops
        );
    }
    
    /**
     * Add hooks for enqueueing assets when registering all widget instances of 
     * this widget class.
     *
     * @since 2.0.0
     *
     * @param int $number Optional. The unique order number of this widget instance
     *                    compared to other instances of the same class. Default -1.
     */
    public function _register_one($number = -1)
    {
        parent::_register_one($number);
        if ($this->registered) {
            return;
        }
        $this->registered = true;

        wp_add_inline_script(
            'custom-html-widgets',
            sprintf(
                'wp.customHtmlWidgets.idBases.push(%s);', 
                wp_json_encode($this->id_base)
            )
        );

        /* 
            Note that the widgets component in the customizer will also do
            the 'admin_print_scripts-widgets.php' action in 
            WP_Customize_Widgets::print_scripts().
        */
        add_action(
            'admin_print_scripts-widgets.php', 
            array($this, 'enqueue_admin_scripts')
        );

        /* 
            Note that the widgets component in the customizer will also do
            the 'admin_footer-widgets.php' action in 
            WP_Customize_Widgets::print_footer_scripts().
        */
        add_action(
            'admin_footer-widgets.php', 
            array('WP_Widget_Custom_HTML', 'render_control_template_scripts')
        );
        
    }
    
    /**
     * Filters gallery shortcode attributes.
     *
     * Prevents all of a site's attachments from being shown in a gallery displayed 
     * on a non-singular template where a $post context is not available.
     *
     * @since 2.0.0
     *
     * @param  array $attrs Attributes.
     * @return array Attributes.
     */
    public function _filter_gallery_shortcode_attrs($attrs)
    {
        if (!is_singular() && empty($attrs['id']) && empty($attrs['include'])) {
            $attrs['id'] = -1;
        }
        return $attrs;
    }
    
    /**
     * Outputs the content for the current widget instance.
     *
     * @since 2.0.0
     *
     * @global WP_Post $post Global post object.
     *
     * @param array $args     Display arguments including 'before_title', 
     *                        'after_title', 'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current Custom HTML widget instance.
     */
    public function widget($args, $instance)
    {
        global $post;

        /* 
            Override global $post so filters (and shortcodes) apply in a consistent 
            context.
        */
        $original_post = $post;
        if (is_singular()) {
            /* 
                Make sure post is always the queried object on singular queries 
                (not from another sub-query that failed to clean up the global 
                $post).
            */
            $post = get_queried_object();
        } else {
            /* 
                Nullify the $post global during widget rendering to prevent 
                shortcodes from running with the unexpected context on archive 
                queries.
            */
            $post = null;
        }

        // Prevent dumping out all attachments from the media library.
        add_filter(
            'shortcode_atts_gallery', 
            array($this, '_filter_gallery_shortcode_attrs')
        );

        $instance = array_merge($this->default_instance, $instance);

        /**
         * This filter is documented in:
         * wp-includes/widgets/class-wp-widget-pages.php 
        */
        $title = apply_filters(
            'widget_title', 
            $instance['title'], 
            $instance, 
            $this->id_base
        );

        // Prepare instance data that looks like a normal Text widget.
        $simulated_text_widget_instance = array_merge(
            $instance,
            array(
                'text'   => isset($instance['text']) ? $instance['text'] : '',
                'filter' => false, // Because wpautop is not applied.
                'visual' => false, // Because it wasn't created in TinyMCE.
            )
        );
        // 'content' has moved to 'text' property.
        unset($simulated_text_widget_instance['content']);

        /**
         * This filter is documented in wp-includes/widgets/class-wp-widget-text.php 
        */
        $content = apply_filters(
            'widget_text', 
            $instance['text'], 
            $simulated_text_widget_instance, 
            $this
        );

        /* 
            Adds 'noopener' relationship, without duplicating values, to all HTML 
            A elements that have a target.
        */
        $content = wp_targeted_link_rel($content);

        /**
         * Filters the content of the HTML + Recent Posts by Category widget.
         *
         * @since 2.0.0
         *
         * @param string                $content  The widget content.
         * @param array                 $instance Array of settings for the 
         *                                        current widget.
         * @param WP_Widget_Custom_HTML $widget   Current Custom HTML widget 
         *                                        instance.
         */
        $content = apply_filters(
            'html_plus_recent_posts_by_category_widget', 
            $content, 
            $instance, 
            $this
        );

        // Restore post global.
        $post = $original_post;
        remove_filter(
            'shortcode_atts_gallery', 
            array($this, '_filter_gallery_shortcode_attrs')
        );

        /* 
            Inject the Text widget's container class name alongside this widget's 
            class name for theme styling compatibility.
        */
        $args['before_widget'] = preg_replace(
            '/(?<=\sclass=["\'])/', 
            'widget_text ', 
            $args['before_widget']
        );

        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        // The textwidget class is for theme styling compatibility.
        echo '<div class="textwidget htmlplusrecpostsbycat">';
        echo $content;
        $category  = $instance['category'];
        $number = (!empty($instance['number'])) ? absint($instance['number']) : 5;
        if (!$number) {
            $number = 5;
        }
        $cat_recent_posts = new WP_Query(
            array(
                'post_type' => 'post',
                'posts_per_page' => $number,
                'cat' => $category
            )
        );
        
        $show_date = isset($instance['show_date']) ? $instance['show_date'] : false;
        if ($cat_recent_posts->have_posts()) {
            echo '<ul>';
            while ($cat_recent_posts->have_posts()) {
                $cat_recent_posts->the_post();
                echo '<li>';
                echo '<a href="' . get_permalink() . '">' . get_the_title() . '</a>';
                if ($show_date) { 
                    echo ' - <span class="post-date">' . get_the_date() . '</span>';
                }
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo __('No posts yet.', 'html-plus-recent-posts-by-category-widget');
        }
        /* 
            Restore the global $post variable of the main query loop after a 
            secondary query loop.
        */
        wp_reset_postdata();
        echo '</div>';
        echo $args['after_widget'];
    }

    /**
     * Handles updating settings for the current widget instance.
     *
     * @param array $new_instance New settings for this instance as input by the 
     *                            user via WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * 
     * @return array Settings to save or bool false to cancel saving.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array_merge($this->default_instance, $old_instance);
        $instance['title'] = sanitize_text_field($new_instance['title']);
        if (current_user_can('unfiltered_html')) {
            $instance['text'] = $new_instance['text'];
        } else {
            $instance['text'] = wp_kses_post($new_instance['text']);
        }
        $instance['category'] = intval($new_instance['category']);
        $instance['number'] = intval($new_instance['number']);
        $instance['show_date'] = !empty($new_instance['show_date']);
        return $instance;
    }
    
    /**
     * Loads the required scripts and styles for the widget control.
     *
     * @since 2.0.0
     */
    public function enqueue_admin_scripts()
    {
        $settings = wp_enqueue_code_editor(
            array(
            'type'       => 'text/html',
            'codemirror' => array(
            'indentUnit' => 2,
            'tabSize'    => 2,
            ),
            )
        );

        wp_enqueue_script('custom-html-widgets');
        if (empty($settings)) {
            $settings = array(
            'disabled' => true,
            );
        }
        wp_add_inline_script(
            'custom-html-widgets', 
            sprintf('wp.customHtmlWidgets.init(%s);', wp_json_encode($settings)), 
            'after'
        );

        /* translators: %d: Error count. */
        $l10n = array(
            'errorNotice' => array(
                'singular' => _n(
                    'There is %d error which must be fixed before you can save.', 
                    'There are %d errors which must be fixed before you can save.', 
                    1
                ),
                'plural' => _n(
                    'There is %d error which must be fixed before you can save.', 
                    'There are %d errors which must be fixed before you can save.', 
                    2
                ),
            ),
        );
        wp_add_inline_script(
            'custom-html-widgets', 
            sprintf(
                'jQuery.extend(wp.customHtmlWidgets.l10n, %s);', 
                wp_json_encode($l10n)
            ), 
            'after'
        );
    }

    /**
     * Outputs the Custom HTML widget settings form.
     *
     * @since 2.0.0 The form contains hidden sync inputs for the title and textarea. 
     *              For the control UI, see:
     *              `WP_Widget_Custom_HTML::render_control_template_scripts()`.
     *
     * @see WP_Widget_Custom_HTML::render_control_template_scripts()
     *
     * @param array $instance Current instance.
     */
    public function form($instance)
    {
        $instance = wp_parse_args((array) $instance, $this->default_instance);
        $category  = intval($instance['category']);
        $number    = intval($instance['number']);
        $show_date = isset($instance['show_date']) ? $instance['show_date'] : 0;
        ?>
        <input 
            id="<?php echo $this->get_field_id('title'); ?>" 
            name="<?php echo $this->get_field_name('title'); ?>" 
            class="title sync-input" 
            type="hidden" 
            value="<?php echo esc_attr($instance['title']); ?>"
        >
        <textarea 
            id="<?php echo $this->get_field_id('text'); ?>" 
            name="<?php echo $this->get_field_name('text'); ?>" 
            class="content sync-input" 
            hidden
        ><?php echo esc_textarea($instance['text']); ?></textarea>
        <p>
            <label for="<?php echo $this->get_field_id('category'); ?>">
                <?php 
                    echo __(
                        'Category', 
                        'html-plus-recent-posts-by-category-widget'
                    ); 
                ?>:
            </label>
        <?php
        wp_dropdown_categories(
            array(
                'orderby'    => 'title',
                'hide_empty' => false,
                'name'       => $this->get_field_name('category'),
                'id'         => 'html_plus_recent_posts_by_category_widget',
                'class'      => 'widefat',
                'selected'   => $category
            )
        );

        ?></p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">
                <?php 
                    echo __(
                        'Number of Posts', 
                        'html-plus-recent-posts-by-category-widget'
                    ); 
                ?>: 
            </label>
            <input 
                type="text" 
                id="<?php echo $this->get_field_id('number'); ?>" 
                name="<?php echo $this->get_field_name('number'); ?>" 
                value="<?php echo esc_attr($number); ?>" 
                size="3"
            >
        </p>
        <p>
            <input 
                type="checkbox" 
                id="<?php echo $this->get_field_id('show_date'); ?>" 
                class="checkbox" 
                name="<?php echo $this->get_field_name('show_date'); ?>" 
                <?php checked($show_date); ?>
            >&nbsp;
            <label for="<?php echo $this->get_field_id('show_date'); ?>">
                <?php 
                    echo __(
                        'Display Post Date?', 
                        'html-plus-recent-posts-by-category-widget'
                    ); 
                ?>
            </label>
        </p>
        <?php
    }

    /**
     * Render form template scripts.
     *
     * @since 4.9.0
     */
    public static function render_control_template_scripts()
    {
        ?>
        <script 
            type="text/html" 
            id="tmpl-widget-html-plus-recent-posts-control-fields"
        >
            <# var elementIdPrefix = 'el' + String(Math.random()).replace(/\D/g, '') + '_' #>
            <p>
                <label for="{{ elementIdPrefix }}title">
                    <?php esc_html_e('Title:'); ?>
                </label>
                <input 
                    id="{{ elementIdPrefix }}title" 
                    type="text" 
                    class="widefat title"
                >
            </p>

            <p>
                <label 
                    for="{{ elementIdPrefix }}content" 
                    id="{{ elementIdPrefix }}content-label"
                ><?php esc_html_e('Content:'); ?></label>
                <textarea 
                    id="{{ elementIdPrefix }}content" 
                    class="widefat code content" 
                    rows="16" 
                    cols="20"
                ></textarea>
            </p>

            <?php 
            if (!current_user_can('unfiltered_html')) {
                $probably_unsafe_html = array(
                'script', 
                'iframe', 
                'form', 
                'input', 
                'style'
                );
                $allowed_html = wp_kses_allowed_html('post');
                $disallowed_html = array_diff(
                    $probably_unsafe_html, 
                    array_keys($allowed_html)
                );
                if (!empty($disallowed_html)) { 
                    ?>
                    <# if (data.codeEditorDisabled) { #>
                        <p>
                    <?php 
                    _e('Some HTML tags are not permitted, including:'); 
                    ?>
                            <code>
                    <?php 
                            echo implode(
                                '</code>, <code>', 
                                $disallowed_html
                            ); 
                    ?>
                            </code>
                        </p>
                    <# } #>
                    <?php 
                }
            } 
            ?>

            <div class="code-editor-error-container"></div>
        </script>
        <?php
    }
}

/* Register the widget. */
add_action('widgets_init', 'register_HTML_Plus_Recent_Posts_by_Category_Widget');

function register_HTML_Plus_Recent_Posts_by_Category_Widget()
{
    register_widget('HTML_Plus_Recent_Posts_by_Category_Widget');
}
?>