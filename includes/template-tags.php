<?php
/**
 * Template tags
 *
 * @package    Configure 8
 * @subpackage Includes
 * @category   Templates
 * @since      1.0.0
 */

namespace CFE_Tags;

// Stop if accessed directly.
if ( ! defined( 'BLUDIT' ) ) {
	die( 'You are not allowed direct access to this file.' );
}

// Alias namespaces.
use CFE\Classes\{
	Front as Front
};

// Import namespaced functions.
use function CFE_Func\{
	helper,
	site,
	url,
	site_domain,
	lang,
	page,
	is_rtl,
	user_logged_in,
	text_replace,
	hex_to_rgb,
	theme,
	favicon_exists,
	loop_data,
	loop_is_static,
	get_nav_position,
	get_config_styles,
	has_cover,
	full_cover,
	asset_min,
	numbers_to_text
};

/**
 * Loading screen
 *
 * Shows a loading screen until the document
 * (web page) is fully loaded.
 *
 * @since  1.0.0
 * @return mixed Returns the screen markup or null.
 */
function page_loader() {

	// Return null if in debug mode.
	if ( defined( 'DEBUG_MODE' ) && DEBUG_MODE ) {
		return null;
	}

	if ( theme() && ! theme()->page_loader() ) {
		return null;
	} else {
		ob_start();
		include( THEME_DIR . 'views/utility/loader.php' );
		return ob_get_clean();
	}
}

/**
 * Favicon tag
 *
 * Returns the site icon meta tag.
 *
 * @since  1.0.0
 * @return mixed Returns the icon tag or null.
 */
function favicon_tag() {

	// If plugin has icon URL.
	if ( theme() && theme()->favicon_src() ) {

		// Get icon src.
		$favicon = theme()->favicon_src();

		// Get the image file extension.
		$info = pathinfo( $favicon );
		$type = $info['extension'];

		return sprintf(
			'<link rel="icon" href="%s" type="image/%s">',
			$favicon,
			$type
		);
	}
	return null;
}

/**
 * Load scheme stylesheet
 *
 * @since  1.0.0
 * @return string Returns a link tag for the `<head>`.
 */
function scheme_stylesheet( $type = '' ) {

	// Stop if no scheme type.
	if ( empty( $type ) || ! theme() ) {
		return null;
	}

	// Get options from the theme plugin.
	$colors = theme()->color_scheme();
	$fonts  = theme()->font_scheme();
	$html   = '';

	// Get minified if not in debug mode.
	$suffix = asset_min();

	// Color scheme stylesheet.
	if ( 'colors' === $type && 'default' != $colors ) {
		$html = helper() :: css( "assets/css/schemes/colors/{$colors}/style{$suffix}.css" );
	}

	// Typography scheme stylesheet.
	if ( 'fonts' == $type && 'default' != $fonts ) {
		$html .= helper() :: css( "assets/css/schemes/fonts/{$fonts}/style{$suffix}.css" );
	}
	return $html;
}

/**
 * Load font files
 *
 * @since  1.0.0
 * @return mixed Returns link tags for the `<head>` or null.
 */
function load_font_files() {

	// Stop if the theme plugin is not installed.
	if ( ! theme() ) {
		return null;
	}

	// Get the font scheme setting.
	$fonts = theme()->font_scheme();

	// Stop if default font, no directory exists.
	if ( 'default' == $fonts || empty( theme()->font_scheme() ) ) {
		return null;
	}
	$valid = [ 'woff', 'woff2', 'otf', 'ttf' ];
	$files = scandir( THEME_DIR . "assets/fonts/{$fonts}/" );
	$tags  = '';

	foreach ( $files as $font => $file ) {

		$href = DOMAIN_THEME . "assets/fonts/{$fonts}/{$file}";
		$tab = '	';

		// Get the font file extension.
		$info = pathinfo( $file );
		$type = $info['extension'];
		if ( 'ttf' == $info ) {
			$type = 'truetype';
		}

		if ( ! in_array( $type, $valid ) ) {
			$tags  .= '';
		} else {
			$tags .= sprintf(
				'<link rel="preload" href="%s" as="font" type="font/%s" crossorigin="anonymous">',
				$href,
				$type
			) . "\n" . $tab;
		}
	}
	return $tags;
}

/**
 * Config styles
 *
 * Returns a CSS block of override styles
 *
 * @since  1.0.0
 * @return string
 */
