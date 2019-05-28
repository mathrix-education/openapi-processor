<?php

namespace Mathrix\OpenAPI\Processor\Transformers;

use Mathrix\OpenAPI\Processor\Config;
use Mathrix\OpenAPI\Processor\Log;

/**
 * Class XTagGroups.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class XTagGroupsTransformer extends BaseTransformer
{
    public function __invoke(array $data)
    {
        Log::debug("Applying " . get_class($this));

        $existingTags = array_map(function (array $tagData) {
            return $tagData["name"];
        }, $data["tags"]);

        if (isset($data["x-tagGroups"])) {
            foreach ($data["x-tagGroups"] as $tagGroup) {
                $existingTags = array_diff($existingTags, $tagGroup["tags"]);
            }
        }

        $data["x-tagGroups"][] = [
            "name" => Config::get("defaultTagGroup"),
            "tags" => array_values($existingTags)
        ];

        return $data;
    }
}
