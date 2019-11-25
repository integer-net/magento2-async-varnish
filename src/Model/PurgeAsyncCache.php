<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Model;

use Magento\CacheInvalidate\Model\PurgeCache as PurgeCache;
use IntegerNet\AsyncVarnish\Model\TagRepository as TagRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PurgeAsyncCache
{
    const VARNISH_PURGE_TAG_GLUE = "|";
    const MAX_HEADER_LENGTH_CONFIG_PATH = 'system/full_page_cache/async_varnish/varnish_max_header_length';

    /**
     * @var PurgeCache
     */
    private $purgeCache;

    /**
     * @var TagRepository
     */
    private $tagRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        PurgeCache $purgeCache,
        ScopeConfigInterface $scopeConfig,
        TagRepository $tagRepository
    ) {
        $this->purgeCache = $purgeCache;
        $this->scopeConfig = $scopeConfig;
        $this->tagRepository = $tagRepository;
    }

    private function getMaxHeaderLengthFromConfig()
    {
        return $this->scopeConfig->getValue(self::MAX_HEADER_LENGTH_CONFIG_PATH);
    }

    private function createTagChunks(array $tags): array
    {
        $maxHeaderLength = $this->getMaxHeaderLengthFromConfig();
        $tagChunks = [];
        $index = 0;
        foreach ($tags as $tag) {
            $nextChunkString = (isset($tagChunks[$index])
                    ? $tagChunks[$index] . self::VARNISH_PURGE_TAG_GLUE
                    : '') . $tag;
            if (strlen($nextChunkString) <= $maxHeaderLength) {
                $tagChunks[$index] = $nextChunkString;
            } else {
                $index ++;
                $tagChunks[$index] = $tag;
            }
        }
        return $tagChunks;
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     * @throws \Exception
     */
    public function run():int
    {
        $tags = $this->tagRepository->getAll();

        if (!empty($tags)) {
            $tagChunks = $this->createTagChunks($tags);
            foreach ($tagChunks as $tagChunk) {
                $this->purgeCache->sendPurgeRequest($tagChunk);
            }
        }

        if ($lastUsedId = $this->tagRepository->getLastUsedId()) {
            $this->tagRepository->deleteUpToId($lastUsedId);
        }
        return count($tags);
    }
}
