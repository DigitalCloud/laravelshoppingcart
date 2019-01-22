<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/12/2015
 * Time: 9:59 PM
 */

use Darryldecode\Cart\Cart;
use Darryldecode\Cart\CartCondition;
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

        $this->assertEquals(501.75, $this->cart->getSubTotal());
        // add condition to subtotal
        $condition = new CartCondition(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '12.5%',
        ));

        $this->cart->condition($condition);

        $this->assertEquals(501.75 + (12.5 / 100 * 501.75), $this->cart->getSubTotal());

        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals(501.75 + (12.5 / 100 * 501.75), $this->cart->getTotal());
    }

    public function test_subtotal_with_items_condition()
    {
        $this->fillCart();

        $this->assertEquals(501.75, $this->cart->getSubTotal());

        // add condition to subtotal
        $condition = new CartCondition(array(
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

        $this->assertEquals(501.75 + (100 - 5), $this->cart->getSubTotal());

        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals(501.75 + (100 - 5), $this->cart->getTotal());
    }

    public function test_subtotal_with_condition_and_items_condition()
    {
        $this->fillCart();
        $manualComputedCartTotal = 501.75;

        $this->assertEquals($manualComputedCartTotal, $this->cart->getSubTotal());

        // add condition to subtotal
        $condition = new CartCondition(array(
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
        $this->assertEquals($manualComputedCartTotalAfterNewItem, $this->cart->getSubTotal());

        $sub_condition = new CartCondition(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '12.5%',
        ));

        $this->cart->condition($sub_condition);

        $this->assertEquals($manualComputedCartTotalAfterNewItem + (12.5 / 100 * $manualComputedCartTotalAfterNewItem), $this->cart->getSubTotal());
        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals($manualComputedCartTotalAfterNewItem + (12.5 / 100 * $manualComputedCartTotalAfterNewItem), $this->cart->getTotal());
    }

    public function test_total_with_condition_and_items_condition()
    {
        $this->fillCart();
        $manualComputedCartTotal = 501.75;

        $this->assertEquals($manualComputedCartTotal, $this->cart->getSubTotal());

        // add condition to subtotal
        $condition = new CartCondition(array(
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

        $this->assertEquals($manualComputedCartTotalAfterNewItem, $this->cart->getSubTotal());
        $this->assertEquals($manualComputedCartTotalAfterNewItem, $this->cart->getTotal());

        $total_condition = new CartCondition(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.5%',
        ));

        $this->cart->condition($total_condition);

        $this->assertEquals($manualComputedCartTotalAfterNewItem , $this->cart->getSubTotal());
        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals($manualComputedCartTotalAfterNewItem + (12.5 / 100 * $manualComputedCartTotalAfterNewItem), $this->cart->getTotal());
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
