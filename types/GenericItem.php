<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	class GenericItem {
		public function printItemBody($id) {
			return '';
		}
		
		public function printAttachments($type, $id) {
			global $dbh;
			global $SETTINGS;
			$return = '';
			$allowsAttachments = in_array($type, $SETTINGS['attachments']);
			
			//anything that has an attachment, show it with a delete option
			$sth = $dbh->prepare(
				'SELECT attachmentID, employeeID, uploadTime, name, extension
				FROM attachments
				WHERE type = :type AND id = :id');
			$sth->execute([':type' => $type, ':id' => $id]);
			$result = $sth->fetchAll();
			$hasAttachments = (count($result) > 0) ? true : false;
			
			//if type currently allows attachments OR already has attachments, build the section
			if ($allowsAttachments || $hasAttachments) {
				$addStr = ($allowsAttachments == true) ? 'class="controlAdd addEnabled" href="#"' : 'class="controlAdd addDisabled" href="#" title="This item type is not currently configured to allow attachments."';
				$return = '<section>
					<h2>Attachments</h2>
					<div class="sectionData">
						<div class="customAddLink" id="addAttachment"><a '.$addStr.'>Add Attachment</a></div>
						<table class="attachmentTable" style="width:100%;">
							<thead>
								<tr>
									<th>Attachment</th>
									<th>Added By</th>
									<th>Uploaded</th>
									<th></th>
								</tr>
							</thead>
							<tbody>';
								if ($hasAttachments) {
									foreach ($result as $row) {
										$return .= '<tr><td><a href="attachment.php?id='.$row['attachmentID'].'">'.$row['name'].'.'.$row['extension'].'</a></td>';
										$return .= '<td>'.getLinkedName('employee', $row['employeeID']).'</td>';
										$return .= '<td>'.formatDateTime($row['uploadTime']).'</td>';
										$return .= '<td class="textCenter"><a class="controlDelete deleteEnabled" href="#" data-id="'.$row['attachmentID'].'"></a></td></tr>';
									}
								}
							$return .= '</tbody>
						</table>
					</div>
				</section>';
			}
			
			return $return;
		}
		
		public function getName($type, $id) {
			global $dbh;
			global $TYPES;
			
			$sth = $dbh->prepare(
				'SELECT name
				FROM '.$TYPES[$type]['pluralName'].'
				WHERE '.$TYPES[$type]['idName'].' = :id');
			$sth->execute([':id' => $id]);
			$row = $sth->fetch();
			
			return $row['name'];
		}
		
		public function parseValue($type, $item) {
			return $item;
		}
		
		public function parseSubTypeValue($subType, $action, $item, $format) {
			//this should either be overridden by a type, or just not used at all, but it's here just in case
			if ($format == 'arr') {
				return $item;
			}
			else {
				$parsed = '';
				foreach ($item as $key => $value) {
					$parsed .= '<b>'.$key.':</b> '.$value.' ';
				}
				return $parsed;
			}
		}
		
		public function generateTypeOptions($type) {
			global $dbh;
			global $TYPES;
			$return = [];
			
			$sth = $dbh->prepare(
				'SELECT '.$TYPES[$type]['idName'].', name
				FROM '.$TYPES[$type]['pluralName'].'
				WHERE active = 1
				ORDER BY name');
			$sth->execute();
			while ($row = $sth->fetch()) {
				$return[] = [$row[$TYPES[$type]['idName']], $row['name']];
			}
			
			return $return;
		}
		
		public function customAjax($id, $data) {
			return $return = ['status' => 'success'];
		}
		
		public function printPopups() {
			return '';
		}
	}
?>