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
			<div class="step-32">
				<div class="content">
					<h3 class="section no-margin-top">Setup Meeting</h3>

					<div class="table-improve-meeting">
						<table class="table">
							<thead>
								<tr>
									<th>Meeting</th>
									<th class="text-center">Date</th>
									<th class="text-center">Time</th>
									<th class="text-center">Team</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="name"></td>
									<td class="date text-center"></td>
									<td class="time text-center"></td>
									<td class="team text-center"></td>
								</tr>
							</tbody>
						</table>
						<strong class="meeting-cost">
							<span class="mic">Meeting Investment Cost</span> <span class="hour">x hrs</span> x <span class="total-participant">x participants</span>=<span class="total-hour">x hrs</span>
						</strong>
					</div>
				</div>

				<a href="#" class='btn-wide selected passed' data-type='goal'><i class="ion-ios-flag-outline"></i> Goal (<span>0</span>) <i class="pull-right ion-ios-arrow-forward"></i></a>
				<div class="table-wrapper goal">
					<table class="table table-goal">
						<thead>
							<tr>
								<th>Goal</th>
								<th class="text-center">Importance</th>
								<th class="text-center">Type</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
				<a href="#" class='btn-wide' data-type='homework'><i class="ion-ios-filing-outline"></i> Todo (<span>0</span>) <i class="pull-right ion-ios-arrow-forward"></i></a>
				<div class="table-wrapper homework">
					<table class="table table-homework">
						<thead>
							<tr>
								<th>To Do</th>
								<th class="text-center">Attachments</th>
								<th class="text-center"><i class="ion-android-people"></i></th>
								<th class="text-center"><i class="ion-android-alarm-clock"></i></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
				<a href="#" class='btn-wide' data-type='agenda'><i class="ion-ios-paper-outline"></i> Agenda (<span>0</span>) <i class="pull-right ion-ios-arrow-forward"></i></a>
				<div class="table-wrapper agenda">
					<table class="table table-agenda">
						<thead>
							<tr>
								<th>Agenda</th>
								<th class="text-center"><i class="ion-android-people"></i></th>
								<th class="text-center"><i class="ion-android-alarm-clock"></i></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
				<a href="#" class='btn-wide' data-type='team'><i class="ion-ios-people-outline"></i> Team (<span>0</span>) <i class="pull-right ion-ios-arrow-forward"></i></a>
			</div>
			<div class="calendar-of-shadow"></div>
			<div class="calendar-wrapper">
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
			</div>
		</div> <!-- .calendar -->
		<div class="config">
			<div class="content-container ">
				<div class="config-content"></div>
			</div>
		</div>
		<div class="summary">
			<div class="bubba-tea"></div>
			
			<div class="step-10">
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
					Let us help you start getting the most out of your 
					investment in meetings! We'll start with your meeting 
					as the Owner.
				</div>
			</div> <!-- .step-10 -->

			<div class="step-30">
				<div class="owner">
					<p class="content">
						Let's start improving events in your calendar that your 
						are the owner and converting it into a Meeting Bullet event.
					</p>

					<div class="instruction">
						<h3 class="section">
							Select a Meeting you want to improve
						</h3>
						<p class="section-sub">
							<i class="ion-arrow-left-a"></i> 
							Click an event on your calendar to the left
						</p>
					</div>

					<div class="content table-improve-meeting" style="display:none">
						<table class="table">
							<thead>
								<tr>
									<th>Meeting</th>
									<th class="text-center">Date</th>
									<th class="text-center">Time</th>
									<th class="text-center">Team</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="name"></td>
									<td class="date text-center"></td>
									<td class="time text-center"></td>
									<td class="team text-center"></td>
								</tr>
							</tbody>
						</table>
						<strong class="meeting-cost">
							<span class="mic">Meeting Investment Cost</span> 
							<span class="hour">x hrs</span> x 
							<span class="total-participant">x participants</span>=
							<span class="total-hour">x hrs</span>
						</strong>

						<div class="bigest-challenge" style="display: none">
							<p>To help us, help your meeting improve, tell us:</p>
							<h3 class="section">
								What are the biggest challenges you typically 
								have as a meeting organizer?
							</h3>
							<p>Select the response that best suites your feeling</p>

							<ul class="answers">
								<li class="answer" data-answer="1">
									<i class="ion-ios-flag-outline"></i>
									Defining a clear goal that guests understand and respect
								</li>
								<li class="answer" data-answer="2">
									<i class="ion-ios-filing-outline"></i>
									My guests are unprepared before the meeting starts
								<li class="answer" data-answer="3">
									<i class="ion-ios-time-outline"></i>
									Meeting time is wasted not adhering to an agenda
								</li>
								<li class="answer" data-answer="4">
									<i class="ion-ios-paper-outline"></i>
									My guests are not effective note takers
								</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="guest">
					<p class="content">
						Let's look at the events in your clendar that you're 
						attending as guest & let the owner know how you feel 
						about the meeting.
					</p>

					<div class="instruction">
						<h3 class="section">Select a Meeting you want to improve</h3>
						<p class="section-sub"><i class="ion-arrow-left-a"></i> Click an event on your calendar to the left</p>
					</div>

					<div class="table-rate" style="display: none">
						<table class="table">
							<thead>
								<tr>
									<th class="text-center">
										Select the response that best suites your feeling
									</th>
									<th class="text-center">Rate</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>
										<i class="ion-ios-paper-outline"></i>
										Meeting
									</td>
									<td class="text-center">
										<div class="todo-rating">
											<input type="radio" id="meeting-star-5" name="meeting" value="5" /><label class="full" for="meeting-star-5"></label>
											<input type="radio" id="meeting-star-4" name="meeting" value="4" /><label class="full" for="meeting-star-4"></label>
											<input type="radio" id="meeting-star-3" name="meeting" value="3" /><label class="full" for="meeting-star-3"></label>
											<input type="radio" id="meeting-star-2" name="meeting" value="2" /><label class="full" for="meeting-star-2"></label>
											<input type="radio" id="meeting-star-1" name="meeting" value="1" /><label class="full" for="meeting-star-1"></label>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<i class="ion-ios-filing-outline"></i>
										Prep
									</td>
									<td class="text-center">
										<div class="todo-rating">
											<input type="radio" id="homework-star-5" name="homework" value="5" /><label class="full" for="homework-star-5"></label>
											<input type="radio" id="homework-star-4" name="homework" value="4" /><label class="full" for="homework-star-4"></label>
											<input type="radio" id="homework-star-3" name="homework" value="3" /><label class="full" for="homework-star-3"></label>
											<input type="radio" id="homework-star-2" name="homework" value="2" /><label class="full" for="homework-star-2"></label>
											<input type="radio" id="homework-star-1" name="homework" value="1" /><label class="full" for="homework-star-1"></label>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<i class="ion-ios-list-outline"></i>
										Agenda
									</td>
									<td class="text-center">
										<div class="todo-rating">
											<input type="radio" id="agenda-star-5" name="agenda" value="5" /><label class="full" for="agenda-star-5"></label>
											<input type="radio" id="agenda-star-4" name="agenda" value="4" /><label class="full" for="agenda-star-4"></label>
											<input type="radio" id="agenda-star-3" name="agenda" value="3" /><label class="full" for="agenda-star-3"></label>
											<input type="radio" id="agenda-star-2" name="agenda" value="2" /><label class="full" for="agenda-star-2"></label>
											<input type="radio" id="agenda-star-1" name="agenda" value="1" /><label class="full" for="agenda-star-1"></label>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div> <!-- .step-30 -->

			<div class="step-32-sub content">
				<div class="define define-goal">
					<h3 class="section no-margin-top">Define a Goal</h3>
					<p style="text-align: justify">Having a <strong class="primary">Goal/Objective</strong> for
					a meeting is priority 1 for defining the purpose of a meeting, 
					other-wise the meeting is a meeting for the sake of a meeting...
					which is boring and expensive.</p>

					<h3 class="section">
						<i class="ion-ios-flag-outline"></i>
						Goals
					</h3>
					<table class="table table-goal">
						<thead>
							<tr>
								<th>Goal</th>
								<th class="text-center">Importance</th>
								<th class="text-center">Type</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>

					<form>
						<label for="goal-name">Add new Goal</label>
						<input id="goal-name" name="name" class="an-form-control" type="text" placeholder="Define your Goal/Objective">

						<div class="row">
							<div class="col-md-6 col-xs-12">
								<label for="goal-type">Type</label>
								<select id="goal-type" name="type" class="an-form-control">
									<option disabled selected>(Select)</option>
									<option value="decide">Decide</option>
									<option value="plan">Plan</option>
									<option value="prioritize">Prioritize</option>
									<option value="assess">Assess</option>
									<option value="review">Review</option>
								</select>
							</div>
							<div class="col-md-6 col-xs-12">
								<label for="goal-importance">Importance</label>
								<select id="goal-importance" name="importance" class="an-form-control">
									<option disabled selected>(Select)</option>
									<option value="urgent">Urgent/Critical</option>
									<option value="required">Requried for [Sprint]</option>
									<option value="requested">Requested for [Sprint]</option>
									<option value="backlog">Backlog for [Project]</option>
									<option value="other">Other [Fill in Blank]</option>
								</select>
							</div>
						</div>

						<div class="pull-right">
							<button class="an-btn an-btn-primary btn-create-goal">Create</button>
						</div>

						<div class="clear"></div>
					</form>
				</div> <!-- .define-goal -->

				<div class="define define-homework" style="display: none">
					<h3 class="section no-margin-top">Create To Do List</h3>
					<p style="text-align: justify">Guests unprepared for meetings
						that benefit from readliness is a sure fire way to
						"spin wheel" and get nowhere. Let's define some required 
						<strong class="primary">Homework</strong> for guests prior 
						to starting a meeting</p>

					<h3 class="section">
						<i class="ion-ios-filing-outline"></i>
						Homework
					</h3>
					<table class="table table-homework">
						<thead>
							<tr>
								<th>To Do</th>
								<th class="text-center">Attachments</th>
								<th class="text-center"><i class="ion-android-people"></i></th>
								<th class="text-center"><i class="ion-android-alarm-clock"></i></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>

					<form>
						<div class="row">
							<div class="col-md-8 col-xs-12">
								<label for="todo-name">Add new To Do</label>
								<input id="todo-name" name="name" class="an-form-control" type="text" placeholder="Describe Homework/Prerequisite">
							</div>
							<div class="col-md-4 col-xs-12">
								<label for="todo-time-spent"><i class="ion-android-alarm-clock"></i> Time Cost</label>
								<div class="input-group">
									<input type="text" id="time-spent" class="form-control time-spent" name="time_spent" min="0">
									<div class="input-group-btn">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Hours <span class="caret"></span></button>
										<ul class="dropdown-menu dropdown-menu-right">
											<li><a href="#" data-type="minute">Minutes</a></li>
											<li><a href="#" data-type="hour">Hours</a></li>
											<li><a href="#" data-type="day">Days</a></li>
											<li><a href="#" data-type="week">Weeks</a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>

						<label for="todo-assignee">Assignees</label>
						<input id="todo-assignee" name="assignee" type="text">

						<label for="todo-attachment">Attachments</label>
						<div class="init-attachment-container">
							<input name="todo-attachment" class="an-form-control input-attachment" type="file">
						</div>

						<div class="pull-right">
							<button class="an-btn an-btn-primary btn-create-todo">Create</button>
						</div>

						<div class="clear"></div>
					</form>
				</div> <!-- .define-homework -->

				<div class="define define-agenda" style="display: none">
					<h3 class="section no-margin-top">Create an Agenda</h3>
					<p style="text-align: justify">A solid <strong class="primary">agenda</strong>
						will help keep the meeting time well spent and on target to adhere 
						to the meeting goal. Agenda-less meetings are far more likely to get 
						sidetracked with unrelated topics and derail your meeting.
					</p>
					<p>Let's set an agenda and resolve that.</p>

					<h3 class="section">
						<i class="ion-ios-filing-outline"></i>
						Agenda
					</h3>
					<table class="table table-agenda">
						<thead>
							<tr>
								<th>Agenda</th>
								<th class="text-center"><i class="ion-android-people"></i></th>
								<th class="text-center"><i class="ion-android-alarm-clock"></i></th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>

					<form>
						<label for="agenda-name">Add new Agenda</label>
						<input id="agenda-name" name="name" class="an-form-control" type="text" placeholder="Describe your Goal/Objective">

						<label for="agenda-assignee">Assignees</label>
						<input id="agenda-assignee" name="assignee" type="text">

						<label for="agenda-attachment">Attachments</label>
						<div class="init-attachment-container">
							<input name="agenda-attachment" class="an-form-control input-attachment" type="file">
						</div>

						<div class="pull-right">
							<button class="an-btn an-btn-primary btn-create-agenda">Create</button>
						</div>

						<div class="clear"></div>
					</form>
				</div> <!-- .define-agenda -->

				<div class="define define-team" style="display: none">
					<h3 class="section no-margin-top">Confirm your guest [TEAM]</h3>
					<p style="text-align: justify">
						A meeting is only valuable if the right guests are 
						invited to attend it and benefit. Your guests are your 
						<strong class="primay">Team</strong>, and without them 
						the meeting has no value, but inviteing unecessary guests 
						to your team will increase the cost of the meeting.
					</p>
					<p>
						Remember to keep your 
						<strong class="primary">Goal & Agenda</strong> 
						in mind when you invite guests to your team so you get 
						the most our of your meetings!
					</p>

					<h3 class="section">
						<i class="ion-ios-filing-outline"></i>
						Agenda
					</h3>
					<table class="table table-team">
						<thead>
							<tr>
								<th>Team</th>
								<th class="text-center">Remove</th>
							</tr>
						</thead>
						<tbody>

						</tbody>
					</table>
				</div>
			</div>
		</div> <!-- .summary -->
	</div>

	<div class="init-footer calendar">
	</div> <!-- .init-footer.calendar -->

	<div class="init-footer summary">
		<a href="#" class="btn-skip-init text-muted" data-dismiss="modal">SKIP</a>
		<button class="an-btn an-btn-primary btn-next-step pull-right" disabled="disabled">NEXT</button>
	</div> <!-- .init-footer.summary -->
