<?php

namespace Mathrix\OpenAPI\PreProcessor;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Loader.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class Loader
{
    public static function make()
    {
        return new self();
    }


    public function load(string $file, bool $extend = true)
    {
        try {
            $data = Yaml::parseFile($file);
        } catch (ParseException $exception) {
            $exception->setParsedFile($file);
            dd($exception);
            throw $exception;
        }

        if ($extend) {
            $data = $this->extends($data, dirname(realpath($file)));
        }

        return $data;
    }


    public function write(string $file, $data)
    {
        file_put_contents($file, Yaml::dump($data, 13, 2));
    }


    public function extends($data, $cwd)
    {
        if (!is_array($data) && !$data instanceof \ArrayAccess) {
            // data is not iterable, return directly
            return $data;
        } elseif (!isset($data["\$extends"])) {
            // data has not the $extends property, we need to inspect deeper
            foreach ($data as $k => $v) {
                $data[$k] = $this->extends($v, $cwd);
            }

            return $data;
        }

        // Now, $extends exists
        $extends = $data["\$extends"];
        unset($data["\$extends"]);

        $extendedData = [];

        foreach ($extends as $file => $context) {
            $extendedData = array_merge_recursive($extendedData, Renderer::make()
                ->setCwd($cwd)
                ->setFile($file)
                ->setContext($context)
                ->compile()
                ->getParsedOutput());
        }

        return array_merge_recursive($extendedData, $data);
    }
}
