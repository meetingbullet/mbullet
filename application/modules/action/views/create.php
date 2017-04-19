<?php if ($this->input->is_ajax_request()): ?>
<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	<h4 class="modal-title" id="myModaloneLabel"><?php e(lang('ac_create_action'))?></h4>
</div> <!-- end MODAL-HEADER -->
<?php else: ?>
<div class="an-body-topbar wow fadeIn" style="visibility: visible; animation-name: fadeIn;">
	<div class="an-page-title">
	<h2><?php e(lang('ac_create_action'))?></h2>
	</div>
</div> <!-- end AN-BODY-TOPBAR -->
<?php endif; ?>

<?php echo form_open($this->uri->uri_string(), ['class' => $this->input->is_ajax_request() ? 'form-ajax' : '']) ?>

<div class='container-fluid<?php echo $this->input->is_ajax_request() ? ' modal-body' : ''?>'>
		<?php echo mb_form_input('text', 'name', lang('pj_project_name'), true) ?>

		<div class="row">
			<div class="col-md-3 col-sm-12">
				<label for="goal" class="pull-right"><?php e(lang('pj_goal')) ?></label>
			</div>
			<div class="col-md-9 col-sm-12">
				<textarea name="goal" class="an-form-control"><?php echo set_value('goal') ?></textarea> 
			</div>
		</div>

		
</div>

<div class="<?php echo $this->input->is_ajax_request() ? 'modal-footer' : 'container-fluid pull-right' ?>">
	<button type="submit" name="save" class="an-btn an-btn-primary"><?php e(lang('pj_create'))?></button>
	<a href="#" class="an-btn an-btn-primary-transparent" <?php echo $this->input->is_ajax_request() ? 'data-dismiss="modal"' : '' ?>><?php e(lang('pj_cancel'))?></a>
</div>

<?php echo form_close(); ?>