<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\ShippingRulesCompany\Test\Unit\CompanyAttributes;

use PHPUnit\Framework\TestCase;

class DataTransferTest extends TestCase
{
    const TESTED_CLASS_NAME = 'MageWorx\ShippingRulesCompany\Model\CompanyAttributesDataTransform';

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $companyModelMock;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $companyManagementMock;

    /**
     * @var \Magento\Company\Api\Data\CompanyCustomerInterface|\Magento\Company\Model\Customer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $companyCustomerModelMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerExtensionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerExtensionAttributesModelMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerModelMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\DataObjectFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \MageWorx\ShippingRulesCompany\Model\CompanyAttributesDataTransform
     */
    private $model;

    public function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->dataObjectFactoryMock = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
                                            ->disableOriginalConstructor()
                                            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\Manager::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $this->customerModelMock = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->customerExtensionAttributesModelMock = $this->getMockBuilder(
            \Magento\Customer\Api\Data\CustomerExtensionInterface::class
        )
                                                           ->disableOriginalConstructor()
                                                           ->getMock();

        $this->companyCustomerModelMock = $this->getMockBuilder(\Magento\Company\Model\Customer::class)
                                               ->disableOriginalConstructor()
                                               ->getMock();

        $this->companyManagementMock = $this->getMockBuilder(
            \Magento\Company\Api\CompanyManagementInterface::class
        )
                                            ->disableOriginalConstructor()
                                            ->getMock();

        $this->companyModelMock = $this->getMockBuilder(\Magento\Company\Model\Company::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $args = [
            'companyManagement' => $this->companyManagementMock,
            'dataObjectFactory' => $this->dataObjectFactoryMock,
            'eventManager'      => $this->eventManagerMock
        ];

        $this->model = $this->objectManager->getObject(
            static::TESTED_CLASS_NAME,
            $args
        );
    }

    /**
     * Checks that the main methods are called with the correct parameters
     *
     * @return void
     */
    public function testBasicMethodsCallsWithCustomer(): void
    {
        $customerId          = 7;
        $companyData         = [];
        $companyCustomerData = [];
        $dataObject          = $this->objectManager->getObject(\Magento\Framework\DataObject::class);

        $this->dataObjectFactoryMock->expects($this->once())
                                    ->method('create')
                                    ->willReturn($dataObject);

        $this->eventManagerMock->expects($this->once())
                               ->method('dispatch')
                               ->with(
                                   'mw_copy_customer_company_data',
                                   [
                                       'data_object' => $dataObject,
                                       'customer'    => $this->customerModelMock,
                                       'company'     => $this->companyModelMock
                                   ]
                               );

        $this->customerExtensionAttributesModelMock->expects($this->atLeastOnce())
                                                   ->method('getCompanyAttributes')
                                                   ->willReturn($this->companyCustomerModelMock);

        $this->companyCustomerModelMock->expects($this->atLeastOnce())
                                       ->method('getData')
                                       ->willReturn($companyCustomerData);

        $this->customerModelMock->expects($this->atLeastOnce())
                                ->method('getId')
                                ->willReturn($customerId);

        $this->customerModelMock->expects($this->atLeastOnce())
                                ->method('getExtensionAttributes')
                                ->willReturn($this->customerExtensionAttributesModelMock);

        $this->companyManagementMock->expects($this->atLeastOnce())
                                    ->method('getByCustomerId')
                                    ->with($customerId)
                                    ->willReturn($this->companyModelMock);

        $this->companyModelMock->expects($this->atLeastOnce())
                               ->method('getData')
                               ->willReturn($companyData);

        $result = $this->model->getCompanyData($this->customerModelMock);

        $this->assertIsObject($result);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $result);
        $this->assertIsArray($result->getData());
        $this->assertEmpty($result->getData());
    }

    /**
     * Checks that the main methods are called with the correct parameters when customer id is not set (guest)
     *
     * @return void
     */
    public function testBasicMethodsCallsWithoutCustomer(): void
    {
        $customerId          = null;
        $dataObject          = $this->objectManager->getObject(\Magento\Framework\DataObject::class);

        $this->dataObjectFactoryMock->expects($this->once())
                                    ->method('create')
                                    ->willReturn($dataObject);

        $this->customerModelMock->expects($this->atLeastOnce())
                                ->method('getId')
                                ->willReturn($customerId);

        $this->eventManagerMock->expects($this->once())
                               ->method('dispatch')
                               ->with(
                                   'mw_copy_customer_company_data',
                                   [
                                       'data_object' => $dataObject,
                                       'customer'    => $this->customerModelMock,
                                       'company'     => null
                                   ]
                               );

        $this->customerExtensionAttributesModelMock->expects($this->never())
                                                   ->method('getCompanyAttributes');

        $this->companyCustomerModelMock->expects($this->never())
                                       ->method('getData');

        $this->customerModelMock->expects($this->never())
                                ->method('getExtensionAttributes');

        $this->companyManagementMock->expects($this->never())
                                    ->method('getByCustomerId');

        $this->companyModelMock->expects($this->never())
                               ->method('getData');

        $result = $this->model->getCompanyData($this->customerModelMock);

        $this->assertIsObject($result);
        $this->assertInstanceOf(\Magento\Framework\DataObject::class, $result);
        $this->assertIsArray($result->getData());
        $this->assertEmpty($result->getData());
    }
}
