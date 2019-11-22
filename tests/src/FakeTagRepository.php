<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish;

use IntegerNet\AsyncVarnish\Api\TagRepositoryInterface;

class FakeTagRepository implements TagRepositoryInterface
{
    /**
     * @var string[]
     */
    private $tags = [];

    public function insertMultiple(array $tags = []): int
    {
        $this->tags = array_merge($this->tags, $tags);
        return count($tags);
    }

    public function deleteUpToId(int $maxId = 0): int
    {
        $deleted = 0;
        foreach ($this->tags as $key => $tag) {
            if ($key <= $maxId) {
                unset($this->tags[$key]);
                ++$deleted;
            }
        }
        return $deleted;
    }

    public function getAll(): array
    {
        return array_unique($this->tags);
    }

    public function getLastUsedId(): int
    {
        return array_key_last($this->tags);
    }

}