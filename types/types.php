<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	/* 
	typeData
		type: int = integer, id = object id, str = string, dec = decimal, opt = option, date = date only, disp = display only, email = email address
		size: max value (int), object type (id), char length (str), array of precision and scale (dec), array of options (opt), 'start' or 'end' or '' to mark which one comes first (date) if needed, not used (disp, email)
	requiredData
		three possible actions: add, edit and delete
		0: not required for that action
		1: required for that action
		array: required when the field in the first element has the value in the second element
	*/
	$TYPES = [
		'order' => [
			'pluralName' => 'orders',
			'formalName' => 'Order',
			'formalPluralName' => 'Orders',
			'idName' => 'orderID',
			'formData' => [
				'Basic Information' => [
					['customerID', 'date'],
					['employeeID', 'amountDue']
				]
			],
			'fields' => [
				//order has orderID as a field because it uses it to identify an order to the user, whereas other types use a name
				'orderID' => [
					'formalName' => 'Order Number',
					'typeData' => ['int', 4294967295],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'customerID' => [
					'formalName' => 'Customer',
					'typeData' => ['id', 'customer'],
					'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
				],
				'employeeID' => [
					'formalName' => 'Employee',
					'typeData' => ['id', 'employee'],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'date' => [
					'formalName' => 'Date',
					'typeData' => ['date', ''],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'amountDue' => [
					'formalName' => 'Amount Due',
					'typeData' => ['disp'],
					'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
				]
			],
			'subTypes' => [
				'payment' => [
					'fields' => [
						'date' => [
							'formalName' => 'Date',
							'typeData' => ['date', ''],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						],
						'paymentType' => [
							'formalName' => 'Payment Type',
							'typeData' => ['opt', ['CA', 'CH', 'CR']],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 0]
						],
						'paymentAmount' => [
							'formalName' => 'Payment Amount',
							'typeData' => ['dec', [12, 2]],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						]
					]
				],
				'product' => [
					'fields' => [
						'subID' => [
							'formalName' => 'Product',
							'typeData' => ['id', 'product'],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
						],
						'unitPrice' => [
							'formalName' => 'Unit Price',
							'typeData' => ['dec', [12, 2]],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 0]
						],
						'quantity' => [
							'formalName' => 'Quantity',
							'typeData' => ['int', 4294967295],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
						],
						'recurring' => [
							'formalName' => 'Recurring',
							'typeData' => ['opt', ['yes', 'no']],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						],
						'interval' => [
							'formalName' => 'Interval',
							'typeData' => ['opt', ['monthly']],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'dayOfMonth' => [
							'formalName' => 'Day of Month',
							'typeData' => ['opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'startDate' => [
							'formalName' => 'Start Date',
							'typeData' => ['date', 'start'],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'endDate' => [
							'formalName' => 'End Date',
							'typeData' => ['date', 'end'],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						]
					]
				],
				'service' => [
					'fields' => [
						'subID' => [
							'formalName' => 'Service',
							'typeData' => ['id', 'service'],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
						],
						'unitPrice' => [
							'formalName' => 'Unit Price',
							'typeData' => ['dec', [12, 2]],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 0]
						],
						'quantity' => [
							'formalName' => 'Quantity',
							'typeData' => ['dec', [12, 2]],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
						],
						'recurring' => [
							'formalName' => 'Recurring',
							'typeData' => ['opt', ['yes', 'no']],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						],
						'interval' => [
							'formalName' => 'Interval',
							'typeData' => ['opt', ['monthly']],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'dayOfMonth' => [
							'formalName' => 'Day of Month',
							'typeData' => ['opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'startDate' => [
							'formalName' => 'Start Date',
							'typeData' => ['date', 'start'],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'endDate' => [
							'formalName' => 'End Date',
							'typeData' => ['date', 'end'],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						]
					]
				],
				'discountOrder' => [
					'fields' => [
						'subID' => [
							'formalName' => '',
							'typeData' => ['int', 0],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						],
						'discountID' => [
							'formalName' => 'Discount',
							'typeData' => ['id', 'discount'],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						]
					]
				],
				'discountProduct' => [
					'fields' => [
						'subID' => [
							'formalName' => 'Product',
							'typeData' => ['id', 'product'],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						],
						'discountID' => [
							'formalName' => 'Discount',
							'typeData' => ['id', 'discount'],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						]
					]
				],
				'discountService' => [
					'fields' => [
						'subID' => [
							'formalName' => 'Service',
							'typeData' => ['id', 'service'],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						],
						'discountID' => [
							'formalName' => 'Discount',
							'typeData' => ['id', 'discount'],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						]
					]
				],
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
		],
		'expense' => [
			'pluralName' => 'expenses',
			'formalName' => 'Expense',
			'formalPluralName' => 'Expenses',
			'idName' => 'expenseID',
			'formData' => [
				'Basic Information' => [
					['supplierID', 'date'],
					['employeeID', 'amountDue']
				]
			],
			'fields' => [
				//expense has expenseID as a field because it uses it to identify an expense to the user, whereas other types use a name
				'expenseID' => [
					'formalName' => 'Expense Number',
					'typeData' => ['int', 4294967295],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'supplierID' => [
					'formalName' => 'Supplier',
					'typeData' => ['id', 'supplier'],
					'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
				],
				'employeeID' => [
					'formalName' => 'Employee',
					'typeData' => ['id', 'employee'],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'date' => [
					'formalName' => 'Date',
					'typeData' => ['date', ''],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'amountDue' => [
					'formalName' => 'Amount Due',
					'typeData' => ['disp'],
					'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
				]
			],
			'subTypes' => [
				'payment' => [
					'fields' => [
						'date' => [
							'formalName' => 'Date',
							'typeData' => ['date', ''],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 0]
						],
						'paymentType' => [
							'formalName' => 'Payment Type',
							'typeData' => ['opt', ['CA', 'CH', 'CR']],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 0]
						],
						'paymentAmount' => [
							'formalName' => 'Payment Amount',
							'typeData' => ['dec', [12, 2]],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 0]
						]
					]
				],
				'product' => [
					'fields' => [
						'productID' => [
							'formalName' => 'Product',
							'typeData' => ['id', 'product'],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
						],
						'locationID' => [
							'formalName' => 'Location',
							'typeData' => ['id', 'location'],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 0]
						],
						'unitPrice' => [
							'formalName' => 'Unit Price',
							'typeData' => ['dec', [12, 2]],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 0]
						],
						'quantity' => [
							'formalName' => 'Quantity',
							'typeData' => ['int', 4294967295],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 0]
						],
						'recurring' => [
							'formalName' => 'Recurring',
							'typeData' => ['opt', ['yes', 'no']],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 0]
						],
						'interval' => [
							'formalName' => 'Interval',
							'typeData' => ['opt', ['monthly']],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'dayOfMonth' => [
							'formalName' => 'Day of Month',
							'typeData' => ['opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'startDate' => [
							'formalName' => 'Start Date',
							'typeData' => ['date', 'start'],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'endDate' => [
							'formalName' => 'End Date',
							'typeData' => ['date', 'end'],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						]
					]
				],
				'other' => [
					'fields' => [
						'name' => [
							'formalName' => 'Name',
							'typeData' => ['str', 200],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 1]
						],
						'unitPrice' => [
							'formalName' => 'Unit Price',
							'typeData' => ['dec', [12, 2]],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 0]
						],
						'quantity' => [
							'formalName' => 'Quantity',
							'typeData' => ['dec', [12, 2]],
							'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 0]
						],
						'recurring' => [
							'formalName' => 'Recurring',
							'typeData' => ['opt', ['yes', 'no']],
							'requiredData' => ['add' => 1, 'edit' => 0, 'delete' => 0]
						],
						'interval' => [
							'formalName' => 'Interval',
							'typeData' => ['opt', ['monthly']],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'dayOfMonth' => [
							'formalName' => 'Day of Month',
							'typeData' => ['opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'startDate' => [
							'formalName' => 'Start Date',
							'typeData' => ['date', 'start'],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						],
						'endDate' => [
							'formalName' => 'End Date',
							'typeData' => ['date', 'end'],
							'requiredData' => ['add' => ['recurring', 'yes'], 'edit' => 0, 'delete' => 0]
						]
					]
				],
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
		],
		'customer' => [
			'pluralName' => 'customers',
			'formalName' => 'Customer',
			'formalPluralName' => 'Customers',
			'idName' => 'customerID',
			'formData' => [
				'Basic Information' => [
					['firstName', 'lastName'],
					['email']
				]
			],
			'fields' => [
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
				'email' => [
					'formalName' => 'Email',
					'typeData' => ['email'],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				]
			]
		],
		'supplier' => [
			'pluralName' => 'suppliers',
			'formalName' => 'Supplier',
			'formalPluralName' => 'Suppliers',
			'idName' => 'supplierID',
			'formData' => [
				'Basic Information' => [
					['name'],
					[]
				]
			],
			'fields' => [
				'name' => [
					'formalName' => 'Company',
					'typeData' => ['str', 200],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
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
					'typeData' => ['str', 200],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'description' => [
					'formalName' => 'Description',
					'typeData' => ['str', 65535],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'defaultPrice' => [
					'formalName' => 'Price',
					'typeData' => ['dec', [12, 2]],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
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
					'typeData' => ['str', 200],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'description' => [
					'formalName' => 'Description',
					'typeData' => ['str', 65535],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'defaultPrice' => [
					'formalName' => 'Price',
					'typeData' => ['dec', [12, 2]],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
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
					['name', 'discountAmount'],
					['discountType']
				]
			],
			'fields' => [
				'name' => [
					'formalName' => 'Name',
					'typeData' => ['str', 200],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'discountType' => [
					'formalName' => 'Type',
					'typeData' => ['opt', ['P', 'C']],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'discountAmount' => [
					'formalName' => 'Amount',
					'typeData' => ['dec', [12, 2]],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				]
			]
		],
		'employee' => [
			'pluralName' => 'employees',
			'formalName' => 'Employee',
			'formalPluralName' => 'Employees',
			'idName' => 'employeeID',
			'formData' => [
				'Basic Information' => [
					['firstName', 'lastName', 'payType', 'payAmount', 'workEmail'],
					['locationID', 'positionID', 'managerID', 'vacationTotal']
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
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
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
				'locationID' => [
					'formalName' => 'Location',
					'typeData' => ['id', 'location'],
					'requiredData' => ['add' => 0, 'edit' => 0, 'delete' => 0]
				],
				'positionID' => [
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
					'typeData' => ['str', 200],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
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
					'typeData' => ['str', 200],
					'requiredData' => ['add' => 1, 'edit' => 1, 'delete' => 1]
				],
				'address' => [
					'formalName' => 'Address',
					'typeData' => ['str', 65535],
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
				]
			]
		]
	];
?>