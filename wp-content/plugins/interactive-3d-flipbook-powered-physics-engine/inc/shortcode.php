<?php
  namespace iberezansky\fb3d;
  use \WP_Query;

  function convert_tax_to_tax_query($tax) {
    $ids = explode(',', $tax);
    $iids = array();
    foreach($ids as $id) {
      array_push($iids, intval($id));
    }
    return array(array(
      'taxonomy'=> POST_ID.'-category',
  		'field'=> 'term_id',
  		'terms'=> $iids
    ));
  }

  function convert_query_to_array($q) {
    $a = json_decode(str_replace('\'', '"', $q), true);
    return $a===null? []: $a;
  }

  function template_url_to_path($url) {
    $url = str_replace('\\', '/', $url);
    $dir = str_replace('\\', '/', DIR);
    $wp_content = str_replace('\\', '/', WP_CONTENT_DIR);
    $wp_content = substr($wp_content, strrpos($wp_content, '/'));
    $pattern = $wp_content.'/plugins/';
    return substr($dir, 0, strpos($dir, $pattern)).substr($url, strpos($url, $pattern));
  }

  function fetch_url_to_js_data($url) {
    global $fb3d;
    if(!isset($fb3d['jsData']['urls'][$url])) {
      $fb3d['jsData']['urls'][$url] = file_get_contents(template_url_to_path($url));
    }
  }

  function fetch_js_data() {
    global $fb3d;
    $posts = client_posts_in($fb3d['jsData']['posts']['ids_mis'], $fb3d['jsData']['posts']['ids']);
    $fb3d['jsData']['posts']['ids_mis'] = [];
    $fb3d['jsData']['posts']['ids'] = [];

    $pages = client_posts_in_pages($fb3d['jsData']['pages']);
    $fb3d['jsData']['pages'] = [];

    $firstPages = client_posts_in_first_page($fb3d['jsData']['firstPages']);
    $fb3d['jsData']['firstPages'] = [];

    $jsData = [
      'posts'=> [],
      'pages'=> [],
      'firstPages'=> []
    ];

    foreach ($posts as $post) {
      $jsData['posts'][$post['ID']] = $post;
    }

    foreach ($pages as $page) {
      if(!isset($jsData['pages'][$page['page_post_ID']])) {
        $jsData['pages'][$page['page_post_ID']] = [];
      }
      array_push($jsData['pages'][$page['page_post_ID']], $page);
    }

    foreach ($firstPages as $page) {
      $jsData['firstPages'][$page['page_post_ID']] = $page;
    }

    return $jsData;
  }

  function load_js_data($a) {
    global $fb3d;
    $jsData = null;

    if($a['mode']!=='thumbnail') {
      client_book_control_props();
      get_book_templates();
    }

    if($a['id']!=='0') {
      array_push($fb3d['jsData']['posts'][in_array($a['mode'], ['thumbnail', 'thumbnail-lightbox'])? 'ids_mis': 'ids'], $a['id']);

      if($a['mode']!=='thumbnail') {
        array_push($fb3d['jsData']['pages'], $a['id']);
      }
      else {
        array_push($fb3d['jsData']['firstPages'], $a['id']);
      }

      $jsData = fetch_js_data();
    }

    if($a['mode']!=='thumbnail') {
      $template = $a['template'];
      if($template==='default') {
        $template = aa(aa($fb3d['jsData']['bookCtrlProps'], 'skin'), 'default', 'short-white-book-view');
        if($template==='auto') {
          $template = 'short-white-book-view';
        }
        $a['template'] = $template;
      }

      if(!isset($fb3d['templates'][$template])) {
        $template = 'short-white-book-view';
        $a['template'] = $template;
      }

      if($a['lightbox']==='default') {
        $a['lightbox'] = aa(aa($fb3d['jsData']['bookCtrlProps'], 'lightbox'), 'default', 'auto');
        if($a['lightbox']==='auto') {
          $a['lightbox'] = 'dark-shadow';
        }
      }
      $template = $fb3d['templates'][$template];
      fetch_url_to_js_data($template['html']);
      fetch_url_to_js_data($template['script']);
      foreach($template['styles'] as $style) {
        fetch_url_to_js_data($style);
      }
    }

    return ['atts'=> $a, 'jsData'=> $jsData];
  }

  function enqueue_client_scripts() {
    global $fb3d;
    if(isset($fb3d['load_client_scripts']) && !isset($fb3d['enqueued_client_scripts'])) {
      $fb3d['enqueued_client_scripts'] = TRUE;
      register_scripts_and_styles();
      wp_enqueue_style(POST_ID.'-client');
      wp_enqueue_script(POST_ID.'-client');
    }
  }

  function to_single_quotes($s) {
    return str_replace('"', '\'', $s);
  }

  add_action('wp_footer', '\iberezansky\fb3d\enqueue_client_scripts');

  function shortcode_handler($atts, $content='') {
    global $fb3d;
    $fb3d['load_client_scripts'] = TRUE;
    $atts = shortcode_atts([
      'id'=> '0',
      'mode'=> 'fullscreen',
      'title'=> 'false',
      'template'=> 'default',
      'lightbox'=> 'default',
      'classes'=> '',
      'urlparam'=> 'fb3d-page',
      'page-n'=>'0',
      'pdf'=> '',
      'tax'=> 'null',
      'thumbnail'=> '',
      'cols'=> '3',
      'style'=> '',
      'query'=> '',
      'book-template'=> 'default'
    ], $atts);

    if($atts['tax']==='null') {
      $is_link = $atts['mode']==='link-lightbox';
      $atts['template'] = 'short-white-book-view';
      $classes = str_replace(array(' ', "\t"), '', $atts['classes']);
      $classes = explode(',', $classes);
      array_push($classes, 'fb3d-'.$atts['mode'].'-mode');
      if($atts['mode']==='fullscreen') {
        array_push($classes, 'full-size');
      }
      $classes = implode(' ', $classes);

      $r = load_js_data($atts);
      $atts = $r['atts'];
      $jsData = $r['jsData'];

      $r = sprintf('<%s class="%s %s"', $is_link? 'a ': 'div', '_'.POST_ID, to_single_quotes($classes));
      foreach($atts as $k=> $v) {
        if($k!=='classes' && $k!=='style' && $k!=='query') {
          $r .= sprintf(' data-%s="%s"', $k, to_single_quotes($v));
        }
      }
      if($atts['style']!=='') {
        $r .= sprintf(' style="%s"', to_single_quotes($atts['style']));
      }

      $res = ($is_link? $r.'>'.$content.'</a>' :$r.'></div>'.$content).($jsData? implode([
      '<script type="text/javascript">',
        'window.FB3D_CLIENT_DATA = window.FB3D_CLIENT_DATA || [];',
        'window.FB3D_CLIENT_DATA.push(\''.base64_encode(json_encode($jsData)).'\');',
        'window.FB3D_CLIENT_LOCALE && window.FB3D_CLIENT_LOCALE.render();',
      '</script>']): '');
    }
    else {
      $params = ['posts_per_page'=>-1];
  		if($atts['tax']!=='') {
        if(substr($atts['tax'], 0, 1)==='{') {
          $params['tax_query'] = convert_query_to_array($atts['tax']);
        }
  			else {
          $params['tax_query'] = convert_tax_to_tax_query($atts['tax']);
        }
  		}
      $q_params = array_merge($params, convert_query_to_array($atts['query']), ['post_type'=> POST_ID]);
  		$q = new WP_Query($q_params);
  		$params = $atts;
  		$cols = intval($atts['cols']);
  		unset($params['tax']);
      unset($params['style']);
      ob_start();
  		echo('<table class="fb3d-categories" data-query="'.to_single_quotes(json_encode($q_params)).'" data-raw-query="'.to_single_quotes($atts['query']).'" style="'.to_single_quotes($atts['style']).'"><tr>');
  		for($i=0; $i<$q->post_count; ++$i) {
  			if($i%$cols===0 && $i) {
  				echo('</tr><tr>');
  			}
  			$params['id'] = $q->posts[$i]->ID;
  			echo('<td>'.shortcode_handler($params).'</td>');
      }
  		echo('</tr></table>');
      $res = ob_get_clean();
    }

    return $res;
  }

  add_shortcode(POST_ID, '\iberezansky\fb3d\shortcode_handler');
?>
