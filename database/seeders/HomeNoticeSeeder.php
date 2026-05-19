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
                'title_es'   => 'Demo CIETE v2.1.0 preparada para revisión',
                'body_es'    => 'La versión v2.1.0 queda preparada con datos reales importados, validación técnica completada y casos de prueba para revisar el flujo diario de trabajo.',
                'title_en'   => 'CIETE v2.1.0 demo ready for review',
                'body_en'    => 'Version v2.1.0 is ready with real imported data, completed technical validation and test cases for reviewing the daily workflow.',
                'is_featured' => true,
            ],
            // ── Avisos internos ───────────────────────────────────────────────
            [
                'category'   => HomeNotice::CATEGORY_INTERNAL_NOTICE,
                'title_es'   => 'Revisión operativa con datos reales',
                'body_es'    => 'La base local trabaja ya con datos reales de MOEVE y REPSOL. Si detectas importes, fechas o referencias extrañas, revísalos como avisos de origen antes de corregirlos.',
                'title_en'   => 'Operational review with real data',
                'body_en'    => 'The local database now uses real MOEVE and REPSOL data. If you detect unusual amounts, dates or references, review them as source-data warnings before correcting anything.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_INTERNAL_NOTICE,
                'title_es'   => 'Contextos reales activos',
                'body_es'    => 'El selector de contexto solo permite MOEVE, REPSOL y OTROS CLIENTES. La vista global TODOS no está disponible para operar ni crear registros.',
                'title_en'   => 'Real contexts enabled',
                'body_en'    => 'The context selector only allows MOEVE, REPSOL and OTHER CLIENTS. The global ALL view is not available for operating or creating records.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_INTERNAL_NOTICE,
                'title_es'   => 'Uso del modo Excel',
                'body_es'    => 'El modo Ciete Excel está pensado para trabajo diario con tablas densas. Usa filtros, paginación e Ir a página para moverte rápido por grandes volúmenes.',
                'title_en'   => 'Using Excel mode',
                'body_en'    => 'Ciete Excel mode is designed for daily work with dense tables. Use filters, pagination and Go to page to move quickly through large datasets.',
                'is_featured' => false,
            ],
            // ── Actualizaciones del sistema ───────────────────────────────────
            [
                'category'   => HomeNotice::CATEGORY_SYSTEM_UPDATE,
                'title_es'   => 'Importación real completada',
                'body_es'    => 'Se han cargado 11 Excel operativos con trabajos, pedidos, ítems y facturas reales. Los conteos principales están reconciliados con la referencia P1-12.',
                'title_en'   => 'Real import completed',
                'body_en'    => '11 operational Excel files have been loaded with real jobs, orders, items and invoices. The main counts are reconciled with the P1-12 reference.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_SYSTEM_UPDATE,
                'title_es'   => 'Paginación mejorada',
                'body_es'    => 'Los listados principales incorporan navegación rápida con primera página, última página e Ir a página para reducir tiempos de trabajo.',
                'title_en'   => 'Improved pagination',
                'body_en'    => 'Main listings now include faster navigation with first page, last page and Go to page to reduce working time.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_SYSTEM_UPDATE,
                'title_es'   => 'Control de edición por celda',
                'body_es'    => 'En Trabajos Excel, si otro usuario modificó una celda recientemente, el sistema avisa, conserva tu borrador y pide confirmación antes de sobrescribir.',
                'title_en'   => 'Cell edit conflict control',
                'body_en'    => 'In Jobs Excel mode, if another user recently changed a cell, the system warns you, keeps your draft and asks for confirmation before overwriting.',
                'is_featured' => false,
            ],
            // ── Novedades de la empresa ───────────────────────────────────────
            [
                'category'   => HomeNotice::CATEGORY_COMPANY_NEWS,
                'title_es'   => 'Versión v2.1.0 disponible',
                'body_es'    => 'La versión v2.1.0 queda disponible para validación operativa local, con roles, permisos, contextos y datos reales cargados.',
                'title_en'   => 'Version v2.1.0 available',
                'body_en'    => 'Version v2.1.0 is available for local operational validation, with roles, permissions, contexts and real data loaded.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_COMPANY_NEWS,
                'title_es'   => 'Panel técnico más limpio',
                'body_es'    => 'El administrador técnico cuenta con un panel más ordenado para usuarios, soporte, auditoría técnica, mantenimiento, avisos y estado del sistema.',
                'title_en'   => 'Cleaner technical panel',
                'body_en'    => 'The technical administrator now has a cleaner panel for users, support, technical audit, maintenance, notices and system status.',
                'is_featured' => false,
            ],
            [
                'category'   => HomeNotice::CATEGORY_COMPANY_NEWS,
                'title_es'   => 'Validación por roles preparada',
                'body_es'    => 'El ERP está preparado para probar el día a día de Dirección, Contabilidad, Ejecución MOEVE, Ejecución REPSOL, multicontexto y administración técnica.',
                'title_en'   => 'Role-based validation ready',
                'body_en'    => 'The ERP is ready to test daily work for Management, Accounting, MOEVE Execution, REPSOL Execution, multicontext users and technical administration.',
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
