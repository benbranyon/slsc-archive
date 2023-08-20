<?php

/*------------------------------------------------------------------*
 * Helper Functions
/*------------------------------------------------------------------*/

require_once( get_template_directory() . '/inc/helpers.php' );

/*------------------------------------------------------------------*
 * Core Theme Features
/*------------------------------------------------------------------*/

// Setting main content width - update to match the width of your site's main content area.
if ( ! isset( $content_width ) ) {
  $content_width = 840;
}

if ( function_exists('add_theme_support') ) {
  // Adding theme support for HTML5
  add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', ) );

  // Adds site name to title tag
  add_theme_support( 'title-tag' );

  // Add support for automatic links for feeds.
  add_theme_support( 'automatic-feed-links' );
  add_theme_support( 'post-thumbnails' );

  # Define automatic thumbnail sizes
  add_image_size( 'large', 700, '', true ); // Large Thumbnail
  add_image_size( 'medium', 250, '', true ); // Medium Thumbnail
  add_image_size( 'small', 120, '', true ); // Small Thumbnail
  //add_image_size( 'custom-size', 700, 200, true ); // Custom Thumbnail Size call using the_post_thumbnail('custom-size');
}

function has_visible_widgets( $sidebar_id ) {
  if ( is_active_sidebar( $sidebar_id ) ) {
    ob_start();
    dynamic_sidebar( $sidebar_id );
    $sidebar = ob_get_contents();
    ob_end_clean();
    if ( $sidebar == "" ) {
      return false;
    }
  } else {
    return false;
  }
  return true;
}

function gesso_scripts() {

  wp_register_script('swiper-js', 'https://unpkg.com/swiper@6.0.0/swiper-bundle.min.js');
  wp_enqueue_script('swiper-js');

  wp_enqueue_style('swiper-styles', 'https://unpkg.com/swiper@6.0.0/swiper-bundle.min.css');

  wp_register_script('gessomodernizr', get_template_directory_uri() . '/js/lib/modernizr.min.js', array('jquery') ); // Modernizr
  wp_enqueue_script('gessomodernizr');

  wp_register_script('sticky-js', get_template_directory_uri() . '/js/lib/sticky.js', array('jquery') );
  wp_enqueue_script('sticky-js');

  if ( is_singular( 'story' ) ) {
      wp_enqueue_style('timeline-styles', get_template_directory_uri() . '/css/lib/timeline.min.css');

      wp_register_script('timeline-js', get_template_directory_uri() . '/js/lib/timeline.min.js', array('jquery') );
      wp_enqueue_script('timeline-js');
  }

  if ( is_singular('archive')) {
      wp_register_script('html-canvas-js', get_template_directory_uri() . '/js/lib/html2canvas.min.js', array('jquery') );
      wp_enqueue_script('html-canvas-js');

      wp_register_script('three-js', get_template_directory_uri() . '/js/lib/three.min.js', array('jquery') );
      wp_enqueue_script('three-js');

      wp_register_script('pdf-js', get_template_directory_uri() . '/js/lib/pdf.min.js', array('jquery') );
      wp_enqueue_script('pdf-js');

      wp_register_script('flipbook-js', get_template_directory_uri() . '/js/lib/3dflipbook.min.js', array('jquery') );
      wp_enqueue_script('flipbook-js');
  }

  if ( is_singular() && comments_open() ) {
    wp_enqueue_script( "comment-reply" );
  }

  wp_register_script('gessomobilemenu', get_template_directory_uri() . '/js/mobile-menu.js', array('jquery','gessomodernizr') ); // Mobile menu
  wp_enqueue_script('gessomobilemenu');

  wp_register_script('gessoscripts', get_template_directory_uri() . '/js/scripts.js', array('jquery','gessomodernizr') ); // Custom scripts
  wp_enqueue_script('gessoscripts');

  wp_enqueue_style( 'style', get_stylesheet_directory_uri() . '/css/styles.css', array(), null, 'all' );

}
add_action( 'wp_enqueue_scripts', 'gesso_scripts' );

function register_gesso_menu() {
  register_nav_menus( array(
    'primary' => __('Primary', 'gesso'),
    'secondary' => __('Secondary', 'gesso'),
  ));
}
add_action( 'init', 'register_gesso_menu' );


// Add page slug to body class. Credit: Starkers Wordpress Theme
function add_slug_to_body_class( $classes ) {
  global $post;
  if (is_home()) {
    $key = array_search( 'blog', $classes );
    if ( $key > -1 ) {
      unset( $classes[ $key ] );
    }
  } elseif ( is_page() ) {
    $classes[] = sanitize_html_class( $post->post_name );
  } elseif ( is_singular() ) {
    $classes[] = sanitize_html_class( $post->post_name );
  }

  return $classes;
}
add_filter( 'body_class', 'add_slug_to_body_class' );

// Initial Sidebar and Footer Widget Areas
add_action( 'widgets_init', 'gesso_widgets_init' );
function gesso_widgets_init() {
  register_sidebar(array(
    'name' => __('Widget Area 1', 'gesso'),
    'description' => __('Widget Area 1', 'gesso'),
    'id' => 'widget-area-1',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="widget__title">',
    'after_title' => '</h3>'
  ));

  register_sidebar(array(
    'name' => __('Footer Widgets', 'gesso'),
    'description' => __('Footer Widgets', 'gesso'),
    'id' => 'footer-widgets',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="widget__title">',
    'after_title' => '</h3>'
  ));
}

