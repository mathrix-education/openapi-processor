<?php

namespace Mathrix\OpenAPI\Processor;

use Mathrix\OpenAPI\Processor\Transformers\TagsTransformer;
use Mathrix\OpenAPI\Processor\Transformers\XTagGroupsTransformer;
use UnexpectedValueException;

/**
 * Class Wrapper.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class Wrapper extends Factory
{
    /** @var string The OpenAPI sources directory. */
    private $srcDir;
    /** @var string The OpenAPI Processor input file. */
    private $inputFile;
    /** @var string The OpenAPI Processor output file. */
    private $outputFile;
    /** @var string The OpenAPI Processor configuration file. */
    private $configurationFile;

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


    /**
     * Set the input file. Sources directory is deducted from the input file.
     *
     * @param string $inputFile The input file.
     *
     * @return $this
     */
    public function setInputFile(string $inputFile)
    {
        $this->inputFile = $inputFile;
        $this->srcDir = dirname($inputFile);
        $this->configurationFile = "$this->srcDir/configuration.yaml"; // Default configuration file
        $this->outputFile = "$this->srcDir/output.yaml"; // Default output file

        return $this;
    }


    /**
     * Set the output file.
     *
     * @param string $outputFile The output file.
     *
     * @return $this
     */
    public function setOutputFile(?string $outputFile)
    {
        if ($outputFile !== null) {
            $this->outputFile = $outputFile;
        }

        return $this;
    }


    /**
     * Set the configuration file.
     *
     * @param string $configurationFile The configuration file.
     *
     * @return $this
     */
    public function setConfigurationFile(?string $configurationFile)
    {
        if ($configurationFile !== null) {
            $this->configurationFile = $configurationFile;
        }

        return $this;
    }


    /**
     * Compile the specification.
     */
    public function compile()
    {
        Config::load("$this->srcDir/config.yaml");

        $types = ["requestBodies", "responses", "schemas"];

        foreach ($types as $type) {
            $this->loadComponents($type);

            foreach (Config::get($type) as $dir => $prefix) {
                $this->loadComponents($type, $dir, $prefix);
            }
        }

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
        Log::debug("Merging specification");

        $indexData = array_replace_recursive(
            FileLoader::make()->load("$this->srcDir/index.yaml"),
            [
                "info" => [
                    "version" => Config::version() ?? $indexData["version"] ?? "NO VERSION",
                ],
                "paths" => $this->paths,
                "tags" => $this->makeTags(),
                "components" => [
                    "requestBodies" => $this->requestBodies,
                    "responses" => $this->responses,
                    "schemas" => $this->schemas
                ]
            ]
        );

        foreach ($indexData["components"] as $componentsClass => $components) {
            if (empty($components)) {
                unset($indexData["components"][$componentsClass]);
            }
        }

        // Transforms
        $transformers = [
            new TagsTransformer(),
            new XTagGroupsTransformer()
        ];

        foreach ($transformers as $transformer) {
            $indexData = $transformer($indexData);
        }

        FileLoader::make()->write($this->outputFile, $indexData);
    }
}
