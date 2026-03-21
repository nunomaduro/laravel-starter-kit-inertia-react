<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    width: 1200px; height: 630px; overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    display: flex; align-items: center; justify-content: center;
}
.card {
    width: 1100px; padding: 60px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 24px;
}
.category {
    display: inline-block; padding: 6px 16px;
    background: #3b82f6; color: #fff;
    border-radius: 999px; font-size: 14px; font-weight: 600;
    margin-bottom: 24px; text-transform: uppercase; letter-spacing: 0.05em;
}
h1 {
    font-size: 56px; line-height: 1.15; font-weight: 800;
    color: #f8fafc; margin-bottom: 20px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.excerpt { font-size: 22px; color: #94a3b8; line-height: 1.5; max-height: 3em; overflow: hidden; }
.footer { margin-top: 40px; display: flex; align-items: center; gap: 12px; }
.site-name { font-size: 18px; color: #64748b; font-weight: 600; }
</style>
</head>
<body>
<div class="card">
    @if($category ?? null)
    <div class="category">{{ $category }}</div>
    @endif
    <h1>{{ $title }}</h1>
    @if($excerpt ?? null)
    <div class="excerpt">{{ $excerpt }}</div>
    @endif
    <div class="footer">
        <div class="site-name">{{ config('app.name') }}</div>
    </div>
</div>
</body>
</html>
