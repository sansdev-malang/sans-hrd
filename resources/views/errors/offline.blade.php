<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedang Offline - SANS HRD</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background-color: #09090b;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            text-align: center;
        }
        .card {
            background: rgba(24, 24, 27, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
        }
        .icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(79, 70, 229, 0.15);
            border: 1px solid rgba(79, 70, 229, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #818cf8;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }
        p {
            font-size: 0.925rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn-retry {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 0.875rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
            transition: all 0.2s ease;
        }
        .btn-retry:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .badge {
            margin-top: 1.5rem;
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="1" y1="1" x2="23" y2="23"></line>
                <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
                <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
                <path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path>
                <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
                <path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
                <line x1="12" y1="20" x2="12.01" y2="20"></line>
            </svg>
        </div>
        <h1>Koneksi Terputus</h1>
        <p>Anda sedang tidak terhubung ke jaringan internet. Halaman ini akan otomatis dimuat ulang saat koneksi internet kembali stabil.</p>
        <button class="btn-retry" onclick="window.location.reload()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
            Muat Ulang Halaman
        </button>
        <div class="badge">SANS HRD &bull; Offline Mode</div>
    </div>

    <script>
        window.addEventListener('online', () => {
            window.location.reload();
        });
    </script>
</body>
</html>
