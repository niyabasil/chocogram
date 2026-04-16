<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Block\Adminhtml\Wrap;

use Amasty\GiftWrap\Api\Data\WrapInterface;
use Amasty\GiftWrap\Model\PriceConverter;

class Analytic extends \Magento\Backend\Block\Template
{
    public const FIELD_ID = 'id';

    public const FIELD_NAME = 'name';

    public const FIELD_QTY = 'qty';

    public const FIELD_TOTAL = 'total';

    public const FIELD_DATE = 'date';
    /**
     * @var \Amasty\GiftWrap\Model\Wrapper\ResourceModel\Analytic
     */
    private $analytic;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var PriceConverter
     */
    private $priceConverter;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\GiftWrap\Model\Wrapper\ResourceModel\Analytic $analytic,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        PriceConverter $priceConverter,
        array $data = []
    ) {
        $this->analytic = $analytic;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
        $this->priceConverter = $priceConverter;
    }

    /**
     * @return array
     */
    public function getMostPopularWraps()
    {
        $mostPopular = $this->analytic->getMostPopularWraps();

        return $this->convertPriceData($mostPopular);
    }

    /**
     * @param array $wrapData
     * @return array
     */
    private function convertPriceData($wrapData = [])
    {
        $qty = 0;
        $total = 0;
        foreach ($wrapData as &$wrap) {
            $qty += $wrap[self::FIELD_QTY];
            $total += $wrap[self::FIELD_TOTAL];
            $wrap[self::FIELD_TOTAL] = $this->priceConverter->convertPrice($wrap[self::FIELD_TOTAL]);
        }

        $data['total'] = [
            self::FIELD_TOTAL => $this->priceConverter->convertPrice($total),
            self::FIELD_QTY => $qty
        ];
        $data['items'] = $wrapData;

        return $data;
    }

    /**
     * @return string
     */
    public function getWrapNames()
    {
        $wrapNames = $this->analytic->getWrapNames();
        $wraps = [];

        foreach ($wrapNames as $wrapName) {
            $wraps[$wrapName[WrapInterface::WRAP_ID]] = $wrapName[WrapInterface::NAME];
        }

        return $this->jsonEncoder->encode($wraps);
    }

    /**
     * @return string
     */
    public function getStatistics()
    {
        $rows = $this->analytic->getStatistics();

        return $this->jsonEncoder->encode($this->convertData($rows));
    }

    /**
     * @param array $rows
     * @return array
     */
    private function convertData($rows = [])
    {
        if (!count($rows)) {
            return [];
        }

        $data = [];
        $period = $rows[0][self::FIELD_DATE];
        $rowsCount = count($rows) - 1;
        $tmp = [];

        foreach ($rows as $key => $row) {
            if (!($period == $row[self::FIELD_DATE])) {
                $tmp[self::FIELD_DATE] = $period;
                $data[] = $tmp;
                $period = $row[self::FIELD_DATE];
                $tmp = [];
            }
            $tmp[$row[self::FIELD_ID]] = $row[self::FIELD_QTY];

            if ($key === $rowsCount) {
                $tmp[self::FIELD_DATE] = $period;
                $data[] = $tmp;
            }
        }

        return $data;
    }
}
