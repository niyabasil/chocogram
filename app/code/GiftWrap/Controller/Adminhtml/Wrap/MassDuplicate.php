<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Controller\Adminhtml\Wrap;

use Amasty\GiftWrap\Api\Data\WrapInterface;

class MassDuplicate extends AbstractMassAction
{
    /**
     * @param WrapInterface $wrap
     */
    protected function itemAction(WrapInterface $wrap)
    {
        $this->repository->duplicate($wrap->getEntityId());
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getErrorMessage()
    {
        return __('We can\'t duplicate item right now. Please review the log and try again.');
    }

    /**
     * @param int $collectionSize
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been duplicated.', $collectionSize);
        }

        return __('No records have been duplicated.');
    }
}
