<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/12/2015
 * Time: 9:59 PM
 */

use Darryldecode\Cart\Cart;
use Mockery as m;

require_once __DIR__ . '/../helpers/SessionMock.php';

class SimpleDiscountTest extends PHPUnit\Framework\TestCase
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

    public function test_subtotal_with_items_discount()
    {
        $this->fillCart();

        $this->assertEquals(501.75, $this->cart->getSubTotal()->get('value'));

        // add condition to subtotal
        $condition = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'Special Discount',
            'type' => 'discount',
            'value' => '-5',
        ));

        $this->cart->add([
            'id' => 4,
            'name' => 'Discounted service',
            'price' => 100,
            'quantity' => 1,
            'conditions' => $condition
        ]);

        $this->assertEquals(501.75 + (100 - 5), $this->cart->getSubTotal()->get('value'));

        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals(501.75 + (100 - 5), $this->cart->getTotal()->get('value'));
    }

    public function test_subtotal_with_discount_and_items_discount()
    {
        $this->fillCart();
        $manualComputedCartTotal = 501.75;

        $this->assertEquals($manualComputedCartTotal, $this->cart->getSubTotal()->get('value'));

        // add condition to subtotal
        $condition = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'Special Discount',
            'type' => 'discount',
            'value' => '-5',
        ));

        $this->cart->add([
            'id' => 4,
            'name' => 'Discounted service',
            'price' => 100,
            'quantity' => 1,
            'conditions' => $condition
        ]);

        $manualComputedCartTotalAfterNewItem = $manualComputedCartTotal + (100 - 5);
        $this->assertEquals($manualComputedCartTotalAfterNewItem, $this->cart->getSubTotal()->get('value'));

        $sub_condition = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'Total Discount',
            'type' => 'discount',
            'target' => 'subtotal',
            'value' => '-10%',
        ));

        $this->cart->condition($sub_condition);

        $this->assertEquals($manualComputedCartTotalAfterNewItem - (10 / 100 * $manualComputedCartTotalAfterNewItem), $this->cart->getSubTotal()->get('value'));
        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals($manualComputedCartTotalAfterNewItem - (10 / 100 * $manualComputedCartTotalAfterNewItem), $this->cart->getTotal()->get('value'));
    }

    public function test_total_with_discount_and_items_discount()
    {
        $this->fillCart();
        $manualComputedCartTotal = 501.75;

        $this->assertEquals($manualComputedCartTotal, $this->cart->getSubTotal()->get('value'));

        // add condition to subtotal
        $condition = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'Special Discount',
            'type' => 'discount',
            'value' => '-5',
        ));

        $this->cart->add([
            'id' => 4,
            'name' => 'Discounted service',
            'price' => 100,
            'quantity' => 1,
            'conditions' => $condition
        ]);

        $manualComputedCartTotalAfterNewItem = $manualComputedCartTotal + (100 - 5);

        $this->assertEquals($manualComputedCartTotalAfterNewItem, $this->cart->getSubTotal()->get('value'));
        $this->assertEquals($manualComputedCartTotalAfterNewItem, $this->cart->getTotal()->get('value'));

        $total_condition = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'Total Discount',
            'type' => 'discount',
            'target' => 'total',
            'value' => '-10%',
        ));

        $this->cart->condition($total_condition);

        $this->assertEquals($manualComputedCartTotalAfterNewItem, $this->cart->getSubTotal()->get('value'));
        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals($manualComputedCartTotalAfterNewItem - (10 / 100 * $manualComputedCartTotalAfterNewItem), $this->cart->getTotal()->get('value'));
    }

    protected function fillCart()
    {
        $items = array(
            array(
                'id' => 1,
                'name' => 'Website',
                'price' => 212.5,
                'quantity' => 1,
                'attributes' => array()
            ),
            array(
                'id' => 2,
                'name' => 'Marketing Campaign',
                'price' => 69.25,
                'quantity' => 2,
                'attributes' => array()
            ),
            array(
                'id' => 3,
                'name' => 'Mobile App',
                'price' => 50.25,
                'quantity' => 3,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);
    }
}