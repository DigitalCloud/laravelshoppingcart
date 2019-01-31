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

class OptionalItemsTest extends PHPUnit\Framework\TestCase
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

    public function test_add_optional_item()
    {
        $this->fillCart();

        $this->assertFalse($this->cart->isEmpty());
    }

    public function test_cart_total_and_subtotal_and_group_sub_total_when_all_items_have_fixed_price()
    {
        $group = array(
            'id' => 1,
            'name' => 'Group'
        );
        $this->cart->addGroup($group);

        $items = array(
            'id' => 3,
            'name' => 'Mandrill',
            'price' => 124.99,
            'quantity' => 1,
            'group_id' => 1
        );

        $this->cart->add($items);

        $expectedResult = collect([
            'value' => 124.99,
            'max' => 124.99,
            'min' => 124.99
        ]);

        $this->assertEquals($expectedResult, $this->cart->getGroupSubTotal(1));
        $this->assertEquals($expectedResult, $this->cart->getSubTotal());
        $this->assertEquals($expectedResult, $this->cart->getTotal());
    }

    public function test_cart_total_and_subtotal_and_group_sub_total_when_optional_item_exist_on_cart_scenario_1()
    {
        $group = array(
            'id' => 1,
            'name' => 'Group'
        );
        $this->cart->addGroup($group);

        $items =
            array(
                array(
                    'id' => 3,
                    'name' => 'Website',
                    'price' => 124.99,
                    'quantity' => 1,
                    'group_id' => 1
                ),
                array(
                    'id' => 2,
                    'name' => 'Mandrill',
                    'price' => 79.12,
                    'quantity' => 1,
                    'group_id' => 1,
                    'is_optional' => true
                )
            );

        $this->cart->add($items);

        $expectedResult = collect([
            'value' => null,
            'max' => 204.11,
            'min' => 124.99
        ]);

        $this->assertEquals($expectedResult, $this->cart->getGroupSubTotal(1));
        $this->assertEquals($expectedResult, $this->cart->getSubTotal());
        $this->assertEquals($expectedResult, $this->cart->getTotal());
    }

    public function test_cart_total_and_subtotal_and_group_sub_total_when_optional_item_exist_on_cart_scenario_2()
    {
        $group = array(
            'id' => 1,
            'name' => 'Group'
        );
        $this->cart->addGroup($group);

        $items =
            array(
                array(
                    'id' => 3,
                    'name' => 'Website',
                    'price' => 124.99,
                    'quantity' => 1,
                    'group_id' => 1
                ),
                array(
                    'id' => 2,
                    'name' => 'Mandrill',
                    'price' => 79.12,
                    'quantity' => 1,
                    'group_id' => 1,
                    'is_optional' => true
                ),
                array(
                    'id' => 4,
                    'name' => 'backup',
                    'price' => 51,
                    'quantity' => 1,
                    'group_id' => 1,
                    'is_optional' => true
                )
            );

        $this->cart->add($items);

        $expectedResult = collect([
            'value' => null,
            'max' => 255.11,
            'min' => 124.99
        ]);

        $this->assertEquals($expectedResult, $this->cart->getGroupSubTotal(1));
        $this->assertEquals($expectedResult, $this->cart->getSubTotal());
        $this->assertEquals($expectedResult, $this->cart->getTotal());
    }

    public function test_cart_total_and_subtotal_and_group_sub_total_when_optional_item_exist_on_cart_scenario_3()
    {
        $group = array(
            'id' => 1,
            'name' => 'Group'
        );
        $this->cart->addGroup($group);

        $items =
            array(
                array(
                    'id' => 3,
                    'name' => 'Website',
                    'price' => 124.99,
                    'quantity' => 1,
                    'group_id' => 1
                ),
                array(
                    'id' => 2,
                    'name' => 'Mandrill',
                    'price' => 79.12,
                    'quantity' => 1,
                    'group_id' => 1,
                    'is_optional' => true
                ),
                array(
                    'id' => 4,
                    'name' => 'backup',
                    'price' => 51,
                    'quantity' => 1,
                    'group_id' => 1,
                    'is_optional' => true,
                    'conditions' => new \Darryldecode\Cart\CartCondition([
                        'name' => 'discount',
                        'value' => '-5',
                        'type' => 'discount'
                    ])
                )
            );

        $this->cart->add($items);

        $expectedResult = collect([
            'value' => null,
            'max' => 250.11,
            'min' => 124.99
        ]);

        $this->assertEquals($expectedResult, $this->cart->getGroupSubTotal(1));
        $this->assertEquals($expectedResult, $this->cart->getSubTotal());
        $this->assertEquals($expectedResult, $this->cart->getTotal());
    }

    public function test_cart_total_and_subtotal_and_group_sub_total_when_optional_item_exist_on_cart_scenario_4()
    {
        $group = array(
            'id' => 1,
            'name' => 'Group'
        );
        $this->cart->addGroup($group);

        $items =
            array(
                array(
                    'id' => 3,
                    'name' => 'Website',
                    'price' => 124.99,
                    'quantity' => 1,
                    'group_id' => 1
                ),
                array(
                    'id' => 2,
                    'name' => 'Mandrill',
                    'price' => 79.12,
                    'quantity' => 1,
                    'group_id' => 1,
                    'is_optional' => true
                ),
                array(
                    'id' => 4,
                    'name' => 'backup',
                    'price' => 51,
                    'quantity' => 1,
                    'group_id' => 1,
                    'is_optional' => true,
                    'conditions' => array(
                        new \Darryldecode\Cart\CartCondition([
                            'name' => 'discount',
                            'value' => '-5',
                            'type' => 'discount'
                        ]),
                        new \Darryldecode\Cart\Tax([
                            'name' => 'tax',
                            'value' => '1.5%',
                            'type' => 'tax'
                        ])
                    )
                )
            );

        $this->cart->add($items);

        $expectedResult = collect([
            'value' => null,
            'max' => 250.8,
            'min' => 124.99
        ]);

        $this->assertEquals($expectedResult, $this->cart->getGroupSubTotal(1));
        $this->assertEquals($expectedResult, $this->cart->getSubTotal());
        $this->assertEquals($expectedResult, $this->cart->getTotal());
    }

    public function test_cart_total_and_subtotal_and_group_sub_total_when_optional_item_exist_on_cart_scenario_5()
    {
        $group = array(
            'id' => 1,
            'name' => 'Group'
        );
        $this->cart->addGroup($group);

        $items =
            array(
                array(
                    'id' => 3,
                    'name' => 'Website',
                    'price' => 124.99,
                    'quantity' => 2,
                    'group_id' => 1,
                    'conditions' => array(
                        new \Darryldecode\Cart\CartCondition([
                            'name' => 'discount',
                            'value' => '-7.5',
                            'type' => 'discount'
                        ])
                    )
                ),
                array(
                    'id' => 2,
                    'name' => 'Mandrill',
                    'price' => 79.12,
                    'quantity' => 1,
                    'group_id' => 1,
                    'is_optional' => true
                ),
                array(
                    'id' => 4,
                    'name' => 'backup',
                    'price' => 51,
                    'quantity' => 3,
                    'group_id' => 1,
                    'is_optional' => true,
                    'conditions' => array(
                        new \Darryldecode\Cart\CartCondition([
                            'name' => 'discount',
                            'value' => '-5',
                            'type' => 'discount'
                        ]),
                        new \Darryldecode\Cart\Tax([
                            'name' => 'tax',
                            'value' => '1.5%',
                            'type' => 'tax'
                        ])
                    )
                )
            );

        $this->cart->add($items);
        $this->cart->condition([
            new \Darryldecode\Cart\CartCondition([
                'name' => 'discount',
                'value' => '-12',
                'type' => 'discount',
                'target' => 'subtotal'
            ])
        ]);

        $this->cart->tax([
            new \Darryldecode\Cart\Tax([
                'name' => 'tax',
                'value' => '12.5%',
                'type' => 'tax',
                'target' => 'total'
            ])
        ]);

        $this->assertEquals(collect([
            'value' => null,
            'max' => 454.17,
            'min' => 234.98
        ]), $this->cart->getGroupSubTotal(1));

        $this->assertEquals(collect([
            'value' => null,
            'max' => 442.17,
            'min' => 222.98
        ]), $this->cart->getSubTotal());

        $this->assertEquals(collect([
            'value' => null,
            'max' => 497.44125,
            'min' => 250.8525
        ]), $this->cart->getTotal());
    }

    protected function fillCart()
    {
        $group = array(
            'id' => 1,
            'name' => 'Group'
        );

        $this->cart->addGroup($group);

        $items = array(
            array(
                'id' => 2,
                'name' => 'Website',
                'price' => 212.5,
                'quantity' => 1,
                "is_optional" => true
            ),
            array(
                'id' => 3,
                'name' => 'Mandrill',
                'price' => 124.99,
                'quantity' => 1
            )
        );

        $this->cart->add($items);
    }
}