function config_styles() {

	$get_nav_pos = get_nav_position();

	$styles = '<style>:root {';

	// Loader image overlay.
	if ( theme() && ! empty( theme()->loader_bg_color() ) ) {
		$styles .= sprintf(
			'--cfe-loader-overlay--bg-color: %s;',
			theme()->loader_bg_color()
		);
	}

	// Loader image text.
	if ( theme() && ! empty( theme()->loader_text_color() ) ) {
		$styles .= sprintf(
			'--cfe-loader--text-color: %s;',
			theme()->loader_text_color()
		);
	}

	// General spacing.
	if ( theme() && ! empty( theme()->horz_spacing() ) ) {
		$styles .= sprintf(
			'--cfe-spacing--horz: %srem;',
			theme()->horz_spacing()
		);
	}
	if ( theme() && ! empty( theme()->vert_spacing() ) ) {
		$styles .= sprintf(
			'--cfe-spacing--vert: %srem;',
			theme()->vert_spacing()
		);
	}

	// Body color.
	if ( theme() &&
		! empty( theme()->color_body() ) &&
		'#ffffff' != theme()->color_body()
	) {
		$styles .= sprintf(
			'--cfe-bg-color: %s;',
			theme()->color_body()
		);
	}

	// Header logo width.
	if ( theme() ) {
		$styles .= sprintf(
			'--cfe-site-logo--max-width: %s;',
			theme()->logo_width_std() . 'px'
		);
		$styles .= sprintf(
			'--cfe-site-logo--max-width--mobile: %s;',
			theme()->logo_width_mob() . 'px'
		);
	}

	// Cover image overlay.
	if ( theme() && ! empty( theme()->cover_overlay() ) ) {
		$styles .= sprintf(
			'--cfe-cover-overlay--bg-color: %s;',
			theme()->cover_overlay()
		);
	}

	// Cover image text.
	if ( theme() && ! empty( theme()->cover_text_color() ) ) {
		$styles .= sprintf(
			'--cfe-cover--text-color: %s;',
			theme()->cover_text_color()
		);
	}

	// Cover image text shadow.
	if ( theme() && ! theme()->cover_text_shadow() ) {
		$styles .= '--cfe-cover--text-shadow: none;';
	}

	/**
	 * Main navigation position
	 *
	 * CSS Flexbox reverses in RTL languages. The nav position
	 * settings use left and right options so in RTL the flex
	 * direction needs to adjust accordingly.
	 */
	if ( 'left' === $get_nav_pos ) {
		if ( ! is_rtl() ) {
			$styles .= '--cfe-site-header-wrap--flex-direction: row-reverse;';
		}
		$styles .= '--cfe-site-header-wrap--flex-direction-tablet: column;';

	} elseif ( 'above' === $get_nav_pos ) {
		$styles .= '--cfe-site-header-wrap--flex-direction: column-reverse;';
		$styles .= '--cfe-site-header-wrap--align-items: flex-start;';
		$styles .= '--cfe-site-header-wrap--flex-direction-tablet: column-reverse;';
		$styles .= '--cfe-site-header-wrap--justify-content--tablet: center;';

	} elseif ( 'below' === $get_nav_pos ) {
		$styles .= '--cfe-site-header-wrap--flex-direction: column;';
		$styles .= '--cfe-site-header-wrap--align-items: flex-start;';
		$styles .= '--cfe-site-header-wrap--flex-direction-tablet: column;';
		$styles .= '--cfe-site-header-wrap--justify-content--tablet: center;';

	// Default is right.
	} else {
		if ( is_rtl() ) {
			$styles .= '--cfe-site-header-wrap--flex-direction: row-reverse;';
		}
	}
	$styles .= '}</style>';

	return $styles;
}

/**
 * Custom CSS
 *
 * CSS from the plugin appearance options.
 *
 * @since  1.0.0
 * @return mixed Returns a CSS style block or null.
 */
function custom_css() {

	if ( theme() && empty( theme()->custom_css() ) ) {
		return null;
	}

	$style  = '<style>';
	$style .= theme()->custom_css();
	$style .= '</style>';

	return $style;
}

/**
 * Body classes
 *
 * For the class attribute on the `<body>` element.
 *
 * @since  1.0.0
 * @return string Returns a string of classes.
 */
function body_classes() {

	// Set up classes.
	$classes = [];

	// Get loop data.
	$loop_data = loop_data();

	// Language direction.
	if ( is_rtl() ) {
		$classes[] = 'rtl';
	} else {
		$classes[] = 'ltr';
	}

	// User logged in/out.
	if ( user_logged_in() ) {
		$classes[] = 'user-logged-in';
	} else {
		$classes[] = 'user-logged-out';
	}

	// User toolbar.
	if ( user_logged_in() && theme() && false != user_toolbar() ) {
		$classes[] = 'toolbar-active';
	}

	// Main navigation position.
	$nav_position = get_nav_position();
	$classes[]    = "main-nav-{$nav_position}";

	// Home page.
	if ( 'home' == url()->whereAmI() ) {
		$classes[] = 'home';

		// If home is not static.
		if ( ! site()->homepage() ) {
			$classes[] = 'loop';
		}
	}

	// If loop, not page.
	if ( 'page' != url()->whereAmI() ) {

		$classes[] = 'loop';

		// Posts loop style.
		$loop_style = $loop_data['style'];
		$classes[]  = "loop-style-{$loop_style}";

		// Posts loop template.
		if ( theme() ) {
			if ( 'grid' == theme()->content_style() ) {
				$classes[] = 'loop-template-grid';
			} elseif ( 'full' == theme()->content_style() ) {
				$classes[] = 'loop-template-full';
			} else {
				$classes[] = 'loop-template-list';
			}
		} else {
			$classes[] = 'loop-template-list';
		}

		// Loop sidebar.
		if ( theme() && 'bottom' == theme()->sidebar_in_loop() ) {
			$classes[] = 'template-sidebar-bottom';
		} elseif ( theme() && 'none' === theme()->sidebar_in_loop() ) {
			$classes[] = 'template-no-sidebar';
		} else {
			$classes[] = 'template-sidebar';
		}
	}

	// If loop, not home.
	if ( 'blog' == url()->whereAmI() ) {

		$classes[] = 'loop-not-home';

		// Templates for the static loop page.
		if ( $loop_data['template'] ) {
			$templates = explode( ' ', $loop_data['template'] );

			foreach ( $templates as $template ) {

				// Exclude `full-cover` template if no cover image or paged.
				if ( str_contains( $template, 'full-cover' ) ) {
					if ( ! has_cover() ) {
						$classes[] = '';
					} elseif ( isset( $_GET['page'] ) ) {
						$classes[] = '';
					} else {
						$classes[] = "template-{$template}";
					}
				} else {
					$classes[] = "template-{$template}";
				}
			}
		}
	}

	// If singular content.
	if ( 'page' == url()->whereAmI() ) {

		// If static content.
		if ( page()->isStatic() ) {
			$classes[] = 'page';

		// If not static content.
		} else {
			$classes[] = 'post';
		}
	}

	// Page templates.
	if (
		'search' != url()->whereAmI() &&
		'page' == url()->whereAmI() &&
		! empty( page()->template() ) &&
		! ctype_space( page()->template() )
	) {
		if ( page()->template() ) {
			$templates = explode( ' ', page()->template() );

			foreach ( $templates as $template ) {

				// Exclude `full-cover` template if no cover image or paged.
				if ( str_contains( page()->template(), 'full-cover' ) ) {
					if ( ! has_cover() ) {
						$classes[] = '';
					} elseif ( isset( $_GET['page'] ) ) {
						$classes[] = '';
					} else {
						$classes[] = "template-{$template}";
					}
				} else {
					$classes[] = "template-{$template}";
				}
			}
		}
	}

	// Sticky sidebar.
	if ( theme() && theme()->sidebar_sticky() ) {
		$classes[] = 'has-sticky-sidebar';
	}

	// Return a string of space-separated classes.
	return implode( ' ', $classes );
}

