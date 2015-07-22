<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	require_once('init.php');
	
	//make sure the id is an int, because we're accessing the filesystem with it
	if (filter_var($_GET['id'], FILTER_VALIDATE_INT, ['min_range' => 1, 'max_range' => 4294967295])) {
		$sth = $dbh->prepare(
			'SELECT name, extension, mime
			FROM attachments
			WHERE attachmentID = :id');
		$sth->execute([':id' => $_GET['id']]);
		$row = $sth->fetch();
		
		header('Content-Disposition: attachment; filename="'.$row['name'].'.'.$row['extension'].'"');
		header('Content-type: '.$row['mime']);
		readfile('attachments/'.$_GET['id']);
	}
?>