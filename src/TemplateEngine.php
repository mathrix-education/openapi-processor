<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor;

use ArrayAccess;
use Mathrix\OpenAPI\Processor\Pipes\DefaultPipe;
use Mathrix\OpenAPI\Processor\Pipes\PluralizePipe;
use Symfony\Component\Yaml\Yaml;
use function array_map;
use function array_merge;
use function array_shift;
use function array_unique;
use function explode;
use function file_get_contents;
use function forward_static_call_array;
use function function_exists;
use function method_exists;
use function preg_match_all;
use function realpath;
use function str_replace;
use function trim;

class TemplateEngine extends Factory
{
    public const TEMPLATE_REGEX = '/\{\{\s?[a-zA-Z0-9\_\| \:\,]+\s?\}\}/';

    private static $pipes = [
        'default'   => DefaultPipe::class,
        'pluralize' => PluralizePipe::class,
    ];

    /** @var string The current working directory. */
    private $cwd;
    /** @var string The file which will be rendered. */
    private $file;
    /** @var mixed[] The context of the file (variables, etc.). */
    private $context;
    /** @var string The file original content. */
    private $input;
    /** @var string The file rendered content. */
    private $output;

    /**
     * Register a pipe in the template engine.
     *
     * @param string $key   The pipe key.
     * @param string $class The pipe class.
     */
    public static function registerPipe(string $key, string $class)
    {
        self::$pipes[$key] = $class;
    }

    /**
     * Set the current working directory.
     *
     * @param string $cwd
     *
     * @return $this
     */
    public function setCwd(string $cwd)
    {
        $this->cwd = $cwd;

        return $this;
    }

    /**
     * Set the current input file.
     *
     * @param string $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file  = realpath("$this->cwd/$file");
        $this->input = file_get_contents($this->file);

        return $this;
    }

    /**
     * Set the current context.
     *
     * @param array|ArrayAccess $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Render all templates, replace them in the input file content and set them into the output property.
     *
     * @return $this
     */
    public function compile()
    {
        preg_match_all(self::TEMPLATE_REGEX, $this->input, $matches);

        $templates = array_unique($matches[0]);
        $rendered  = array_map(function ($template) {
            return $this->render($template);
        },
            $templates);

        $this->output = str_replace($templates, $rendered, $this->input);

        return $this;
    }

    /**
     * Get the string representation of the output.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Get the array representation of the output.
     *
     * @return array|ArrayAccess
     */
    public function getParsedOutput()
    {
        return Yaml::parse($this->output);
    }

    /**
     * Render a template using context and native functions
     *
     * @param string $template The template content string.
     *
     * @return string
     */
    public function render(string $template): string
    {
        /** @var string $expression The raw expression, without the double-braces */
        $expression = trim(str_replace(['{{', '}}'], '', $template));
        $parts      = array_map(
            static function ($part) {
                return trim($part);
            },
            explode('|', $expression)
        );

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
            $pipeData = explode(':', $pipe);
            $pipe     = $pipeData[0];
            $args     = $value !== null ? [$value] : [];

            if (isset($pipeData[1])) {
                $args = array_merge($args, explode(',', $pipeData[1]));
            }

            if (isset(self::$pipes[$pipe])) {
                $value = forward_static_call_array([self::$pipes[$pipe], 'transform'], $args);
            } elseif (function_exists($pipe)) {
                $value = $pipe(...$args);
            } elseif (method_exists($this, $pipe)) {
                $value = $this->$pipe(...$args);
            }
        }

        return (string)$value ?? '';
    }
}
