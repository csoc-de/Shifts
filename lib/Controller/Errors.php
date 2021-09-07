<?php
/*
 * @copyright Copyright (c) 2021. Fabian Kirchesch <fabian.kirchesch@outlook.de>
 *
 * @author Fabian Kirchesch <fabian.kirchesch@outlook.de>
 */

namespace OCA\Shifts\Controller;

use Closure;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\Shifts\Service\NotFoundException;

// Allows for handling of NotFoundExceptions
trait Errors {
	protected function handleNotFound(Closure $callback): DataResponse {
		try {
			return new DataResponse($callback());
		} catch (NotFoundException $e) {
			$message = ['message' => $e->getMessage()];
			return new DataResponse($message, Http::STATUS_NOT_FOUND);
		}
	}
}
