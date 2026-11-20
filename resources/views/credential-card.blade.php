<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Карточка учётных данных — {{ $name ?? '' }}</title>
<style>
  :root{
    --bg:#0B1220; --surface:#16243D; --light:#F3F4F6; --accent:#38BDF8;
    --text:#E6EDF5; --muted:#94A3B8; --ok:#22C55E; --border:#1F2D44;
  }
  *{box-sizing:border-box}
  body{
    margin:0; background:var(--bg); color:var(--text);
    font-family:"Golos Text","Manrope",-apple-system,Segoe UI,Roboto,Arial,sans-serif;
    padding:24px; line-height:1.5;
  }
  .wrap{max-width:720px; margin:0 auto;}
  .card{
    background:linear-gradient(180deg,var(--surface),#0F1A2E);
    border:1px solid var(--border); border-radius:18px; overflow:hidden;
    box-shadow:0 20px 60px rgba(0,0,0,.45);
  }
  .head{
    padding:28px 32px; border-bottom:1px solid var(--border);
    display:flex; align-items:center; gap:16px; flex-wrap:wrap;
  }
  .logo{
    width:46px; height:46px; border-radius:12px; flex:0 0 auto;
    background:var(--accent); display:flex; align-items:center; justify-content:center;
    color:var(--bg); font-weight:800; font-family:"Manrope",Arial,sans-serif; font-size:22px;
  }
  .brand{font-size:22px; font-weight:700; letter-spacing:.5px; color:var(--accent);}
  .brand small{display:block; font-size:12px; font-weight:500; color:var(--muted); letter-spacing:0;}
  .body{padding:24px 32px 8px;}
  .emp{font-size:20px; font-weight:700; margin:0 0 4px;}
  .sub{color:var(--muted); font-size:13px; margin:0 0 20px;}
  .sec{margin:0 0 18px;}
  .sec h3{
    margin:0 0 10px; font-size:13px; text-transform:uppercase; letter-spacing:.8px;
    color:var(--accent); font-weight:700;
  }
  .grid{display:grid; grid-template-columns:130px 1fr; gap:8px 14px; font-size:14px;}
  .grid .k{color:var(--muted);}
  .grid .v{font-family:"JetBrains Mono",ui-monospace,SFMono-Regular,Menlo,monospace;
           background:#0B1220; border:1px solid var(--border); border-radius:8px;
           padding:6px 10px; word-break:break-all;}
  .note{margin:18px 0 0; padding:14px 16px; border-radius:10px;
        background:rgba(56,189,248,.08); border:1px solid rgba(56,189,248,.3);
        color:#cfe9f7; font-size:13px;}
  .note b{color:var(--accent);}
  .foot{padding:16px 32px; color:var(--muted); font-size:12px;
        border-top:1px solid var(--border); display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px;}
  .btn{display:inline-block; margin:18px 0 0; background:var(--accent); color:var(--bg);
       font-weight:700; border:none; border-radius:10px; padding:12px 22px; font-size:14px;
       cursor:pointer; text-decoration:none;}
  @media print{
    body{background:#fff; color:#0B1220; padding:0;}
    .card{box-shadow:none; border:1px solid #ddd;}
    .head,.body,.foot{color:#0B1220;}
    .grid .v{background:#F3F4F6; border-color:#ddd; color:#0B1220;}
    .btn,.note{display:none;}
    .brand,.sec h3{color:#0B1220;}
  }
  @media (max-width:520px){
    .grid{grid-template-columns:1fr;}
    .grid .k{margin-top:6px;}
  }
</style>
</head>
<body>
<div class="wrap">
  <a class="btn" href="#" onclick="window.print();return=false;">🖨 Печать / Сохранить в PDF</a>
  <div class="card">
    <div class="head">
      <div class="logo">V</div>
      <div class="brand">vitteza.ru<small>корпоративная система учёта перевозок</small></div>
    </div>
    <div class="body">
      <p class="emp">{{ $name ?? '' }}</p>
      <p class="sub">Карточка учётных данных сотрудника · {{ $date ?? now()->format('d.m.Y') }}</p>

      <div class="sec">
        <h3>Вход в систему</h3>
        <div class="grid">
          <span class="k">Адрес</span><span class="v">{{ $app_url ?? '' }}</span>
          <span class="k">Логин</span><span class="v">{{ $login ?? '' }}</span>
          <span class="k">Пароль</span><span class="v">{{ $app_password ?? '' }}</span>
        </div>
      </div>

      @if(!empty($mail_email))
      <div class="sec">
        <h3>Корпоративная почта</h3>
        <div class="grid">
          <span class="k">E-mail</span><span class="v">{{ $mail_email }}</span>
          <span class="k">Пароль</span><span class="v">{{ $mail_password ?? '' }}</span>
        </div>
      </div>

      <div class="sec">
        <h3>Настройки почтового клиента (Thunderbird / Outlook / мобильные)</h3>
        <div class="grid">
          <span class="k">Входящая IMAP</span><span class="v">imap.vitteza.ru · порт 993 · SSL/TLS</span>
          <span class="k">Исходящая SMTP</span><span class="v">smtp.vitteza.ru · порт 465 SSL / 587 STARTTLS</span>
          <span class="k">Логин почты</span><span class="v">{{ $mail_email }} (полный адрес)</span>
        </div>
      </div>

      <div class="note">
        <b>Через сборщик mail.ru / Яндекс:</b> зайдите в свой ящик на mail.ru или yandex.ru,
        в настройках включите «Сборщик почты» / «Сбор почты» и добавьте внешний аккаунт
        <b>{{ $mail_email }}</b> по IMAP (сервер <b>imap.vitteza.ru</b>, порт 993, SSL) —
        корпоративная почта будет приходить в ваш привычный интерфейс.
      </div>
      @endif

      <div class="note">
        <b>Безопасность:</b> после первого входа рекомендуется сменить пароль.
        Не передавайте эту карточку третьим лицам. При утере — обратитесь к администратору для сброса.
      </div>
    </div>
    <div class="foot">
      <span>VITTEZA · сгенерировано системой учёта</span>
      <span>{{ $date ?? now()->format('d.m.Y H:i') }}</span>
    </div>
  </div>
</div>
</body>
</html>
