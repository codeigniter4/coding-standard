<?php
class MyClass {

	/**
	 * Bad alignment and indenting check
	 *
	 * @var array
	 */
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

	// @todo this fails to fix.
	// It's probably the comment in the empty array.
	// public $globals = [
	// 	'before' => [
	// 		// 'csrf'
	// 	],
	// 	'after'  => [
	// 		'toolbar'
	// 	]
	// ];

}
