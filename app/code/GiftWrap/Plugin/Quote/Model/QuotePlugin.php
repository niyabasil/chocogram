<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Quote\Model;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use \Amasty\GiftWrap\Api\SaleData\SaleDataResourceInterface;

class QuotePlugin
{
    /**
     * @var SaleDataResourceInterface
     */
    private $saleDataResource;

    public function __construct(
        SaleDataResourceInterface $saleDataResource
    ) {
        $this->saleDataResource = $saleDataResource;
    }

    /**
     * @param Quote $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSave(Quote $subject, $result)
    {
        $this->saleDataResource->saveData(
            SaleDataResourceInterface::QUOTE_TABLE,
            'quote_id',
            $subject
        );
        $this->saleDataResource->saveItemsData(
            SaleDataResourceInterface::QUOTE_WRAP_TABLE,
            'quote_id',
            $subject
        );
        if ($subject->getIsAmNeedReloadItems()) {
            foreach ($subject->getAllItems() as $quoteItem) {
                $this->saleDataResource->loadItemsData(
                    SaleDataResourceInterface::QUOTE_ITEM_TABLE,
                    'quote_item_id',
                    $quoteItem
                );
            }
        }

        return $result;
    }

    /**
     * @param Quote $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterLoad(Quote $subject, $result)
    {
        $this->saleDataResource->loadData(
            SaleDataResourceInterface::QUOTE_TABLE,
            'quote_id',
            $subject
        );
        $this->saleDataResource->loadItemsData(
            SaleDataResourceInterface::QUOTE_WRAP_TABLE,
            'quote_id',
            $subject
        );

        return $result;
    }

    /**
     * @param Quote $subject
     * @param Collection $result
     * @return Quote
     */
    public function afterGetItemsCollection(Quote $subject, Collection $result)
    {
        if (!$subject->getIsAdditionalDataLoaded() && $result->isLoaded()) {
            foreach ($result->getItems() as $item) {
                $this->saleDataResource->loadItemsData(
                    SaleDataResourceInterface::QUOTE_ITEM_TABLE,
                    'quote_item_id',
                    $item
                );
            }
            $subject->setIsAdditionalDataLoaded(true);
        }

        return $result;
    }

    /**
     * @param Quote $subject
     * @return Quote
     */
    public function afterDelete(Quote $subject)
    {
        $this->saleDataResource->saveItemsData(
            SaleDataResourceInterface::QUOTE_WRAP_TABLE,
            'quote_id',
            $subject
        );

        return $subject;
    }
}
