<!DOCTYPE html>
<html>

<head>
    <title>Debug Session Data</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: #4ec9b0;
        }

        h2 {
            color: #569cd6;
            margin-top: 30px;
        }

        pre {
            background: #252526;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 3px solid #007acc;
        }

        .success {
            color: #4ec9b0;
        }

        .error {
            color: #f48771;
        }

        .warning {
            color: #dcdcaa;
        }

        .section {
            margin-bottom: 30px;
            background: #252526;
            padding: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🔍 Session Debug Information</h1>

        <div class="section">
            <h2>📋 Session Status</h2>
            @if (session()->has('accident_application_data'))
                <p class="success">✅ Accident Application Data: EXISTS</p>
            @else
                <p class="error">❌ Accident Application Data: NOT FOUND</p>
            @endif

            @if (session()->has('accident_api_response'))
                <p class="success">✅ Accident API Response: EXISTS</p>
            @else
                <p class="warning">⚠️ Accident API Response: NOT FOUND</p>
            @endif

            @if (session()->has('property_application_data'))
                <p class="success">✅ Property Application Data: EXISTS</p>
            @else
                <p class="error">❌ Property Application Data: NOT FOUND</p>
            @endif

            @if (session()->has('property_api_response'))
                <p class="success">✅ Property API Response: EXISTS</p>
            @else
                <p class="warning">⚠️ Property API Response: NOT FOUND</p>
            @endif
        </div>

        @if (session()->has('accident_application_data'))
            <div class="section">
                <h2>🚗 Accident Application Data</h2>
                <pre>{{ json_encode(session('accident_application_data'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

        @if (session()->has('accident_api_response'))
            <div class="section">
                <h2>📡 Accident API Response</h2>
                <pre>{{ json_encode(session('accident_api_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

        @if (session()->has('property_application_data'))
            <div class="section">
                <h2>🏠 Property Application Data</h2>
                <pre>{{ json_encode(session('property_application_data'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

        @if (session()->has('property_api_response'))
            <div class="section">
                <h2>📡 Property API Response</h2>
                <pre>{{ json_encode(session('property_api_response'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        @endif

        <div class="section">
            <h2>📦 All Session Data</h2>
            <pre>{{ json_encode(session()->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>

        <div class="section">
            <h2>🔧 Environment Info</h2>
            <pre>
APP_ENV: {{ env('APP_ENV') }}
APP_DEBUG: {{ env('APP_DEBUG') ? 'true' : 'false' }}
SESSION_DRIVER: {{ env('SESSION_DRIVER', 'file') }}
SESSION_LIFETIME: {{ env('SESSION_LIFETIME', 120) }} minutes
DB_CONNECTION: {{ env('DB_CONNECTION', 'mysql') }}
            </pre>
        </div>
    </div>
</body>

</html>



