<?php

namespace Mathrix\OpenAPI\Processor;

use ArrayAccess;
use Symfony\Component\Yaml\Yaml;

/**
 * Class FileLoader.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class FileLoader extends Factory
{
    /**
     * Load a YAML file, and extend it if necessary.
     *
     * @param string $file The input file.
     * @param bool $extend If the $extends key should be used.
     *
     * @return array|ArrayAccess
     */
    public function load(string $file, bool $extend = true)
    {
        $data = Yaml::parseFile($file);

        if ($extend) {
            $data = $this->extends($data, dirname(realpath($file)));
        }
        Log::debug("Loaded $file");

        return $data;
    }


    /**
     * Write data to a YAML file.
     *
     * @param string $file The output file.
     * @param array|ArrayAccess $data The data to dump.
     */
    public function write(string $file, $data): void
    {
        file_put_contents($file, Yaml::dump($data, 13, 2));
        Log::debug("Written $file");
    }


    /**
     * Extends a file using the key $extends
     *
     * @param array|ArrayAccess $data The data to extend.
     * @param string $cwd The current working directory.
     *
     * @return array|ArrayAccess
     */
    public function extends($data, $cwd)
    {
        if (!is_array($data) && !$data instanceof ArrayAccess) {
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
            $extendedData = array_merge_recursive($extendedData, TemplateEngine::make()
                ->setCwd($cwd)
                ->setFile($file)
                ->setContext($context)
                ->compile()
                ->getParsedOutput());
        }

        return array_merge_recursive($extendedData, $data);
    }
}
