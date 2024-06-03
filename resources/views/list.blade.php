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
				@foreach ($columns as $column)
					<th>{{ $column }}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@foreach ($data as $item)
				@foreach ($columns as $column)
					<th>{{ $item[$column] }}</th>
				@endforeach
			@endforeach
		</tbody>
	</table>
@endsection
