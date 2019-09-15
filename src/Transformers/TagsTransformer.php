<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Transformers;

use Mathrix\OpenAPI\Processor\Log;
use function array_map;
use function array_unique;
use function preg_match;
use function sort;
use function ucfirst;

class TagsTransformer extends BaseTransformer
{
    public function __invoke(array $data)
    {
        Log::debug('Applying ' . static::class);

        $bases = [];

        foreach ($data['paths'] as $uri => $pathItemData) {
            $result = preg_match('/^\/([a-z\-\_]+)\/?.*$/', $uri, $matches);

            if ($result === false) {
                continue;
            }

            $bases[] = $matches[1];
        }

        $bases = array_unique($bases);
        sort($bases);

        $tags = array_map(static function ($base) {
            $tag = ucfirst($base);

            return [
                'name' => $tag,
                'description' => "The $tag API, which is handled by the `/$base` root API.",
            ];
        }, $bases);

        $data['tags'] = $tags;

        return $data;
    }
}
