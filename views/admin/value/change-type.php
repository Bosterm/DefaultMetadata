<div class="five columns offset-by-two omega">
    <p class="element-set-description">
        <?php echo html_escape(@get_current_record('item')->Type->description); ?>
    </p>
</div>
<?php
//echo $elementText;
echo $form;
?>