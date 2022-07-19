<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\ShippingRulesCompany\Model\Rule\Condition;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Directory\Model\Config\Source\Allregion as RegionsSourceModel;
use Magento\Directory\Model\Config\Source\Country as CountrySourceModel;
use Magento\Payment\Model\Config\Source\Allmethods as PaymentMethodsSourceModel;
use Magento\Rule\Model\Condition\AbstractCondition as RuleAbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Rule\Model\Condition\Context as ConditionContext;
use Magento\Shipping\Model\Config\Source\Allmethods as ShippingMethodsSourceModel;
use MageWorx\ShippingRules\Api\CustomerResolverInterface;
use MageWorx\ShippingRules\Api\SourceModelFactoryInterface;
use MageWorx\ShippingRules\Helper\Data as Helper;
use MageWorx\ShippingRules\Model\Config\Source\Locale\Country as ExtendedCountrySourceModel;
use MageWorx\ShippingRulesCompany\Api\CompanyAttributesDataTransformInterface;
use MageWorx\ShippingRulesCompany\Api\CompanyAttributesStackInterface;

/**
 * Conditions for company attributes and other B2B attributes.
 */
class Company extends \MageWorx\ShippingRules\Model\Condition\AbstractAddress
{
    /**
     * @var CompanyAttributesStackInterface
     */
    protected $attributesStack;

    /**
     * @var ExtendedCountrySourceModel
     */
    protected $sourceCountry;

    /**
     * @var CustomerResolverInterface
     */
    protected $customerResolver;

    /**
     * @var CompanyAttributesDataTransformInterface
     */
    protected $dataTransform;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var SourceModelFactoryInterface
     */
    protected $sourceModelFactory;

    /**
     * @param Helper $helper
     * @param Context $context
     * @param CountrySourceModel $directoryCountry
     * @param RegionsSourceModel $directoryAllRegion
     * @param ShippingMethodsSourceModel $shippingAllMethods
     * @param PaymentMethodsSourceModel $paymentAllMethods
     * @param CompanyAttributesStackInterface $attributesStack
     * @param array $data
     */
    public function __construct(
        Helper                                  $helper,
        ConditionContext                        $context,
        CountrySourceModel                      $directoryCountry,
        RegionsSourceModel                      $directoryAllRegion,
        ShippingMethodsSourceModel              $shippingAllMethods,
        PaymentMethodsSourceModel               $paymentAllMethods,
        ExtendedCountrySourceModel              $sourceCountry,
        CompanyAttributesStackInterface         $attributesStack,
        CustomerResolverInterface               $customerResolver,
        CompanyAttributesDataTransformInterface $dataTransform,
        SourceModelFactoryInterface             $sourceModelFactory,
        array                                   $data = []
    ) {
        $this->sourceCountry    = $sourceCountry;
        $this->attributesStack  = $attributesStack;
        $this->customerResolver = $customerResolver;
        $this->dataTransform    = $dataTransform;

        $this->sourceModelFactory = $sourceModelFactory;

        parent::__construct(
            $helper,
            $context,
            $directoryCountry,
            $directoryAllRegion,
            $shippingAllMethods,
            $paymentAllMethods,
            $data
        );
        $this->setType('MageWorx\ShippingRulesCompany\Model\Rule\Condition\Company');
    }

    /**
     * Validate model.
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model): bool
    {
        /** @var \Magento\Customer\Model\Data\Customer|CustomerInterface $customer */
        $customer = $this->customerResolver->resolve($model);
        /** @var \Magento\Framework\DataObject $dataObjectToValidate */
        $dataObjectToValidate = $this->dataTransform->getCompanyData($customer);
        $attributeValue       = $dataObjectToValidate->getData($this->getAttribute());

        if ($this->getAttribute() == 'country_id' && $this->helper->isExtendedCountrySelectEnabled()) {
            $this->prepareCountryId();
        }

        return $this->validateAttribute($attributeValue);
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions(): array
    {
        $attributes = $this->loadAttributeOptions()->getAttributeOption();
        $conditions = [];
        foreach ($attributes as $code => $label) {
            $conditions[] = ['value' => $this->getType() . '|' . $code, 'label' => $label];
        }

        return $conditions;
    }

    /**
     * Load attribute options.
     *
     * @return $this
     */
    public function loadAttributeOptions(): RuleAbstractCondition
    {
        if (empty($this->attributes)) {
            $this->attributes = $this->attributesStack->getAttributes();
            $attributes       = [];
            foreach ($this->attributes as $attributeCode => $attributeData) {
                $attributes[$attributeCode] = $attributeData['label'];
            }

            $this->setAttributeOption($attributes);
        }

        return $this;
    }

    /**
     * Get attribute options.
     *
     * @return array
     */
    public function getAttributeOptions(): array
    {
        $attributeOptions = $this->getData('attribute_option') ?? [];

        return $attributeOptions;
    }

    /**
     * Get input type for attribute operators.
     *
     * @return string
     */
    public function getInputType(): string
    {
        if (isset($this->attributes[$this->getAttribute()]['input_type'])) {
            return $this->attributes[$this->getAttribute()]['input_type'];
        }

        return 'string';
    }

    /**
     * Load value options.
     *
     * @return $this
     */
    public function loadValueOptions(): RuleAbstractCondition
    {
        $this->setValueOption([]);

        return $this;
    }

    /**
     * Value element type getter
     *
     * @return string
     */
    public function getValueElementType(): string
    {
        if (isset($this->attributes[$this->getAttribute()]['value_type'])) {
            return $this->attributes[$this->getAttribute()]['value_type'];
        }

        return 'text';
    }

    /**
     * Get countries as an option array based on modules settings
     *
     * @return array
     */
    protected function getCountryOptionsByConfig(): array
    {
        if ($this->helper->isExtendedCountrySelectEnabled()) {
            $options = $this->sourceCountry->toOptionArray();
        } else {
            $options = $this->_directoryCountry->toOptionArray();
        }

        return $options;
    }

    /**
     * Get value select options
     *
     * @return array
     */
    public function getValueSelectOptions(): array
    {
        if (!$this->hasData('value_select_options')) {
            switch ($this->getAttribute()) {
                case 'country_id':
                    $options = $this->getCountryOptionsByConfig();
                    break;

                case 'region_id':
                    $options = $this->_directoryAllregion->toOptionArray();
                    break;

                default:
                    $options = [];
            }

            if (empty($options) && !empty($this->attributes[$this->getAttribute()]['source_model'])) {
                if (is_string($this->attributes[$this->getAttribute()]['source_model'])) {
                    $source = $this->sourceModelFactory->create(
                        $this->attributes[$this->getAttribute()]['source_model']
                    );
                } else {
                    $source = $this->attributes[$this->getAttribute()]['source_model'];
                }

                $options = $source->toOptionArray();
            }

            $this->setData('value_select_options', $options);
        }

        $result = $this->getData('value_select_options');

        if (!is_array($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getAttributeElement(): \Magento\Framework\Data\Form\Element\AbstractElement
    {
        return parent::getAttributeElement()->setShowAsText(true);
    }
}
