		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4">
					<div class="an-single-component with-shadow">
						<div class="an-component-header">
							<h6><?php echo lang('org_choose_organization'); ?></h6>
						</div>
						<div class="an-component-body">
							<div class="an-user-lists user-stats">
								<div class="an-lists-body list-organizations">
<?php if (is_array($organizations)) foreach ($organizations as $organization) { ?>
									<a href="<?php echo $organization->url; ?>">
										<div class="list-user-single">
											<img class="organization-icon" src="<?php echo empty($organization->icon) ? Template::theme_url('images/organization.png') : base_url('assets/images/organizations/' . $organization->icon); ?>">
											<span class="organization-name"><?php echo $organization->name; ?></span>
										</div> <!-- end .USER-LIST-SINGLE -->
									</a>
<?php } ?>
								</div> <!-- end .AN-LISTS-BODY -->
							</div>
						</div> <!-- end .AN-COMPONENT-BODY -->
					</div> <!-- end .AN-SINGLE-COMPONENT messages -->
				</div>
			</div> <!-- end row -->
		</div>
