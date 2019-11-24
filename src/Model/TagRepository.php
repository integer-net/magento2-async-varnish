<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Model;

use Magento\Framework\App\ResourceConnection;
use IntegerNet\AsyncVarnish\Model\ResourceModel\Tag as TagResource;
use Magento\Framework\App\Config\ScopeConfigInterface;

class TagRepository
{
    /**
     * DB Storage table name
     */
    const TABLE_NAME = 'integernet_async_varnish_tags';

    /**
     * Limits the amount of tags being fetched from database
     */
    const FETCH_TAG_LIMIT_CONFIG_PATH = 'system/full_page_cache/async_varnish/varnish_fetch_tag_limit';

    private $lastUsedId;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var TagResource
     */
    private $tagResource;

    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource,
        TagResource $tagResource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->tagResource = $tagResource;
        $this->scopeConfig = $scopeConfig;
    }

    private function getTagFetchLimit()
    {
        return $this->scopeConfig->getValue(self::FETCH_TAG_LIMIT_CONFIG_PATH);
    }

    /**
     * Insert multiple Tags
     *
     * @param array $tags
     * @return int
     * @throws \Exception
     */
    public function insertMultiple($tags = [])
    {
        if (empty($tags)) {
            return 0;
        }

        $data = array_map(
            function ($tag) {
                return ['entity_id' => null, 'tag' => $tag];
            },
            $tags
        );

        try {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);
            return $this->connection->insertMultiple($tableName, $data);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete multiple Tags by max entity_id
     *
     * @param int $maxId
     * @return int
     * @throws \Exception
     */
    public function deleteUpToId($maxId = 0)
    {
        try {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);
            return $this->connection->delete($tableName, 'entity_id <= '.$maxId);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function getAll()
    {

        $tags = [];

        $tagResource = $this->tagResource;
        $tagFetchLimit = $this->getTagFetchLimit();

        $maxIdResult = $tagResource->getMaxTagId($tagFetchLimit);

        if (empty($maxIdResult)) {
            return $tags;
        }

        $maxId = $maxIdResult['max_id'];

        $uniqueTagsResult = $tagResource->getUniqueTagsByMaxId((int)$maxId);

        if (!empty($uniqueTagsResult)) {
            $this->lastUsedId = $maxId;

            foreach ($uniqueTagsResult as $tag) {
                $tags[] = ($tag['tag']);
            }
        }
        return $tags;
    }

    /**
     * @return int
     */
    public function getLastUsedId()
    {
        return $this->lastUsedId ?: 0;
    }
}
