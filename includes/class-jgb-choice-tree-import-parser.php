<?php

define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_OK',0);
define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_PARAMETER_STRING_INVALID',1);
define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_SUB_PARAM_STRING_INVALID',2);

define('JGB_WPS_CHCTREE_DATA_COL_PARSING_OK',0);
define('JGB_WPS_CHCTREE_DATA_COL_PARSING_EMPTY',1);

define('JGB_WPSBSC_ITEMS_DATA_TYPES', ['INT', 'DECIMAL', 'VARCHR'] );

class JGBWPSChoiceTreeImportParser{

    protected $linesCount;

    protected $currentLine;

    protected $currentSoC; //Step or column.

    protected $allowedFldParameters;

    protected $allowedFldSubParameters;

    protected $currentReadingParameter;

    protected $currentReadingSubParameter;

    protected $processedFields;

    protected $valuesCombinationSets;

    protected $vcsInProcess;

    protected $currentValueSlugInVTM;

    protected $previousValueSlugRoSNotMultiple; // Store last value slug in the same reading row.
    
    protected $previousFieldSlugRoSNotMultiple; // Store last field slug in the same reading row.

    protected $postId;

    protected $parentFVPath;
    
    function __construct( $data = null )
    {
        $this->setup();

        if( !is_null( $data ) && is_array( $data ) ){

            

            $this->process_input( $data );
        }
    }

    private function set_value_def_type_parsers_hooks(){
        $hooks_array = [
            'data' => [
                'callback' => [$this,'process_col_value_type_data'],
                'priority' => 30
            ],
            'radio' => [
                'callback' => [$this,'process_col_value_type_radio'],
                'priority' => 30
            ]
        ];

        $hooks_array = apply_filters( 'JGB/wpsbsc/import/col_data_type_parsers_hooks', $hooks_array );

        foreach( $hooks_array as $hook_sufix => $hcb ){
            add_filter('JGB/wpsbsc/choice_tree_import_value_type/' . $hook_sufix, $hcb['callback'], $hcb['priority'], 5 );
        }
    }

    private function set_items_parsers_hooks(){
        $hooks_array = [
            'field:label' => [
                'callback' => [$this,'process_dt_col_fld_lbl'],
                'priority' => 30
            ],
            'field:values-combination-set' => [
                'callback' => [$this,'process_dt_col_fld_vcs'],
                'priority' => 30
            ],
            'field:value-def' => [
                'callback' => [$this,'process_dt_col_fld_def'],
                'priority' => 30
            ],
            'values' => [
                'callback' => [$this,'process_dt_col_fld_mvals'],
                'priority' => 30
            ]

        ];

        $hooks_array = apply_filters( 'jgb/wpsbsc/import/items_parsers_hooks', $hooks_array );

        foreach( $hooks_array as $hook_sufix => $hcb ){
            add_filter('JGB/wpsbsc/choice_tree_import_item/' . $hook_sufix, $hcb['callback'], $hcb['priority'], 5 );
        }
    }

    private function set_fields_storer_by_type_hooks(){
        
        $hooks_array = [
            
            'radio' => [
                'callback' => [$this,'sd_storer_type_radio'],
                'priority' => 30
            ]

        ];

        $hooks_array = apply_filters( 'jgb/wpsbsc/import/fields_by_type_storer_hooks', $hooks_array );

        foreach( $hooks_array as $hook_sufix => $hcb ){
            add_filter('JGB/wpsbsc/store_field_data/type_' . $hook_sufix, $hcb['callback'], $hcb['priority'], 2 );
        }
    }

    private function setup(){

        $this->allowedFldParameters = $this->get_allowed_parameters();

        $this->set_items_parsers_hooks();

        $this->set_value_def_type_parsers_hooks();

        $this->set_fields_storer_by_type_hooks();

        $this->valuesCombinationSets = [];
    }

    function get_allowed_parameters(){
        return [
            'field',
            'values'
        ];
    }

    function get_allowed_sub_parameters( $parameter ){
        switch( $parameter){
            case 'field':
                return [
                    'label',
                    'values-combination-set',
                    'value-def'
                ];
                break;

            case 'values':
                return [];
                break;

            default:
                return JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_PARAMETER_STRING_INVALID;
        }
        
    }

