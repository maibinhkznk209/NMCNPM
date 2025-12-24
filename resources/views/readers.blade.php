@extends('layouts.app')

@section('title', 'Quản lý độc giả - Hệ thống thư viện')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/books.css') }}">
<style>
    /* Additional styles for readers page */
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
    
    /* Enhanced textarea styling for address field */
    .form-group textarea {
        width: 100%;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        transition: all 0.3s ease;
    }
    
    .form-group textarea:focus {
        outline: none;
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
        transform: scale(1.02);
    }
    
    .form-group textarea::placeholder {
        color: #adb5bd;
        font-style: italic;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2d3748;
    }
    
    .form-group label small {
        display: block;
        margin-top: 2px;
        font-size: 12px;
        font-style: italic;
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
      box-shadow: 0 8px 25px rgba(66, 153, 225, 0.3);
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
    
    .add-reader-btn {
      background: linear-gradient(135deg, #48bb78, #38a169) !important;
      box-shadow: 0 4px 15px rgba(72, 187, 120, 0.2) !important;
    }
    
    .add-reader-btn:hover {
      background: linear-gradient(135deg, #38a169, #2f855a) !important;
      box-shadow: 0 8px 25px rgba(56, 161, 105, 0.3) !important;
    }
    
    /* Action button styles - larger text size like genres */
    .btn {
      padding: 8px 14px;
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
      gap: 4px;
      text-decoration: none;
      white-space: nowrap;
      flex-shrink: 0;
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

    /* Status badges for readers */
    .status-badge {
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;      /* đảm bảo áp dụng nowrap đúng */
    white-space: nowrap;        /* không xuống dòng */
    }

    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-expired {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .debt-amount {
        font-weight: bold;
    }
    
    .debt-amount.zero {
        color: #28a745;
    }
    
    .debt-amount.positive {
        color: #dc3545;
    }
    
    .reader-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .reader-info small {
        color: #6c757d;
        font-size: 11px;
    }
    
    /* Ensure action buttons never wrap and always stay in one line */
    td.actions {
      padding: 12px 8px !important;
      text-align: center !important;
      vertical-align: middle !important;
      /* Remove display: flex to match other td */
      position: relative !important;
      min-width: 220px;
      min-height: 130px !important;
      height: 100% !important;
      /* Ensure border-bottom is consistent with other cells */
      border-bottom: 1px solid #e2e8f0 !important;
    }
    
    /* Create a flex container inside td.actions for button alignment */
    td.actions .action-buttons {
      display: flex !important;
      gap: 6px;
      justify-content: center;
      align-items: center;
      flex-wrap: nowrap;
      height: 100%;
      min-height: 130px;
    }
    
    td.actions form {
      margin: 0;
      display: inline-flex;
      align-items: center;
    }
</style>
@endpush

@section('content')
    <div class="container">
        <div class="header">
            <h1>👥 Quản lý độc giả</h1>
            <p>Hệ thống quản lý thông tin độc giả thư viện</p>
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
                <div class="stat-number" style="color: #3498db;">{{ $docGias->count() ?? 0 }}</div>
                <div class="stat-label">Tổng số độc giả</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #38a169;">{{ $loaiDocGias->count() ?? 0 }}</div>
                <div class="stat-label">Loại độc giả</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #e53e3e;">{{ $docGias->where('NgayHetHan', '>', now())->count() ?? 0 }}</div>
                <div class="stat-label">Còn hiệu lực</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #e6d222;">
                    {{ $docGias->where('TongNo', '>', 0)->count() ?? 0 }}
                </div>
                <div class="stat-label">Có nợ</div>
            </div>
        </div>
        
        <div class="controls">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm kiếm độc giả..." />
                <span class="search-icon">🔍</span>
            </div>
            <div class="button-group">
                <a href="{{ route('home') }}" class="add-btn nav-home">
                    🏠 Trang chủ
                </a>
                <button class="add-btn add-reader-btn" onclick="openAddModal()">➕ Thêm độc giả mới</button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Thông tin độc giả</th>
                        <th>Loại độc giả</th>
                        <th>Ngày sinh</th>
                        <th>Thông tin thẻ</th>
                        <th>Tổng nợ</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($docGias as $docGia)
                        <tr>
                            <td>{{ $docGia->id }}</td>
                            <td>
                                <div class="reader-info">
                                    <strong>{{ $docGia->TenDocGia }}</strong>
                                    <small>📧 {{ $docGia->Email }}</small>
                                    <small>📍 {{ Str::limit($docGia->DiaChi, 50) }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="genre-tag" style="cursor: default; background: #e2e8f0;">{{ $docGia->loaiDocGia->TenLoaiDocGia ?? 'N/A' }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($docGia->NgaySinh)->format('d/m/Y') }}</td>
                            <td>
                                <div style="font-size: 13px;">
                                    <div><strong>Lập:</strong> {{ \Carbon\Carbon::parse($docGia->NgayLapThe)->format('d/m/Y') }}</div>
                                    <div><strong>Hết hạn:</strong> {{ \Carbon\Carbon::parse($docGia->NgayHetHan)->format('d/m/Y') }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="debt-amount {{ ($docGia->TongNo ?? 0) > 0 ? 'positive' : 'zero' }}">
                                    {{ number_format($docGia->TongNo ?? 0, 0, ',', '.') }}đ
                                </span>
                            </td>
                            <td>
                                @if(\Carbon\Carbon::parse($docGia->NgayHetHan)->isPast())
                                    <span class="status-badge status-expired">Hết hạn</span>
                                @else
                                    <span class="status-badge status-active">Còn hạn</span>
                                @endif
                            </td>
                            <td class="actions">
                                <div class="action-buttons">
                                    <button class="btn edit-btn" onclick="openEditModal('{{ $docGia->id }}')">✏️ Sửa</button>
                                    <form action="{{ route('readers.destroy', $docGia->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa độc giả này?\n\nLưu ý: Không thể xóa nếu độc giả còn nợ.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn delete-btn">🗑️ Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div style="font-size: 4rem; margin-bottom: 20px;">📭</div>
                                    <h3>Chưa có độc giả nào</h3>
                                    <p>Hãy thêm độc giả đầu tiên!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal thêm độc giả --}}
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>➕ Thêm độc giả mới</h3>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form action="{{ route('readers.store') }}" method="POST" id="addReaderForm" onsubmit="return validateAddForm()">
                @csrf
                <div class="form-group">
                    <label for="TenDocGia">Họ và tên *</label>
                    <input type="text" id="TenDocGia" name="TenDocGia" required>
                </div>
                
                <div class="form-group">
                    <label for="MaLoaiDocGia">Loại độc giả *</label>
                    <select id="MaLoaiDocGia" name="MaLoaiDocGia" required>
                        <option value="">Chọn loại độc giả</option>
                        @foreach($loaiDocGias as $loai)
                            <option value="{{ $loai->MaLoaiDocGia }}">{{ $loai->TenLoaiDocGia }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="NgaySinh">📅 Ngày sinh *</label>
                    <input 
                        type="text" 
                        id="NgaySinh" 
                        name="NgaySinh" 
                        required 
                        placeholder="dd/mm/yyyy"
                        pattern="^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])/\d{4}$"
                        title="Vui lòng nhập theo định dạng dd/mm/yyyy"
                        style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;"
                    >
                    <div class="invalid-feedback" id="NgaySinhError" style="color: #dc3545; font-size: 12px; margin-top: 2px; display: none;"></div>
                    <small style="color: #6c757d; font-size: 12px; margin-top: 4px; display: block;">
                        ⚠️ Độ tuổi phải từ {{ App\Models\QuyDinh::getMinAge() }} đến {{ App\Models\QuyDinh::getMaxAge() }} tuổi
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="Email">📧 Email *</label>
                    <input type="email" id="Email" name="Email" required style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;">
                </div>
                
                <div class="form-group">
                    <label for="DiaChi">
                        📍 Địa chỉ *
                        <small style="color: #6c757d; font-weight: normal;">(Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố)</small>
                    </label>
                    <textarea 
                        id="DiaChi" 
                        name="DiaChi" 
                        rows="4" 
                        required 
                        style="resize: vertical; min-height: 80px; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px; line-height: 1.5; transition: border-color 0.3s ease;"
                        onfocus="this.style.borderColor='#007bff'; this.style.boxShadow='0 0 0 0.2rem rgba(0,123,255,.25)'"
                        onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
                    ></textarea>
                </div>
                
                <div class="form-group">
                    <label for="NgayLapThe">📅 Ngày lập thẻ *</label>
                    <input 
                        type="text" 
                        id="NgayLapThe" 
                        name="NgayLapThe" 
                        required 
                        placeholder="dd/mm/yyyy"
                        pattern="^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])/\d{4}$"
                        title="Vui lòng nhập theo định dạng dd/mm/yyyy"
                        style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;"
                    >
                    <small style="color: #6c757d; display: block; margin-top: 4px;">
                        💡 Ngày hết hạn sẽ tự động được tính sau {{ App\Models\QuyDinh::getCardValidityMonths() }} tháng
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="TongNo">💰 Tổng nợ (VND)</label>
                    <input 
                        type="number" 
                        id="TongNo" 
                        name="TongNo" 
                        min="0" 
                        step="1000" 
                        value="0"
                        placeholder="0"
                        style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;"
                    >
                    <small style="color: #6c757d; display: block; margin-top: 4px;">
                        💡 Mặc định là 0. Chỉ nhập nếu độc giả đã có nợ từ trước
                    </small>
                </div>
                
                <!-- Hidden field for NgayHetHan - will be calculated automatically -->
                <input type="hidden" id="NgayHetHan" name="NgayHetHan">
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm độc giả</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal sửa độc giả --}}
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Sửa thông tin độc giả</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form action="" method="POST" id="editReaderForm" onsubmit="return validateEditForm()">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="editTenDocGia">Họ và tên *</label>
                    <input type="text" id="editTenDocGia" name="TenDocGia" required>
                </div>
                
                <div class="form-group">
                    <label for="editMaLoaiDocGia">Loại độc giả *</label>
                    <select id="editMaLoaiDocGia" name="MaLoaiDocGia" required>
                        <option value="">Chọn loại độc giả</option>
                        @foreach($loaiDocGias as $loai)
                            <option value="{{ $loai->MaLoaiDocGia }}">{{ $loai->TenLoaiDocGia }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editNgaySinh">📅 Ngày sinh *</label>
                    <input 
                        type="text" 
                        id="editNgaySinh" 
                        name="NgaySinh" 
                        required 
                        placeholder="dd/mm/yyyy"
                        pattern="^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])/\d{4}$"
                        title="Vui lòng nhập theo định dạng dd/mm/yyyy"
                        style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;"
                    >
                    <div class="invalid-feedback" id="editNgaySinhError" style="color: #dc3545; font-size: 12px; margin-top: 2px; display: none;"></div>
                    <small style="color: #6c757d; font-size: 12px; margin-top: 4px; display: block;">
                        ⚠️ Độ tuổi phải từ {{ App\Models\QuyDinh::getMinAge() }} đến {{ App\Models\QuyDinh::getMaxAge() }} tuổi
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="editEmail">📧 Email *</label>
                    <input type="email" id="editEmail" name="Email" required style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;">
                </div>
                
                <div class="form-group">
                    <label for="editDiaChi">
                        📍 Địa chỉ *
                        <small style="color: #6c757d; font-weight: normal;">(Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố)</small>
                    </label>
                    <textarea 
                        id="editDiaChi" 
                        name="DiaChi" 
                        rows="4" 
                        required
                        placeholder="Ví dụ: 123 Đường Nguyễn Văn Cừ, Phường An Hòa, Quận Ninh Kiều, TP. Cần Thơ"
                        style="resize: vertical; min-height: 80px; padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px; line-height: 1.5; transition: border-color 0.3s ease;"
                        onfocus="this.style.borderColor='#007bff'; this.style.boxShadow='0 0 0 0.2rem rgba(0,123,255,.25)'"
                        onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
                    ></textarea>
                </div>
                
                <div class="form-group">
                    <label for="editNgayLapThe">📅 Ngày lập thẻ *</label>
                    <input 
                        type="text" 
                        id="editNgayLapThe" 
                        name="NgayLapThe" 
                        required 
                        placeholder="dd/mm/yyyy"
                        pattern="^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[0-2])/\d{4}$"
                        title="Vui lòng nhập theo định dạng dd/mm/yyyy"
                        style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;"
                    >
                    <small style="color: #6c757d; display: block; margin-top: 4px;">
                        💡 Ngày hết hạn sẽ tự động được tính sau {{ App\Models\QuyDinh::getCardValidityMonths() }} tháng
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="editTongNo">💰 Tổng nợ (VND)</label>
                    <input 
                        type="number" 
                        id="editTongNo" 
                        name="TongNo" 
                        min="0" 
                        step="1000" 
                        placeholder="0"
                        style="padding: 12px; border: 2px solid #e9ecef; border-radius: 8px; font-size: 14px;"
                    >
                    <small style="color: #6c757d; display: block; margin-top: 4px;">
                        💡 Tổng số tiền nợ hiện tại của độc giả
                    </small>
                </div>
                
                <!-- Hidden field for NgayHetHan - will be calculated automatically -->
                <input type="hidden" id="editNgayHetHan" name="NgayHetHan">
                
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
    // Get regulations from server
    const MIN_AGE = {{ App\Models\QuyDinh::getMinAge() }};
    const MAX_AGE = {{ App\Models\QuyDinh::getMaxAge() }};
    const CARD_VALIDITY_MONTHS = {{ App\Models\QuyDinh::getCardValidityMonths() }};

    // Date formatting and validation functions
    function formatDateInput(input) {
        let value = input.value.replace(/\D/g, ''); // Remove non-digits
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '/' + value.substring(5, 9);
        }
        input.value = value;
    }

    function validateDate(dateString) {
        const regex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/;
        if (!regex.test(dateString)) return false;
        
        const [day, month, year] = dateString.split('/').map(Number);
        const date = new Date(year, month - 1, day);
        
        return date.getDate() === day && 
               date.getMonth() === month - 1 && 
               date.getFullYear() === year;
    }

    function validateAge(birthDateString) {
        if (!validateDate(birthDateString)) {
            return { valid: false, message: 'Ngày sinh không hợp lệ' };
        }
        
        const [day, month, year] = birthDateString.split('/').map(Number);
        const birthDate = new Date(year, month - 1, day);
        const today = new Date();
        
        // Calculate age
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        const dayDiff = today.getDate() - birthDate.getDate();
        
        // Adjust age if birthday hasn't occurred this year
        if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
            age--;
        }
        
        // Check age constraints
        if (age < MIN_AGE) {
            return { 
                valid: false, 
                message: `Độc giả phải từ ${MIN_AGE} tuổi trở lên (hiện tại: ${age} tuổi)`,
                age: age 
            };
        }
        
        if (age > MAX_AGE) {
            return { 
                valid: false, 
                message: `Độc giả không được quá ${MAX_AGE} tuổi (hiện tại: ${age} tuổi)`,
                age: age 
            };
        }
        
        return { valid: true, age: age };
    }

    function convertToDbFormat(ddmmyyyy) {
        const [day, month, year] = ddmmyyyy.split('/');
        return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    }

    function convertFromDbFormat(dateString) {
        if (!dateString) return '';
        
        // Database now returns Y-m-d format consistently
        if (dateString.includes('-') && dateString.split('-').length === 3) {
            const [year, month, day] = dateString.split('-');
            // Validate date parts
            if (year && month && day) {
                return `${day.padStart(2, '0')}/${month.padStart(2, '0')}/${year}`;
            }
        }
        
        // If it's already dd/mm/yyyy format
        if (dateString.includes('/') && dateString.split('/').length === 3) {
            return dateString; // Already in correct format
        }
        
        return dateString; // Fallback for unknown formats
    }

    function calculateExpiryDate(lapTheDate) {
        if (!validateDate(lapTheDate)) return '';
        
        const [day, month, year] = lapTheDate.split('/').map(Number);
        const date = new Date(year, month - 1, day);
        
        // Add 6 months
        date.setMonth(date.getMonth() + CARD_VALIDITY_MONTHS);
        
        const expDay = String(date.getDate()).padStart(2, '0');
        const expMonth = String(date.getMonth() + 1).padStart(2, '0');
        const expYear = date.getFullYear();
        
        return `${expDay}/${expMonth}/${expYear}`;
    }

    // Script đơn giản để ẩn thông báo sau vài giây
    document.addEventListener('DOMContentLoaded', function() {
        const toast = document.getElementById('toast-message');
        if (toast) {
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        // Set default dates with dd/mm/yyyy format
        const today = new Date();
        const todayFormatted = `${String(today.getDate()).padStart(2, '0')}/${String(today.getMonth() + 1).padStart(2, '0')}/${today.getFullYear()}`;
        
        const ngayLapTheElement = document.getElementById('NgayLapThe');
        if (ngayLapTheElement) {
            ngayLapTheElement.value = todayFormatted;
            // Auto-calculate expiry date
            const expiry = calculateExpiryDate(todayFormatted);
            document.getElementById('NgayHetHan').value = convertToDbFormat(expiry);
        }

        // Date input formatting and validation
        const dateInputs = ['NgaySinh', 'NgayLapThe', 'editNgaySinh', 'editNgayLapThe'];
        dateInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', function() {
                    formatDateInput(this);
                    
                    // For NgayLapThe, auto-calculate expiry date
                    if ((inputId === 'NgayLapThe' || inputId === 'editNgayLapThe') && validateDate(this.value)) {
                        const expiry = calculateExpiryDate(this.value);
                        const hiddenField = inputId === 'NgayLapThe' ? 'NgayHetHan' : 'editNgayHetHan';
                        document.getElementById(hiddenField).value = convertToDbFormat(expiry);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value && !validateDate(this.value)) {
                        showToast('Vui lòng nhập ngày theo định dạng dd/mm/yyyy và đảm bảo ngày hợp lệ', 'error');
                        this.focus();
                    }
                });
            }
        });

        // Enhanced address field functionality
        const addressFields = ['DiaChi', 'editDiaChi'];
        addressFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                // Auto-capitalize first letter of each word
                field.addEventListener('input', function() {
                    const cursorPosition = this.selectionStart;
                    const value = this.value;
                    
                    // Capitalize first letter after comma, period, or at start
                    const formatted = value.replace(/(^|\. |, )([a-z])/g, function(match, prefix, letter) {
                        return prefix + letter.toUpperCase();
                    });
                    
                    if (formatted !== value) {
                        this.value = formatted;
                        this.setSelectionRange(cursorPosition, cursorPosition);
                    }
                });
                
                // Add character counter
                const counter = document.createElement('small');
                counter.style.cssText = 'color: #6c757d; float: right; margin-top: 4px;';
                counter.textContent = `0/500 ký tự`;
                field.parentNode.appendChild(counter);
                
                field.addEventListener('input', function() {
                    const length = this.value.length;
                    counter.textContent = `${length}/500 ký tự`;
                    counter.style.color = length > 450 ? '#dc3545' : '#6c757d';
                });
                
                // Limit character count
                field.setAttribute('maxlength', '500');
            }
        });

        // Client-side search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const tableBody = document.querySelector('tbody');
                const rows = tableBody.querySelectorAll('tr');
                let hasVisibleRows = false;

                rows.forEach(row => {
                    if (row.querySelector('.empty-state')) return;
                    
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                        hasVisibleRows = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show/hide empty state
                let emptyState = tableBody.querySelector('.empty-state-row');
                if (!hasVisibleRows && searchTerm) {
                    if (!emptyState) {
                        emptyState = document.createElement('tr');
                        emptyState.className = 'empty-state-row';
                        emptyState.innerHTML = `
                            <td colspan="8">
                                <div class="empty-state">
                                    <div style="font-size: 4rem; margin-bottom: 20px;">🔍</div>
                                    <h3>Không tìm thấy độc giả nào</h3>
                                    <p>Hãy thử thay đổi từ khóa tìm kiếm.</p>
                                </div>
                            </td>
                        `;
                        tableBody.appendChild(emptyState);
                    }
                    emptyState.style.display = '';
                } else if (emptyState) {
                    emptyState.style.display = 'none';
                }
            });
        }
    });

    // Modal functions - Global scope
    window.openAddModal = function() {
        document.getElementById('addModal').style.display = 'block';
        
        // Set default dates when opening modal
        const today = new Date();
        const todayFormatted = `${String(today.getDate()).padStart(2, '0')}/${String(today.getMonth() + 1).padStart(2, '0')}/${today.getFullYear()}`;
        
        const ngayLapTheElement = document.getElementById('NgayLapThe');
        if (ngayLapTheElement) {
            ngayLapTheElement.value = todayFormatted;
            // Auto-calculate expiry date
            const expiry = calculateExpiryDate(todayFormatted);
            document.getElementById('NgayHetHan').value = convertToDbFormat(expiry);
        }
        
        // Focus on first input
        setTimeout(() => {
            document.getElementById('TenDocGia').focus();
        }, 100);
    };

    window.closeAddModal = function() {
        document.getElementById('addModal').style.display = 'none';
        document.getElementById('addReaderForm').reset();
        
        // Reset default dates with dd/mm/yyyy format
        const today = new Date();
        const todayFormatted = `${String(today.getDate()).padStart(2, '0')}/${String(today.getMonth() + 1).padStart(2, '0')}/${today.getFullYear()}`;
        
        const ngayLapTheElement = document.getElementById('NgayLapThe');
        if (ngayLapTheElement) {
            ngayLapTheElement.value = todayFormatted;
            // Auto-calculate expiry date
            const expiry = calculateExpiryDate(todayFormatted);
            document.getElementById('NgayHetHan').value = convertToDbFormat(expiry);
        }
    };

    window.openEditModal = function(id) {
        fetch(`/readers/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const reader = data.data;
                    document.getElementById('editTenDocGia').value = reader.TenDocGia;
                    document.getElementById('editMaLoaiDocGia').value = reader.MaLoaiDocGia;
                    
                    // Convert dates from database format (yyyy-mm-dd) to display format (dd/mm/yyyy)
                    document.getElementById('editNgaySinh').value = convertFromDbFormat(reader.NgaySinh);
                    document.getElementById('editEmail').value = reader.Email;
                    document.getElementById('editDiaChi').value = reader.DiaChi;
                    document.getElementById('editNgayLapThe').value = convertFromDbFormat(reader.NgayLapThe);
                    document.getElementById('editTongNo').value = Math.floor(reader.TongNo || 0);
                    
                    // Set hidden expiry date field (database format)
                    document.getElementById('editNgayHetHan').value = reader.NgayHetHan;
                    
                    document.getElementById('editReaderForm').action = `/readers/${id}`;
                    document.getElementById('editModal').style.display = 'block';
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi tải thông tin độc giả', 'error');
            });
    };

    window.closeEditModal = function() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editReaderForm').reset();
    };

    // Toast notification function - Global scope
    window.showToast = function(message, type = 'success') {
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
    };

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
    };

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

    // Validation functions - Global scope
    window.validateAddForm = function() {
        try {
            let isValid = true;
            
            // Validate required text fields
            const textFields = ['TenDocGia', 'MaLoaiDocGia', 'Email', 'DiaChi'];
            textFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element || !element.value.trim()) {
                    showToast(`Vui lòng nhập đầy đủ thông tin bắt buộc: ${field}`, 'error');
                    if (element) element.focus();
                    isValid = false;
                    return false;
                }
            });

            if (!isValid) return false;

            // Validate date fields
            const dateFields = ['NgaySinh', 'NgayLapThe'];
            dateFields.forEach(field => {
                const element = document.getElementById(field);
                if (!element || !element.value.trim()) {
                    showToast(`Vui lòng nhập đầy đủ thông tin ngày tháng`, 'error');
                    if (element) element.focus();
                    isValid = false;
                    return false;
                }
                if (!validateDate(element.value)) {
                    showToast(`Vui lòng nhập ngày ${field === 'NgaySinh' ? 'sinh' : 'lập thẻ'} theo định dạng dd/mm/yyyy`, 'error');
                    if (element) element.focus();
                    isValid = false;
                    return false;
                }
                
                // Special validation for birth date (age check)
                if (field === 'NgaySinh') {
                    const ageValidation = validateAge(element.value);
                    if (!ageValidation.valid) {
                        showToast(ageValidation.message, 'error');
                        if (element) element.focus();
                        isValid = false;
                        return false;
                    }
                }
            });

            if (!isValid) return false;

            // Ensure expiry date is calculated
            const lapThe = document.getElementById('NgayLapThe').value;
            if (lapThe && validateDate(lapThe)) {
                const expiry = calculateExpiryDate(lapThe);
                document.getElementById('NgayHetHan').value = convertToDbFormat(expiry);
            }

            // Convert date formats before submission
            document.getElementById('NgaySinh').value = convertToDbFormat(document.getElementById('NgaySinh').value);
            document.getElementById('NgayLapThe').value = convertToDbFormat(document.getElementById('NgayLapThe').value);
            
            return true;
        } catch (error) {
            console.error('Validation error:', error);
            showToast('Có lỗi xảy ra trong quá trình kiểm tra dữ liệu', 'error');
            return false;
        }
    };

    window.validateEditForm = function() {
        let isValid = true;
        
        // Validate required text fields
        const textFields = ['editTenDocGia', 'editMaLoaiDocGia', 'editEmail', 'editDiaChi'];
        textFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element || !element.value.trim()) {
                showToast(`Vui lòng nhập đầy đủ thông tin bắt buộc`, 'error');
                if (element) element.focus();
                isValid = false;
                return false;
            }
        });

        // Validate date fields
        const dateFields = ['editNgaySinh', 'editNgayLapThe'];
        dateFields.forEach(field => {
            const element = document.getElementById(field);
            if (!element || !element.value.trim()) {
                showToast(`Vui lòng nhập đầy đủ thông tin ngày tháng`, 'error');
                if (element) element.focus();
                isValid = false;
                return false;
            }
            if (!validateDate(element.value)) {
                showToast(`Vui lòng nhập ngày ${field === 'editNgaySinh' ? 'sinh' : 'lập thẻ'} theo định dạng dd/mm/yyyy`, 'error');
                if (element) element.focus();
                isValid = false;
                return false;
            }
            
            // Special validation for birth date (age check)
            if (field === 'editNgaySinh') {
                const ageValidation = validateAge(element.value);
                if (!ageValidation.valid) {
                    showToast(ageValidation.message, 'error');
                    if (element) element.focus();
                    isValid = false;
                    return false;
                }
            }
        });

        // Ensure expiry date is calculated
        const lapThe = document.getElementById('editNgayLapThe').value;
        if (lapThe && validateDate(lapThe)) {
            const expiry = calculateExpiryDate(lapThe);
            document.getElementById('editNgayHetHan').value = convertToDbFormat(expiry);
        }

        // Convert date formats before submission
        if (isValid) {
            document.getElementById('editNgaySinh').value = convertToDbFormat(document.getElementById('editNgaySinh').value);
            document.getElementById('editNgayLapThe').value = convertToDbFormat(document.getElementById('editNgayLapThe').value);
        }
        
        return isValid;
    };

    // Real-time validation for NgaySinh (add)
    const ngaySinhInput = document.getElementById('NgaySinh');
    const ngaySinhError = document.getElementById('NgaySinhError');
    if (ngaySinhInput && ngaySinhError) {
        ngaySinhInput.addEventListener('input', function() {
            if (!this.value) {
                ngaySinhError.style.display = 'none';
                return;
            }
            if (!validateDate(this.value)) {
                ngaySinhError.textContent = 'Ngày sinh không hợp lệ (dd/mm/yyyy)';
                ngaySinhError.style.display = 'block';
                return;
            }
            const result = validateAge(this.value);
            if (!result.valid) {
                ngaySinhError.textContent = result.message;
                ngaySinhError.style.display = 'block';
            } else {
                ngaySinhError.style.display = 'none';
            }
        });
    }
    // Real-time validation for editNgaySinh (edit)
    const editNgaySinhInput = document.getElementById('editNgaySinh');
    const editNgaySinhError = document.getElementById('editNgaySinhError');
    if (editNgaySinhInput && editNgaySinhError) {
        editNgaySinhInput.addEventListener('input', function() {
            if (!this.value) {
                editNgaySinhError.style.display = 'none';
                return;
            }
            if (!validateDate(this.value)) {
                editNgaySinhError.textContent = 'Ngày sinh không hợp lệ (dd/mm/yyyy)';
                editNgaySinhError.style.display = 'block';
                return;
            }
            const result = validateAge(this.value);
            if (!result.valid) {
                editNgaySinhError.textContent = result.message;
                editNgaySinhError.style.display = 'block';
            } else {
                editNgaySinhError.style.display = 'none';
            }
        });
    }
</script>
@endpush
