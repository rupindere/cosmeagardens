<?php
if (is_array($meta_data) && count($meta_data)) {
    ?>
         
                 <h3>Gift Product </h3>
                
        <?php
        foreach ($meta_data as $k => $data) {
            if(!is_array($data)){
                continue;
            }
            if (in_array($data['type'], array('checkbox-group', 'select', 'radio-group', 'image-group', 'color-group')) && is_array($data['value'])) {
                $label_printed = false;

                foreach ($data['value'] as $l => $v) {
                    //print_r($v);
                    ?>
                    <div class="view">

                                <?php
                                if ($data['type'] == 'image-group') {

                                    echo '' . __($v['label'], 'wcpa-text-domain') . '<br>';

                                    if (isset($v['image']) && $v['image'] !== FALSE) {
                                        $img_size_style = ((isset($data['form_data']->disp_size_img) && $data['form_data']->disp_size_img > 0) ? 'style="width:' . $data['form_data']->disp_size_img . 'px"' : '');

                                        echo ' <img class="wcpa_img" '.$img_size_style.'  src="' . $v['image'] . '" />';
                                    } else
                                    if (isset($v['value']) && $v['value'] !== FALSE) {
                                        echo ' ' . $v['value'];
                                    }
                                } 
                                ?>

                            </div>
                            <div class="edit" style="display: none;">
                                <?php
                                if ($data['type'] == 'image-group') {
                                    ?>
                                    <?php echo '<strong>' . __('Label:', 'wcpa-text-domain') . '</strong>'; ?>
                                    <input type="text" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][label]" 
                                           value="<?php echo $v['label'] ?>"> <br>
                                           <?php
                                           if (isset($v['image']) && $v['image'] !== FALSE) {
                                               echo __('Value:', 'wcpa-text-domain') . '<input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][value]" 
                                           value="' . $v['image'] . '">';
                                           } else
                                           if (isset($v['value']) && $v['value'] !== FALSE) {
                                               echo __('Value:', 'wcpa-text-domain') . ' <input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][value]" 
                                           value="' . $v['value'] . '">';
                                           }
                                       } else if (isset($v['i'])) {
                                           ?>
                                    <?php echo '<strong>' . __('Label:', 'wcpa-text-domain') . '</strong>'; ?>  <input type="text" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][label]" 
                                           value="<?php echo $v['label'] ?>"> <br>
                                    <?php echo '<strong>' . __('Value:', 'wcpa-text-domain') . '</strong>'; ?> <input type="text" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][value]" 
                                           value="<?php echo $v['value'] ?>">
                                           <?php
                                       } else {
                                           ?>
                                    <input type="text" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>]" value="<?php echo $v ?>">

                                <?php }
                                ?>


                            </div>

                            <div class="">
                                 <?php
                            if (isset($data['form_data']->enablePrice) && $data['form_data']->enablePrice &&
                                    (!isset($data['is_fee']) || $data['is_fee'] === false)) {
                                ?>
                                <div class="view">
                                   £<?php echo isset($data['price'][$l]) ? $data['price'][$l] : '0'; ?>.00
                                </div>
                                <div class="edit" style="display: none;">
                                    <input type="text"
                                           data-price="<?php echo (isset($data['price'][$l]) ? $data['price'][$l] : '0') ?>"
                                           class="wcpa_has_price" 
                                           name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>]"
                                           value="<?php echo (isset($data['price'][$l]) ? $data['price'][$l] : '0'); ?>">
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                  
                    <?php
                }
            } else {
                ?>
                

                        <?php
                        if ($data['type'] == 'hidden' && empty($data['label'])) {
                            echo $data['label'] . '[hidden]';
                        } else {
                            echo $data['label'];
                        }
                        ?>
                   
                        <div class="view">

                            <?php
                            if ($data['type'] == 'color') {
                                echo '<span style = "color:' . $data['value'] . ';font-size: 20px;
            padding: 0;
    line-height: 0;">&#9632;</span>' . $data['value'];
                            } else {
                                echo nl2br($data['value']);
                            }
                            ?>
                        </div>

                        <div class="edit" style="display: none;">
                            <?php
                            if ($data['type'] == 'paragraph' || $data['type'] == 'header') {
                                echo $data['value'];
                                echo '<input type="hidden" 
                                       name="wcpa_meta[value][' . $item_id . '][' . $k . ']" 
                                       value="1">';
                            } else if($data['type'] == 'textarea' ) {
                                ?>
                                <textarea  name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>]" ><?php echo ($data['value']) ?></textarea>
                                <?php
                            }
                            else {
                                ?>
                                <input type="text" 
                                       name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>]" 
                                       value="<?php echo htmlspecialchars($data['value']) ?>">
                                       <?php
                                   }
                                   ?>

                        </div>
                    
                 
              
                <?php
            }
            ?>


            <?php
        }
        ?>
     
            <!--   /* dummy field , it will help to iterate through all data for removing last item*/-->
        <input type="hidden" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k + 99; ?>]" value="">

        

    <?php
}



