<?php


$opt = $args['option'];
$k   = $args['key'];

?>


        <tr>
            <td class="value">
                <div class="wrapper">
                    <label for="id-value-<?= $opt['slug']?>"><?= $opt['label'] ?></label>
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
        