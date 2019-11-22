<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Model;

use Magento\CacheInvalidate\Model\PurgeCache as PurgeCache;
use IntegerNet\AsyncVarnish\Model\TagRepository as TagRepository;

class PurgeAsyncCache
{
    /**
     * Size of the chunks we're sending to Varnish
     */
    const CHUNK_SIZE = 100;

    /**
     * @var \Magento\CacheInvalidate\Model\PurgeCache
     */
    private $purgeCache;

    /**
     * @var \IntegerNet\AsyncVarnish\Model\TagRepository
     */
    private $tagRepository;

    public function __construct(
        PurgeCache $purgeCache,
        TagRepository $tagRepository
    ) {
        $this->purgeCache = $purgeCache;
        $this->tagRepository = $tagRepository;
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     * @throws \Exception
     */
    public function run():int
    {
        $tags = $this->tagRepository->getAll();

        if (!empty($tags)) {
            $tagChunks = array_chunk($tags, self::CHUNK_SIZE, true);
            foreach ($tagChunks as $tagChunk) {
                $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tagChunk)));
            }
        }

        if ($lastUsedId = $this->tagRepository->getLastUsedId()) {
            $this->tagRepository->deleteUpToId($lastUsedId);
        }
        return count($tags);
    }
}
