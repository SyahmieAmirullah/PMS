<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied</title>
    <style>
      body { font-family: Arial, sans-serif; background: #f6f7fb; color: #222; margin: 0; }
      .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
      .card { max-width: 520px; width: 100%; background: #fff; border: 1px solid #e6e8ef; border-radius: 12px; padding: 28px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
      h1 { margin: 0 0 8px; font-size: 22px; }
      p { margin: 0 0 16px; line-height: 1.5; color: #4b5563; }
      .code { font-size: 12px; color: #9ca3af; }
      .actions a { display: inline-block; padding: 10px 14px; border-radius: 8px; text-decoration: none; margin-right: 8px; }
      .primary { background: #111827; color: #fff; }
      .secondary { background: #eef2f7; color: #111827; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <div class="card">
        <h1>Access denied</h1>
        <p>You do not have access to this page or module. Please contact the administrator if you believe this is a mistake.</p>
        <div class="actions">
          <a class="primary" href="/dashboard">Go to Dashboard</a>
          <a class="secondary" href="javascript:history.back()">Go Back</a>
        </div>
        <p class="code">Error 403</p>
      </div>
    </div>
  </body>
</html>
