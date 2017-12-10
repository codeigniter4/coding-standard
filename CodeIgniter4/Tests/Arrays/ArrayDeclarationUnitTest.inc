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

	public function error()
	{
		return [
				'code'         => '',
				'message'     => pg_last_error($this->connID)
		];
	}

	public function error2()
	{
		$this->QBWhere[] = ['condition' => $like_statement, 'escape' => $escape];

		$this->QBWhere[] = [
							 'condition' => $like_statement,
							 'escape'    => $escape,
						 ];
	}

	public $arr = array( );

	public $arr = [   ];

	public $arr = [1,];

	public $arr = [
		'a'=> 1,
		'b'=> 2,
		'c' => 3,
	];

	$cache->save($cacheKeyPagination, [
									  'page'    => $this->pager->getCurrentPage(),
			'perPage' => $this->pager->getPerPage(),
						 'total'   => $this->pager->getPerPage() * $this->pager->getPageCount(),
								  ] , 15);

	return preg_replace(
				array(
			'#<img[\s/]+.*?src\s*=\s*(["\'])([^\\1]+?)\\1.*?\>#i',
			'#<img[\s/]+.*?src\s*=\s*?(([^\s"\'=<>`]+)).*?\>#i'
				), '\\2', $str
		);

		$this->db->table($this->table)
			->insert([
'version'   => $version,
'name'      => $this->name,
			]);

		$message->setCustomProperty('user_info', array(
				'id' => $id,
				'name' => 'Test message'
			)
		);

		$users = [
			[
				'id'    => 1,
				'name'  => 'John',
				'email' => 'john@example.com',
				'fact'  => 'Loves coding',
			],
			[
				'id'    => 2,
				'name'  => 'Jim',
				'email' => 'jim@example.com',
				'fact'  => 'Developed on CodeIgniter',
			],
			[
				'id'    => 3,
				'name'  => 'Jane',
				'email' => 'jane@example.com',
				'fact'  => 'Lives in the USA', [
					'hobbies' => [
						'guitar',
						'cycling',
					],
				]
			],
		];
}
