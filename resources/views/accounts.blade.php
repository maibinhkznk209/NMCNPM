@extends('layouts.app')

@section('title', 'Quản lý tài khoản - Hệ thống thư viện')

@push('styles')
<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      color: #2d3748;
      line-height: 1.6;
    }

    .container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
      animation: fadeInDown 1s ease-out;
    }

    .header h1 {
      color: #fff;
      font-size: 2.5rem;
      margin-bottom: 10px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .header p {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.1rem;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
      animation: fadeInUp 1s ease-out 0.2s both;
    }

    .stat-card {
      background: rgba(255, 255, 255, 0.95);
      padding: 20px;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: #4299e1;
      margin-bottom: 5px;
    }

    .stat-label {
      color: #718096;
      font-weight: 500;
    }

    .controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      animation: fadeInUp 1s ease-out 0.4s both;
      flex-wrap: wrap;
      gap: 15px;
    }

    .search-box {
      position: relative;
      flex: 1;
      max-width: 400px;
    }

    .search-box input {
      width: 100%;
      padding: 12px 45px 12px 15px;
      border: none;
      border-radius: 25px;
      font-size: 16px;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .search-box input:focus {
      outline: none;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      background: #fff;
    }

    .search-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #a0aec0;
    }

    .button-group {
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
    }

    .add-btn {
      padding: 12px 20px;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      font-size: 14px;
      text-decoration: none;
      color: white;
      background: linear-gradient(135deg, #4299e1, #3182ce);
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 4px 15px rgba(66, 153, 225, 0.2);
      white-space: nowrap;
    }
    
    .add-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .add-btn:active {
      transform: translateY(0);
    }

    .nav-home {
      background: linear-gradient(135deg, #f39c12, #e67e22) !important;
      box-shadow: 0 4px 15px rgba(243, 156, 18, 0.2) !important;
    }
    
    .nav-home:hover {
      background: linear-gradient(135deg, #e67e22, #d35400) !important;
      box-shadow: 0 8px 25px rgba(230, 126, 34, 0.3) !important;
    }

    .add-account-btn {
      background: linear-gradient(135deg, #48bb78, #38a169) !important;
      box-shadow: 0 4px 15px rgba(72, 187, 120, 0.2) !important;
    }
    
    .add-account-btn:hover {
      background: linear-gradient(135deg, #38a169, #2f855a) !important;
      box-shadow: 0 8px 25px rgba(56, 161, 105, 0.3) !important;
    }

    .table-container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: fadeInUp 1s ease-out 0.6s both;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #e2e8f0;
    }

    th {
      background: linear-gradient(135deg, #f7fafc, #edf2f7);
      color: #4a5568;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
    }

    tbody tr {
      transition: all 0.3s ease;
    }

    tbody tr:hover {
      background: #f8fafc;
      transform: scale(1.01);
    }

    .role-badge {
      padding: 5px 12px;
      border-radius: 15px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }

    .role-admin {
      background: #fed7d7;
      color: #c53030;
    }

    .role-librarian {
      background: #c6f6d5;
      color: #2f855a;
    }

    .role-reader {
      background: #bee3f8;
      color: #2b6cb0;
    }

    .actions {
      display: flex !important;
      gap: 10px !important;
      justify-content: center !important;
      align-items: center !important;
      flex-wrap: nowrap !important;
    }

    .btn {
      padding: 8px 16px;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      font-weight: 500;
      font-size: 14px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 5px !important;
      white-space: nowrap !important;
      line-height: 1 !important;
      vertical-align: middle !important;
      min-height: 36px !important;
    }

    .btn span {
      display: inline-block !important;
      vertical-align: middle !important;
      line-height: 1 !important;
    }

    .edit-btn {
      background: linear-gradient(135deg, #ed8936, #dd6b20);
      color: white;
      box-shadow: 0 3px 10px rgba(237, 137, 54, 0.2);
    }

    .edit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(237, 137, 54, 0.4);
    }

    .delete-btn {
      background: linear-gradient(135deg, #e53e3e, #c53030);
      color: white;
      box-shadow: 0 3px 10px rgba(229, 62, 62, 0.2);
    }

    .delete-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(229, 62, 62, 0.4);
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      z-index: 1000;
      animation: fadeIn 0.3s ease-out;
    }

    .modal-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
      max-width: 500px;
      width: 90%;
      animation: slideIn 0.3s ease-out;
    }

    .modal h2 {
      color: #2d3748;
      margin-bottom: 20px;
      font-size: 1.5rem;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #4a5568;
      font-weight: 600;
    }

    .form-group input {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 16px;
      transition: all 0.3s ease;
    }

    .form-group input:focus {
      outline: none;
      border-color: #4299e1;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .form-group select {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 16px;
      background: white;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .form-group select:focus {
      outline: none;
      border-color: #4299e1;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .modal-buttons {
      display: flex;
      gap: 15px;
      justify-content: flex-end;
      margin-top: 30px;
    }

    .save-btn {
      background: linear-gradient(135deg, #48bb78, #38a169);
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .save-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(72, 187, 120, 0.3);
    }

    .cancel-btn {
      background: #e2e8f0;
      color: #4a5568;
      padding: 12px 24px;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .cancel-btn:hover {
      background: #cbd5e0;
      transform: translateY(-2px);
    }

    /* Toast Notification Styles */
    .toast {
      position: fixed !important;
      top: 20px !important;
      right: 20px !important;
      padding: 16px 20px !important;
      border-radius: 12px !important;
      font-weight: 600 !important;
      font-size: 14px !important;
      z-index: 9999 !important;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
      backdrop-filter: blur(10px) !important;
      border: 1px solid rgba(255, 255, 255, 0.2) !important;
      animation: slideInRight 0.4s ease-out !important;
      max-width: 350px !important;
      display: flex !important;
      align-items: center !important;
      gap: 12px !important;
      color: white !important;
    }

    .toast.success {
      background: linear-gradient(135deg, #48bb78, #38a169) !important;
      color: white !important;
    }

    .toast.error {
      background: linear-gradient(135deg, #f56565, #e53e3e) !important;
      color: white !important;
    }

    .toast i {
      font-size: 18px !important;
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(100%);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes slideOutRight {
      from {
        opacity: 1;
        transform: translateX(0);
      }
      to {
        opacity: 0;
        transform: translateX(100%);
      }
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #718096;
    }

    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      color: #cbd5e0;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.9);
      }
      to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }
    }

    @media (max-width: 768px) {
      .container {
        padding: 10px;
      }

      .controls {
        flex-direction: column;
        align-items: stretch;
      }

      .search-box {
        max-width: none;
        margin-bottom: 15px;
      }

      .button-group {
        justify-content: center;
      }

      table {
        font-size: 14px;
      }

      th, td {
        padding: 10px 8px;
      }

      .actions {
        flex-direction: column;
        gap: 5px;
      }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="header">
        <h1>Quản Lý Tài Khoản</h1>
        <p>Quản lý tài khoản thủ thư trong hệ thống</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalAccounts">{{ $taiKhoans->count() }}</div>
            <div class="stat-label">Tổng Tài Khoản</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="adminAccounts">{{ $taiKhoans->where('vaiTro.VaiTro', 'Admin')->count() }}</div>
            <div class="stat-label">Admin</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="librarianAccounts">{{ $taiKhoans->where('vaiTro.VaiTro', 'Thủ thư')->count() }}</div>
            <div class="stat-label">Thủ Thư</div>
        </div>
    </div>

    <!-- Controls -->
    <div class="controls">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Tìm kiếm tài khoản...">
            <i class="fas fa-search search-icon"></i>
        </div>
        <div class="button-group">
            <a href="/" class="add-btn nav-home">
                <i class="fas fa-home"></i>
                🏠 Trang chủ
            </a>
            <button class="add-btn add-account-btn" onclick="openAddModal()">
                <i class="fas fa-plus"></i>
                ➕ Thêm Tài Khoản
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <h3 style="margin-bottom: 20px; color: #2d3748;">Danh Sách Tài Khoản</h3>
        <table id="accountsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Vai Trò</th>
                    <th>Ngày Tạo</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody id="accountsTableBody">
                @forelse($taiKhoans as $taiKhoan)
                <tr data-id="{{ $taiKhoan->id }}">
                    <td>{{ $taiKhoan->id }}</td>
                    <td>{{ $taiKhoan->HoVaTen }}</td>
                    <td>{{ $taiKhoan->Email }}</td>
                    <td>
                        <span class="role-badge 
                            @if($taiKhoan->vaiTro->VaiTro === 'Admin') role-admin
                            @elseif($taiKhoan->vaiTro->VaiTro === 'Thủ thư') role-librarian
                            @else role-reader
                            @endif">
                            {{ $taiKhoan->vaiTro->VaiTro }}
                        </span>
                    </td>
                    <td>{{ $taiKhoan->id }}</td>
                    <td>
                        <div class="actions">
                            <button class="btn edit-btn" onclick="openEditModal({{ $taiKhoan->id }}, '{{ $taiKhoan->HoVaTen }}', '{{ $taiKhoan->Email }}')">
                                ✏️ Sửa
                            </button>
                            <button class="btn delete-btn" onclick="deleteAccount({{ $taiKhoan->id }}, '{{ $taiKhoan->HoVaTen }}')">
                                🗑️ Xóa
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <h3>Chưa có tài khoản nào</h3>
                            <p>Hãy thêm tài khoản đầu tiên cho hệ thống</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="accountModal" class="modal">
    <div class="modal-content">
        <h2 id="modalTitle">Thêm Tài Khoản Mới</h2>
        <form id="accountForm">
            <div class="form-group">
                <label for="accountName">Họ và tên <span style="color: red;">*</span></label>
                <input type="text" id="accountName" name="HoVaTen" required>
            </div>
            <div class="form-group">
                <label for="accountEmail">Email <span style="color: red;">*</span></label>
                <input type="email" id="accountEmail" name="Email" required>
            </div>
            <div class="form-group">
                <label for="accountPassword">Mật Khẩu <span style="color: red;" id="passwordRequired">*</span></label>
                <input type="password" id="accountPassword" name="MatKhau">
                <small style="color: #718096; font-size: 12px;" id="passwordNote">Để trống nếu không muốn thay đổi mật khẩu</small>
            </div>
            <div class="form-group">
                <label for="accountPasswordConfirm">Nhập Lại Mật Khẩu <span style="color: red;" id="passwordConfirmRequired">*</span></label>
                <input type="password" id="accountPasswordConfirm" name="MatKhauConfirm">
                <small style="color: #718096; font-size: 12px;" id="passwordConfirmNote">Để trống nếu không muốn thay đổi mật khẩu</small>
            </div>
            <div class="form-group">
                <label for="accountRole">Vai Trò <span style="color: red;">*</span></label>
                <select id="accountRole" name="VaiTroId" required style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 16px; background: white; cursor: pointer; transition: all 0.3s ease;">
                    <option value="">-- Chọn Vai Trò --</option>
                    <option value="1">Admin</option>
                    <option value="2">Thủ thư</option>
                </select>
            </div>
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeModal()">Hủy</button>
                <button type="submit" class="save-btn">Lưu</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let editingAccountId = null;

// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#accountsTableBody tr');
    
    rows.forEach(row => {
        if (row.children.length === 1) return; // Skip empty state row
        
        const accountName = row.children[1].textContent.toLowerCase();
        const email = row.children[2].textContent.toLowerCase();
        
        if (accountName.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Modal functions
function openAddModal() {
    editingAccountId = null;
    document.getElementById('modalTitle').textContent = 'Thêm Tài Khoản Mới';
    document.getElementById('accountForm').reset();
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('passwordNote').style.display = 'none';
    document.getElementById('accountPassword').required = true;
    document.getElementById('passwordConfirmRequired').style.display = 'inline';
    document.getElementById('passwordConfirmNote').style.display = 'none';
    document.getElementById('accountPasswordConfirm').required = true;
    document.getElementById('accountModal').style.display = 'block';
}

function openEditModal(id, name, email) {
    editingAccountId = id;
    document.getElementById('modalTitle').textContent = 'Sửa Tài Khoản';
    document.getElementById('accountName').value = name;
    document.getElementById('accountEmail').value = email;
    document.getElementById('accountPassword').value = '';
    document.getElementById('accountPasswordConfirm').value = '';
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordNote').style.display = 'block';
    document.getElementById('accountPassword').required = false;
    document.getElementById('passwordConfirmRequired').style.display = 'none';
    document.getElementById('passwordConfirmNote').style.display = 'block';
    document.getElementById('accountPasswordConfirm').required = false;
    document.getElementById('accountModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('accountModal').style.display = 'none';
    editingAccountId = null;
}

// Password confirmation validation
document.getElementById('accountPassword').addEventListener('input', function() {
    const password = this.value;
    const confirmField = document.getElementById('accountPasswordConfirm');
    const confirmRequired = document.getElementById('passwordConfirmRequired');
    const confirmNote = document.getElementById('passwordConfirmNote');
    
    if (editingAccountId) {
        // Edit mode
        if (password) {
            confirmRequired.style.display = 'inline';
            confirmNote.style.display = 'none';
            confirmField.required = true;
        } else {
            confirmRequired.style.display = 'none';
            confirmNote.style.display = 'block';
            confirmField.required = false;
            confirmField.value = '';
        }
    }
});

// Alert functions
function showAlert(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.4s ease-out';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 400);
    }, 5000);
}

// Form submission
document.getElementById('accountForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Validate password confirmation
    if (data.MatKhau && data.MatKhau !== data.MatKhauConfirm) {
        showAlert('Mật khẩu và mật khẩu xác nhận không khớp nhau', 'error');
        return;
    }
    
    // Don't send empty password for edit
    if (editingAccountId && !data.MatKhau) {
        delete data.MatKhau;
    }
    
    // Remove password confirmation from data before sending
    delete data.MatKhauConfirm;
    
    try {
        const url = editingAccountId 
            ? `/api/tai-khoan/${editingAccountId}`
            : '/api/tai-khoan';
        const method = editingAccountId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            closeModal();
            loadAccounts();
        } else {
            showAlert(result.message || 'Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra khi xử lý yêu cầu', 'error');
    }
});

// Delete account
async function deleteAccount(id, name) {
    if (!confirm(`Bạn có chắc chắn muốn xóa tài khoản "${name}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/tai-khoan/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            loadAccounts();
        } else {
            showAlert(result.message || 'Có lỗi xảy ra khi xóa tài khoản', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Có lỗi xảy ra khi xử lý yêu cầu', 'error');
    }
}

// Load accounts
async function loadAccounts() {
    try {
        const response = await fetch('/api/tai-khoan', {
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            updateAccountsTable(result.data);
            updateStats(result.data);
        }
    } catch (error) {
        console.error('Error loading accounts:', error);
    }
}

// Update accounts table
function updateAccountsTable(accounts) {
    const tbody = document.getElementById('accountsTableBody');
    
    if (accounts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <span style="font-size: 3rem;">👥</span>
                        <h3>Chưa có tài khoản nào</h3>
                        <p>Hãy thêm tài khoản đầu tiên cho hệ thống</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = accounts.map(account => {
        const roleClass = account.vai_tro.VaiTro === 'Admin' ? 'role-admin' :
                         account.vai_tro.VaiTro === 'Thủ thư' ? 'role-librarian' : 'role-reader';
        
        return `
            <tr data-id="${account.id}">
                <td>${account.id}</td>
                <td>${account.HoVaTen}</td>
                <td>${account.Email}</td>
                <td>
                    <span class="role-badge ${roleClass}">
                        ${account.vai_tro.VaiTro}
                    </span>
                </td>
                <td>${account.id}</td>
                <td>
                    <div class="actions">
                        <button class="btn edit-btn" onclick="openEditModal(${account.id}, '${account.HoVaTen}', '${account.Email}')">
                            ✏️ Sửa
                        </button>
                        <button class="btn delete-btn" onclick="deleteAccount(${account.id}, '${account.HoVaTen}')">
                            🗑️ Xóa
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Update stats
function updateStats(accounts) {
    const totalAccounts = accounts.length;
    const adminAccounts = accounts.filter(acc => acc.vai_tro.VaiTro === 'Admin').length;
    const librarianAccounts = accounts.filter(acc => acc.vai_tro.VaiTro === 'Thủ thư').length;
    
    document.getElementById('totalAccounts').textContent = totalAccounts;
    document.getElementById('adminAccounts').textContent = adminAccounts;
    document.getElementById('librarianAccounts').textContent = librarianAccounts;
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('accountModal');
    if (event.target === modal) {
        closeModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});
</script>
@endpush