<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Model\ResourceModel;

class Tag extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * DB Storage table name
     */
    const TABLE_NAME = 'integernet_async_varnish_tags';

    /* must be protected */
    protected function _construct() //phpcs:ignore MEQP2.PHP.ProtectedClassMember.FoundProtected
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    public function getMaxTagId(int $limit):array
    {
        $connection = $this->getConnection();

        $subSetSelect = $connection->select()->from(
            self::TABLE_NAME,
            ['entity_id','tag']
        )->order(
            'entity_id',
            'ASC'
        )->limit(
            $limit
        );

        $maxIdSelect = $connection->select()->from(
            $subSetSelect,
            ['max_id'=>'MAX(entity_id)']
        );

        return $connection->fetchRow($maxIdSelect);
    }

    public function getUniqueTagsByMaxId(int $maxId):array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['main_table' => self::TABLE_NAME],
            ['tag']
        )->group(
            'tag'
        )->where(
            'main_table.entity_id <= ?',
            $maxId
        );

        return $connection->fetchAll($select);
    }
}
