<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Document') — BagSmart Wholesale</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .print-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 820px;
            margin: 2rem auto;
            padding: 2.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        @media print {
            body {
                background: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none !important;
            }
            .print-sheet {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print bg-white border-bottom py-2 text-center sticky-top shadow-sm">
        <button onclick="window.print()" class="btn btn-primary btn-sm me-2">
            <i class="bi bi-printer-fill me-1"></i> Print / Save PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-lg me-1"></i> Close Window
        </button>
    </div>

    <div class="container">
        <div class="print-sheet">
            @yield('content')
        </div>
    </div>
</body>
</html>
