<?php
/**
 * Database Driven Simple & Easy to Manage Cart Class Library for Laravel 5+
 * @author Md. Ziyed Uddin <ziyed.cse@gmail.com>
 * @version Cart 1.0.1
 * @link Website https://ziyedbd.wordpress.com/
 * Release On: 09 Feb, 2017
 */

namespace App\Http\Libraries;

use DB;
use Session;

class Cart {

    /**
     * Constructor Will Set Default table name of database to manage cart: "order_data_temps"
     */
    function __construct() {
        Session::put('DbTableNameForSimpleCartClass', 'order_data_temps');
    }

    /**
     * Get Table,
     * Get Temporary Order Table Name to manage cart
     * @return string Order Temp Table Name
     */
    public function getTable() {
        return Session::get('DbTableNameForSimpleCartClass');
    }

    /**
     * Set Table,
     * Set the temporary Table Name to manage cart
     * @param string $table_name The Temp Order table name to use managing cart items
     * @return void 
     */
    public function setTable($table_name) {
        Session::put('DbTableNameForSimpleCartClass', $table_name);
    }

    /**
     * Get Items,
     * Return All Items Already Added To Cart
     * @return Array-object All Items Added to Cart of Current Active Session
     */
    public static function getItems() {
        $table_name = Session::get('DbTableNameForSimpleCartClass');
        $items = DB::table($table_name)
                ->select('*')
                ->where('session_id', Session::getId())                
                ->get();
        return $items;
    }

    /**
     * Insert, 
     * Insert An Item to Cart
     * @param array $data Information as array to save in the cart as new
     * @param int $customer_id Castomer Id (optional)
     * @return int/boolean id on success, FALSE on failure
     */
    public static function insert($data = array(), $customer_id = 0) {
        $table_name = Session::get('DbTableNameForSimpleCartClass');
        if (empty($data)) {
            return FALSE;
        }
        if (!isset($data['product_id']) || !isset($data['quantity']) || !isset($data['price']) || !isset($data['name'])) {
            //log_message('error', 'The cart array must contain a product ID, quantity, price, and name.');
            return FALSE;
        }
        if (empty($data['product_id']) || empty($data['quantity']) || empty($data['price']) || empty($data['name'])) {
            return FALSE;
        }
        if (!is_array($data) OR count($data) == 0) {
            //log_message('error', 'The insert method must be passed an array containing data.');
            return FALSE;
        }
        $data['quantity'] = trim(preg_replace('/([^0-9])/i', '', $data['quantity']));
        $data['quantity'] = trim(preg_replace('/(^[0]+)/i', '', $data['quantity']));
        if (!is_numeric($data['quantity']) || $data['quantity'] == 0) {
            return FALSE;
        }
        $product_id_rules = '\.a-z0-9_-';
        $product_name_rules = '\.\:\-_ a-z0-9';
        if (!preg_match("/^[" . $product_id_rules . "]+$/i", $data['product_id'])) {
            //log_message('error', 'Invalid product ID.  The product ID can only contain alpha-numeric characters, dashes, and underscores');
            return FALSE;
        }
        if (!preg_match("/^[" . $product_name_rules . "]+$/i", $data['name'])) {
            //log_message('error', 'An invalid name was submitted as the product name: ' . $data['name'] . ' The name can only contain alpha-numeric characters, dashes, underscores, colons, and spaces');
            return FALSE;
        }
        $data['price'] = trim(preg_replace('/([^0-9\.])/i', '', $data['price']));
        $data['price'] = trim(preg_replace('/(^[0]+)/i', '', $data['price']));
        // Is the price a valid number?
        if (!is_numeric($data['price'])) {
            //log_message('error', 'An invalid price was submitted for product ID: ' . $data['id']);
            return FALSE;
        }

        //Making a unique row id for a entry to make it unique
        if (isset($data['options']) && count($data['options']) > 0) {
            $rowid = md5($data['product_id'] . implode('', $data['options']));
        } else {
            $rowid = md5($data['product_id']);
        }

        //Check Item is exist to cart already
        $check = DB::table($table_name)
                ->select('id')
                ->where('session_id', Session::getId())
                ->where('product_id', $data['product_id'])
                ->where('rowid', $rowid)
                ->get();
        
        if (count($check) > 0) {
            //Update Existing Product item in a cart                        
            $data_update = array(
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'name' => $data['name']
                //,'updated_at' => date('Y-m-d H:i:s')
            );
            DB::table($table_name)
                    ->where('id', $check[0]->id)
                    ->update($data_update);
            return $check[0]->id;
        } else {
            //Add New Product Item to cart
            $data_save = array(
                'rowid' => $rowid,
                'session_id' => Session::getId(),
                'product_id' => $data['product_id'],
                'customer_id' => $customer_id,
                'quantity' => $data['quantity'],
                'price' => $data['price'],
                'name' => $data['name'],
                'options' => (isset($data['options']) && !empty($data['options'])) ? serialize($data['options']) : ''
                //,'created_at' => date('Y-m-d H:i:s'),
                //'updated_at' => date('Y-m-d H:i:s')
            );
            $insertedId = DB::table($table_name)->insertGetId($data_save);
            return $insertedId;
        }
    }

