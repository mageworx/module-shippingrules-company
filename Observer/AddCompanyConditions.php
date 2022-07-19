<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\ShippingRulesCompany\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Adds company condition to the shipping rules
 */
class AddCompanyConditions implements ObserverInterface
{
    /**
     * @var \MageWorx\ShippingRulesCompany\Model\Rule\Condition\Company
     */
    protected $companyConditions;

    /**
     * @param \MageWorx\ShippingRulesCompany\Model\Rule\Condition\Company $companyConditions
     */
    public function __construct(
        \MageWorx\ShippingRulesCompany\Model\Rule\Condition\Company $companyConditions
    ) {
        $this->companyConditions = $companyConditions;
    }

    /**
     * Add company condition to the shipping rules
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var DataObject $additional */
        $additional = $observer->getEvent()->getAdditional();
        if (empty($additional) || !$additional instanceof DataObject) {
            return;
        }

        $conditions = (array)$additional->getData('conditions');
        $companyAttributes = $this->companyConditions->loadAttributeOptions()->getAttributeOptions();
        $companyConditions = [];
        foreach ($companyAttributes as $attributeCode => $attributeLabel) {
            $companyConditions[] = [
                'label' => $attributeLabel,
                'value' => 'MageWorx\ShippingRulesCompany\Model\Rule\Condition\Company|' . $attributeCode,
            ];
        }
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Company'),
                    'value' => $companyConditions,
                ],
            ]
        );

        $additional->setData('conditions', $conditions);
    }
}
