<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Transformers;

use Mathrix\OpenAPI\Processor\Config;
use Mathrix\OpenAPI\Processor\Log;
use function array_diff;
use function array_map;
use function array_values;

class XTagGroupsTransformer extends BaseTransformer
{
    public function __invoke(array $data)
    {
        Log::debug('Applying ' . static::class);

        if (!isset($data['x-tagGroups'])) {
            return $data;
        }

        $existingTags = array_map(static function (array $tagData) {
            return $tagData['name'];
        }, $data['tags']);

        foreach ($data['x-tagGroups'] as $tagGroup) {
            $existingTags = array_diff($existingTags, $tagGroup['tags']);
        }

        $defaultGroupTags = array_values($existingTags);

        if (!empty($defaultGroupTags)) {
            $data['x-tagGroups'][] = [
                'name' => Config::get('defaultTagGroup'),
                'tags' => array_values($defaultGroupTags),
            ];
        }

        return $data;
    }
}
