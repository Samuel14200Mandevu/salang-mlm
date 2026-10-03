<?php

$dir = $argv[1] ?? __DIR__.'/../public/images';
$quality = (int) ($argv[2] ?? 80);

if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
    fwrite(STDERR, "GD avec imagewebp requis.\n");
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
);

$count = 0;
foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }
    $path = $file->getPathname();
    if (! preg_match('/\.(png|jpe?g)$/i', $path)) {
        continue;
    }
    $webp = preg_replace('/\.(png|jpe?g)$/i', '.webp', $path);
    if (is_file($webp) && filemtime($webp) >= filemtime($path)) {
        echo "⏭️  ".basename($path)."\n";
        continue;
    }

    $before = filesize($path);
    $image = str_ends_with(strtolower($path), '.png')
        ? @imagecreatefrompng($path)
        : @imagecreatefromjpeg($path);

    if ($image === false) {
        echo "❌ ".basename($path)."\n";
        continue;
    }

    imagepalettetotruecolor($image);
    imagealphablending($image, true);
    imagesavealpha($image, true);

    if (imagewebp($image, $webp, $quality)) {
        $after = filesize($webp);
        echo '✅ '.basename($path).': '.intdiv($before, 1024).' KB → '.intdiv($after, 1024)." KB\n";
        $count++;
    }
    imagedestroy($image);
}

echo "\n🎉 {$count} fichier(s) converti(s).\n";
