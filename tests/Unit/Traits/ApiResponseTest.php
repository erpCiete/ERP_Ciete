<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponseTest extends TestCase
{
    /**
     * Una clase de prueba que utiliza el trait ApiResponse
     */
    private $mockClass;

    public function setUp(): void
    {
        parent::setUp();
        
        // Crear una clase anónima que use el trait
        $this->mockClass = new class {
            use ApiResponse;
            
            public function testSuccessResponse($data = null, $message = 'Operación exitosa', $code = 200)
            {
                return $this->successResponse($data, $message, $code);
            }
            
            public function testErrorResponse($message, $errorCode, $errors = [], $code = 400)
            {
                return $this->errorResponse($message, $errorCode, $errors, $code);
            }
        };
    }

    /**
     * Helper to decode JSON response
     */
    private function getJsonFromResponse($response)
    {
        return json_decode($response->getContent(), true);
    }

    /**
     * Test: successResponse debe retornar respuesta exitosa con datos como array
     */
    public function test_success_response_with_array_data(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $message = 'Datos obtenidos exitosamente';
        
        $response = $this->mockClass->testSuccessResponse($data, $message, 200);
        $json = json_decode($response->getContent(), true);
        
        $this->assertEquals(200, $response->status());
        $this->assertTrue($json['success']);
        $this->assertEquals($message, $json['message']);
        $this->assertEquals($data, $json['data']);
        $this->assertNotNull($json['meta']['timestamp']);
    }

    /**
     * Test: successResponse sin datos (null)
     */
    public function test_success_response_without_data(): void
    {
        $response = $this->mockClass->testSuccessResponse();
        $json = $this->getJsonFromResponse($response);
        
        $this->assertEquals(200, $response->status());
        $this->assertTrue($json['success']);
        $this->assertEquals('Operación exitosa', $json['message']);
        $this->assertNull($json['data']);
    }

    /**
     * Test: successResponse con código HTTP personalizado
     */
    public function test_success_response_with_custom_code(): void
    {
        $response = $this->mockClass->testSuccessResponse(['created' => true], 'Recurso creado', 201);
        $json = $this->getJsonFromResponse($response);
        
        $this->assertEquals(201, $response->status());
        $this->assertTrue($json['success']);
        $this->assertEquals('Recurso creado', $json['message']);
    }

    /**
     * Test: successResponse incluye timestamp en formato ISO 8601 Zulu
     */
    public function test_success_response_meta_timestamp_format(): void
    {
        $response = $this->mockClass->testSuccessResponse(['test' => 'data']);
        $json = $this->getJsonFromResponse($response);
        $timestamp = $json['meta']['timestamp'];
        
        $this->assertNotNull($timestamp);
        // Verificar que sea un formato ISO 8601 Zulu (termina en Z)
        $this->assertStringEndsWith('Z', $timestamp);
        $this->assertTrue(Carbon::parse($timestamp) instanceof Carbon);
    }

    /**
     * Test: errorResponse con mensaje y código de error
     */
    public function test_error_response_basic(): void
    {
        $message = 'Recurso no encontrado';
        $errorCode = 'RESOURCE_NOT_FOUND';
        
        $response = $this->mockClass->testErrorResponse($message, $errorCode);
        $json = $this->getJsonFromResponse($response);
        
        $this->assertEquals(400, $response->status());
        $this->assertFalse($json['success']);
        $this->assertEquals($message, $json['message']);
        $this->assertEquals($errorCode, $json['error_code']);
        $this->assertEquals([], $json['errors']);
    }

    /**
     * Test: errorResponse con errores adicionales
     */
    public function test_error_response_with_errors_array(): void
    {
        $message = 'Validación fallida';
        $errorCode = 'VALIDATION_ERROR';
        $errors = [
            'email' => 'El email es inválido',
            'password' => 'La contraseña debe tener al menos 8 caracteres'
        ];
        
        $response = $this->mockClass->testErrorResponse($message, $errorCode, $errors);
        $json = $this->getJsonFromResponse($response);
        
        $this->assertEquals(400, $response->status());
        $this->assertFalse($json['success']);
        $this->assertEquals($message, $json['message']);
        $this->assertEquals($errorCode, $json['error_code']);
        $this->assertEquals($errors, $json['errors']);
    }

    /**
     * Test: errorResponse con código HTTP personalizado (404)
     */
    public function test_error_response_with_404_code(): void
    {
        $response = $this->mockClass->testErrorResponse(
            'No encontrado',
            'NOT_FOUND',
            [],
            404
        );
        $json = $this->getJsonFromResponse($response);
        
        $this->assertEquals(404, $response->status());
        $this->assertFalse($json['success']);
    }

    /**
     * Test: errorResponse con código HTTP personalizado (500)
     */
    public function test_error_response_with_500_code(): void
    {
        $response = $this->mockClass->testErrorResponse(
            'Error interno del servidor',
            'INTERNAL_ERROR',
            [],
            500
        );
        $json = $this->getJsonFromResponse($response);
        
        $this->assertEquals(500, $response->status());
        $this->assertFalse($json['success']);
    }

    /**
     * Test: errorResponse incluye timestamp
     */
    public function test_error_response_includes_timestamp(): void
    {
        $response = $this->mockClass->testErrorResponse('Error', 'ERROR_CODE');
        $json = $this->getJsonFromResponse($response);
        $timestamp = $json['meta']['timestamp'];
        
        $this->assertNotNull($timestamp);
        $this->assertStringEndsWith('Z', $timestamp);
    }

    /**
     * Test: successResponse estructura de respuesta JSON completa
     */
    public function test_success_response_json_structure(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $response = $this->mockClass->testSuccessResponse($data, 'Test message', 200);
        
        $json = $this->getJsonFromResponse($response);
        
        // Verificar que tenga las claves requeridas
        $this->assertArrayHasKey('success', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('meta', $json);
    }

    /**
     * Test: errorResponse estructura de respuesta JSON completa
     */
    public function test_error_response_json_structure(): void
    {
        $response = $this->mockClass->testErrorResponse('Error', 'ERROR_CODE', ['field' => 'error']);
        
        $json = $this->getJsonFromResponse($response);
        
        // Verificar que tenga las claves requeridas
        $this->assertArrayHasKey('success', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('error_code', $json);
        $this->assertArrayHasKey('errors', $json);
        $this->assertArrayHasKey('meta', $json);
    }

    /**
     * Test: successResponse con datos complejos (objetos anidados)
     */
    public function test_success_response_with_complex_data(): void
    {
        $data = [
            'user' => [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'company' => [
                'id' => 1,
                'name' => 'Empresa Test'
            ]
        ];
        
        $response = $this->mockClass->testSuccessResponse($data);
        $json = $this->getJsonFromResponse($response);
        
        $this->assertTrue($json['success']);
        $this->assertEquals($data, $json['data']);
        $this->assertEquals('john@example.com', $json['data']['user']['email']);
    }

    /**
     * Test: successResponse con lista vacía
     */
    public function test_success_response_with_empty_list(): void
    {
        $response = $this->mockClass->testSuccessResponse([], 'Sin resultados');
        $json = $this->getJsonFromResponse($response);
        
        $this->assertTrue($json['success']);
        $this->assertEquals([], $json['data']);
        $this->assertEquals('Sin resultados', $json['message']);
    }

    /**
     * Test: errorResponse con múltiples errores de validación
     */
    public function test_error_response_with_multiple_validation_errors(): void
    {
        $errors = [
            'nombre' => ['El nombre es requerido'],
            'email' => ['El email es inválido', 'El email ya existe'],
            'telefono' => ['El teléfono debe tener 10 dígitos']
        ];
        
        $response = $this->mockClass->testErrorResponse(
            'Errores de validación',
            'VALIDATION_ERROR',
            $errors,
            422
        );
        $json = $this->getJsonFromResponse($response);
        
        $this->assertEquals(422, $response->status());
        $this->assertFalse($json['success']);
        $this->assertEquals($errors, $json['errors']);
    }
}
