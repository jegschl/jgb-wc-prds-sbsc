<?php

$wg = $args['widget'];
$label_field =  $wg->get_label();
$options = $wg->get_options();
?>

<table 
    class="variations field" 
    data-jgbsbsc-field-type="radio" 
    cellspacing="0" 
    data-field-id="<?= $wg->get_id() ?>"
>
    <tbody>
        <tr>
        <td colspan="2" class="label"><?= $label_field ?> </td>
        </tr>
        
        <?php do_action('jgb_wc_prds_sbsc_widget_render_before_radio_options', $wg); ?>

        {{#radio-options}}

    </tbody>
</table>