/**
 * Page Schema
 *
 * Conditional Schema attributes for `<div id="page"`.
 *
 * @since  1.0.0
 * @return string Returns the relevant itemtype.
 */
function page_schema() {

	if ( 'search' == url()->whereAmI() ) {
			echo 'SearchResultsPage';
			return;
	}

	// Change page slugs and template names as needed.
	if ( str_contains( page()->template(), 'profile' ) ) {
		$itemtype = 'ProfilePage';

	} elseif (
		'about'    == page()->slug() ||
		'about-us' == page()->slug() ||
		str_contains( page()->template(), 'about' )
	) {
		$itemtype = 'AboutPage';

	} elseif (
		'contact'    == page()->slug() ||
		'contact-us' == page()->slug() ||
		str_contains( page()->template(), 'contact' )
	) {
		$itemtype = 'ContactPage';

	} elseif (
		'faq'  == page()->slug() ||
		'faqs' == page()->slug() ||
		str_contains( page()->template(), 'faq' )
	) {
		$itemtype = 'QAPage';

	} elseif (
		'cart'          == page()->slug() ||
		'shopping-cart' == page()->slug() ||
		str_contains( page()->template(), 'cart' ) ||
		str_contains( page()->template(), 'checkout' )
	) {
		$itemtype = 'CheckoutPage';

	} elseif (
		'blog'   == url()->whereAmI() ||
		( 'home' == url()->whereAmI() && ! site()->homepage() )
	) {
		if ( theme() && 'news' == theme()->loop_style() ) {
			$itemtype = 'WebPage';
		} else {
			$itemtype = 'Blog';
		}

	} else {
		$itemtype = 'WebPage';
	}
	echo 'https://schema.org/' . $itemtype;
}

/**
 * Page header
 *
 * Returns the page title and description
 *
 * @since  1.0.0
 * @return string Returns the header markup.
 */
function page_header() {

	$wrapper     = 'header';
	$heading     = 'h1';
	$description = page()->description();
	$sticky_icon = '';

	/**
	 * Do not use `<header>` element for the
	 * `full-cover` page template because
	 * this will be used inside the site
	 * header; a `<header>` element must not
	 * contain another `<header>` element.
	 */
	if ( full_cover() ) {
		$wrapper = 'div';
	}

	// Site title is `h1` on front page; only one per page.
	if ( 'page' == url()->whereAmI() ) {
		if ( page()->key() == site()->getField( 'homepage' ) ) {
			$heading = 'h2';
		}
	}

	// If the page is sticky.
	if ( page()->sticky() ) {
		$sticky_icon = sticky_icon( 'false', 'sticky-icon-heading' ) . ' ';
	}

	$html = sprintf(
		'<%s class="page-header" data-page-header>',
		$wrapper
	);
	$html .= sprintf(
		'<%s class="page-title">%s%s</%s>',
		$heading,
		$sticky_icon,
		page()->title(),
		$heading
	);

	if ( ! empty( $description ) && ! ctype_space( $description ) ) {
		$html .= sprintf(
			'<p class="page-description page-description-single">%s</p>',
			$description
		);
	}
	$html .= "</{$wrapper}>";

	return $html;
}

/**
 * Cover header
 *
 * Returns the page title and description
 *
 * @since  1.0.0
 * @return string Returns the header markup.
 */
