<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Utilities class - singleton class
 *
 * @since 2.0
 */

class Alarnd_Utility {

	/**
	 * Private constructor so nobody else can instantiate it
	 *
	 */
	private function __construct() {

	}

    /**
	 * Call this method to get singleton
	 *
	 * @return singleton instance of Alarnd_Utility
	 */
	public static function instance() {

		static $instance = null;
		if ( is_null( $instance ) ) {
			$instance = new Alarnd_Utility();
		}

		return $instance;
	}

	public function logger( $message ) {
		if ( WP_DEBUG === true ) {
			if ( is_array( $message ) || is_object( $message ) ) {
				error_log( print_r( $message, true ) );
			} else {
				error_log( $message );
			}
		}
	}

    function get_prev($hash = array(), $key = '') {
        $keys = array_keys($hash);
        $found_index = array_search($key, $keys);
        if ($found_index === false || $found_index === 0)
            return false;
        return $hash[$keys[$found_index-1]];
    }

    public function remap_setps( $discount_steps ) {
        $remap_steps = [];
        $i = 0;
        $len = count($discount_steps);
        foreach( $discount_steps as $key => $step ) {
            $prev_item = $this->get_prev( $discount_steps, $key );
            $remap_steps[$key]['quantity'] = $step['quantity'];
            $remap_steps[$key]['amount'] = $step['amount'];
            if( ! empty( $prev_item ) ) {
                $remap_steps[$key]['min'] = $prev_item['quantity'];
            }
            if ($i == $len - 1) {
                $remap_steps[$key]['last'] = true;
            }
            $i++;
        }

        return $remap_steps;
    }

    public function get_the_key( $remap_steps, $totalqty ) {
        $filtered = array_filter($remap_steps, function($step) use ($totalqty) : int {
            if( isset( $step['min'] ) && isset( $step['last'] ) ) {
                return ( $step['quantity'] >= $totalqty && $step['min'] < $totalqty ) || $step['min'] <= $totalqty;
            } elseif( isset( $step['min'] ) ) {
                return $step['quantity'] >= $totalqty && $step['min'] < $totalqty;
            }
            
            return $step['quantity'] >= $totalqty && 1 <= $totalqty;
        });
        
        $keys = array_keys($filtered);
        return $keys[0];
    }
    public function get_all_keys( $remap_steps, $totalqty ) {
        
        $filtered = [];
        foreach( $remap_steps as $key => $step ) {
            if($step['quantity'] <= $totalqty){
                $filtered[] = $step['amount'];
            }
        }

        end($filtered); 
        $key = key($filtered);
        return $key;
    }

    public function get_final_amount( $product_id, $total_qty, $regular_price ) {
        if( empty( $total_qty ) )
            return $regular_price;

        $discount_steps = get_field( 'discount_steps', $product_id );
        if( empty( $discount_steps ) )
            return $regular_price;

        $remap_steps = $this->remap_setps( $discount_steps );
        $the_key = $this->get_the_key( $remap_steps, $total_qty );
        if( ! isset( $discount_steps[$the_key] ) )
            return $regular_price;

        if( empty( $discount_steps[$the_key]['amount'] ) || 0 ==  $discount_steps[$the_key]['amount'] )
            return $regular_price;

        return $discount_steps[$the_key]['amount'];
    }
    
    public function get_custom_amount( $product_id, $total_qty, $regular_price ) {
        if( empty( $total_qty ) )
            return $regular_price;

        $discount_steps = get_field( 'quantity_steps', $product_id );
        if( empty( $discount_steps ) )
            return $regular_price;

        $the_key = $this->get_all_keys( $discount_steps, $total_qty );
        if( ! isset( $discount_steps[$the_key] ) )
            return $regular_price;

        if( empty( $discount_steps[$the_key]['amount'] ) || 0 ==  $discount_steps[$the_key]['amount'] )
            return $regular_price;

        return $discount_steps[$the_key]['amount'];
    }

    public function qty_price( $product_id, $item ) {

        $product    = wc_get_product( $product_id );

        $steps = get_field( 'quantity_steps', $product_id );
        $sp_quanity = get_field( 'custom_quanity', $product_id );

        if( isset( $item['alarnd_custom_quantity'] ) && ! empty( $item['alarnd_custom_quantity'] ) ) {
            return (int) $item['alarnd_step_key'];
        }

        if( isset( $steps[$item['alarnd_step_key']] ) ) {
            return (int) $steps[$item['alarnd_step_key']]['price'];
        }

        return $product->get_regular_price();
    }
	
}