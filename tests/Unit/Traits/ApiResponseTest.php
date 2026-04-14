<?php

namespace Tests\Unit\Traits;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    use RefreshDatabase;

    private object $harness;

    protected function setUp(): void
    {
        parent::setUp();

        $this->harness = new class {
            use ApiResponse;

            public function success(mixed $data = null, string $message = 'Operacion exitosa', int $code = 200, array $meta = [])
            {
                return $this->successResponse($data, $message, $code, $meta);
            }

            public function paginated(LengthAwarePaginator $paginator, mixed $data, string $message = 'Listado obtenido')
            {
                return $this->paginatedResponse($paginator, $data, $message);
            }

            public function error(string $message, string $errorCode, array $errors = [], int $code = 400)
            {
                return $this->errorResponse($message, $errorCode, $errors, $code);
            }
        };
    }

    public function test_success_response_returns_expected_structure(): void
    {
        $payload = ['id' => 7, 'nombre' => 'Cliente Test'];
        $response = $this->harness->success($payload, 'Creado', 201, ['source' => 'unit-test']);
        $json = $response->getData(true);

        $this->assertSame(201, $response->status());
        $this->assertTrue($json['success']);
        $this->assertSame('Creado', $json['message']);
        $this->assertSame($payload, $json['data']);
        $this->assertSame('unit-test', $json['meta']['source']);
        $this->assertNotEmpty($json['meta']['timestamp']);
    }

    public function test_paginated_response_includes_pagination_metadata(): void
    {
        $items = [
            ['id' => 1],
            ['id' => 2],
        ];
        $paginator = new LengthAwarePaginator($items, 5, 2, 2);
        $response = $this->harness->paginated($paginator, $items, 'Listado paginado');
        $json = $response->getData(true);

        $this->assertSame(200, $response->status());
        $this->assertSame('Listado paginado', $json['message']);
        $this->assertSame(5, $json['meta']['pagination']['total']);
        $this->assertSame(2, $json['meta']['pagination']['per_page']);
        $this->assertSame(2, $json['meta']['pagination']['current_page']);
        $this->assertSame(3, $json['meta']['pagination']['total_pages']);
    }

    public function test_error_response_returns_expected_structure(): void
    {
        $response = $this->harness->error(
            'Validacion fallida',
            'VALIDATION_ERROR',
            ['nombre' => ['El nombre es obligatorio.']],
            422,
        );
        $json = $response->getData(true);

        $this->assertSame(422, $response->status());
        $this->assertFalse($json['success']);
        $this->assertSame('Validacion fallida', $json['message']);
        $this->assertSame('VALIDATION_ERROR', $json['error_code']);
        $this->assertSame(['nombre' => ['El nombre es obligatorio.']], $json['errors']);
        $this->assertNotEmpty($json['meta']['timestamp']);
    }
}
