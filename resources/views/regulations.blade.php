@extends('layouts.app')

@section('title', 'Quản lý quy định - Hệ thống thư viện')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/books.css') }}">
<style>
    /* Additional styles for regulations page */
    .regulation-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid #007bff;
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .regulation-card:nth-child(1) { animation-delay: 0.1s; }
    .regulation-card:nth-child(2) { animation-delay: 0.2s; }
    .regulation-card:nth-child(3) { animation-delay: 0.3s; }
    .regulation-card:nth-child(4) { animation-delay: 0.4s; }
    .regulation-card:nth-child(5) { animation-delay: 0.5s; }
    .regulation-card:nth-child(6) { animation-delay: 0.6s; }
    
    .regulation-card:hover {
        border-left-color: #28a745;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .regulation-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    .regulation-title {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }
    
    .regulation-card:hover .regulation-title {
        color: #007bff;
    }
    
    .regulation-value {
        font-size: 24px;
        font-weight: 700;
        color: #007bff;
        margin: 0;
    }
    
    .regulation-card:hover .regulation-value {
        color: #28a745;
    }
    
    .regulation-description {
        color: #6c757d;
        font-size: 14px;
        margin-top: 8px;
    }
    
    .regulation-card:hover .regulation-description {
        color: #495057;
    }
    
    .edit-regulation-btn {
        background: linear-gradient(135deg, #ed8936, #dd6b20);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        overflow: hidden;
    }
    
    .edit-regulation-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .edit-regulation-btn:hover::before {
        left: 100%;
    }
    
    .edit-regulation-btn:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 20px rgba(237, 137, 54, 0.4);
        background: linear-gradient(135deg, #f6ad55, #ed8936);
    }
    
    .edit-regulation-btn:active {
        transform: translateY(0) scale(0.98);
    }
    
    .regulations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-top: 24px;
    }
    
    .regulation-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-right: 16px;
    }
    
    .icon-age { 
        background-color: #e3f2fd; 
        color: #1976d2; 
    }
    .icon-card { 
        background-color: #f3e5f5; 
        color: #7b1fa2;
    }
    .icon-books { 
        background-color: #e8f5e8; 
        color: #388e3c;
    }
    .icon-days { 
        background-color: #fff3e0; 
        color: #f57c00;
    }
    .icon-years { 
        background-color: #fce4ec; 
        color: #c2185b;
    }
    
    .regulation-content {
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    .regulation-info {
        flex: 1;
    }
    
    /* Navigation button styles */
    .nav-home {
        background: linear-gradient(135deg, #f39c12, #e67e22) !important;
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.2) !important;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 25px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        position: relative;
        overflow: hidden;
    }
    
    .nav-home:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4) !important;
    }
    
    .nav-home:active {
        transform: translateY(-1px) scale(1.02);
    }
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 0;
        border: none;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }
    
    .modal-header {
        background: white;
        color: #2d3748;
        padding: 20px;
        border-radius: 12px 12px 0 0;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .modal-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
    }
    
    .close {
        color: #2d3748;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    
    .close:hover {
        background-color: rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 20px;
        padding: 0 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2d3748;
    }
    
    .form-group input {
        width: 100%;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
    }
    
    .form-group input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }
    
    .form-actions {
        padding: 20px;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        background-color: #f8f9fa;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #5a6268;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #0056b3, #004085);
    }
    
    .warning-notice {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .warning-notice .icon {
        font-size: 24px;
        color: #f39c12;
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="header">
            <h1>⚙️ Quản lý quy định</h1>
            <p>Cấu hình các quy định và thông số hoạt động của hệ thống thư viện</p>
        </div>

        {{-- Thẻ thông báo thành công/lỗi --}}
        @if (session('success'))
            <div class="toast success" id="toast-message">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="toast error" id="toast-message">{{ session('error') }}</div>
        @endif
        
        <div class="controls">
            <div class="search-box" style="flex: 1;">
                <!-- Empty search box for consistency -->
            </div>
            <div class="button-group">
                <a href="{{ route('home') }}" class="nav-home">
                    🏠 Trang chủ
                </a>
            </div>
        </div>

        <div class="regulations-grid">
            @forelse ($quyDinhs as $quyDinh)
                <div class="regulation-card">
                    <div class="regulation-content">
                        <div class="regulation-icon {{ getRegulationIconClass($quyDinh->TenThamSo) }}">
                            {{ getRegulationIcon($quyDinh->TenThamSo) }}
                        </div>
                        <div class="regulation-info">
                            <div class="regulation-header">
                                <h3 class="regulation-title">{{ getFriendlyLabel($quyDinh->TenThamSo) }}</h3>
                                <button class="edit-regulation-btn" onclick="openEditModal({{ $quyDinh->id }})">
                                    ✏️ Sửa
                                </button>
                            </div>
                            <div class="regulation-value">
                                {{ $quyDinh->GiaTri }} {{ getRegulationUnit($quyDinh->TenThamSo) }}
                            </div>
                            <div class="regulation-description">
                                {{ getRegulationDescription($quyDinh->TenThamSo) }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="regulation-card">
                    <div class="empty-state">
                        <div style="font-size: 4rem; margin-bottom: 20px;">📋</div>
                        <h3>Chưa có quy định nào</h3>
                        <p>Hệ thống chưa được cấu hình quy định.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal sửa quy định --}}
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Chỉnh sửa quy định</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form action="" method="POST" id="editRegulationForm" onsubmit="return validateEditForm()">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="editTenThamSo">Tên tham số</label>
                    <input type="text" id="editTenThamSo" name="TenThamSo" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                    <small style="color: #6c757d; font-size: 12px; margin-top: 4px; display: block;">
                        Tên tham số không thể thay đổi
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="editGiaTri">Giá trị *</label>
                    <input 
                        type="number" 
                        id="editGiaTri" 
                        name="GiaTri" 
                        required 
                        min="1"
                        style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;"
                    >
                    <small id="editValidationHint" style="color: #6c757d; font-size: 12px; margin-top: 4px; display: block;">
                        Nhập giá trị hợp lệ
                    </small>
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
    // Cache regulations data
    const regulations = [
        @foreach($quyDinhs as $quyDinh)
        {
            id: {{ $quyDinh->id }},
            name: '{{ $quyDinh->TenThamSo }}',
            value: {{ $quyDinh->GiaTri }},
            validation: getValidationInfo('{{ $quyDinh->TenThamSo }}')
        },
        @endforeach
    ];

    // Helper function to get validation info
    function getValidationInfo(tenThamSo) {
        const info = {
            min: 1,
            max: 100,
            unit: '',
            description: ''
        };

        if (tenThamSo.includes('Tuoi')) {
            info.max = 100;
            info.unit = 'tuổi';
            info.description = 'Độ tuổi hợp lệ (1-100)';
        } else if (tenThamSo.includes('ThoiHan')) {
            info.max = 120;
            info.unit = 'tháng';
            info.description = 'Số tháng hợp lệ (1-120)';
        } else if (tenThamSo.includes('Ngay')) {
            info.max = 365;
            info.unit = 'ngày';
            info.description = 'Số ngày hợp lệ (1-365)';
        } else if (tenThamSo.includes('Sach')) {
            info.max = 50;
            info.unit = 'cuốn';
            info.description = 'Số sách hợp lệ (1-50)';
        } else if (tenThamSo.includes('Nam')) {
            info.max = 50;
            info.unit = 'năm';
            info.description = 'Số năm hợp lệ (1-50)';
        }

        return info;
    }

    // Modal functions
    window.openEditModal = function(id) {
        const regulation = regulations.find(r => r.id === id);
        if (!regulation) {
            showToast('Không tìm thấy quy định', 'error');
            return;
        }

        const modal = document.getElementById('editModal');
        
        // Set form values
        document.getElementById('editTenThamSo').value = regulation.name;
        
        const giaTri = document.getElementById('editGiaTri');
        giaTri.value = regulation.value;
        giaTri.min = regulation.validation.min;
        giaTri.max = regulation.validation.max;
        
        // Set validation hint
        document.getElementById('editValidationHint').textContent = `💡 ${regulation.validation.description}`;
        
        // Set form action and show modal
        document.getElementById('editRegulationForm').action = `/regulations/${id}`;
        modal.style.display = 'block';
        giaTri.focus();
    };

    window.closeEditModal = function() {
        const modal = document.getElementById('editModal');
        modal.style.display = 'none';
        document.getElementById('editRegulationForm').reset();
    };

    // Form validation
    window.validateEditForm = function() {
        const giaTri = document.getElementById('editGiaTri');
        const value = parseInt(giaTri.value);
        const min = parseInt(giaTri.min);
        const max = parseInt(giaTri.max);

        if (isNaN(value) || value < min || value > max) {
            showToast(`Giá trị phải từ ${min} đến ${max}`, 'error');
            return false;
        }
        return true;
    };

    // Toast notification function
    window.showToast = function(message, type = 'success') {
        const existingToast = document.querySelector('.toast');
        if (existingToast) {
            existingToast.remove();
        }
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            ${type === 'success' ? 'background: linear-gradient(135deg, #28a745, #20c997);' : 'background: linear-gradient(135deg, #dc3545, #c82333);'}
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 3000);
    };

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeEditModal();
        }
    };

    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modal = document.getElementById('editModal');
            if (modal.style.display === 'block') {
                closeEditModal();
            }
        }
    });
