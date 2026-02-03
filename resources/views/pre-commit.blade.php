<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project_name ?? 'Cometa Aspro Sync API' }} — Pre-Commit</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 1200px; margin: 0 auto; padding: 20px; background-color: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; border-left: 4px solid #3498db; padding-left: 15px; }
        h3 { color: #7f8c8d; margin-top: 25px; }
        code { background-color: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; font-size: 0.9em; }
        pre { background-color: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; margin: 15px 0; }
        pre code { background: none; padding: 0; color: inherit; }
        .error { background-color: #e74c3c; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info-box { background-color: #3498db; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .info-box a { color: white; text-decoration: underline; font-weight: bold; }
        .info-box a:hover { color: #ecf0f1; text-decoration: none; }
        a { color: #3498db; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .doc-content h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; margin-top: 0; }
        .doc-content h2 { color: #34495e; margin-top: 30px; border-left: 4px solid #3498db; padding-left: 15px; }
        .doc-content h3 { color: #7f8c8d; margin-top: 25px; }
        .doc-content p { margin: 15px 0; line-height: 1.7; }
        .doc-content ul, .doc-content ol { margin: 15px 0; padding-left: 30px; }
        .doc-content li { margin: 8px 0; line-height: 1.6; }
        .doc-content blockquote { border-left: 4px solid #3498db; margin: 20px 0; padding: 15px 20px; background-color: #f8f9fa; border-radius: 0 5px 5px 0; }
        .doc-content table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .doc-content th, .doc-content td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .doc-content th { background-color: #3498db; color: white; font-weight: bold; }
        .doc-content tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pre-Commit</h1>

        <div class="info-box">
            <a href="{{ route('about') }}">← Информация о проекте</a>
        </div>

        @if(isset($error))
            <div class="error"><strong>Ошибка:</strong> {{ $error }}</div>
        @endif

        @if(isset($pre_commit_html) && !empty($pre_commit_html))
            <div class="doc-content">
                {!! $pre_commit_html !!}
            </div>
        @endif
    </div>
</body>
</html>
