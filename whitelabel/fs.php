<?php

require_once __DIR__ . '/term.php';

function sanitizeDirInput(string $dir): string
{
    return ltrim(str_replace('../', '', $dir), '/');
}

function deleteDir(string $dir): void
{
    $dir = sanitizeDirInput($dir);

    $dirPath = realpath($dir);

    if(!$dirPath) {
        return;
    }

    $wlScript = dirname(get_included_files()[0]);

    if($dirPath === $wlScript) {
        // do not allow to delete this script's parent directory
        error(sprintf('Deleting directory %s is not allowed', $dir));
        exit;
    }

    if(strpos($dirPath, $wlScript) !== 0) {
        // do not allow to delete any path that is not within this script's
        // parent directory
        error(sprintf('Deleting directory %s is not allowed', $dir));
        exit;
    }

    warn(sprintf('Deleting %s', $dirPath));

    $it = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file) {
        if($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }

    rmdir($dirPath);
}

function rrmdir(string $dir): void
{
    if(is_dir($dir)) {
        foreach(scandir($dir) as $file) {
            if($file !== '.' && $file !== '..') {
                rrmdir(sprintf('%s/%s', $dir, $file));
            }
        }

        rmdir($dir);
    } elseif(file_exists($dir)) {
        unlink($dir);
    }
}