</div> <!-- #init -->

<div class="step-20">
	<div class="block-wrapper">
		<div class="block choose-your-path">
			<h3 class="title">Choose your Path</h4>
			<p>Begin your <b>ATTACK</b> on Meeting Drag!<br>
			Start as a...
			</p>
		</div>
	</div>
	<div class="block-wrapper">
		<div class="block path guest">
			<div class="content">
				<h3 class="title">
					<i class="ion-android-people"></i>
					Participant/Guest
				</h4>

				<p>You loath the meetings you attend, but you are just a cog in a great machine; Biting your tongue in silence...</p>
				<img class="thumbnail" src="<?php echo img_path() . '/path-guest.jpg' ?>" alt="Participant/Guest">
				<p>...until now!</p>

				<div class="center">
					<button class="an-btn an-btn-primary rounded btn-underdog">
						<img src="<?php echo img_path() . '/thinking_face.png' ?>"  alt="" class="emoji">
						The Underdog!
					</button>
					<p class="subtitle">Let's do this</p>
				</div>
			</div>
		</div>
		<div class="block path owner">
			<div class="content">
				<h3 class="title">
					<i class="ion-ios-contact"></i>
					Organizer/Owner
				</h4>

				<p>You know your meetings suck, but your mamma says you are special and your boss bombs all your meetings anyway...</p>
				<img class="thumbnail" src="<?php echo img_path() . '/path-owner-2.png' ?>" alt="Organizer/Owner">
				<p>...until now!</p>

				<div class="center">
					<button class="an-btn an-btn-primary rounded btn-like-a-boss">
						<img src="<?php echo img_path() . '/sunglasses.png' ?>"  alt="" class="emoji">
						Like a Boss!
					</button>
					<p class="subtitle">Let's do this</p>
				</div>
			</div>
		</div>
	</div>
