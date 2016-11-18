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

use Composer\Util\Filesystem;

/**
 * Class TypoScriptConstantsFiles
 */
class TypoScriptConstantsFiles
{
    /**
     * @var PackageConfig
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * TypoScriptConstantsFiles constructor.
     *
     * @param PackageConfig $config
     */
    public function __construct(PackageConfig $config, Filesystem $filesystem = null)
    {
        $this->config = $config;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function write()
    {
        foreach ($this->config->get('files') as $fileName => $prefixes) {
            $this->filesystem->ensureDirectoryExists(dirname($fileName));
            file_put_contents($fileName, $this->generateContentFromPrefixes($prefixes));
        }
    }

    /**
     * @param string[] $prefixes
     * @return string
     */
    private function generateContentFromPrefixes(array $prefixes)
    {
        $content = '';
        foreach ($prefixes as $prefix) {
            $content .= $this->generateContentFromPrefix($prefix) . chr(10);
        }
        $content = trim($content);
        return $content ? $content . chr(10) : '';
    }

    /**
     * @param string $prefix
     * @return string
     */
    private function generateContentFromPrefix($prefix)
    {
        $content = '';
        foreach ($_ENV as $varName => $value) {
            if (strpos($varName, $prefix) === 0) {
                $content .= sprintf(
                    '%s = %s',
                    $this->getConstantVarName($varName),
                    $_ENV[$varName]
                );
                $content .= chr(10);
            }
        }
        return $content;
    }

    /**
     * @param string $envVarName
     * @return string
     */
    private function getConstantVarName($envVarName)
    {
        $constantVarName = $envVarName;
        if ($this->config->get('array-delimiter')) {
            $constantVarName = str_replace((string)$this->config->get('array-delimiter'), '.', $constantVarName);
        }
        if ($this->config->get('lower-camel-case')) {
            // Expression taken from \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToLowerCamelCase()
            $constantVarName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($constantVarName)))));
        }
        if ($this->config->get('prefix')) {
            $constantVarName = (string)$this->config->get('prefix') . '.' . $constantVarName;
        }
        return $constantVarName;
    }
}
