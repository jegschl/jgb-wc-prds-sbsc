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

    protected $currentValueSlugInVTM;

    protected $previousValueSlugRoSNotMultiple; // Store last value slug in the same reading row.
    protected $previousFieldSlugRoSNotMultiple; // Store last field slug in the same reading row.

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

    private function setup(){
        $this->allowedFldParameters = $this->get_allowed_parameters();

        $this->set_items_parsers_hooks();

        $this->set_value_def_type_parsers_hooks();
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

    function process_input( $data ){
        $this->linesCount = count( $data );
        foreach( $data as $this->currentLine => $fld_inf_reg ){

            if( is_array($fld_inf_reg) && !empty( $fld_inf_reg ) && ($this->parse_first_column( $fld_inf_reg[0] ) == JGB_WPS_CHCTREE_FIRST_COL_PARSING_OK ) ){
                $firLen = count( $fld_inf_reg );
                for( $i = 1; $i < $firLen; $i++ ){
                    $this->currentSoC = $i - 1;
                    $this->process_data_column( $fld_inf_reg[ $i ] );
                }

            } else {
                continue;
            }

        }
        
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

        }

        return $currentFldData;
    }

    function process_col_value_type_data( $currentFldData, $data, $subParameter, $soc, $ctip ){

        return $currentFldData;
    }

    function parse_first_column( $data ){

        $this->previousFieldSlugRoSNotMultiple = null;

        $this->previousValueSlugRoSNotMultiple = null;

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
}