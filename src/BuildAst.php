<?php

namespace Differ\BuildAst;

use function Functional\map;
use function Functional\sort;

function buildAst(array $firstContentFromFile, array $secondContentFromFile): array
{
    $keys = array_merge(array_keys($firstContentFromFile), array_keys($secondContentFromFile));
    $uniqueKeys = array_unique($keys);
    $sortedKeys = sort($uniqueKeys, fn ($a, $b) => strcmp($a, $b), false);

    return array_map(fn($key) => getAst($key, $firstContentFromFile, $secondContentFromFile), $sortedKeys);
}

function getAstNode(string $type, string $key, $value, $secondValue = null): array
{
    return ['type' => $type,
        'key' => $key,
        'value' => $value,
        'secondValue' => $secondValue];
}

function getAst(string $key, array $firstContentFromFile, array $secondContentFromFile): array
{
    $firstContent = $firstContentFromFile[$key] ?? null;
    $secondContent = $secondContentFromFile[$key] ?? null;
    if (is_array($firstContent) && is_array($secondContent)) {
        return getAstNode('hasChildren', $key, buildAst($firstContent, $secondContent));
    }

    if (!array_key_exists($key, $firstContentFromFile)) {
        return getAstNode('added', $key, normalizeContent($secondContent));
    }

    if (!array_key_exists($key, $secondContentFromFile)) {
        return  getAstNode('deleted', $key, normalizeContent($firstContent));
    }

    if ($firstContent !== $secondContent) {
        return getAstNode('changed', $key, normalizeContent($firstContent), normalizeContent($secondContent));
    }

    return getAstNode('unchanged', $key, $firstContent);
}

function normalizeContent(mixed $content)
{
    $iter = function ($content) use (&$iter) {
        if (!is_array($content)) {
            return $content;
        }

	$keys = array_keys($content);
	return map($keys, function ($key) use ($content, $iter) {
	    $value = (is_array($content[$key])) ? $iter($content[$key]) : $content[$key];

	    return ['type' => 'unchanged', 'key' => $key, 'value' => $value];
	});
    };

    return $iter($content);
}

function getType(array $node): string
{
    return $node['type'];
}

function getKey(array $node): string
{
    return $node['key'];
}

function getValue(array $node)
{
    return $node['value'];
}

function getSecondValue(array $node)
{
    return $node['secondValue'];
}

function getChildren(array $node): array
{
    return $node['value'];
}


function hasChildren(array $node): bool
{
    return array_key_exists('children', $node);
}
