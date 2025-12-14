@extends('layouts.app')

@section('title', 'Quản lý sách - Hệ thống thư viện')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/books.css') }}">
  <style>
    /* Validation styles */
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
    
    .selected-item {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 4px 8px;
        border-radius: 15px;
        margin: 2px;
        display: inline-flex;
        align-items: center;
        font-size: 13px;
    }
    
    .selected-item .remove-btn {
        background: rgba(255,255,255,0.3);
        border: none;
        border-radius: 50%;
        color: white;
        margin-left: 5px;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 12px;
        line-height: 1;
    }
    
    .selected-item .remove-btn:hover {
        background: rgba(255,255,255,0.5);
    }

    /* Enhanced button styles for navigation */
    .add-btn {
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
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
    
    /* Color scheme for navigation buttons */
    .nav-home {
      background: linear-gradient(135deg, #f39c12, #e67e22) !important;
    }
    
    .nav-home:hover {
      background: linear-gradient(135deg, #e67e22, #d35400) !important;
    }
    
    .nav-books {
      background: linear-gradient(135deg, #3498db, #2980b9) !important;
    }
    
    .nav-books:hover {
      background: linear-gradient(135deg, #2980b9, #1f618d) !important;
    }
    
    .nav-genres {
      background: linear-gradient(135deg, #667eea, #764ba2) !important;
    }
    
    .nav-genres:hover {
      background: linear-gradient(135deg, #764ba2, #6a4c93) !important;
    }
    
    .nav-authors {
      background: linear-gradient(135deg, #38b2ac, #319795) !important;
    }
    
    .nav-authors:hover {
      background: linear-gradient(135deg, #319795, #2c7a7b) !important;
    }
    
    .nav-publishers {
      background: linear-gradient(135deg, #9f7aea, #805ad5) !important;
    }
    
    .nav-publishers:hover {
      background: linear-gradient(135deg, #805ad5, #6b46c1) !important;
    }
    
    /* Genre cell styles for wrapping */
    .genre-cell {
      max-width: 150px;
      line-height: 1.3;
      font-size: 12px;
    }
    
    .genre-cell .genre-tag {
      display: inline-block;
      background: #e2e8f0;
      color: #2d3748;
      padding: 2px 6px;
      border-radius: 10px;
      font-size: 10px;
      margin: 1px;
      white-space: nowrap;
      cursor: default;
    }
    
    /* Status badge styles */
    .status-badge {
      padding: 4px 8px;
      border-radius: 15px;
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      white-space: nowrap;
      display: inline-block;
    }
    
    /* New status styles for TinhTrang */
    .status-available {
      background: linear-gradient(135deg, #d4edda, #c3e6cb);
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .status-borrowed {
      background: linear-gradient(135deg, #f8d7da, #f5c6cb);
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .status-damaged {
      background: linear-gradient(135deg, #fff3cd, #ffeaa7);
      color: #856404;
      border: 1px solid #ffeaa7;
    }
    
    .status-lost {
      background: linear-gradient(135deg, #6c757d, #495057);
      color: #ffffff;
      border: 1px solid #495057;
    }
    
    /* Button content alignment */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      white-space: nowrap;
      padding: 6px 10px;
      margin: 2px;
      font-size: 12px;
    }
    
    .btn-icon {
      font-size: 12px;
      line-height: 1;
    }
    
    .btn-text {
      font-size: 11px;
      font-weight: 500;
    }
    
    /* Table responsive layout */
    .table-container {
      width: 100%;
      overflow-x: visible;
      margin-bottom: 20px;
    }
    
    .table-container table {
      width: 100%;
      table-layout: auto;
      border-collapse: collapse;
    }
    
    /* Optimize column widths */
    .table-container table th:nth-child(1),
    .table-container table td:nth-child(1) {
      width: 80px;
      min-width: 80px;
    }
    
    .table-container table th:nth-child(2),
    .table-container table td:nth-child(2) {
      width: 25%;
      min-width: 180px;
    }
    
    .table-container table th:nth-child(3),
    .table-container table td:nth-child(3) {
      width: 15%;
      min-width: 120px;
    }
    
    .table-container table th:nth-child(4),
    .table-container table td:nth-child(4) {
      width: 20%;
      min-width: 150px;
    }
    
    .table-container table th:nth-child(5),
    .table-container table td:nth-child(5) {
      width: 100px;
      min-width: 100px;
      text-align: center;
    }
    
    .table-container table th:nth-child(6),
    .table-container table td:nth-child(6) {
      width: 110px;
      min-width: 110px;
      text-align: center;
    }
    
    .table-container table th:nth-child(7),
    .table-container table td:nth-child(7) {
      width: 140px;
      min-width: 140px;
      text-align: center;
      vertical-align: middle;
    }
    
    /* Actions cell specific styling */
    .actions {
      display: flex;
      flex-direction: column;
      gap: 3px;
      align-items: center;
      justify-content: center;
      padding: 8px 4px !important;
    }
    
    .actions .btn {
      width: 100%;
      max-width: 80px;
      min-height: 28px;
    }
    
    .actions form {
      width: 100%;
      display: flex;
      justify-content: center;
    }
    
    .actions form .btn {
      width: 100%;
      max-width: 80px;
    }
    
    /* Make table more compact */
    .table-container table td {
      padding: 8px 6px;
      font-size: 13px;
      vertical-align: middle;
    }
    
    .table-container table th {
      padding: 10px 6px;
      font-size: 12px;
      font-weight: 600;
    }
    
    /* Compact text in cells */
    .table-container table td strong {
      font-size: 12px;
      font-weight: 600;
    }
    
    /* Container width adjustment */
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 15px;
    }
  </style>
  <style>
.stats-grid .stat-card.total .stat-number {
  color: #3498db;
}
.stats-grid .stat-card.genres .stat-number {
  color: #38a169;
}
.stats-grid .stat-card.authors .stat-number {
  color: #e67e22;
}
.stats-grid .stat-card.problem .stat-number {
  color: #e53e3e;
    }
  </style>
@endpush

@section('content')
    <div class="container">
        <div class="header">
            <h1>📚 Hệ thống quản lý sách</h1>
            <p>Dành cho nhân viên thư viện</p>
        </div>

        {{-- Thẻ thông báo thành công/lỗi --}}
        @if (session('success'))
            <div class="toast success" id="toast-message">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="toast error" id="toast-message">{{ session('error') }}</div>
        @endif

        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number">{{ $totalBooks }}</div>
                <div class="stat-label">Tổng số sách</div>
            </div>
            <div class="stat-card genres">
                <div class="stat-number">{{ $totalGenres }}</div>
                <div class="stat-label">Thể loại</div>
            </div>
            <div class="stat-card authors">
                <div class="stat-number">{{ $totalAuthors }}</div>
                <div class="stat-label">Tác giả</div>
            </div>
            <div class="stat-card problem">
                <div class="stat-number">{{ $totalProblemBooks }}</div>
                <div class="stat-label">Sách mất/hỏng</div>
            </div>
        </div>
        
        <form action="{{ route('books.index') }}" method="GET">        <div class="controls">
            <div class="search-box">
                <input type="text" name="search" placeholder="Tìm kiếm sách, tác giả..." value="{{ request('search') }}" />
                <span class="search-icon">🔍</span>
            </div>
            {{-- Giữ lại bộ lọc thể loại khi tìm kiếm --}}
            @if (request('genre'))
                <input type="hidden" name="genre" value="{{ request('genre') }}">
            @endif
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('home') }}" class="add-btn nav-home" style="text-decoration: none; color: white;">
                    🏠 Trang chủ
                </a>
                <button type="button" class="add-btn" onclick="openAddModal()">➕ Thêm sách mới</button>
            </div>
        </div>
        </form>

        <div class="genre-filter-container">
            <button class="scroll-btn scroll-left" onclick="scrollGenres(-1)">‹</button>
            <div class="genre-filter" id="genreFilter">
                <a href="{{ route('books.index', ['search' => request('search')]) }}" 
                    class="genre-tag {{ !request('genre') || request('genre') == 'all' ? 'active' : '' }}">
                    Tất cả
                </a>
                @foreach ($genresForFilter as $genre)
                    <a href="{{ route('books.index', ['genre' => $genre->id, 'search' => request('search')]) }}" 
                       class="genre-tag {{ request('genre') == $genre->id ? 'active' : '' }}">
                       {{ $genre->TenTheLoai }}
                    </a>
                @endforeach
            </div>
            <button class="scroll-btn scroll-right" onclick="scrollGenres(1)">›</button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Mã sách</th>
                        <th>Tên sách</th>
                        <th>Tác giả</th>
                        <th>Thể loại</th>
                        <th>Trị giá</th>
                        <th>Ngày thêm</th>
                        <th>Tình trạng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($danhSachSach as $sach)
                        <tr>
                            <td><strong>{{ $sach->MaSach }}</strong></td>
                            <td><strong>{{ $sach->TenSach }}</strong></td>
                            <td>{{ $sach->tacGia ? $sach->tacGia->TenTacGia : 'Chưa có tác giả' }}</td>
                            <td class="genre-cell">
                                @foreach($sach->theLoais as $theLoai)
                                    <span class="genre-tag">{{ $theLoai->TenTheLoai }}</span>
                                @endforeach
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #2d3748;">
                                {{ number_format($sach->TriGia ?? 0, 0, ',', '.') }}đ
                            </td>
                            <td>{{ \Carbon\Carbon::parse($sach->NgayNhap)->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $tinhTrangText = '';
                                    $statusClass = '';
                                    if ($sach->TinhTrang == 1) {
                                        $tinhTrangText = 'Có sẵn';
                                        $statusClass = 'status-available';
                                    } else if ($sach->TinhTrang == 0) {
                                        $tinhTrangText = 'Đang được mượn';
                                        $statusClass = 'status-borrowed';
                                    } else if ($sach->TinhTrang == 3) {
                                        $tinhTrangText = 'Hỏng';
                                        $statusClass = 'status-damaged';
                                    } else if ($sach->TinhTrang == 4) {
                                        $tinhTrangText = 'Mất';
                                        $statusClass = 'status-lost';
                                    }
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $tinhTrangText }}
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn edit-btn" onclick="openEditModal('{{ $sach->id }}')">
                                    <span class="btn-icon">✏️</span>
                                    <span class="btn-text">Sửa</span>
                                </button>
                                <button class="btn delete-btn" onclick="deleteBook('{{ $sach->id }}', '{{ $sach->TenSach }}', {{ $sach->TinhTrang }})">
                                    <span class="btn-icon">🗑️</span>
                                    <span class="btn-text">Xóa</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div style="font-size: 4rem; margin-bottom: 20px;">📚</div>
                                    <h3>Không tìm thấy sách nào</h3>
                                    <p>Hãy thử thay đổi từ khóa tìm kiếm hoặc bộ lọc.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modal thêm sách --}}
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>➕ Thêm sách mới</h3>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form action="{{ route('books.store') }}" method="POST" id="addBookForm" onsubmit="return validateAddForm()">
                @csrf
                <div class="form-group">
                    <label for="TenSach">Tên sách *</label>
                    <input type="text" id="TenSach" name="TenSach" required>
                </div>
                
                <div class="form-group">
                    <label for="NamXuatBan">Năm xuất bản *</label>
                    <input type="number" id="NamXuatBan" name="NamXuatBan" min="{{ date('Y') - App\Models\QuyDinh::getBookPublicationYears() }}" max="{{ date('Y') }}" required>
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">
                        📅 Chỉ nhận sách xuất bản trong vòng {{ App\Models\QuyDinh::getBookPublicationYears() }} năm (từ {{ date('Y') - App\Models\QuyDinh::getBookPublicationYears() }} đến {{ date('Y') }})
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="TriGia">Trị giá sách *</label>
                    <input type="number" id="TriGia" name="TriGia" min="0" max="999999999.99" step="0.01" required>
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">
                        💰 Nhập trị giá sách bằng VNĐ (ví dụ: 50000 cho 50.000đ)
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="SoLuong">Số lượng sách *</label>
                    <input type="number" id="SoLuong" name="SoLuong" min="1" max="1000" value="1" required>
                </div>
                
                {{-- Tình trạng sách sẽ được mặc định là "có sẵn" và không cho phép chỉnh sửa --}}
                <input type="hidden" id="TinhTrang" name="TinhTrang" value="1">
                
                <div class="form-group">
                    <label for="tacGias">Tác giả * <small style="color: #666; font-style: italic;">(Chỉ chọn được 1 tác giả)</small></label>
                    <div class="search-select-container" id="tacGiasContainer">
                        <input type="text" class="search-input" id="tacGiaSearch" placeholder="Tìm kiếm tác giả..." onkeyup="searchTacGias(this.value)">
                        <div class="selected-items" id="selectedTacGias"></div>
                        <div class="dropdown-list" id="tacGiasList" style="display: none;">
                            @foreach($tacGias as $tacGia)
                                <div class="dropdown-item" data-id="{{ $tacGia->id }}" data-name="{{ $tacGia->TenTacGia }}" onclick="selectTacGia('{{ $tacGia->id }}', '{{ $tacGia->TenTacGia }}')">
                                    {{ $tacGia->TenTacGia }}
                                </div>
                            @endforeach
                            <div class="add-new-item" onclick="window.open('{{ route('authors.index') }}', '_blank')">
                                <i class="fas fa-plus"></i> Thêm tác giả mới
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="tacGias" id="tacGiasInput">
                </div>
                
                <div class="form-group">
                    <label for="theLoais">Thể loại * <small style="color: #666; font-style: italic;">(Có thể chọn nhiều thể loại)</small></label>
                    <div class="search-select-container" id="theLoaisContainer">
                        <input type="text" class="search-input" id="theLoaiSearch" placeholder="Tìm kiếm thể loại..." onkeyup="searchTheLoais(this.value)">
                        <div class="selected-items" id="selectedTheLoais"></div>
                        <div class="dropdown-list" id="theLoaisList" style="display: none;">
                            @foreach($theLoais as $theLoai)
                                <div class="dropdown-item" data-id="{{ $theLoai->id }}" data-name="{{ $theLoai->TenTheLoai }}" onclick="selectTheLoai('{{ $theLoai->id }}', '{{ $theLoai->TenTheLoai }}')">
                                    {{ $theLoai->TenTheLoai }}
                                </div>
                            @endforeach
                            <div class="add-new-item" onclick="window.open('{{ route('genres.index') }}', '_blank')">
                                <i class="fas fa-plus"></i> Thêm thể loại mới
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="theLoais" id="theLoaisInput">
                </div>
                
                <div class="form-group">
                    <label for="nhaXuatBans">Nhà xuất bản * <small style="color: #666; font-style: italic;">(Chỉ chọn được 1 nhà xuất bản)</small></label>
                    <div class="search-select-container" id="nhaXuatBansContainer">
                        <input type="text" class="search-input" id="nxbSearch" placeholder="Tìm kiếm nhà xuất bản..." onkeyup="searchNXBs(this.value)">
                        <div class="selected-items" id="selectedNXBs"></div>
                        <div class="dropdown-list" id="nxbsList" style="display: none;">
                            @foreach($nhaXuatBans as $nxb)
                                <div class="dropdown-item" data-id="{{ $nxb->id }}" data-name="{{ $nxb->TenNXB }}" onclick="selectNXB('{{ $nxb->id }}', '{{ $nxb->TenNXB }}')">
                                    {{ $nxb->TenNXB }}
                                </div>
                            @endforeach
                            <div class="add-new-item" onclick="window.open('{{ route('publishers.index') }}', '_blank')">
                                <i class="fas fa-plus"></i> Thêm nhà xuất bản mới
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="nhaXuatBans" id="nxbsInput">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm sách</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal sửa sách --}}
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Sửa thông tin sách</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form action="" method="POST" id="editBookForm" onsubmit="return validateEditForm()">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="editTenSach">Tên sách *</label>
                    <input type="text" id="editTenSach" name="TenSach" required>
                </div>
                
                <div class="form-group">
                    <label for="editNamXuatBan">Năm xuất bản *</label>
                    <input type="number" id="editNamXuatBan" name="NamXuatBan" min="{{ date('Y') - App\Models\QuyDinh::getBookPublicationYears() }}" max="{{ date('Y') }}" required>
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">
                        📅 Chỉ nhận sách xuất bản trong vòng {{ App\Models\QuyDinh::getBookPublicationYears() }} năm (từ {{ date('Y') - App\Models\QuyDinh::getBookPublicationYears() }} đến {{ date('Y') }})
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="editTriGia">Trị giá sách *</label>
                    <input type="number" id="editTriGia" name="TriGia" min="0" max="999999999.99" step="0.01" placeholder="Ví dụ: 50000" required>
                    <small style="color: #666; font-size: 12px; margin-top: 4px; display: block;">
                        💰 Nhập trị giá sách bằng VNĐ (ví dụ: 50000 cho 50.000đ)
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="editTinhTrang">Tình trạng sách</label>
                    <select id="editTinhTrang" name="TinhTrang" required>
                        <option value="">-- Chọn tình trạng --</option>
                        <option value="1">Có sẵn</option>
                        <option value="0">Đang được mượn</option>
                        <option value="3">Hỏng</option>
                        <option value="4">Mất</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editTacGias">Tác giả * <small style="color: #666; font-style: italic;">(Chỉ chọn được 1 tác giả)</small></label>
                    <div class="search-select-container" id="editTacGiasContainer">
                        <input type="text" class="search-input" id="editTacGiaSearch" placeholder="Tìm kiếm tác giả..." onkeyup="searchEditTacGias(this.value)">
                        <div class="selected-items" id="editSelectedTacGias"></div>
                        <div class="dropdown-list" id="editTacGiasList" style="display: none;">
                            @foreach($tacGias as $tacGia)
                                <div class="dropdown-item" data-id="{{ $tacGia->id }}" data-name="{{ $tacGia->TenTacGia }}" onclick="selectEditTacGia('{{ $tacGia->id }}', '{{ $tacGia->TenTacGia }}')">
                                    {{ $tacGia->TenTacGia }}
                                </div>
                            @endforeach
                            <div class="add-new-item" onclick="window.open('{{ route('authors.index') }}', '_blank')">
                                <i class="fas fa-plus"></i> Thêm tác giả mới
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="tacGias" id="editTacGiasInput">
                </div>
                
                <div class="form-group">
                    <label for="editTheLoais">Thể loại * <small style="color: #666; font-style: italic;">(Có thể chọn nhiều thể loại)</small></label>
                    <div class="search-select-container" id="editTheLoaisContainer">
                        <input type="text" class="search-input" id="editTheLoaiSearch" placeholder="Tìm kiếm thể loại..." onkeyup="searchEditTheLoais(this.value)">
                        <div class="selected-items" id="editSelectedTheLoais"></div>
                        <div class="dropdown-list" id="editTheLoaisList" style="display: none;">
                            @foreach($theLoais as $theLoai)
                                <div class="dropdown-item" data-id="{{ $theLoai->id }}" data-name="{{ $theLoai->TenTheLoai }}" onclick="selectEditTheLoai('{{ $theLoai->id }}', '{{ $theLoai->TenTheLoai }}')">
                                    {{ $theLoai->TenTheLoai }}
                                </div>
                            @endforeach
                            <div class="add-new-item" onclick="window.open('{{ route('genres.index') }}', '_blank')">
                                <i class="fas fa-plus"></i> Thêm thể loại mới
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="theLoais" id="editTheLoaisInput">
                </div>
                
                <div class="form-group">
                    <label for="editNhaXuatBans">Nhà xuất bản * <small style="color: #666; font-style: italic;">(Chỉ chọn được 1 nhà xuất bản)</small></label>
                    <div class="search-select-container" id="editNhaXuatBansContainer">
                        <input type="text" class="search-input" id="editNxbSearch" placeholder="Tìm kiếm nhà xuất bản..." onkeyup="searchEditNXBs(this.value)">
                        <div class="selected-items" id="editSelectedNXBs"></div>
                        <div class="dropdown-list" id="editNxbsList" style="display: none;">
                            @foreach($nhaXuatBans as $nxb)
                                <div class="dropdown-item" data-id="{{ $nxb->id }}" data-name="{{ $nxb->TenNXB }}" onclick="selectEditNXB('{{ $nxb->id }}', '{{ $nxb->TenNXB }}')">
                                    {{ $nxb->TenNXB }}
                                </div>
                            @endforeach
                            <div class="add-new-item" onclick="window.open('{{ route('publishers.index') }}', '_blank')">
                                <i class="fas fa-plus"></i> Thêm nhà xuất bản mới
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="nhaXuatBans" id="editNxbsInput">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Test buttons for debugging -->
    <!-- Removed for production release -->

@endsection

@push('scripts')
    <script>
        // Initialize variables first
        let selectedTacGias = [];
        let selectedTheLoais = [];
        let selectedNXBs = [];
        let editSelectedTacGias = [];
        let editSelectedTheLoais = [];
        let editSelectedNXBs = [];

        // Define openEditModal function early to ensure it's available
        function openEditModal(id) {            
            // Fetch book data
            fetch(`/books/${id}/edit`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Book data received:', data);
                try {
                    // Populate form
                    document.getElementById('editTenSach').value = data.TenSach || '';
                    document.getElementById('editNamXuatBan').value = data.NamXuatBan || '';
                    document.getElementById('editTriGia').value = Math.floor(data.TriGia || 0);
                    
                    // Hiển thị tình trạng hiện tại nhưng không cho phép chỉnh sửa
                    document.getElementById('editTinhTrang').value = data.TinhTrang;
                    
                    // Debug: Log all data to console
                    console.log('Full book data received:', data);
                    console.log('TenSach:', data.TenSach);
                    console.log('NamXuatBan:', data.NamXuatBan);
                    console.log('TriGia:', data.TriGia);
                    console.log('TinhTrang:', data.TinhTrang);
                    
                    // Set selected items for edit modal
                    editSelectedTacGias = data.tacGias ? [{
                        id: data.tacGias,
                        name: document.querySelector(`#editTacGiasList [data-id="${data.tacGias}"]`)?.getAttribute('data-name') || ''
                    }] : [];
                    
                    // Xử lý nhiều thể loại
                    editSelectedTheLoais = [];
                    if (data.theLoais && Array.isArray(data.theLoais)) {
                        data.theLoais.forEach(theLoaiId => {
                            const element = document.querySelector(`#editTheLoaisList [data-id="${theLoaiId}"]`);
                            if (element) {
                                editSelectedTheLoais.push({
                                    id: theLoaiId,
                                    name: element.getAttribute('data-name') || ''
                                });
                            }
                        });
                    }
                    
                    editSelectedNXBs = data.nhaXuatBans ? [{
                        id: data.nhaXuatBans,
                        name: document.querySelector(`#editNxbsList [data-id="${data.nhaXuatBans}"]`)?.getAttribute('data-name') || ''
                    }] : [];

                    updateEditSelectedTacGias();
                    updateEditSelectedTheLoais();
                    updateEditSelectedNXBs();
                    updateEditTacGiasInput();
                    updateEditTheLoaisInput();
                    updateEditNXBsInput();
                    
                    // Set form action
                    document.getElementById('editBookForm').action = `/books/${id}`;
                    
                    // Show modal
                    document.getElementById('editModal').style.display = 'block';
                } catch (error) {
                    console.error('Error processing book data:', error);
                    alert('Có lỗi xảy ra khi xử lý dữ liệu sách: ' + error.message);
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                alert('Có lỗi xảy ra khi tải dữ liệu sách: ' + error.message);
            });
        }

        // Script đơn giản để ẩn thông báo sau vài giây
        document.addEventListener('DOMContentLoaded', function() {
            const toast = document.getElementById('toast-message');
            if (toast) {
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 3000);
            }
        });

        // Genre Filter Scroll Functions
        function scrollGenres(direction) {
            const container = document.getElementById('genreFilter');
            const scrollAmount = 200;
            const currentScroll = container.scrollLeft;
            const targetScroll = currentScroll + (direction * scrollAmount);
            
            // Smooth scroll
            container.scrollTo({
                left: targetScroll,
                behavior: 'smooth'
            });
            
            // Update scroll button states
            updateScrollButtons();
        }

        function updateScrollButtons() {
            const container = document.getElementById('genreFilter');
            const leftBtn = document.querySelector('.scroll-left');
            const rightBtn = document.querySelector('.scroll-right');
            
            if (!container || !leftBtn || !rightBtn) return;
            
            // Check if can scroll left
            leftBtn.disabled = container.scrollLeft <= 0;
            
            // Check if can scroll right
            const maxScroll = container.scrollWidth - container.clientWidth;
            rightBtn.disabled = container.scrollLeft >= maxScroll;
        }

        // Initialize scroll buttons on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateScrollButtons();
            
            // Update button states when scrolling manually
            const container = document.getElementById('genreFilter');
            if (container) {
                container.addEventListener('scroll', updateScrollButtons);
            }
        });

        // Tác giả functions
        function searchTacGias(query) {
            const dropdown = document.getElementById('tacGiasList');
            const items = dropdown.querySelectorAll('.dropdown-item:not(.add-new-item)');
            
            if (query.length > 0) {
                dropdown.style.display = 'block';
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(query.toLowerCase())) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                dropdown.style.display = 'none';
            }
        }

        function selectTacGia(id, name) {
            // Chỉ cho phép chọn 1 tác giả
            selectedTacGias = [{id, name}];
            updateSelectedTacGias();
            updateTacGiasInput();
            document.getElementById('tacGiaSearch').value = '';
            document.getElementById('tacGiasList').style.display = 'none';
            // Xóa border lỗi nếu có
            document.getElementById('tacGiasContainer').style.border = '';
        }

        function updateSelectedTacGias() {
            const container = document.getElementById('selectedTacGias');
            container.innerHTML = '';
            selectedTacGias.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    <span>${item.name}</span>
                    <button class="remove-btn" onclick="removeTacGia(${index})" type="button">×</button>
                `;
                container.appendChild(div);
            });
        }

        function removeTacGia(index) {
            selectedTacGias.splice(index, 1);
            updateSelectedTacGias();
            updateTacGiasInput();
        }

        function updateTacGiasInput() {
            const input = document.getElementById('tacGiasInput');
            input.value = selectedTacGias.length > 0 ? selectedTacGias[0].id : '';
        }

        // Thể loại functions (tương tự)
        function searchTheLoais(query) {
            const dropdown = document.getElementById('theLoaisList');
            const items = dropdown.querySelectorAll('.dropdown-item:not(.add-new-item)');
            
            if (query.length > 0) {
                dropdown.style.display = 'block';
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(query.toLowerCase())) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                dropdown.style.display = 'none';
            }
        }

        function selectTheLoai(id, name) {
            // Kiểm tra xem đã chọn chưa
            if (!selectedTheLoais.find(item => item.id === id)) {
                selectedTheLoais.push({id, name});
                updateSelectedTheLoais();
                updateTheLoaisInput();
            }
            document.getElementById('theLoaiSearch').value = '';
            document.getElementById('theLoaisList').style.display = 'none';
            // Xóa border lỗi nếu có
            document.getElementById('theLoaisContainer').style.border = '';
        }

        function updateSelectedTheLoais() {
            const container = document.getElementById('selectedTheLoais');
            container.innerHTML = '';
            selectedTheLoais.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    <span>${item.name}</span>
                    <button class="remove-btn" onclick="removeTheLoai(${index})" type="button">×</button>
                `;
                container.appendChild(div);
            });
        }

        function removeTheLoai(index) {
            selectedTheLoais.splice(index, 1);
            updateSelectedTheLoais();
            updateTheLoaisInput();
        }

        function updateTheLoaisInput() {
            const input = document.getElementById('theLoaisInput');
            const ids = selectedTheLoais.map(item => item.id);
            input.value = JSON.stringify(ids);
        }

        // NXB functions (tương tự)
        function searchNXBs(query) {
            const dropdown = document.getElementById('nxbsList');
            const items = dropdown.querySelectorAll('.dropdown-item:not(.add-new-item)');
            
            if (query.length > 0) {
                dropdown.style.display = 'block';
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(query.toLowerCase())) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                dropdown.style.display = 'none';
            }
        }

        function selectNXB(id, name) {
            // Chỉ cho phép chọn 1 nhà xuất bản
            selectedNXBs = [{id, name}];
            updateSelectedNXBs();
            updateNXBsInput();
            document.getElementById('nxbSearch').value = '';
            document.getElementById('nxbsList').style.display = 'none';
            // Xóa border lỗi nếu có
            document.getElementById('nhaXuatBansContainer').style.border = '';
        }

        function updateSelectedNXBs() {
            const container = document.getElementById('selectedNXBs');
            container.innerHTML = '';
            selectedNXBs.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    <span>${item.name}</span>
                    <button class="remove-btn" onclick="removeNXB(${index})" type="button">×</button>
                `;
                container.appendChild(div);
            });
        }

        function removeNXB(index) {
            selectedNXBs.splice(index, 1);
            updateSelectedNXBs();
            updateNXBsInput();
        }

        function updateNXBsInput() {
            const input = document.getElementById('nxbsInput');
            input.value = selectedNXBs.length > 0 ? selectedNXBs[0].id : '';
        }

        // Edit modal functions (similar pattern)
        function searchEditTacGias(query) {
            const dropdown = document.getElementById('editTacGiasList');
            const items = dropdown.querySelectorAll('.dropdown-item:not(.add-new-item)');
            
            if (query.length > 0) {
                dropdown.style.display = 'block';
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(query.toLowerCase())) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                dropdown.style.display = 'none';
            }
        }

        function selectEditTacGia(id, name) {
            // Chỉ cho phép chọn 1 tác giả
            editSelectedTacGias = [{id, name}];
            updateEditSelectedTacGias();
            updateEditTacGiasInput();
            document.getElementById('editTacGiaSearch').value = '';
            document.getElementById('editTacGiasList').style.display = 'none';
            // Xóa border lỗi nếu có
            document.getElementById('editTacGiasContainer').style.border = '';
        }

        function updateEditSelectedTacGias() {
            const container = document.getElementById('editSelectedTacGias');
            container.innerHTML = '';
            editSelectedTacGias.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    <span>${item.name}</span>
                    <button class="remove-btn" onclick="removeEditTacGia(${index})" type="button">×</button>
                `;
                container.appendChild(div);
            });
        }

        function removeEditTacGia(index) {
            editSelectedTacGias.splice(index, 1);
            updateEditSelectedTacGias();
            updateEditTacGiasInput();
        }

        function updateEditTacGiasInput() {
            const input = document.getElementById('editTacGiasInput');
            input.value = editSelectedTacGias.length > 0 ? editSelectedTacGias[0].id : '';
        }

        // TheLoais
        function searchEditTheLoais(query) {
            const dropdown = document.getElementById('editTheLoaisList');
            const items = dropdown.querySelectorAll('.dropdown-item:not(.add-new-item)');
            
            if (query.length > 0) {
                dropdown.style.display = 'block';
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(query.toLowerCase())) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                dropdown.style.display = 'none';
            }
        }

        function selectEditTheLoai(id, name) {
            // Kiểm tra xem đã chọn chưa
            if (!editSelectedTheLoais.find(item => item.id === id)) {
                editSelectedTheLoais.push({id, name});
                updateEditSelectedTheLoais();
                updateEditTheLoaisInput();
            }
            document.getElementById('editTheLoaiSearch').value = '';
            document.getElementById('editTheLoaisList').style.display = 'none';
            // Xóa border lỗi nếu có
            document.getElementById('editTheLoaisContainer').style.border = '';
        }

        function updateEditSelectedTheLoais() {
            const container = document.getElementById('editSelectedTheLoais');
            container.innerHTML = '';
            editSelectedTheLoais.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    <span>${item.name}</span>
                    <button class="remove-btn" onclick="removeEditTheLoai(${index})" type="button">×</button>
                `;
                container.appendChild(div);
            });
        }

        function removeEditTheLoai(index) {
            editSelectedTheLoais.splice(index, 1);
            updateEditSelectedTheLoais();
            updateEditTheLoaisInput();
        }

        // NXBs
        function searchEditNXBs(query) {
            const dropdown = document.getElementById('editNxbsList');
            const items = dropdown.querySelectorAll('.dropdown-item:not(.add-new-item)');
            
            if (query.length > 0) {
                dropdown.style.display = 'block';
                items.forEach(item => {
                    const name = item.getAttribute('data-name').toLowerCase();
                    if (name.includes(query.toLowerCase())) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            } else {
                dropdown.style.display = 'none';
            }
        }

        function selectEditNXB(id, name) {
            // Chỉ cho phép chọn 1 nhà xuất bản
            editSelectedNXBs = [{id, name}];
            updateEditSelectedNXBs();
            updateEditNXBsInput();
            document.getElementById('editNxbSearch').value = '';
            document.getElementById('editNxbsList').style.display = 'none';
            // Xóa border lỗi nếu có
            document.getElementById('editNhaXuatBansContainer').style.border = '';
        }

        function updateEditSelectedNXBs() {
            const container = document.getElementById('editSelectedNXBs');
            container.innerHTML = '';
            editSelectedNXBs.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    <span>${item.name}</span>
                    <button class="remove-btn" onclick="removeEditNXB(${index})" type="button">×</button>
                `;
                container.appendChild(div);
            });
        }

        function removeEditNXB(index) {
            editSelectedNXBs.splice(index, 1);
            updateEditSelectedNXBs();
            updateEditNXBsInput();
        }

        function updateEditNXBsInput() {
            const input = document.getElementById('editNxbsInput');
            input.value = editSelectedNXBs.length > 0 ? editSelectedNXBs[0].id : '';
        }

        function updateEditTheLoaisInput() {
            const input = document.getElementById('editTheLoaisInput');
            const ids = editSelectedTheLoais.map(item => item.id);
            input.value = JSON.stringify(ids);
        }

        function updateEditSelectedTheLoais() {
            const container = document.getElementById('editSelectedTheLoais');
            container.innerHTML = '';
            editSelectedTheLoais.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    <span>${item.name}</span>
                    <button class="remove-btn" onclick="removeEditTheLoai(${index})" type="button">×</button>
                `;
                container.appendChild(div);
            });
        }

        function removeEditTheLoai(index) {
            editSelectedTheLoais.splice(index, 1);
            updateEditSelectedTheLoais();
            updateEditTheLoaisInput();
        }

        // Quick add functions
        function addNewTacGia() {
            console.log('Opening TacGia modal...');
            const modal = document.getElementById('quickAddTacGiaModal');
            
            if (modal) {
                console.log('Found existing TacGia modal, showing it...');
                
                // Remove all existing styles
                modal.removeAttribute('style');
                
                // Set styles directly with important
                modal.style.setProperty('display', 'block', 'important');
                modal.style.setProperty('position', 'fixed', 'important');
                modal.style.setProperty('z-index', '10000', 'important');
                modal.style.setProperty('left', '0', 'important');
                modal.style.setProperty('top', '0', 'important');
                modal.style.setProperty('width', '100%', 'important');
                modal.style.setProperty('height', '100%', 'important');
                modal.style.setProperty('background-color', 'rgba(0, 0, 0, 0.5)', 'important');
                modal.style.setProperty('backdrop-filter', 'blur(5px)', 'important');
                
                const input = document.getElementById('quickTenTacGia');
                if (input) input.focus();
                console.log('TacGia modal opened successfully');
                console.log('Modal display style:', window.getComputedStyle(modal).display);
            } else {
                console.error('TacGia modal not found in HTML!');
            }
        }

        function addNewTheLoai() {
            window.location.href = "{{ route('genres.index') }}";
        }

        function addNewNXB() {
            console.log('Opening NXB modal...');
            const modal = document.getElementById('quickAddNXBModal');
            
            if (modal) {
                console.log('Found existing NXB modal, showing it...');
                
                // Remove all existing styles
                modal.removeAttribute('style');
                
                // Set styles directly with important
                modal.style.setProperty('display', 'block', 'important');
                modal.style.setProperty('position', 'fixed', 'important');
                modal.style.setProperty('z-index', '10000', 'important');
                modal.style.setProperty('left', '0', 'important');
                modal.style.setProperty('top', '0', 'important');
                modal.style.setProperty('width', '100%', 'important');
                modal.style.setProperty('height', '100%', 'important');
                modal.style.setProperty('background-color', 'rgba(0, 0, 0, 0.5)', 'important');
                modal.style.setProperty('backdrop-filter', 'blur(5px)', 'important');
                
                const input = document.getElementById('quickTenNXB');
                if (input) input.focus();
                console.log('NXB modal opened successfully');
                console.log('Modal display style:', window.getComputedStyle(modal).display);
            } else {
                console.error('NXB modal not found in HTML!');
            }
        }
        
        function createTacGiaModal() {
            const modal = document.createElement('div');
            modal.id = 'quickAddTacGiaModal';
            modal.className = 'modal';
            modal.style.cssText = 'position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(5px);';
            
            modal.innerHTML = `
                <div class="modal-content quick-add-modal" style="background: white; margin: 5% auto; padding: 20px; border-radius: 10px; max-width: 400px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
                    <div class="modal-header" style="background: white; color: #2d3748; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; margin: -20px -20px 20px -20px; border-radius: 10px 10px 0 0;">
                        <h3 style="margin: 0; font-size: 1.5rem; font-weight: 600; color: #2d3748;">Thêm tác giả mới</h3>
                        <span class="close" onclick="closeQuickAddTacGiaModal()" style="color: #6c757d; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                    </div>
                    <form id="quickAddTacGiaForm">
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label for="quickTenTacGia" style="display: block; margin-bottom: 8px; color: #2d3748; font-weight: 500; font-size: 14px;">Tên tác giả *</label>
                            <input type="text" id="quickTenTacGia" name="TenTacGia" required style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 16px;">
                        </div>
                        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                            <button type="button" class="btn btn-secondary" onclick="closeQuickAddTacGiaModal()" style="padding: 12px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; background-color: #e2e8f0; color: #4a5568;">Hủy</button>
                            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">Thêm</button>
                        </div>
                    </form>
                </div>
            `;
            
            // Add event listener for form submission
            setTimeout(() => {
                const form = modal.querySelector('#quickAddTacGiaForm');
                if (form) {
                    form.addEventListener('submit', handleTacGiaFormSubmit);
                }
            }, 100);
            
            return modal;
        }
        
        function handleTacGiaFormSubmit(e) {
            e.preventDefault();
            console.log('TacGia form submitted');
            
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Đang thêm...';
            submitBtn.disabled = true;
            
            fetch('/api/tacgia', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addTacGiaToDropdowns(data.data);
                    closeQuickAddTacGiaModal();
                    showToast('Thêm tác giả thành công!', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi thêm tác giả', 'error');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }

        function addNewTheLoai() {
            window.location.href = "{{ route('genres.index') }}";
        }

        function addNewNXB() {
            console.log('Opening NXB modal...');
            const modal = document.getElementById('quickAddNXBModal');
            
            if (modal) {
                console.log('Found existing NXB modal, showing it...');
                
                // Remove all existing styles
                modal.removeAttribute('style');
                
                // Set styles directly with important
                modal.style.setProperty('display', 'block', 'important');
                modal.style.setProperty('position', 'fixed', 'important');
                modal.style.setProperty('z-index', '10000', 'important');
                modal.style.setProperty('left', '0', 'important');
                modal.style.setProperty('top', '0', 'important');
                modal.style.setProperty('width', '100%', 'important');
                modal.style.setProperty('height', '100%', 'important');
                modal.style.setProperty('background-color', 'rgba(0, 0, 0, 0.5)', 'important');
                modal.style.setProperty('backdrop-filter', 'blur(5px)', 'important');
                
                const input = document.getElementById('quickTenNXB');
                if (input) input.focus();
                console.log('NXB modal opened successfully');
                console.log('Modal display style:', window.getComputedStyle(modal).display);
            } else {
                console.error('NXB modal not found in HTML!');
            }
        }
        
        function createNXBModal() {
            const modal = document.createElement('div');
            modal.id = 'quickAddNXBModal';
            modal.className = 'modal';
            modal.style.cssText = 'position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(5px);';
            
            modal.innerHTML = `
                <div class="modal-content quick-add-modal" style="background: white; margin: 5% auto; padding: 20px; border-radius: 10px; max-width: 400px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);">
                    <div class="modal-header" style="background: white; color: #2d3748; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; margin: -20px -20px 20px -20px; border-radius: 10px 10px 0 0;">
                        <h3 style="margin: 0; font-size: 1.5rem; font-weight: 600; color: #2d3748;">Thêm nhà xuất bản mới</h3>
                        <span class="close" onclick="closeQuickAddNXBModal()" style="color: #6c757d; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                    </div>
                    <form id="quickAddNXBForm">
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label for="quickTenNXB" style="display: block; margin-bottom: 8px; color: #2d3748; font-weight: 500; font-size: 14px;">Tên nhà xuất bản *</label>
                            <input type="text" id="quickTenNXB" name="TenNXB" required style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 16px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 25px;">
                            <label for="quickDiaChiNXB" style="display: block; margin-bottom: 8px; color: #2d3748; font-weight: 500; font-size: 14px;">Địa chỉ</label>
                            <input type="text" id="quickDiaChiNXB" name="DiaChi" style="width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 16px;">
                        </div>
                        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                            <button type="button" class="btn btn-secondary" onclick="closeQuickAddNXBModal()" style="padding: 12px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; background-color: #e2e8f0; color: #4a5568;">Hủy</button>
                            <button type="submit" class="btn btn-primary" style="padding: 12px 24px; border: none; border-radius: 10px; font-size: 14px; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">Thêm</button>
                        </div>
                    </form>
                </div>
            `;
            
            // Add event listener for form submission
            setTimeout(() => {
                const form = modal.querySelector('#quickAddNXBForm');
                if (form) {
                    form.addEventListener('submit', handleNXBFormSubmit);
                }
            }, 100);
            
            return modal;
        }
        
        function handleNXBFormSubmit(e) {
            e.preventDefault();
            console.log('NXB form submitted');
            
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Đang thêm...';
            submitBtn.disabled = true;
            
            fetch('/api/nhaxuatban', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addNXBToDropdowns(data.data);
                    closeQuickAddNXBModal();
                    showToast('Thêm nhà xuất bản thành công!', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi thêm nhà xuất bản', 'error');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        }

        // Quick Add Modal Functions
        function closeQuickAddTacGiaModal() {
            const modal = document.getElementById('quickAddTacGiaModal');
            if (modal) {
                modal.style.display = 'none';
                const form = document.getElementById('quickAddTacGiaForm');
                if (form) form.reset();
                console.log('TacGia modal closed');
            }
        }

        function closeQuickAddNXBModal() {
            const modal = document.getElementById('quickAddNXBModal');
            if (modal) {
                modal.style.display = 'none';
                const form = document.getElementById('quickAddNXBForm');
                if (form) form.reset();
                console.log('NXB modal closed');
            }
        }

        // Test function to check modal elements
        function testModals() {
            console.log('Testing modals...');
            const tacGiaModal = document.getElementById('quickAddTacGiaModal');
            const nxbModal = document.getElementById('quickAddNXBModal');
            
            console.log('TacGia modal:', tacGiaModal);
            console.log('NXB modal:', nxbModal);
            
            if (tacGiaModal) {
                console.log('TacGia modal classes:', tacGiaModal.className);
                console.log('TacGia modal display:', window.getComputedStyle(tacGiaModal).display);
                console.log('TacGia modal z-index:', window.getComputedStyle(tacGiaModal).zIndex);
                console.log('TacGia modal position:', window.getComputedStyle(tacGiaModal).position);
                console.log('TacGia modal content:', tacGiaModal.innerHTML.substring(0, 200) + '...');
            }
            
            if (nxbModal) {
                console.log('NXB modal classes:', nxbModal.className);
                console.log('NXB modal display:', window.getComputedStyle(nxbModal).display);
                console.log('NXB modal z-index:', window.getComputedStyle(nxbModal).zIndex);
                console.log('NXB modal position:', window.getComputedStyle(nxbModal).position);
                console.log('NXB modal content:', nxbModal.innerHTML.substring(0, 200) + '...');
            }
            
            // Check if modals exist in HTML
            const allModals = document.querySelectorAll('.modal');
            console.log('All modals found:', allModals.length);
            allModals.forEach((modal, index) => {
                console.log(`Modal ${index}:`, modal.id, modal.className);
            });
            
            // Check if modals exist by querySelector
            const tacGiaByQuery = document.querySelector('#quickAddTacGiaModal');
            const nxbByQuery = document.querySelector('#quickAddNXBModal');
            console.log('TacGia by querySelector:', tacGiaByQuery);
            console.log('NXB by querySelector:', nxbByQuery);
        }

        // Force show modal for testing
        function forceShowModal() {
            console.log('Force showing modal...');
            const modal = document.getElementById('quickAddTacGiaModal');
            if (modal) {
                modal.style.cssText = 'display: block !important; position: fixed !important; z-index: 10000 !important; left: 0 !important; top: 0 !important; width: 100% !important; height: 100% !important; background-color: rgba(0, 0, 0, 0.5) !important;';
                console.log('Modal forced to show');
                console.log('Modal style:', modal.style.cssText);
                
                // Also check if content is visible
                const content = modal.querySelector('.modal-content');
                if (content) {
                    content.style.cssText = 'display: block !important; background: white !important; margin: 5% auto !important; padding: 20px !important; border-radius: 10px !important; max-width: 400px !important;';
                    console.log('Modal content forced to show');
                }
            } else {
                console.error('Modal not found for force show');
            }
        }
        
        // Simple test function
        function simpleTest() {
            console.log('=== Simple Test ===');
            
            // Create a simple modal
            const testModal = document.createElement('div');
            testModal.id = 'testModal';
            testModal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;';
            
            testModal.innerHTML = `
                <div style="background: white; padding: 20px; border-radius: 10px; max-width: 300px;">
                    <h3>Test Modal</h3>
                    <p>This is a simple test modal.</p>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Close</button>
                </div>
            `;
            
            document.body.appendChild(testModal);
            console.log('Simple test modal created and shown');
        }
        
        // Test existing modal function
        function testExistingModal() {
            console.log('=== Test Existing Modal ===');
            
            const modal = document.getElementById('quickAddTacGiaModal');
            if (modal) {
                console.log('Modal found:', modal);
                console.log('Modal display before:', window.getComputedStyle(modal).display);
                console.log('Modal visibility before:', window.getComputedStyle(modal).visibility);
                console.log('Modal opacity before:', window.getComputedStyle(modal).opacity);
                
                // Remove any existing styles
                modal.removeAttribute('style');
                
                // Set new styles
                modal.style.display = 'block';
                modal.style.position = 'fixed';
                modal.style.zIndex = '10000';
                modal.style.left = '0';
                modal.style.top = '0';
                modal.style.width = '100%';
                modal.style.height = '100%';
                modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
                modal.style.backdropFilter = 'blur(5px)';
                
                console.log('Modal display after:', window.getComputedStyle(modal).display);
                console.log('Modal visibility after:', window.getComputedStyle(modal).visibility);
                console.log('Modal opacity after:', window.getComputedStyle(modal).opacity);
                console.log('Modal style:', modal.style.cssText);
                
                // Check if content is visible
                const content = modal.querySelector('.modal-content');
                if (content) {
                    console.log('Modal content found:', content);
                    console.log('Content display:', window.getComputedStyle(content).display);
                } else {
                    console.log('Modal content not found');
                }
            } else {
                console.log('Modal not found');
            }
        }

        // Run test on page load
        document.addEventListener('DOMContentLoaded', function() {
            testModals();
            
            // Add event listeners for existing modal forms
            const tacGiaForm = document.getElementById('quickAddTacGiaForm');
            const nxbForm = document.getElementById('quickAddNXBForm');
            
            if (tacGiaForm) {
                tacGiaForm.addEventListener('submit', handleTacGiaFormSubmit);
                console.log('TacGia form event listener added');
            }
            
            if (nxbForm) {
                nxbForm.addEventListener('submit', handleNXBFormSubmit);
                console.log('NXB form event listener added');
            }
            
            // Additional test: Check if modals are in DOM
            setTimeout(() => {
                console.log('=== DOM Check ===');
                const allModals = document.querySelectorAll('.modal');
                console.log('All modals found:', allModals.length);
                allModals.forEach((modal, index) => {
                    console.log(`Modal ${index}:`, modal.id, modal.className);
                    console.log(`Modal ${index} display:`, window.getComputedStyle(modal).display);
                    console.log(`Modal ${index} visibility:`, window.getComputedStyle(modal).visibility);
                    console.log(`Modal ${index} opacity:`, window.getComputedStyle(modal).opacity);
                });
            }, 1000);
        });

        // AJAX handlers for quick add forms
        var tacGiaForm = document.getElementById('quickAddTacGiaForm');
        if (tacGiaForm) {
            tacGiaForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Đang thêm...';
            submitBtn.disabled = true;
            fetch('/api/tacgia', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add to dropdown lists
                    addTacGiaToDropdowns(data.data);
                    closeQuickAddTacGiaModal();
                    showToast('Thêm tác giả thành công!', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi thêm tác giả', 'error');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
        }

        var nxbForm = document.getElementById('quickAddNXBForm');
        if (nxbForm) {
            nxbForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Đang thêm...';
            submitBtn.disabled = true;
            fetch('/api/nhaxuatban', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add to dropdown lists
                    addNXBToDropdowns(data.data);
                    closeQuickAddNXBModal();
                    showToast('Thêm nhà xuất bản thành công!', 'success');
                } else {
                    showToast(data.message || 'Có lỗi xảy ra', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra khi thêm nhà xuất bản', 'error');
            })
            .finally(() => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
        }

        // Helper functions to add new items to dropdowns
        function addTacGiaToDropdowns(tacGia) {
            const dropdownIds = ['tacGiasList', 'editTacGiasList'];
            
            dropdownIds.forEach(dropdownId => {
                const dropdown = document.getElementById(dropdownId);
                if (dropdown) {
                    const addNewItem = dropdown.querySelector('.add-new-item');
                    const newItem = document.createElement('div');
                    newItem.className = 'dropdown-item';
                    newItem.setAttribute('data-id', tacGia.id);
                    newItem.setAttribute('data-name', tacGia.TenTacGia);
                    
                    if (dropdownId === 'tacGiasList') {
                        newItem.setAttribute('onclick', `selectTacGia('${tacGia.id}', '${tacGia.TenTacGia}')`);
                    } else {
                        newItem.setAttribute('onclick', `selectEditTacGia('${tacGia.id}', '${tacGia.TenTacGia}')`);
                    }
                    
                    newItem.textContent = tacGia.TenTacGia;
                    dropdown.insertBefore(newItem, addNewItem);
                }
            });
        }

        function addNXBToDropdowns(nxb) {
            const dropdownIds = ['nxbsList', 'editNxbsList'];
            
            dropdownIds.forEach(dropdownId => {
                const dropdown = document.getElementById(dropdownId);
                if (dropdown) {
                    const addNewItem = dropdown.querySelector('.add-new-item');
                    const newItem = document.createElement('div');
                    newItem.className = 'dropdown-item';
                    newItem.setAttribute('data-id', nxb.id);
                    newItem.setAttribute('data-name', nxb.TenNXB);
                    
                    if (dropdownId === 'nxbsList') {
                        newItem.setAttribute('onclick', `selectNXB('${nxb.id}', '${nxb.TenNXB}')`);
                    } else {
                        newItem.setAttribute('onclick', `selectEditNXB('${nxb.id}', '${nxb.TenNXB}')`);
                    }
                    
                    newItem.textContent = nxb.TenNXB;
                    dropdown.insertBefore(newItem, addNewItem);
                }
            });
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

        // Modal functions
        function openAddModal() {
            selectedTacGias = [];
            selectedTheLoais = [];
            selectedNXBs = [];
            updateSelectedTacGias();
            updateSelectedTheLoais();
            updateSelectedNXBs();
            // Reset border validation
            document.getElementById('tacGiasContainer').style.border = '';
            document.getElementById('theLoaisContainer').style.border = '';
            document.getElementById('nhaXuatBansContainer').style.border = '';
            document.getElementById('addModal').style.display = 'block';
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('addBookForm').reset();
            selectedTacGias = [];
            selectedTheLoais = [];
            selectedNXBs = [];
            updateSelectedTacGias();
            updateSelectedTheLoais();
            updateSelectedNXBs();
            // Reset border validation
            document.getElementById('tacGiasContainer').style.border = '';
            document.getElementById('theLoaisContainer').style.border = '';
            document.getElementById('nhaXuatBansContainer').style.border = '';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editBookForm').reset();
            editSelectedTacGias = [];
            editSelectedTheLoais = [];
            editSelectedNXBs = [];
            // Reset border validation
            document.getElementById('editTacGiasContainer').style.border = '';
            document.getElementById('editTheLoaisContainer').style.border = '';
            document.getElementById('editNhaXuatBansContainer').style.border = '';
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-select-container')) {
                document.querySelectorAll('.dropdown-list').forEach(dropdown => {
                    dropdown.style.display = 'none';
                });
            }
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            console.log('Window clicked, target:', event.target);
            console.log('Target class:', event.target.className);
            console.log('Target id:', event.target.id);
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            const quickAddTacGiaModal = document.getElementById('quickAddTacGiaModal');
            const quickAddNXBModal = document.getElementById('quickAddNXBModal');
            
            if (event.target == addModal) {
                closeAddModal();
            }
            if (event.target == editModal) {
                closeEditModal();
            }
            if (event.target == quickAddTacGiaModal) {
                console.log('Closing TacGia modal via outside click');
                closeQuickAddTacGiaModal();
            }
            if (event.target == quickAddNXBModal) {
                console.log('Closing NXB modal via outside click');
                closeQuickAddNXBModal();
            }
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const addModal = document.getElementById('addModal');
                const editModal = document.getElementById('editModal');
                const quickAddTacGiaModal = document.getElementById('quickAddTacGiaModal');
                const quickAddNXBModal = document.getElementById('quickAddNXBModal');
                
                if (addModal.style.display === 'block') {
                    closeAddModal();
                }
                if (editModal.style.display === 'block') {
                    closeEditModal();
                }
                if (quickAddTacGiaModal.style.display === 'block') {
                    closeQuickAddTacGiaModal();
                }
                if (quickAddNXBModal.style.display === 'block') {
                    closeQuickAddNXBModal();
                }
            }
        });

        // Validation functions
        function validateAddForm() {
            let isValid = true;
            
            // Đảm bảo input được cập nhật trước khi validate
            updateTheLoaisInput();
            
            // Debug: Kiểm tra dữ liệu
            console.log('Selected theLoais:', selectedTheLoais);
            console.log('TheLoais input value:', document.getElementById('theLoaisInput').value);
            
            // Kiểm tra tác giả
            if (selectedTacGias.length === 0) {
                showToast('Vui lòng chọn tác giả', 'error');
                document.getElementById('tacGiasContainer').style.border = '2px solid #e74c3c';
                document.getElementById('tacGiaSearch').focus();
                isValid = false;
            } else {
                document.getElementById('tacGiasContainer').style.border = '';
            }
            
            // Kiểm tra thể loại
            if (selectedTheLoais.length === 0) {
                showToast('Vui lòng chọn thể loại', 'error');
                document.getElementById('theLoaisContainer').style.border = '2px solid #e74c3c';
                if (isValid) document.getElementById('theLoaiSearch').focus();
                isValid = false;
            } else {
                document.getElementById('theLoaisContainer').style.border = '';
            }
            
            // Kiểm tra nhà xuất bản
            if (selectedNXBs.length === 0) {
                showToast('Vui lòng chọn nhà xuất bản', 'error');
                document.getElementById('nhaXuatBansContainer').style.border = '2px solid #e74c3c';
                if (isValid) document.getElementById('nxbSearch').focus();
                isValid = false;
            } else {
                document.getElementById('nhaXuatBansContainer').style.border = '';
            }
            
            // Kiểm tra tình trạng sách
            const tinhTrangValue = document.getElementById('TinhTrang').value;
            if (tinhTrangValue === '') {
                showToast('Vui lòng chọn tình trạng sách', 'error');
                document.getElementById('TinhTrang').style.border = '2px solid #e74c3c';
                if (isValid) document.getElementById('TinhTrang').focus();
                isValid = false;
            } else {
                document.getElementById('TinhTrang').style.border = '';
            }
            
            return isValid;
        }

        function validateEditForm() {
            let isValid = true;
            
            // Đảm bảo input được cập nhật trước khi validate
            updateEditTheLoaisInput();
            
            // Debug: Kiểm tra dữ liệu
            console.log('Edit Selected theLoais:', editSelectedTheLoais);
            console.log('Edit TheLoais input value:', document.getElementById('editTheLoaisInput').value);
            
            // Kiểm tra tác giả
            if (editSelectedTacGias.length === 0) {
                showToast('Vui lòng chọn tác giả', 'error');
                document.getElementById('editTacGiasContainer').style.border = '2px solid #e74c3c';
                document.getElementById('editTacGiaSearch').focus();
                isValid = false;
            } else {
                document.getElementById('editTacGiasContainer').style.border = '';
            }
            
            // Kiểm tra thể loại
            if (editSelectedTheLoais.length === 0) {
                showToast('Vui lòng chọn thể loại', 'error');
                document.getElementById('editTheLoaisContainer').style.border = '2px solid #e74c3c';
                if (isValid) document.getElementById('editTheLoaiSearch').focus();
                isValid = false;
            } else {
                document.getElementById('editTheLoaisContainer').style.border = '';
            }
            
            // Kiểm tra nhà xuất bản
            if (editSelectedNXBs.length === 0) {
                showToast('Vui lòng chọn nhà xuất bản', 'error');
                document.getElementById('editNhaXuatBansContainer').style.border = '2px solid #e74c3c';
                if (isValid) document.getElementById('editNxbSearch').focus();
                isValid = false;
            } else {
                document.getElementById('editNhaXuatBansContainer').style.border = '';
            }
            
            // Tình trạng sách không cần kiểm tra vì được quản lý tự động
            
            return isValid;
        }

        // Function to handle book deletion with status check
        async function deleteBook(bookId, bookName, bookStatus) {
            // Check if book is damaged (status = 3) or lost (status = 4)
            if (bookStatus == 3) {
                alert('⚠️ Không thể xóa sách đã hỏng!\n\nSách "' + bookName + '" đã được đánh dấu là hỏng và cần được xử lý riêng.');
                return;
            }
            
            if (bookStatus == 4) {
                alert('⚠️ Không thể xóa sách đã mất!\n\nSách "' + bookName + '" đã được đánh dấu là mất và cần được xử lý riêng.');
                return;
            }
            
            // Warning for borrowed books
            let confirmMessage = '🗑️ Xác nhận xóa sách\n\nBạn có chắc chắn muốn xóa sách "' + bookName + '"?\n\nHành động này không thể hoàn tác!';
            
            if (bookStatus == 0) {
                confirmMessage = '⚠️ Cảnh báo: Sách đang được mượn!\n\nSách "' + bookName + '" hiện đang được mượn bởi độc giả.\n\nNếu xóa, tình trạng sách sẽ được cập nhật về "Có sẵn" và phiếu mượn sẽ bị ảnh hưởng.\n\nBạn có chắc chắn muốn tiếp tục?';
            }

            // Show confirmation dialog
            if (!confirm(confirmMessage)) {
                return;
            }

            try {
                // Create form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/books/${bookId}`;
                form.style.display = 'none';

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Add method override
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);

                // Submit form
                form.submit();

            } catch (error) {
                console.error('Error deleting book:', error);
                alert('❌ Có lỗi xảy ra khi xóa sách: ' + error.message);
            }
        }

        // Basic test function
        function basicTest() {
            console.log('=== Basic Test ===');
            
            // Create a very simple modal
            const modal = document.createElement('div');
            modal.id = 'basicTestModal';
            modal.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                    <div style="background: white; padding: 20px; border-radius: 10px; max-width: 300px;">
                        <h3>Basic Test Modal</h3>
                        <p>This is a basic test modal.</p>
                        <button onclick="document.getElementById('basicTestModal').remove()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Close</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            console.log('Basic test modal created and shown');
        }
        
        // Check existing modal
        function checkModal() {
            console.log('=== Check Modal ===');
            
            const modal = document.getElementById('quickAddTacGiaModal');
            console.log('Modal element:', modal);
            
            if (modal) {
                console.log('Modal found!');
                console.log('Modal HTML:', modal.outerHTML.substring(0, 200) + '...');
                console.log('Modal display (computed):', window.getComputedStyle(modal).display);
                console.log('Modal visibility (computed):', window.getComputedStyle(modal).visibility);
                console.log('Modal opacity (computed):', window.getComputedStyle(modal).opacity);
                console.log('Modal position (computed):', window.getComputedStyle(modal).position);
                console.log('Modal z-index (computed):', window.getComputedStyle(modal).zIndex);
                
                // Check if modal has any inline styles
                console.log('Modal inline styles:', modal.style.cssText);
                
                // Check modal content
                const content = modal.querySelector('.modal-content');
                if (content) {
                    console.log('Modal content found:', content);
                    console.log('Content display:', window.getComputedStyle(content).display);
                } else {
                    console.log('Modal content not found');
                }
            } else {
                console.log('Modal not found!');
            }
        }
        
        // Show modal directly
        function showModalDirect() {
            console.log('=== Show Modal Direct ===');
            
            const modal = document.getElementById('quickAddTacGiaModal');
            if (modal) {
                console.log('Showing modal directly...');
                
                // Remove all existing styles
                modal.removeAttribute('style');
                
                // Set styles directly
                modal.style.setProperty('display', 'block', 'important');
                modal.style.setProperty('position', 'fixed', 'important');
                modal.style.setProperty('z-index', '10000', 'important');
                modal.style.setProperty('left', '0', 'important');
                modal.style.setProperty('top', '0', 'important');
                modal.style.setProperty('width', '100%', 'important');
                modal.style.setProperty('height', '100%', 'important');
                modal.style.setProperty('background-color', 'rgba(0, 0, 0, 0.5)', 'important');
                modal.style.setProperty('backdrop-filter', 'blur(5px)', 'important');
                
                console.log('Modal styles set:', modal.style.cssText);
                console.log('Modal display after:', window.getComputedStyle(modal).display);
                
                // Focus on input
                const input = document.getElementById('quickTenTacGia');
                if (input) {
                    input.focus();
                    console.log('Input focused');
                }
            } else {
                console.log('Modal not found for direct show');
            }
        }

        // Real-time validation for publication year (add & edit modals)
        document.addEventListener('DOMContentLoaded', function() {
            // Add Book Modal
            (function() {
                const yearInput = document.getElementById('NamXuatBan');
                if (yearInput) {
                    const minYear = parseInt(yearInput.min);
                    const maxYear = parseInt(yearInput.max);
                    let yearError = yearInput.parentNode.querySelector('.year-error');
                    if (!yearError) {
                        yearError = document.createElement('div');
                        yearError.className = 'year-error';
                        yearError.style.color = '#e74c3c';
                        yearError.style.fontSize = '13px';
                        yearError.style.marginTop = '4px';
                        yearInput.parentNode.insertBefore(yearError, yearInput.nextSibling);
                    }
                    function validateYearInput() {
                        const value = parseInt(yearInput.value);
                        if (isNaN(value)) {
                            yearError.textContent = 'Vui lòng nhập năm xuất bản';
                            yearInput.style.border = '2px solid #e74c3c';
                            return false;
                        }
                        if (value < minYear || value > maxYear) {
                            yearError.textContent = `Năm xuất bản phải từ ${minYear} đến ${maxYear}`;
                            yearInput.style.border = '2px solid #e74c3c';
                            return false;
                        }
                        yearError.textContent = '';
                        yearInput.style.border = '';
                        return true;
                    }
                    yearInput.addEventListener('input', validateYearInput);
                    yearInput.addEventListener('blur', validateYearInput);
                    // Patch validateAddForm
                    const oldValidateAddForm = window.validateAddForm;
                    window.validateAddForm = function() {
                        let isValid = true;
                        if (!validateYearInput()) {
                            yearInput.focus();
                            isValid = false;
                        }
                        if (typeof oldValidateAddForm === 'function') {
                            isValid = oldValidateAddForm() && isValid;
                        }
                        return isValid;
                    };
                }
            })();
            // Edit Book Modal
            (function() {
                const yearInput = document.getElementById('editNamXuatBan');
                if (yearInput) {
                    const minYear = parseInt(yearInput.min);
                    const maxYear = parseInt(yearInput.max);
                    let yearError = yearInput.parentNode.querySelector('.year-error');
                    if (!yearError) {
                        yearError = document.createElement('div');
                        yearError.className = 'year-error';
                        yearError.style.color = '#e74c3c';
                        yearError.style.fontSize = '13px';
                        yearError.style.marginTop = '4px';
                        yearInput.parentNode.insertBefore(yearError, yearInput.nextSibling);
                    }
                    function validateYearInput() {
                        const value = parseInt(yearInput.value);
                        if (isNaN(value)) {
                            yearError.textContent = 'Vui lòng nhập năm xuất bản';
                            yearInput.style.border = '2px solid #e74c3c';
                            return false;
                        }
                        if (value < minYear || value > maxYear) {
                            yearError.textContent = `Năm xuất bản phải từ ${minYear} đến ${maxYear}`;
                            yearInput.style.border = '2px solid #e74c3c';
                            return false;
                        }
                        yearError.textContent = '';
                        yearInput.style.border = '';
                        return true;
                    }
                    yearInput.addEventListener('input', validateYearInput);
                    yearInput.addEventListener('blur', validateYearInput);
                    // Patch validateEditForm
                    const oldValidateEditForm = window.validateEditForm;
                    window.validateEditForm = function() {
                        let isValid = true;
                        if (!validateYearInput()) {
                            yearInput.focus();
                            isValid = false;
                        }
                        if (typeof oldValidateEditForm === 'function') {
                            isValid = oldValidateEditForm() && isValid;
                        }
                        return isValid;
                    };
                }
            })();
        });
    </script>
@endpush