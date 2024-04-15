<?php

$wg = $args['widget'];
$label_field =  $wg->get_label();
$options = $wg->get_options();
?>

<table class="variations field" data-jgbsbsc-field-type="radio" cellspacing="0">
    <tbody>
        <tr>
        <td colspan="2" class="label"><?= $label_field ?> </td>
        </tr>
        

        <?php 
        foreach( $options as $k => $opt ) {
        ?>
        <tr>
            <td class="value">
                <div class="wrapper">
                    <label for="premium-glass"><?= $opt['label'] ?></label>
                    <input type="radio" name="<?= $opt['slug'] ?>" value="<?= $opt['value'] ?>" id="id-value-<?= $opt['slug']?>"/>
                    <?php if( isset( $opt['value_type'] ) && $opt['value_type'] != 'simple' ) {
                        ?>
                        <div class="buton-group <?= $opt['value_type'] ?>" data-opts-sels="">
                        <?php
                        foreach( $opt['sub_options'] as $sk => $sopt ){
                    ?>
                        <div class="option-buton outer">
                            <div class="option-buton" data-option="<?= $sopt['slug']?>"><?= $sopt['label']?></div>
                        </div>
                    <?php
                        }
                    } else {
                    ?>
                        <div class="select-buton outer">
                            <div class="select-buton">Escoger</div>
                        </div>
                    <?php } ?>
                    
                </div>
            </td>
        </tr>
        <?php } ?>

    </tbody>
</table>