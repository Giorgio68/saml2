<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Utilities;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\RuntimeException;
use SimpleSAML\SAML2\Utilities\File;

/**
 * @package simplesamlphp/saml2
 */
#[CoversClass(File::class)]
final class FileTest extends TestCase
{
    /**
     */
    #[Group('utilities')]
    public function testWhenLoadingANonExistantFileAnExceptionIsThrown(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File "/foo/bar/baz/quux" does not exist or is not readable');
        File::getFileContents('/foo/bar/baz/quux');
    }

    /**
     */
    #[Group('utilities')]
    public function testAnExistingReadableFileCanBeLoaded(): void
    {
        $contents = File::getFileContents(__DIR__ . '/File/can_be_loaded.txt');

        $this->assertEquals(
            "Yes we can!\n",
            $contents,
            'The contents of the loaded file differ from what was expected',
        );
    }
}
