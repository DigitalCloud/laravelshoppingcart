<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 3/18/2015
 * Time: 6:17 PM
 */

use Darryldecode\Cart\Cart;
use Mockery as m;
use Darryldecode\Cart\CartCondition;

require_once __DIR__ . '/../helpers/SessionMock.php';

class GroupTest extends PHPUnit\Framework\TestCase
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

    public function test_cart_can_add_group()
    {
        $this->cart->addGroup(455, 'Sample Item', array());

        $this->assertEquals(1, $this->cart->getContent()->count(), 'Cart content should be 1');
        $this->assertEquals(455, $this->cart->getContent()->first()['id'], 'Item added has ID of 455 so first content ID should be 455');
    }

    public function test_cart_is_empty_when_only_group_item_in()
    {
        $this->cart->addGroup(455, 'Sample Item', array());

        $this->assertTrue($this->cart->isEmpty(), 'Cart should be empty');
    }

    public function test_cart_can_add_group_items_as_array()
    {
        $item = array(
            'id' => 456,
            'name' => 'Sample Item',
            'attributes' => array()
        );

        $this->cart->addGroup($item);

        $this->assertTrue($this->cart->isEmpty(), 'Cart should be empty');
        $this->assertEquals(1, $this->cart->getContent()->count(), 'Cart should have 1 item on it');
        $this->assertEquals(456, $this->cart->getContent()->first()['id'], 'The first content must have ID of 456');
        $this->assertEquals('Sample Item', $this->cart->getContent()->first()['name'], 'The first content must have name of "Sample Item"');
    }

    public function test_cart_can_add_group_items_with_multidimensional_array()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'attributes' => array()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'attributes' => array()
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'attributes' => array()
            ),
        );

        $this->cart->addGroup($items);

        $this->assertTrue($this->cart->isEmpty(), 'Cart should be empty');
        $this->assertCount(3, $this->cart->getContent()->toArray(), 'Cart should have 3 items');
    }

    public function test_cart_update_existing_group_item()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'attributes' => array()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'attributes' => array()
            ),
        );

        $this->cart->addGroup($items);

        $itemIdToEvaluate = 456;

        $item = $this->cart->get($itemIdToEvaluate);
        $this->assertEquals('Sample Item 1', $item['name'], 'Item name should be "Sample Item 1"');

        // when cart's item quantity is updated, the subtotal should be updated as well
        $this->cart->update(456, array(
            'name' => 'Renamed'
        ));

        $item = $this->cart->get($itemIdToEvaluate);
        $this->assertEquals('Renamed', $item['name'], 'Item name should be "Renamed"');
    }

    public function test_cart_can_add_item_to_group()
    {
        $group = array(
            'id' => 1,
            'name' => 'Sample Item',
            'attributes' => array()
        );

        $this->cart->addGroup($group);

        $this->assertTrue($this->cart->isEmpty(), 'Cart should be empty');

        $item = array(
            'id' => 456,
            'name' => 'Sample Item',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(
                'group_id' => 1
            )
        );

        $this->cart->add($item);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should be not empty');
    }

    /**
     * @expectedException Darryldecode\Cart\Exceptions\InvalidGroup
     */
    public function test_cart_cant_add_item_to_invalid_group()
    {
        $item = array(
            'id' => 456,
            'name' => 'Sample Item',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(
                'group_id' => 1
            )
        );

        $this->cart->add($item);
    }

    public function test_group_sub_total()
    {
        $group = array(
            'id' => 1,
            'name' => 'Sample Group',
            'attributes' => array()
        );

        $this->cart->addGroup($group);

        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => array(
                    'group_id' => 1
                )
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
                'attributes' => array(
                    'group_id' => 1
                )
            ),
        );

        $this->cart->add($items);

        $this->assertEquals(118.24, $this->cart->getGroupSubTotal(1), 'Group should have sub total of 118.24');

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');
    }

    public function test_group_sub_total_when_sub_item_price_is_updated()
    {
        $group = array(
            'id' => 1,
            'name' => 'Sample Group',
            'attributes' => array()
        );

        $this->cart->addGroup($group);

        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => array(
                    'group_id' => 1
                )
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
                'attributes' => array(
                    'group_id' => 1
                )
            ),
        );

        $this->cart->add($items);

        $this->assertEquals(118.24, $this->cart->getGroupSubTotal(1), 'Group should have sub total of 118.24');
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        $this->cart->update(456, ['price' => 59.99]);

        $this->assertEquals(110.24, $this->cart->getGroupSubTotal(1), 'Group should have sub total of 110.24');
        $this->assertEquals(179.49, $this->cart->getSubTotal(), 'Cart should have sub total of 179.49');
    }

    public function test_group_sub_total_when_sub_item_quantity_is_updated()
    {
        $group = array(
            'id' => 1,
            'name' => 'Sample Group',
            'attributes' => array()
        );

        $this->cart->addGroup($group);

        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => array(
                    'group_id' => 1
                )
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
                'attributes' => array(
                    'group_id' => 1
                )
            ),
        );

        $this->cart->add($items);

        $this->assertEquals(118.24, $this->cart->getGroupSubTotal(1), 'Group should have sub total of 118.24');
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        $this->cart->update(456, ['quantity' => 1]);

        $this->assertEquals(186.23, $this->cart->getGroupSubTotal(1), 'Group should have sub total of 186.23');
        $this->assertEquals(255.48, $this->cart->getSubTotal(), 'Cart should have sub total of 255.48');
    }

    public function test_group_sub_total_when_new_sub_item_added_to_group()
    {
        $group = array(
            'id' => 1,
            'name' => 'Sample Group',
            'attributes' => array()
        );

        $this->cart->addGroup($group);

        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => array(
                    'group_id' => 1
                )
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 1,
                'attributes' => array(
                    'group_id' => 1
                )
            ),
        );

        $this->cart->add($items);

        $this->assertEquals(118.24, $this->cart->getGroupSubTotal(1), 'Group should have sub total of 118.24');

        $this->cart->add(array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => array(
                    'group_id' => 1
                )
            )
        );

        $this->assertEquals(187.49, $this->cart->getGroupSubTotal(1), 'Group should have sub total of 110.24');
    }


    public function test_cart_get_total_quantity()
    {
        $group = array(
            'id' => 1,
            'name' => 'Sample Group',
            'attributes' => array()
        );

        $this->cart->addGroup($group);

        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 3,
                'attributes' => array()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);

        // now let's count the cart's quantity
        $this->assertInternalType("int", $this->cart->getTotalQuantity(), 'Return type should be INT');
        $this->assertEquals(4, $this->cart->getTotalQuantity(), 'Cart\'s quantity should be 4.');
    }

    public function test_group_get_sum_price_using_property_return_0()
    {
        $this->cart->addGroup(455, 'Sample Item', array());

        $item = $this->cart->get(455);

        $this->assertEquals(0, $item->getPriceSum(), 'Item summed price should be 201.98');
    }
}