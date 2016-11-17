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
use Composer\Script\Event;

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
        $packageMap = $this->extractPackageMapFromComposer($this->event->getComposer());
        foreach ($packageMap as $item) {
            /** @var PackageInterface $package */
            list($package, $installPath) = $item;

            $tsFiles = new TypoScriptConstantsFiles(
                PackageConfig::createFromPackage(
                    $package,
                    $installPath
                )
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
