{{-- «Entrar como»: aviso fijo mientras una cuenta principal usa la cuenta de otra persona.
     El botón cierra sesión, que en este caso regresa a la cuenta principal. --}}
@if(\App\Support\Suplantacion::activa())
  <div role="status" style="position: fixed; left: 50%; bottom: 16px; transform: translateX(-50%); z-index: 10000; width: max-content; max-width: calc(100vw - 32px); display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 10px 14px; padding: 0.55rem 0.6rem 0.55rem 1rem; border-radius: 14px; background: #1A2620; color: #F1F5F3; border: 1px solid rgba(168,230,192,0.35); box-shadow: 0 10px 28px rgba(0,0,0,0.28); font-family: 'Manrope', sans-serif; font-size: 0.82rem; line-height: 1.3;">
    <span style="display: inline-flex; align-items: center; gap: 8px;">
      <i class="fa-solid fa-user-secret" style="color: #A8E6C0;"></i>
      <span>Estás dentro de la cuenta de <strong style="color: #A8E6C0;">{{ auth()->user()?->name }}</strong></span>
    </span>
    <form action="{{ route('logout') }}" method="POST" data-no-spa style="margin: 0;">
      @csrf
      <button type="submit" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.4rem 0.8rem; border: none; border-radius: 9px; background: #A8E6C0; color: #1A2620; font-family: inherit; font-size: 0.78rem; font-weight: 700; cursor: pointer;">
        <i class="fa-solid fa-arrow-rotate-left"></i>
        <span>Volver a mi cuenta</span>
      </button>
    </form>
  </div>
@endif
