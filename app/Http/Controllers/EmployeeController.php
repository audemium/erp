<?php

namespace App\Http\Controllers;

use App\Models\Employee;

use Illuminate\Support\Arr;
use Illuminate\View\View;

class EmployeeController extends GenericController {
	public static $metadata = [
		'pluralName' => 'employees',
		'formalName' => 'Employee',
		'formalPluralName' => 'Employees',
		'idName' => 'employeeID',
		'formData' => [
			'Basic Information' => [
				['firstName', 'lastName', 'payType', 'payAmount', 'workEmail'],
				['location.name', 'position.name', 'managerID', 'vacationTotal']
			],
			'Personal Information' => [
				['address', 'city'],
				['state', 'zip']
			]
		],
		'fields' => [
			'username' => [
				'formalName' => 'Username',
				'typeData' => ['str', 100],
				'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
			],
			'firstName' => [
				'formalName' => 'First Name',
				'typeData' => ['str', 200],
				'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
			],
			'lastName' => [
				'formalName' => 'Last Name',
				'typeData' => ['str', 200],
				'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
			],
			'payType' => [
				'formalName' => 'Pay Type',
				'typeData' => ['opt', ['S', 'H']],
				'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
			],
			'payAmount' => [
				'formalName' => 'Pay Amount',
				'typeData' => ['dec', [12, 2]],
				'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
			],
			'location' => [
				'formalName' => 'Location',
				'typeData' => ['id', 'location'],
				'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
			],
			'position' => [
				'formalName' => 'Position',
				'typeData' => ['id', 'position'],
				'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
			],
			'managerID' => [
				'formalName' => 'Manager',
				'typeData' => ['id', 'employee'],
				'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
			],
			'vacationTotal' => [
				'formalName' => 'Total Vacation (hours)',
				'typeData' => ['int', 4294967295],
				'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
			],
			'address' => [
				'formalName' => 'Address',
				'typeData' => ['str', 200],
				'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
			],
			'city' => [
				'formalName' => 'City',
				'typeData' => ['str', 200],
				'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
			],
			'state' => [
				'formalName' => 'State',
				'typeData' => ['str', 2],
				'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
			],
			'zip' => [
				'formalName' => 'Zip Code',
				'typeData' => ['str', 10],
				'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
			],
			'workEmail' => [
				'formalName' => 'Email',
				'typeData' => ['email'],
				'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
			]
		],
		'subTypes' => [
			'attachment' => [
				'fields' => [
					'name' => [
						'formalName' => 'Name',
						'typeData' => ['str', 200],
						'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
					],
					'extension' => [
						'formalName' => 'Extension',
						'typeData' => ['str', 10],
						'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
					]
				]
			]
		]
	];

	public static $fieldMapping = [
		'name' => 'Name',
		'username' => 'Username',
		'firstName' => 'First Name',
		'lastName' => 'Last Name',
		'payType' => 'Pay Type',
		'payAmount' => 'Pay Amount',
		'location' => 'Location',
		'position' => 'Position',
		'managerID' => 'Manager',
		'vacationTotal' => 'Total Vacation (hours)',
		'address' => 'Address',
		'city' => 'City',
		'state' => 'State',
		'zip' => 'Zip',
		'workEmail' => 'Email',
	];

	public function list(): View {
		/*//check to see if the user has a custom config, otherwise use default
		$sth = $dbh->prepare(
			'SELECT columnOrder
			FROM columns
			WHERE type = :type AND employeeID = :employeeID');
		$sth->execute([':type' => $_GET['type'], ':employeeID' => $_SESSION['employeeID']]);
		$result = $sth->fetchAll();
		if (count($result) > 0) {
			$columns = explode(',', $result[0]['columnOrder']);
		}
		else {
			$columns = $SETTINGS['columns'][$_GET['type']];
		}*/
		$columns = config('audemium.columns.employees');
		foreach ($columns as $column) {
			$column = (str_contains($column, '.')) ? strstr($column, '.', true) : $column;
		}


		/*$sth = $dbh->prepare(
			'SELECT *
			FROM '.$TYPES[$_GET['type']]['pluralName'].'
			WHERE active = 1');
		$sth->execute();
		while ($row = $sth->fetch()) {
			$id = $row[$TYPES[$_GET['type']]['idName']];
			$item = parseValue($_GET['type'], $row);

			echo '<tr><td class="selectCol"><input type="checkbox" class="selectCheckbox" id="'.$id.'"></td>';
			foreach ($columns as $column) {
				if ($column == 'name' || $column == 'orderID' || $column == 'expenseID') {
					$temp = getLinkedName($_GET['type'], $id);
				}
				else {
					$temp = $item[$column];
				}
				echo '<td>'.$temp.'</td>';
			}
			echo '</tr>';
		}*/
		$data = [];
		foreach (Employee::all() as $employee) {
			$row['id'] = $employee->id;

			foreach ($columns as $column) {
				if (str_contains($column, '.')) {
					[$class, $property] = explode('.', $column);
					$row[$column] = $employee->$class->$property;
				}
				else {
					if ($column == 'name') {
						$row[$column] = [$employee->url, $employee->$column];
					}
					else {
						$row[$column] = $employee->$column;
					}
				}
			}

			$data[] = $row;
		}

		return view('list', [
			'columns' => $columns,
			'fieldMapping' => self::$fieldMapping,
			'data' => $data
		]);
	}

	public function show($id): View {
		$employee = Employee::find($id);
		$data = [];

		foreach (Arr::flatten(self::$metadata['formData']) as $column) {
			if (str_contains($column, '.')) {
				[$class, $property] = explode('.', $column);
				$data[$column] = $employee->$class->$property;
			}
			else {
				if ($column == 'name') {
					$data[$column] = [$employee->url, $employee->$column];
				}
				else {
					$data[$column] = $employee->$column;
				}
			}
		}

		return view('item', [
			'formData' => self::$metadata['formData'],
			'fieldMapping' => self::$fieldMapping,
			'data' => $data
		]);
	}
}
