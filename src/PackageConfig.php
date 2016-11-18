<?php
namespace Helhum\EnvTs;

/*
 * This file is part of the env ts plugin package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\Package\PackageInterface;

/**
 * Class PackageConfig
 */
class PackageConfig
{
    const RELATIVE_PATHS = 1;

    /**
     * @var array
     */
    protected $config = [
        'files' => [],
        'prefix' => 'environment',
        'array-delimiter' => '__',
        'lower-camel-case' => true,
    ];

    /**
     * @var string
     */
    protected $packageInstallDir;

    /**
     * @param array $config
     * @param string $packageInstallDir
     */
    public function __construct(array $config, $packageInstallDir)
    {
        $this->merge($config);
        $this->packageInstallDir = $packageInstallDir;
    }

    /**
     * Returns a setting
     *
     * @param  string $key
     * @param  int $flags Options (see class constants)
     * @throws \RuntimeException
     * @return mixed
     */
    public function get($key, $flags = 0)
    {
        switch ($key) {
            case 'files':
                if (empty($this->config[$key]) || !is_array($this->config[$key])) {
                    return [];
                }
                $files = [];
                foreach ($this->config[$key]  as $file => $prefixes) {
                    $val = rtrim($this->process($file, $flags), '/\\');
                    $processedFile = ($flags & self::RELATIVE_PATHS === 1) ? $val : $this->realpath($val);
                    foreach ((array)$prefixes as &$prefix) {
                        $prefix = $this->process($prefix, $flags);
                    }
                    unset($prefix);
                    $files[$processedFile] = $prefixes;
                }
                return $files;
            default:
                if (!isset($this->config[$key])) {
                    return null;
                }
                return $this->process($this->config[$key], $flags);
        }
    }

    /**
     * Checks whether a setting exists
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->config);
    }

    /**
     * Merges new config values with the existing ones (overriding)
     *
     * @param array $config
     */
    private function merge(array $config)
    {
        // override defaults with given config
        foreach ($config as $key => $val) {
            if (!isset($this->config[$key])) {
                throw new \RuntimeException(sprintf('Configuration key "%s" is invalid', $key), 1479381278);
            }
            if (gettype($this->config[$key]) !== gettype($config[$key])) {
                throw new \RuntimeException(sprintf('Type of key "%s" must be array, "%s" given', $key, gettype($config[$key])), 1479381279);
            }
            $this->config[$key] = $val;
        }
    }

    /**
     * Replaces {$refs} inside a config string
     *
     * @param  string $value a config string that can contain {$refs-to-other-config}
     * @param  int $flags Options (see class constants)
     * @return string
     */
    private function process($value, $flags)
    {
        $config = $this;

        if (!is_string($value)) {
            return $value;
        }

        return preg_replace_callback('#\{\$(.+)\}#',
            function ($match) use ($config, $flags) {
                return $config->get($match[1], $flags);
            },
            $value);
    }

    /**
     * Turns relative paths in absolute paths without realpath()
     *
     * Since the dirs might not exist yet we can not call realpath or it will fail.
     *
     * @param  string $path
     * @return string
     */
    private function realpath($path)
    {
        if ($path === '') {
            return $this->packageInstallDir;
        }

        if ($path[0] === '/' || $path[1] === ':') {
            return $path;
        }

        return $this->packageInstallDir . '/' . $path;
    }

    /**
     * @param PackageInterface $package
     * @param string $packageInstallDir
     * @return PackageConfig
     */
    public static function createFromPackage(PackageInterface $package, $packageInstallDir)
    {
        $extraConfig = $package->getExtra();
        $packageConfig = [];
        if (isset($extraConfig['helhum/env-ts']) && is_array($extraConfig['helhum/env-ts'])) {
            $packageConfig = $extraConfig['helhum/env-ts'];
        }
        $config = new static(
            $packageConfig,
            $packageInstallDir
        );

        return $config;
    }
}
