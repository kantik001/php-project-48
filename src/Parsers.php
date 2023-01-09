<?php

namespace Differ\Parsers;

use Exception;
use Symfony\Component\Yaml\Yaml;

function parse(string $type, string $content): array
{
    switch ($type) {
        case "yml":
        case "yaml":
            return Yaml::parse($content);
        case "json":
            return json_decode($content, true);
        default:
            throw new Exception('Unknown type of file ' . $type);
    }
}
