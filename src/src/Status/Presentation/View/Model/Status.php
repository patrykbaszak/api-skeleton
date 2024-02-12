<?php

declare(strict_types=1);

namespace App\Status\Presentation\View\Model;

use OpenApi\Attributes as OA;

class Status
{
    public function __construct(
        #[OA\Property(type: 'string', example: 'ok')]
        public readonly string $status = 'ok',
    ) {
    }
}
