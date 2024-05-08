<?php
/**
 * Profile edit and extra functionality for profiles.
 */
if ( !class_exists( 'AllAroundShortcodes' ) ) {
	class AllAroundShortcodes {

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
        
		
	}
}
$allaroundrules = new AllAroundShortcodes();