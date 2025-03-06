<?php
  namespace iberezansky\fb3d;

  function init_local_templates() {
    global $fb3d;
    $fb3d['templates'] = [
      'short-white-book-view'=> [
        'styles'=> [
          ASSETS_CSS.'font-awesome.min.css',
          ASSETS_CSS.'short-white-book-view.css'
        ],
        'links'=> [],
        'html'=> ASSETS_TEMPLATES.'default-book-view.html',
        'script'=> ASSETS_JS.'default-book-view.js',
        'sounds'=> [
          'startFlip'=> ASSETS_SOUNDS.'start-flip.mp3',
          'endFlip'=> ASSETS_SOUNDS.'end-flip.mp3'
        ]
      ]

    ];
  }

  $fb3d['lightboxes'] = [
    'light' => [
      'caption'=> 'Light Glass Box'
    ],
    'dark' => [
      'caption'=> 'Dark Glass Box'
    ],
    'dark-shadow' => [
      'caption'=> 'Dark Glass Shadow'
    ],
    'light-shadow' => [
      'caption'=> 'Light Glass Shadow'
    ]
  ];

  function get_cache_dir() {
    $dir = wp_upload_dir();
    return $dir['basedir'].'/'.POST_ID.'/cache/';
  }

  function get_cache_url() {
    $dir = wp_upload_dir();
    return $dir['baseurl'].'/'.POST_ID.'/cache/';
  }

  function update_templates_cache() {
    global $fb3d;
    $us = [];
    foreach($fb3d['templates'] as $t) {
      $us[$t['html']] = 1;
      $us[$t['script']] = 1;
      foreach($t['styles'] as $s) {
        $us[$s] = 1;
      }
    }
    $urls = [];
    foreach($us as $u=>$v) {
      $urls[substr($u, strpos($u, '/plugins/')+9)] = file_get_contents(template_url_to_path($u));
    }

    $dir = get_cache_dir();
    $path = $dir.'skins.js';
    $old = file_exists($path)? file_get_contents($path): '';
    $new = implode('', [
      'FB3D_CLIENT_LOCALE.templates=', preg_replace('/"http.*?plugins\\\\\//i', '"', json_encode($fb3d['templates'])), ';',
      'FB3D_CLIENT_LOCALE.jsData.urls=', json_encode($urls), ';'
    ]);
    if($old!==$new) {
      if(!file_exists($dir)) {
        mkdir($dir, 0777, TRUE);
      }
      file_put_contents($path, $new);
    }
  }

  add_action('init', 'iberezansky\fb3d\update_templates_cache', 12);

?>
