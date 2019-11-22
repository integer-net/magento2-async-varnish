<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Test\Integration;

use IntegerNet\AsyncVarnish\Api\TagRepositoryInterface;
use IntegerNet\AsyncVarnish\Model\TagRepository;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class TagRepositoryTest extends AbstractTagRepositoryTest
{
    protected function getTestSubject(): TagRepositoryInterface
    {
        $objectManager = Bootstrap::getObjectManager();
        return $objectManager->get(TagRepository::class);
    }
}
