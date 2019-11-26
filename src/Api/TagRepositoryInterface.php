<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Api;

interface TagRepositoryInterface
{
    /**
     * Insert multiple Tags
     *
     * @param string[] $tags
     * @return int
     * @throws \Exception
     */
    public function insertMultiple(array $tags = []): int;

    /**
     * Delete multiple Tags by max entity_id
     *
     * @param int $maxId
     * @return int
     * @throws \Exception
     */
    public function deleteUpToId(int $maxId = 0): int;

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAll(): array;

    /**
     * Return last ID (after getAll() call)
     *
     * @return int
     */
    public function getLastUsedId(): int;
}
