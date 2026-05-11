<?php

declare(strict_types=1);

namespace App\Services\Importacion;

use Illuminate\Support\Str;

final class ExcelHeaderNormalizer
{
    /**
     * @var array<string, string>
     */
    private const CANONICAL_HEADERS = [
        'n' => 'work_number',
        'no' => 'work_number',
        'num' => 'work_number',
        'numero' => 'work_number',
        'n es' => 'station_code',
        'no es' => 'station_code',
        'num es' => 'station_code',
        'numero es' => 'station_code',
        'n estacion' => 'station_code',
        'es' => 'station_code',
        '1 n concn' => 'station_code',
        'c emp' => 'station_code',
        'concesion' => 'station_code',
        'codigo' => 'station_code',
        'codgio' => 'station_code',
        'codigo estacion' => 'station_code',
        'codigo solred' => 'solred_code',
        'nombre' => 'station_name',
        '3 nombre' => 'station_name',
        'nombre comercial' => 'station_name',
        'localidad' => 'city',
        'poblacion' => 'city',
        'municipio' => 'city',
        '8 municipio' => 'city',
        'provincia' => 'province',
        '9 provincia' => 'province',
        'direccion' => 'address',
        '5 direccion' => 'address',
        'c postal' => 'postal_code',
        'cod postal' => 'postal_code',
        '7 cod postal' => 'postal_code',
        'cod postale' => 'postal_code',
        'n pedido' => 'order_number',
        'no pedido' => 'order_number',
        'numero pedido' => 'order_number',
        'importe pedido' => 'order_amount',
        'importe total pedido' => 'order_amount',
        'fecha solicitud pedido' => 'order_request_date',
        'fecha reclamo app solicitud pedido' => 'order_request_date',
        'facturacion solicitada' => 'requested_amount',
        'importe solicitado' => 'requested_amount',
        'importe solictado' => 'requested_amount',
        'facturado solo esther' => 'invoiced_amount',
        'ya facturado' => 'invoiced_amount',
        'importe facturado' => 'invoiced_amount',
        'fecha encargo' => 'assignment_date',
        'fecha t' => 'completion_date',
        'fecha terminacion trabajo' => 'completion_date',
        'descripcion del trabajo' => 'work_description',
        'descripcion del trabajo abierta' => 'work_description',
        'descripcion del servicio' => 'service_description',
        'descripcion del servicio tarifa' => 'service_description',
        'responsable ciete' => 'responsible_ciete',
        'responsable moeve' => 'responsible_client',
        'responsable repsol' => 'responsible_client',
        'responsable gestor' => 'responsible_client',
        'observaciones' => 'observations',
        'n factura' => 'invoice_number',
        'no factura' => 'invoice_number',
        'factura' => 'invoice_number',
        'n factura ccp' => 'invoice_ccp',
        'no factura ccp' => 'invoice_ccp',
        'n factura ciete' => 'invoice_number',
        'no factura ciete' => 'invoice_number',
        'numero 1 factura' => 'invoice_number_1',
        'numero 2 factura' => 'invoice_number_2',
        '1 factura' => 'invoice_amount_1',
        '2 factura' => 'invoice_amount_2',
        'fecha factura' => 'invoice_date',
        'fecha solicitud factura' => 'invoice_date',
        'fecha solicitud 1 factura' => 'invoice_date_1',
        'fecha solicitud 2 factura' => 'invoice_date_2',
        'contrato' => 'contract_code',
        'status' => 'status',
        'estado' => 'status',
        'categoria' => 'category',
        'tipo de trabajo' => 'category',
        'n aviso' => 'notice_number',
        'no aviso' => 'notice_number',
        'n aviso p keops' => 'notice_number',
        'no aviso p keops' => 'notice_number',
        'n aviso orden manten' => 'notice_number',
        'no aviso orden manten' => 'notice_number',
        'orden manten' => 'maintenance_order',
        'codigo servicio' => 'service_code',
        'numero tarifa con punto' => 'tariff_number',
        'importe unitario' => 'unit_price',
        'uds del pedido' => 'quantity',
        'uds solicitadas' => 'requested_units',
        'sociedad' => 'billing_company',
        'cif' => 'tax_id',
        'y wgs84' => 'latitude',
        'x wgs84' => 'longitude',
        'f baja' => 'inactive_date',
        'f alta f modificacion' => 'updated_source_date',
        'cod sociedad' => 'billing_company_code',
        'cod retailgas' => 'retailgas_code',
        'n margenes' => 'margin_number',
        'ltrs 21' => 'liters_21',
        'cliente' => 'client_name',
        'nom encargado' => 'manager_name',
        'nom gerente' => 'director_name',
        'tfno instalacion' => 'phone',
        'margen' => 'margin',
        'provincial' => 'regional',
    ];

    public function normalize(?string $value): string
    {
        $value = trim((string) $value);
        $value = str_replace(["\r", "\n", "\t"], ' ', $value);
        $value = strtr($value, [
            'Âª' => 'ª',
            'Âº' => 'º',
            'Ã¡' => 'á',
            'Ã©' => 'é',
            'Ã­' => 'í',
            'Ã³' => 'ó',
            'Ãº' => 'ú',
            'Ã' => 'Á',
            'Ã‰' => 'É',
            'Ã' => 'Í',
            'Ã“' => 'Ó',
            'Ãš' => 'Ú',
            'Ã¼' => 'ü',
            'Ãœ' => 'Ü',
            'Ã±' => 'ñ',
            'Ã‘' => 'Ñ',
            'º' => 'o',
            'ª' => 'a',
        ]);
        $value = Str::ascii($value);
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^a-z0-9]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    public function canonical(?string $header): ?string
    {
        $normalized = $this->normalize($header);

        return self::CANONICAL_HEADERS[$normalized] ?? null;
    }

    /**
     * @param array<int, string|null> $headers
     * @return array<string, int>
     */
    public function mapHeaders(array $headers): array
    {
        $map = [];

        foreach ($headers as $index => $header) {
            $canonical = $this->canonical($header);

            if ($canonical !== null && ! array_key_exists($canonical, $map)) {
                $map[$canonical] = $index;
            }
        }

        return $map;
    }

    /**
     * @param array<int, string|null> $headers
     * @return array<int, string>
     */
    public function unknownHeaders(array $headers): array
    {
        $unknown = [];

        foreach ($headers as $header) {
            $header = trim((string) $header);
            if ($header !== '' && $this->canonical($header) === null) {
                $unknown[] = $header;
            }
        }

        return array_values(array_unique($unknown));
    }
}
