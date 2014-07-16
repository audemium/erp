<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of ERPxyz.  ERPxyz is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    ERPxyz is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with ERPxyz.  If not, see <http://www.gnu.org/licenses/>.
	*/

	//define types
	//verifyData explanation
		//required: 0 = no, 1 = yes
		//type: int = integer, id = object id, str = string, dec = decimal, opt = option
		//size: max value (int), object type (id), char length (str), array of precision and scale (dec), array of options (opt)
	$TYPES = [
		'employee' => [
			'pluralName' => 'employees',
			'formalName' => 'Employee',
			'formalPluralName' => 'Employees',
			'idName' => 'employeeID',
			'formData' => [
				'Basic Information' => [
					['firstName', 'lastName', 'payType', 'payAmount'],
					['locationID', 'positionID', 'managerID']
				],
				'Personal Information' => [
					['address'],
					[]
				]
			],
			'fields' => [
				'firstName' => [
					'formalName' => 'First Name',
					'verifyData' => [1, 'str', 200]
				],
				'lastName' => [
					'formalName' => 'Last Name',
					'verifyData' => [1, 'str', 200]
				],
				'payType' => [
					'formalName' => 'Pay Type',
					'verifyData' => [1, 'opt', ['S', 'H']]
				],
				'payAmount' => [
					'formalName' => 'Pay Amount',
					'verifyData' => [1, 'dec', [12, 2]]
				],
				'locationID' => [
					'formalName' => 'Location',
					'verifyData' => [1, 'id', 'location']
				],
				'positionID' => [
					'formalName' => 'Position',
					'verifyData' => [1, 'id', 'position']
				],
				'managerID' => [
					'formalName' => 'Manager',
					'verifyData' => [1, 'id', 'employee']
				],
				'address' => [
					'formalName' => 'Address',
					'verifyData' => [0, 'str', 200]
				]
			]
		],
		'location' => [
			'pluralName' => 'locations',
			'formalName' => 'Location',
			'formalPluralName' => 'Locations',
			'idName' => 'locationID',
			'formData' => [
				'Basic Information' => [
					['name', 'address'],
					['city', 'state', 'zip']
				],
			],
			'fields' => [
				'name' => [
					'formalName' => 'Name',
					'verifyData' => [1, 'str', 200]
				],
				'address' => [
					'formalName' => 'Address',
					'verifyData' => [1, 'str', 65535]
				],
				'city' => [
					'formalName' => 'City',
					'verifyData' => [1, 'str', 200]
				],
				'state' => [
					'formalName' => 'State',
					'verifyData' => [1, 'str', 2]
				],
				'zip' => [
					'formalName' => 'Zip Code',
					'verifyData' => [1, 'str', 10]
				],
				
			]
		],
		'order' => [
			'pluralName' => 'orders',
			'formalName' => 'Order',
			'formalPluralName' => 'Orders',
			'idName' => 'orderID',
			'formData' => [
			],
			'fields' => [
			]
		],
		'position' => [
			'pluralName' => 'positions',
			'formalName' => 'Position',
			'formalPluralName' => 'Positions',
			'idName' => 'positionID',
			'formData' => [
				'Basic Information' => [
					['name']
				]
			],
			'fields' => [
				'name' => [
					'formalName' => 'Name',
					'verifyData' => [1, 'str', 200]
				]
			]
		],
		'product' => [
			'pluralName' => 'products',
			'formalName' => 'Product',
			'formalPluralName' => 'Products',
			'idName' => 'productID',
			'formData' => [
			],
			'fields' => [
			]
		],
		'service' => [
			'pluralName' => 'services',
			'formalName' => 'Service',
			'formalPluralName' => 'Services',
			'idName' => 'serviceID',
			'formData' => [
			],
			'fields' => [
			]
		]
	];
?>