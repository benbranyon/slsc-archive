<?php
  namespace iberezansky\fb3d;

  function register_styles() {
    wp_register_style(POST_ID.'-font-awesome', ASSETS_CSS.'font-awesome.min.css', array(), '4.7.0');
    wp_register_style(POST_ID.'-colorpicker', ASSETS_CSS.'colorpicker.css', array(), '1.1.0');

    wp_register_style(POST_ID.'-admin', ASSETS_CSS.'admin.css', array(POST_ID.'-font-awesome'), VERSION);
    wp_register_style(POST_ID.'-edit', ASSETS_CSS.'edit.css', array(POST_ID.'-admin', POST_ID.'-colorpicker'), VERSION);
    wp_register_style(POST_ID.'-insert', ASSETS_CSS.'insert.css', array(POST_ID.'-admin'), VERSION);
    wp_register_style(POST_ID.'-settings', ASSETS_CSS.'settings.css', array(POST_ID.'-admin'), VERSION);

    wp_register_style(POST_ID.'-client', ASSETS_CSS.'client.css', array(POST_ID.'-font-awesome'), VERSION);
  }

?>
