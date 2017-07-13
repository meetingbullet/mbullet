<div class="init">
	<div class="init-nav calendar">
		<div class="step setup">
			Setup
			<div class="transitor"></div>
		</div>
		<div class="step import">
			Import
			<div class="transitor"></div>
		</div>
		<div class="step my-meeting">
			<span class="text-center">
				My Meeting
				<p class="sub-step">
					<span class="dot"></span>
					<span class="dot"></span>
					<span class="dot"></span>
				</p>
			</span> 
			<div class="transitor"></div>
		</div>
		<div class="step projects">
			Projects
			<div class="transitor"></div>
		</div>
		<div class="step team">
			Team
			<div class="transitor"></div>
		</div>
		<div class="step">Finish</div>
	</div>

	<div class="init-nav summary">
		<h4>
			<i class="ion-ios-cloud-download-outline"></i> 
			<h3 class="title">Import Summary</h3>
		</h4>
	</div>

	<div class="clear"></div>

	<div class="init-body">
		<div class="calendar">
			<div class="calendar-info">
				<div class="wrapper">
					<div class="user">
						<div class="avatar" style="float:left; background-image:url('<?php echo avatar_url($current_user->avatar, $current_user->email) ?>')"></div>
						<div class="info">
							<span class="name"><strong><?php echo $current_user->first_name . ' ' . $current_user->last_name ?></strong></span><br/>
							<span class="email"><?php echo $current_user->email ?></span>
						</div>
					</div>
				</div>

				<div class="wrapper">
					<h2 id="calendar-init-title"></h2>
				</div>

				<div class="wrapper">
					<div class="fc-toolbar fc-header-toolbar">
						<div class="fc-button-group">
							<button type="button" class="fc-prev-button fc-button fc-state-default fc-corner-left">
								<span class="fc-icon fc-icon-left-single-arrow"></span>
							</button>
							<button type="button" class="fc-today-button fc-button fc-state-default">Today</button>
							<button type="button" class="fc-next-button fc-button fc-state-default fc-corner-right">
								<span class="fc-icon fc-icon-right-single-arrow"></span>
							</button>
						</div>

						<div class="fc-button-group">
							<button type="button" class="fc-full-button fc-button fc-state-default fc-corner-left fc-state-active">Full</button>
							<button type="button" class="fc-list-button fc-button fc-state-default fc-corner-right">List</button> 
						</div>

						<div class="fc-button-group">
							<button type="button" data-full-view="agendaDay" data-list-view="listDay" class="fc-button fc-change-view fc-state-default fc-corner-left">Day</button>
							<button type="button" data-full-view="agendaWeek" data-list-view="listWeek" class="fc-button fc-change-view fc-state-default">Week</button>
							<button type="button" data-full-view="month" data-list-view="listMonth" class="fc-button fc-change-view fc-state-default fc-corner-right fc-state-active">Month</button>
						</div>
					</div>
				</div>
			</div>
			<div class="calendar-init-wrapper">
				<div class="loading-indicator">LOADING...</div>
				<div id="calendar-init"></div>
			</div>
		</div> <!-- .calendar -->
		<div class="summary">
			<div class="bubba-tea"></div>
			
			<div class="content-wrapper">
				<div class="content">
					<h3 class='title'>Hey <?php echo $current_user->first_name ?></h3>
					<p>From <b>May 1, 2017 - May 31, 2017</b></p>
					<p>You're in <b class='number totalMeeting'>0</b> meetings for <b class='number totalTime'>0</b> hours</p>
					<p>That's <b class='number'><span class="percentOfWorkingHour">0</span>%</b> of your total working hours! <span class="text-muted">(based on 40hr work weeks)</span></p>

					<h3 class="section">Overview</h3>
					<div class="overview-table">
						<div class="row vertical-align">
							<div class="col-md-6 text-center">
								Your events
							</div>
							<div class="col-md-3 text-center">
								<div>
									<div class='time-wrapper'>
										<i class="ion-easel"></i>
										Meeting
									</div>
									<b class='totalMeeting'>0</b>
								</div>
							</div>
							<div class="col-md-3 text-center">
								<div>
									<div class='time-wrapper'>
									<i class="ion-android-alarm-clock"></i>
									<?php echo lang('db_time') ?>
									<button class="btn btn-default btn-convert-time dropdown-toggle" data-toggle="dropdown">
										<span class='text'><?php echo lang('db_hours') ?></span>
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu">
										<li><a href="#" data-option="minute"><?php echo lang('db_minutes') ?></a></li>
										<li><a href="#" data-option="hour"><?php echo lang('db_hours') ?></a></li>
										<li><a href="#" data-option="day"><?php echo lang('db_days') ?></a></li>
									</ul>
									</div>
									<b class='target-time totalTime'>0</b>
								</div>
							</div>
						</div>
						<hr>

						<div class="row bold">
							<div class="col-md-6">As an Owner</div>
							<div class="col-md-3 ownerMeeting">0</div>
							<div class="col-md-3 ownerTime target-time">0</div>
						</div>
						<div class="row">
							<div class="col-md-6">&nbsp; Meeting Bullet</div>
							<div class="col-md-3 ownerMBMeeting">0</div>
							<div class="col-md-3 ownerMBTime target-time">0</div>
						</div>
						<div class="row">
							<div class="col-md-6">&nbsp; Non-Meeting Bullet</div>
							<div class="col-md-3 ownerNonMBMeeting">0</div>
							<div class="col-md-3 ownerNonMBTime target-time">0</div>
						</div>
						<br>
						<div class="row bold">
							<div class="col-md-6">As a Guest</div>
							<div class="col-md-3 guestMeeting">10</div>
							<div class="col-md-3 guestTime target-time">12</div>
						</div>
						<div class="row">
							<div class="col-md-6">&nbsp; Meeting Bullet</div>
							<div class="col-md-3 guestMBMeeting">0</div>
							<div class="col-md-3 guestMBTime target-time">0</div>
						</div>
						<div class="row">
							<div class="col-md-6">&nbsp; Non-Meeting Bullet</div>
							<div class="col-md-3 guestNonMBMeeting">0</div>
							<div class="col-md-3 guestNonMBTime target-time">0</div>
						</div>
					</div>
				</div> <!-- .content -->

				<div class="an-small-doc-block primary">
					<h3 class="section primary">Next Step</h3>
					Let us help you start getting the most out of your investment in meetings! We'll start with your meeting as the Owner.
				</div>
			</div> <!-- .content-wrapper -->
		</div> <!-- .summary -->
	</div>

	<div class="init-footer calendar">
	</div> <!-- .init-footer.calendar -->

	<div class="init-footer summary">
		<a href="#" class="btn-skip-init text-muted" data-dismiss="modal">SKIP</a>
		<button class="an-btn an-btn-primary btn-next-step pull-right">NEXT</button>
	</div> <!-- .init-footer.summary -->
</div> <!-- .init -->

<script>
var INIT_DATA = JSON.parse('{"current_step":4,"meetings":{"ggc123456789":{"owner":{"email":"baodg@gearinc.com","self":true},"members":["tungnt@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 1 - WWE Champions seminar","description":"WWE Champions seminar","scheduled_start_time":"2017-07-15 10:30:00","in":"90"},"ggc987654321":{"owner":{"email":"datls@gearinc.com","self":false},"members":["tungnt@gearinc.com","viethd@gearinc.com","baodg@gearinc.com"],"name":"Scopely meeting 2 - Coding email tool","description":"Coding email tool","scheduled_start_time":"2017-07-18 11:30:00","in":"180"},"ggc192837465":{"owner":{"email":"baodg@gearinc.com","self":true},"members":["tungnt@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 3 - Review code","description":"Review code","scheduled_start_time":"2017-08-15 10:26:00","in":"60"},"ggc101010101":{"owner":{"email":"tungnt@gearinc.com","self":false},"members":["baodg@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 4 - Finish project","description":"Finish project","scheduled_start_time":"2017-10-15 11:30:00","in":"30"}}}');  
</script>

<?php
	echo '<script type="text/javascript">' . $this->load->view('init_js', [
	], true) . '</script>';
?>