function cover_header() {

	$loop_data   = loop_data();
	$heading_el  = 'h1';
	$page_title  = page()->title();
	$description = page()->description();

	// Site title is `h1` on front page; only one per page.
	if ( 'page' == url()->whereAmI() ) {
		if ( page()->key() == site()->getField( 'homepage' ) ) {
			$heading_el = 'h2';
		}
	}

	// Conditional heading & description.
	if (
		'blog' == url()->whereAmI() &&
		'page' == $loop_data['location']
	) {
		$class       = 'loop-page-description';
		$page_title  = loop_title();
		$description = loop_description();

	} elseif (
		'home' == url()->whereAmI() ||
		'blog' == url()->whereAmI()
	) {
		$class       = 'loop-page-description';
		$page_title  = lang()->get( 'Blog' );
		if ( theme() ) {
			if ( ! empty( theme()->loop_title() ) ) {
				$page_title = ucwords( theme()->loop_title() );
			} elseif ( theme()->loop_style() ) {
				$page_title = ucwords( theme()->loop_style() );
			}
		}
		$description = loop_description();

	} elseif ( 'category' == url()->whereAmI() ) {
		$get_cat     = new \Category( url()->slug() );
		$class       = 'category-page-description';
		$page_title  = $get_cat->name();
		$description = text_replace( 'posts-loop-desc-cat', $get_cat->name() );

	} elseif ( 'tag' == url()->whereAmI() ) {
		$get_tag     = new \Tag( url()->slug() );
		$class       = 'tag-page-description';
		$page_title  = $get_tag->name();
		$description = text_replace( 'posts-loop-desc-tag', $get_tag->name() );
	}

	$html = '<div class="cover-header" data-cover-header>';
	$html .= sprintf(
		'<%s class="cover-title">%s</%s>',
		$heading_el,
		$page_title,
		$heading_el
	);

	if ( ! empty( $description ) && ! ctype_space( $description ) ) {
		$html .= sprintf(
			'<p class="cover-description">%s</p>',
			$description
		);
	}

	// Full cover down icon.
	$icon = 'angle-down-light';
	if ( theme() && theme()->cover_icon() ) {
		$icon = theme()->cover_icon();
	}

	if ( full_cover() ) {
		$html .= sprintf(
			'<a href="#content" class="button intro-scroll hide-if-no-js"><span class="screen-reader-text">%s</span>%s</a>',
			lang()->get( 'Scroll to content' ),
			icon( $icon )
		);
	}
	$html .= '</div>';

	return $html;
}

/**
 * Get user toolbar
 *
 * Includes the toolbar file if user is
 * logged in and theme plugin value is true.
 *
 * @since  1.0.0
 * @return mixed Returns the toolbar markup or null.
 */
function get_toolbar() {

	ob_start();
	include( THEME_DIR . 'views/utility/toolbar.php' );

	return ob_get_clean();
}

/**
 * Print user toolbar
 *
 * @since  1.0.0
 * @return mixed Returns the `get_toolbar()` function or false.
 */
function user_toolbar() {

	if (
		user_logged_in() && theme() &&
		( 'enabled' == theme()->show_user_toolbar() ||
		'frontend' == theme()->show_user_toolbar() )
	) {
		return get_toolbar();
	}
	return false;
}

/**
 * Print site logo
 *
 * @since  1.0.0
 * @return mixed Returns null if no logo set.
 */
function site_logo() {

	if ( empty( site()->logo() ) ) {
		return null;
	}

	?>
	<div class="site-logo" data-site-logo>
		<figure>
			<a href="<?php echo site_domain(); ?>">
				<img src="<?php echo site()->logo(); ?>" alt="<?php echo site()->title(); ?>" width="80">
			</a>
			<figcaption class="screen-reader-text"><?php echo site()->title(); ?></figcaption>
		</figure>
	</div>
	<?php
}

/**
 * Menu toggle
 *
 * Returns the mobile menu icon or text.
 *
 * @since  1.0.0
 * @return string
 */
function menu_toggle( $toggle = '' ) {

	// If an icon option is set (plugin default is bars).
	if ( theme() && 'none' != theme()->main_nav_icon() ) {

		// Bars icon.
		$icon  = 'bars';
		$class = 'nav-icon-bars';

		// Dots icon.
		if ( 'dots' == theme()->main_nav_icon() ) {
			$icon  = 'dots-h';
			$class = 'nav-icon-dots';
		}
		return icon( $icon, true, $class );

	// If no icon option and custom text in the tag..
	} elseif ( 'none' == theme()->main_nav_icon() && ! empty( $toggle ) ) {
		return $toggle;
	}

	// Default, text.
	return lang()->get( 'Menu' );
}

/**
 * Page ID
 *
 * Returns an ID based on the page type
 * and the page key.
 *
 * @since  1.0.0
 * @return mixed Returns the page ID or null.
 */
function page_id() {

	// Null if in search results (global errors).
	if ( 'search' == url()->whereAmI() ) {
		return null;
	}

	// Conditional page ID, static or not.
	$id = '';
	if (
		( 'blog' == url()->whereAmI() && 'home' != url()->whereAmI() ) ||
		( 'home' == url()->whereAmI() && 'page' != url()->whereAmI() )
	) {
		$id = 'loop-page';
		if ( ! isset( $_GET['page'] ) ) {
			$id .= '-' . 1;
		} else {
			$id .= '-' . $_GET['page'];
		}

	} elseif ( page()->isStatic() && 'blog' != url()->whereAmI() ) {
		$id = 'page-' . page()->key();
	} else {
		$id = 'post-' . page()->key();
	}

	// String replace not necessary but just in case...
	return strtolower( str_replace( [ '_', ' ' ], '-', $id ) );
}

