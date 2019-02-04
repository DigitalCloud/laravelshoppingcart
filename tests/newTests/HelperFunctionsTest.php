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

class HelperFunctionsTest extends PHPUnit\Framework\TestCase
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

    public function test_function_from_json()
    {
        $this->fillCart();
        $this->assertEquals(3, $this->cart->getContent()->count());
        $data = $this->cart->getContent()->toJson();

        $this->cart->clear();
        $this->assertEquals(0, $this->cart->getContent()->count());

        $this->cart->fromJson($data);

        $this->assertEquals(3, $this->cart->getContent()->count());
    }

    protected function fillCart()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 1
            ),
        );

        $this->cart->add($items);
    }
}
