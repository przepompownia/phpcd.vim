<?php

namespace tests\PHPFile;

use PHPCD\PHPFile\PHPFileFactory;
use PHPCD\PHPFile\StringBasedPHPFile;
use PHPUnit\Framework\TestCase;

class StringBasedPHPFileTest extends TestCase
{
    /**
     * @return StringBasedPHPFile|null
     */
    private function getFile($filePath)
    {
        $factory = new PHPFileFactory();

        return $factory->createFile($filePath);
    }

    public function newUsedClassInputAndFixProvider()
    {
        return [
            [
                // Alias Image is the same as the current class so suggest its change
                // The path is not used, suggest to add it
                'Fixtures/ClassNamesAndAliases/Models/Image.php',
                ['alias' => 'Image', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Image'],
                ['Image2' => ['alias' => 'Image2', 'full_path' => 'PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Image']]
            ],
            [
                // inserted alias may be used (expect null),
                //  the path does not exist yet (expect that it will be suggested to adding)
                'Fixtures/ClassNamesAndAliases/Services/Image.php',
                ['alias' => 'Image3', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Models\Image'],
                [null => ['alias' => null, 'full_path' => 'PHPCD\Fixtures\ClassNamesAndAliases\Models\Image']]
            ],
            [
                // the path is already used but with another alias
                // so suggest change the alias only
                'Fixtures/ClassNamesAndAliases/Services/Image.php',
                ['alias' => 'Category', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Category'],
                ['Cat' => ['alias' => 'Cat', 'full_path' => null]]
            ],
            [
                // When the same path is aliased more than once, but one of alias is the same as inserted
                // then do nothing
                'Fixtures/ClassNamesAndAliases/Repositories/Category.php',
                ['alias' => 'Image', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Repositories\Image'],
                [
                    null => ['alias' => null, 'full_path' => null]
                ]
            ],
            [
                'Fixtures/ClassNamesAndAliases/Repositories/Category.php',
                ['alias' => 'TestCase', 'full_path' => '\PHPUnit\Framework\TestCase'],
                [
                    null => ['alias' => null, 'full_path' => 'PHPUnit\Framework\TestCase']
                ]
            ],
            [
                // When the same path is aliased more than once, but differently than the inserted alias
                // give user the choice from the list of used aliases
                'Fixtures/ClassNamesAndAliases/Models/Gallery.php',
                ['alias' => 'Image', 'full_path' => '\PHPCD\Fixtures\ClassNamesAndAliases\Models\Image'],
                [
                    'Photo' => ['alias' => 'Photo', 'full_path' => null],
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
        $inputClass,
        $expectedSuggestions
    ) {
        $filePath = $this->getAbsoluteFilePath($where);

        $file = $this->getFile($filePath);

        $fix = $file->getFixForNewClassUsage($inputClass);

        $this->assertEquals(
            count($expectedSuggestions),
            count($fix),
            'Count of fixes differs from expected.'
        );

        foreach ($fix as $suggestion) {
            $new_alias = $suggestion['alias'];

            $this->assertTrue(array_key_exists($new_alias, $expectedSuggestions), 'No such alias');
            $this->assertEquals(
                $new_alias,
                $expectedSuggestions[$new_alias]['alias'],
                'Aliases are not equal.'
            );
            $this->assertEquals(
                $suggestion['full_path'],
                $expectedSuggestions[$new_alias]['full_path'],
                'Paths are not equal.'
            );
        }
    }

    public function testScanFileWithoutErrors()
    {
        $path = 'Fixtures/StringBasedPHPFile/ExampleWithoutErrors.php';

        $file = $this->getFile($this->getAbsoluteFilePath($path));

        $this->assertInstanceOf('PHPCD\PHPFile\PHPFile', $file);
        $this->assertEquals('tests\Fixtures\StringBasedPHPFile', $file->getNamespace());
        $this->assertEquals('ExampleWithoutErrors', $file->getClassName());
        $this->assertEquals('Cat', $file->getSuperclass());

        $interfaces = $file->getInterfaces();
        $this->assertFalse($file->hasErrors());
    }

    /**
     * @test
     */
    public function scanFileWithNonExistingInterface()
    {
        $path = 'Fixtures/StringBasedPHPFile/ExampleWithNonExistingInterfaces.php';

        $file = $this->getFile($this->getAbsoluteFilePath($path));

        $this->assertInstanceOf('PHPCD\PHPFile\PHPFile', $file);
        $this->assertEquals('tests\Fixtures\StringBasedPHPFile', $file->getNamespace());
        $this->assertEquals('ExampleWithNonExistingInterfaces', $file->getClassName());
        $this->assertNull($file->getSuperclass());

        $interfaces = $file->getInterfaces();
        $this->assertEquals(['I1', 'I2'], $interfaces);

        $this->assertTrue($file->hasErrors());
        $errors = $file->getErrors();
        $this->assertEquals('Interface tests\Fixtures\StringBasedPHPFile\I1 does not exist.', current($errors));
    }

    /**
     * @markTestSuiteSkipped
     */
    public function testScanFileWithSyntaxErrors()
    {
        $path = 'Fixtures/StringBasedPHPFile/ExampleWithSyntaxError.php';

        $file = $this->getFile($this->getAbsoluteFilePath($path));

        $this->assertInstanceOf('PHPCD\PHPFile\PHPFile', $file);
        $this->assertTrue($file->hasErrors());
    }

    public function testScanFileWithNonexistingSuperclass()
    {
        $path = 'Fixtures/StringBasedPHPFile/ExampleWithNonExistingSuperclass.php';

        $file = $this->getFile($this->getAbsoluteFilePath($path));

        $this->assertInstanceOf('PHPCD\PHPFile\PHPFile', $file);
        $this->assertTrue($file->hasErrors());

        $errors = $file->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals('Class A\\X1234 does not exist.', $errors[0]);
    }

    public function testScanFileWithNonexistingInterface()
    {
        $path = 'Fixtures/StringBasedPHPFile/ExampleWithNonExistingInterface.php';

        $file = $this->getFile($this->getAbsoluteFilePath($path));

        $this->assertInstanceOf('PHPCD\PHPFile\PHPFile', $file);
        $this->assertTrue($file->hasErrors());

        $errors = $file->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals('Interface A\\X456 does not exist.', $errors[0]);
    }

    private function getAbsoluteFilePath($relativePath)
    {
        return sprintf("%s/%s/%s", realpath('.'), 'tests/php', $relativePath);
    }

    public function testScanSuperInterface()
    {
        $path = 'Fixtures/StringBasedPHPFile/Subinterface.php';

        $file = $this->getFile($this->getAbsoluteFilePath($path));

        $this->assertInstanceOf('PHPCD\PHPFile\PHPFile', $file);
        $this->assertFalse($file->hasErrors(), implode(',', $file->getErrors()));
        $this->assertTrue($file->isInterface());
    }

    public function testScanClassThatImplementsQualifiedName()
    {
        $path = 'Fixtures/StringBasedPHPFile/ClassImplementsQualifiedName.php';

        $file = $this->getFile($this->getAbsoluteFilePath($path));

        $this->assertInstanceOf('PHPCD\PHPFile\PHPFile', $file);

        $this->assertFalse($file->hasErrors(), implode(',', $file->getErrors()));

        $this->assertTrue($file->isClass());
    }

    /**
     * @test
     */
    public function scanClassWithAliasedNamespace()
    {
        $path = 'Fixtures/StringBasedPHPFile/ExampleWithAliasedNamespace.php';

        $file = $this->getFile($this->getAbsoluteFilePath($path));

        $this->assertInstanceOf('PHPCD\PHPFile\PHPFile', $file);

        $this->assertFalse($file->hasErrors(), implode(',', $file->getErrors()));
    }
}
