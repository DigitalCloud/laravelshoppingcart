<?php namespace Darryldecode\Cart;

/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/17/2015
 * Time: 11:03 AM
 */

use Darryldecode\Cart\Helpers\Helpers;
use Illuminate\Support\Collection;

class ItemCollection extends Collection
{

    /**
     * Sets the config parameters.
     *
     * @var
     */
    protected $config;

    /**
     * ItemCollection constructor.
     * @param array|mixed $items
     * @param $config
     */
    public function __construct($items, $config)
    {
        parent::__construct($items);

        $this->config = $config;
    }

    /**
     * get the sum of price
     *
     * @return mixed|null
     */
    public function getPriceSum()
    {

        return Helpers::formatValue($this->price * $this->quantity, $this->config['format_numbers'], $this->config);

    }

    public function __get($name)
    {
        if ($this->has($name)) return $this->get($name);
        return null;
    }

    public function getUnit()
    {
        if ($this->has('unit')) return $this->get('unit');
        return null;
    }

    public function getQuantities()
    {
        if ($this->has('quantities')) return $this->get('quantities');
        return collect([]);
    }

    public function getPricePercent()
    {
        if ($this->has('price_percent')) return $this->get('price_percent');
        return collect([]);
    }

    public function getQuantityPercent()
    {
        if ($this->has('quantity_percent')) return $this->get('quantity_percent');
        return collect([]);
    }

    /**
     * check if item has conditions
     *
     * @return bool
     */
    public function hasConditions()
    {
        if (!isset($this['conditions'])) return false;
        if (is_array($this['conditions'])) {
            return count($this['conditions']) > 0;
        }
        $conditionInstance = "Darryldecode\\Cart\\CartCondition";
        if ($this['conditions'] instanceof $conditionInstance) return true;

        return false;
    }

    public function hasAlternatives()
    {
        if (isset($this['alternative_id']) && $this['alternative_id']) {
            return true;
        }

        return false;
    }

    /**
     * check if item has taxes
     *
     * @return bool
     */
    public function hasTaxes()
    {
        if (!isset($this['taxes'])) return false;
        if (is_array($this['taxes'])) {
            return count($this['taxes']) > 0;
        }
        $taxInstance = "Darryldecode\\Cart\\Tax";
        if ($this['taxes'] instanceof $taxInstance) return true;

        return false;
    }

    /**
     * check if item has conditions
     *
     * @return mixed|null
     */
    public function getConditions()
    {
        if (!$this->hasConditions()) return [];
        return $this['conditions'];
    }

    /**
     * check if item has taxes
     *
     * @return mixed|null
     */
    public function getTaxes()
    {
        if (!$this->hasTaxes()) return [];
        return $this['taxes'];
    }

    public function inGroup()
    {
        if (isset($this['group_id']) && $this['group_id']) return true;

        return false;
    }

    /**
     * get the single price in which conditions are already applied
     * @param bool $formatted
     * @return mixed|null
     */
    public function getPriceWithConditions($formatted = true)
    {
        $originalPrice = $newPrice = $this->price;
        $processed = 0;

        if ($this->hasConditions()) {
            if (is_array($this->conditions)) {
                foreach ($this->conditions as $cond) {
                    ($processed > 0) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;
                    $newPrice = $cond->applyCondition($toBeCalculated);
                    $processed++;
                }
            } else {
                $newPrice = $this['conditions']->applyCondition($originalPrice);
            }
        }

        if ($this->hasTaxes()) {
            if (is_array($this->taxes)) {
                $processed = 0;
                foreach ($this->taxes as $tax) {
                    $toBeCalculated = $newPrice;
                    $newPrice = $tax->applyCondition($toBeCalculated);
                    $processed++;
                }
            } else {
                $newPrice = $this['taxes']->applyCondition($originalPrice);
            }
        }

        return Helpers::formatValue($newPrice, $formatted, $this->config);
    }

    /**
     * get the sum of price in which conditions are already applied
     * @param bool $formatted
     * @return mixed|null
     */
    public function getPriceSumWithConditions($formatted = true)
    {
        return Helpers::formatValue($this->getPriceWithConditions(false) * $this->quantity, $formatted, $this->config);
    }

    public function isOption()
    {
        return $this->is_optional;
    }
}