function gesso_pagination() {
  global $wp_query;
  $big = 999999999;
  echo paginate_links( array(
    'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
    'format' => '?paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' => $wp_query->max_num_pages,
  ) );
}
add_action('init', 'gesso_pagination');

//Adds proper markup to pages content
function gesso_link_pages() {
  $gesso_links = array(
    'before'    => '<nav role="navigation" aria-labelledby="pagination-heading"><h2 id="pagination-heading" class="visually-hidden">Pagination</h2><ul class="pager">',
    'after'     => '</ul></nav>',
    'link_before' => '<li class="pager__item>',
    'link_after'  => '</li>',
    );
  wp_link_pages( $gesso_links );
}


 // Remove thumbnail dimensions
function remove_thumbnail_dimensions( $html ) {
  $html = preg_replace( '/(width|height)=\"\d*\"\s/', '', $html );
  return $html;
}
add_filter( 'post_thumbnail_html', 'remove_thumbnail_dimensions', 10 ); // Remove width and height attributes from thumbnails
add_filter( 'image_send_to_editor', 'remove_thumbnail_dimensions', 10 ); // Remove width and height attributes from post images

// Allowing styles for post editor to match how it will actually be visually represented
function gesso_add_editor_styles() {
  add_editor_style( 'css/custom-editor-styles.css' );
}
add_action( 'admin_init', 'gesso_add_editor_styles' );

//Remove core block styles
add_filter( 'should_load_separate_core_block_assets', '__return_true' );


//------------------------------------------------------
// Timber Support - Starter Theme Functions
//------------------------------------------------------
if ( ! class_exists( 'Timber' ) ) {
  add_action( 'admin_notices', function() {
    echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
  } );
  return;
}

Timber::$dirname = array('templates');

class StarterSite extends TimberSite {

  function __construct() {
    // add_theme_support( 'post-formats' ); // uncomment to enable post formats
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'menus' );
    add_filter( 'timber/context', array( $this, 'add_to_context' ) );
    add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
    parent::__construct();
  }

  function add_to_context( $context ) {
    $context['foo'] = 'bar';
    $context['stuff'] = 'I am a value set in your functions.php file';
    $context['notes'] = 'These values are available everytime you call Timber::get_context();';
    $context['primary_menu'] = new Timber\Menu('primary');
    $context['secondary_menu'] = new Timber\Menu('secondary');
    $context['menu'] = new Timber\Menu();
    $context['current_year'] = date('Y');
    $context['site'] = $this;
    return $context;
  }

  function add_to_twig( \Twig_Environment $twig ) {
    // This is where you can add your own fuctions to twig
    // https://timber.github.io/docs/guides/extending-timber/#adding-to-twig
    $twig->addExtension( new \Twig_Extension_StringLoader() );		  
    $twig->addFilter( new \Twig_SimpleFilter( 'myfoo', 'my_foo' ) );
    return $twig;
  }

}

new StarterSite();


/**
 * Override default WordPress gallery markup, outputs in BEM format.
 * @param string $gallery
 * @param array $attr
 * @return string
 */
function gesso_bem_gallery( $gallery, $attr ) {
  // [ thumbnail | medium | large | full ]
  $size   = 'thumbnail';
  $output = '<div class="gallery">';
  $posts  = get_posts( array( 'include' => $attr['ids'], 'post_type' => 'attachment' ) );

  foreach ( $posts as $image_post ) {
    $src      = wp_get_attachment_image_src( $image_post->ID, $size );
    $alt_text = get_post_meta( $image_post->ID, '_wp_attachment_image_alt', true );
    $output .= '<div class="gallery__item">';
    $output .= '<a href="' . $src[0] . '"><img alt="' . $alt_text . '" class="gallery__item-image" src="' . $src[0] . '"></a>';
    $output .= '<div class="gallery__item-title">' . $image_post->post_title . '</div>';
    $output .= '<div class="gallery__item-caption">' . $image_post->post_excerpt . '</div>';
    $output .= '<div class="gallery__item-description">' . $image_post->post_content . '</div>';
    $output .= '</div>';
  }

  $output .= '</div>';

  return $output;
}
add_filter( 'post_gallery', 'gesso_bem_gallery', 10, 2 );

/**
 * Replace the default excerpt with our new custom excerpt
 * @param string $excerpt The original announcement excerpt.
 * @param integer $post_id The announcement post ID
 * @return string Return the new excerpt to use.
 */
function replace_default_timeline_express_excerpt( $excerpt, $post_id ) {
    if ( get_post($post_id) ) {
      $content_post = get_post($post_id);
      $content = $content_post->post_content;
      $content = apply_filters('the_content', $content);
      return $content;
    } else {
       return $excerpt;
    }
}
add_filter( 'timeline_express_frontend_excerpt', 'replace_default_timeline_express_excerpt', 10, 2 );

/**
 * Register Twig namespaces to Pattern Lab patterns. 
 * @param Twig_Loader_Filesystem $loader
 * @return Twig_Loader_Filesystem
 */
add_filter('timber/loader/loader', function($loader){
	$loader->addPath(__DIR__ . "/pattern-lab/source/_patterns/01-base", "base");
	$loader->addPath(__DIR__ . "/pattern-lab/source/_patterns/02-layouts", "layouts");
	$loader->addPath(__DIR__ . "/pattern-lab/source/_patterns/03-components", "components");
	$loader->addPath(__DIR__ . "/pattern-lab/source/_patterns/04-templates", "templates");
	$loader->addPath(__DIR__ . "/pattern-lab/source/_patterns/05-pages", "pages");
	return $loader;
});
