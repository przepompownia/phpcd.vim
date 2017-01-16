<?php

namespace tests\NamespaceInfo;

use PHPCD\NamespaceInfo;
use PHPUnit\Framework\TestCase;

class NamespaceInfoTest extends TestCase
{
    public function prefixesDataProvider()
    {
        return [
            [
                '{"autoload": {"psr-4": {"PHPCD\\\\": "php/"}}, "autoload-dev": {"psr-4": {"PHPCD\\\\": "test/php/"}}}',
                ['PHPCD\\' => ['php/', 'test/php/']]
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
                '{"autoload": {"psr-4": {"PHPCD\\\\": "php/"}}, "autoload-dev": {"psr-4": {"PHPCD\\\\": "test/php/"}}}',
                '/root',
                '/root/php/X.php',
                ['PHPCD']
            ],
            [
                '{"autoload": {"psr-4": {"PHPCD\\\\": "php/"}}, "autoload-dev": {"psr-4": {"PHPCD\\\\": "test/php/"}}}',
                '/root',
                '/root/php/Foo/NewClass.php',
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
    public function getByPath($json, $root, $path, $expectedNamespace)
    {
        $nsinfo = new NamespaceInfo($root);
        $nsinfo->loadPrefixesFromComposerJson($json);

        $this->assertSame($expectedNamespace, $nsinfo->getByPath($path));
    }
}
