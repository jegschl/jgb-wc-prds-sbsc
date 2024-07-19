<?php

define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_OK',0);
define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_PARAMETER_STRING_INVALID',1);
define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_SUB_PARAM_STRING_INVALID',2);

define('JGB_WPS_CHCTREE_DATA_COL_PARSING_OK',0);
define('JGB_WPS_CHCTREE_DATA_COL_PARSING_EMPTY',1);

define('JGB_WPSBSC_ITEMS_DATA_TYPES', ['INT', 'DECIMAL', 'VARCHR'] );

define('JGB_WPSBSC_ITEMS_FIELD_TYPES', ['CHECK','RADIO','SELECT','TEXT'] );

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

    protected $previousFieldIdRoSNotMultiple; // Store last field id in the same reading row.
    
    protected $postId;

    protected $parentFVPath;

    protected $parentFVPathForItemsInProcess;

    protected $parentFVPathForItemsToKeepStored;

    private $slugsGertnKeyCache = [];
    
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
            ],
            'additional-selection' => [
                'callback' => [$this,'process_col_value_type_field'],
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

    private function set_columns_storer_by_type_hooks(){
        
        $hooks_array = [
            
            'radio' => [
                'callback' => [$this,'sd_col_tp_radio'],
                'priority' => 30
            ]

        ];

        $hooks_array = apply_filters( 'jgb/wpsbsc/import/columns_by_type_storer_hooks', $hooks_array );

        foreach( $hooks_array as $hook_sufix => $hcb ){
            add_filter('JGB/wpsbsc/store_column_data/type_' . $hook_sufix, $hcb['callback'], $hcb['priority'], 2 );
        }
    }

    private function set_additional_selection_field_type_hooks(){
        $hooks_array = [
            'CHECK' => [
                'callback' => [$this,'process_additional_selection_options_type_check'],
                'priority' => 30
            ],
            'RADIO' => [
                'callback' => [$this,'process_additional_selection_options_type_radio'],
                'priority' => 30
            ],
            'SELECT' => [
                'callback' => [$this,'process_additional_selection_options_type_select'],
                'priority' => 30
            ],
            'TEXT' => [
                'callback' => [$this,'process_additional_selection_options_type_text'],
                'priority' => 30
            ]
        ];

        $hooks_array = apply_filters( 'jgb/wpsbsc/import/additional_selection_field_type_hooks', $hooks_array );

        foreach( $hooks_array as $hook_sufix => $hcb ){
            add_filter('JGB/wpsbsc/choice_tree_import_item/type_' . $hook_sufix . '_options', $hcb['callback'], $hcb['priority'], 2 );
        }
    }

    private function setup(){

        $this->allowedFldParameters = $this->get_allowed_parameters();

        $this->set_items_parsers_hooks();

        $this->set_value_def_type_parsers_hooks();

        $this->set_columns_storer_by_type_hooks();

        $this->set_additional_selection_field_type_hooks();

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

        // by delete
       /*  $memlim = ini_get('memory_limit');

        $nml = '1024M';

        $curr_memlim = '';

        @ini_set('memory_limit' , $nml);

        $curr_memlim = ini_get('memory_limit'); */
        // until here by delete

        $this->sd_reset();
        
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

                $this->previousFieldIdRoSNotMultiple = null;

                $this->parentFVPath = '';

                $this->store_vcs_in_process();

                $this->mem_store_parents_fv_paths_in_process();

            } else {

                continue;

            }

        }

        // by delete
        /*
        @ini_set('memory_limit' , $memlim);

        $curr_memlim = ini_get('memory_limit');
        */
        
        //$this->store_data();

        // until here by delete

        $this->sd_vcs_items();
        
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

    function sd_column_bsdt( &$fld, $postId ){

        $fld = apply_filters(
            'JGB/wpsbsc/store_column_data/type_' . $fld['value-def']['type'],
            $fld,
            $postId
        );

        return $fld['stored_id'];

    }
     
    function sd_col_tp_radio( $fld, $postId ){
        global $wpdb;

        $pfx = $wpdb->prefix;

        $tbl_nm = "{$pfx}jgb_wpsbsc_fields";

        $query = "SELECT * FROM $tbl_nm WHERE post_id = $postId AND slug = \"{$fld['slug']}\"";

        $r = $wpdb->get_row( $query, ARRAY_A );

        if( is_array( $r ) && count( $r ) > 0 ){
            $fld['stored_id'] = $r['id'];
            return $r['id'];
        }

        if( ! isset( $this->slugsGertnKeyCache[$tbl_nm] ) ){
            $this->slugsGertnKeyCache[$tbl_nm] = [];
        }

        if( ! isset( $this->slugsGertnKeyCache[$tbl_nm][ $fld['slug'] ] ) ){
            $this->slugsGertnKeyCache[$tbl_nm][ $fld['slug'] ] = 0;
        }

        $slg_inspect = $this->generate_unique_unexistent_slug( $fld['slug'], $tbl_nm, $this->slugsGertnKeyCache[$tbl_nm][ $fld['slug'] ] );

        if( !is_null( $slg_inspect['err'] ) ){
            return null;
        } else {
            $fld['slug'] = $slg_inspect['slug'];
        }

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

            

        } else {
            $fld['stored_id'] = null;
        }

        return $fld;

    }

    private function generate_unique_unexistent_slug( $slug, $table, &$keyCache = null, $field = 'slug'){
        global $wpdb;

        if( $keyCache != null ){
            $i = $keyCache;
        } else {
            $i = 0;
        }

        $sql = "SELECT COUNT(*) FROM $table WHERE $field = '$slug'";

        $qr = $wpdb->get_var( $sql );

        while( $qr > 0 ){

            $i++;
            
            $sql = "SELECT COUNT(*) FROM $table WHERE $field = '{$slug}-$i'";

            $qr = $wpdb->get_var( $sql );

        }

        $r = [];

        if( empty( $wpdb->error_get_last ) ){
            $keyCache = $i; 
            $r['err'] = null;
            $r['slug'] = $i > 0 ? $slug . "-$i" : $slug;

        } else {
            $r['err'] = [
                'code' => $wpdb->error_get_last()['code'],
                'message' => $wpdb->error_get_last()['message']
            ];

            $r['slug'] = $slug;
        }

        return $r;
    }

    function get_choice_value_match_in_fv_tree_branch( $base_slug ){
        global $wpdb;

        $r = null;

        $pfx = $wpdb->prefix;
        $table_nm = "{$pfx}jgb_wpsbsc_choices_availables";

        $query = "SELECT id, selectable_value_slug as slug, selectable_value_label as label FROM $table_nm ";
        $query .= "WHERE post_id = {$this->postId} ";
        $query .= "AND parents_fv_path = '{$this->parentFVPath}' ";
        $query .= "AND selectable_value_slug REGEXP '^{$base_slug}(-[1-9][0-9]*)?$'";

        $rows = $wpdb->get_results( $query, ARRAY_A );

        if( count( $rows ) > 0 ){

            $r = $rows[0];

        }

        return $r;
    }

    function sd_available_value( &$nv, $field_id, $parent_field_id  ){
            
        global $wpdb;

        $pfx = $wpdb->prefix;

        $table_nm = "{$pfx}jgb_wpsbsc_choices_availables";

        $previous_parents_fv_path_checking = $this->get_choice_value_match_in_fv_tree_branch( $nv['slug'] );

        if( !is_null( $previous_parents_fv_path_checking ) ){
            
            $nv['stored_id'] = $previous_parents_fv_path_checking['id'];
            
            $nv['slug'] = $previous_parents_fv_path_checking['slug'];

        } else {

            if( ! isset( $this->slugsGertnKeyCache[$table_nm] ) ){
                $this->slugsGertnKeyCache[$table_nm] = 0;
            }
    
            if( ! isset( $this->slugsGertnKeyCache[$table_nm][ $nv['slug'] ] ) ){
                $this->slugsGertnKeyCache[$table_nm][ $nv['slug'] ] = 0;
            }
    
            $slg_inspect = $this->generate_unique_unexistent_slug( $nv['slug'], $table_nm, $this->slugsGertnKeyCache[$table_nm][ $nv['slug'] ], 'selectable_value_slug' );
    
            if( !is_null( $slg_inspect['err'] ) ){
                return null;
            } else {
                $nv['slug'] = $slg_inspect['slug'];
            }
    
            $data = [
                'post_id'  => $this->postId,
                'field_id' => $field_id,
                'selectable_value_slug' => $nv['slug'],
                'selectable_value_label'=> trim( $nv['label'] ),
                'parent_field_id' => $parent_field_id,
                'parent_on_browser_selected_slug_value' => $nv['parent']['value_slug'],
                'parents_fv_path' => $nv['parents_fv_path']
            ];
    
            if( $wpdb->insert(
                    $table_nm,
                    $data
                ) 
            ){
                $nv['stored_id'] = $wpdb->insert_id;
            }

        }

        return $nv['stored_id'];

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
        $this->sd_column_bsdt( $currentData, $this->postId );
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
        // test
        if( $v['parents_fv_path'] != $this->parentFVPath ){
            return false;
        }
        // until here test

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
        
        $this->parentFVPath = $this->get_curent_partial_vcs_str( 
            $this->parentFVPath,
            $fieldId, 
            $valueId
        );

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

        
        
        $this->currentValueSlugInVTM = sanitize_title( $data );

        $matchs = array_filter( $currentFldData['value-def']['values'],[$this,'checkValues'],ARRAY_FILTER_USE_BOTH);
        
        if( count( $matchs ) == 0 ){
        
            $nv['label'] = $data;
        
            $nv['slug'] = sanitize_title( $data );
            
            if( !is_null( $this->previousValueSlugRoSNotMultiple ) && !is_null( $this->previousFieldSlugRoSNotMultiple ) ){
                
                //deprecated
                $nv['parent']=[
                    'value_slug' => $this->previousValueSlugRoSNotMultiple,
                    'field_slug' => $this->previousFieldSlugRoSNotMultiple 
                ];
                // until here deprecated

                $nv['parents_fv_path'] = $this->parentFVPath;
            }

            $this->sd_available_value(
                $nv, 
                $currentFldData['stored_id'], 
                $this->previousFieldIdRoSNotMultiple
            );

            $currentFldData['value-def']['values'][] = $nv;
            
        } else {
            foreach( $matchs as $m ){
                $nv['stored_id'] = $m['stored_id'];
                break;
            }
        }

        $this->previousFieldSlugRoSNotMultiple = $currentFldData['slug'];

        $this->previousValueSlugRoSNotMultiple = $this->currentValueSlugInVTM;

        $this->previousFieldIdRoSNotMultiple = $currentFldData['stored_id'];

        $this->addFVPairToParentFVPathString( $currentFldData['stored_id'], $nv['stored_id'] );

        $this->check_field_for_process_vcs( $currentFldData, $nv['stored_id'] );

        

        return $currentFldData;

    }

    function process_col_value_type_data( $currentFldData, $data, $subParameter, $soc, $ctip ){
        if( is_null( $data ) || empty( $data ) || ( trim( $data ) == '-' ) ){
            return $currentFldData;
        }

        $vcsm = '*';

        if( 
            isset( $currentFldData['value-def']['vcs-match'] ) 
            && !empty( $currentFldData['value-def']['vcs-match'] ) 
            
        ){

            $vcsm = $currentFldData['value-def']['vcs-match'];

        }
     
        if( !isset( $this->vcsInProcess[ $vcsm ] ) ){

            $this->vcsInProcess[ $vcsm ] = [];
        
        }

        $vcsParentFVPath = $this->parentFVPathForItemsInProcess[ $vcsm ];

        if( !isset( $this->vcsInProcess[ $vcsm ][ $vcsParentFVPath ] ) ){
            
            $this->vcsInProcess[ $vcsm ][ $vcsParentFVPath ] = [ 'items' => [] ];

        }

        $dataType = !in_array( $currentFldData['data_type'], JGB_WPSBSC_ITEMS_DATA_TYPES ) || empty( $currentFldData['data_type'] ) ? 'INT' : $currentFldData['data_type'];

        $this->vcsInProcess[ $vcsm ][ $vcsParentFVPath ]['items'][] = [
            'slug'  => $currentFldData['slug'],
            'label' => $currentFldData['label'],
            'data_type' => $dataType,
            'item_type' => 'DATA',
            'value' => trim( $data )
        ];

        

        return $currentFldData;
    }

    function process_col_value_type_field( $currentFldData, $data, $subParameter, $soc, $ctip ){
        if( is_null( $data ) || empty( $data ) || ( trim( $data ) == '-' ) ){
            return $currentFldData;
        }

        $vcsm = '*';

        if( 
            isset( $currentFldData['value-def']['vcs-match'] ) 
            && !empty( $currentFldData['value-def']['vcs-match'] ) 
            
        ){

            $vcsm = $currentFldData['value-def']['vcs-match'];

        }
     
        if( !isset( $this->vcsInProcess[ $vcsm ] ) ){

            $this->vcsInProcess[ $vcsm ] = [];
        
        }

        $vcsParentFVPath = $this->parentFVPathForItemsInProcess[ $vcsm ];

        if( !isset( $this->vcsInProcess[ $vcsm ][ $vcsParentFVPath ] ) ){
            
            $this->vcsInProcess[ $vcsm ][ $vcsParentFVPath ] = [ 'items' => [] ];

        }

        $fieldType = !in_array( $currentFldData['field_type'], JGB_WPSBSC_ITEMS_FIELD_TYPES ) || empty( $currentFldData['field_type'] ) ? 'RADIO' : $currentFldData['field_type'];

        $this->vcsInProcess[ $vcsm ][ $vcsParentFVPath ]['items'][] = [
            'slug'  => $currentFldData['slug'],
            'label' => $currentFldData['label'],
            'item_type' => 'FIELD',
            'field_type' => $fieldType,
            'options' => trim( $data )
        ];

        return $currentFldData;
    }

    function check_field_for_process_vcs( $currentFieldDataProcessing, $permanent_stored_value_id ){
        
        if( !is_null( $currentFieldDataProcessing['values-combination-set'] ) && is_array( $currentFieldDataProcessing['values-combination-set'] )){

            foreach( $currentFieldDataProcessing['values-combination-set'] as $vcs ){


                if( !isset( $this->parentFVPathForItemsInProcess[ $vcs ] ) ){
                    $this->parentFVPathForItemsInProcess[ $vcs ] = '';
                }

                $this->parentFVPathForItemsInProcess[ $vcs ] = $this->get_curent_partial_vcs_str( 
                                                                    $this->parentFVPathForItemsInProcess[ $vcs ],
                                                                    $currentFieldDataProcessing['stored_id'], 
                                                                    $permanent_stored_value_id
                                                                );

            }

        }

    }

    function get_curent_partial_vcs_str( $currentFVPathStr, $currentFieldDataProcessingId, $permanent_stored_value_id ){

        if( $currentFVPathStr != ''){
            $vcs = $currentFVPathStr . ',';
        } else {
            $vcs = '';
        }

        $vcs .= $currentFieldDataProcessingId;

        $vcs .= '=';

        $vcs .= $permanent_stored_value_id;

        return $vcs;
    }

    function mem_store_parents_fv_paths_in_process(){

        foreach( $this->parentFVPathForItemsInProcess as $vcsKey => &$pov ){

            foreach( $pov as $path => &$doas ){ //doas = data or additional selection

                $doas = null;

            }

            $pov = null;

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

        $postId = $this->postId;


        /* Con la lista de choices_combinations que no se utilizarán se pueden obtener
           la lista de items de la tabla vcs_items según su tipo (DATA o FIELD) que ya 
           no se usarán. */
        $vcs_items_ids   = [];
        $items_DATA_ids  = [];
        $items_FIELD_ids = [];

        $q  = "SELECT id, id_item, item_type FROM {$pfx}jgb_wpsbsc_vcs_items ";
        $q .= "WHERE post_id = $postId";

        foreach( $wpdb->get_results( $q, ARRAY_A ) as $itm ){

            $vcs_items_ids[] = $itm['id'];

            if( empty( $itm['item_type'] ) || ( $itm['item_type'] == 'DATA' ) ){
                $items_DATA_ids[] = $itm['id_item'];
            }

            if( $itm['item_type'] == 'FIELD' ){
                $items_FIELD_ids[] = $itm['id_item'];
            }
        }

        /* Eliminar items FIELD de tabla items_field. */
        $itrs = '';
        $i = 0;
        foreach( $items_FIELD_ids as $id ){
            $itrs .= $i > 0 ? ',' : '';
            $itrs .= $id;
            $i++;
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
            $i++;     
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
            $i++;
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
            "{$pfx}jgb_wpsbsc_choices_combinations",
            ['post_id' => $this->postId ],
            ['%d']
        );
        
        // Deleting fields.
        $wpdb->delete(
            "{$pfx}jgb_wpsbsc_fields",
            ['post_id' => $this->postId ],
            ['%d']
        );

        /* Eliminar registros de vls_cmbs_sets */
        $wpdb->delete(
            "{$pfx}jgb_wpsbsc_vls_cmbs_sets",
            ['post_id' => $this->postId ],
            ['%d']
        );

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
                    'post_id' => $this->postId,
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

                    foreach( $sc as $cci => $vcsItems ){

                        
                        $choicesCombinationId = $this->sd_fv_parent_path_for_items( $cci );

                        $vcsItemsIdsAndType = $this->sd_vcs_items_data( $vcsItems['items'], $choicesCombinationId );
                        
                        $this->sd_vcs_items_link( $vcsItemsIdsAndType, $choicesCombinationId, $vcsId );

                    }

                }
                
            }

        }

    }

    private function sd_vcs_items_link( Array $viiat, $cci, $vcsId ){

        global $wpdb;

        $pfx = $wpdb->prefix;

        $rids = [];

        foreach( $viiat as $itm ){

            if( $wpdb->insert( 
                    "{$pfx}jgb_wpsbsc_vcs_items",
                    [ 
                        'post_id'               => $this->postId,
                        'id_item'               => $itm['itm_dof_stored_id'],
                        'id_choice_combination' => $cci,
                        'id_vcs'                => $vcsId,
                        'item_type'             => $itm['item_type'],
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

    private function sd_fv_parent_path_for_items( $fvparentpath ){

        global $wpdb;

        $pfx = $wpdb->prefix;

        $tbl_nm = "{$pfx}jgb_wpsbsc_choices_combinations";

        

        $query  = "SELECT * FROM $tbl_nm ";
        $query .= "WHERE vls_ids_combinations_string = \"$fvparentpath\"";

        $row = $wpdb->get_row( $query, ARRAY_A );

        if( is_array( $row ) && count( $row ) > 0 ){

            return $row['id'];

        } else {

            if( $wpdb->insert(
                    $tbl_nm,
                    [
                        'post_id'                     => $this->postId,
                        'vls_ids_combinations_string' => $fvparentpath 
                    ]
                )
            ){

                return $wpdb->insert_id;

            }

        }

        return null;

    }

    private function process_additional_selection_options( $itemDataField ){

        $optionString = '';

        $optionString = apply_filters(
                            'JGB/wpsbsc/choice_tree_import_item/type_'. $itemDataField['field_type'] . '_options', 
                            $itemDataField['options'], 
                            $itemDataField  
                        );

        return $optionString;

    }

    private function sd_vcs_items_data( Array &$itms, $cci ){

        global $wpdb;

        $pfx = $wpdb->prefix;

        $viiat = [];

        foreach( $itms as $k => &$itm ){

            $tbl  = "{$pfx}jgb_wpsbsc_items_";

            $dt = [];

            switch( $itm['item_type'] ){
                case 'DATA':
                    $tbl .= "data";
                    $dt = [
                        'type'  => $itm['data_type'],
                        'value' => $itm['value']
                    ];
                    break;

                case 'FIELD':
                    $tbl .= "field";
                    $dt = [
                        'type'  => $itm['field_type'],
                        'options' => $this->process_additional_selection_options( $itm )
                    ];
                    break;

                }

            if( $wpdb->insert( 
                    $tbl,
                    $dt
                )
            ){

                $itm['itm_dof_stored_id'] = $wpdb->insert_id;

            }

            $viiat[] = $itm;

        }

        return $viiat;

    }

    private function verify_slug_key_in_array( $slug, $elementSlugKey, $a ){
        if( !is_array( $a ) ){
            return false;
        }

        if( count( $a ) < 1 ){
            return $slug;
        }

        $i = 0;
        $slugAlreadyExists = false;
        $iSlug = '';
        $slugExpldd = [];

        do {
            $slugAlreadyExists = false;
            foreach( $a as $v ){

                if( !isset( $v[ $elementSlugKey ] ) ){
                    continue;
                }

                $slugExpldd = explode('-', $v[ $elementSlugKey ] );

                if( count( $slugExpldd ) == 1 ){
                    $iSlug = $slugExpldd[0];
                    if( $iSlug == $slug ){
                        $slugAlreadyExists = true;
                        $i++;
                        $slug = $iSlug . '-' . $i;
                        break;
                    }
                }

                if( count( $slugExpldd ) == 2 ){
                    $iSlug  = $slugExpldd[0];

                    if( $v[ $elementSlugKey ] == $iSlug . '-' . $i ){
                        $slugAlreadyExists = true;
                        $i++;
                        $slug = $iSlug . '-' . $i;
                        break;
                    }
                }
                
            }
        } while( $slugAlreadyExists );
        
        return $slug;   

    }

    public function process_additional_selection_options_type_radio( $value, $itemDataField ){

        $raw_options = explode(',',$value);

        $tmp_structured_option = [];
        
        $strctured_opts = [];


        foreach( $raw_options as $v ){

            $nSlug = sanitize_title( trim( $v ) );

            $tmp_structured_option['slug'] = $this->verify_slug_key_in_array( $nSlug, 'slug', $strctured_opts );

            $tmp_structured_option['label'] = trim( $v );

            $strctured_opts[] = $tmp_structured_option;

        }

        return json_encode( ['selection-options' => $strctured_opts] );
    }

    public function process_additional_selection_options_type_check( $value, $itemDataField ){
        return $value;
    }
    
    public function process_additional_selection_options_type_select( $value, $itemDataField ){
        return $value;
    }
    
    public function process_additional_selection_options_type_text( $value, $itemDataField ){
        return $value;
    }

}