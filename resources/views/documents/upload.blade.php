<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents - AgentLoom</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; margin: 24px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; background: #fff; }
        .error { color: #b91c1c; margin: 8px 0 0; }
        .message { margin: 0 0 16px; padding: 12px 14px; border-radius: 10px; background: #ecfeff; border: 1px solid #a5f3fc; }
        label { display: block; font-weight: 600; margin: 12px 0 6px; }
        input[type="file"] { width: 100%; }
        .btn { margin-top: 16px; background: #111827; color: #fff; border: 0; border-radius: 10px; padding: 10px 14px; cursor: pointer; }
        a { color: #1d4ed8; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <h1>Document Upload</h1>

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="error">{{ $error }}</div>
        @endforeach
    @endif

    <div class="card">
        <form method="POST" action="{{ route('documents.upload.post') }}" enctype="multipart/form-data">
            @csrf

            <label>Choose one or more files</label>
            <input type="file" name="documents[]" multiple accept=".pdf,.docx,.txt,.html,.htm,text/plain,text/html" />

            <div style="margin-top: 10px; color: #6b7280; font-size: 14px;">
                Upload is fast; ingestion runs asynchronously in the background.
            </div>

            <button class="btn" type="submit">Upload</button>
        </form>
    </div>

    <div style="margin-top: 18px;">
        <a href="{{ route('workflow-runs.index') }}">View workflow history</a>
    </div>
</div>
</body>
</html>

