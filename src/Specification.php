<?php

namespace Mathrix\OpenAPI\PreProcessor;

use UnexpectedValueException;

/**
 * Class Specification.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class Specification
{
    public $srcDir;
    public $outputFile;

    private $paths = [];
    private $requestBodies = [];
    private $responses = [];
    private $schemas = [];
    private $tags = [];


    public function __construct($srcDir, $outputFile = null)
    {
        $srcDir = realpath(rtrim($srcDir, "\t\n\r \v/"));

        if ($outputFile === null) {
            $outDir = dirname($srcDir);
            $outputFile = "$outDir/index.yaml";
        }

        $this->srcDir = $srcDir;
        $this->outputFile = $outputFile;
    }


    public function compile()
    {
        $this->loadComponents("requestBodies");
        $this->loadComponents("responses");
        $this->loadComponents("schemas");
        $this->loadPaths();

        $this->merge();
    }


    private function loadComponents(string $type, string $dir = null, string $prefix = null)
    {
        $glob = "$this->srcDir/$type/" . ($dir === null ? "" : "$dir/") . "*.yaml";
        $componentsFiles = glob($glob);

        foreach ($componentsFiles as $componentsFile) {
            $componentName = ($prefix ?? "") . str_replace(".yaml", "", basename($componentsFile));

            $this->{$type}[$componentName] = Loader::make()->load($componentsFile);
        }

        return $this;
    }


    /**
     * Get the path URI of a path file.
     *
     * @param string $pathFile
     *
     * @return string
     */
    private function getUri(string $pathFile)
    {
        $pattern = "/\/paths\/([a-zA-Z0-9\-\_]+)\/\_([a-zA-Z0-9\-\_\{\}\[\]]*)\.yaml/";
        preg_match($pattern, $pathFile, $matches);

        if (count($matches) !== 3) {
            throw new UnexpectedValueException("$pathFile does not follow the pattern $pattern");
        }

        [$base, $parts] = [$matches[1], $matches[2]];

        return rtrim("/$base/" . str_replace("_", "/", $parts), "/");
    }


    /**
     * Process OpenAPI paths.
     *
     * @return Specification
     */
    private function loadPaths()
    {
        $tagPattern = "/([a-zA-Z0-9\-\_]+)\/?.*/";
        $pathFiles = glob("$this->srcDir/paths/*/*.yaml");

        foreach ($pathFiles as $pathFile) {
            $uri = $this->getUri($pathFile);

            preg_match($tagPattern, $uri, $tagMatches);

            $tag = ucfirst($tagMatches[1]);


            if (!in_array($tag, $this->tags)) {
                $this->tags[] = $tag;
            }

            $pathData = Loader::make()->load($pathFile);

            foreach ($pathData as $method => $pathItemData) {
                $pathData[$method]["tags"] = $pathItemData["tags"] ?? [$tag];
            }

            $this->paths[$uri] = $pathData;
        }

        sort($this->tags);

        return $this;
    }


    private function makeTags()
    {
        return array_map(function(string $tag) {
            return [
                "name" => $tag,
                "description" => "The $tag API"
            ];
        }, $this->tags);
    }


    private function merge()
    {
        $indexData = Loader::make()->load("$this->srcDir/index.yaml");

        $indexData = array_merge_recursive($indexData, [
            "paths" => $this->paths,
            "tags" => $this->makeTags(),
            "components" => [
                "requestBodies" => $this->requestBodies,
                "responses" => $this->responses,
                "schemas" => $this->schemas
            ]
        ]);

        foreach ($indexData["components"] as $componentsClass => $components) {
            if (empty($components)) {
                unset($indexData["components"][$componentsClass]);
            }
        }

        Loader::make()->write("$this->outputFile", $indexData);
    }
}
