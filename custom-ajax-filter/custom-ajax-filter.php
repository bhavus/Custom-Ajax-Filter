<?php
/*
Plugin Name: Custom AJAX Filter
Description: Adds AJAX filtering functionality for custom post types.[custom_ajax_filter]
Version: 1.0
Author: Shailesh Parmar
*/



if ( ! defined( 'ABSPATH' ) ) {
    die( 'Invalid request.' );
}

/*// Activation hook
function cptaf_activate() {
    // Perform any activation tasks if needed
}
register_activation_hook(__FILE__, 'cptaf_activate');

// Deactivation hook
function cptaf_deactivate() {
    // Perform any deactivation tasks if needed
}
register_deactivation_hook(__FILE__, 'cptaf_deactivate');*/

/**
 *  ADD PAGE TEMPLATE 
 **/


function custom_template_register_page_template( $templates ) {
    $templates['template-movies.php'] = 'Template Movies';
    return $templates;
}
add_filter( 'theme_page_templates', 'custom_template_register_page_template' );



/**
 *   Create custom Post Type 
 **/

add_action('init','register_custom_post_types');

function register_custom_post_types(){
    register_post_type('movie',[
       'labels' => [
           'name' => 'Movie',
           'singular_name' => 'Movie',
           'menu_name' => 'Movies',
       ],
        'public' => true,
        'publicly_queryable' =>true,
        'menu_icon' => 'dashicons-format-video',
        'has_archive' =>true,
        'rewrite' => ['slug' => 'movie'],
        'supports' => [
            'title',
            'editor',
            'thumbnail',
        ],
    ]
    );
}
/**
 *   Create custom taxonomy 
 **/


add_action('init','register_taxonomies');

function register_taxonomies(){

    register_taxonomy('movie_type',['movie'],
        [
            'hierarchical' => true,
            'labels' => [
                'name' => __('Categories'),
                'singular_name' => __('Category'),
                'menu_name' => __('Categories'),
            ],
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => ['slug' => __('type')],
        ]

    );
}

 /**
 *   Enqueue scripts and css
 **/

add_action('wp_enqueue_scripts', 'custom_ajax_filter_enqueue_scripts');

function custom_ajax_filter_enqueue_scripts() {

      // Enqueue your custom CSS file
    wp_enqueue_style('custom-style', plugins_url('css/custom-style.css', __FILE__), array(), '1.0');

    // Enqueue jQuery if not already loaded
    wp_enqueue_script('jquery');

    wp_enqueue_script('custom-ajax-filter', plugin_dir_url(__FILE__) . 'js/custom-ajax-filter.js', array('jquery'), '1.0', true);
    wp_localize_script('custom-ajax-filter', 'customAjaxFilter', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
    ));
}

/**
 *   Register the AJAX action for filtering
 **/
 
add_action('wp_ajax_custom_ajax_filter', 'custom_ajax_filter_callback');
add_action('wp_ajax_nopriv_custom_ajax_filter', 'custom_ajax_filter_callback');

// AJAX callback function for filtering
function custom_ajax_filter_callback() {
    $category = $_POST['category'];
    $args = array(
        'post_type' => 'movie',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'movie_type',
                'field' => 'slug',
                'terms' => $category,
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            ?>
    <div class="column column-4">
        <?php if(has_post_thumbnail()) { ?>
            <picture><img width="500" height="250" src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>"> </picture>
        <?php } ?>
        <h4><?php the_title(); ?></h4>
        <?php
           $cats = get_the_terms(get_the_ID(),'movie_type');
           
            if(!empty($cats) ){  ?>
            <ul>
                <?php if(!empty($cats)) { ?>
                    <li>
                    <strong>Category: </strong>
                    <?php foreach ($cats as $cat){
                        echo "<span>$cat->name</span>";
                    }
                    ?>
                    </li>
                <?php } ?>
                      </ul>
         <?php } ?>
        </div>
        <?php
            // Display your post content here
        }
        wp_reset_postdata();
    } else {
        echo 'No posts found.';
    }

    wp_die();
}

/**
 *   Create the SETTINGS PAGE
 **/

// Add the admin menu
function cptaf_add_admin_menu() {
    add_options_page('Custom Post Type AJAX Filter Settings', 'Filter Settings', 'manage_options', 'cptaf_settings', 'cptaf_render_settings_page');
}
add_action('admin_menu', 'cptaf_add_admin_menu');

// Render the settings page
function cptaf_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Custom Post Type AJAX Filter Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cptaf_settings');
            do_settings_sections('cptaf_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register and sanitize the settings
function cptaf_settings_init() {
    register_setting('cptaf_settings', 'cptaf_filter_option', 'sanitize_text_field');

    add_settings_section('cptaf_settings_section', 'Filter Settings', 'cptaf_settings_section_callback', 'cptaf_settings');

    add_settings_field('cptaf_filter_option', 'Filter Option', 'cptaf_filter_option_callback', 'cptaf_settings', 'cptaf_settings_section');
}
add_action('admin_init', 'cptaf_settings_init');

// Render the settings section description
function cptaf_settings_section_callback() {
    echo 'Configure the filter option for the custom post type AJAX filter.';
}

// Render the settings field
function cptaf_filter_option_callback() {
    $filter_option = get_option('cptaf_filter_option');
    ?>
    <input type="text" name="cptaf_filter_option" value="<?php echo esc_attr($filter_option); ?>">
    <?php
}

/**
 *   Create a Shortcode
 **/

function custom_ajax_filter_shortcode($atts) {
    ob_start(); 
    ?>
       <div class="custom-ajax-filter">
                <?php $terms = get_terms(['taxonomy'=>'movie_type']);
                if($terms) { ?>
                    <select id="category-filter">
                        <option value="">Select Category</option>
                        <?php foreach ($terms as $term) { ?>
                            <option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
            
            <div id="filtered-posts-container">
              <!-- The filtered posts will be displayed here -->
           </div>
         </div>       
    <?php
    return ob_get_clean();
}
add_shortcode('custom_ajax_filter', 'custom_ajax_filter_shortcode');
