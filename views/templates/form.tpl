<?php
use oat\tao\helpers\Template;
Template::inc('form_context.tpl', 'tao')
?>
<div class="main-container" data-tpl="taoOpenWebItem/form.tpl">
    <h2><?=get_data('formTitle')?></h2>
    <?php if(get_data('hasContent')):?>
        <div class="feedback-info">
            <span class="icon-info"></span><?= __('This Open Web Item already has content. Go to Preview to see it or import a replacement below.')?>
        </div>
	<?php endif?>
    <div class="form-content">
    
    	<?=get_data('myForm')?>
    	<?php if(has_data('report')):?>
    	   <?php echo tao_helpers_report_Rendering::render(get_data('report')); ?>
    	<?php endif?>
    	
    </div>
</div>
