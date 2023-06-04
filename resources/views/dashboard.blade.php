@extends('app')

@section('title', 'Dashboard')

@section('content')
    <?php
			//TODO: get user's module setup from the db
			$modules = [['unpaidBills', 'unpaidOrders', 'unapprovedTimesheets', 'recentTransactions'], ['netIncome', 'income', 'expenses']];
		?>
		<div id="col1">
			<?php
				foreach ($modules[0] as $module) {
					echo '<div class="module" id="'.$module.'">';
						echo '<h2></h2>';
						echo '<div class="moduleData"></div>';
					echo '</div>';
				}
			?>
		</div>
		<div id="col2">
			<?php
				foreach ($modules[1] as $module) {
					echo '<div class="module" id="'.$module.'">';
						echo '<h2></h2>';
						echo '<div class="moduleData"></div>';
					echo '</div>';
				}
			?>
		</div>
		<div style="clear:both;"></div>
@endsection
