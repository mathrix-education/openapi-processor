<?php

namespace Mathrix\OpenAPI\PreProcessor;

use UnexpectedValueException;

/**
 * Class Wrapper.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class Wrapper
{
    /** @var string The OpenAPI sources directory. */
    private $srcDir;
    /** @var string The OpenAPI Processor output file. */
    private $outputFile;

    /** @var array The OpenAPI PathItem objects. */
    private $paths = [];
    /** @var array The OpenAPI RequestBody objects. */
    private $requestBodies = [];
    /** @var array The OpenAPI Response objects. */
    private $responses = [];
    /** @var array The OpenAPI Schema objects. */
    private $schemas = [];
    /** @var array The OpenAPI Tags objects. */
    private $tags = [];


    public function __construct($srcDir, $outputFile = null)
    {
        $srcDir = realpath(rtrim($srcDir, "\t\n\r \v/"));

        if ($srcDir === false) {
            throw new UnexpectedValueException("Sources directory $srcDir does not exist");
        }

        if ($outputFile === null) {
            $outputFile = "$srcDir/index.yaml";
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

    /**
     * Load OpenAPI components (responses, requestBodies, schemas)
     *
     * @param string $type The components type.
     * @param string|null $dir The directory, if different from the components type.
     * @param string|null $prefix The prefix to apply, if any.
     *
     * @return $this
     */
    private function loadComponents(string $type, string $dir = null, string $prefix = null)
    {
        $glob = "$this->srcDir/$type/" . ($dir === null ? "" : "$dir/") . "*.yaml";
        $componentsFiles = glob($glob);

        foreach ($componentsFiles as $componentsFile) {
            $componentName = ($prefix ?? "") . str_replace(".yaml", "", basename($componentsFile));

            $this->{$type}[$componentName] = FileLoader::make()->load($componentsFile);
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
     * @return Wrapper
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

            $pathData = FileLoader::make()->load($pathFile);

            foreach ($pathData as $method => $pathItemData) {
                $pathData[$method]["tags"] = $pathItemData["tags"] ?? [$tag];
            }

            $this->paths[$uri] = $pathData;
        }

        sort($this->tags);

        return $this;
    }

    /**
     * Generate the OpenAPI tags objects.
     *
     * @return array
     */
    private function makeTags()
    {
        return array_map(function (string $tag) {
            return [
                "name" => $tag,
                "description" => "The $tag API"
            ];
        }, $this->tags);
    }


    /**
     * Merge paths and components into the final file.
     */
    private function merge()
    {
        $indexData = FileLoader::make()->load("$this->srcDir/index.yaml");

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

        FileLoader::make()->write($this->outputFile, $indexData);
    }
}
