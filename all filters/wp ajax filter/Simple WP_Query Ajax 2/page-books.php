<?php
/*
 * Template Name: Books
 */

get_header();
?>

<section id="primary" class="content-area">
	<div id="content" class="site-content" role="main">    
	<?php
    if( have_posts() ):
        while( have_posts() ): the_post();
            get_template_part('content');
        endwhile;
    endif;
    ?>
    
    <div class="entry-content">
        <form id="genre-search">
            <input type="text" class="text-search" placeholder="Search books..." />
            <input type="submit" value="Search" id="submit-search" />
        </form>
        <div id="genre-filter">
            <?php echo get_genre_filters(); ?>
        </div>
        <div id="genre-results"></div>
    </div>
    
	</div>
</section>

<?php get_footer(); ?>