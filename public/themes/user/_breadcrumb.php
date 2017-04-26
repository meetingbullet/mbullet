<?php
if (($this->uri->segment(1) == 'projects') OR ($this->uri->segment(1) == 'action') OR ($this->uri->segment(1) == 'step') OR ($this->uri->segment(1) == 'task')) {
?>
				<div class="an-breadcrumb wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
					<ol class="breadcrumb">
						<li><a href="#">Home</a></li>
						<li><a href="#">Admin Panel</a></li>
						<li class="active">Dashboard</li>
					</ol>
				</div>
<?php
}
?>