<?php
namespace JGB\WPSBSC;
class ProductFieldsManager{
    protected $product_fields;

    protected $items_data_keys;

    function __construct()
    {
        $this->set_product_fields();
        $this->items_data_keys = [];
    }

    public function set_product_fields(){
        $pf = [
            'jwps-prod-data'
        ];

        $this->product_fields = apply_filters('jgb/wpsbsc/setProductFields',$pf);
    }

    public function render_fields(){
        // chequear que sea un oproducto al que se le haya asignado un grupo de campos de configuración.
        ob_start();
        foreach( $this->product_fields as $pf ){
            ?><input type="hidden" name="<?= $pf ?>" value=""><?php
        }
        $output = ob_get_clean();
        echo $output;
    }

    public function process_product_fields($cart_item_data){
        foreach( $this->product_fields as $pf ){
            if(isset( $_POST[ $pf ] ) && !empty($_POST[ $pf ])) {
                
                $pfdata_bs64_encoded = sanitize_text_field($_POST[ $pf ]);
                $pfdata_json_encoded = mb_convert_encoding( rawurldecode( base64_decode($pfdata_bs64_encoded) ), 'UTF-8', 'UTF-8' );
                $pfdata = json_decode($pfdata_json_encoded, true);
                foreach( $pfdata as $v ){
                    $cart_item_data[ $v['field'] ] = [
                        'title' => $v['label'],
                        'value_label' => $v['valueLabel']
                    ];
                    if( $v['field'] == 'precio' ){
                        $cart_item_data[ $v['field'] ]['value'] = $v['value'];
                    }
                    $this->items_data_keys[] = $v['field'];
                }
            }
        }

        return $cart_item_data;
    }

    public function save_order_line_item($item, $cart_item_key, $values, $order) {
        foreach( $this->items_data_keys as $pf ){
            if(!empty($values[ $pf ])) {
                $item->add_meta_data( $pf , $values[ $pf ]);
            }
        }
    }

    public function update_product_price($cart) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;

        // Recorre cada producto en el carrito y modifica su precio
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            if( isset( $cart_item['precio'] ) ){
                $prod_price = $cart_item['data']->get_price();
                $cart_item['data']->set_price( $prod_price + $cart_item[ 'precio' ]['value'] );
                return;
            }
            
        }
        
    }

    public function poduct_item_data($item_data, $cart_item){
        foreach( $this->items_data_keys as $k ){
            if(isset( $cart_item[ $k ] ) && !empty($cart_item[ $k ])) {
                $pf = $cart_item[ $k ];
                $item_data[] = [
                    'key' => $pf['title'],
                    'value' => $pf['value_label']
                ];
            }
        }

        return $item_data;
    }

}