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

use Composer\IO\IOInterface;
use Composer\IO\NullIO;
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
     * @var IOInterface
     */
    private $io;

    /**
     * TypoScriptConstantsFiles constructor.
     *
     * @param PackageConfig $config
     * @param Filesystem $filesystem
     * @param IOInterface $io
     */
    public function __construct(PackageConfig $config, Filesystem $filesystem = null, IOInterface $io = null)
    {
        $this->config = $config;
        $this->filesystem = $filesystem ?: new Filesystem();
        $this->io = $io ?: new NullIO();
    }

    public function write()
    {
        foreach ($this->config->get('files', PackageConfig::RELATIVE_PATHS) as $fileName => $prefixes) {
            $this->io->writeError(sprintf('<info>Writing file "%s" in package "%s"</info>', $fileName, $this->config->get('package-name')));
            $fileName = $this->getAbsolutePath($fileName);
            $this->filesystem->ensureDirectoryExists(dirname($fileName));
            file_put_contents($fileName, $this->generateContentFromPrefixes($prefixes));
        }
    }

    /**
     * @param string $fileName
     * @return string
     * @throws \RuntimeException
     */
    private function getAbsolutePath($fileName)
    {
        $absolutePath = $this->filesystem->normalizePath($this->config->get('package-install-dir') . '/' . $fileName);
        $packageInstallDir = $this->filesystem->normalizePath($this->config->get('package-install-dir'));
        if (strpos($absolutePath, $packageInstallDir) !== 0) {
            throw new \RuntimeException(sprintf('The path "%s" invalid, because it is not within path of the package "%s"', $fileName, $this->config->get('package-name')), 1479428249);
        }
        return $absolutePath;
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
