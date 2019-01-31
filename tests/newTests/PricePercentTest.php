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

class PricePercentTest extends PHPUnit\Framework\TestCase
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

    public function test_add_price_percent_as_base_property()
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
                'quantity' => 1,
                'price_percent' => [
                    'percent' => 5,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(5, $this->cart->get(3)->getPricePercent()['percent']);
        $this->assertEquals(1, $this->cart->get(3)->getPricePercent()['from']);
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidItemException
     */
    public function test_cant_add_item_without_price_percent_and_without_price()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'quantity' => 5
            )
        );

        $this->cart->add($items);
    }

    public function test_price_can_be_auto_calculated_from_price_percent_of_item_on_add()
    {
        $items = array(
            array(
                'id' => 1,
                'name' => 'Website',
                'price' => 200,
                'quantity' => 1
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'quantity' => 1,
                'price_percent' => [
                    'percent' => 10,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(20, $this->cart->get(3)->get('price'));
    }

    public function test_price_can_be_auto_calculated_from_price_percent_on_update()
    {
        $items = array(
            array(
                'id' => 1,
                'name' => 'Website',
                'price' => 200,
                'quantity' => 1
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'quantity' => 1,
                'price_percent' => [
                    'percent' => 10,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(20, $this->cart->get(3)->get('price'));

        $this->cart->update(3, [
            'price_percent' => [
                'percent' => 25,
                'from' => 1
            ]
        ]);

        $this->assertEquals(50, $this->cart->get(3)->get('price'));
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidItemException
     */
    public function test_cant_add_price_percent_price_as_not_positive_number()
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
                'quantity' => 1,
                'price_percent' => [
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
    public function test_cant_add_price_percent_from_as_not_valid_reference()
    {
        $items = array(
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'quantity' => 1,
                'price_percent' => [
                    'percent' => 5,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);
    }

    public function test_price_can_be_auto_calculated_from_price_percent_of_group_item_with_no_alternatives_or_options_on_add()
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
                'quantity' => 1,
                'price_percent' => [
                    'percent' => 10,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(49, $this->cart->get(4)->get('price'));
    }

    public function test_price_can_be_auto_calculated_from_price_percent_of_group_item_with_alternatives_or_options_on_add()
    {
        $group = array(
            'id' => 1,
            'name' => 'development',
        );
        $this->cart->addGroup($group);

        $alternative_id=uniqid();
        $items = array(
            array(
                'id' => 2,
                'name' => 'Website',
                'price' => 250,
                'quantity' => 1,
                'group_id' => 1,
                'alternative_id'=>$alternative_id
            ),
            array(
                'id' => 3,
                'name' => 'Website',
                'price' => 120,
                'quantity' => 2,
                'group_id' => 1,
                'alternative_id'=>$alternative_id
            ),
            array(
                'id' => 4,
                'name' => 'Mandrill',
                'quantity' => 1,
                'price_percent' => [
                    'percent' => 10,
                    'from' => 1
                ]
            )
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(0, $this->cart->get(4)->get('price'));
    }
}