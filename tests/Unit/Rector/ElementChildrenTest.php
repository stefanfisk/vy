<?php

declare(strict_types=1);

namespace Package\Tests\Rector\MyFirstRector;

use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use StefanFisk\Vy\Rector\ElementChildren;

#[CoversClass(ElementChildren::class)]
class ElementChildrenTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/../../Fixtures/Rector/ElementChildren');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/../../Fixtures/Rector/ElementChildren/config.php';
    }
}
