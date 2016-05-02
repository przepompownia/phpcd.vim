<?php

namespace PHPCD\PHPFileInfo;

class StringBasedPHPFileInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return StringBasedPHPFileInfo
     */
    private function getFileInfo($class_path)
    {
        $reflection = new \ReflectionClass($class_path);
        return new StringBasedPHPFileInfo($reflection->getFileName());
    }

    public function newUsedClassInputAndFixProvider()
    {
        return [
            [
                // Alias Image is the same as the current class so suggest its change
                // The path is not used, suggest to add it
                '\PHPCD\Fixtures\ClassNamesAndAliases\Models\Image',
                ['alias' => 'Image', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Image'],
                ['Image2' => ['alias' => 'Image2', 'full_path' => 'PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Image']]
            ],
            [
                // Alias Image is already used, the path is not used
                // Suggest both to change alias and to add the path to imports
                '\PHPCD\Fixtures\ClassNamesAndAliases\Services\Image',
                ['alias' => 'Image', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Models\Image'],
                ['Image2' => ['alias' => 'Image2', 'full_path' => 'PHPCD\Fixtures\ClassNamesAndAliases\Models\Image']]
            ],
            [
                // inserted alias may be used (expect null),
                //  the path does not exist yet (expect that it will be suggested to adding)
                '\PHPCD\Fixtures\ClassNamesAndAliases\Services\Image',
                ['alias' => 'Image3', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Models\Image'],
                [null => ['alias' => null, 'full_path' => 'PHPCD\Fixtures\ClassNamesAndAliases\Models\Image']]
            ],
            [
                // the path is already used but with another alias
                // so suggest change the alias only
                '\PHPCD\Fixtures\ClassNamesAndAliases\Services\Image',
                ['alias' => 'Category', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Category'],
                ['Cat' => ['alias' => 'Cat', 'full_path' => null]]
            ],
            [
                // new used class is the current class, suggest nothing
                '\PHPCD\Fixtures\ClassNamesAndAliases\Services\Image',
                ['alias' => 'Image', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Services\Image'],
                ['namespace\Image' => ['alias' => 'namespace\Image', 'full_path' => null]]
            ],
            [
                // When the same path is aliased more than once
                // give user the choice
                '\PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Category',
                ['alias' => 'Image', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Image'],
                [
                    'Image' => ['alias' => 'Image', 'full_path' => null],
                    'Picture' => ['alias' => 'Picture', 'full_path' => null]
                ]
            ]
        ];
    }

    /**
     * @dataProvider newUsedClassInputAndFixProvider
     */
    public function testGetFixForNewClassUsage(
        $where,
        $input_class_info,
        $expected_suggestions
    ) {
        $file_info = $this->getFileInfo($where);

        $fix = $file_info->getFixForNewClassUsage($input_class_info);

        $this->assertEquals(
            count($expected_suggestions),
            count($fix),
            'Count of fixes differs from expected.'
        );

        foreach ($fix as $suggestion) {
            $new_alias = $suggestion['alias'];

            $this->assertTrue(array_key_exists($new_alias, $expected_suggestions), 'No such alias');
            $this->assertEquals(
                $new_alias,
                $expected_suggestions[$new_alias]['alias'],
                'Aliases are not equal.'
            );
            $this->assertEquals(
                $suggestion['full_path'],
                $expected_suggestions[$new_alias]['full_path'],
                'Paths are not equal.'
            );
        }
    }
}
