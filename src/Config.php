<?php

namespace Mathrix\OpenAPI\Processor;

/**
 * Class Config.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class Config
{
    /** @var array $defaultConfiguration The default configuration. */
    public static $defaultConfiguration = [
        "versionFrom" => null,
        "defaultTagGroup" => "Others",
        "schemas" => []
    ];
    /** @var string $configurationPath The configuration path. */
    private static $configurationPath;
    /** @var array $configurationData The configuration data. */
    private static $configurationData;


    /**
     * Get piece of data from array.
     *
     * @param array $data The input data.
     * @param string $key The key, in dot notation.
     *
     * @return array
     */
    private static function getFromKey(array $data, string $key)
    {
        $parts = explode(".", $key);

        foreach ($parts as $subKey) {
            if (!isset($data[$subKey])) {
                return null;
            }

            $data = $data[$subKey];
        }

        return $data;
    }


    /**
     * Load the configuration.
     *
     * @param string $configurationPath
     */
    public static function load(string $configurationPath)
    {
        self::$configurationPath = $configurationPath;

        if (file_exists($configurationPath)) {
            self::$configurationData = array_replace_recursive(
                self::$defaultConfiguration,
                FileLoader::make()->load($configurationPath)
            );
        } else {
            self::$configurationData = [];
        }
    }


    /**
     * Get a configuration entry.
     *
     * @param string $key The configuration key.
     *
     * @return mixed
     */
    public static function get(string $key)
    {
        return self::getFromKey(self::$configurationData, $key);
    }


    /**
     * Get the version.
     *
     * @return string
     */
    public static function version()
    {
        $versionFrom = Config::get("versionFrom");

        if (!(bool)strstr($versionFrom, "#")) {
            return null;
        }

        [$file, $key] = explode("#", $versionFrom);

        $realFile = realpath(dirname(self::$configurationPath) . "/$file");

        if ($realFile !== false) {
            $versionFileData = json_decode(file_get_contents($realFile), true);

            return (string)self::getFromKey($versionFileData, $key);
        } else {
            return null;
        }
    }
}
