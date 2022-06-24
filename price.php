
<?php 
header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8" ?>';


$sitemapText = '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">
-<zipcodepricedata>';
?>

<?php 
$args = array( 'post_type' => 'prices', 'posts_per_page' => 10 );
$the_query = new WP_Query( $args ); 
?>
<?php if ( $the_query->have_posts() ) : ?>
<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
<h2><?php the_title(); ?></h2>
<div class="entry-content">
<?php the_content(); ?> 
</div>
<?php endwhile;
wp_reset_postdata(); ?>
<?php else:  ?>
<p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
<?php endif; ?>

<?php
$sitemapText .= '</zipcodepricedata></urlset>';

$sitemap = fopen("price_xml.xml", "w") or die("Unable to open file!");

fwrite($sitemap, $sitemapText);
fclose($sitemap);
?>