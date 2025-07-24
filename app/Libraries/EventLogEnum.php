<?php

namespace App\Libraries;

class EventLogEnum
{
	const CREATED = 'CREATED';
	const UPDATED = 'UPDATED';
	const DELETED = 'DELETED';
	const VERIFIED = 'VERIFIED';
	const FAILED = 'FAILED';
	const REQUEST = 'REQUEST';
	const RESPONSE = 'RESPONSE';
	const PENDING = 'PENDING';
}
