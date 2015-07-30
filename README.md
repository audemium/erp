## Synopsis

Audemium ERP is a free and open-source Enterprise Resource Planning system for small businesses.  It provides essential features in a clean and intuitive interface, and includes comprehensive search functionality.

It is currently under development and isn't ready for production systems.  ONLY USE THIS IF YOU KNOW WHAT YOU ARE DOING.

## Installation

1. Download Audemium ERP and copy it to your web server.
2. Configure your web server to deny access to the attachments directory.  In Apache, this could be something like:
	<DirectoryMatch "attachments">
		Order allow,deny
		Deny from all
	</DirectoryMatch>
3. Update settings.php with your database information, and change any other settings as desired.
4. Run install.sql to set up the database and populate with starter data.  Delete install.sql afterwards.  Typically, the command to run it will be something like: mysql -u user -pPassword < /var/www/install.sql
5. Open a browser to the site where you placed Audemium ERP and log on using the default credentials.  User: fs1  Password: userPasswordHere

## Planned Features

* Employee Time system (shift planning, paycheck tracking, etc.)
* Cash register interface
* Charts for Net Income / Income / Expenses
* Access controls

## License

Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.  See LICENSE.txt for a full copy of the GNU Affero General Public License.