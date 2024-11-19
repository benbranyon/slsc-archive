<?php
  namespace iberezansky\fb3d;

  function register_post_type() {
    $props = client_book_control_props();
    $slug = sanitize_title(aa(aa($props, 'plugin'), 'slug', POST_ID));
    $slug = $slug==='auto'? POST_ID: $slug;
    \register_post_type(POST_ID, array(
      'public'=> true,
      'label'=> __('3D FlipBook', POST_ID),
      'labels'=> array(
        'name'               => __( '3D FlipBook', POST_ID),
        'singular_name'      => __( '3D FlipBook', POST_ID),
        'menu_name'          => __( '3D FlipBook', POST_ID),
        'name_admin_bar'     => __( '3D FlipBook', POST_ID),
        'add_new'            => __( 'Add New Book', POST_ID),
        'add_new_item'       => __( 'Add New Book', POST_ID),
        'new_item'           => __( 'New 3D FlipBook', POST_ID),
        'edit_item'          => __( 'Edit 3D FlipBook', POST_ID),
        'view_item'          => __( 'View 3D FlipBook', POST_ID),
        'all_items'          => __( 'All Books', POST_ID),
        'search_items'       => __( 'Search 3D FlipBooks', POST_ID),
        'parent_item_colon'  => __( 'Parent 3D FlipBooks:', POST_ID),
        'not_found'          => __( 'No 3D FlipBooks found.', POST_ID),
        'not_found_in_trash' => __( 'No 3D FlipBooks found in Trash.', POST_ID)
      ),
      'menu_icon'=> 'dashicons-book-alt',
      'exclude_from_search'=> true,
      'publicly_queryable'=> $slug!=='none',
      'capability_type'=> 'post',
      'has_archive'=> true,
      'hierarchical'=> false,
      'query_var'=> true,
      'supports'=> array(
        'title'
      ),
      'rewrite'=> [
        'slug'=> $slug,
      ]
    ));
    if(!aa($props, 'flushed', true)) {
      $props['flushed'] = true;
      update_option(META_PREFIX.'book_control_props', serialize($props));
      flush_rewrite_rules(false);
    }
  }

  add_action('init', '\iberezansky\fb3d\register_post_type');

  function custom_template($single) {
    global $wp_query, $post;
    if($post->post_type===POST_ID) {
      $template = TEMPLATES.'/single-3d-flip-book.php';
      if(file_exists($template)) {
        $single = $template;
      }
    }
    return $single;
  }

  add_filter('single_template', '\iberezansky\fb3d\custom_template');
?>
