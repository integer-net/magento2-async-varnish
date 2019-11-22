<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Cron;

use IntegerNet\AsyncVarnish\Model\PurgeAsyncCache as Purger;
use \Psr\Log\LoggerInterface;

/**
 * Cronjob: Regularly purge Varnish cache by saved tags in DB.
 */
class PurgeAsyncTags
{
    /**
     * @var Purger
     */
    private $purger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Purger $purger,
        LoggerInterface $logger
    ) {
        $this->purger = $purger;
        $this->logger = $logger;
    }

    public function run()
    {
        try {
            $successQty = $this->purger->run();
            $this->logger->info(sprintf('%s records purged from cache', $successQty));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
