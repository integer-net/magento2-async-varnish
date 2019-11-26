<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Test\Integration;

use IntegerNet\AsyncVarnish\Api\TagRepositoryInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractTagRepositoryTest extends TestCase
{
    /**
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    protected function setUp()
    {
        $this->tagRepository = $this->getTestSubject();
    }

    abstract protected function getTestSubject(): TagRepositoryInterface;

    public function testInsertAndRetrieve()
    {
        $affected = $this->tagRepository->insertMultiple(['x', 'y', 'z']);
        $this->assertEquals(3, $affected, 'insertMultiple() should return number of inserted rows');
        $this->assertEqualsCanonicalizing(['x', 'y', 'z'], $this->tagRepository->getAll());
    }

    public function testNoDuplicatesAreRetrieved()
    {
        $affected = $this->tagRepository->insertMultiple(['x', 'y', 'x']);
        $this->assertEquals(3, $affected, 'insertMultiple() should return number of inserted rows');
        $this->assertEqualsCanonicalizing(['x', 'y'], $this->tagRepository->getAll());
    }

    public function testNoDuplicatesAreRetrievedAfterSubsequentCalls()
    {
        $affected = $this->tagRepository->insertMultiple(['x', 'y']);
        $this->assertEquals(2, $affected, 'insertMultiple() should return number of inserted rows');
        $affected = $this->tagRepository->insertMultiple(['y', 'z']);
        $this->assertEquals(2, $affected, 'insertMultiple() should return number of inserted rows');
        $this->assertEqualsCanonicalizing(['x', 'y', 'z'], $this->tagRepository->getAll());
    }

    public function testLastUsedIdIncreases()
    {
        $this->tagRepository->insertMultiple(['x']);
        $this->tagRepository->getAll();
        $lastUsedId = $this->tagRepository->getLastUsedId();
        $this->tagRepository->insertMultiple(['y']);
        $this->tagRepository->getAll();
        //TODO maybe throw exception if getAll has not been called before:
        $this->assertEquals($lastUsedId + 1, $this->tagRepository->getLastUsedId());
    }

    public function testDeleteUpToId()
    {
        $this->tagRepository->insertMultiple(['x', 'y', 'z']);
        $this->tagRepository->getAll();
        $lastUsedId = $this->tagRepository->getLastUsedId();
        $this->tagRepository->insertMultiple(['a', 'b', 'c']);
        $affected = $this->tagRepository->deleteUpToId($lastUsedId);
        $this->assertEquals(3, $affected, 'deleteUpToId() should return number of deleted rows');
        $this->assertEqualsCanonicalizing(['a', 'b', 'c'], $this->tagRepository->getAll());
    }

    /**
     * Backport from PHPUnit 8
     *
     * @param array $expected
     * @param array $actual
     */
    public static function assertEqualsCanonicalizing(array $expected, array $actual, string $message = '')
    {
        self::assertEquals($expected, $actual, $message, 0.0, 10, true);
    }
}
