<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\ShippingRulesCompany\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;

interface CompanyAttributesDataTransformInterface
{
    /**
     * @param CustomerInterface $customer
     * @return DataObject
     */
    public function getCompanyData(CustomerInterface $customer): DataObject;
}
