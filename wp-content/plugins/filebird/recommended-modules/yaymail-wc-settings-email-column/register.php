<?php

defined( 'ABSPATH' ) || exit;

// Bump version on every init.php behavior change. Newest wins across consumers.
\YayRecommendedModules\Registry::register(
    'yaymail-wc-settings-email-column',
    '1.4.0',
    __DIR__ . '/init.php'
);
