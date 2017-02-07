# LaravelShoppingCart
A Simple Session Based Database Driven Shopping Cart Library For Laravel.
 
It's Really Simple & Easy To Manage Your Shopping Cart Item At Laravel

## Installation

Download The Class Library and put it to app/Http/Libraries folder

Now Load the Cart Class To you base controller
	
	use App\Http\Libraries\Cart;
	
Now you're ready to start using the shoppingcart in your application.

## Usage

Add the table to your database,
	
	database/order_data_temps.sql
	
The shopping cart gives you the following methods to use:

You Need to Create an Object Of the Cart Class Then

	$cart = new \App\Http\Libraries\Cart();
	
### $cart->insert()

Adding an item to the cart is really simple, you just use the `insert()` method, which accepts a variety of parameters.

In its most basic form you can specify the id, name, quantity, price of the product you'd like to add to the cart.

```php
$data = array(
	'product_id' => 4,
	'quantity' => 5,
	'price' => 258,
	'name' => 'child dress'
);  
$cart->insert($data);
```

As an optional fifth parameter you can pass others items information as options, so you can add multiple items with the same id, but with a different size or color etc.

```php
$data = array(
	'product_id' => 4,
	'quantity' => 5,
	'price' => 258,
	'name' => 'child dress',
	'options' => ['size'=> 'L', 'type' => 'Silk', 'Manufacturer' => 'Cat\'s Eye']
);  
$cart->insert($data);
```


### $cart->update()

To update an item in the cart, you'll first need the rowId of the item.
Next you can use the `update()` method to update it.

If you want to update any item then pass it to array and it will replace, you'll pass the update method the rowId and the new info of product:

```php
$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
$data = array(	
	'quantity' => 5,	
	'name' => 'child dress',
	'options' => ['size'=> 'L', 'type' => 'Silk', 'Manufacturer' => 'Cat\'s Eye']	
);  
$cart->update(rowId, $data);
```

### $cart->removeItem()

To remove an item from the cart, you'll again need the rowId. This rowId you simply pass to the `removeItem()` method and it will remove the item from the cart.

```php
$rowId = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';

$cart->removeItem($rowId);
```

### $cart->getItems()

YYou just class the `getItems()` method to get all cart items

```php
$cart->getItems();
```


### $cart->removeAllItems()

If you want to completely remove the content of a cart, you can call the removeAllItems method on the cart. This will remove all CartItems from the cart.

```php
$cart->removeAllItems();
```

### $cart->total()

The `total()` method can be used to get the calculated total of all items in the cart, based on there price and quantity.

```php
$cart->total();
```

### $cart->total_items()

If you want to know how many items there are in your cart, you can use the `total_items()` method. This method will return the total number of items in the cart. So if you've added 2 Pens and 1 umbrella, it will return 3 items.

```php
$cart->total_items()
```