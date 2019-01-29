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

class DependentItemsTest extends PHPUnit\Framework\TestCase
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

    public function test_add_dependent_item()
    {
        $this->fillCart();

        $this->assertFalse($this->cart->isEmpty());
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidDependent
     */
    public function test_cant_add_dependent_none_exist_item()
    {
        $items = array(
            array(
                'id' => 2,
                'name' => 'Website',
                'price' => 212.5,
                'quantity' => 1
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantity' => 1,
                'attributes' => array(
                    'dependent_id' => 99
                )
            )
        );

        $this->cart->add($items);
    }

    protected function fillCart()
    {
        $items = array(
            array(
                'id' => 2,
                'name' => 'Website',
                'price' => 212.5,
                'quantity' => 1
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantity' => 1,
                'attributes' => array(
                    'dependent_id' => 2
                )
            )
        );

        $this->cart->add($items);
    }
}