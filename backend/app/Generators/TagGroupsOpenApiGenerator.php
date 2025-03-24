<?php

namespace App\Generators;

use Knuckles\Camel\Output\OutputEndpointData;
use Knuckles\Scribe\Writing\OpenApiSpecGenerators\OpenApiGenerator;

class TagGroupsOpenApiGenerator extends OpenApiGenerator
{
    /**
     * Add tags to root.
     */
    public function root(array $root, array $groupedEndpoints): array
    {
        $groupsAndSubgroups = ['Endpoints' => ['Endpoints']];
        $subgroups = [];

        // Extract groups and subgroups from endpoints
        foreach ($groupedEndpoints as $endpoointGroup) {
            $endpoints = $endpoointGroup['endpoints'];
            foreach ($endpoints as $endpoint) {
                $metadata = $endpoint->metadata;
                $group = $metadata->groupName;
                $subgroup = $metadata->subgroup;
                if ($subgroup) {
                    $subgroups[] = [
                        'name' => $subgroup,
                        'description' => $metadata->subgroupDescription,
                    ];
                    if (array_key_exists($group, $groupsAndSubgroups)) {
                        $groupsAndSubgroups[$group] = array_unique(
                            array_merge($groupsAndSubgroups[$group], [
                                $subgroup,
                            ])
                        );
                    } else {
                        $groupsAndSubgroups[$group] = [$group, $subgroup];
                    }
                } else {
                    if (! array_key_exists($group, $groupsAndSubgroups)) {
                        $groupsAndSubgroups[$group] = [$group];
                    }
                }
            }
        }

        // Add subgroups to groups
        $subgroups = array_map(
            'unserialize',
            array_unique(array_map('serialize', $subgroups))
        );
        $root['tags'] = array_merge($root['tags'], $subgroups);

        // Generate x-tagGroups
        $tagGroups = [];
        foreach ($groupsAndSubgroups as $group => $subgroups) {
            $tagGroups[] = [
                'name' => $group,
                'tags' => $subgroups,
            ];
        }
        $root['x-tagGroups'] = $tagGroups;

        return $root;
    }

    /**
     * Add tags to path item.
     */
    public function pathItem(
        array $pathItem,
        array $groupedEndpoints,
        OutputEndpointData $endpoint
    ): array {
        // For each HTTP method, add the tags to the endpoint
        $tags = [];
        $metadata = $endpoint->metadata;
        $group = $metadata->groupName;
        $subgroup = $metadata->subgroup;
        if ($subgroup) {
            $tags = [$subgroup];
        } else {
            $tags = [$group];
        }

        $pathItem['tags'] = $tags;

        return $pathItem;
    }
}
