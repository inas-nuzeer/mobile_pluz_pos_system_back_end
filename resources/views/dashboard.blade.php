<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - POS Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --bg: #0f172a;
            --card-bg: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .user-info h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .badge {
            background-color: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .logout-btn {
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background-color: var(--bg);
            padding: 1.5rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="user-info">
                <h1>Hello, {{ auth()->user()->name }}</h1>
                <span class="badge">{{ auth()->user()->role }}</span>
            </div>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="logout-btn">Log Out</button>
            </form>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 1.5rem;">Your Shop Context</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Shop Name</div>
                    <div class="stat-value">{{ auth()->user()->shop->name }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Email</div>
                    <div class="stat-value" style="font-size: 1.1rem;">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>

        <div style="text-align: center; color: var(--text-muted); font-size: 0.875rem;">
            POS Backend is running. You can now use your Flutter app to connect.
        </div>
    </div>
</body>
</html>
