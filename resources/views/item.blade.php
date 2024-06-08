@extends('app')

@section('title', '')

@section('content')
	<div id="topControls">
		<div id="topControlLeft"></div>
		<div id="topControlCenter">
			<a class="controlAdd addEnabled" href="#">Add</a>
			<a class="controlEdit editDisabled" href="#" title="Item is inactive and cannot be edited">Edit</a>
			<a class="controlDelete deleteDisabled" href="#" title="Item is inactive and cannot be deleted">Delete</a>
		</div>
		<div id="topControlRight"></div>
	</div>

	<div id="data">
			<h1>TBD</h1>
			@foreach ($formData as $key => $section)
				<section>
					<h2>{{ $key }}</h2>
					<div class="sectionData">
						@foreach ($section as $column)
							<dl>
							@foreach ($column as $field)
								<dt>{{ $fieldMapping[(str_contains($field, '.')) ? strstr($field, '.', true) : $field] }}</dt>
								<dd>{{ $data[$field] }}</dd>
							@endforeach
							</dl>
						@endforeach
					</div>
				</section>
			@endforeach
			<?php
				/*foreach ($TYPES[$_GET['type']]['formData'] as $key => $section) {
					echo '<section><h2>'.$key.'</h2><div class="sectionData">';
					foreach ($section as $column) {
						echo '<dl>';
						foreach ($column as $field) {
							echo '<dt>'.$TYPES[$_GET['type']]['fields'][$field]['formalName'].'</dt>';
							echo '<dd>'.$parsed[$field].'</dd>';
						}
						echo '</dl>';
					}
					echo '</div></section>';
				}

				$factoryItem = Factory::createItem($_GET['type']);
				echo $factoryItem->printItemBody($_GET['id']);
				echo $factoryItem->printAttachments($_GET['type'], $_GET['id']);*/
			?>
			<section>
				<h2>History</h2>
				<div class="sectionData">
					<table class="dataTable stripe row-border" id="historyTable">
						<thead>
							<tr>
								<th class="dateTimeHeader textLeft">Time</th>
								<th class="textLeft">Modified By</th>
								<th class="textLeft">Changes</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3" class="tableFooter">
									<a href="#">View All</a>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</section>
		</div>
@endsection
