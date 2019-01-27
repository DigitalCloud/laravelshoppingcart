<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/12/2015
 * Time: 9:59 PM
 */

use Darryldecode\Cart\Cart;
use Darryldecode\Cart\Tax;
use Mockery as m;

require_once __DIR__ . '/../helpers/SessionMock.php';

class TaxesTest extends PHPUnit\Framework\TestCase
{

    /**
     * @var Darryldecode\Cart\Cart
     */
    protected $cart;

    public function setUp()
    {
        $events = m::mock('Illuminate\Contracts\Events\Dispatcher');
        $events->shouldReceive('fire');

        $this->cart = new Cart(
            new SessionMock(),
            $events,
            'shopping',
            'SAMPLESESSIONKEY',
            require(__DIR__ . '/../helpers/configMock.php')
        );
    }

    public function tearDown()
    {
        m::close();
    }

    public function test_subtotal()
    {
        $this->fillCart();

        // add tax to subtotal
        $tax = new \Darryldecode\Cart\Tax(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '5',
        ));

        $this->cart->tax($tax);

        $this->assertEquals(192.49, $this->cart->getSubTotal());

        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals(192.49, $this->cart->getTotal());
    }

    public function test_total_without_tax()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be the same as subtotal
        $this->assertEquals(187.49, $this->cart->getTotal(), 'Cart should have a total of 187.49');
    }

    public function test_total_with_tax()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add tax
        $tax = new \Darryldecode\Cart\Tax(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.5%',
        ));

        $this->cart->tax($tax);

        // no changes in subtotal as the tax's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->cart->setDecimals(5);
        $this->assertEquals(210.92625, $this->cart->getTotal(), 'Cart should have a total of 210.92625');
    }

    public function test_total_with_multiple_taxes_added_scenario_one()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add tax
        $tax1 = new Tax(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Express Shipping $15',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '15',
        ));

        $this->cart->condition($tax1);
        $this->cart->condition($tax2);

        // no changes in subtotal as the condition's target added was for subtotal
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->cart->setDecimals(5);
        $this->assertEquals(225.92625, $this->cart->getTotal(), 'Cart should have a total of 225.92625');
    }

    public function test_total_with_multiple_taxes_added_scenario_two()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
        $tax = new Tax(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.5%',
        ));
        $condition = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'Express Shipping $15',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '-15',
        ));

        $this->cart->condition($condition);
        $this->cart->tax($tax);

        // no changes in subtotal as the condition's target added was for subtotal
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->cart->setDecimals(5);
        $this->assertEquals(194.05125, $this->cart->getTotal(), 'Cart should have a total of 195.92625');
    }

    public function test_total_with_multiple_taxes_added_scenario_three()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
        $tax1 = new Tax(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Express Shipping $15',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '15',
        ));

        $this->cart->tax($tax1);
        $this->cart->tax($tax2);

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->cart->setDecimals(5);
        $this->assertEquals(225.92625, $this->cart->getTotal(), 'Cart should have a total of 149.05375');
    }

    public function test_cart_multiple_taxes_can_be_added_once_by_array()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
        $tax1 = new Tax(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Express Shipping $15',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '15',
        ));

        $this->cart->tax([$tax1, $tax2]);

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->cart->setDecimals(5);
        $this->assertEquals(225.92625, $this->cart->getTotal(), 'Cart should have a total of 225.92625');
    }

    public function test_add_item_with_tax()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'tax',
            'value' => '5%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'taxes' => $tax1
        );

        $this->cart->add($item);

        $this->assertEquals(105, $this->cart->get(456)->getPriceSumWithConditions());
        $this->assertEquals(105, $this->cart->getSubTotal());
    }

    public function test_add_item_with_multiple_item_taxes_in_multiple_tax_instance()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'value' => '25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'conditions' => [$tax1, $tax2]
        );

        $this->cart->add($item);

        $this->assertEquals(130.00, $this->cart->get(456)->getPriceSumWithConditions(), 'Item subtotal with 1 item should be 80');
        $this->assertEquals(130.00, $this->cart->getSubTotal(), 'Cart subtotal with 1 item should be 80');
    }

    public function test_add_item_tax()
    {
        $tax1 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'value' => '25',
        ));
        $tax2 = new Tax(array(
            'name' => 'COUPON 101',
            'type' => 'coupon',
            'value' => '5%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'taxes' => [$tax1]
        );

        $this->cart->add($item);

        // let's prove first we have 1 taxes on this item
        $this->assertCount(1, $this->cart->get($item['id'])['taxes'], "Item should have 1 condition");

        // now let's insert a condition on an existing item on the cart
        $this->cart->addItemTax($item['id'], $tax2);

        $this->assertCount(2, $this->cart->get($item['id'])['taxes'], "Item should have 2 conditions");
    }

    public function test_get_cart_tax_by_tax_name()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->tax([$tax1, $tax2]);

        // get a condition applied on cart by condition name
        $tax = $this->cart->getTax($tax1->getName());

        $this->assertEquals($tax->getName(), 'SALE 5%');
        $this->assertEquals($tax->getTarget(), 'total');
        $this->assertEquals($tax->getType(), 'sale');
        $this->assertEquals($tax->getValue(), '5%');
    }

    public function test_remove_cart_tax_by_tax_name()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->tax([$tax1, $tax2]);

        // let's prove first we have now two conditions in the cart
        $this->assertEquals(2, $this->cart->getTaxes()->count(), 'Cart should have two conditions');

        // now let's remove a specific condition by condition name
        $this->cart->removeTax('SALE 5%');

        // cart should have now only 1 condition
        $this->assertEquals(1, $this->cart->getTaxes()->count(), 'Cart should have one condition');
        $this->assertEquals('Item Gift Pack 25.00', $this->cart->getTaxes()->first()->getName());
    }

    public function test_remove_item_tax_by_tax_name()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'value' => '25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'taxes' => [$tax1, $tax2]
        );

        $this->cart->add($item);

        // let's very first the item has 2 conditions in it
        $this->assertCount(2, $this->cart->get(456)['taxes'], 'Item should have two conditions');

        // now let's remove a condition on that item using the condition name
        $this->cart->removeItemTax(456, 'SALE 5%');

        // now we should have only 1 condition left on that item
        $this->assertCount(1, $this->cart->get(456)['taxes'], 'Item should have one condition left');
    }

    public function test_remove_item_tax_by_tax_name_scenario_two()
    {
        // NOTE: in this scenario, we will add the conditions not in array format

        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'value' => '5%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'taxes' => $tax1 // <--not in array format
        );

        $this->cart->add($item);

        // let's very first the item has 2 conditions in it
        $this->assertNotEmpty($this->cart->get(456)['taxes'], 'Item should have one condition in it.');

        // now let's remove a condition on that item using the condition name
        $this->cart->removeItemTax(456, 'SALE 5%');

        // now we should have only 1 condition left on that item
        $this->assertEmpty($this->cart->get(456)['taxes'], 'Item should have no condition now');
    }

    public function test_clear_item_taxes()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'value' => '25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'taxes' => [$tax1, $tax2]
        );

        $this->cart->add($item);

        // let's very first the item has 2 conditions in it
        $this->assertCount(2, $this->cart->get(456)['taxes'], 'Item should have two conditions');

        // now let's remove all condition on that item
        $this->cart->clearItemTaxes(456);

        // now we should have only 0 condition left on that item
        $this->assertCount(0, $this->cart->get(456)['taxes'], 'Item should have no conditions now');
    }

    public function test_clear_cart_taxes()
    {
        // NOTE:
        // This only clears all conditions that has been added in a cart bases
        // this does not remove conditions on per item bases

        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->tax([$tax1, $tax2]);

        // let's prove first we have now two conditions in the cart
        $this->assertEquals(2, $this->cart->getTaxes()->count(), 'Cart should have two conditions');

        // now let's clear cart conditions
        $this->cart->clearTaxes();

        // cart should have now only 1 condition
        $this->assertEquals(0, $this->cart->getTaxes()->count(), 'Cart should have no conditions now');
    }

    public function test_get_calculated_value_of_a_tax()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->tax([$tax1, $tax2]);

        $subTotal = $this->cart->getSubTotal();

        $this->assertEquals(100, $subTotal, 'Subtotal should be 100');

        // way 1
        // now we will get the calculated value of the condition 1
        $cond1 = $this->cart->getTax('SALE 5%');
        $this->assertEquals(5, $cond1->getCalculatedValue($subTotal), 'The calculated value must be 5');

        // way 2
        // get all cart conditions and get their calculated values
        $taxes = $this->cart->getTaxes();
        $this->assertEquals(5, $taxes['SALE 5%']->getCalculatedValue($subTotal), 'First condition calculated value must be 5');
        $this->assertEquals(25, $taxes['Item Gift Pack 25.00']->getCalculatedValue($subTotal), 'First condition calculated value must be 5');
    }

    public function test_get_tax_by_type()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '25',
        ));
        $tax3 = new Tax(array(
            'name' => 'Item Less 8%',
            'type' => 'promo',
            'target' => 'total',
            'value' => '8%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->tax([$tax1, $tax2, $tax3]);

        // now lets get all conditions added in the cart with the type "promo"
        $promoTaxes = $this->cart->getTaxesByType('promo');

        $this->assertEquals(2, $promoTaxes->count(), "We should have 2 items as promo condition type.");
    }

    public function test_remove_taxes_by_type()
    {
        // NOTE:
        // when add a new condition, the condition's name will be the key to be use
        // to access the condition. For some reasons, if the condition name contains
        // a "dot" on it ("."), for example adding a condition with name "SALE 35.00"
        // this will cause issues when removing this condition by name, this will not be removed
        // so when adding a condition, the condition name should not contain any "period" (.)
        // to avoid any issues removing it using remove method: removeCartCondition($conditionName);

        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 20',
            'type' => 'promo',
            'target' => 'total',
            'value' => '25',
        ));
        $tax3 = new Tax(array(
            'name' => 'Item Less 8%',
            'type' => 'promo',
            'target' => 'total',
            'value' => '8%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->tax([$tax1, $tax2, $tax3]);

        // now lets remove all conditions added in the cart with the type "promo"
        $this->cart->removeTaxesByType('promo');

        $this->assertEquals(1, $this->cart->getTaxes()->count(), "We should have 1 condition remaining as promo conditions type has been removed.");
    }

    public function test_add_cart_tax_without_tax_attributes()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%'
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->tax([$tax1]);

        // prove first we have now the condition on the cart
        $tax = $this->cart->getTax("SALE 5%");
        $this->assertEquals('SALE 5%', $tax->getName());

        // when get attribute is called and there is no attributes added,
        // it should return an empty array
        $taxAttribute = $tax->getAttributes();
        $this->assertInternalType('array', $taxAttribute);
    }

    public function test_add_cart_tax_with_tax_attributes()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
            'attributes' => array(
                'description' => 'october fest promo sale',
                'sale_start_date' => '2015-01-20',
                'sale_end_date' => '2015-01-30',
            )
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->tax([$tax1]);

        // prove first we have now the condition on the cart
        $tax = $this->cart->getTax("SALE 5%");
        $this->assertEquals('SALE 5%', $tax->getName());

        // when get attribute is called and there is no attributes added,
        // it should return an empty array
        $taxAttributes = $tax->getAttributes();
        $this->assertInternalType('array', $taxAttributes);
        $this->assertArrayHasKey('description', $taxAttributes);
        $this->assertArrayHasKey('sale_start_date', $taxAttributes);
        $this->assertArrayHasKey('sale_end_date', $taxAttributes);
        $this->assertEquals('october fest promo sale', $taxAttributes['description']);
        $this->assertEquals('2015-01-20', $taxAttributes['sale_start_date']);
        $this->assertEquals('2015-01-30', $taxAttributes['sale_end_date']);
    }

    public function test_get_order_from_tax()
    {
        $tax1 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
            'order' => 2
        ));
        $tax2 = new Tax(array(
            'name' => 'Item Gift Pack 20',
            'type' => 'promo',
            'target' => 'total',
            'value' => '25',
            'order' => '3'
        ));
        $tax3 = new Tax(array(
            'name' => 'Item Less 8%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '8%',
            'order' => 'first'
        ));

        $this->assertEquals(2, $tax1->getOrder());
        $this->assertEquals(3, $tax2->getOrder()); // numeric string is converted to integer
        $this->assertEquals(0, $tax3->getOrder()); // no numeric string is converted to 0

        $this->cart->tax($tax1);
        $this->cart->tax($tax2);
        $this->cart->tax($tax3);

        $taxes = $this->cart->getTaxes();

        $this->assertEquals('sale', $taxes->shift()->getType());
        $this->assertEquals('promo', $taxes->shift()->getType());
        $this->assertEquals('tax', $taxes->shift()->getType());
    }

    public function test_tax_ordering()
    {
        $tax1 = new Tax(array(
            'name' => 'TAX',
            'type' => 'tax',
            'target' => 'total',
            'value' => '8%',
            'order' => 5
        ));
        $tax2 = new Tax(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '5%',
            'order' => 2
        ));
        $tax3 = new Tax(array(
            'name' => 'Item Gift Pack 20',
            'type' => 'promo',
            'target' => 'total',
            'value' => '25',
            'order' => 1
        ));

        $this->fillCart();

        $this->cart->tax($tax1);
        $this->cart->tax($tax2);
        $this->cart->tax($tax3);

        $this->assertEquals('Item Gift Pack 20', $this->cart->getTaxes()->first()->getName());
        $this->assertEquals('TAX', $this->cart->getTaxes()->last()->getName());
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidTaxException
     */
    public function test_should_throw_exception_when_provided_negative_tax_value()
    {
        $tax1 = new Tax(array(
            'name' => 'TAX',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-2%',
            'order' => 5
        ));

        $this->cart->tax($tax1);
    }

    protected function fillCart()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => array()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => array()
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 1,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);
    }
}