/**
 * Content template
 *
 * @since  1.0.0
 * @return string Returns the relevant template.
 */
function content_template() {

	// Blog template when a static home page is used.
	if ( 'page' == url()->whereAmI() && page()->slug() == str_replace( '/', '', site()->getField( 'uriBlog' ) ) ) {

		if ( theme() ) {
			if ( 'grid' == theme()->content_style() ) {
				$template = 'views/content/posts-grid.php';
			} elseif ( 'full' == theme()->content_style() ) {
				$template = 'views/content/posts-full.php';
			} else {
				$template = 'views/content/posts-list.php';
			}
		} else {
			$template = 'views/content/posts-list.php';
		}

	// Page templates.
	} elseif ( 'page' == url()->whereAmI() ) {

		// Static home page.
		if ( site()->getField( 'homepage' ) && page()->slug() == site()->getField( 'homepage' ) ) {
			$template = 'views/content/front-page.php';

		/**
		 * Static page with template applied, excluding some templates.
		 * Sidebar templates are excluded because sidebar location
		 * is achieved with CSS based on body class.
		 *
		 * @see body_classes()
		 *
		 * The `full-cover` template is excluded because a different
		 * site header is used prior to calling this function.
		 */
		} elseif ( page()->template() && page()->isStatic() ) {
			$template = 'views/content/' . str_replace( [ ' ', 'full-cover', 'no-sidebar', 'sidebar-bottom' ], '', page()->template() ) . '.php';
			if ( file_exists( THEME_DIR . $template ) ) {
				$template = $template;
			} else {
				$template = 'views/content/page.php';
			}

		// Static page.
		} elseif ( page()->isStatic() ) {
			$template = 'views/content/page.php';

		// Sticky page (post).
		} elseif ( page()->sticky() ) {
			$template = 'views/content/sticky.php';

		// Default (post) page.
		} else {
			$template = 'views/content/post.php';
		}

	// Default to posts loop.
	} else {
		if ( theme() ) {
			if ( 'grid' == theme()->content_style() ) {
				$template = 'views/content/posts-grid.php';
			} elseif ( 'full' == theme()->content_style() ) {
				$template = 'views/content/posts-full.php';
			} else {
				$template = 'views/content/posts-list.php';
			}
		} else {
			$template = 'views/content/posts-list.php';
		}
	}
	return $template;
}

/**
 * Loop content style
 *
 * Gets loop content style from the theme plugin.
 *
 * @since  1.0.0
 * @return string Returns the loop content style.
 */
function loop_content_style() {

	// Conditional template.
	$template = 'list';
	if ( theme() ) {
		if ( 'grid' === theme()->content_style() ) {
			$template = 'grid';
		} elseif ( 'full' === theme()->content_style() ) {
			$template = 'full';
		}
	}
	return $template;
}

/**
 * Loop post count
 *
 * Gets loop post count from the loop data.
 *
 * @since  1.0.0
 * @return integer Returns the loop post count.
 */
function loop_post_count() {
	$loop_data = loop_data();
	return $loop_data['post_count'];
}

/**
 * Loop show posts
 *
 * Gets loop posts per page from the loop data.
 *
 * @since  1.0.0
 * @return integer Returns the loop posts per page.
 */
function loop_show_posts() {
	$loop_data = loop_data();
	return $loop_data['show_posts'];
}

/**
 * Loop location
 *
 * Gets loop location from the loop data.
 *
 * @since  1.0.0
 * @return string Returns the loop location.
 */
function loop_location() {
	$loop_data = loop_data();
	return $loop_data['location'];
}

/**
 * Loop key
 *
 * Gets loop key from the loop data.
 *
 * @since  1.0.0
 * @return mixed Returns the loop key or false.
 */
function loop_key() {
	$loop_data = loop_data();
	return $loop_data['key'];
}

/**
 * Loop URL
 *
 * Gets loop URL from the loop data.
 *
 * @since  1.0.0
 * @return string Returns the loop URL.
 */
function loop_url() {
	$loop_data = loop_data();
	return $loop_data['url'];
}

/**
 * Loop slug
 *
 * Gets loop slug from the loop data.
 *
 * @since  1.0.0
 * @return string Returns the loop slug.
 */
function loop_slug() {
	$loop_data = loop_data();
	return $loop_data['slug'];
}

/**
 * Loop template
 *
 * Gets loop template from the loop data.
 *
 * @since  1.0.0
 * @return mixed Returns the loop template or false.
 */
function loop_template() {
	$loop_data = loop_data();
	return $loop_data['template'];
}

/**
 * Loop style
 *
 * Gets loop style from the loop data.
 *
 * @since  1.0.0
 * @return string Returns the loop style.
 */
function loop_style() {
	$loop_data = loop_data();
	return $loop_data['style'];
}

/**
 * Loop title
 *
 * Gets loop title from the loop data.
 *
 * @since  1.0.0
 * @return string Returns the loop title.
 */
