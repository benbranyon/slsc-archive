<?php
  namespace iberezansky\fb3d;
  defined('ABSPATH') || die();

  function register_taxonomy() {
    \register_taxonomy(POST_ID.'-category', POST_ID, array(
      'hierarchical'=> true,
      'label'=> __('Categories', POST_ID),
      'show-ui'=> true
    ));
  }

  add_action('init', '\iberezansky\fb3d\register_taxonomy');
?>
