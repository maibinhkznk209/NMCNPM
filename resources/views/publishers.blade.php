@extends('layouts.app')

@section('title', 'Quản lý nhà xuất bản - Hệ thống thư viện')

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
      grid-template-columns: 1fr;
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

    /* Enhanced navigation button styles */
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
    
    .add-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .add-btn:hover::before {
      left: 100%;
    }
    
    /* Button container for proper spacing */
    .button-group {
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
    }
    
    /* Navigation button color schemes */
    .nav-home {
      background: linear-gradient(135deg, #f39c12, #e67e22) !important;
      box-shadow: 0 4px 15px rgba(243, 156, 18, 0.2) !important;
    }
    
    .nav-home:hover {
      background: linear-gradient(135deg, #e67e22, #d35400) !important;
      box-shadow: 0 8px 25px rgba(230, 126, 34, 0.3) !important;
    }
    
    .nav-books {
      background: linear-gradient(135deg, #3498db, #2980b9) !important;
      box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2) !important;
    }
    
    .nav-books:hover {
      background: linear-gradient(135deg, #2980b9, #1f618d) !important;
      box-shadow: 0 8px 25px rgba(41, 128, 185, 0.3) !important;
    }
    
    .nav-genres {
      background: linear-gradient(135deg, #667eea, #764ba2) !important;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2) !important;
    }
    
    .nav-genres:hover {
      background: linear-gradient(135deg, #764ba2, #6a4c93) !important;
      box-shadow: 0 8px 25px rgba(118, 75, 162, 0.3) !important;
    }
    
    .nav-authors {
      background: linear-gradient(135deg, #38b2ac, #319795) !important;
      box-shadow: 0 4px 15px rgba(56, 178, 172, 0.2) !important;
    }
    
    .nav-authors:hover {
      background: linear-gradient(135deg, #319795, #2c7a7b) !important;
      box-shadow: 0 8px 25px rgba(49, 151, 149, 0.3) !important;
    }
    
    .add-publisher-btn {
      background: linear-gradient(135deg, #48bb78, #38a169) !important;
      box-shadow: 0 4px 15px rgba(72, 187, 120, 0.2) !important;
    }
    
    .add-publisher-btn:hover {
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

    tbody tr:hover {
      background: #f8fafc;
      transform: scale(1.01);
    }

    .actions {
      display: flex;
      gap: 10px;
      justify-content: center;
      align-items: center;
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
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
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
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
      max-width: 500px;
      width: 90%;
      animation: slideIn 0.3s ease-out;
    }

    .modal h2 {
      margin-bottom: 25px;
      color: #2d3748;
      text-align: center;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #4a5568;
      font-weight: 500;
    }

    .form-group input, .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 16px;
      transition: border-color 0.3s ease;
      font-family: inherit;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }

    .form-group input:focus, .form-group textarea:focus {
      outline: none;
      border-color: #4299e1;
    }

    .modal-actions {
      display: flex;
      gap: 15px;
      justify-content: flex-end;
      margin-top: 30px;
    }

    .cancel-btn {
      background: linear-gradient(135deg, #e2e8f0, #cbd5e0);
      color: #4a5568;
      border-radius: 20px;
      box-shadow: 0 3px 10px rgba(160, 174, 192, 0.2);
    }

    .cancel-btn:hover {
      background: linear-gradient(135deg, #cbd5e0, #a0aec0);
      transform: translateY(-1px);
      box-shadow: 0 5px 15px rgba(160, 174, 192, 0.3);
    }

    .save-btn {
      background: linear-gradient(135deg, #4299e1, #3182ce);
      color: white;
      border-radius: 20px;
      box-shadow: 0 3px 10px rgba(66, 153, 225, 0.2);
    }

    .save-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
    }

    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background: #48bb78;
      color: white;
      padding: 15px 20px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
      z-index: 1001;
      display: none;
    }

    .toast.error {
      background: #e53e3e;
    }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slideIn { from { opacity: 0; transform: translate(-50%, -50%) scale(0.8); } to { opacity: 1; transform: translate(-50%, -50%) scale(1); } }
    @keyframes slideInRight { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
  </style>
@endpush

@section('content')
  <div class="container">
    <div class="header">
      <h1>🏢 Hệ thống quản lý nhà xuất bản</h1>
      <p>Dành cho nhân viên thư viện</p>
    </div>

    <!-- Toast notifications -->
    <div id="toast" class="toast" style="display: none;">
      <span id="toast-message"></span>
    </div>
    
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-number" id="totalPublishers">0</div>
        <div class="stat-label">Tổng số nhà xuất bản</div>
      </div>
    </div>
    
    <div class="controls">
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Tìm kiếm nhà xuất bản..." />
        <span class="search-icon">🔍</span>
      </div>
      <div class="button-group">
        <a href="{{ route('home') }}" class="add-btn nav-home">
          🏠 Trang chủ
        </a>
        <button class="add-btn add-publisher-btn" onclick="openModal()">➕ Thêm nhà xuất bản mới</button>
      </div>
    </div>
    
    <div class="table-container">
      <table id="publishersTable">
        <thead>
          <tr>
            <th>Tên nhà xuất bản</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody id="publishersTableBody"></tbody>
      </table>
      <div class="empty-state" id="emptyState" style="display: none; text-align: center; padding: 60px 20px; color: #718096;">
        <div style="font-size: 4rem; margin-bottom: 20px;">🏢</div>
        <h3>Chưa có nhà xuất bản nào</h3>
        <p>Hãy thêm nhà xuất bản đầu tiên!</p>
      </div>
    </div>
  </div>
  
  <div class="modal" id="publisherModal">
    <div class="modal-content">
      <h2 id="modalTitle">Thêm nhà xuất bản mới</h2>
      <form id="publisherForm">
        <div class="form-group">
          <label for="publisherName">Tên nhà xuất bản *</label>
          <input type="text" id="publisherName" required />
        </div>
        <div class="modal-actions">
          <button type="button" class="btn cancel-btn" onclick="closeModal()">Hủy</button>
          <button type="submit" class="btn save-btn">Lưu</button>
        </div>
      </form>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // Lấy CSRF token từ meta
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Khởi tạo dữ liệu từ server
  let publishers = [
    @foreach ($nhaXuatBans as $nxb)
      {
        id: {{ $nxb->id }},
        name: '{{ addslashes($nxb->TenNXB) }}',
                        dateAdded: '{{ date("Y-m-d") }}',
      },
    @endforeach
  ];
  let filteredPublishers = [...publishers];
  let currentEditId = null;

  // Hàm AJAX chung dùng fetch
  async function goApi(url, method, data = null) {
    const opts = {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      }
    };
    if (data) opts.body = JSON.stringify(data);
    
    const res = await fetch(url, opts);
    const responseData = await res.json();
    
    if (!res.ok) {
      throw new Error(responseData.message || 'Có lỗi xảy ra');
    }
    
    return responseData;
  }

  // Hiển thị thông báo toast
  function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    
    toastMessage.textContent = message;
    toast.style.background = type === 'success' ? '#48bb78' : '#e53e3e';
    toast.style.display = 'block';
    
    setTimeout(() => {
      toast.style.display = 'none';
    }, 3000);
  }

  // Cập nhật số liệu
  function updateStats() {
    document.getElementById('totalPublishers').textContent = publishers.length;
  }

  // Vẽ lại bảng
  function renderPublishers() {
    const tbody = document.getElementById('publishersTableBody');
    const emptyState = document.getElementById('emptyState');
    if (filteredPublishers.length === 0) {
      tbody.innerHTML = '';
      emptyState.style.display = 'block';
    } else {
      emptyState.style.display = 'none';
      tbody.innerHTML = '';
      filteredPublishers.forEach((publisher, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${publisher.name}</td>
          <td class="actions">
            <button class="btn edit-btn" onclick="openModal(${publisher.id})">✏️ Sửa</button>
            <button class="btn delete-btn" onclick="deletePublisher(${publisher.id})">🗑️ Xóa</button>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }
    updateStats();
  }

  // Tìm kiếm
  function searchPublishers() {
    const term = document.getElementById('searchInput').value.toLowerCase();
    filteredPublishers = term
      ? publishers.filter(publisher => 
          publisher.name.toLowerCase().includes(term) || 
          (publisher.address && publisher.address.toLowerCase().includes(term))
        )
      : [...publishers];
    renderPublishers();
  }

  // Mở modal (thêm hoặc sửa)
  function openModal(id = null) {
    currentEditId = null;
    const title = document.getElementById('modalTitle');
    const nameInput = document.getElementById('publisherName');
    
    if (id) {
      const publisher = publishers.find(p => p.id === id);
      if (publisher) {
        currentEditId = publisher.id;
        title.textContent = 'Sửa nhà xuất bản';
        nameInput.value = publisher.name;
      }
    } else {
      title.textContent = 'Thêm nhà xuất bản mới';
      document.getElementById('publisherForm').reset();
    }
    document.getElementById('publisherModal').style.display = 'block';
  }

  // Đóng modal
  function closeModal() {
    document.getElementById('publisherModal').style.display = 'none';
    currentEditId = null;
  }

  // Lưu (thêm hoặc cập nhật)
  async function savePublisher(e) {
    e.preventDefault();
    const name = document.getElementById('publisherName').value.trim();
    
    if (!name) {
      showToast('Vui lòng nhập tên nhà xuất bản', 'error');
      return;
    }

    try {
      let response;
      const data = { TenNXB: name };
      
      if (currentEditId) {
        // Cập nhật
        response = await goApi(`/api/nhaxuatban/${currentEditId}`, 'PUT', data);
        if (response.success) {
          // Cập nhật trong mảng publishers
          publishers = publishers.map(publisher =>
            publisher.id === currentEditId ? { 
              ...publisher, 
              name: response.data.TenNXB
            } : publisher
          );
          showToast('Cập nhật nhà xuất bản thành công!');
        }
      } else {
        // Tạo mới
        response = await goApi('/api/nhaxuatban', 'POST', data);
        if (response.success) {
          publishers.push({
            id: response.data.id,
            name: response.data.TenNXB,
                            dateAdded: new Date().toISOString().split('T')[0]
          });
          showToast('Thêm nhà xuất bản thành công!');
        }
      }
      searchPublishers();
      closeModal();
    } catch (err) {
      console.error('Error:', err);
      showToast('Lỗi khi lưu nhà xuất bản: ' + err.message, 'error');
    }
  }

  // Xóa
  async function deletePublisher(id) {
    const publisher = publishers.find(p => p.id === id);
    if (!publisher) return;
    
    if (!confirm(`Bạn có chắc chắn xóa nhà xuất bản "${publisher.name}"?`)) return;
    
    try {
      const response = await goApi(`/api/nhaxuatban/${id}`, 'DELETE');
      if (response.success) {
        publishers = publishers.filter(p => p.id !== id);
        searchPublishers();
        showToast('Xóa nhà xuất bản thành công!');
      } else {
        showToast(response.message || 'Không thể xóa nhà xuất bản', 'error');
      }
    } catch (err) {
      console.error('Error:', err);
      showToast('Lỗi khi xóa: ' + err.message, 'error');
    }
  }

  // Error handling for uncaught errors
  window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    showToast('Có lỗi JavaScript xảy ra', 'error');
  });

  // Khởi chạy khi DOM sẵn sàng
  document.addEventListener('DOMContentLoaded', () => {
    console.log('Publishers loaded:', publishers.length);
    console.log('CSRF Token:', csrfToken);
    document.getElementById('searchInput').addEventListener('input', searchPublishers);
    document.getElementById('publisherForm').addEventListener('submit', savePublisher);
    document.getElementById('publisherModal').addEventListener('click', e => {
      if (e.target === e.currentTarget) closeModal();
    });
    renderPublishers();
  });
</script>
@endpush
