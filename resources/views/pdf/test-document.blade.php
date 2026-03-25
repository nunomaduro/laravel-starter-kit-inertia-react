<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Document' }}</title>
</head>
<body>
    <h1>{{ $title ?? 'Test Document' }}</h1>
    @if(isset($body))
        <p>{{ $body }}</p>
    @endif
</body>
</html>
