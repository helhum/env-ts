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

use Composer\Config as ComposerConfig;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;

/**
 * Class Plugin
 */
class PluginImplementation
{
    /**
     * @var Event
     */
    private $event;

    /**
     * PluginImplementation constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function preAutoloadDump()
    {
        // Nothing to do for us here currently
    }

    /**
     * Action called after autoload dump
     */
    public function postAutoloadDump()
    {
        $composerConfig = $this->event->getComposer()->getConfig();
        // Establish environment variables from dotenv-connector if it is present
        $dotEnvInclusionFile = $composerConfig->get('vendor-dir') . '/helhum/dotenv-include.php';
        if (file_exists($dotEnvInclusionFile)) {
            require_once $dotEnvInclusionFile;
        }

        // Write constants files for each package
        $packageMap = $this->extractPackageMapFromComposer($this->event->getComposer());
        $basePath = realpath(substr($composerConfig->get('vendor-dir'), 0, -strlen($composerConfig->get('vendor-dir', ComposerConfig::RELATIVE_PATHS))));
        foreach ($packageMap as $item) {
            /** @var PackageInterface $package */
            list($package, $installPath) = $item;
            $installPath = ($installPath ?: $basePath);

            $tsFiles = new TypoScriptConstantsFiles(
                PackageConfig::createFromPackage(
                    $package,
                    $installPath
                ),
                new Filesystem(),
                $this->event->getIO()
            );
            $tsFiles->write();
        }
    }

    /**
     * @param \Composer\Composer $composer
     * @return array
     */
    private function extractPackageMapFromComposer(\Composer\Composer $composer)
    {
        $mainPackage = $composer->getPackage();
        $autoLoadGenerator = $composer->getAutoloadGenerator();
        $localRepo = $composer->getRepositoryManager()->getLocalRepository();
        return $autoLoadGenerator->buildPackageMap($composer->getInstallationManager(), $mainPackage, $localRepo->getCanonicalPackages());
    }
}
