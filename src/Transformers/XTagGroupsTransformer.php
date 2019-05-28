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

        if (!isset($data["x-tagGroups"])) {
            return $data;
        }

        $existingTags = array_map(function (array $tagData) {
            return $tagData["name"];
        }, $data["tags"]);

        foreach ($data["x-tagGroups"] as $tagGroup) {
            $existingTags = array_diff($existingTags, $tagGroup["tags"]);
        }

        $defaultGroupTags = array_values($existingTags);

        if (!empty($defaultGroupTags)) {
            $data["x-tagGroups"][] = [
                "name" => Config::get("defaultTagGroup"),
                "tags" => array_values($defaultGroupTags)
            ];
        }

        return $data;
    }
}
