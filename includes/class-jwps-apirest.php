<?php

define('JGB_WPSBSC_APIREST_BASE_ROUTE','jgb-wpsbsc');
define('JGB_WPSBSC_APIREST_VERSION','v1');
define('JGB_WPSBSC_APIREST_MAIN_ROUTE', JGB_WPSBSC_APIREST_BASE_ROUTE . '/' . JGB_WPSBSC_APIREST_VERSION);

define('JGB_WPSBSC_APIREST_ENPT_IMPORT_TD','/td-import');

class JWPSApiRest {

    function __construct()
    {
        
    }

    public function registerEndpoints(){
        register_rest_route(
            JGB_WPSBSC_APIREST_MAIN_ROUTE,
            JGB_WPSBSC_APIREST_ENPT_IMPORT_TD,
            [
                'methods' => 'POST',
                'callback' => [
                    $this,
                    'receive_decision_tree_data'
                ]
            ]
        );
    }

    public function receive_decision_tree_data( WP_REST_Request $r ){
        $response = [];

        return new WP_REST_Response( $response );
    }

}