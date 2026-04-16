<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Plugin\Sales\Block\Order\Email\Items;

use Amasty\GiftWrap\Block\Sales\Order\View\Info;
use Amasty\GiftWrap\Model\ConfigProvider;
use Magento\Sales\Block\Order\Email\Items;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class AddGiftWrapInfo
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function afterToHtml(Items $subject, string $result): string
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        if ($this->configProvider->isAddOptionsToEmail($storeId)) {
            try {
                /** @var Info $infoBlock */
                $infoBlock = $subject->getLayout()->createBlock(Info::class);
                $infoBlock->setData('order', $subject->getOrder());
                $infoBlock->setTemplate('Amasty_GiftWrap::sales/order/view/info.phtml');
                $infoBlock->setForEmail(true);

                if ($infoBlock->isAllowedToDisplay()) {
                    return $infoBlock->toHtml() . $result;
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }

        return $result;
    }
}
