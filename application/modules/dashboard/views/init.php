<div class="init">
	<div class="init-nav calendar">
		<div class="step passed">
			Setup
			<div class="transitor"></div>
		</div>
		<div class="step passed">
			Import
			<div class="transitor"></div>
		</div>
		<div class="step my-meeting">
			<span class="text-center">
				My Meeting
				<p class="sub-step">
					<span class="dot passed"></span>
					<span class="dot"></span>
					<span class="dot"></span>
				</p>
			</span> 
			<div class="transitor"></div>
		</div>
		<div class="step">
			Projects
			<div class="transitor"></div>
		</div>
		<div class="step">
			Team
			<div class="transitor"></div>
		</div>
		<div class="step">Finish</div>
	</div>

	<div class="init-nav summary">
		<h4>
			<i class="ion-ios-cloud-download-outline"></i> 
			<span class="title">Import Summary</span>
		</h4>
	</div>

	<div class="clear"></div>

	<div class="init-body">
		<div class="calendar">
			<div class="calendar-info">
				<div class="wrapper">
					<h6 class="title">Showing Calendar for user</h6>
					<div class="user">
						<div class="avatar" style="float:left; background-image:url('<?php echo avatar_url($current_user->avatar, $current_user->email) ?>')"></div>
						<div class="info">
							<span class="name"><strong><?php echo $current_user->first_name . ' ' . $current_user->last_name ?></strong></span><br/>
							<span class="email"><?php echo $current_user->email ?></span>
						</div>
					</div>
				</div>

				<div class="wrapper">
					<h6 class="title">Import your Calendar Events</h6>

					<div class="an-input-group group-range" title="Select events from before and after Today's date" data-toggle="tooltip">
						<div class="an-input-group-addon text">Before</div>
						<input type="number" min="0" step="1" value="90" name="before" class="an-form-control event-range text-right">
						<div class="an-input-group-addon text">after</div>
						<input type="number" min="0" step="1" value="90" name="after" class="an-form-control event-range text-right">
						<div class="an-input-group-addon text">days</div>
					</div>

					<button class="an-btn an-btn-primary btn-reload-calendar">
						<i class="ion-loop"></i>
					</button>

					<p>Showing results for 
						<strong class="text-range">
							<?php echo display_time( date('Y-m-d H:i:s', strtotime('-90 days') ), null, 'M j, Y') ?> - 
							<?php echo display_time( date('Y-m-d H:i:s', strtotime('+90 days') ), null, 'M j, Y') ?>
						</strong>
					</p>
				</div>

				<div class="wrapper">
					<div class="fc-toolbar fc-header-toolbar">
						<h3 id="calendar-init-title"></h3>

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
			S<br/>o<br/>m<br/>e<br/> <br/>o<br/>t<br/>h<br/>e<br/>r<br/> <br/>a<br/>m<br/>a<br/>z<br/>i<br/>n<br/>g<br/> <br/>c<br/>o<br/>n<br/>t<br/>e<br/>n<br/>t<br/>
		</div> <!-- .summary -->
	</div>

	<div class="init-footer calendar">
	</div> <!-- .init-footer.calendar -->

	<div class="init-footer summary">

	</div> <!-- .init-footer.summary -->
</div> <!-- .init -->

<script>
var INIT_DATA = JSON.parse('{"current_step":4,"meetings":{"ggc123456789":{"owner":{"email":"baodg@gearinc.com","self":true},"members":["tungnt@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 1 - WWE Champions seminar","description":"WWE Champions seminar","scheduled_start_time":"2017-07-15 10:30:00","in":"90"},"ggc987654321":{"owner":{"email":"datls@gearinc.com","self":false},"members":["tungnt@gearinc.com","viethd@gearinc.com","baodg@gearinc.com"],"name":"Scopely meeting 2 - Coding email tool","description":"Coding email tool","scheduled_start_time":"2017-07-18 11:30:00","in":"180"},"ggc192837465":{"owner":{"email":"baodg@gearinc.com","self":true},"members":["tungnt@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 3 - Review code","description":"Review code","scheduled_start_time":"2017-08-15 10:26:00","in":"60"},"ggc101010101":{"owner":{"email":"tungnt@gearinc.com","self":false},"members":["baodg@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 4 - Finish project","description":"Finish project","scheduled_start_time":"2017-10-15 11:30:00","in":"30"}}}');  
</script>

<?php
	echo '<script type="text/javascript">' . $this->load->view('init_js', [
	], true) . '</script>';
?>
