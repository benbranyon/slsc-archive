<?php
  namespace iberezansky\fb3d;

  function rec_stripslashes($mixed) {
    return  is_array($mixed) ? array_map('\iberezansky\fb3d\rec_stripslashes', $mixed) : stripslashes($mixed);
  }

  function is_request_allowed() {
    $res = false;
    if(wp_verify_nonce($_POST[NONCE], NONCE) && is_user_logged_in()) {
      $user = wp_get_current_user();
      $roles = (array)$user->roles;
      $res = array_search('administrator', $roles)!==false;
    }
    return $res;
  }

  function receive_book_template() {
    if(is_request_allowed()) {
      $template = rec_stripslashes(is_array($_POST['template'])? $_POST['template']: []);
      $name = to_single_quotes(isset($template['name'])? $template['name']: 'unnamed');
      $data = get_post_data(0, array2plane(isset($template['post'])? $template['post']: [], '3dfb-post-'));
      $post = $data['3dfb']['post'];
      $templates = get_book_templates();
      if(isset($template['post'])) {
        $templates[$name] = [
          'props'=> $post['props'],
          'book_style'=> $post['book_style'],
          'controlProps'=> $post['controlProps'],
          'ready_function'=> $post['ready_function']
        ];
      }
      else {
        unset($templates[$name]);
      }
      update_option(META_PREFIX.'book_templates', serialize($templates));
      wp_send_json(array('code'=> CODE_OK));
    }
  }

  add_action('wp_ajax_fb3d_receive_book_template', '\iberezansky\fb3d\receive_book_template');

  function receive_question_answer_json() {
    if(is_request_allowed()) {
      global $fb3d;
      $data = plane2struct(array2plane($_POST), [
        'base'=> [
          'question-id'=> ['default'=> 'unknown', 'qualifier'=> '%s'],
          'question-state'=> ['default'=> 0, 'qualifier'=> '%d'],
        ]
      ]);
      $q = $data['question'];
      $fb3d['options']['questions'][$q['id']] = [
        'state'=> $q['state'],
        'date'=> date(DTM_FORMAT)
      ];
      push_options();
      wp_send_json(['code'=> CODE_OK]);
    }
  }

  add_action('wp_ajax_fb3d_receive_question_answer', '\iberezansky\fb3d\receive_question_answer_json');

?>
