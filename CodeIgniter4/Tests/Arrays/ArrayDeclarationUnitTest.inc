<?php

class MyClass {

	protected $codes = [
		'created'					 => 201,
		'unsupported_response_type'	 => 400,
		'invalid_scope'				 => 400,
		'temporarily_unavailable'	 => 400,
		'invalid_grant'				 => [
			'invalid_data'				 => 400,
			'access_denied'				 => 401,
		 'forbidden'					 => 403,
		 'resource_not_found'		 => 404],
		'conflict'					 => 409,
	    ];

	public $globals = [
		'before' => [
			// 'csrf'
		],
		'after'  => [
			'toolbar'
		]
	];

	protected $defaultHTTPMethods = [
		'options',
		 'get',
		 'head',
	];

	protected function isValidLuhn()
	{
		$sumTable = [
			[0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
			[0, 2, 4, 6, 8, 1, 3, 5, 7, 9]
		];
	}

	protected $cards = [
		'American Express'			 => ['name' => 'amex', 'length' => '15', 'prefixes' => '34,37', 'checkdigit' => true],
		'DinersClub'				 => ['name' => 'dinersclub', 'length' => '14,16', 'prefixes' => '300,301,302,303,304,305,309,36,38,39,54,55', 'checkdigit' => true],
		'MasterCard'				 => ['name' => 'mastercard', 'length' => '16', 'prefixes' => '51,52,53,54,55,22,23,24,25,26,27', 'checkdigit' => true],
		'Visa'						 => ['name' => 'visa', 'length' => '13,16,19', 'prefixes' => '4', 'checkdigit' => true],
		// Canadian Cards
		'CIBC Convenience Card'				 => ['name' => 'cibc', 'length' => '16', 'prefixes' => '4506', 'checkdigit' => false],
		'Royal Bank of Canada Client Card'	 => ['name' => 'rbc', 'length' => '16', 'prefixes' => '45', 'checkdigit' => false],
		'TD Canada Trust Access Card'		 => ['name' => 'tdtrust', 'length' => '16', 'prefixes' => '589297', 'checkdigit' => false],
	];

	$a = array
	// comment
	( 'a', 'b' );

	$a = array /* comment */ ( 'a', 'b' );

	protected static $statusCodes = [
		// 1xx: Informational
		100	 => 'Continue',
		101	 => 'Switching Protocols',
		102	 => 'Processing', // http://www.iana.org/go/rfc2518
		// 2xx: Success
		200	 => 'OK',
	];
}
