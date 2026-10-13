<?php

$dir = __DIR__ . '/app/Models';
$files = scandir($dir);
foreach ($files as $file) {
    if (str_ends_with($file, '.php')) {
        $path = $dir . '/' . $file;
        $content = file_get_contents($path);
        if (!str_contains($content, '$guarded')) {
            $content = preg_replace('/class (\w+) extends Model\s*\{/', "class $1 extends Model\n{\n    protected \$guarded = ['id'];\n", $content);
            file_put_contents($path, $content);
        }
    }
}
