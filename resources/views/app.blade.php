<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <title>@yield('title')</title>
    <link rel="icon" href="images/favicon-16x16.png" type="image/png">
	<link type="text/css" rel="stylesheet" href="css/styles.css">
  </head>
  <body>
	<header>
		<img id="logo" src="images/logo.png" alt="logo">
		<div id="accountBar"><a href="account.php" style="margin-right:20px;">Account Settings</a> <a href="logout.php">Sign out</a></div>
	</header>
	<nav>
		<div id="searchDiv">
			<input type="text" id="searchTerm" placeholder="Search">
			<div id="searchResults">Type to begin searching...</div>
		</div>
		<ul>
			<li id="search">
				<div id="searchLink"><div style="background-image:url('images/icons/search.png');" title="Search"></div></div>
			</li>
			<li id="index">
				<a href="/"><div style="background-image:url('images/icons/home.png');" title="Home"></div></a>
			</li>
			@foreach (config('audemium.types') as $key => $value)
				<li id="{{$key}}">
					<a href="/{{$value['pluralName']}}">
						<div style="background-image:url('images/icons/{{$key}}.png');" title="{{$value['formalPluralName']}}"></div>
					</a>
				</li>
			@endforeach
		</ul>
	</nav>
	<main>
		@yield('content')
	</main>
	<footer>
		Powered by <a href="https://www.audemium.com">Audemium ERP</a>
	</footer>
  </body>
</html>
