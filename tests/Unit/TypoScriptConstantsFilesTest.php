<?php
namespace Helhum\EnvTs\tests\Unit;

/*
 * This file is part of the env ts plugin package.
 *
 * (c) Helmut Hummel <info@helhum.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\Package\PackageInterface;
use Helhum\EnvTs\PackageConfig;
use Helhum\EnvTs\TypoScriptConstantsFiles;
use org\bovigo\vfs\vfsStream;

/**
 * Class TypoScriptConstantsFilesTest
 */
class TypoScriptConstantsFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function writesConstantFileFromEnvVar()
    {
        $mockedPackage = $this->getMockBuilder(PackageInterface::class)->disableOriginalConstructor()->getMock();
        $mockedPackage->expects($this->any())
            ->method('getExtra')
            ->willReturn(['helhum/env-ts' => ['files' => [
                'Config/TS/Evn.t3s' => ['FOO_']
            ]]]);
        $root = vfsStream::setup('package-dir');
        $root->addChild(vfsStream::newDirectory('foo'));
        $config = PackageConfig::createFromPackage($mockedPackage, 'vfs://package-dir/foo');
        $_ENV['FOO_BAR__BAZ'] = 'foobarbaz';
        $_ENV['FOO_BLA__BAZ'] = 'fooblabaz';

        $constantsFile = new TypoScriptConstantsFiles($config);
        $constantsFile->write();

        $fileContent = file_get_contents('vfs://package-dir/foo/Config/TS/Evn.t3s');
        $this->assertSame(
            'environment.fooBar.baz = foobarbaz'
            . chr(10)
            . 'environment.fooBla.baz = fooblabaz'
            . chr(10),
            $fileContent
        );
    }

    /**
     * @test
     */
    public function writesEmptyFileIfNoMatchingConstant()
    {
        $mockedPackage = $this->getMockBuilder(PackageInterface::class)->disableOriginalConstructor()->getMock();
        $mockedPackage->expects($this->any())
            ->method('getExtra')
            ->willReturn(['helhum/env-ts' => ['files' => [
                'Config/TS/Evn.t3s' => ['FOO_']
            ]]]);
        $root = vfsStream::setup('package-dir');
        $root->addChild(vfsStream::newDirectory('foo'));
        $config = PackageConfig::createFromPackage($mockedPackage, 'vfs://package-dir/foo');

        $constantsFile = new TypoScriptConstantsFiles($config);
        $constantsFile->write();

        $fileContent = file_get_contents('vfs://package-dir/foo/Config/TS/Evn.t3s');
        $this->assertSame('', $fileContent);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionCode 1479428249
     */
    public function throwsExceptionOnInvalidPath()
    {
        $mockedPackage = $this->getMockBuilder(PackageInterface::class)->disableOriginalConstructor()->getMock();
        $mockedPackage->expects($this->any())
            ->method('getExtra')
            ->willReturn(['helhum/env-ts' => ['files' => [
                '../../Evn.t3s' => ['FOO_']
            ]]]);
        $root = vfsStream::setup('package-dir');
        $root->addChild(vfsStream::newDirectory('foo'));
        $config = PackageConfig::createFromPackage($mockedPackage, 'vfs://package-dir/foo');

        $constantsFile = new TypoScriptConstantsFiles($config);
        $constantsFile->write();
    }
}
