<?php

require_once __DIR__ . '/fs.php';
require_once __DIR__ . '/term.php';

function build($src, $dst, $replacementMap = [])
{
    debug(sprintf('Scan %s', $src));

    $dir = opendir($src);
    mkdir($dst, 0755, true);

    while(false !== ($file = readdir($dir))) {
        if(($file !== '.') && ($file !== '..')) {
            $srcFile = sprintf('%s/%s', $src, $file);
            $destFile = sprintf('%s/%s', $dst, applyReplacements($file, $replacementMap));

            if(is_dir($srcFile)) {
                build($srcFile, $destFile, $replacementMap);
            } else {
                debug(sprintf('Copying file from %s to %s', $srcFile, $destFile));
                copy($srcFile, $destFile);
                replaceContents($destFile, $replacementMap);
            }
        }
    }

    closedir($dir);
}

function replaceContents(string $file, array $replacementMap): void
{
    file_put_contents($file, applyReplacements(file_get_contents($file), $replacementMap));
}

function applyReplacements(string $string, array $replacementMap): string
{
    foreach ($replacementMap as $old => $new) {
        $string = str_replace($old, $new, $string);
    }
    return $string;
}

function zipBuildToDist($srcDir, $destDir, $filename)
{
    $srcDir = sanitizeDirInput($srcDir);
    $destDir = sanitizeDirInput($destDir);

    $srcDirPath = realpath($srcDir);
    mkdir($destDir, 0755, true);
    $destDirPath = realpath($destDir);
    $zipFilename = sprintf('%s.zip', $filename);
    $destZip = sprintf('%s/%s', $destDirPath, $zipFilename);

    debug(sprintf('Crating %s', $destZip));
    $zip = new ZipArchive();
    $res = $zip->open($destZip, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if($res !== true) {
        error(sprintf('Failed creating %s'));
        exit;
    }

    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDirPath),
        RecursiveIteratorIterator::LEAVES_ONLY,
    );

    foreach($files as $file) {
        if(! $file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($srcDirPath) + 1);

            debug(sprintf('Adding %s as %s', $filePath, $relativePath));
            $res = $zip->addFile($filePath, $relativePath);
            if(empty($res)) {
                error(sprintf('Failed adding %s to %s', $filePath, $destZip));
                exit;
            }
        }
    }

    $zip->close();

    success(sprintf('Created file "%s"', $destZip));
}
