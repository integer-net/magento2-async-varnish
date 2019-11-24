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
 * Backend model for processing Varnish Max header length settings
 *
 * Class HeaderLength
 */
class HeaderLength extends Value
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
     * @param Escaper $escaper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Escaper $escaper,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->escaper = $escaper;
    }

    /**
     * Throw exception if HeaderLength data is invalid or lower than 40 bytes
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if ($value < 40 || !preg_match('/^[0-9]+$/', $value)) {
            throw new LocalizedException(
                __(
                    'Header Length value "%1" is not valid. Please use only numbers equal or greater than 40.',
                    $this->escaper->escapeHtml($value)
                )
            );
        }
        return $this;
    }
}
