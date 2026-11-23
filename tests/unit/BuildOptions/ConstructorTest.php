<?php

declare(strict_types=1);

namespace PHPMath\DocsBuilder\Tests\BuildOptions;

use PHPMath\DocsBuilder\BuildOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversClass(BuildOptions::class)]
#[CoversMethod(BuildOptions::class, '__construct')]
final class ConstructorTest extends TestCase
{
    public function testStoresTheValues(): void
    {
        // Arrange

        // Act
        $options = new BuildOptions('biginteger', '3.0.0', 'phpmath/biginteger', 'docs', 'build', 'docs', 'main');

        // Assert
        self::assertSame('biginteger', $options->library);
        self::assertSame('3.0.0', $options->version);
        self::assertSame('phpmath/biginteger', $options->repository);
        self::assertSame('docs', $options->source);
        self::assertSame('build', $options->output);
        self::assertSame('docs', $options->editPath);
        self::assertSame('main', $options->editBranch);
    }

    public function testEditBranchDefaultsToMaster(): void
    {
        // Arrange

        // Act
        $options = new BuildOptions('biginteger', 'master', 'phpmath/biginteger', 'docs', 'build', 'docs');

        // Assert
        self::assertSame('master', $options->editBranch);
    }
}
