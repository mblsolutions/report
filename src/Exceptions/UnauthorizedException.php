<?php

namespace MBLSolutions\Report\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UnauthorizedException extends Exception
{

    /**
     * Render as 403
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return new JsonResponse(['message' => $this->getMessage() ?: 'This action is unauthorized.'], 403);
    }

}
