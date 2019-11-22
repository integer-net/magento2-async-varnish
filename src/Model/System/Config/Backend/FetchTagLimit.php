<?php
declare(strict_types=1);

namespace IntegerNet\AsyncVarnish\Model\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Escaper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Backend model for processing Varnish Fetch Tag Limit settings
 *
 * Class HeaderLength
 */
class FetchTagLimit extends Value
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Ttl constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param Escaper|null $escaper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?Escaper $escaper = null
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->escaper = $escaper ?: ObjectManager::getInstance()->create(Escaper::class);
    }

    /**
     * Throw exception if HeaderLength data is invalid or empty
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value < 0 || !preg_match('/^[0-9]+$/', $value)) {
            throw new LocalizedException(
                __(
                    'Fetch tag limit value "%1" is not valid. Please use only numbers equal or greater than 0.',
                    $this->escaper->escapeHtml($value)
                )
            );
        }
        return $this;
    }
}
