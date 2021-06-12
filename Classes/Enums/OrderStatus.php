<?php

namespace App\Classes\Enums;

final class OrderStatus
{
	const PENDING = 'pending';
	const CANCEL = 'cancel';
	const LOADED = 'loaded';
	const RESERVED = 'reserved';
	const CLOSED = 'closed';
	const CHANGED = 'changed';
	const CANCELCONFIRMED = 'cancelconfirmed';
}
