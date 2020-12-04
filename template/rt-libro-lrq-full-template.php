<?php

/*

  Template Name: Libro Full Template

 */
get_header();
?>
<header class="entry-header has-text-align-center">
    <div class="entry-header-inner section-inner medium">
        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
    </div>
</header>
<?php
the_content();
?>
<?php get_footer(); ?>