<?php
$is_owner = $meeting->owner_id == $current_user->user_id;
?>
<div>
	<?php if (IS_AJAX): ?>
	<div class="modal-header">
		<h4 class="modal-title" style="display: inline">
			<span class="label label-bordered label-default"><?php e($meeting_key) ?></span>
			<?php e($meeting->name)?>
		</h4>

		<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
	</div> <!-- end MODAL-HEADER -->
	<?php endif; ?>
		<div class="modal-body">
			<div class="an-single-component with-shadow">
				<div class="an-component-header">
					<h6><?php e(lang('st_goal'))?></h6>
				</div>
				<div class="an-component-body an-helper-block">
					<div class="meeting-goal-container readmore-container">
						<div class="goal">
							<?php echo $meeting->goal?>
						</div>
					</div>
				</div> <!-- end .AN-COMPONENT-BODY -->
			</div>

			<div id="comment">
				<div class="an-single-component with-shadow">
					<div class="an-component-header">
						<h6><?php e(lang('mt_comments'))?></h6>

						<span class="badge badge-bordered badge-warning badge-comment" style="display: none">
							<span class="number"></span> <span><?php echo lang('mt_new_message') ?></span>
						</span>
					</div>
					<div class="an-component-body">
						<div class="an-user-lists chat-container chat-page">
							<div id="comment-body" class="an-lists-body" style="max-height: 300px">
								<?php foreach ($comments as $comment): ?>
								<div data-id="<?php echo $comment->meeting_comment_id ?>" class="list-user-single">
									<div class="list-name">
										<span class="avatar" 
											style="background-image: url('<?php echo avatar_url($comment->avatar, $comment->email) ?>'); width: 30px; height: 30px;">
										</span>
										<a href="#" target="_blank">
											<span class="name"><?php echo $comment->full_name ?></span>
											<?php if ($comment->is_owner == '1'): ?>
											<span class="badge badge-bordered badge-owner"><?php echo lang('mt_owner') ?></span>
											<?php endif; ?>
											<span class="an-time">
												<i class="icon-clock"></i>
												<span class="time" data-created-on="<?php echo $comment->created_on ?>"></span>
											</span>
										</a>
									</div>
									<p class="comment"><?php e($comment->comment) ?></p>
								</div> <!-- end .USER-LIST-SINGLE -->
								<?php endforeach; ?>
							</div> <!-- end .AN-LISTS-BODY -->
							<div class="an-chat-form">
								<form class="an-form" action="#">
								<div class="an-search-field topbar">
									<input id="send-comment" class="an-form-control" type="text" placeholder="<?php echo lang('mt_type_a_comment') ?>"
											autocomplete="off"
											data-i-am-owner="<?php echo (int) $is_owner ?>"
											data-my-full-name="<?php echo $current_user->first_name .' '. $current_user->last_name ?>"
											data-my-avatar-url="<?php echo avatar_url($current_user->avatar, $current_user->email) ?>"
									>
									<button class="an-btn an-btn-icon btn-send-comment">
										<i class="ion-paper-airplane"></i>
									</button>
								</div>
								</form>
							</div>
						</div>
					</div> <!-- end .AN-COMPONENT-BODY -->
				</div>
			</div> <!-- end #comment -->
		</div>
		<div class="modal-footer">
			<a href="<?php echo site_url('meeting/' . $meeting_key) ?>" class="an-btn an-btn-primary">
				<?php echo lang('mt_go_to_detail') ?>
			</a>
			<button class="an-btn an-btn-primary-transparent" data-dismiss="modal"><?php e(lang('mt_close')) ?></button>
		</div>
</div>

<?php if (IS_AJAX) {
	echo '<script type="text/javascript">' . $this->load->view('preview_js', [
		'meeting_id' => $meeting_id,
		'is_owner' => $is_owner,
	], true) . '</script>';
}
?>

<script id="single-comment" type="ajax/vithd">
	<div data-id="{{:id}}" class="list-user-single{{if mark_as_read==false}} unread{{/if}}" style="display:none">
		<div class="list-name">
			<span class="avatar" 
				style="background-image: url('{{:avatar_url}}'); width: 30px; height: 30px;">
			</span>
			<a href="#" target="_blank">
				<span class="name">{{:full_name}}</span>
				{{if is_owner=="1"}}<span class="badge badge-bordered badge-owner"><?php echo lang('mt_owner') ?></span>{{/if}}
				<span class="an-time">
					<i class="icon-clock"></i>
					<span class="time" data-created-on="{{:created_on}}"><?php echo lang('mt_a_few_second_ago') ?></span>
				</span>
			</a>
		</div>
		<p class="comment">{{:comment}}</p>
	</div> <!-- end .USER-LIST-SINGLE -->
</script>