</script>
@endpush

@php
function getRegulationIcon($tenThamSo) {
    if (str_contains($tenThamSo, 'Tuoi')) return '👥';
    if (str_contains($tenThamSo, 'ThoiHan')) return '📅';
    if (str_contains($tenThamSo, 'Sach')) return '📚';
    if (str_contains($tenThamSo, 'Ngay')) return '⏰';
    if (str_contains($tenThamSo, 'Nam')) return '📖';
    return '⚙️';
}

function getRegulationIconClass($tenThamSo) {
    if (str_contains($tenThamSo, 'Tuoi')) return 'icon-age';
    if (str_contains($tenThamSo, 'ThoiHan')) return 'icon-card';
    if (str_contains($tenThamSo, 'Sach')) return 'icon-books';
    if (str_contains($tenThamSo, 'Ngay')) return 'icon-days';
    if (str_contains($tenThamSo, 'Nam')) return 'icon-years';
    return 'icon-age';
}

function getRegulationUnit($tenThamSo) {
    if (str_contains($tenThamSo, 'Tuoi')) return 'tuổi';
    if (str_contains($tenThamSo, 'ThoiHan')) return 'tháng';
    if (str_contains($tenThamSo, 'Sach')) return 'cuốn';
    if (str_contains($tenThamSo, 'Ngay')) return 'ngày';
    if (str_contains($tenThamSo, 'Nam')) return 'năm';
    return '';
}

