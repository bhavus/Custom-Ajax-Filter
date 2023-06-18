<?php
//Register books post type
function codex_custom_init() {
    $args = array(
		'public' => true,
		'label'  => 'Books'
    );
    register_post_type( 'book', $args );
}
add_action( 'init', 'codex_custom_init' );

//Register book genre taxonomy
add_action( 'init', 'create_book_tax' );

function create_book_tax() {
	register_taxonomy(
		'genre',
		'book',
		array(
			'label' => __( 'Genre' ),
			'rewrite' => array( 'slug' => 'genre' ),
			'hierarchical' => true
		)
	);
}

//Get Genre Filters
function get_genre_filters()
{
	$terms = get_terms('genre');
	$filters_html = false;
	
	if( $terms ):
		$filters_html = '<ul>';
		
		foreach( $terms as $term )
		{
			$term_id = $term->term_id;
			$term_name = $term->name;
		
			$filters_html .= '<li class="term_id_'.$term_id.'">'.$term_name.'<input type="checkbox" name="filter_genre[]" value="'.$term_id.'"></li>';	
		}
		$filters_html .= '<li class="clear-all">Clear All</li>';
		$filters_html .= '</ul>';
		
		return $filters_html;
	endif;
}

//Enqueue Ajax Scripts
function enqueue_genre_ajax_scripts() {
    wp_register_script( 'genre-ajax-js', get_bloginfo('template_url') . '/js/genre.js', array( 'jquery' ), '', true );
    wp_localize_script( 'genre-ajax-js', 'ajax_genre_params', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script( 'genre-ajax-js' );
}
add_action('wp_enqueue_scripts', 'enqueue_genre_ajax_scripts');

//Add Ajax Actions
add_action('wp_ajax_genre_filter', 'ajax_genre_filter');
add_action('wp_ajax_nopriv_genre_filter', 'ajax_genre_filter');

//Construct Loop & Results
function ajax_genre_filter()
{
	$query_data = $_GET;
	
	$genre_terms = ($query_data['genres']) ? explode(',',$query_data['genres']) : false;
	
	$tax_query = ($genre_terms) ? array( array(
		'taxonomy' => 'genre',
		'field' => 'id',
		'terms' => $genre_terms
	) ) : false;
	
	$search_value = ($query_data['search']) ? $query_data['search'] : false;
	
	$paged = (isset($query_data['paged']) ) ? intval($query_data['paged']) : 1;
	
	$book_args = array(
		'post_type' => 'book',
		's' => $search_value,
		'posts_per_page' => 2,
		'tax_query' => $tax_query,
		'paged' => $paged
	);
	$book_loop = new WP_Query($book_args);
	
	if( $book_loop->have_posts() ):
		while( $book_loop->have_posts() ): $book_loop->the_post();
			get_template_part('content');
		endwhile;
		
		echo '<div class="genre-filter-navigation">';
		$big = 999999999;
		echo paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, $paged ),
			'total' => $book_loop->max_num_pages
		) );
		echo '</div>';	
	else:
		get_template_part('content-none');
	endif;
	wp_reset_postdata();
	
	die();
}