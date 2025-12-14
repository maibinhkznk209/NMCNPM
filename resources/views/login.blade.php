@extends('layouts.auth')

@section('title', 'Đăng nhập - Hệ thống thư viện')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
  <div class="bg-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>
  <div class="login-container">
    <div class="header">
      <div class="logo">📚</div>
      <h2>Đăng nhập</h2>
      <p class="subtitle">Chào mừng bạn quay trở lại</p>
    </div>
    
    @if ($errors->any())
      <div class="error-message" style="display: block;">
        @foreach ($errors->all() as $error)
          {{ $error }}<br>
        @endforeach
      </div>
    @endif
    
    @if (session('success'))
      <div class="success-message" style="display: block;">
        {{ session('success') }}
      </div>
    @endif
    
    @if (session('error'))
      <div class="error-message" style="display: block;">
        {{ session('error') }}
      </div>
    @endif
    
    <div id="errorMessage" class="error-message"></div>
    <div id="successMessage" class="success-message"></div>
    
    <form id="loginForm" method="POST" action="{{ route('login.post') }}">
      @csrf
      <div class="form-group">
        <label for="email">Email</label>
        <div class="input-wrapper">
          <input type="email" id="email" name="email" required placeholder="you@example.com" autocomplete="email" value="{{ old('email') }}" />
          <span class="input-icon">📧</span>
        </div>
      </div>
      
      <div class="form-group">
        <label for="password">Mật khẩu</label>
        <div class="input-wrapper">
          <input type="password" id="password" name="password" required placeholder="••••••••" autocomplete="current-password" />
          <span class="input-icon password-toggle" id="passwordToggle">👁️</span>
        </div>
      </div>
      
      <button type="submit" id="loginButton">
        <span>Đăng nhập</span>
      </button>
    </form>
    
    <div class="links">
      <div class="link-item">
        <a href="{{ route('home') }}">← Quay lại trang chủ</a>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
// Password toggle functionality
const passwordToggle = document.getElementById('passwordToggle');
const passwordInput = document.getElementById('password');

passwordToggle.addEventListener('click', function() {
    const type = passwordInput.type === 'password' ? 'text' : 'password';
    passwordInput.type = type;
    passwordToggle.textContent = type === 'password' ? '👁️' : '🙈';
});

// Handle form submission with AJAX
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = document.getElementById('loginButton');
    const errorMessage = document.getElementById('errorMessage');
    const successMessage = document.getElementById('successMessage');
    
    // Clear previous messages
    errorMessage.style.display = 'none';
    successMessage.style.display = 'none';
    
    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<span>Đang đăng nhập...</span>';
    
    try {
        const formData = new FormData(this);
        const response = await fetch('{{ route("login.api") }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            successMessage.textContent = result.message;
            successMessage.style.display = 'block';
            
            // Store user info in localStorage for client-side access
            localStorage.setItem('username', result.user.username);
            localStorage.setItem('role', result.user.role);
            localStorage.setItem('email', result.user.email);
            localStorage.setItem('user_id', result.user.id);
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = '{{ route("home") }}';
            }, 1500);
        } else {
            errorMessage.textContent = result.message || 'Đăng nhập không thành công';
            errorMessage.style.display = 'block';
        }
    } catch (error) {
        console.error('Login error:', error);
        errorMessage.textContent = 'Có lỗi xảy ra. Vui lòng thử lại.';
        errorMessage.style.display = 'block';
    } finally {
        // Reset button state
        submitButton.disabled = false;
        submitButton.innerHTML = '<span>Đăng nhập</span>';
    }
});

// Auto-hide messages after 5 seconds
setTimeout(() => {
    const messages = document.querySelectorAll('.error-message, .success-message');
    messages.forEach(msg => {
        if (msg.style.display === 'block') {
            msg.style.display = 'none';
        }
    });
}, 5000);
</script>
@endpush