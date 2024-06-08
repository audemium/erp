@extends('app')

@section('title', '')

@section('content')
	<div id="topControls">
		<div id="topControlLeft">
			<input type="text" id="filter" placeholder="Filter">
		</div>
		<div id="topControlCenter">
			<a class="controlAdd addEnabled" href="#">Add</a>
			<a class="controlEdit editDisabled" href="#" title="Select one or more rows to edit">Edit</a>
			<a class="controlDelete deleteDisabled" href="#" title="Select one or more rows to delete">Delete</a>
		</div>
		<div id="topControlRight">
			<a class="settings" href="#"></a>
		</div>
	</div>

	<table id="itemTable" class="stripe row-border">
		<thead>
			<tr>
				<th></th>
				@foreach ($columns as $column)
					<th>{{ $fieldMapping[(str_contains($column, '.')) ? strstr($column, '.', true) : $column] }}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@foreach ($data as $item)
				<tr>
					<td class="selectCol"><input type="checkbox" class="selectCheckbox" id="{{ $item['id'] }}"></td>
					@foreach ($columns as $column)
						<td>
						@if (is_array($item[$column]))
							<a href="{{ $item[$column][0] }}">{{ $item[$column][1] }}</a>
						@else
							{{ $item[$column] }}
						@endif
						</td>
					@endforeach
				</tr>
			@endforeach
		</tbody>
	</table>
@endsection
