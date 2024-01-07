<?php
/**
 * Front page slider
 *
 * @package    Configure 8
 * @subpackage Templates
 * @category   Partials
 * @since      1.0.0
 */

// Import namespaced functions.
use function CFE_Func\{
	plugin,
	lang,
	is_rtl
};
use function CFE_Tags\{
	page_description,
	icon
};

// Get published posts, full objects.
$posts = $pages->getPublishedDB();
$posts = array_slice( $posts, 0, plugin()->slider_number() );

// Cover image class.
$cover_class = 'full-cover-image cover-overlay';

if (
	'blend' == plugin()->cover_style() &&
	is_array( plugin()->cover_blend_use() ) &&
	in_array( 'slider', plugin()->cover_blend_use() )
) {
	$cover_class = 'full-cover-image cover-blend';
}

// Icons.
$link_arrow = 'arrow-right-light';
$prev_arrow = 'arrow-left-light';
$next_arrow = 'arrow-right-light';
if ( is_rtl() ) {
	$link_arrow = 'arrow-left-light';
	$prev_arrow = 'arrow-right-light';
	$next_arrow = 'arrow-left-light';
}

// Arrow options.
if ( 'angle' == plugin()->slider_arrows() ) {
	$prev_arrow = 'angle-left-light';
	$next_arrow = 'angle-right-light';

	if ( is_rtl() ) {
		$prev_arrow = 'angle-right-light';
		$next_arrow = 'angle-left-light';
	}
} elseif ( 'angles' == plugin()->slider_arrows() ) {
	$prev_arrow = 'angles-left-light';
	$next_arrow = 'angles-right-light';

	if ( is_rtl() ) {
		$prev_arrow = 'angles-right-light';
		$next_arrow = 'angles-left-light';
	}
}

// Slider markup.
if ( $posts ) : ?>
<div id="front-page-slider" class="slider-wrap front-page-slider hide-if-no-js">
<?php foreach ( $posts as $post ) :
	$slide = new Page( $post );

	?>
	<div class="<?php echo $cover_class; ?> page-slide">
		<figure>
			<img src="<?php echo $slide->coverImage(); ?>" role="presentation">
		</figure>
		<div class="cover-header" data-cover-header>
			<h2 class="cover-title"><a href="<?php echo $slide->permalink(); ?>"><?php echo $slide->title(); ?></a></h2>
			<p class="cover-description"><?php echo page_description( $slide->key() ); ?></p>
			<a class="slider-more" href="<?php echo $slide->permalink(); ?>"><span><?php lang()->p( 'Read More' ); ?><span> <?php echo icon( $link_arrow, true, 'slider-more-icon' ); ?></a>
		</div>
	</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<script>
jQuery(document).ready( function($) {

	// Instantiate Slick slider for the front page slider.
	$( '#front-page-slider' ).slick({
		arrows         : <?php echo ( 'none' != plugin()->slider_arrows() ? 'true' : 'false' ) ?>,
		prevArrow      : '<?php echo icon( $prev_arrow, true, 'slick-prev' ); ?>',
        nextArrow      : '<?php echo icon( $next_arrow, true, 'slick-next' ); ?>',
		dots           : true,
		slidesToScroll : 1,
		autoplay       : true,
		autoplaySpeed  : 3000,
		infinite       : true,
		adaptiveHeight : false,
		speed          : 800,
		pauseOnHover   : false,
		fade           : <?php echo ( 'slide' == plugin()->slider_animate() ? 'false' : 'true' ) ?>,
		cssEase        : 'ease-in-out',
		rtl            : <?php echo ( is_rtl() ? 'true' : 'false' ); ?>
	});

	// Show slides when page is loaded.
	$( '.page-slide' ).css( 'opacity', '1' );
});
</script>