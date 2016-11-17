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

/**
 * Class PackageConfigTest
 */
class PackageConfigTest extends \PHPUnit_Framework_TestCase
{
    public function invalidConfigTypesDataProvider()
    {
        return [
            'invalid files' => [
                [
                    'files' => 'foo'
                ]
            ],
            'invalid prefix' => [
                [
                    'prefix' => true
                ]
            ],
            'invalid array-delimiter' => [
                [
                    'array-delimiter' => new \stdClass()
                ]
            ],
            'invalid lower-camel-case' => [
                [
                    'lower-camel-case' => '1'
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidConfigTypesDataProvider
     * @expectedException \RuntimeException
     * @expectedExceptionCode 1479381279
     */
    public function createThrowsExceptionOnInvalidType($packageConfig)
    {
        $mockedPackage = $this->getMockBuilder(PackageInterface::class)->disableOriginalConstructor()->getMock();
        $mockedPackage->expects($this->any())
            ->method('getExtra')
            ->willReturn(['helhum/env-ts' => $packageConfig]);

        PackageConfig::createFromPackage($mockedPackage, '/foo/bar');
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionCode 1479381278
     */
    public function createThrowsExceptionOnInvalidKey()
    {
        $mockedPackage = $this->getMockBuilder(PackageInterface::class)->disableOriginalConstructor()->getMock();
        $mockedPackage->expects($this->any())
            ->method('getExtra')
            ->willReturn(['helhum/env-ts' => ['foo' => 'bar']]);

        PackageConfig::createFromPackage($mockedPackage, '/foo/bar');
    }

    /**
     * @test
     */
    public function returnsDefaultIfNoConfigIsGiven()
    {
        $mockedPackage = $this->getMockBuilder(PackageInterface::class)->disableOriginalConstructor()->getMock();
        $mockedPackage->expects($this->any())
            ->method('getExtra')
            ->willReturn([]);

        $config = PackageConfig::createFromPackage($mockedPackage, '/foo/bar');
        $this->assertTrue($config->get('lower-camel-case'));
    }

    /**
     * @test
     */
    public function returnsConfigIfConfigIsGiven()
    {
        $mockedPackage = $this->getMockBuilder(PackageInterface::class)->disableOriginalConstructor()->getMock();
        $mockedPackage->expects($this->any())
            ->method('getExtra')
            ->willReturn(['helhum/env-ts' => ['lower-camel-case' => false]]);

        $config = PackageConfig::createFromPackage($mockedPackage, '/foo/bar');
        $this->assertFalse($config->get('lower-camel-case'));
    }

    /**
     * @test
     */
    public function returnsProcessedFilesConfig()
    {
        $mockedPackage = $this->getMockBuilder(PackageInterface::class)->disableOriginalConstructor()->getMock();
        $mockedPackage->expects($this->any())
            ->method('getExtra')
            ->willReturn(['helhum/env-ts' => ['files' => [
                'Config/TS/Evn.t3s' => ['FOO_']
            ]]]);

        $config = PackageConfig::createFromPackage($mockedPackage, '/foo/bar');
        $filesConfig = $config->get('files');
        $this->assertArrayHasKey('/foo/bar/Config/TS/Evn.t3s', $filesConfig);
        $this->assertSame(['FOO_'], $filesConfig['/foo/bar/Config/TS/Evn.t3s']);
    }
}
