<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project_name ?? 'МЮЗ - Backend' }} - Информация о проекте</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #34495e;
            margin-top: 30px;
            border-left: 4px solid #3498db;
            padding-left: 15px;
        }
        h3 {
            color: #7f8c8d;
            margin-top: 25px;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.9em;
        }
        pre {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 15px 0;
        }
        pre code {
            background: none;
            padding: 0;
            color: inherit;
        }
        ul, ol {
            margin: 10px 0;
            padding-left: 25px;
        }
        li {
            margin: 5px 0;
        }
        .error {
            background-color: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #3498db;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .info-box a {
            color: white;
            text-decoration: underline;
            font-weight: bold;
        }
        
        .info-box a:hover {
            color: #ecf0f1;
            text-decoration: none;
        }
        .endpoint {
            background-color: #ecf0f1;
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid #3498db;
            border-radius: 3px;
        }
        .tech-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }
        .tech-item {
            background-color: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9em;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .header-info {
            background-color: #ecf0f1;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        /* Стили для Markdown контента */
        .readme-content h1 {
            color: #2c3e50;
            border-bottom: 3px solid #3498db;
            padding-bottom: 10px;
            margin-top: 0;
        }
        
        .readme-content h2 {
            color: #34495e;
            margin-top: 30px;
            border-left: 4px solid #3498db;
            padding-left: 15px;
        }
        
        .readme-content h3 {
            color: #7f8c8d;
            margin-top: 25px;
        }
        
        .readme-content h4 {
            color: #95a5a6;
            margin-top: 20px;
        }
        
        .readme-content p {
            margin: 15px 0;
            line-height: 1.7;
        }
        
        .readme-content ul, .readme-content ol {
            margin: 15px 0;
            padding-left: 30px;
        }
        
        .readme-content li {
            margin: 8px 0;
            line-height: 1.6;
        }
        
        .readme-content blockquote {
            border-left: 4px solid #3498db;
            margin: 20px 0;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-radius: 0 5px 5px 0;
        }
        
        .readme-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .readme-content th, .readme-content td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .readme-content th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        .readme-content tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .readme-content hr {
            border: none;
            height: 2px;
            background-color: #ecf0f1;
            margin: 30px 0;
        }
        
        .readme-content strong {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .readme-content em {
            font-style: italic;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(isset($error))
            <div class="error">
                <strong>Ошибка:</strong> {{ $error }}
            </div>
        @endif

        @if(isset($readme_html) && !empty($readme_html))
            <div class="readme-content">
                {!! $readme_html !!}
            </div>
        @elseif(isset($readme_content) && !empty($readme_content))
            <div class="readme-content">
                {!! nl2br(e($readme_content)) !!}
            </div>
        @else
            <div class="info-box">
                <strong>Информация:</strong> Содержимое README.md недоступно
            </div>
        @endif

        <div class="tech-stack">
            <span class="tech-item">Laravel 12</span>
            <span class="tech-item">PHP 8.2+</span>
            <span class="tech-item">PostgreSQL</span>
            {{-- <span class="tech-item">MoonShine</span> --}}
            <span class="tech-item">JWT</span>
            <span class="tech-item">AWS S3</span>
            <span class="tech-item">Swagger/OpenAPI</span>
        </div>

        <div class="info-box">
            <strong>Полезные ссылки:</strong><br>
            <a href="{{ url('/api') }}" target="_blank">API</a> |
            <a href="{{ url('/api/swagger') }}" target="_blank">Swagger UI</a> |
            <a href="{{ url('/api/docs') }}" target="_blank">API Swagger JSON</a> |
            <a href="{{ route('pre-commit') }}">Pre-Commit</a>@env('local')
            | <a href="{{ url('/telescope') }}" target="_blank">Telescope</a>
            | <a href="{{ url('/horizon') }}" target="_blank">Horizon</a>@endenv
        </div>
    </div>
</body>
</html>
