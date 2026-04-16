<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\Price\Calculation;

use Amasty\GiftWrap\Model\Price\SalableInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;

class GetTaxPrice
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var TaxClassKeyInterfaceFactory
     */
    private $taxClassKeyFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var QuoteDetailsInterfaceFactory
     */
    private $quoteDetailsFactory;

    /**
     * @var QuoteDetailsItemInterfaceFactory
     */
    private $quoteDetailsItemFactory;

    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculationService;

    public function __construct(
        CustomerSession $customerSession,
        TaxClassKeyInterfaceFactory $taxClassKeyFactory,
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory,
        GroupRepositoryInterface $customerGroupRepository,
        QuoteDetailsInterfaceFactory $quoteDetailsFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory,
        TaxCalculationInterface $taxCalculationService
    ) {
        $this->customerSession = $customerSession;
        $this->taxClassKeyFactory = $taxClassKeyFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->quoteDetailsFactory = $quoteDetailsFactory;
        $this->quoteDetailsItemFactory = $quoteDetailsItemFactory;
        $this->taxCalculationService = $taxCalculationService;
    }

    public function execute(
        SalableInterface $salableItem,
        ?int $customerTaxClassId = null,
        ?AbstractAddress $shippingAddress = null,
        ?AbstractAddress $billingAddress = null
    ): float {
        $shippingAddressDataObject = null;
        if ($shippingAddress === null) {
            $shippingAddressDataObject = $this->convertDefaultTaxAddress(
                $this->customerSession->getDefaultTaxShippingAddress()
            );
        } elseif ($shippingAddress instanceof AbstractAddress) {
            $shippingAddressDataObject = $shippingAddress->getDataModel();
        }

        $billingAddressDataObject = null;
        if ($billingAddress === null) {
            $billingAddressDataObject = $this->convertDefaultTaxAddress(
                $this->customerSession->getDefaultTaxBillingAddress()
            );
        } elseif ($billingAddress instanceof AbstractAddress) {
            $billingAddressDataObject = $billingAddress->getDataModel();
        }

        $taxClassKey = $this->taxClassKeyFactory->create();
        $taxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
            ->setValue($salableItem->getTaxClassId());

        if ($customerTaxClassId === null && $this->customerSession->getCustomerGroupId() != null) {
            $customerTaxClassId = $this->customerGroupRepository->getById($this->customerSession->getCustomerGroupId())
                ->getTaxClassId();
        }

        $customerTaxClassKey = $this->taxClassKeyFactory->create();
        $customerTaxClassKey->setType(TaxClassKeyInterface::TYPE_ID)
            ->setValue($customerTaxClassId);

        $item = $this->quoteDetailsItemFactory->create();
        $item->setQuantity($salableItem->getQty())
            ->setCode(sprintf('%s-%d', $salableItem->getSalableType(), $salableItem->getId()))
            ->setTaxClassKey($taxClassKey)
            ->setIsTaxIncluded(false)
            ->setType($salableItem->getSalableType())
            ->setUnitPrice($salableItem->getPrice());

        $quoteDetails = $this->quoteDetailsFactory->create();
        $quoteDetails->setShippingAddress($shippingAddressDataObject)
            ->setBillingAddress($billingAddressDataObject)
            ->setCustomerTaxClassKey($customerTaxClassKey)
            ->setItems([$item])
            ->setCustomerId($this->customerSession->getCustomerId());

        $taxDetails = $this->taxCalculationService->calculateTax($quoteDetails, null, true);
        $items = $taxDetails->getItems();
        $taxDetailsItem = array_shift($items);

        return $taxDetailsItem->getPriceInclTax();
    }

    /**
     * Convert tax address array to address data object with country id and postcode
     *
     * @param array|null $taxAddress
     * @return AddressInterface|null
     */
    private function convertDefaultTaxAddress(?array $taxAddress = null): ?AddressInterface
    {
        if (empty($taxAddress)) {
            return null;
        }

        /** @var AddressInterface $addressDataObject */
        $addressDataObject = $this->addressFactory->create()
            ->setCountryId($taxAddress['country_id'])
            ->setPostcode($taxAddress['postcode']);

        if (isset($taxAddress['region_id'])) {
            $addressDataObject->setRegion($this->regionFactory->create()->setRegionId($taxAddress['region_id']));
        }

        return $addressDataObject;
    }
}
