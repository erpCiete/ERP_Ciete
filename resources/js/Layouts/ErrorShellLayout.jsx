import CieteMark from '@/Components/CieteMark';

export default function ErrorShellLayout({ header, children, footerText = 'ERP Ciete' }) {
    return (
        <div className="flex min-h-screen bg-surface font-sans text-text-main antialiased">
            <div className="flex min-w-0 flex-1 flex-col">
                <header className="sticky top-0 z-40 border-b border-border bg-surface shadow-sm">
                    <div className="flex min-h-13 items-center gap-4 px-4 md:min-h-16 md:px-8">
                        <div className="flex min-w-0 flex-1 items-center gap-3">
                            <div className="flex items-center gap-2">
                                <CieteMark className="h-6 w-6 md:h-7 md:w-7" />
                                <span className="text-sm font-bold tracking-tight text-text-main md:text-base">
                                    ERP Ciete
                                </span>
                            </div>
                            <div className="min-w-0 flex-1">
                                <h2 className="truncate text-sm font-semibold text-text-main md:text-base">
                                    {header}
                                </h2>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="flex-1 p-4 md:p-8">
                    <div className="mx-auto w-full max-w-[1400px]">{children}</div>
                </main>

                <footer className="mt-auto border-t border-border p-6 text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {footerText}
                </footer>
            </div>
        </div>
    );
}
