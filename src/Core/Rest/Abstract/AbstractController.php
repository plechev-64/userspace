<?php

namespace UserSpace\Core\Rest\Abstract;

use UserSpace\Core\Http\JsonResponse;

abstract class AbstractController
{
    protected function success(mixed $data = null, int $code = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $code, $headers);
    }

    protected function error(mixed $data, int $code = 500, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $code, $headers);
    }
}