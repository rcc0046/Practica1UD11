<?php
declare(strict_types=1);

$host = getenv('DB_HOST') ?: 'db';
$dbName = getenv('DB_NAME') ?: 'dwes_db';
$user = getenv('DB_USER') ?: 'dwes_user';
$password = getenv('DB_PASS') ?: 'dwes_password';

$dbConnected = false;
$dbError = null;
$serverVersion = 'Desconocida';
$currentDbTime = null;

try {
    $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ]);

    $dbConnected = true;

    // Obtener versión y fecha del servidor MySQL para confirmar la consulta SQL
    $stmt = $pdo->query('SELECT VERSION() AS mysql_ver, NOW() AS db_time');
    $info = $stmt->fetch();
    if ($info) {
        $serverVersion = $info['mysql_ver'];
        $currentDbTime = $info['db_time'];
    }
} catch (PDOException $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Práctica 1 (UD1) — Entorno Docker DWES</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-blue: #38bdf8;
            --accent-purple: #a855f7;
            --font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background: linear-gradient(135deg, #0b0f19 0%, #1e1b4b 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 1.5rem;
        }

        h1 {
            font-size: 1.9rem;
            font-weight: 700;
            background: linear-gradient(to right, #38bdf8, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .card {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            border-color: var(--accent-blue);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.2);
            color: var(--accent-green);
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .badge-error {
            background: rgba(239, 68, 68, 0.2);
            color: var(--accent-red);
            border: 1px solid rgba(239, 68, 68, 0.4);
        }

        .card-body p {
            color: var(--text-muted);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-top: 0.25rem;
        }

        .details-section {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .details-section h2 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: var(--accent-blue);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th, td {
            text-align: left;
            padding: 0.65rem 0.75rem;
            border-bottom: 1px solid var(--card-border);
        }

        th {
            color: var(--text-muted);
            font-weight: 500;
            width: 40%;
        }

        td {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: var(--text-main);
        }

        tr:last-child th, tr:last-child td {
            border-bottom: none;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            border-top: 1px solid var(--card-border);
            padding-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Práctica 1 (UD1) — Entorno de Desarrollo con Docker</h1>
            <p class="subtitle">0613 · Desarrollo Web en Entorno Servidor · 2º DAW</p>
        </header>

        <div class="grid">
            <!-- Contenedor Web -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">🌐 Servidor Web</span>
                    <span class="badge badge-success">Activo</span>
                </div>
                <div class="card-body">
                    <p><strong>Software:</strong> <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Nginx Alpine') ?></p>
                    <p><strong>Puerto anfitrión:</strong> 8080</p>
                    <p><strong>Rol:</strong> Proxy inverso y FastCGI hacia PHP-FPM.</p>
                </div>
            </div>

            <!-- Contenedor PHP -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">🐘 Intérprete PHP</span>
                    <span class="badge badge-success">PHP <?= htmlspecialchars(PHP_VERSION) ?></span>
                </div>
                <div class="card-body">
                    <p><strong>Modo:</strong> <?= php_sapi_name() ?></p>
                    <p><strong>Extensión PDO:</strong> <?= extension_loaded('pdo_mysql') ? '✅ Instalada (pdo_mysql)' : '❌ No encontrada' ?></p>
                    <p><strong>Rol:</strong> Procesamiento de scripts PHP 8.3.</p>
                </div>
            </div>

            <!-- Contenedor Base de Datos -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">🗄️ Base de Datos</span>
                    <?php if ($dbConnected): ?>
                        <span class="badge badge-success">Conectado</span>
                    <?php else: ?>
                        <span class="badge badge-error">Sin conexión</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p><strong>Motor:</strong> MySQL 8.0</p>
                    <p><strong>Host:</strong> <?= htmlspecialchars($host) ?></p>
                    <p><strong>Estado PDO:</strong> <?= $dbConnected ? '✅ Conexión exitosa' : '❌ Fallo de conexión' ?></p>
                </div>
            </div>
        </div>

        <section class="details-section">
            <h2>⚙️ Detalles de la Conexión PDO a MySQL</h2>
            <table>
                <tr>
                    <th>Host del contenedor DB</th>
                    <td><?= htmlspecialchars($host) ?> (puerto 3306)</td>
                </tr>
                <tr>
                    <th>Nombre de la Base de Datos</th>
                    <td><?= htmlspecialchars($dbName) ?></td>
                </tr>
                <tr>
                    <th>Usuario configurado</th>
                    <td><?= htmlspecialchars($user) ?></td>
                </tr>
                <tr>
                    <th>Versión de MySQL Server</th>
                    <td><?= htmlspecialchars($serverVersion) ?></td>
                </tr>
                <tr>
                    <th>Consulta de prueba (SELECT NOW())</th>
                    <td><?= htmlspecialchars($currentDbTime ?? 'N/A') ?></td>
                </tr>
            </table>

            <?php if (!$dbConnected): ?>
                <div class="alert-error">
                    <strong>Error de conexión:</strong> <?= htmlspecialchars($dbError ?? 'Error desconocido') ?><br>
                    <small>Si acabas de levantar los contenedores, MySQL puede tardar unos segundos en inicializar la base de datos por primera vez. Recarga la página en unos instantes.</small>
                </div>
            <?php endif; ?>
        </section>

        <footer>
            Entorno multi-contenedor configurado con Docker Compose (Nginx + PHP 8.3 FPM + MySQL 8.0)
        </footer>
    </div>
</body>
</html>
