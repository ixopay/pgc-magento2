<?php

require_once __DIR__ . '/build.php';
require_once __DIR__ . '/fs.php';
require_once __DIR__ . '/getopt.php';
require_once __DIR__ . '/term.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/validation.php';

$requiredExt = [
    'filter',
    'zip',
];
foreach($requiredExt as $ext) {
    if(! extension_loaded($ext)) {
        error(sprintf('PHP extension %s is required', $ext));
        exit;
    }
}
