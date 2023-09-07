<?php
/**
 * Contact page template
 *
 * @package    BS Bludit
 * @subpackage Templates
 * @category   Content
 * @since      1.0.0
 */

?>
<article class="site-article" role="article">

	<?php Theme :: plugins( 'pageBegin' ); ?>

	<header class="page-header">
		<h1><?php echo $page->title(); ?></h1>

		<?php if ( $page->description() ) {
			printf(
				'<p class="page-description page-description-single">%s</p>',
				$page->description()
			);
		} ?>
	</header>

	<div class="page-content" itemprop="articleBody">
		<?php echo $page->contentBreak(); ?>
	</div>

	<?php if ( $page->readMore() ) : ?>
		<p><a class="button" href="<?php echo $page->permalink(); ?>" role="button"><?php echo $L->get( 'Read More' ); ?></a></p>
	<?php endif; ?>

	<?php Theme :: plugins( 'pageEnd' ); ?>

</article>
