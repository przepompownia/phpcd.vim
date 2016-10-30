<?php

namespace PHPCD\NamespaceInfo;

use PHPCD\NamespaceInfo;
use PHPUnit\Framework\TestCase;

class NamespaceInfoTest extends TestCase
{
    public function prefixesDataProvider()
    {
        return [
            [
                '{"autoload": {"psr-4": {"PHPCD\\\\": "php/"}}, "autoload-dev": {"psr-4": {"PHPCD\\\\": "tests/php/"}}}',
                ['PHPCD\\' => ['php/', 'tests/php/']]
            ],
            [
                '{"autoload": {"psr-4": {"": "src/"}}}',
                ['' => ['src/']]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider prefixesDataProvider
     */
    public function getPrefixesFromComposerJson($json, $result)
    {
        $root = '/phpcd';
        $nsinfo = new NamespaceInfo($root);

        $this->assertSame($result, $nsinfo->getPrefixesFromComposerJson($json));
    }

    public function getByPathDataProvider()
    {
        return [
            [
                '{"autoload": {"psr-4": {"PHPCD\\\\": "php/"}}, "autoload-dev": {"psr-4": {"PHPCD\\\\": "tests/php/"}}}',
                '/root',
                '/root/src/Foo/NewClass.php',
                ['PHPCD\\Foo']
            ],
            [
                '{"autoload": {"psr-4": {"": "src/"}}}',
                '/root',
                '/root/src/Bar/NewClass.php',
                ['Bar']
            ]
        ];
    }

    /**
     * @test
     * @dataProvider getByPathDataProvider
     */
    public function getByPath($json, $root, $path, $namespace)
    {
        $this->markTestSkipped('Test skipped because of using realpath()');
        $nsinfo = new NamespaceInfo($root);
        $nsinfo->loadPrefixesFromComposerJson($json);

        $this->assertSame($namespace, $nsinfo->getByPath($path));
    }
}
