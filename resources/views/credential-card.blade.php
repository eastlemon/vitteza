<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('credential.title') }} — {{ $name ?? '' }}</title>
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
  <a class="btn" href="#" onclick="window.print();return=false;">{{ __('credential.print') }}</a>
  <div class="card">
    <div class="head">
      <div class="logo">V</div>
      <div class="brand">vitteza.ru<small>{{ __('credential.brand_sub') }}</small></div>
    </div>
    <div class="body">
      <p class="emp">{{ $name ?? '' }}</p>
      <p class="sub">{{ __('credential.subtitle') }} · {{ $date ?? now()->format('d.m.Y') }}</p>

      <div class="sec">
        <h3>{{ __('credential.system_section') }}</h3>
        <div class="grid">
          <span class="k">{{ __('credential.address') }}</span><span class="v">{{ $app_url ?? '' }}</span>
          <span class="k">{{ __('credential.login') }}</span><span class="v">{{ $login ?? '' }}</span>
          <span class="k">{{ __('credential.password') }}</span><span class="v">{{ $app_password ?? '' }}</span>
        </div>
      </div>

      @if(!empty($mail_email))
      <div class="sec">
        <h3>{{ __('credential.mail_section') }}</h3>
        <div class="grid">
          <span class="k">E-mail</span><span class="v">{{ $mail_email }}</span>
          <span class="k">{{ __('credential.password') }}</span><span class="v">{{ $mail_password ?? '' }}</span>
        </div>
      </div>

      <div class="sec">
        <h3>{{ __('credential.client_section') }}</h3>
        <div class="grid">
          <span class="k">{{ __('credential.imap') }}</span><span class="v">{{ __('credential.imap_value') }}</span>
          <span class="k">{{ __('credential.smtp') }}</span><span class="v">{{ __('credential.smtp_value') }}</span>
          <span class="k">{{ __('credential.mail_login') }}</span><span class="v">{{ $mail_email }} {{ __('credential.full_address') }}</span>
        </div>
      </div>

      <div class="note">
        <b>{{ __('credential.collector_title') }}</b> {{ __('credential.collector_p1') }}
        {{ __('credential.collector_p2') }}
        <b>{{ $mail_email }}</b> {{ __('credential.collector_p3') }}
        {{ __('credential.collector_p4') }}
      </div>
      @endif

      <div class="note">
        <b>{{ __('credential.security_title') }}</b> {{ __('credential.security_p1') }}
        {{ __('credential.security_p2') }}
      </div>
    </div>
    <div class="foot">
      <span>VITTEZA · {{ __('credential.footer') }}</span>
      <span>{{ $date ?? now()->format('d.m.Y H:i') }}</span>
    </div>
  </div>
</div>
</body>
</html>