function getRegulationDescription($tenThamSo) {
    if (str_contains($tenThamSo, 'TuoiToiThieu')) return 'Độ tuổi tối thiểu để được cấp thẻ độc giả';
    if (str_contains($tenThamSo, 'TuoiToiDa')) return 'Độ tuổi tối đa để được cấp thẻ độc giả';
    if (str_contains($tenThamSo, 'ThoiHanThe')) return 'Thời gian hiệu lực của thẻ độc giả';
    if (str_contains($tenThamSo, 'SoSachToiDa')) return 'Số sách tối đa mà một độc giả có thể mượn cùng lúc';
    if (str_contains($tenThamSo, 'NgayMuonToiDa')) return 'Thời gian mượn sách tối đa cho mỗi lần mượn';
    if (str_contains($tenThamSo, 'SoNamXuatBan')) return 'Chỉ nhận sách xuất bản trong khoảng thời gian này';
    return 'Tham số hệ thống';
}

function getFriendlyLabel($tenThamSo) {
    return [
        'TuoiToiThieu' => 'Tuổi tối thiểu độc giả',
        'TuoiToiDa' => 'Tuổi tối đa độc giả',
        'ThoiHanThe' => 'Thời hạn thẻ độc giả (tháng)',
        'SoSachToiDa' => 'Số lượng sách tối đa mượn cùng lúc',
        'NgayMuonToiDa' => 'Số ngày mượn tối đa',
        'SoNamXuatBan' => 'Số năm xuất bản sách được chấp nhận',
    ][$tenThamSo] ?? $tenThamSo;
}
@endphp
