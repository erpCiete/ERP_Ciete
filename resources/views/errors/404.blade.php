<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error 404</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f7fb;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475569;
            --primary: #1d4ed8;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top right, rgba(29, 78, 216, 0.10), transparent 45%), var(--bg);
            color: var(--text);
            padding: 24px;
        }

        .card {
            width: min(100%, 640px);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.08);
        }

        .code {
            display: inline-block;
            font-weight: 700;
            letter-spacing: .08em;
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
        }

        h1 {
            margin: 12px 0 8px;
            font-size: 30px;
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .actions {
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            background: var(--primary);
            color: #fff;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn:hover { opacity: .92; }
    </style>
</head>
<body>
    <main class="card">
        <span class="code">Error 404</span>
        <h1>La pagina solicitada no esta disponible</h1>
        <p>La ruta que intentabas abrir no existe o ya no forma parte del portal actual.</p>
        <div class="actions">
            <a class="btn" href="{{ url('/') }}">Volver a inicio</a>
        </div>
    </main>
</body>
</html>
