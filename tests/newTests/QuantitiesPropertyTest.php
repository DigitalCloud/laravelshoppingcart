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

class QuantitiesPropertyTest extends PHPUnit\Framework\TestCase
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

    public function test_add_quantities_as_base_property()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantity' => 1,
                'quantities' => [
                    [
                        'quantity' => 5,
                        'unit' => 'account'
                    ]
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(5, $this->cart->get(3)->getQuantities()->first()['quantity']);
        $this->assertEquals('account', $this->cart->get(3)->getQuantities()->first()['unit']);
    }

    public function test_can_add_item_with_quantities_and_without_quantity()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantities' => [
                    [
                        'quantity' => 5,
                        'unit' => 'account'
                    ]
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
    }

    public function test_can_add_item_with_multible_quantities()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantities' => [
                    [
                        'quantity' => 5,
                        'unit' => 'account'
                    ],
                    [
                        'quantity' => 3,
                        'unit' => 'month'
                    ]
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(3, $this->cart->get(3)->getQuantities()->last()['quantity']);
        $this->assertEquals('month', $this->cart->get(3)->getQuantities()->last()['unit']);
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidItemException
     */
    public function test_cant_add_item_without_quantities_and_without_quantity()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99
            )
        );

        $this->cart->add($items);
    }

    public function test_quantity_can_be_auto_calculated_from_quantities_on_add()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantities' => [
                    [
                        'quantity' => 5,
                        'unit' => 'account'
                    ],
                    [
                        'quantity' => 3,
                        'unit' => 'month'
                    ]
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(15, $this->cart->get(3)->get('quantity'));
        $this->assertEquals('account/month', $this->cart->get(3)->get('unit'));
    }

    public function test_quantity_can_be_auto_calculated_from_quantities_on_update()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantities' => [
                    [
                        'quantity' => 5,
                        'unit' => 'account'
                    ],
                    [
                        'quantity' => 3,
                        'unit' => 'month'
                    ]
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(15, $this->cart->get(3)->get('quantity'));
        $this->assertEquals('account/month', $this->cart->get(3)->get('unit'));

        $this->cart->update(3,[
            'quantities' => [
                [
                    'quantity' => 2,
                    'unit' => 'service'
                ],
                [
                    'quantity' => 3,
                    'unit' => 'month'
                ]
            ]
        ]);

        $this->assertEquals(6, $this->cart->get(3)->get('quantity'));
        $this->assertEquals('service/month', $this->cart->get(3)->get('unit'));
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidItemException
     */
    public function test_cant_add_quantities_quantity_as_not_positive_number_or_zero()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantities' => [
                    [
                        'quantity' => -5,
                        'unit' => 'account'
                    ]
                ]
            )
        );

        $this->cart->add($items);
    }


}