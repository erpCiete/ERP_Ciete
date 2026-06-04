<?php

namespace Database\Seeders;

use App\Models\HomeNotice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HomeNoticeSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()
            ->where('email', 'admin@ciete.es')
            ->value('id_usuario');

        $messages = [
            // ── Destacado ─────────────────────────────────────────────────────
            [
                'category'   => HomeNotice::CATEGORY_INTERNAL_NOTICE,
                'title_es'   => 'ERP Ciete v2.2.0 listo para demo con CIETE',
                'body_es'    => 'La versión v2.2.0 incluye exportación MOEVE completa (PDF, CSV, ARIBA), parametrización de campos ARIBA desde maestros, modal de preparación de correo Moeve, protección de exportaciones por contexto y tarifa Repsol alineada con negocio.',
                'title_en'   => 'ERP Ciete v2.2.0 ready for CIETE demo',
                'body_en'    => 'Version v2.2.0 includes full MOEVE export (PDF, CSV, ARIBA), ARIBA field configuration from masters, Moeve email preparation modal, export protection by context and Repsol tariff aligned with business rules.',
                'is_featured' => true,
            ],
            // ── Avisos internos ───────────────────────────────────────────────
            [
                'category'   => HomeNotice::CATEGORY_INTERNAL_NOTICE,
                'title_es'   => 'Datos reales MOEVE y REPSOL activos',
                'body_es'    => 'La base local trabaja con datos reales de MOEVE y REPSOL. Si detectas importes, fechas o referencias extrañas, revísalos como avisos de origen antes de corregirlos.',
                'title_en'   => 'Real MOEVE and REPSOL data active',
                'body_en'    => 'The local database uses real MOEVE and REPSOL data. If you detect unusual amounts, dates or references, review them as source-data warnings before correcting anything.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_INTERNAL_NOTICE,
                'title_es'   => 'Exportación MOEVE: campos ARIBA configurados',
                'body_es'    => 'Los campos ARIBA del contrato 772 MOEVE están parametrizados en Maestros → Contratos. Antes de demo con CIETE, César debe revisar y actualizar Cta. de Mayor, Propuesta de Inversión y Acción de gasto con los valores reales de Moeve.',
                'title_en'   => 'MOEVE export: ARIBA fields configured',
                'body_en'    => 'The ARIBA fields for contract 772 MOEVE are configured in Masters → Contracts. Before the CIETE demo, César must review and update Cta. de Mayor, Propuesta de Inversión and Acción de gasto with the real Moeve values.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_INTERNAL_NOTICE,
                'title_es'   => 'Contextos reales activos',
                'body_es'    => 'El selector de contexto permite MOEVE, REPSOL y OTROS CLIENTES. La exportación PDF/CSV/ARIBA es exclusiva de MOEVE. Repsol trabaja con tarifa única vigente.',
                'title_en'   => 'Real contexts active',
                'body_en'    => 'The context selector allows MOEVE, REPSOL and OTHER CLIENTS. PDF/CSV/ARIBA export is exclusive to MOEVE. Repsol uses a single active tariff.',
                'is_featured' => false,
            ],
            // ── Actualizaciones del sistema ───────────────────────────────────
            [
                'category'   => HomeNotice::CATEGORY_SYSTEM_UPDATE,
                'title_es'   => 'Exportación MOEVE operativa',
                'body_es'    => 'El pedido MOEVE genera PDF imprimible, CSV con formato ARIBA y cuadro de tramitación. Desde la ficha del pedido: botón "Preparar correo Moeve" para asunto, cuerpo y checklist de envío.',
                'title_en'   => 'MOEVE export operational',
                'body_en'    => 'MOEVE orders generate printable PDF, ARIBA-format CSV and processing form. From the order detail: "Prepare Moeve email" button for subject, body and sending checklist.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_SYSTEM_UPDATE,
                'title_es'   => 'Control de edición por celda',
                'body_es'    => 'Si otro usuario modificó un campo en los últimos 60 minutos, el sistema muestra aviso con el valor anterior, el nuevo, quién lo cambió y cuándo. Tu borrador se conserva hasta que decides.',
                'title_en'   => 'Cell-level edit conflict control',
                'body_en'    => 'If another user changed a field in the last 60 minutes, the system shows a notice with the previous and new value, who changed it and when. Your draft is preserved until you decide.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_SYSTEM_UPDATE,
                'title_es'   => 'Cierre y facturación mejorados',
                'body_es'    => 'El panel de cierre de Dirección distingue trabajos listos para finalizar, bloqueados e incidentes. Las facturas pueden ser parciales, con anulación conservando trazabilidad.',
                'title_en'   => 'Improved closure and billing',
                'body_en'    => 'The Management closure panel distinguishes jobs ready to close, blocked and with incidents. Invoices can be partial, with cancellation keeping full traceability.',
                'is_featured' => false,
            ],
            // ── Novedades de la empresa ───────────────────────────────────────
            [
                'category'   => HomeNotice::CATEGORY_COMPANY_NEWS,
                'title_es'   => 'Versión v2.2.0 disponible',
                'body_es'    => 'La versión v2.2.0 cierra el ciclo de desarrollo principal: exportación MOEVE completa, protección de exportaciones por contexto, tarifa Repsol alineada, documentación de entrega y limpieza de proyecto.',
                'title_en'   => 'Version v2.2.0 available',
                'body_en'    => 'Version v2.2.0 closes the main development cycle: full MOEVE export, context-based export protection, Repsol tariff aligned, delivery documentation and project cleanup.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_COMPANY_NEWS,
                'title_es'   => 'Tarifa Repsol alineada con negocio',
                'body_es'    => 'Repsol trabaja con tarifa única vigente (Adjud. 2023-2027). El tarifario alternativo de demo queda inactivo. La exportación PDF/CSV/ARIBA es específica de Moeve y no aplica a Repsol.',
                'title_en'   => 'Repsol tariff aligned with business',
                'body_en'    => 'Repsol uses a single active tariff (Adjud. 2023-2027). The demo alternative tariff is now inactive. PDF/CSV/ARIBA export is specific to Moeve and does not apply to Repsol.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_COMPANY_NEWS,
                'title_es'   => 'Documentación de entrega lista',
                'body_es'    => 'La carpeta docs/00_ENTREGA_FINAL contiene 17 documentos: instalación, arquitectura, guía por roles, flujo operativo, exportación Moeve, importación, testing, despliegue y pendientes.',
                'title_en'   => 'Delivery documentation ready',
                'body_en'    => 'The docs/00_ENTREGA_FINAL folder contains 17 documents: installation, architecture, role guide, operational flow, Moeve export, import, testing, deployment and pending items.',
                'is_featured' => false,
            ],
        ];

        DB::transaction(function () use ($messages, $adminId) {
            HomeNotice::query()
                ->where('is_featured', true)
                ->update(['is_featured' => false]);

            foreach ($messages as $message) {
                HomeNotice::query()->updateOrCreate(
                    [
                        'category' => $message['category'],
                        'title_es' => $message['title_es'],
                    ],
                    [
                        ...$message,
                        'is_active' => true,
                        'starts_at' => null,
                        'ends_at' => null,
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ],
                );
            }
        });
    }
}