    function set_post_id( $pid ){
        
        if( empty( $pid ) ){
            throw new Exception("Invalid post Id", 1);
            return 1;
        }

        if( !( is_string( $pid ) || is_int( $pid ) ) ){
            throw new Exception("Type Invalid for post Id", 2);
            return 2;
        }

        if( is_string( $pid )  &&  !is_numeric( $pid ) ){
            throw new Exception("Post Id string is not number", 3);
            return 3;
        }

        $this->postId = $pid;

        return 0;
    }

    function process_input( $data ){
        
        $this->linesCount = count( $data );
        
        foreach( $data as $this->currentLine => $fld_inf_reg ){

            if( is_array($fld_inf_reg) && !empty( $fld_inf_reg ) && ($this->parse_first_column( $fld_inf_reg[0] ) == JGB_WPS_CHCTREE_FIRST_COL_PARSING_OK ) ){
                
                $firLen = count( $fld_inf_reg );
               
                for( $i = 1; $i < $firLen; $i++ ){
                   
                    $this->currentSoC = $i - 1;
                    
                    $this->process_data_column( $fld_inf_reg[ $i ] );
                }

                $this->previousFieldSlugRoSNotMultiple = null;

                $this->previousValueSlugRoSNotMultiple = null;

                $this->store_vcs_in_process();

            } else {

                continue;

            }

        }

        $this->store_data();
        
    }

    function process_data_column( $data ){
        if( empty( $data ) || $data == '-' ){
            return JGB_WPS_CHCTREE_DATA_COL_PARSING_EMPTY;
        }

        $hook_sufix  = $this->currentReadingParameter;
        $hook_sufix .= empty( $this->currentReadingSubParameter ) ? '' : ':' . $this->currentReadingSubParameter;

        if( is_null( $this->processedFields[ $this->currentSoC ] ) || empty( $this->processedFields[ $this->currentSoC ] )){
            $this->processedFields[ $this->currentSoC ] = [];
        }
        
        $resulting_value = apply_filters(
            'JGB/wpsbsc/choice_tree_import_item/' . $hook_sufix,
            $this->processedFields[ $this->currentSoC ],
            $data,
            $this->currentReadingSubParameter,
            $this->currentSoC,
            $this
        );
        
        $this->processedFields[ $this->currentSoC ] = $resulting_value;

        return JGB_WPS_CHCTREE_DATA_COL_PARSING_OK;
        
    }

    function process_dt_col_fld_lbl( $currentData, $data, $subParameter, $soc, $ctip ){
        $currentData['label'] = trim($data);
        $currentData['slug'] = sanitize_title( trim( $data ) );
        $currentData['priority_in_step'] = $soc;
        return $currentData;
    }

    function process_dt_col_fld_vcs( $currentData, $data, $subParameter, $soc, $ctip ){

        $currentData['values-combination-set'] = explode(',',$data);

        foreach( $currentData['values-combination-set'] as $vcs ){
            
            if( !array_key_exists( $vcs, $this->valuesCombinationSets ) ){

                $this->valuesCombinationSets[ $vcs ] = [];

            }

        }

        return $currentData;
    }

    function process_dt_col_fld_def( $currentData, $data, $subParameter, $soc, $ctip ){
        $currentData[ $subParameter ] = json_decode( '{'. $data . '}', true );

        /* $fieldDefStrings = explode(',',$data);
        foreach( $fieldDefStrings as $k => $v ){
            $currentData[ $subParameter ] = json_decode( '{'. $v . '}', true );
        }*/
        return $currentData; 
    }

    function checkValues($v,$k){
        if( $this->currentValueSlugInVTM != sanitize_title( $v['slug'] ) ){
            return false;
        }

        if( $v['parent']['value_slug'] != $this->previousValueSlugRoSNotMultiple ){
            return false;
        }

        if( $v['parent']['field_slug'] != $this->previousFieldSlugRoSNotMultiple ){
            return false;
        }

        return true;
    }

