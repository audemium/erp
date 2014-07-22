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
				//TODO: do I need to add the idName column here? because no other type has it
				'employeeID' => [
					'formalName' => 'Employee Number',
					'verifyData' => [1, 'int', 4294967295]
				],
				'username' => [
					'formalName' => 'Username',
					'verifyData' => [1, 'str', 100]
				],
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
				]
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
				'Basic Information' => [
					['customerID'],
					['employeeID']
				]
			],
			'fields' => [
				'orderID' => [
					'formalName' => 'Order Number',
					'verifyData' => [1, 'int', 4294967295]
				],
				'customerID' => [
					'formalName' => 'Customer',
					'verifyData' => [0, 'id', 'customer']
				],
				'employeeID' => [
					'formalName' => 'Employee',
					'verifyData' => [1, 'id', 'employee']
				]
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
				'Basic Information' => [
					['name', 'description'],
					['defaultPrice']
				]
			],
			'fields' => [
				'name' => [
					'formalName' => 'Name',
					'verifyData' => [1, 'str', 200]
				],
				'description' => [
					'formalName' => 'Description',
					'verifyData' => [1, 'str', 65535]
				],
				'defaultPrice' => [
					'formalName' => 'Price',
					'verifyData' => [1, 'dec', [12, 2]]
				]
			]
		],
		'service' => [
			'pluralName' => 'services',
			'formalName' => 'Service',
			'formalPluralName' => 'Services',
			'idName' => 'serviceID',
			'formData' => [
				'Basic Information' => [
					['name', 'description'],
					['defaultPrice']
				]
			],
			'fields' => [
				'name' => [
					'formalName' => 'Name',
					'verifyData' => [1, 'str', 200]
				],
				'description' => [
					'formalName' => 'Description',
					'verifyData' => [1, 'str', 65535]
				],
				'defaultPrice' => [
					'formalName' => 'Price',
					'verifyData' => [1, 'dec', [12, 2]]
				]
			]
		],
		'customer' => [
			'pluralName' => 'customers',
			'formalName' => 'Customer',
			'formalPluralName' => 'Customers',
			'idName' => 'customerID',
			'formData' => [
				'Basic Information' => [
					['firstName', 'lastName']
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
				]
			]
		],
		'discount' => [
			'pluralName' => 'discounts',
			'formalName' => 'Discount',
			'formalPluralName' => 'Discounts',
			'idName' => 'discountID',
			'formData' => [
				'Basic Information' => [
					['name', 'amount'],
					['type']
				]
			],
			'fields' => [
				'name' => [
					'formalName' => 'Name',
					'verifyData' => [1, 'str', 200]
				],
				'type' => [
					'formalName' => 'Type',
					'verifyData' => [1, 'opt', ['P', 'C']]
				],
				'amount' => [
					'formalName' => 'Amount',
					'verifyData' => [1, 'dec', [12, 2]]
				]
			]
		]
	];
?>