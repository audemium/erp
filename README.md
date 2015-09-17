## Synopsis

Audemium ERP is a free and open-source Enterprise Resource Planning system for small businesses. It provides essential features in a clean and intuitive interface, and includes comprehensive search functionality.

It is currently under development and isn't ready for production systems. ONLY USE THIS IF YOU KNOW WHAT YOU ARE DOING.

## Installation

1. Download Audemium ERP and copy it to your web server.
2. Configure your web server to deny access to the attachments directory. In Apache, this could be something like:
	<DirectoryMatch "attachments">
		Order allow,deny
		Deny from all
	</DirectoryMatch>
3. Add a cron job to run cron.php once a day. In crontab, this would be something like:
	1 2 * * * php /var/www/erp/cron.php
4. Open a browser to the site where you placed Audemium ERP. This will redirect you to the installer. Enter your information there and click Install.

## Updating

1. Create a backup of your files and database.
2. Download Audemium ERP and copy it to your web server, except for the attachments folder.
3. In a browser or on the command line, run maintenance/update.php.
4. You may need to add any new settings to your settings.php file. Copy any new lines from maintenance/settings.php, but don't overwrite your settings.php with the default one.
5. Delete the maintenance folder from the server.

## Planned Features

* Employee Time system (shift planning, paycheck tracking, etc.)
* Cash register interface
* Charts for Net Income / Income / Expenses
* Access controls

## License

Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version. See LICENSE.txt for a full copy of the GNU Affero General Public License.