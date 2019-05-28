<?php

namespace Mathrix\OpenAPI\Processor\Transformers;

use Mathrix\OpenAPI\Processor\Log;

/**
 * Class TagsTransformers.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class TagsTransformer extends BaseTransformer
{
    public function __invoke(array $data)
    {
        Log::debug("Applying " . get_class($this));

        $bases = [];

        foreach ($data["paths"] as $uri => $pathItemData) {
            $result = preg_match("/^\/([a-z\-\_]+)\/?.*$/", $uri, $matches);

            if ($result !== false) {
                $bases[] = $matches[1];
            }
        }

        $bases = array_unique($bases);
        sort($bases);

        $tags = array_map(function ($base) {
            $tag = ucfirst($base);

            return [
                "name" => $tag,
                "description" => "The $tag API, which is handled by the `/$base` root API."
            ];
        }, $bases);

        $data["tags"] = $tags;

        return $data;
    }
}
