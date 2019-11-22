<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Observer;

use Magento\Framework\Event\ObserverInterface;

class InvalidateVarnishAsyncObserver implements ObserverInterface
{
    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @var \Magento\CacheInvalidate\Model\PurgeCache
     */
    private $purgeCache;

    /**
     * Invalidation tags resolver
     *
     * @var \Magento\Framework\App\Cache\Tag\Resolver
     */
    private $tagResolver;

    /**
     * Async tags storage
     *
     * @var \IntegerNet\AsyncVarnish\Model\TagRepository
     */
    private $tagRepository;
    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\CacheInvalidate\Model\PurgeCache $purgeCache
     * @param \Magento\Framework\App\Cache\Tag\Resolver $tagResolver
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\CacheInvalidate\Model\PurgeCache $purgeCache,
        \Magento\Framework\App\Cache\Tag\Resolver $tagResolver,
        \IntegerNet\AsyncVarnish\Model\TagRepository $tagRepository
    ) {
        $this->config = $config;
        $this->purgeCache = $purgeCache;
        $this->tagResolver = $tagResolver;
        $this->tagRepository = $tagRepository;
    }

    /**
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $object = $observer->getEvent()->getObject();
        if (!is_object($object)) {
            return;
        }
        if ($this->config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->config->isEnabled()) {
            $bareTags = $this->tagResolver->getTags($object);

            $tags = [];
            $pattern = "((^|,)%s(,|$))";
            foreach ($bareTags as $tag) {
                $tags[] = sprintf($pattern, $tag);
            }
            if (!empty($tags)) {
                $this->tagRepository->insertMultiple($tags);
            }
        }
    }
}
