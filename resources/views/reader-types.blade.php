@extends('layouts.app')

@section('title', 'Quản lý loại độc giả - Hệ thống thư viện')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/books.css') }}">
<style>
    /* Additional styles for reader-types page */
    .search-select-container.error {
        border: 2px solid #e74c3c !important;
        border-radius: 4px;
        background-color: #fdf2f2;
    }
    
    .search-select-container {
        transition: border-color 0.3s ease, background-color 0.3s ease;
        border-radius: 4px;
        padding: 2px;
    }
    
    .form-group label small {
        font-weight: normal;
        margin-left: 5px;
    }
    
    /* Color scheme for navigation buttons */
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
    
    .add-reader-type-btn {
      background: linear-gradient(135deg, #48bb78, #38a169) !important;
      box-shadow: 0 4px 15px rgba(72, 187, 120, 0.2) !important;
    }
    
    .add-reader-type-btn:hover {
      background: linear-gradient(135deg, #38a169, #2f855a) !important;
      box-shadow: 0 8px 25px rgba(56, 161, 105, 0.3) !important;
    }
    
    /* Action button styles */
    .btn {
      padding: 8px 15px;
      border: none;
      border-radius: 20px;
      font-weight: 600;
      font-size: 12px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      text-decoration: none;
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
    
    .type-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .type-info small {
        color: #6c757d;
        font-size: 11px;
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="header">
            <h1>📂 Quản lý loại độc giả</h1>
            <p>Hệ thống quản lý các loại độc giả trong thư viện</p>
        </div>

        {{-- Thẻ thông báo thành công/lỗi --}}
        @if (session('success'))
            <div class="toast success" id="toast-message">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="toast error" id="toast-message">{{ session('error') }}</div>
        @endif

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $loaiDocGias->count() }}</div>
                <div class="stat-label">Tổng loại độc giả</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $docGiaCount ?? 0 }}</div>
                <div class="stat-label">Tổng độc giả</div>
            </div>
        </div>
        
        <form action="{{ route('reader-types.index') }}" method="GET">        
            <div class="controls">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Tìm kiếm loại độc giả..." value="{{ request('search') }}" />
                    <span class="search-icon">🔍</span>
                </div>
                {{-- Giữ lại bộ lọc khi tìm kiếm --}}
                @if (request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
                <div class="button-group">
                    <a href="{{ route('home') }}" class="add-btn nav-home">
                        🏠 Trang chủ
                    </a>
                    <button type="button" class="add-btn add-reader-type-btn" onclick="openAddModal()">➕ Thêm loại độc giả</button>
                </div>
            </div>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên loại độc giả</th>
                        <th>Số lượng độc giả</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loaiDocGias as $loaiDocGia)
                        <tr>
                            <td>{{ $loaiDocGia->MaLoaiDocGia }}</td>
                            <td>
                                <div class="type-info">
                                    <strong>{{ $loaiDocGia->TenLoaiDocGia }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="genre-tag" style="cursor: default; background: #e3f2fd;">
                                    {{ $loaiDocGia->readers_count ?? 0 }} độc giả
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn edit-btn" onclick="openEditModal('{{ $loaiDocGia->MaLoaiDocGia }}')">✏️ Sửa</button>
                                <form action="{{ route('reader-types.destroy', $loaiDocGia->MaLoaiDocGia) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa loại độc giả này?\n\nLưu ý: Không thể xóa nếu còn độc giả thuộc loại này.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn delete-btn">🗑️ Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div style="font-size: 4rem; margin-bottom: 20px;">📂</div>
                                    <h3>Chưa có loại độc giả nào</h3>
                                    <p>Hãy thêm loại độc giả đầu tiên!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>

    {{-- Modal thêm loại độc giả --}}
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>➕ Thêm loại độc giả mới</h3>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form action="{{ route('reader-types.store') }}" method="POST" id="addReaderTypeForm" onsubmit="return validateAddForm()">
                @csrf
                <div class="form-group">
                    <label for="TenLoaiDocGia">Tên loại độc giả *</label>
                    <input type="text" id="TenLoaiDocGia" name="TenLoaiDocGia" required placeholder="VD: Sinh viên, Giảng viên, Cán bộ...">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm loại độc giả</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal sửa loại độc giả --}}
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Sửa loại độc giả</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form action="" method="POST" id="editReaderTypeForm" onsubmit="return validateEditForm()">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="editTenLoaiDocGia">Tên loại độc giả *</label>
                    <input type="text" id="editTenLoaiDocGia" name="TenLoaiDocGia" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Script đơn giản để ẩn thông báo sau vài giây
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('toast-message');
        if (toast) {
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    });

    // Modal functions
    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
        document.getElementById('TenLoaiDocGia').focus();
    }

    function closeAddModal() {
        document.getElementById('addModal').style.display = 'none';
        document.getElementById('addReaderTypeForm').reset();
    }

    function openEditModal(id) {
        fetch(`/reader-types/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const readerType = data.data;
                    document.getElementById('editTenLoaiDocGia').value = readerType.TenLoaiDocGia;
                    
                    document.getElementById('editReaderTypeForm').action = `/reader-types/${id}`;
                    document.getElementById('editModal').style.display = 'block';
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi tải thông tin loại độc giả', 'error');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editReaderTypeForm').reset();
    }

    // Toast notification function
    function showToast(message, type = 'success') {
        // Remove existing toast if any
        const existingToast = document.querySelector('.toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const addModal = document.getElementById('addModal');
        const editModal = document.getElementById('editModal');
        
        if (event.target == addModal) {
            closeAddModal();
        }
        if (event.target == editModal) {
            closeEditModal();
        }
    }

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (addModal.style.display === 'block') {
                closeAddModal();
            }
            if (editModal.style.display === 'block') {
                closeEditModal();
            }
        }
    });

    // Validation functions
    function validateAddForm() {
        let isValid = true;
        
        // Kiểm tra tên loại độc giả
        const tenLoaiDocGia = document.getElementById('TenLoaiDocGia');
        if (!tenLoaiDocGia.value.trim()) {
            showToast('Vui lòng nhập tên loại độc giả', 'error');
            tenLoaiDocGia.focus();
            isValid = false;
        } else if (tenLoaiDocGia.value.trim().length < 2) {
            showToast('Tên loại độc giả phải có ít nhất 2 ký tự', 'error');
            tenLoaiDocGia.focus();
            isValid = false;
        }
        
        return isValid;
    }

    function validateEditForm() {
        let isValid = true;
        
        // Kiểm tra tên loại độc giả
        const tenLoaiDocGia = document.getElementById('editTenLoaiDocGia');
        if (!tenLoaiDocGia.value.trim()) {
            showToast('Vui lòng nhập tên loại độc giả', 'error');
            tenLoaiDocGia.focus();
            isValid = false;
        } else if (tenLoaiDocGia.value.trim().length < 2) {
            showToast('Tên loại độc giả phải có ít nhất 2 ký tự', 'error');
            tenLoaiDocGia.focus();
            isValid = false;
        }
        
        return isValid;
    }
</script>
@endpush
