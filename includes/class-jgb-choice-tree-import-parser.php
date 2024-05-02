<?php

define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_OK',0);
define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_PARAMETER_STRING_INVALID',1);
define('JGB_WPS_CHCTREE_FIRST_COL_PARSING_ERROR_SUB_PARAM_STRING_INVALID',2);

define('JGB_WPS_CHCTREE_DATA_COL_PARSING_OK',0);
define('JGB_WPS_CHCTREE_DATA_COL_PARSING_EMPTY',1);

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
            ],
            'data' => [
                'callback' => [$this,'sd_storer_type_data'],
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
        return $this->currentValueSlugInVTM == sanitize_title( $v['slug'] );
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

        if( isset( $currentFldData['value-def']['vcs-match'] ) && !empty( $currentFldData['value-def']['vcs-match'] ) && isset( $this->vcsInProcess[ $currentFldData['value-def']['vcs-match'] ] )){
            $this->vcsInProcess[ $currentFldData['value-def']['vcs-match'] ]['data-value'] = trim( $data );
        }

        return $currentFldData;
    }

    function check_field_for_process_vcs( $currentFieldDataProcessing ){
        
        if( !is_null( $currentFieldDataProcessing['values-combination-set'] ) && is_array( $currentFieldDataProcessing['values-combination-set'] )){

            foreach( $currentFieldDataProcessing['values-combination-set'] as $vcs ){

                if( !isset( $this->vcsInProcess[ $vcs ] ) ){
                    
                    $this->vcsInProcess[ $vcs ] = [
                        'values-slugs-combinations' => [],
                        'data-value' => null
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

        // Deleting fields.
        $wpdb->delete(
            "{$pfx}jgb_wpsbsc_fields",
            ['post_id' => $this->postId ],
            ['%d']
        );

        // deleting choices
        $wpdb->delete(
            "{$pfx}jgb_wpsbsc_choices_availables",
            ['post_id' => $this->postId ],
            ['%d']
        );

    }

    function sd_storer_type_data( $fld, $postId ){

        global $wpdb;
        
        $pfx = $wpdb->prefix;

        $data = [
            'slug'      => $fld['slug'],
            'label'     => $fld['label'],
            'data_type' => 'INT',
            'value'     => 
        ];

        $format = ['%d','%s','%s'];

        if( $wpdb->insert(
                "{$pfx}jgb_wpsbsc_fields",
                $data,
                $format
            ) 
        ){

        }

        return $fld;

    }

    function sd_storer_type_radio( $fld, $postId ){
        
        global $wpdb;
        
        $pfx = $wpdb->prefix;

        $data = [
            'post_id' => $postId,
            'slug'    => $fld['slug'],
            'name'    => $fld['label']
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

    private function store_data(){

        // reset old data.
        $this->sd_reset();

        // storing fields y VCS.
        $this->sd_fields();

        

    }
}