</div> <!-- #step-20 -->


<!--
<script>
var INIT_DATA={"currentStep":4,"meetings":{"ggc123456789":{"owner":{"email":"baodg@gearinc.com","self":true},"members":["tungnt@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 1 - WWE Champions seminar","description":"WWE Champions seminar","scheduled_start_time":"2017-07-15 10:30:00","in":"90"},"ggc987654321":{"owner":{"email":"datls@gearinc.com","self":false},"members":["tungnt@gearinc.com","viethd@gearinc.com","baodg@gearinc.com"],"name":"Scopely meeting 2 - Coding email tool","description":"Coding email tool","scheduled_start_time":"2017-07-18 11:30:00","in":"180"},"ggc192837465":{"owner":{"email":"baodg@gearinc.com","self":true},"members":["tungnt@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 3 - Review code","description":"Review code","scheduled_start_time":"2017-08-15 10:26:00","in":"60"},"ggc101010101":{"owner":{"email":"tungnt@gearinc.com","self":false},"members":["baodg@gearinc.com","viethd@gearinc.com","datls@gearinc.com"],"name":"Scopely meeting 4 - Finish project","description":"Finish project","scheduled_start_time":"2017-10-15 11:30:00","in":"30"}}};
</script>
-->
<?php
	echo '<script type="text/javascript">' . $this->load->view('init_js', [
	], true) . '</script>';
?>