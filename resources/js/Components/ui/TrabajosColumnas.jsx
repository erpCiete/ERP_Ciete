import BadgeCliente from '@/Components/ui/BadgeCliente';

function DetailItem({ label, value }) {
    return (
        <div className="min-w-0">
            <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">{label}</p>
            <p className="truncate text-sm text-text-main">{value || '—'}</p>
        </div>
    );
}

export default function TrabajosColumnas({ isMoeve = false, isRepsol = false, trabajo = {} }) {
    const contractValue = trabajo.contrato?.nombre ?? trabajo.contrato_nombre ?? '—';
    const categoryValue = trabajo.categoria?.nombre ?? trabajo.categoria_nombre ?? trabajo.categoria ?? '—';
    const documentValue = trabajo.tipo_documento?.nombre ?? trabajo.tipoDocumento?.nombre ?? '—';
    const workTypeValue = trabajo.tipo_trabajo?.nombre ?? trabajo.tipoTrabajo?.nombre ?? '—';
    const noticeValue = trabajo.numero_aviso ?? trabajo.numeroAviso ?? '—';
    const contextClient = Number(trabajo.id_contexto) === 1 ? 'moeve' : Number(trabajo.id_contexto) === 2 ? 'repsol' : null;

    if (!isMoeve && !isRepsol) {
        return null;
    }

    return (
        <td className="px-3 py-4 align-top">
            <div className="grid min-w-[220px] gap-2">
                {contextClient && (
                    <div>
                        <BadgeCliente cliente={contextClient} />
                    </div>
                )}

                {Number(trabajo.id_contexto) === 1 && isMoeve && (
                    <div className="grid gap-2 xl:grid-cols-2">
                        <DetailItem label="Contrato" value={contractValue} />
                        <DetailItem label="Categoría" value={categoryValue} />
                    </div>
                )}

                {Number(trabajo.id_contexto) === 2 && isRepsol && (
                    <div className="grid gap-2">
                        <DetailItem label="Tipo doc." value={documentValue} />
                        <div className="grid gap-2 xl:grid-cols-2">
                            <DetailItem label="Tipo trabajo" value={workTypeValue} />
                            <DetailItem label="Nº aviso" value={noticeValue} />
                        </div>
                    </div>
                )}

                {!contextClient && (
                    <p className="text-sm text-text-muted">—</p>
                )}
            </div>
        </td>
    );
}
