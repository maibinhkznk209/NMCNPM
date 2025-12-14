@extends('layouts.auth')

@section('title', 'Quên mật khẩu - Hệ thống thư viện')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/forgot-password.css') }}">
@endpush

@section('content')
  <div class="bg-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <div class="forgot-container">
    <div class="header-section">
      <div class="icon-wrapper"></div>
      <h2>Quên mật khẩu</h2>
      <p>Nhập địa chỉ email của bạn và chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu</p>
    </div>

    <div class="success-message" id="successMessage">
      ✅ Email đặt lại mật khẩu đã được gửi! Vui lòng kiểm tra hộp thư của bạn.
    </div>

    <form id="forgotForm" action="#" method="POST">
      <div class="form-group">
        <label for="email">Địa chỉ email</label>
        <div class="input-wrapper">
          <input type="email" id="email" name="email" required placeholder="Nhập email của bạn">
          <span class="input-icon">📧</span>
        </div>
      </div>
      
      <button type="submit" class="submit-button" id="submitBtn">
        Gửi yêu cầu đặt lại
      </button>
      
      <div class="back-link">
        <a href="{{ route('home') }}" onclick="goBack()">← Quay lại đăng nhập</a>
      </div>
    </form>
  </div>
@endsection

@section('scripts')
  <script>
    document.getElementById('forgotForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const submitBtn = document.getElementById('submitBtn');
      const successMessage = document.getElementById('successMessage');
      const email = document.getElementById('email').value;
      
      if (!email) {
        alert('Vui lòng nhập địa chỉ email!');
        return;
      }
      
      // Add loading state
      submitBtn.classList.add('loading');
      submitBtn.disabled = true;
      
      // Simulate API call
      setTimeout(() => {
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        successMessage.style.display = 'block';
        
        // Hide success message after 5 seconds
        setTimeout(() => {
          successMessage.style.display = 'none';
        }, 5000);
        
        // Reset form
        document.getElementById('email').value = '';
      }, 2000);
    });
    
    function goBack() {
      // In a real application, this would navigate back to the login page
      alert('Chuyển về trang đăng nhập...');
    }
    
    // Add floating animation to input focus
    document.getElementById('email').addEventListener('focus', function() {
      this.parentElement.style.transform = 'translateY(-2px)';
    });
    
    document.getElementById('email').addEventListener('blur', function() {
      this.parentElement.style.transform = 'translateY(0)';
    });
  </script>
@endsection