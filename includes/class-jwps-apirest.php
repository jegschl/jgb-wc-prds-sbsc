<?php

define('JGB_WPSBSC_APIREST_BASE_ROUTE','jgb-wpsbsc');
define('JGB_WPSBSC_APIREST_VERSION','v1');
define('JGB_WPSBSC_APIREST_MAIN_ROUTE', JGB_WPSBSC_APIREST_BASE_ROUTE . '/' . JGB_WPSBSC_APIREST_VERSION);

define('JGB_WPSBSC_APIREST_ENPT_IMPORT_TD','/td-import');

define('JGB_WPSBSC_APIREST_RESPONSE_ERR_IMPORT_TD_PARSER_EMPTY',1);

class JWPSAdminApiRest {

    protected JGBWPSChoiceTreeImportParser $ctImporter;

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

    function set_cti_parser( JGBWPSChoiceTreeImportParser $importer = null ){
        if(!is_null( $importer )) { 
            $this->ctImporter = $importer;
        } else {
            $this->ctImporter = new JGBWPSChoiceTreeImportParser(); //se le podría ingresar argumentos aquí.
        }
    }

    public function receive_decision_tree_data( WP_REST_Request $r ){
        
        $response = [ 'error' => ['status' => false, 'code' => 0 ] ];

        $data = $r->get_json_params();

        $response[ 'data' ] = $data;

        if( !isset( $this->ctImporter ) ){

            $response[ 'error' ] [ 'status'  ] = true ;

            $response[ 'error' ] [ 'code' ] = JGB_WPSBSC_APIREST_RESPONSE_ERR_IMPORT_TD_PARSER_EMPTY;
           
            return new WP_REST_Response( $response );
        }

        $this->ctImporter->set_post_id( $data['postId'] );
        
        $response['parsingProcessResult'] = $this->ctImporter->process_input( $data['data'] );
        
        return new WP_REST_Response( $response );
    }

}