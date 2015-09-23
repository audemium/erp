<?php
	/*
	Copyright (C) 2014 Nicholas Anderson
	
	This file is part of Audemium ERP.  Audemium ERP is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    Audemium ERP is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License along with Audemium ERP.  If not, see <http://www.gnu.org/licenses/>.
	*/

	//define types
	//verifyData explanation
		//required: 0 = no, 1 = yes
		//type: int = integer, id = object id, str = string, dec = decimal, opt = option, date = date only, disp = display only, email = email address
		//size: max value (int), object type (id), char length (str), array of precision and scale (dec), array of options (opt), 'start' or 'end' or '' to mark which one comes first (date) if needed, not used (disp, email)
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
					'verifyData' => [1, 'int', 4294967295]
				],
				'customerID' => [
					'formalName' => 'Customer',
					'verifyData' => [0, 'id', 'customer']
				],
				'employeeID' => [
					'formalName' => 'Employee',
					'verifyData' => [1, 'id', 'employee']
				],
				'date' => [
					'formalName' => 'Date',
					'verifyData' => [1, 'date', '']
				],
				'amountDue' => [
					'formalName' => 'Amount Due',
					'verifyData' => [0, 'disp']
				]
			],
			'subTypes' => [
				'payment' => [
					'fields' => [
						'date' => [
							'formalName' => 'Date',
							'verifyData' => [1, 'date', ''],
							'actions' => [1, 0, 1]
						],
						'paymentType' => [
							'formalName' => 'Payment Type',
							'verifyData' => [1, 'opt', ['CA', 'CH', 'CR']],
							'actions' => [1, 0, 0]
						],
						'paymentAmount' => [
							'formalName' => 'Payment Amount',
							'verifyData' => [1, 'dec', [12, 2]],
							'actions' => [1, 0, 1]
						]
					]
				],
				'product' => [
					'fields' => [
						'subID' => [
							'formalName' => 'Product',
							'verifyData' => [1, 'id', 'product'],
							'actions' => [1, 1, 1]
						],
						'unitPrice' => [
							'formalName' => 'Unit Price',
							'verifyData' => [1, 'dec', [12, 2]],
							'actions' => [1, 1, 0]
						],
						'quantity' => [
							'formalName' => 'Quantity',
							'verifyData' => [1, 'int', 4294967295],
							'actions' => [1, 1, 1]
						],
						'recurring' => [
							'formalName' => 'Recurring',
							'verifyData' => [1, 'opt', ['yes', 'no']],
							'actions' => [1, 0, 1]
						],
						'interval' => [
							'formalName' => 'Interval',
							'verifyData' => [['recurring', 'yes'], 'opt', ['monthly']],
							'actions' => [1, 0, 0]
						],
						'dayOfMonth' => [
							'formalName' => 'Day of Month',
							'verifyData' => [['recurring', 'yes'], 'opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]],
							'actions' => [1, 0, 0]
						],
						'startDate' => [
							'formalName' => 'Start Date',
							'verifyData' => [['recurring', 'yes'], 'date', 'start'],
							'actions' => [1, 0, 0]
						],
						'endDate' => [
							'formalName' => 'End Date',
							'verifyData' => [['recurring', 'yes'], 'date', 'end'],
							'actions' => [1, 0, 0]
						]
					]
				],
				'service' => [
					'fields' => [
						'subID' => [
							'formalName' => 'Service',
							'verifyData' => [1, 'id', 'service'],
							'actions' => [1, 1, 1]
						],
						'unitPrice' => [
							'formalName' => 'Unit Price',
							'verifyData' => [1, 'dec', [12, 2]],
							'actions' => [1, 1, 0]
						],
						'quantity' => [
							'formalName' => 'Quantity',
							'verifyData' => [1, 'dec', [12, 2]],
							'actions' => [1, 1, 1]
						],
						'recurring' => [
							'formalName' => 'Recurring',
							'verifyData' => [1, 'opt', ['yes', 'no']],
							'actions' => [1, 0, 1]
						],
						'interval' => [
							'formalName' => 'Interval',
							'verifyData' => [['recurring', 'yes'], 'opt', ['monthly']],
							'actions' => [1, 0, 0]
						],
						'dayOfMonth' => [
							'formalName' => 'Day of Month',
							'verifyData' => [['recurring', 'yes'], 'opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]],
							'actions' => [1, 0, 0]
						],
						'startDate' => [
							'formalName' => 'Start Date',
							'verifyData' => [['recurring', 'yes'], 'date', 'start'],
							'actions' => [1, 0, 0]
						],
						'endDate' => [
							'formalName' => 'End Date',
							'verifyData' => [['recurring', 'yes'], 'date', 'end'],
							'actions' => [1, 0, 0]
						]
					]
				],
				'discountOrder' => [
					'fields' => [
						'subID' => [
							'formalName' => '',
							'verifyData' => [0, 'int', 0],
							'actions' => [1, 0, 1]
						],
						'discountID' => [
							'formalName' => 'Discount',
							'verifyData' => [1, 'id', 'discount'],
							'actions' => [1, 0, 1]
						]
					]
				],
				'discountProduct' => [
					'fields' => [
						'subID' => [
							'formalName' => 'Product',
							'verifyData' => [1, 'id', 'product'],
							'actions' => [1, 0, 1]
						],
						'discountID' => [
							'formalName' => 'Discount',
							'verifyData' => [1, 'id', 'discount'],
							'actions' => [1, 0, 1]
						]
					]
				],
				'discountService' => [
					'fields' => [
						'subID' => [
							'formalName' => 'Service',
							'verifyData' => [1, 'id', 'service'],
							'actions' => [1, 0, 1]
						],
						'discountID' => [
							'formalName' => 'Discount',
							'verifyData' => [1, 'id', 'discount'],
							'actions' => [1, 0, 1]
						]
					]
				],
				'attachment' => [
					'fields' => [
						'name' => [
							'formalName' => 'Name',
							'verifyData' => [1, 'str', 200],
							'actions' => [1, 0, 1]
						],
						'extension' => [
							'formalName' => 'Extension',
							'verifyData' => [1, 'str', 10],
							'actions' => [1, 0, 1]
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
					'verifyData' => [1, 'int', 4294967295]
				],
				'supplierID' => [
					'formalName' => 'Supplier',
					'verifyData' => [0, 'id', 'supplier']
				],
				'employeeID' => [
					'formalName' => 'Employee',
					'verifyData' => [1, 'id', 'employee']
				],
				'date' => [
					'formalName' => 'Date',
					'verifyData' => [1, 'date', '']
				],
				'amountDue' => [
					'formalName' => 'Amount Due',
					'verifyData' => [0, 'disp']
				]
			],
			'subTypes' => [
				'payment' => [
					'fields' => [
						'date' => [
							'formalName' => 'Date',
							'verifyData' => [1, 'date', ''],
							'actions' => [1, 0, 0]
						],
						'paymentType' => [
							'formalName' => 'Payment Type',
							'verifyData' => [1, 'opt', ['CA', 'CH', 'CR']],
							'actions' => [1, 0, 0]
						],
						'paymentAmount' => [
							'formalName' => 'Payment Amount',
							'verifyData' => [1, 'dec', [12, 2]],
							'actions' => [1, 0, 0]
						]
					]
				],
				'product' => [
					'fields' => [
						'productID' => [
							'formalName' => 'Product',
							'verifyData' => [1, 'id', 'product'],
							'actions' => [1, 1, 1]
						],
						'locationID' => [
							'formalName' => 'Location',
							'verifyData' => [1, 'id', 'location'],
							'actions' => [1, 0, 0]
						],
						'unitPrice' => [
							'formalName' => 'Unit Price',
							'verifyData' => [1, 'dec', [12, 2]],
							'actions' => [1, 1, 0]
						],
						'quantity' => [
							'formalName' => 'Quantity',
							'verifyData' => [1, 'int', 4294967295],
							'actions' => [1, 1, 0]
						],
						'recurring' => [
							'formalName' => 'Recurring',
							'verifyData' => [1, 'opt', ['yes', 'no']],
							'actions' => [1, 0, 0]
						],
						'interval' => [
							'formalName' => 'Interval',
							'verifyData' => [['recurring', 'yes'], 'opt', ['monthly']],
							'actions' => [1, 0, 0]
						],
						'dayOfMonth' => [
							'formalName' => 'Day of Month',
							'verifyData' => [['recurring', 'yes'], 'opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]],
							'actions' => [1, 0, 0]
						],
						'startDate' => [
							'formalName' => 'Start Date',
							'verifyData' => [['recurring', 'yes'], 'date', 'start'],
							'actions' => [1, 0, 0]
						],
						'endDate' => [
							'formalName' => 'End Date',
							'verifyData' => [['recurring', 'yes'], 'date', 'end'],
							'actions' => [1, 0, 0]
						]
					]
				],
				'other' => [
					'fields' => [
						'name' => [
							'formalName' => 'Name',
							'verifyData' => [1, 'str', 200],
							'actions' => [1, 1, 1]
						],
						'unitPrice' => [
							'formalName' => 'Unit Price',
							'verifyData' => [1, 'dec', [12, 2]],
							'actions' => [1, 1, 0]
						],
						'quantity' => [
							'formalName' => 'Quantity',
							'verifyData' => [1, 'dec', [12, 2]],
							'actions' => [1, 1, 0]
						],
						'recurring' => [
							'formalName' => 'Recurring',
							'verifyData' => [1, 'opt', ['yes', 'no']],
							'actions' => [1, 0, 0]
						],
						'interval' => [
							'formalName' => 'Interval',
							'verifyData' => [['recurring', 'yes'], 'opt', ['monthly']],
							'actions' => [1, 0, 0]
						],
						'dayOfMonth' => [
							'formalName' => 'Day of Month',
							'verifyData' => [['recurring', 'yes'], 'opt', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28]],
							'actions' => [1, 0, 0]
						],
						'startDate' => [
							'formalName' => 'Start Date',
							'verifyData' => [['recurring', 'yes'], 'date', 'start'],
							'actions' => [1, 0, 0]
						],
						'endDate' => [
							'formalName' => 'End Date',
							'verifyData' => [['recurring', 'yes'], 'date', 'end'],
							'actions' => [1, 0, 0]
						]
					]
				],
				'attachment' => [
					'fields' => [
						'name' => [
							'formalName' => 'Name',
							'verifyData' => [1, 'str', 200],
							'actions' => [1, 0, 1]
						],
						'extension' => [
							'formalName' => 'Extension',
							'verifyData' => [1, 'str', 10],
							'actions' => [1, 0, 1]
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
					'verifyData' => [1, 'str', 200]
				],
				'lastName' => [
					'formalName' => 'Last Name',
					'verifyData' => [1, 'str', 200]
				],
				'email' => [
					'formalName' => 'Email',
					'verifyData' => [1, 'email']
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
					'verifyData' => [1, 'str', 200]
				],
				'discountType' => [
					'formalName' => 'Type',
					'verifyData' => [1, 'opt', ['P', 'C']]
				],
				'discountAmount' => [
					'formalName' => 'Amount',
					'verifyData' => [1, 'dec', [12, 2]]
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
					'verifyData' => [0, 'id', 'location']
				],
				'positionID' => [
					'formalName' => 'Position',
					'verifyData' => [1, 'id', 'position']
				],
				'managerID' => [
					'formalName' => 'Manager',
					'verifyData' => [1, 'id', 'employee']
				],
				'vacationTotal' => [
					'formalName' => 'Total Vacation (hours)',
					'verifyData' => [1, 'int', 4294967295]
				],
				'address' => [
					'formalName' => 'Address',
					'verifyData' => [0, 'str', 200]
				],
				'city' => [
					'formalName' => 'City',
					'verifyData' => [0, 'str', 200]
				],
				'state' => [
					'formalName' => 'State',
					'verifyData' => [0, 'str', 2]
				],
				'zip' => [
					'formalName' => 'Zip Code',
					'verifyData' => [0, 'str', 10]
				],
				'workEmail' => [
					'formalName' => 'Email',
					'verifyData' => [1, 'email']
				]
			],
			'subTypes' => [
				'attachment' => [
					'fields' => [
						'name' => [
							'formalName' => 'Name',
							'verifyData' => [1, 'str', 200],
							'actions' => [1, 0, 1]
						],
						'extension' => [
							'formalName' => 'Extension',
							'verifyData' => [1, 'str', 10],
							'actions' => [1, 0, 1]
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
					'verifyData' => [1, 'str', 200]
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
					'verifyData' => [0, 'str', 65535]
				],
				'city' => [
					'formalName' => 'City',
					'verifyData' => [0, 'str', 200]
				],
				'state' => [
					'formalName' => 'State',
					'verifyData' => [0, 'str', 2]
				],
				'zip' => [
					'formalName' => 'Zip Code',
					'verifyData' => [0, 'str', 10]
				]
			]
		]
	];
?>