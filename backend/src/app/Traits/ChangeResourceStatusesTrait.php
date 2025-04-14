<?php

namespace App\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

/**
 * @mixin JsonResource
 */
trait ChangeResourceStatusesTrait
{
    public static int $status_code = 200;

    public function toResponse($request): Response
    {
        return parent::toResponse($request)->setStatusCode(self::$status_code);
    }

    public static function create($resource, int $code = 200): self
    {
        self::$status_code = $code;

        return parent::make($resource);
    }

    public function setStatus(int $code): self
    {
        self::$status_code = $code;

        return $this;
    }
}
