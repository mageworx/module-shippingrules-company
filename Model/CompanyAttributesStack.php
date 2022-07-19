<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\ShippingRulesCompany\Model;

use Magento\Framework\Event\ManagerInterface;

/**
 * Get company attributes which will be available in the conditions (or somewhere else)
 */
class CompanyAttributesStack implements \MageWorx\ShippingRulesCompany\Api\CompanyAttributesStackInterface
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param ManagerInterface $eventManager
     * @param array $attributes
     */
    public function __construct(
        ManagerInterface $eventManager,
        array            $attributes = []
    ) {
        $this->eventManager = $eventManager;
        $this->attributes   = $attributes;
    }

    /**
     * @inheritDoc
     */
    public function getAttributes(): array
    {
        $this->eventManager->dispatch(
            'mageworx_shippingrules_company_get_attributes_list',
            [
                'attributes' => $this->attributes
            ]
        );

        return $this->attributes;
    }
}
