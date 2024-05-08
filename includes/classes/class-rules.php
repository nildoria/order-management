<?php
/**
 * Profile edit and extra functionality for profiles.
 */
if ( !class_exists( 'AllAroundRules' ) ) {
	class AllAroundRules {

		/**
         * Start up
         */
        public function __construct()
        {
            add_action( 'init', array( $this, 'rewrite_rule' ) );
            add_filter( 'query_vars', array( $this, 'add_vars' ) );
		}

        public function add_vars($vars)
        {
			$vars[] = 'configure';
    		return $vars;
        }
        
        function rewrite_rule()
        {   
            add_rewrite_endpoint( 'configure', EP_PERMALINK );

            // username edit virtual page rewrite rules
            // add_rewrite_rule( 'u/([^/]+)/edit/page/([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&edit=1&paged=$matches[2]', 'top' );
            // add_rewrite_rule( 'u/([^/]+)/payout/page/([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&payout=1&paged=$matches[2]', 'top' );
            // add_rewrite_rule( 'u/([^/]+)/orders/page/([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&orders=1&paged=$matches[2]', 'top' );

            // //If you don't want the last trailing slash change the las /?$ to just $
            // add_rewrite_rule( 'u/([^/]+)/edit/?$', 'index.php?author_name=$matches[1]&edit=1', 'top' );
            // add_rewrite_rule( 'u/([^/]+)/payout/?$', 'index.php?author_name=$matches[1]&payout=1', 'top' );
            // add_rewrite_rule( 'u/([^/]+)/orders/?$', 'index.php?author_name=$matches[1]&orders=1', 'top' );

            // order update virtual page
            add_rewrite_rule( 'product/([^/]+)/configure/?$', 'index.php?post_type=product&p=$matches[1]&configure=1', 'top' );

            // order postname to id
            // add_rewrite_rule( 'order/([0-9]+)?$', 'index.php?post_type=qcorder&p=$matches[1]', 'top' );

        }
		
	}
}
$allaroundrules = new AllAroundRules();