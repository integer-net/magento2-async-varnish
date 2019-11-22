<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Test\Unit;

use IntegerNet\AsyncVarnish\Api\TagRepositoryInterface;
use IntegerNet\AsyncVarnish\FakeTagRepository;
use IntegerNet\AsyncVarnish\Test\Integration\AbstractTagRepositoryTest;

class FakeTagRepositoryTest extends AbstractTagRepositoryTest
{
    protected function getTestSubject(): TagRepositoryInterface
    {
        return new FakeTagRepository();
    }

}