    /**
     * Update,
     * Update An Item to Cart
     * @param array $data Information as array to update the cart item
     * @param string $rowId the unique id of each product item added to cart
     * @param int $customer_id Customer Id can be passed default 0
     * @return boolean TRUE on success, FALSE on failure
     */
    public static function update($data = array(), $rowId = null, $customer_id = 0) {
        $table_name = Session::get('DbTableNameForSimpleCartClass');
        $data_update_array = array();
        if (empty($data)) {
            return FALSE;
        }
        if (!is_array($data) OR count($data) == 0) {
            return FALSE;
        }
        //checking quantity and set to array if exist
        if (isset($data['quantity']) && !empty($data['quantity'])) {
            $data['quantity'] = trim(preg_replace('/([^0-9])/i', '', $data['quantity']));
            $data['quantity'] = trim(preg_replace('/(^[0]+)/i', '', $data['quantity']));
            if (!is_numeric($data['quantity']) || $data['quantity'] == 0) {
                return FALSE;
            }
            $data_update_array['quantity'] = $data['quantity'];
        }
        //Checking customer id and set to array if exist
        if ($customer_id > 0) {
            $data_update_array['customer_id'] = $customer_id;
        }
        //Checking product id and set to array if exist
        if (isset($data['product_id']) && !empty($data['product_id'])) {
            $product_id_rules = '\.a-z0-9_-';
            if (!preg_match("/^[" . $product_id_rules . "]+$/i", $data['product_id'])) {
                return FALSE;
            }
            $data_update_array['product_id'] = $data['product_id'];
        }
        //Checking name and set to array if exist
        if (isset($data['name']) && !empty($data['name'])) {
            $product_name_rules = '\.\:\-_ a-z0-9';
            if (!preg_match("/^[" . $product_name_rules . "]+$/i", $data['name'])) {
                return FALSE;
            }
            $data_update_array['name'] = $data['name'];
        }
        //Checking price and set to array if exist
        if (isset($data['price']) && !empty($data['price'])) {
            $data['price'] = trim(preg_replace('/([^0-9\.])/i', '', $data['price']));
            $data['price'] = trim(preg_replace('/(^[0]+)/i', '', $data['price']));
            // Is the price a valid number?
            if (!is_numeric($data['price'])) {
                return FALSE;
            }
            $data_update_array['price'] = $data['price'];
        }
        //Checking options and set to array if exist
        if (isset($data['options']) && count($data['options']) > 0) {
            $data_update_array['options'] = serialize($data['options']);
        } 

        if(!empty($data_update_array) && count($data_update_array) > 0){
            //$data_update_array['updated_at'] = date('Y-m-d H:i:s');
            DB::table($table_name)
                    ->where('rowid', $rowId)
                    ->update($data_update_array);
            return TRUE;
        }else{
            return FALSE;
        }
    }

    /**
     * Remove Item,
     * Delete an Item from the Cart by unique rowid, rowid is not a primary key rather it is a unique composite key
     * @param string $rowid the unique id of the cart item
     * @return void
     */
    public static function removeItem($rowid) {
        $table_name = Session::get('DbTableNameForSimpleCartClass');
        DB::table($table_name)
                ->where('session_id', '=', Session::getId())
                ->where('rowid', '=', $rowid)
                ->delete();
    }

    /**
     * Remove All Items,
     * Delete All Items from the Cart of Current Session
     * @return void
     */
    public static function removeAllItems() {
        $table_name = Session::get('DbTableNameForSimpleCartClass');
        DB::table($table_name)
                ->where('session_id', '=', Session::getId())
                ->delete();
    }

    /**
     * Total Items
     * @return int Return Total Number of Items at the Cart
     */
    public static function total_items() {
        $table_name = Session::get('DbTableNameForSimpleCartClass');
        $total = 0;
        $data = DB::table($table_name)
                ->select('quantity')
                ->where('session_id', Session::getId())
                ->get();
        foreach ($data as $k => $item) {
            $total += $item->quantity;
        }
        return $total;
    }

    /**
     * Total
     * @return float Return Total Amount of Cart Items
     */
    public static function total() {
        $table_name = Session::get('DbTableNameForSimpleCartClass');
        $total = 0;
        $data = DB::table($table_name)
                ->select('quantity', 'price')
                ->where('session_id', Session::getId())
                ->get();
        foreach ($data as $k => $item) {
            $total += ($item->price * $item->quantity);
        }
        return $total;
    }

    /**
     * Format Number,
     * Returns the supplied number with commas and a decimal point.
     * @param float $n 
     * @return	integer
     */
    function format_number($n = '') {
        if ($n == '') {
            return '';
        }
        // Remove anything that isn't a number or decimal point.
        $n = trim(preg_replace('/([^0-9\.])/i', '', $n));
        return number_format($n, 2, '.', ',');
    }

}
