<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Restricted</title>
    <style>
      body { font-family: Arial, sans-serif; background: rgba(17,24,39,0.45); color: #111; margin: 0; }
      .wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
      .modal { max-width: 420px; width: 100%; background: #fff; border-radius: 12px; padding: 20px 22px; box-shadow: 0 12px 30px rgba(0,0,0,0.2); }
      h1 { margin: 0 0 6px; font-size: 18px; }
      p { margin: 0 0 14px; line-height: 1.4; color: #4b5563; }
      .actions { display: flex; gap: 8px; }
      .actions a, .actions button { flex: 1; text-align: center; padding: 9px 12px; border-radius: 8px; text-decoration: none; font-size: 14px; border: none; cursor: pointer; }
      .primary { background: #111827; color: #fff; }
      .secondary { background: #eef2f7; color: #111827; }
    </style>
  </head>
  <body>
    <div class="wrap">
      <div class="modal" role="dialog" aria-modal="true" aria-labelledby="title" aria-describedby="message">
        <h1 id="title">Access Restricted</h1>
        <p id="message">
          {{ $exception->getMessage() ?: 'You do not have permission to access this action.' }}
        </p>
        <div class="actions">
          <button class="secondary" type="button" onclick="history.back()">Go Back</button>
          <a class="primary" href="/dashboard">Dashboard</a>
        </div>
      </div>
    </div>
  </body>
</html>