function loop_title() {
	$loop_data = loop_data();
	return $loop_data['title'];
}

/**
 * Loop description
 *
 * Gets loop description from the loop data.
 *
 * @since  1.0.0
 * @return string Returns the loop description.
 */
function loop_description() {
	$loop_data = loop_data();
	return $loop_data['description'];
}

/**
 * Loop cover image
 *
 * Gets loop cover image from the loop data.
 *
 * @since  1.0.0
 * @return mixed Returns the loop cover image or false.
 */
function loop_cover() {
	$loop_data = loop_data();
	return $loop_data['cover'];
}

/**
 * Posts loop header
 *
 * prints a header section in a posts loop
 * page: posts, category, tag.
 *
 * @since  1.0.0
 * @return mixed
 */
function posts_loop_header() {

	// Null if in search results (global errors).
	if ( 'search' == url()->whereAmI() ) {
		return null;
	}

	// Header variables.
	$heading     = '';
	$description = '';
	$class       = '';
	$format_slug =  ucwords( str_replace( [ '-', '_' ], '', url()->slug() ) );
	$loop_data   = loop_data();
	$loop_page   = '';

	// If on a loop page other than the first.
	if ( isset( $_GET['page'] ) && $_GET['page'] > 1 ) {
		$loop_page = sprintf(
			' &rsaquo; %s %s',
			lang()->get( 'page' ),
			$_GET['page']
		);
	}

	// Conditional heading & description.
	if ( 'home' == url()->whereAmI() ) {
		$heading  = lang()->get( 'Blog' ) . $loop_page;
		if ( theme() && theme()->loop_style() ) {
			$heading = ucwords( theme()->loop_style() . $loop_page );
		}

	} elseif ( 'blog' == url()->whereAmI() ) {
		$class   = 'loop-page-description';
		$heading = ucwords( $loop_data['slug'] . $loop_page );

	} elseif ( 'category' == url()->whereAmI() ) {
		$get_cat     = new \Category( url()->slug() );
		$class       = 'category-page-description';
		$heading     = $get_cat->name();
		$description = text_replace( 'posts-loop-desc-cat', $get_cat->name() );

	} elseif ( 'tag' == url()->whereAmI() ) {
		$get_tag     = new \Tag( url()->slug() );
		$class       = 'tag-page-description';
		$heading     = $get_tag->name();
		$description = text_replace( 'posts-loop-desc-tag', $get_tag->name() );
	}

	// SEt up the header markup.
	$html = '<header class="page-header posts-loop-header">';

	if ( ! empty( $heading ) && ! ctype_space( $heading ) ) {
		$html .= sprintf(
			'<h3 class="posts-loop-heading">%s</h3>',
			$heading
		);
	}

	if ( ! empty( $description ) && ! ctype_space( $description ) ) {
		$html .= sprintf(
			'<p class="page-description %s">%s</p>',
			$class,
			$description
		);
	}
	$html .= '</header>';

	// Print nothing if site home or singular page.
	if ( 'page' == url()->whereAmI() ) {
		return '';
	}
	return $html;
}

/**
 * Get SVG icon
 *
 * @since  1.0.0
 * @param  string $$file Name of the SVG file.
 * @return array
 */
function icon( $filename = '', $wrap = false, $class = '' ) {

	$exists = file_exists( sprintf(
		THEME_DIR . 'assets/images/svg-icons/%s.svg',
		$filename
	) );
	if ( ! empty( $filename ) && $exists ) {

		if ( true == $wrap ) {
			return sprintf(
				'<span class="theme-icon %s">%s</span>',
				$class,
				file_get_contents( THEME_DIR . "assets/images/svg-icons/{$filename}.svg" )
			);
		} else {
			return file_get_contents( THEME_DIR . "assets/images/svg-icons/{$filename}.svg" );
		}
	}
	return '';
}

/**
 * Sticky icon
 *
 * @since  1.0.0
 * @param  boolean $echo Whether to echo or return the icon.
 * @param  string $class Add classes to the icon markup.
 * @param  string $title Text for the title attribute.
 * @return mixed Echoes the icon, or returns the icon or empty.
 */
function sticky_icon( $echo = '', $class = '', $title = '' ) {

	$icon = '';
	if ( page()->sticky() ) {
		$icon = sprintf(
			'<span class="theme-icon sticky-icon %s" title="%s" role="img">%s</span><span class="screen-reader-text">%s </span>',
			$class,
			$title,
			icon( 'sticky' ),
			lang()->get( 'Sticky Post:' )
		);
	}

	// Echo or return the icon
	if ( $echo == 'true' ) {
		echo $icon;
	} else {
		return $icon;
	}
}

/**
 * Page description
 *
 * Gets the page description or
 * an excerpt of the content.
 *
 * @since  1.0.0
 * @return string Returns the description.
 */
function page_description( $key = '' ) {

	if ( empty( $key ) ) {
		$key = $page->key();
	}

	$page = buildPage( $key );

	if ( $page->description() ) {
		$page_desc = $page->description();
	} else {
		$page_desc  = substr( strip_tags( $page->content() ), 0, 85 );
		if ( ! empty( $page->content() ) && ! ctype_space( $page->content() ) ) {
			$page_desc .= '&hellip;';
		}
	}
	return $page_desc;
}

