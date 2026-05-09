<?php

if (!empty($fields)){
    /** @var \pmai_acf_add_on\fields\Field $subField */
    foreach ($fields as $subField){
        ?>
        <tr class="field sub_field field_type-<?php echo $subField->getType();?> field_key-<?php echo $subField->getFieldKey();?>">
            <td>
                <div class="inner">
                    <?php
                    $subField->setFieldInputName($field_name . '[' . $field['key'] . ']');
                    $subField->view();
                    ?>
                </div>
            </td>
        </tr>
        <?php
    }
}