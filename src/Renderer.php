<?php

namespace Mathrix\OpenAPI\PreProcessor;

use Symfony\Component\Inflector\Inflector;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Renderer.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class Renderer
{
    public const TEMPLATE_REGEX = "/\{\{\s?[a-zA-Z0-9\_\| \:\,]+\s?\}\}/";

    private $cwd;
    private $file;
    private $context;
    private $input;
    private $output;


    public static function make()
    {
        return new self();
    }


    public function setCwd(string $cwd)
    {
        $this->cwd = $cwd;

        return $this;
    }


    public function setFile($file)
    {
        $this->file = realpath("$this->cwd/$file");
        $this->input = file_get_contents($this->file);

        return $this;
    }


    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }


    public function compile()
    {
        preg_match_all(self::TEMPLATE_REGEX, $this->input, $matches);

        $templates = array_unique($matches[0]);
        $rendered = array_map(function ($template) {
            return $this->render($template);
        }, $templates);

        $this->output = str_replace($templates, $rendered, $this->input);

        return $this;
    }


    public function getOutput()
    {
        return $this->output;
    }


    public function getParsedOutput()
    {
        try {
            return Yaml::parse($this->output);
        } catch (\Exception $e) {
            dump($this->output);
            throw new \Exception("Unable to parse $this->file", 0, $e);
        }
    }


    /**
     * Render a template using context and native functions
     *
     * @param $template
     *
     * @return string
     */
    private function render(string $template): string
    {
        /** @var string $expression The raw expression, without the double-braces */
        $expression = trim(str_replace(["{{", "}}"], "", $template));
        $parts = array_map(function ($part) {
            return trim($part);
        }, explode("|", $expression));

        if (isset($this->context[$parts[0]])) {
            $value = $this->context[$parts[0]];
            $pipes = $parts;
            array_shift($pipes);
        } else {
            $value = null;
            $pipes = $parts;
        }

        // Apply pipes
        foreach ($pipes as $pipe) {
            $pipeData = explode(":", $pipe);
            $pipe = $pipeData[0];
            $args = $value !== null ? [$value] : [];

            if (isset($pipeData[1])) {
                $args = array_merge($args, explode(",", $pipeData[1]));
            }

            if (function_exists($pipe)) {
                $value = $pipe(...$args);
            } elseif (method_exists($this, $pipe)) {
                $value = $this->$pipe(...$args);
            }
        }

        return $value !== null ? $value : "";
    }


    // Custom pipes


    /**
     * @param $value
     *
     * @return array|string
     */
    private function pluralize($value)
    {
        return Inflector::pluralize($value);
    }


    private function default(...$args)
    {
        return $args[0];
    }
}
