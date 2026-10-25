<style>
:root{
  --purple:#4A2574; --purple-deep:#331A52; --gold:#C9962C; --gold-soft:#E8C97A;
  --paper:#FBF7F1; --ink:#1C1B1F; --muted:#6B6878; --line:#E6E0D4;
  --danger:#B3261E; --danger-bg:#FCEBEA; --ok:#1E6B4A; --ok-bg:#E6F4EC;
  --radius:10px;
}
*{box-sizing:border-box;margin:0;padding:0}
*,::before,::after{box-shadow:none!important;text-shadow:none!important}
body{font-family:'Inter',system-ui,sans-serif;background:var(--paper);color:var(--ink);-webkit-font-smoothing:antialiased}
h1,h2,h3{font-family:'Fraunces',Georgia,serif;font-weight:600;letter-spacing:-0.01em}
button{font-family:inherit}

/* ===== Login ===== */
.auth{min-height:100vh;display:grid;grid-template-columns:1.05fr 1fr}
.auth-brand{position:relative;overflow:hidden;background:linear-gradient(155deg,var(--purple) 0%,var(--purple-deep) 100%);color:#FBF7F1;padding:56px;display:flex;flex-direction:column;justify-content:space-between;gap:32px}
.auth-brand::before{content:"";position:absolute;right:-140px;top:-140px;width:420px;height:420px;border-radius:50%;border:1px solid rgba(232,201,122,.35)}
.auth-brand::after{content:"";position:absolute;right:-60px;top:-60px;width:260px;height:260px;border-radius:50%;border:1px solid rgba(232,201,122,.2)}
.auth-logo{position:relative;z-index:1;width:min(300px,70%);background:#FBF7F1;padding:16px 20px;border-radius:var(--radius)}
.auth-logo img{display:block;width:100%;height:auto}
.auth-brand h2{position:relative;z-index:1;font-size:clamp(1.8rem,3vw,2.4rem);line-height:1.15;max-width:16ch}
.auth-brand p{position:relative;z-index:1;margin-top:14px;color:rgba(251,247,241,.78);max-width:42ch;line-height:1.6;font-size:.95rem}
.auth-foot{position:relative;z-index:1;font-size:.78rem;color:rgba(251,247,241,.6);border-top:1px solid rgba(251,247,241,.16);padding-top:16px}
.auth-panel{display:flex;align-items:center;justify-content:center;padding:40px 28px}
.auth-card{width:100%;max-width:400px}
.auth-card h1{font-size:1.9rem}
.auth-sub{margin-top:8px;color:var(--muted);font-size:.95rem;line-height:1.5}
.alert{margin-top:22px;padding:12px 14px;border-radius:8px;font-size:.88rem;line-height:1.45}
.alert-error{background:var(--danger-bg);color:var(--danger);border:1px solid #F3C9C6}
.alert-ok{background:var(--ok-bg);color:var(--ok);border:1px solid #BFE0CF}
.field{margin-top:18px}
.field label{display:block;font-size:.8rem;font-weight:600;letter-spacing:.02em;margin-bottom:6px}
.input-wrap{position:relative}
.input{width:100%;padding:12px 14px;font-size:.95rem;border:1.5px solid var(--line);border-radius:8px;background:#fff;color:var(--ink);transition:border-color .15s}
.input:focus{outline:none;border-color:var(--purple)}
.input.is-invalid{border-color:var(--danger)}
.input-wrap .input{padding-right:70px}
.toggle-pw{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--purple);font-size:.8rem;font-weight:600;cursor:pointer;padding:6px 8px;border-radius:6px}
.toggle-pw:hover{background:rgba(74,37,116,.08)}
.field-error{margin-top:6px;color:var(--danger);font-size:.8rem}
.check{display:flex;align-items:center;gap:8px;margin-top:16px;font-size:.85rem;color:var(--muted);cursor:pointer}
.check input{accent-color:var(--purple);width:16px;height:16px}
.btn-primary{width:100%;margin-top:24px;padding:13px 18px;background:var(--purple);color:#FBF7F1;border:none;border-radius:8px;font-size:.95rem;font-weight:600;cursor:pointer;transition:background .15s,transform .05s}
.btn-primary:hover{background:var(--purple-deep)}
.btn-primary:active{transform:translateY(1px)}
.btn-primary:focus-visible{outline:3px solid var(--gold);outline-offset:2px}
.btn-primary:disabled{opacity:.6;cursor:not-allowed}
.auth-note{margin-top:26px;font-size:.78rem;color:var(--muted);text-align:center}
@media (max-width:860px){
  .auth{grid-template-columns:1fr}
  .auth-brand{padding:28px 24px;gap:18px}
  .auth-brand p,.auth-foot{display:none}
  .auth-brand h2{font-size:1.5rem}
  .auth-logo{width:180px;padding:10px 14px}
}
@media (max-width:480px){
  .auth-panel{padding:24px 16px}
  .auth-card h1{font-size:1.6rem}
}

/* ===== Admin shell ===== */
.adm{min-height:100vh;display:grid;grid-template-columns:250px 1fr}
.adm-side{background:var(--purple-deep);color:#FBF7F1;padding:24px 16px;display:flex;flex-direction:column;gap:24px;position:sticky;top:0;height:100vh;overflow-y:auto}
.adm-brand{font-family:'Fraunces',serif;font-size:1.25rem;font-weight:600;padding:0 10px}
.adm-brand span{color:var(--gold)}
.adm-nav{display:flex;flex-direction:column;gap:2px}
.adm-link{display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:8px;text-decoration:none;font-size:.9rem;color:rgba(251,247,241,.78)}
.adm-link:hover{background:rgba(251,247,241,.08);color:#FBF7F1}
.adm-link.active{background:rgba(232,201,122,.16);color:var(--gold-soft)}
.adm-link.soon{opacity:.45;pointer-events:none}
.adm-pill{font-size:.62rem;letter-spacing:.06em;text-transform:uppercase;border:1px solid rgba(251,247,241,.3);border-radius:99px;padding:2px 7px}
.adm-main{display:flex;flex-direction:column;min-width:0}
.adm-top{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:18px 32px;border-bottom:1px solid var(--line);background:#fff;position:sticky;top:0;z-index:40}
.adm-top h1{font-size:1.4rem}
.adm-user{display:flex;align-items:center;gap:14px;font-size:.85rem;color:var(--muted)}
.btn-ghost{background:none;border:1.5px solid var(--line);border-radius:8px;padding:8px 14px;font-size:.82rem;font-weight:600;cursor:pointer;color:var(--ink)}
.btn-ghost:hover{border-color:var(--purple);color:var(--purple)}
.adm-content{padding:32px}
.card{background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:24px}
.grid-4{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px}
.stat .n{font-family:'Fraunces',serif;font-size:2rem;color:var(--purple)}
.stat .l{font-size:.82rem;color:var(--muted);margin-top:2px}

/* Tables and Form Grids Responsiveness */
.adm-mobile-header { display: none; }
.card:has(table), .table-wrap {
  overflow-x: auto !important;
  -webkit-overflow-scrolling: touch;
}
.card > table, .table-wrap > table {
  min-width: 560px;
}
.form-grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-grid-3 { display:grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

@media (max-width:860px){
  .adm{
    display:flex;
    flex-direction:column;
    min-height:100vh;
  }
  .adm-mobile-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:var(--purple-deep);
    padding:14px 18px;
    border-bottom:1px solid rgba(251,247,241,.14);
    position:sticky;
    top:0;
    z-index:50;
  }
  .adm-mobile-header .adm-brand{
    color:#FBF7F1;
    font-family:'Fraunces',Georgia,serif;
    font-size:1.25rem;
    font-weight:600;
    padding:0;
  }
  .adm-mobile-header .adm-brand span{
    color:var(--gold);
  }
  .adm-brand-desktop{
    display:none;
  }
  .adm-nav-toggle{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:rgba(251,247,241,.12);
    color:#FBF7F1;
    border:1px solid rgba(251,247,241,.24);
    border-radius:8px;
    padding:8px 14px;
    font-size:.85rem;
    font-weight:600;
    cursor:pointer;
    transition:background .15s ease,border-color .15s ease;
  }
  .adm-nav-toggle:hover{
    background:rgba(251,247,241,.2);
    border-color:rgba(251,247,241,.35);
  }
  .adm-side{
    display:none;
    padding:16px 18px 20px;
    border-bottom:1px solid rgba(201,150,44,.28);
    background:#27133f;
    box-shadow:0 14px 30px rgba(0,0,0,.45);
    gap:12px;
  }
  .adm-side.open{
    display:flex;
    flex-direction:column;
    animation:admNavDown .2s cubic-bezier(.16,1,.3,1);
  }
  @keyframes admNavDown{
    from{opacity:0;transform:translateY(-8px)}
    to{opacity:1;transform:translateY(0)}
  }
  .adm-nav{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:8px;
    width:100%;
  }
  .adm-link{
    display:flex;
    align-items:center;
    justify-content:space-between;
    white-space:nowrap;
    padding:11px 14px;
    font-size:.86rem;
    font-weight:500;
    background:rgba(251,247,241,.06);
    border:1px solid rgba(251,247,241,.12);
    border-radius:8px;
    color:rgba(251,247,241,.88);
    text-decoration:none;
    transition:background .15s ease,border-color .15s ease,color .15s ease;
  }
  .adm-link:hover{
    background:rgba(251,247,241,.14);
    border-color:rgba(251,247,241,.28);
    color:#FBF7F1;
  }
  .adm-link.active{
    background:rgba(201,150,44,.2);
    border-color:var(--gold);
    color:var(--gold-soft);
    font-weight:600;
  }
  @media (max-width:360px){
    .adm-nav{
      grid-template-columns:1fr;
    }
  }
  .adm-top,.adm-content{
    padding-left:18px;
    padding-right:18px;
  }
}

@media (max-width:680px){
  .form-grid-2, .form-grid-3, form div[style*="grid-template-columns"] {
    grid-template-columns: 1fr !important;
    gap: 14px !important;
  }
}

@media (max-width:640px){
  .adm-top{
    flex-direction:column;
    align-items:flex-start;
    gap:12px;
    padding:16px 14px;
  }
  .adm-top h1{
    font-size:1.25rem;
  }
  .adm-user{
    width:100%;
    flex-wrap:wrap;
    gap:8px;
    justify-content:flex-start;
  }
  .adm-content{
    padding:16px 12px;
  }
  .card{
    padding:18px 14px;
  }
}
</style>