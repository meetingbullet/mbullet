<?php
?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title"><?php e(lang('hw_add_homework')) ?></h4>
</div> <!-- end MODAL-HEADER -->

<?php echo form_open($this->uri->uri_string(), ['class' => 'form-ajax', 'id' => 'create-homework']) ?>

<div class="container-fluid modal-body">
	<?php echo mb_form_input('text', 'name', lang('hw_name'), true, null) ?>
	<?php echo mb_form_input('text', 'description', lang('hw_description'), false, null) ?>

	<div class="row">
		<div class="col-md-3 col-sm-12">
			<label for="time_spent" class="pull-right"><?php e(lang('hw_time_spend')) ?><span class="required">*</span></label>
		</div>
		<div class="col-md-9 col-sm-12">
			<div class="row">
				<div class="col-md-5">
					<input type="number" name="time_spent" time_spent="in" class="an-form-control<?php e(iif( form_error('time_spent') , ' danger')) ?>" value="<?php e(set_value('time_spent')) ?>" step="0.1">
				</div>
				<div class="col-md-2" style="vertical-align: middle">
					<?php e(lang('hw_minutes'))?>
				</div>
			</div>
		</div>
	</div>
	<?php echo mb_form_input('text', 'member', lang('hw_member'), true, null, 'team select-member an-tags-input', '', lang('hw_add_team_member')) ?>

	<div class="row">
		<div class="col-md-3 col-sm-12">
			<label for="attachment" class="pull-right"><?php echo lang('hw_attachment') ?></label>
		</div>
		<div class="col-md-9 col-xs-12">
			<div class="attachment input">
				<div id="attachment-data">
					<?php if ($this->input->post('attachments')) :
							foreach ($this->input->post('attachments') as $i => $att): ?>
							<div class="single-attachment">
								<a href="<?php echo $att['url'] ?>" class="an-control-btn" target="_blank">
									<span class="icon">
										<?php if ( isset($att['favicon']) ): ?>
										<img src="<?php echo $att['favicon'] ?>">
										<?php else: ?>
										<i class="icon-file"></i>
										<?php endif; ?>
									</span>
								</a>
								
								<span class="filename"><?php echo $att['title'] ? $att['title'] : word_limiter($att['url'], 60) ?></span>

								<i class="ion-close-round remove-attachment pull-right"></i>

								<input type="hidden" name="attachments[<?php echo $i ?>][url]" value="<?php echo $att['url'] ?>" />
								<input type="hidden" name="attachments[<?php echo $i ?>][title]" <?php echo isset($att['title']) ? 'value="' . $att['title'] . '"' : '' ?> />
								<input type="hidden" name="attachments[<?php echo $i ?>][favicon]" <?php echo isset($att['favicon']) ? 'value="' . $att['favicon'] .'"' : '' ?> />
							</div>
					<?php 	endforeach;
						endif; ?>
				</div>

				<div class="single-attachment">
					<input type="text" id="attachment" name="attachment" class="btn-add-attachment" placeholder="<?php echo lang('hw_paste_your_attachment_url') ?>"/>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal-footer">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('hw_add'))?></button>
	<a href="#" class="an-btn an-btn-danger-transparent" data-dismiss="modal"><?php e(lang('hw_cancel'))?></a>
</div>

<?php echo form_close(); ?>

<?php if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('create_js', [
		'organization_members ' => $organization_members 
	], true) . '</script>';
}
?>