    function process_dt_col_fld_mvals( $currentData, $data, $subParameter, $soc, $ctip ){
        
        if( isset( $currentData['value-def'] ) && isset( $currentData['value-def']['type'] ) ){
        
            $hknm = $currentData['value-def']['type'];
            
            $currentData = apply_filters(
                'JGB/wpsbsc/choice_tree_import_value_type/' . $hknm,
                $currentData, $data, $subParameter, $soc, $ctip
            );
            
            
        }
        
        return $currentData;
    }

    function addFVPairToParentFVPathString( $fieldId, $valueId ){
        if( $this->currentSoC < 1 ){
            $this->parentFVPath = '';        
        }   

        $this->parentFVPath .= $this->currentSoC > 0 ? ',' : '';

        $this->parentFVPath .= $fieldId . '=' . $valueId;

        return $this->parentFVPath;
    
    }

    function process_col_value_type_radio( $currentFldData, $data, $subParameter, $soc, $ctip ){
        
        if( ( trim( $data ) == '-') || ( '' == trim( $data ) ) ){
            return $currentFldData;
        }

        if( !isset( $currentFldData['value-def']['values'] ) ){
        
            $currentFldData['value-def']['values'] = [];
        
        }
        
        $nv = [];

        if( isset( $currentFldData['value-def']['value-type'] ) && $currentFldData['value-def']['value-type'] == 'multiple' ){
            
            $array_values = explode(',',$data);
            
            foreach( $array_values as $vl ){
               
                $nv['multiple'] = [];
                
                $nv['multiple'][] = [
                    'slug' => sanitize_title( $vl ),
                    'label' => $vl
                ];
                
                if( !is_null( $this->previousValueSlugRoSNotMultiple ) && !is_null( $this->previousFieldSlugRoSNotMultiple ) ){
                   
                    $nv['parent']=[
                        'value_slug' => $this->previousValueSlugRoSNotMultiple,
                        'field_slug' => $this->previousFieldSlugRoSNotMultiple 
                    ];

                }

            }
            
        } else {
        
            $this->currentValueSlugInVTM = sanitize_title( $data );

            $matchs = array_filter( $currentFldData['value-def']['values'],[$this,'checkValues'],ARRAY_FILTER_USE_BOTH);

            if( count( $matchs ) == 0 ){
            
                $nv['label'] = $data;
            
                $nv['slug'] = sanitize_title( $data );
                
                if( !is_null( $this->previousValueSlugRoSNotMultiple ) && !is_null( $this->previousFieldSlugRoSNotMultiple ) ){
                    
                    $nv['parent']=[
                        'value_slug' => $this->previousValueSlugRoSNotMultiple,
                        'field_slug' => $this->previousFieldSlugRoSNotMultiple 
                    ];
                }

                $currentFldData['value-def']['values'][] = $nv;
            }

            $this->previousFieldSlugRoSNotMultiple = $currentFldData['slug'];

            $this->previousValueSlugRoSNotMultiple = $this->currentValueSlugInVTM;

            $this->check_field_for_process_vcs( $currentFldData );

        }

        return $currentFldData;
    }

    function process_col_value_type_data( $currentFldData, $data, $subParameter, $soc, $ctip ){
        if( is_null( $data ) && empty( $data ) && ( trim( $data ) == '-' ) ){
            return $currentFldData;
        }

        $vcsm = '*';

        if( 
            isset( $currentFldData['value-def']['vcs-match'] ) 
            && !empty( $currentFldData['value-def']['vcs-match'] ) 
            
        ){

            $vcsm = $currentFldData['value-def']['vcs-match'];

        }            

        if( isset( $this->vcsInProcess[ $vcsm ] ) ){
            
            if( !isset( $this->vcsInProcess[ $vcsm ]['items'] ) ){
                
                $this->vcsInProcess[ $vcsm ]['items'] = [];

            }

            $dataType = !in_array( $currentFldData['data_type'], JGB_WPSBSC_ITEMS_DATA_TYPES ) || empty( $currentFldData['data_type'] ) ? 'INT' : $currentFldData['data_type'];

            $this->vcsInProcess[ $vcsm ]['items'][] = [
                'slug'  => $currentFldData['slug'],
                'label' => $currentFldData['label'],
                'data_type' => $dataType,
                'item_type' => 'DATA',
                'value' => trim( $data )
            ];

        }

        return $currentFldData;
    }

