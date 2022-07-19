<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\ShippingRulesCompany\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\Manager;
use MageWorx\ShippingRulesCompany\Api\CompanyAttributesDataTransformInterface;
use Magento\Company\Api\CompanyManagementInterface;

/**
 * Collect all company attributes of customer and create the data object from it.
 * Can be extended using the "mw_copy_customer_company_data" event.
 */
class CompanyAttributesDataTransform implements CompanyAttributesDataTransformInterface
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Manager
     */
    private $eventManager;

    /**
     * @var CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @param CompanyManagementInterface $companyManagement
     * @param DataObjectFactory $dataObjectFactory
     * @param Manager $eventManager
     */
    public function __construct(
        CompanyManagementInterface $companyManagement,
        DataObjectFactory          $dataObjectFactory,
        Manager                    $eventManager
    ) {
        $this->companyManagement = $companyManagement;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager      = $eventManager;
    }

    /**
     * @param CustomerInterface $customer
     * @return DataObject
     */
    public function getCompanyData(CustomerInterface $customer): DataObject
    {
        $data = [];

        if ($customer->getId()) {
            /** @var \Magento\Company\Api\Data\CompanyCustomerInterface|null $companyAttributes */
            $companyAttributes = $customer->getExtensionAttributes()->getCompanyAttributes();
            /** @var \Magento\Company\Api\Data\CompanyInterface|null $company */
            $company = $this->companyManagement->getByCustomerId($customer->getId());

            if ($company) {
                $data += $company->getData();
            }

            if ($companyAttributes) {
                $data += $companyAttributes->getData();
            }
        }

        $dataObject = $this->dataObjectFactory->create(['data' => $data]);

        $this->eventManager->dispatch(
            'mw_copy_customer_company_data',
            [
                'data_object' => $dataObject,
                'customer'    => $customer,
                'company'     => $company ?? null
            ]
        );

        return $dataObject;
    }
}
