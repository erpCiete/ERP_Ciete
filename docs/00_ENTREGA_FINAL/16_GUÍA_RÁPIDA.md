# Guía rápida de continuidad

Aquí está lo esencial para comprender, ejecutar y validar el proyecto.

---

## ¿Qué es esto?

ERP interno de Ciete Ingenieros (empresa de ingeniería que trabaja con MOEVE y REPSOL). Digitalizamos su hoja Excel de control de trabajos. Ahora tienen: trabajos, pedidos con líneas de tarifa, facturas, cierre de obra y exportación de documentos al formato ARIBA de Moeve.

Stack: **Laravel 12 + Inertia.js + React 19 + Tailwind + MySQL**.

---

## Instalar en tu máquina

```bash
# 1. Copiar el proyecto a XAMPP (o Laragon)
# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate
# Editar .env con tus datos de MySQL

# 4. Crear BD y migrar
# En MySQL: CREATE DATABASE abaco_ciete;
php artisan migrate --seed

# 5. Levantar
php artisan serve   # http://127.0.0.1:8000
npm run dev         # en otra terminal
```

Lee el detalle en [`01_INSTALACIÓN_LOCAL.md`](01_INSTALACIÓN_LOCAL.md).

---

## Las 5 cosas más importantes que debes saber

**1. El sistema trabaja por contextos.**
Cada usuario ve solo los datos de su contexto (MOEVE / REPSOL / OTROS). Se cambia desde el perfil de usuario. Si algo no aparece, es probable que el contexto no esté cambiado.

**2. El tarifario se bloquea al crear el primer pedido.**
Es intencional. No se puede cambiar después. Si ves que está "bloqueado" en la interfaz, es normal.

**3. Los estados del trabajo son automáticos.**
No se editan a mano. Se calculan desde los pedidos y facturas. Solo `cancelado` y `finalizado` son terminales (no se recalculan).

**4. El cierre lo hace solo Dirección desde `/cierre`.**
Ejecución no puede finalizar trabajos. Si el cliente pregunta por qué el estado no cambia, revisa si la facturación está completa y si Dirección lo ha revisado.

**5. Los campos ARIBA en el contrato son placeholders.**
Los valores configurados en demo (OPEX-MOEVE-2026, etc.) son de prueba. César García tiene que actualizarlos con los valores reales de Moeve antes de producción. Dile que vaya a Maestros → Contratos → Contrato 772 MOEVE → Editar.

---

## Usuarios para probar

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Dirección | `cesar@ciete.es` | `Cesar1234!` |
| Contabilidad | `contable@ciete.es` | `Contable1234!` |
| Admin técnico | `admin@ciete.es` | `Admin1234!` |
| Ejecución | `usuario@ciete.es` | `Usuario1234!` |

---

## Rutas clave

| Ruta | Quién accede | Qué es |
|------|-------------|--------|
| `/trabajos` | Dirección, Ejecución | Tabla principal de obras |
| `/pedidos` | Dirección, Ejecución, Contabilidad | Lista de pedidos |
| `/facturas` | Dirección, Contabilidad | Facturas y exportación |
| `/cierre` | Solo Dirección | Panel de cierre de obras |
| `/maestros` | Dirección, Admin | Contratos, tarifarios, estaciones |
| `/soporte` | Solo Admin | 403 para el resto |
| `/estado` | Solo Admin | 403 para el resto |
| `/registro-actividad` | Dirección, Admin | Auditoría de cambios |
| `/importaciones` | Solo Admin | Importación Excel |

---

## Flujo principal que debes poder hacer de memoria

1. Login como `moeve@ciete.es`
2. Ir a Trabajos → Nuevo trabajo
3. Buscar estación `33450` (LA SENYERA I)
4. Añadir descripción y guardar
5. En la fila → "Crear pedido"
6. En el pedido: añadir línea buscando código `165023`
7. Guardar pedido
8. En la ficha del pedido: "PDF Moeve", "CSV Moeve", "✉ Preparar correo Moeve"

---

## Tests y build

```bash
php artisan test       # estado actual: 270 correctos y 3 fallidos
npm run build          # debe finalizar sin errores
```

---

## Documentación completa

Está en `docs/00_ENTREGA_FINAL/`. Si tienes dudas sobre algo:

- Arquitectura → [`04_ARQUITECTURA_TÉCNICA.md`](04_ARQUITECTURA_TÉCNICA.md)
- Base de datos → [`05_BASE_DATOS_DECISIONES.md`](05_BASE_DATOS_DECISIONES.md)
- Exportación Moeve → [`09_EXPORTACIÓN_MOEVE_ARIBA_CORREO.md`](09_EXPORTACIÓN_MOEVE_ARIBA_CORREO.md)
- Qué falta → [`15_PENDIENTES_Y_RIESGOS.md`](15_PENDIENTES_Y_RIESGOS.md)

---

## Preguntas frecuentes

**¿Por qué el trabajo sigue en "en_curso" aunque puse que está terminado?**
El estado se calcula automáticamente. Si no hay pedido con importe, se queda en "terminado". Si hay pedido sin factura, se queda en "pendiente_facturar".

**¿Por qué el tarifario está bloqueado?**
Porque ya tiene un pedido asociado. Es intencional.

**¿Por qué César ve 403 en /soporte?**
Es diseño. Soporte es solo para el admin técnico.

**¿Cómo cambio el contexto de usuario a MOEVE?**
Login → foto de perfil → Mi perfil → sección "Contexto activo" → clic en "Moeve".

**El ARIBA sigue mostrando "Pendiente de parametrizar".**
Ir a Maestros → Contratos → Contrato 772 MOEVE → Editar → bloque "Datos ARIBA / Solicitud Moeve" → rellenar los campos → Guardar.
