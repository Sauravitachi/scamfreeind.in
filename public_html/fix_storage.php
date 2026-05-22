<?php
/**
 * Script to fix storage symlink issues in root-as-public setups.
 */

$link = __DIR__ . '/public_storage';
$target = __DIR__ . '/storage/app/public';

echo "<h1>Storage Symlink Fixer</h1>";

if (file_exists($link)) {
    if (is_link($link)) {
        echo "<p>Existing link found. Removing...</p>";
        unlink($link);
    } else {
        echo "<p><b>Error:</b> '$link' exists but is a directory. Please rename or delete it manually.</p>";
        exit;
    }
}

if (symlink($target, $link)) {
    echo "<p style='color: green;'><b>Success!</b> Created symlink '$link' pointing to '$target'.</p>";
    echo "<p>Your files should now be accessible via <code>/public_storage/...</code></p>";
} else {
    echo "<p style='color: red;'><b>Failed</b> to create symlink. Check directory permissions.</p>";
    echo "<p>Try running <code>ln -s $target $link</code> via SSH if possible.</p>";
}