    function check_field_for_process_vcs( $currentFieldDataProcessing ){
        
        if( !is_null( $currentFieldDataProcessing['values-combination-set'] ) && is_array( $currentFieldDataProcessing['values-combination-set'] )){

            foreach( $currentFieldDataProcessing['values-combination-set'] as $vcs ){

                if( !isset( $this->vcsInProcess[ $vcs ] ) ){
                    
                    $this->vcsInProcess[ $vcs ] = [
                        'values-slugs-combinations' => []
                    ];

                }

                $this->vcsInProcess[ $vcs ]['values-slugs-combinations'][] = $this->currentValueSlugInVTM;

            }

        }

    }

    function store_vcs_in_process(){
        
        if( count( $this->vcsInProcess ) ){
            
            foreach( $this->vcsInProcess as $k => $vcsIP ){
                $this->valuesCombinationSets[$k][] = $vcsIP;
            }

            $this->vcsInProcess = [];

        }

    }

    function parse_first_column( $data ){

        [$fld_parameter, $fld_sub_param] = explode(':',$data);

        if( in_array( $fld_parameter, $this->allowedFldParameters ) ){
            $this->currentReadingParameter = $fld_parameter;
            
        } else {
            return JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_PARAMETER_STRING_INVALID;
        }

        $this->allowedFldSubParameters = $this->get_allowed_sub_parameters( $fld_parameter );

        if( 
            ( in_array( $fld_sub_param, $this->allowedFldSubParameters ) )
            || ( empty( $fld_sub_param) && empty( $this->allowedFldSubParameters ) ) 
        ){
            $this->currentReadingSubParameter = $fld_sub_param;
        } else {
            return JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_SUB_PARAM_STRING_INVALID;
        }
        
        return JGB_WPS_CHCTREE_FIRST_COL_PARSING_OK;
    }


