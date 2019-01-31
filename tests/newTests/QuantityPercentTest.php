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

class QuantityPercentTest extends PHPUnit\Framework\TestCase
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

    public function test_add_quantity_percent_as_base_property()
    {
        $items = array(
            array(
                'id' => 1,
                'name' => 'Website',
                'price' => 100,
                'quantity' => 1
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 50,
                'quantity_percent' => [
                    'percent' => 5,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(5, $this->cart->get(3)->getQuantityPercent()['percent']);
        $this->assertEquals(1, $this->cart->get(3)->getQuantityPercent()['from']);
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidItemException
     */
    public function test_cant_add_item_without_quantity_percent_and_without_quantity_and_without_quantities()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill'
            )
        );

        $this->cart->add($items);
    }

    public function test_quantity_can_be_auto_calculated_from_quantity_percent_of_item_on_add()
    {
        $items = array(
            array(
                'id' => 1,
                'name' => 'Website',
                'price' => 200,
                'quantity' => 10
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 100,
                'quantity_percent' => [
                    'percent' => 10,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(1, $this->cart->get(3)->get('quantity'));
    }

    public function test_quantity_can_be_auto_calculated_from_quantity_percent_on_update()
    {
        $items = array(
            array(
                'id' => 1,
                'name' => 'Website',
                'price' => 200,
                'quantity' => 10
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 100,
                'quantity_percent' => [
                    'percent' => 10,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(1, $this->cart->get(3)->get('quantity'));

        $this->cart->update(3, [
            'quantity_percent' => [
                'percent' => 50,
                'from' => 1
            ]
        ]);

        $this->assertEquals(5, $this->cart->get(3)->get('quantity'));
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidItemException
     */
    public function test_cant_add_quantity_percent_percent_as_not_positive_number()
    {
        $items = array(
            array(
                'id' => 1,
                'name' => 'Mandrill',
                'price' => 5,
                'quantity' => 1
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 1,
                'quantity_percent' => [
                    'percent' => -5,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidItemException
     */
    public function test_cant_add_quantity_percent_from_as_not_valid_reference()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 1,
                'quantity_percent' => [
                    'percent' => 5,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);
    }

    public function test_quantity_can_be_auto_calculated_from_quantity_percent_of_group_item_return_1()
    {
        $group = array(
            'id' => 1,
            'name' => 'development',
        );
        $this->cart->addGroup($group);

        $items = array(
            array(
                'id' => 2,
                'name' => 'Website',
                'price' => 250,
                'quantity' => 1,
                'group_id' => 1,
            ),
            array(
                'id' => 3,
                'name' => 'Website',
                'price' => 120,
                'quantity' => 2,
                'group_id' => 1
            ),
            array(
                'id' => 4,
                'name' => 'Mandrill',
                'price' => 1,
                'quantity_percent' => [
                    'percent' => 10,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(1, $this->cart->get(4)->get('price'));
    }
}