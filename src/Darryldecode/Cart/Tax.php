<?php namespace Darryldecode\Cart;

use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Darryldecode\Cart\Exceptions\InvalidTaxException;
use Darryldecode\Cart\Helpers\Helpers;
use Darryldecode\Cart\Validators\CartConditionValidator;

/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/15/2015
 * Time: 9:02 PM
 */
class Tax extends CartCondition
{
    /**
     * @param array $args (name, type, target, value)
     * @throws InvalidConditionException
     */
    public function __construct(array $args)
    {
        parent::__construct($args);
    }

    /**
     * apply condition
     *
     * @param $totalOrSubTotalOrPrice
     * @param $conditionValue
     * @return float
     */
    protected function apply($totalOrSubTotalOrPrice, $conditionValue)
    {
        // by default the value is not percent sign on it, the operation will not be a percentage
        $this->parsedRawValue = Helpers::normalizePrice($conditionValue);

        //die($this->parsedRawValue);
        // if value has a percentage sign on it, we will get first
        // its percentage then we will evaluate again if the value
        if ($this->valueIsPercentage($conditionValue)) {

            $value = Helpers::normalizePrice($conditionValue);

            $this->parsedRawValue = $totalOrSubTotalOrPrice * ($value / 100);
            //die($this->parsedRawValue);
        }

        $result = floatval($totalOrSubTotalOrPrice + $this->parsedRawValue);

        // Do not allow items with negative prices.
        return $result < 0 ? 0.00 : $result;
    }

    /**
     * validates tax arguments
     *
     * @param $args
     * @throws InvalidConditionException
     */
    protected function validate($args)
    {
        $rules = array(
            'name' => 'required',
            'type' => 'required',
            'value' => 'required',
        );

        $validator = CartConditionValidator::make($args, $rules);

        if ($validator->fails() || preg_replace('/[^0-9%.]/', '', $args['value']) !== $args['value']) {
            throw new InvalidTaxException($validator->messages()->first());
        }
    }
}