    private function sd_reset(){
        
        global $wpdb;
        
        $pfx = $wpdb->prefix;

        

        /* Buscar Ids de choices_availables relacionados con el postid actual. COn
           esto tenemos todos la lista de registros de IDs de choices_availables que
           se deben eliminar. */

        $q  = "SELECT id FROM {$pfx}jgb_wpsbsc_choices_availables ";
        $q .= "WHERE post_id = {$this->postId}";

        $choices_availables_ids = $wpdb->get_col( $q );



        /* Con la lista de IDs de choices_availables se puede obtener la lista de 
           choices_combinations que ya no se usarán */
        
        $choices_combinations_ids = [];
        
        foreach( $choices_availables_ids as $caid ){

            $q  = "SELECT id FROM {$pfx}jgb_wpsbsc_choices_combinations ";
            $q .= "WHERE vls_ids_combinations_string LIKE \"%{$caid}%\"";

            $tawCCI = $wpdb->get_col( $q );

            foreach( $tawCCI as $t ){

                if( !in_array( $t, $choices_combinations_ids ) ){
                    
                    $choices_combinations_ids[] = $t;

                }

            }

        }

        /* Con la lista de choices_combinations que no se utilizarán se pueden obtener
           la lista de items de la tabla vcs_items según su tipo (DATA o FIELD) que ya 
           no se usarán. */
        $vcs_items_ids   = [];
        $items_DATA_ids  = [];
        $items_FIELD_ids = [];

        foreach( $choices_combinations_ids as $cci ){

            $q  = "SELECT id, id_item, item_type FROM {$pfx}jgb_wpsbsc_vcs_items ";
            $q .= "WHERE id_choice_combination = $cci";

            foreach( $wpdb->get_results( $q, ARRAY_A ) as $itm ){

                $vcs_items_ids[] = $itm['id'];

                if( empty( $itm['item_type'] ) || ( $itm['item_type'] == 'DATA' ) ){
                    $items_DATA_ids[] = $itm['id_item'];
                }

                if( $itm['item_type'] == 'FIELD' ){
                    $items_FIELD_ids[] = $itm['id_item'];
                }
            }
        }

        /* Eliminar items FIELD de tabla items_field. */
        $itrs = '';
        $i = 0;
        foreach( $items_FIELD_ids as $id ){
            $itrs .= $i > 0 ? ',' : '';
            $itrs .= $id;        
        }
        if( !empty( $itrs ) ){
            $q  = "DELETE FROM {$pfx}jgb_wpsbsc_items_field ";
            $q .= "WHERE id IN ($itrs)";
            $wpdb->query( $q );
        }

        /* ELiminar items DATA de tabla items_data. */
        $itrs = '';
        $i = 0;
        foreach( $items_DATA_ids as $id ){
            $itrs .= $i > 0 ? ',' : '';
            $itrs .= $id;        
        }
        if( !empty( $itrs ) ){
            $q  = "DELETE FROM {$pfx}jgb_wpsbsc_items_data ";
            $q .= "WHERE id IN ($itrs)";
            $wpdb->query( $q );
        }

        /* Eliminar items de tabla vcs_items. */
        $itrs = '';
        $i = 0;
        foreach( $vcs_items_ids as $id ){
            $itrs .= $i > 0 ? ',' : '';
            $itrs .= $id;        
        }
        if( !empty( $itrs ) ){
            $q  = "DELETE FROM {$pfx}jgb_wpsbsc_vcs_items ";
            $q .= "WHERE id IN ($itrs)";
            $wpdb->query( $q );
        }

        /* Eliminar registros de tabla choices_available */
        $wpdb->delete(
            "{$pfx}jgb_wpsbsc_choices_availables",
            ['post_id' => $this->postId ],
            ['%d']
        );

        /* Eliminar todos los registros choices_combinatios que contengan en la cadena 
           de IDs de choices el id de algún choices available. */
        
        // Deleting fields.
        $wpdb->delete(
            "{$pfx}jgb_wpsbsc_fields",
            ['post_id' => $this->postId ],
            ['%d']
        );

        

    }

    function sd_storer_type_radio( $fld, $postId ){
        
        global $wpdb;
        
        $pfx = $wpdb->prefix;

        $data = [
            'post_id'           => $postId,
            'slug'              => $fld['slug'],
            'name'              => $fld['label'],
            'priority_in_step'  => $fld['priority_in_step']
        ];

        $format = ['%d','%s','%s'];

        if( $wpdb->insert(
                "{$pfx}jgb_wpsbsc_fields",
                $data,
                $format
            ) 
        ){
            $fldId = $wpdb->insert_id;

            $fld['stored_id'] = $fldId;

            foreach( $fld['value-def']['values'] as &$vl ){
                
                $choices_data = [
                    'post_id'  => $postId,
                    'field_id' => $fldId,
                    'selectable_value_slug' => $vl['slug'],
                    'selectable_value_label'=> $vl['label']
                ];

                if( isset( $vl['parent'] ) ){

                    $query = "SELECT * FROM {$pfx}jgb_wpsbsc_fields WHERE post_id = {$postId} AND slug = \"{$vl['parent']['field_slug']}\"";
                    
                    $r = $wpdb->get_row($query, ARRAY_A );
                    
                    if( is_Array( $r ) && count( $r ) > 0 ){
                        
                        $choices_data['parent_field_id'] = $r['id'];
                        
                        $choices_data['parent_on_browser_selected_slug_value'] = $vl['parent']['value_slug'];
                    
                    }
                    
                }

                if( $wpdb->insert(
                    "{$pfx}jgb_wpsbsc_choices_availables",
                    $choices_data
                    ) 
                ){
                    $vl['stored_id'] = $wpdb->insert_id;
                }

            }

        }

        return $fld;

    }

    private function sd_fields(){
        
        foreach( $this->processedFields as &$fld ){

            $fld = apply_filters(
                'JGB/wpsbsc/store_field_data/type_' . $fld['value-def']['type'],
                $fld,
                $this->postId
            );

        }
    }

