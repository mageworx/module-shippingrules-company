<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\ShippingRulesCompany\Test\Unit\CompanyAttributes\Condition;

use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    const TESTED_CLASS_NAME = 'MageWorx\ShippingRulesCompany\Model\Rule\Condition\Company';

    /**
     * Operators in constants for better code readability
     *
     * '==' => __('is'),
     * '!=' => __('is not'),
     * '>=' => __('equals or greater than'),
     * '<=' => __('equals or less than'),
     * '>' => __('greater than'),
     * '<' => __('less than'),
     * '{}' => __('contains'),
     * '!{}' => __('does not contain'),
     * '()' => __('is one of'),
     * '!()' => __('is not one of'),
     * '<=>' => __('is undefined'),
     */
    const OPERATOR_IS                     = '==';
    const OPERATOR_IS_NOT                 = '!=';
    const OPERATOR_EQUALS_OR_GREATER_THAN = '>=';
    const OPERATOR_EQUALS_OR_LESS_THAN    = '<=';
    const OPERATOR_GREATER_THAN           = '>';
    const OPERATOR_LESS_THAN              = '<';
    const OPERATOR_CONTAINS               = '{}';
    const OPERATOR_DOES_NOT_CONTAIN       = '!{}';
    const OPERATOR_IS_ONE_OF              = '()';
    const OPERATOR_IS_NOT_ONE_OF          = '!()';
    const OPERATOR_IS_UNDEFINED           = '<=>';

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectMock;

    /**
     * @var \MageWorx\ShippingRulesCompany\Api\CompanyAttributesDataTransformInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $dataTransformMock;

    /**
     * @var \MageWorx\ShippingRulesCompany\Api\CompanyAttributesStackInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributesStackMock;

    /**
     * @var \MageWorx\ShippingRules\Api\CustomerResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerResolverMock;

    /**
     * @var Quote\Address|\PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteAddressMock;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \MageWorx\ShippingRulesCompany\Model\Rule\Condition\Company
     */
    private $model;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->customerMock = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();

        $this->quoteAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
                                       ->addmethods(['getCustomer'])
                                       ->onlyMethods(['getId', 'getQuote'])
                                       ->disableOriginalConstructor()
                                       ->getMock();

        $this->customerResolverMock = $this->getMockBuilder(
            \MageWorx\ShippingRules\Api\CustomerResolverInterface::class
        )
                                           ->disableOriginalConstructor()
                                           ->getMock();

        $this->attributesStackMock = $this->getMockBuilder(
            \MageWorx\ShippingRulesCompany\Api\CompanyAttributesStackInterface::class
        )
                                          ->disableOriginalConstructor()
                                          ->getMock();

        $this->dataTransformMock = $this->getMockBuilder(
            \MageWorx\ShippingRulesCompany\Api\CompanyAttributesDataTransformInterface::class
        )
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->dataObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
                                     ->disableOriginalConstructor()
                                     ->getMock();

        $this->helperMock = $this->getMockBuilder(\MageWorx\ShippingRules\Helper\Data::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $args = [
            'helper'           => $this->helperMock,
            'attributesStack'  => $this->attributesStackMock,
            'customerResolver' => $this->customerResolverMock,
            'dataTransform'    => $this->dataTransformMock
        ];

        $this->model = $this->objectManager->getObject(
            static::TESTED_CLASS_NAME,
            $args
        );
    }

    /**
     * Test validation method
     *
     * @return void
     * @dataProvider attributeValidationDataProvider @dataProvider
     */
    public function testValidation(array $attributeData, bool $expectedResult)
    {
        $testAttributeCode            = $attributeData['code'];
        $testAttributeOperator        = $attributeData['operator'];
        $testAttributeConditionValue  = $attributeData['condition_value'];
        $testAttributeValidationValue = $attributeData['value'];

        $this->customerResolverMock->expects($this->once())
                                   ->method('resolve')
                                   ->with($this->quoteAddressMock)
                                   ->willReturn($this->customerMock);

        $this->dataObjectMock->expects($this->atLeastOnce())
                             ->method('getData')
                             ->with($testAttributeCode)
                             ->willReturn($testAttributeValidationValue);

        $this->dataTransformMock->expects($this->once())
                                ->method('getCompanyData')
                                ->with($this->customerMock)
                                ->willReturn($this->dataObjectMock);

        $this->model->setAttribute($testAttributeCode);
        $this->model->setValue($testAttributeConditionValue);
        $this->model->setOperator($testAttributeOperator);

        $result = $this->model->validate($this->quoteAddressMock);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test type of conditions object
     *
     * @return void
     */
    public function testType(): void
    {
        $type = $this->model->getType();

        $this->assertEquals('MageWorx\ShippingRulesCompany\Model\Rule\Condition\Company', $type);
    }

    /**
     * Prepare country options method should be called if extended country select is enabled
     *
     * @return void
     */
    public function testCountryValidationWithExtendedOptions(): void
    {
        $testAttributeCode            = 'country_id';
        $testAttributeConditionValue  = 'EU';
        $testAttributeValidationValue = 'DE';
        $testAttributeOperator        = static::OPERATOR_IS_ONE_OF;
        $testAttributeInputType       = 'select';
        $testAttributeValueType       = 'select';
        $testAttributeSourceModel     = null;

        $attributes = [
            $testAttributeCode => [
                'code'         => $testAttributeCode,
                'label'        => 'Attribute Label',
                'input_type'   => $testAttributeInputType,
                'value_type'   => $testAttributeValueType,
                'source_model' => $testAttributeSourceModel
            ]
        ];

        $this->helperMock->expects($this->atLeastOnce())
                         ->method('isExtendedCountrySelectEnabled')
                         ->willReturn(true);

        $this->helperMock->expects($this->atLeastOnce())
                         ->method('getEuCountries')
                         ->willReturn(['DE', 'BG', 'ES']);

        $this->customerResolverMock->expects($this->once())
                                   ->method('resolve')
                                   ->with($this->quoteAddressMock)
                                   ->willReturn($this->customerMock);

        $this->attributesStackMock->expects($this->atLeastOnce())
                                  ->method('getAttributes')
                                  ->willReturn($attributes);

        $this->dataObjectMock->expects($this->atLeastOnce())
                             ->method('getData')
                             ->with($testAttributeCode)
                             ->willReturn($testAttributeValidationValue);

        $this->dataTransformMock->expects($this->once())
                                ->method('getCompanyData')
                                ->with($this->customerMock)
                                ->willReturn($this->dataObjectMock);

        // Configure validator
        $this->model->setAttribute($testAttributeCode);
        $this->model->setValue($testAttributeConditionValue);
        $this->model->setOperator($testAttributeOperator);
        $this->model->setInputType($testAttributeInputType);

        // For correct checking we must load attributes before we call validate method
        $this->model->loadAttributeOptions();

        $result = $this->model->validate($this->quoteAddressMock);

        $this->assertTrue($result);
    }

    /**
     * @return array[]
     */
    public function attributeValidationDataProvider(): array
    {
        return [
            [
                'attributeData'  => [
                    'code'            => 'company_id', // Attribute code
                    'condition_value' => '10', // Value in condition
                    'value'           => '10', // Value in object
                    'operator'        => self::OPERATOR_IS // Operator in condition
                ],
                'expectedResult' => true
            ],
            [
                'attributeData'  => [
                    'code'            => 'company_id',
                    'condition_value' => '10',
                    'value'           => '11',
                    'operator'        => self::OPERATOR_IS
                ],
                'expectedResult' => false
            ],
            [
                'attributeData'  => [
                    'code'            => 'company_id', // Attribute code
                    'condition_value' => '10', // Value in condition
                    'value'           => '10', // Value in object
                    'operator'        => self::OPERATOR_IS_NOT // Operator in condition
                ],
                'expectedResult' => false
            ],
            [
                'attributeData'  => [
                    'code'            => 'company_id',
                    'condition_value' => '10',
                    'value'           => '11',
                    'operator'        => self::OPERATOR_IS_NOT
                ],
                'expectedResult' => true
            ],
            [
                'attributeData'  => [
                    'code'            => 'company_id',
                    'condition_value' => '10',
                    'value'           => null,
                    'operator'        => self::OPERATOR_IS
                ],
                'expectedResult' => false
            ],
            [
                'attributeData'  => [
                    'code'            => 'company_email',
                    'condition_value' => '@mageworx.com',
                    'value'           => 's.uchuhlebov@mageworx.com',
                    'operator'        => self::OPERATOR_CONTAINS
                ],
                'expectedResult' => true
            ],
            [
                'attributeData'  => [
                    'code'            => 'company_email',
                    'condition_value' => '@mageworx.com',
                    'value'           => 's.uchuhlebov@mageworx.com',
                    'operator'        => self::OPERATOR_DOES_NOT_CONTAIN
                ],
                'expectedResult' => false
            ],
        ];
    }
}