/**
 * Page has tags
 *
 * Whether a page has tags attached.
 *
 * @since  1.0.0
 * @return boolean Returns true if tags are attached.
 */
function has_tags() {

	if ( page()->tags( true ) ) {
		return true;
	}
	return false;
}

/**
 * Static pages list
 *
 * @since  1.0.0
 * @param  array $args Arguments to be passed.
 * @param  array $defaults Default arguments.
 * @return string
 */
function static_list( $args = null, $defaults = [] ) {

	// Default arguments.
	$defaults = [
		'wrap'      => false,
		'direction' => 'vert',
		'title'     => false,
		'heading'   => 'h3',
		'links'     => true
	];

	// Maybe override defaults.
	if ( is_array( $args ) && $args ) {
		$args = array_merge( $defaults, $args );
	} else {
		$args = $defaults;
	}

	// List classes.
	$classes   = [];
	$classes[] = 'static-list';
	if ( 'vert' == $args['direction'] ) {
		$classes[] = 'static-list-vertical';
	} else {
		$classes[] = 'static-list-horizontal';
	}
	$classes = implode( ' ', $classes );

	// List markup.
	$html = '';
	if ( $args['wrap'] ) {
		$html = '<div class="static-list-wrap">';
	}

	if ( $args['title'] ) {
		$html .= sprintf(
			'<%s>%s</%s>',
			$args['heading'],
			$args['title'],
			$args['heading']
		);
	}

	$html .= sprintf(
		'<ul class="%s">',
		$classes
	);

	$static = buildStaticPages();
	foreach ( $static as $page ) {

		// Item class.
		$classes = [ 'static-page' ];
		if ( $page->hasChildren() ) {
			$classes[] = 'parent-page';
		} elseif ( $page->isChild() ) {
			$classes[] = 'child-page';
		}
		$classes = implode( ' ', $classes );

		if (
			$page->key() != site()->homepage() &&
			$page->key() != site()->pageNotFound()
		) {
			$html .= "<li class='{$classes}'>";

			if ( $args['links'] ) {
				$html .= '<a href="' . $page->permalink() . '">';
			}
			$html .= $page->title();
			if ( $args['links'] ) {
				$html .= '</a>';
			}
			$html .= '</li>';
		}
	}
	$html .= '</ul>';

	if ( $args['wrap'] ) {
		$html  .= '</div>';
	}
	return $html;
}

/**
 * Categories list
 *
 * @since  1.0.0
 * @param  array $args Arguments to be passed.
 * @param  array $defaults Default arguments.
 * @global object $categories The Categories class.
 * @return string
 */
function categories_list( $args = null, $defaults = [] ) {

	// Access global variables.
	global $categories;

	// Default arguments.
	$defaults = [
		'wrap'      => false,
		'direction' => 'horz',
		'buttons'   => false,
		'title'     => false,
		'heading'   => 'h3',
		'links'     => true,
		'count'     => false
	];

	// Maybe override defaults.
	if ( is_array( $args ) && $args ) {
		$args = array_merge( $defaults, $args );
	} else {
		$args = $defaults;
	}

	// List classes.
	$classes   = [];
	$classes[] = 'categories-list';
	if ( 'vert' == $args['direction'] ) {
		$classes[] = 'categories-list-vertical';
	} else {
		$classes[] = 'categories-list-horizontal';
	}
	if ( $args['buttons'] ) {
		$classes[] = 'categories-list-buttons';
	}
	$classes = implode( ' ', $classes );

	// List markup.
	$html = '';
	if ( $args['wrap'] ) {
		$html = '<div class="categories-list-wrap">';
	}

	if ( $args['title'] ) {
		$html .= sprintf(
			'<%s>%s</%s>',
			$args['heading'],
			$args['title'],
			$args['heading']
		);
	}
	$html .= sprintf(
		'<ul class="%s">',
		$classes
	);

	// By default the database of categories are alphanumeric sorted.
	foreach ( $categories->db as $key => $fields ) {

		$get_count = count( $fields['list'] );
		$get_name  = $fields['name'];

		$name = $get_name;
		if ( $args['count'] ) {
			$name = sprintf(
				'%s (%s)',
				$get_name,
				$get_count
			);
		}

		if ( $get_count > 0 ) {
			$html .= '<li>';
			if ( $args['links'] ) {
				$html .= '<a href="' . DOMAIN_CATEGORIES . $key . '">';
			}
			$html .= $name;
			if ( $args['links'] ) {
				$html .= '</a>';
			}
			$html .= '</li>';
		}
	}
	$html .= '</ul>';

	if ( $args['wrap'] ) {
		$html  .= '</div>';
	}

	return $html;
}

/**
 * Tags list
 *
 * @since  1.0.0
 * @param  array $args Arguments to be passed.
 * @param  array $defaults Default arguments.
 * @global object $tags The Tags class.
 * @return string
 */