    private function sd_vcs_items(){
        global $wpdb;

        $pfx = $wpdb->prefix;

        foreach( $this->valuesCombinationSets as $k => $vcs ){

            $query  = "SELECT * FROM {$pfx}jgb_wpsbsc_vls_cmbs_sets ";
            $query .= "WHERE slug = \"$k\"";

            $vcsQueryResults = $wpdb->get_row( $query, ARRAY_A );

            $vcsId = '';

            if( is_Array( $vcsQueryResults ) && count( $vcsQueryResults ) > 0 ){

                $vcsId = $vcsQueryResults['id'];

            } else {
                
                $d = [
                    'slug' => $k,
                    'desc' => $k
                ];
                
                if(
                    $wpdb->insert(
                        "{$pfx}jgb_wpsbsc_vls_cmbs_sets",
                        $d
                    )
                ){
                    $vcsId = $wpdb->insert_id;
                }
            }

            if( $vcsId != '' ){

                foreach( $vcs as $sc ){

                    $choicesCombinationId = $this->sd_slugs_combination( $sc['values-slugs-combinations'] );
                    
                    $vcsItemsIdsAndType = $this->sd_vcs_items_data( $sc['items'], $choicesCombinationId );
                    
                    $this->sd_vcs_items_link( $vcsItemsIdsAndType, $choicesCombinationId );

                }
                
            }

        }

    }

    private function sd_vcs_items_link( Array $viiat, $cci ){

        global $wpdb;

        $pfx = $wpdb->prefix;

        $rids = [];

        foreach( $viiat as $itm ){

            if( $wpdb->insert( 
                    "{$pfx}jgb_wpsbsc_vcs_items",
                    [ 
                        'id_choice_combination' => $cci,
                        'item_type'             => $itm['item_type'],
                        'id_item'               => $itm['id'],
                        'data_type'             => $itm['data_type'],
                        'slug'                  => $itm['slug'],
                        'label'                 => $itm['label']    
                    ]
                )
            ){

                $itm['link_id'] = $wpdb->insert_id;

            }

            $rids[] = $itm;

        }

        return $rids;

    }

    private function sd_slugs_combination( Array $slugs ){

        global $wpdb;

        $pfx = $wpdb->prefix;

        $slugsString = '';

        foreach( $slugs as $k => $slug ){
            
            $query  = "SELECT * FROM {$pfx}jgb_wpsbsc_choices_availables ";
            $query .= "WHERE selectable_value_slug = \"$slug\"";

            $row = $wpdb->get_row( $query, ARRAY_A );

            if( is_array( $row ) && count( $row ) > 0 ){

                $slugsString .= $k > 0 ? ':' : '';

                $slugsString .= $row['id'];

            }

        }

        $query  = "SELECT * FROM {$pfx}jgb_wpsbsc_choices_combinations ";
        $query .= "WHERE vls_ids_combinations_string = \"$slugsString\"";

        $row = $wpdb->get_row( $query, ARRAY_A );

        if( is_array( $row ) && count( $row ) > 0 ){

            return $row['id'];

        } else {

            if( $wpdb->insert(
                    "{$pfx}jgb_wpsbsc_choices_combinations",
                    ['vls_ids_combinations_string' => $slugsString ]
                )
            ){

                return $wpdb->insert_id;

            }

        }

        return null;

    }

    private function sd_vcs_items_data( $itms, $cci ){

        global $wpdb;

        $pfx = $wpdb->prefix;

        $viiat = [];

        foreach( $itms as $k => $itm ){

            $tbl  = "{$pfx}jgb_wpsbsc_items_";
            $tbl .= $itm['item_type'] == 'DATA' ? "data" : "field";

            if( $wpdb->insert( 
                    $tbl,
                    [ 
                        'value'     => $itm['value']
                    ]
                )
            ){

                $itm['id'] = $wpdb->insert_id;

            }

            $viiat[] = $itm;

        }

        return $viiat;

    }

    private function store_data(){

        // reset old data.
        $this->sd_reset();

        // storing fields y VCS for radio type fields.
        $this->sd_fields();

        // Storing VCS's Slugs and items
        $this->sd_vcs_items();

    }
}