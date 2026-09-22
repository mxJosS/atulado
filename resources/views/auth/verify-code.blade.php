@extends('layouts.guest')

@section('title', 'Verificación de Correo — A tu lado')

@section('content')
<div class="auth-page-wrapper" style="min-height: 90vh; background: #080C0A !important; color: #FFFFFF !important; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2.5rem 1.5rem; position: relative; overflow: hidden;">

  <!-- Ambient Halos -->
  <div style="position: absolute; width: 600px; height: 600px; border-radius: 50%; background: radial-gradient(circle, rgba(90, 181, 110, 0.22) 0%, transparent 68%); top: 50%; left: 50%; transform: translate(-50%, -60%); pointer-events: none;"></div>
  <div style="position: absolute; width: 450px; height: 450px; border-radius: 50%; background: radial-gradient(circle, rgba(91, 74, 138, 0.2) 0%, transparent 68%); bottom: -10%; right: -5%; pointer-events: none;"></div>

  <div style="width: 100%; max-width: 480px; position: relative; z-index: 2;">

    <!-- Main Card -->
    <div style="background: #FFFFFF !important; border-radius: 24px; padding: 2.5rem 2.25rem; box-shadow: var(--shadow-lg); border: 1.5px solid #DCE8E0; color: #1A2620;">
      
      <!-- Card Header -->
      <div style="text-align: center; margin-bottom: 1.75rem;">
        <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; margin-bottom: 1rem;">
          <x-logo :size="32" />
          <span style="font-family: var(--font-display); font-size: 1.35rem; color: #2E5D4B; font-weight: 700;">a tu <em class="editorial-italic" style="color: #5AB56E;">lado</em></span>
        </a>

        <div style="width: 52px; height: 52px; background: #E8F5E9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; color: #2E5D4B; font-size: 1.5rem;">
          <i class="fa-solid fa-envelope-circle-check"></i>
        </div>

        <h1 style="font-family: var(--font-display); font-size: 1.65rem; font-weight: 700; color: #1A2620; line-height: 1.25; margin-bottom: 0.5rem;">
          Verifica tu correo electrónico
        </h1>
        <p style="font-size: 0.9rem; color: #556860; line-height: 1.5;">
          Hemos enviado un código de 6 dígitos a:<br>
          <strong style="color: #1A2620; font-weight: 600;">{{ auth()->user()->email }}</strong>
        </p>
      </div>

      <!-- Feedback Alerts -->
      @if (session('status'))
        <div style="background: #E8F5E9; border-left: 4px solid #2E5D4B; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.86rem; color: #1B5E20; line-height: 1.4;">
          <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i>
          {{ session('status') }}
        </div>
      @endif

      @if (session('info'))
        <div style="background: #E3F2FD; border-left: 4px solid #1976D2; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.86rem; color: #0D47A1; line-height: 1.4;">
          <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i>
          {{ session('info') }}
        </div>
      @endif

      @if ($errors->any())
        <div style="background: #FFEBEE; border-left: 4px solid #D32F2F; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.86rem; color: #C62828; line-height: 1.4;">
          <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i>
          {{ $errors->first('code') ?: $errors->first() }}
        </div>
      @endif

      <!-- Verification Form -->
      <form method="POST" action="{{ route('verification.code.verify') }}" id="verifyCodeForm" style="margin-bottom: 1.5rem;">
        @csrf

        <label style="display: block; font-size: 0.82rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #4A5B53; margin-bottom: 0.75rem; text-align: center;">
          Ingresa el código de 6 dígitos
        </label>

        <!-- Segmented 6-digit Boxes -->
        <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 1.25rem;" id="digitInputsContainer">
          @for ($i = 0; $i < 6; $i++)
            <input 
              type="text" 
              class="digit-box" 
              id="digit-{{ $i }}" 
              data-index="{{ $i }}"
              maxlength="1" 
              inputmode="numeric" 
              pattern="[0-9]*" 
              autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
              style="width: 46px; height: 56px; text-align: center; font-size: 1.5rem; font-weight: 700; font-family: var(--font-mono, monospace); color: #1B5E20; background: #F8FAF9; border: 2px solid #DCE6E1; border-radius: 12px; outline: none; transition: all 0.2s ease;"
              onfocus="this.style.borderColor='#2E5D4B'; this.style.background='#FFFFFF'; this.style.boxShadow='0 0 0 3px rgba(46, 93, 75, 0.15)';"
              onblur="this.style.borderColor='#DCE6E1'; this.style.background='#F8FAF9'; this.style.boxShadow='none';"
            />
          @endfor
        </div>

        <!-- Hidden full code field -->
        <input type="hidden" name="code" id="hiddenFullCode" required>

        <button 
          type="submit" 
          id="submitVerifyBtn"
          style="width: 100%; background: #2E5D4B; color: #FFFFFF; border: none; border-radius: 12px; padding: 0.9rem 1.25rem; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(46, 93, 75, 0.25); display: flex; align-items: center; justify-content: center; gap: 8px;"
          onmouseover="this.style.background='#244A3C';"
          onmouseout="this.style.background='#2E5D4B';"
        >
          <i class="fa-solid fa-circle-check"></i>
          <span>Verificar y continuar</span>
        </button>
      </form>

      <!-- Resend & Alternative Actions -->
      <div style="text-align: center; border-top: 1px solid #EEF3F0; padding-top: 1.25rem;">
        <p style="font-size: 0.85rem; color: #62756D; margin-bottom: 0.65rem;">
          ¿No recibiste el correo o ya expiró?
        </p>

        <form method="POST" action="{{ route('verification.code.resend') }}" id="resendCodeForm" style="display: inline-block;">
          @csrf
          <button 
            type="submit" 
            id="resendBtn"
            style="background: transparent; border: 1.5px solid #2E5D4B; color: #2E5D4B; border-radius: 10px; padding: 0.5rem 1rem; font-size: 0.84rem; font-weight: 600; cursor: pointer; transition: all 0.2s;"
            onmouseover="this.style.background='#F0F7F3';"
            onmouseout="this.style.background='transparent';"
          >
            <i class="fa-solid fa-rotate-right" style="margin-right: 5px;"></i>
            <span id="resendText">Reenviar código</span>
          </button>
        </form>

        <div style="margin-top: 1.25rem; font-size: 0.82rem; color: #7B8F85;">
          ¿El correo está mal escrito? 
          <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #C0392B; text-decoration: underline; font-size: 0.82rem; cursor: pointer; padding: 0; font-weight: 500;">
              Cerrar sesión e intentar con otro
            </button>
          </form>
        </div>

      </div>

    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const digits = document.querySelectorAll('.digit-box');
  const hiddenCode = document.getElementById('hiddenFullCode');
  const form = document.getElementById('verifyCodeForm');

  if (digits.length > 0) {
    digits[0].focus();
  }

  function updateHiddenCode() {
    let full = '';
    digits.forEach(d => {
      full += d.value.trim();
    });
    hiddenCode.value = full;
    return full;
  }

  digits.forEach((digit, index) => {
    // Handle typing
    digit.addEventListener('input', function(e) {
      const val = this.value.replace(/[^0-9]/g, '');
      this.value = val ? val.slice(-1) : '';

      if (this.value && index < digits.length - 1) {
        digits[index + 1].focus();
      }

      const full = updateHiddenCode();
      if (full.length === 6) {
        form.submit();
      }
    });

    // Handle backspace navigation
    digit.addEventListener('keydown', function(e) {
      if (e.key === 'Backspace') {
        if (!this.value && index > 0) {
          digits[index - 1].focus();
          digits[index - 1].value = '';
          updateHiddenCode();
        } else {
          this.value = '';
          updateHiddenCode();
        }
      }
    });

    // Handle paste event (e.g. copying 6 digits from email)
    digit.addEventListener('paste', function(e) {
      e.preventDefault();
      const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
      if (pasteData.length > 0) {
        const chars = pasteData.slice(0, 6).split('');
        chars.forEach((char, i) => {
          if (digits[i]) {
            digits[i].value = char;
          }
        });
        const nextIndex = Math.min(chars.length, digits.length - 1);
        digits[nextIndex].focus();
        const full = updateHiddenCode();
        if (full.length === 6) {
          form.submit();
        }
      }
    });
  });

  form.addEventListener('submit', function(e) {
    const full = updateHiddenCode();
    if (full.length !== 6) {
      e.preventDefault();
      alert('Por favor ingresa el código completo de 6 dígitos.');
    }
  });

  // Resend cooldown timer
  const resendBtn = document.getElementById('resendBtn');
  const resendText = document.getElementById('resendText');
  let countdown = 60;
  
  // Start countdown on first load or after resend
  function startCooldown() {
    resendBtn.disabled = true;
    resendBtn.style.opacity = '0.6';
    resendBtn.style.cursor = 'not-allowed';

    const interval = setInterval(() => {
      countdown--;
      resendText.textContent = `Reenviar en ${countdown}s`;
      if (countdown <= 0) {
        clearInterval(interval);
        resendBtn.disabled = false;
        resendBtn.style.opacity = '1';
        resendBtn.style.cursor = 'pointer';
        resendText.textContent = 'Reenviar código';
      }
    }, 1000);
  }

  // Activate timer if recently loaded or redirected with status
  @if (session('status'))
    startCooldown();
  @endif
});
</script>
@endsection
