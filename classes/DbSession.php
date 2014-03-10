<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	//change sessions to use db
	class DbSession implements SessionHandlerInterface {
		private $dbh;
	
		public function open($savePath, $sessionName) {
			global $dbh;
			$this->dbh = $dbh;
			return true;
		}

		public function close() {
			return true;
		}

		public function read($sessionID) {
			$sth = $this->dbh->prepare('SELECT sessionData FROM sessions WHERE sessionID = :sessionID');
			$sth->execute(array(':sessionID' => $sessionID));
			$result = $sth->fetchAll();
			if (count($result) == 1) {
				return $result[0]['sessionData'];
			}
			else {
				return '';
			}
		}

		public function write($sessionID, $sessionData) {
			$creationTime = time();
			$sth = $this->dbh->prepare('DELETE FROM sessions WHERE sessionID = :sessionID');
			$sth->execute(array(':sessionID' => $sessionID));
			$sth = $this->dbh->prepare('INSERT INTO sessions (sessionID, creationTime, sessionData) VALUES(:sessionID, :creationTime, :sessionData)');
			$sth->execute(array(':sessionID' => $sessionID, ':creationTime' => $creationTime, ':sessionData' => $sessionData));
			return true;
		}

		public function destroy($sessionID) {
			$sth = $this->dbh->prepare('DELETE FROM sessions WHERE sessionID = :sessionID');
			$sth->execute(array(':sessionID' => $sessionID));
			return true;
		}

		public function gc($maxlifetime) {
			$sth = $this->dbh->prepare('DELETE FROM sessions WHERE creationTime < UNIX_TIMESTAMP() - :maxlifetime');
			$sth->execute(array(':maxlifetime' => $maxlifetime));
			return true;
		}
	}
?>