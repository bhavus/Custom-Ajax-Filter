
<?php /* Template Name: Template Movies */ get_header(); ?>

<?php

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


    $movies = new WP_Query($args); ?>
    <main>

        <div class="movie_container" style="width: 80%; margin: 0 auto;">
            <br>

    <?php    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            ?>
    <div class="column column-4">
        <?php if(has_post_thumbnail()) { ?>
            <picture><img width="500" height="250" src="<?php the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>"> </picture>
        <?php } ?>
        <h4><?php the_title(); ?></h4>
        
          <div class="custom-ajax-filter">
                <?php $terms = get_terms(['taxonomy'=>'movie_type']);
                if($terms) { ?>
                    <select id="category-filter">
                        <option value="">Select Category</option>
                        <?php foreach ($terms as $term) { ?>
                            <option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
                        <?php  } ?>
                    </select>
                <?php } ?>
            
            <div id="filtered-posts-container">
              <!-- The filtered posts will be displayed here -->
           </div>
         </div> 
    </div>
    <?php
            // Display your post content here
        } //end while
        wp_reset_postdata();
    } else {
        echo 'No posts found.';
    }



?>
        </div>
    </main>


<?php  get_footer(); ?>