function tags_list( $args = null, $defaults = [] ) {

	// Access global variables.
	global $tags;

	// Default arguments.
	$defaults = [
		'wrap'      => false,
		'direction' => 'horz',
		'buttons'   => false,
		'title'     => false,
		'heading'   => 'h3',
		'links'     => true,
		'count'     => false
	];

	// Maybe override defaults.
	if ( is_array( $args ) && $args ) {
		$args = array_merge( $defaults, $args );
	} else {
		$args = $defaults;
	}

	// List classes.
	$classes   = [];
	$classes[] = 'tags-list';
	if ( 'vert' == $args['direction'] ) {
		$classes[] = 'tags-list-vertical';
	} else {
		$classes[] = 'tags-list-horizontal';
	}
	if ( $args['buttons'] ) {
		$classes[] = 'tags-list-buttons';
	}
	$classes = implode( ' ', $classes );

	// List markup.
	$html = '';
	if ( $args['wrap'] ) {
		$html = '<div class="tags-list-wrap">';
	}

	if ( $args['title'] ) {
		$html .= sprintf(
			'<%s>%s</%s>',
			$args['heading'],
			$args['title'],
			$args['heading']
		);
	}
	$html .= sprintf(
		'<ul class="%s">',
		$classes
	);

	// By default the database of tags are alphanumeric sorted.
	foreach ( $tags->db as $key => $fields ) {

		$get_count = $tags->numberOfPages( $key );
		$get_name  = $fields['name'];

		$name = $get_name;
		if ( $args['count'] ) {
			$name = sprintf(
				'%s (%s)',
				$get_name,
				$get_count
			);
		}
		$html .= '<li>';
		if ( $args['links'] ) {
			$html .= '<a href="' . DOMAIN_TAGS . $key . '">';
		}
		$html .= $name;
		if ( $args['links'] ) {
			$html .= '</a>';
		}
		$html .= '</li>';
	}
	$html .= '</ul>';

	if ( $args['wrap'] ) {
		$html  .= '</div>';
	}
	return $html;
}

/**
 * Get page author
 *
 * @since  1.0.0
 * @return string
 */
function get_author() {

	$user   = page()->username();
	$author = new \User( $user );

	if ( $author->nickname() ) {
		$name = $author->nickname();
	} elseif ( $author->firstName() && $author->lastName() ) {
		$name = sprintf(
			'%s %s',
			$author->firstName(),
			$author->lastName()
		);
	} elseif ( $author->firstName() ) {
		$name = $author->firstName();
	} else {
		$name = ucwords( str_replace( [ '-', '_', '.' ], ' ', $user ) );
	}
	return $name;
}

/**
 * Loop pagination
 *
 * @since  1.0.0
 * @return mixed Returns the navigation markup or false.
 */
function get_loop_pagination() {

	if ( theme() && 'numerical' == theme()->loop_paged() ) {
		ob_start();
		include( THEME_DIR . 'views/navigation/paged-numerical.php' );
		return ob_get_clean();
	} else {
		ob_start();
		include( THEME_DIR . 'views/navigation/paged-prev-next.php' );
		return ob_get_clean();
	}
}

/**
 * Previous key
 *
 * @since  1.0.0
 * @global object $pages Pages class
 * @return mixed
 */
function prev_key() {

	// Access global variables.
	global $pages;

	// Stop if on a static page.
	if ( page()->isStatic() ) {
		return false;
	}

	$current  = page()->key();
	$keys     = $pages->getPublishedDB( true );
	$position = array_search( $current, $keys ) + 1;

	if ( isset( $keys[$position] ) ) {
		return $keys[$position];
	}
	return false;
}

/**
 * Next key
 *
 * @since  1.0.0
 * @global object $pages Pages class
 * @return mixed
 */
function next_key() {

	// Access global variables.
	global $pages;

	// Stop if on a static page.
	if ( page()->isStatic() ) {
		return false;
	}

	$current  = page()->key();
	$keys     = $pages->getPublishedDB( true );
	$position = array_search( $current, $keys ) - 1;

	if ( isset( $keys[$position] ) ) {
		return $keys[$position];
	}
	return false;
}

/**
 * Social navigation
 *
 * Displays a list of links to social sites.
 *
 * @since  1.0.0
 * @param  boolean $wrap Whether to wrap the list in a `<nav>` element.
 * @return string
 */
function social_nav( $wrap = true ) {

	$links = helper() :: socialNetworks();
	if ( $links ) :

	if ( $wrap ) {
		echo '<nav class="social-navigation" data-page-navigation>';
	}
	?>
		<ul class="nav-list social-nav-list">
			<?php foreach ( $links as $link => $label ) :

			// Get icon SVG file.
			$icon = '';
			$file = THEME_DIR . 'assets/images/svg-icons/' . $link . '.svg';
			if ( file_exists( $file ) ) {
				$icon = file_get_contents( $file );
			} ?>
			<li>
				<a href="<?php echo site()->{$link}(); ?>" target="_blank" rel="noreferrer noopener" title="<?php echo $label; ?>">
					<span class="theme-icon social-icon"><?php echo $icon; ?></span>
					<span class="screen-reader-text social-label"><?php echo $label; ?></span>
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
	<?php
	if ( $wrap ) {
		echo '</nav>';
	}
	endif;
}

/**
 * Pint footer scripts
 *
 * @since  1.0.0
 * @return void
 */
function footer_scripts() {

	$suffix = asset_min();

	echo helper() :: js( "assets/js/fitvids{$suffix}.js" );
	echo helper() :: js( "assets/js/lightbox{$suffix}.js" );
	echo helper() :: js( "assets/js/sticky{$suffix}.js" );
	echo helper() :: js( "assets/js/theme{$suffix}.js" );
}
