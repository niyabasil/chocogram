<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Gift Wrap for Magento 2
 */

namespace Amasty\GiftWrap\Model\OptionSource;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Option\ArrayInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Model\ClassModel;

class TaxClass implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $builder;

    /**
     * @var TaxClassRepositoryInterface
     */
    private $taxClassRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    public function __construct(
        TaxClassRepositoryInterface $taxClassRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $builder
    ) {
        $this->taxClassRepository = $taxClassRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->builder = $builder;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [['value' => '0', 'label' => __('None')]];

            foreach ($this->getTaxClasses() as $taxClass) {
                /** @var TaxClassInterface $taxClass */
                $this->options[] = [
                    'value' => $taxClass->getClassId(),
                    'label' => $taxClass->getClassName(),
                ];
            }
        }

        return $this->options;
    }

    private function getTaxClasses(): array
    {
        $filter = $this->builder->setField(ClassModel::KEY_TYPE)
            ->setValue(TaxClassManagementInterface::TYPE_PRODUCT)
            ->setConditionType('=')
            ->create();

        $searchCriteria = $this->criteriaBuilder->addFilters([$filter])->create();
        return $this->taxClassRepository->getList($searchCriteria)->getItems();
    }
}
