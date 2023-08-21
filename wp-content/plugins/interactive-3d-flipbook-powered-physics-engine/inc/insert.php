<?php
  namespace iberezansky\fb3d;

  function register_tinymce_plugin( $plugin_array ) {
    global $fb3d;
     $plugin_array['3dfbInsert'] = ASSETS_JS.'tiny-mce-insert.js';
     $fb3d['insert-scripts'] = true;
     return $plugin_array;
  }

  function register_tinymce_button( $buttons ) {
    array_push( $buttons, 'dropcap', '3dfbInsert' ); // dropcap', 'recentposts
    return $buttons;
  }

  function add_tinymce_plugins() {
    add_filter('mce_external_plugins', '\iberezansky\fb3d\register_tinymce_plugin');
    add_filter('mce_buttons', '\iberezansky\fb3d\register_tinymce_button');
  }

  add_action('init', '\iberezansky\fb3d\add_tinymce_plugins');

  function enqueue_insert_scripts() {
    global $pagenow, $typenow, $fb3d;
    if(isset($fb3d['insert-scripts']) && ($pagenow == 'post.php' || $pagenow == 'post-new.php' || $pagenow == 'admin.php') && $typenow != POST_ID) {
      register_scripts_and_styles();

      wp_enqueue_style(POST_ID.'-insert');
      wp_enqueue_script(POST_ID.'-insert');

      before_wp_tiny_mce();
    }
  }

  add_action('admin_footer', '\iberezansky\fb3d\enqueue_insert_scripts'); // admin_enqueue_scripts

  function before_wp_tiny_mce() {
    ?>
    <script type="text/javascript">
      window.FB3D_MCE_LOCALE = {
        key: '<?php echo(esc_js(POST_ID))?>',
        icon: '<?php echo(esc_js(ASSETS_IMAGES.'icon.ico'))?>'
      };
    </script>
    <?php
  }

  // add_action('before_wp_tiny_mce', '\iberezansky\fb3d\before_wp_tiny_mce');


?>
