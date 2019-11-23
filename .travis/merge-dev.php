<?php
/*
 * Merge autoload-dev and require-dev from one composer.json into another one
 * 
 * This is used to install the module within a Magento 2 installation, but with its dev dependencies
 */
declare(strict_types=1);

$fromFile = $argv[1];
$toFile = $argv[2];

$fromJson = json_decode(file_get_contents($fromFile), true);
$toJson = json_decode(file_get_contents($toFile), true);

foreach ($fromJson['autoload-dev']['psr-4'] ?? [] as $key => $value) {
    $pathPrefix = dirname($fromFile) . DIRECTORY_SEPARATOR;
    $fromJson['autoload-dev']['psr-4'][$key] = $pathPrefix . $value;
}

$toJson['require-dev'] = array_replace_recursive($toJson['require-dev'] ?? [], $fromJson['require-dev']);
$toJson['autoload-dev'] = array_merge_recursive($toJson['autoload-dev'] ?? [], $fromJson['autoload-dev']);

file_put_contents($toFile, json_encode($toJson, JSON_PRETTY_PRINT));