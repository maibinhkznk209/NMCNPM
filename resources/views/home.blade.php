@extends('layouts.app')

@section('title', 'Tra cứu sách - Hệ thống thư viện')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/home.css') }}">
  <style>
    /* Enhanced filter styles - White theme */
    .book-search-container {
      background: white;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 25px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: 1px solid #e9ecef;
    }
    
    .book-search-container h2 {
      color: #2c3e50;
      margin-bottom: 15px;
      font-size: 1.3em;
      text-align: center;
      font-weight: 600;
    }
    
    .filter-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 20px;
    }
    
    .filter-group {
      display: flex;
      flex-direction: column;
      background: #f8f9fa;
      padding: 12px;
      border-radius: 8px;
      border: 1px solid #e9ecef;
    }
    
    .filter-group label {
      font-weight: 600;
      margin-bottom: 6px;
      color: #495057;
      font-size: 0.85em;
    }
    
    .filter-group select {
      padding: 8px 12px;
      border: 1px solid #ced4da;
      border-radius: 6px;
      background: white;
      color: #495057;
      font-size: 0.9em;
      transition: all 0.2s ease;
    }
    
    .filter-group select:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .sort-section {
      display: flex;
      gap: 12px;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      border: 1px solid #e9ecef;
    }
    
    .sort-section label {
      font-weight: 600;
      color: #495057;
      font-size: 0.85em;
    }
    
    .sort-section select {
      padding: 6px 10px;
      border: 1px solid #ced4da;
      border-radius: 5px;
      background: white;
      color: #495057;
      font-size: 0.85em;
      transition: all 0.2s ease;
    }
    
    .sort-section select:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
    }
    
    .filter-btn {
      padding: 8px 16px;
      background: #667eea;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.85em;
      transition: all 0.2s ease;
    }
    
    .filter-btn:hover {
      background: #5a6fd8;
      transform: translateY(-1px);
    }
    
    .clear-btn {
      padding: 8px 16px;
      background: #6c757d;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.85em;
      text-decoration: none;
      transition: all 0.2s ease;
      display: inline-block;
      line-height: 1.2;
    }
    
    .clear-btn:hover {
      background: #5a6268;
      color: white;
      text-decoration: none;
      transform: translateY(-1px);
    }
    
    /* Table styles - White theme */
    .books-table-container {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      overflow: hidden;
      margin-top: 20px;
      border: 1px solid #e9ecef;
    }
    
    .books-table {
      width: 100%;
      border-collapse: collapse;
      background: white;
    }
    
    .books-table th {
      background: #f8f9fa;
      color: #495057;
      padding: 22px 28px;
      text-align: left;
      font-weight: 600;
      font-size: 0.9em;
      border-bottom: 2px solid #e9ecef;
    }
    
    .books-table th:first-child {
      border-top-left-radius: 12px;
    }
    
    .books-table th:last-child {
      border-top-right-radius: 12px;
    }
    
    .books-table td {
      padding: 20px 28px;
      border-bottom: 1px solid #f1f3f4;
      vertical-align: middle;
    }
    
    .books-table tr:hover {
      background-color: #f8f9fa;
      transition: background-color 0.2s ease;
    }
    
    .books-table tr:last-child td {
      border-bottom: none;
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
    
    /* Genre tags */
    .genre-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 3px;
    }
    
    .genre-tag {
      background: #e9ecef;
      color: #495057;
      padding: 2px 6px;
      border-radius: 10px;
      font-size: 0.7em;
      white-space: nowrap;
    }
    
    /* Pagination styles */
    .pagination-container {
      margin-top: 25px;
      text-align: center;
    }
    
    .pagination-info {
      margin-bottom: 12px;
      color: #6c757d;
      font-size: 0.9em;
      font-weight: 500;
    }
    
    .pagination {
      display: inline-flex;
      gap: 6px;
      background: white;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      flex-wrap: wrap;
      justify-content: center;
      list-style: none;
      margin: 0;
      border: 1px solid #e9ecef;
    }
    
    .pagination .page-item {
      margin: 0;
    }
    
    .pagination .page-link {
      padding: 8px 12px;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      text-decoration: none;
      color: #495057;
      font-weight: 500;
      transition: all 0.2s ease;
      min-width: 40px;
      text-align: center;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: white;
      font-size: 0.9em;
    }
    
    .pagination .page-item.active .page-link {
      background: #667eea;
      color: white;
      border-color: #667eea;
    }
    
    .pagination .page-link:hover {
      background: #f8f9fa;
      border-color: #667eea;
      color: #667eea;
    }
    
    .pagination .page-item.disabled .page-link {
      color: #adb5bd;
      border-color: #e9ecef;
      cursor: not-allowed;
      opacity: 0.6;
      background: #f8f9fa;
    }
    
    .no-books {
      text-align: center;
      padding: 50px 20px;
      color: #6c757d;
      font-size: 1em;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      border: 1px solid #e9ecef;
    }
    
    .search-results-info {
      background: #e3f2fd;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #2196f3;
      color: #1976d2;
      font-size: 0.9em;
    }
    
    .search-results-info strong {
      color: #1565c0;
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
      .filter-section {
        grid-template-columns: 1fr;
      }
      
      .sort-section {
        flex-direction: column;
        gap: 8px;
      }
      
      .books-table {
        font-size: 0.85em;
      }
      
      .books-table th,
      .books-table td {
        padding: 8px 6px;
      }
      
      .books-table-container {
        padding: 15px;
      }
    }
  </style>
@endpush

@section('content')
  @if($isLoggedIn)
  <button id="toggle-sidebar" class="menu-button">☰</button>
    <div id="user-display" class="user-info">
      <span id="username-display">{{ $user->HoVaTen }}</span>
      <form method="POST" action="{{ route('logout') }}" class="user-logout-form" onsubmit="event.stopPropagation();">
        @csrf
        <button type="submit" class="user-logout-btn" onclick="event.stopPropagation();">Đăng Xuất</button>
      </form>
    </div>

  <!-- Hộp thông tin tài khoản -->
    <div id="account-info-box" style="display: none; position: fixed; top: 70px; right: 30px; padding: 20px; z-index: 1000; background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e9ecef;">
                      <p><strong>👤 Họ và tên:</strong> <span id="account-username-display">{{ $user->HoVaTen }}</span></p>
      <p><strong>🎭 Vai trò:</strong> <span id="account-role-display">{{ $userRole }}</span></p>
                      <p style="margin-top: 15px; text-align: center;">
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" style="background: linear-gradient(135deg, #ff6b6b, #ee5a52); border: none; color: white; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 14px; font-weight: 500; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease;" id="account-logout-link" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)'">🚪 Đăng xuất</button>
                    </form>
                </p>
  </div>

  <!-- TOÀN BỘ GIAO DIỆN CHÍNH -->
  <div class="container">
    <aside class="sidebar">
      <h2>MENU</h2>
      <nav>
        <ul>
          <li><a href="{{ route('home') }}">🏠 Trang chủ</a></li>
          <li><a href="{{ route('books.index') }}">📚 Quản lý sách</a></li>
          <li><a href="{{ route('genres.index') }}">📂 Quản lý thể loại</a></li>
          <li><a href="{{ route('authors.index') }}">👤 Quản lý tác giả</a></li>
          <li><a href="{{ route('publishers.index') }}">🏢 Quản lý nhà xuất bản</a></li>
          <li><a href="{{ route('readers.index') }}">📚 Quản lý độc giả</a></li>
          <li><a href="{{ route('reader-types.index') }}">👥 Quản lý loại độc giả</a></li>
            <li><a href="{{ route('borrow-records.index') }}">📖 Quản lý mượn sách</a></li>
                            <li><a href="{{ route('fine-payments.index') }}">💰 Quản lý phiếu phạt</a></li>
          <li><a href="{{ route('reports.index') }}">📊 Báo cáo thống kê</a></li>
            @if($userRole === 'Admin')
          <li><a href="{{ route('regulations.index') }}">⚙️ Quản lý quy định</a></li>
          <li><a href="{{ route('accounts.index') }}">👥 Quản lý tài khoản</a></li>
            @endif
            
        </ul>
      </nav>
    </aside>
  @else
    <!-- Giao diện cho khách không đăng nhập -->
    <div class="container">
  @endif

    <main class="main-content">
      <!-- HERO section -->
      <section class="hero">
        <div class="hero-content">
          <h1>THƯ VIỆN HIỆN ĐẠI</h1>
          <p>Khám phá thế giới tri thức với hàng ngàn cuốn sách chất lượng cao. Tìm kiếm và tra cứu sách dễ dàng!</p>

          <!-- TÌM KIẾM -->
          <form method="GET" action="{{ route('home') }}" class="search-box">
            <div class="search-input-wrapper">
              <input type="text" name="search" id="search-input" placeholder="Tìm kiếm sách, tác giả, thể loại..." value="{{ request('search') }}">
            </div>
            <div class="search-button-wrapper">
              <button type="submit" id="search-button">TÌM KIẾM</button>
            </div>
          </form>
        </div>
      </section>

      <!-- Bộ lọc tìm kiếm - Thu gọn -->
      <div class="book-search-container">
        <h2>🔍 Bộ lọc tìm kiếm</h2>
        
        <form method="GET" action="{{ route('home') }}" id="filter-form">
          @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
          @endif
          
          <div class="filter-section">
            <div class="filter-group">
              <label for="genre">📂 Thể loại</label>
              <select name="genre" id="genre">
                <option value="">Tất cả thể loại</option>
                @foreach($genres as $genre)
                  <option value="{{ $genre->MaTheLoai }}" {{ (string)request('genre') === (string)$genre->MaTheLoai ? 'selected' : '' }}>
                    {{ $genre->TenTheLoai }}
                  </option>
                @endforeach
              </select>
          </div>
          
            <div class="filter-group">
              <label for="author">👤 Tác giả</label>
              <select name="author" id="author">
                <option value="">Tất cả tác giả</option>
                @foreach($authors as $author)
                  <option value="{{ $author->MaTacGia }}" {{ (string)request('author') === (string)$author->MaTacGia ? 'selected' : '' }}>
                    {{ $author->TenTacGia }}
                  </option>
                @endforeach
              </select>

            </div>
            
            <div class="filter-group">
              <label for="publisher">🏢 Nhà xuất bản</label>
              <select name="publisher" id="publisher">
                <option value="">Tất cả nhà xuất bản</option>
                @foreach($publishers as $publisher)
                  <option value="{{ $publisher->MaNXB }}" {{ (string)request('publisher') === (string)$publisher->MaNXB ? 'selected' : '' }}>
                    {{ $publisher->TenNXB }}
                  </option>
                @endforeach
              </select>
          </div>

            <div class="filter-group">
              <label for="status">📊 Tình trạng</label>
              <select name="status" id="status">
                <option value="">Tất cả tình trạng</option>
                <option value="{{ \App\Models\CuonSach::TINH_TRANG_CO_SAN }}" {{ (string)request('status') === (string)\App\Models\CuonSach::TINH_TRANG_CO_SAN ? 'selected' : '' }}>Có sẵn</option>
                <option value="{{ \App\Models\CuonSach::TINH_TRANG_DANG_MUON }}" {{ (string)request('status') === (string)\App\Models\CuonSach::TINH_TRANG_DANG_MUON ? 'selected' : '' }}>Đang được mượn</option>
                <option value="{{ \App\Models\CuonSach::TINH_TRANG_HONG }}" {{ (string)request('status') === (string)\App\Models\CuonSach::TINH_TRANG_HONG ? 'selected' : '' }}>Hỏng</option>
                <option value="{{ \App\Models\CuonSach::TINH_TRANG_BI_MAT }}" {{ (string)request('status') === (string)\App\Models\CuonSach::TINH_TRANG_BI_MAT ? 'selected' : '' }}>Mất</option>
              </select>

            </div>
          </div>

          <div class="sort-section">
            <label for="sort">🔄 Sắp xếp:</label>
            <select name="sort" id="sort">
              <option value="TenSach" {{ request('sort') == 'TenSach' ? 'selected' : '' }}>Tên sách</option>
              <option value="MaSach" {{ request('sort') == 'MaSach' ? 'selected' : '' }}>Mã sách</option>
              <option value="NamXuatBan" {{ request('sort') == 'NamXuatBan' ? 'selected' : '' }}>Năm xuất bản</option>
              <option value="TriGia" {{ request('sort') == 'TriGia' ? 'selected' : '' }}>Giá trị</option>
            </select>
            
            <select name="order" id="order">
              <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Tăng dần</option>
              <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Giảm dần</option>
            </select>
            
            <button type="submit" class="filter-btn">
              ✅ Áp dụng
            </button>
            
            <a href="{{ route('home') }}" class="clear-btn">
              🗑️ Xóa bộ lọc
            </a>
          </div>
        </form>
      </div>

      <!-- Thông tin kết quả tìm kiếm -->
      @if(request('search') || request('genre') || request('author') || request('publisher') || request('status'))
        <div class="search-results-info">
          <strong>🔍 Kết quả tìm kiếm:</strong> 
          Tìm thấy {{ $books->total() }} cuốn sách
          @if(request('search'))
            cho từ khóa "{{ request('search') }}"
          @endif
          @if(request('genre'))
            trong thể loại "{{ $genres->find(request('genre'))->TenTheLoai ?? '' }}"
          @endif
          @if(request('author'))
            của tác giả "{{ $authors->find(request('author'))->TenTacGia ?? '' }}"
          @endif
          @if(request('publisher'))
            từ nhà xuất bản "{{ $publishers->find(request('publisher'))->TenNXB ?? '' }}"
          @endif
        </div>
      @endif

      <!-- Danh sách sách dạng bảng -->
      <div class="books-table-container">
        @if($books->count() > 0)
          <table class="books-table">
            <thead>
              <tr>
                <th>Mã sách</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Thể loại</th>
                <th>Nhà xuất bản</th>
                <th>Năm XB</th>
                <th>Giá trị</th>
                <th>Tình trạng</th>
              </tr>
            </thead>
            <tbody>
              @foreach($books as $book)
                @php
                  $tenDauSach = optional($book->dauSach)->TenDauSach;

                  $authorNames = '';
                  if ($book->dauSach && $book->dauSach->tacGias && $book->dauSach->tacGias->count() > 0) {
                      $authorNames = $book->dauSach->tacGias->pluck('TenTacGia')->unique()->implode(', ');
                  }

                  $theLoaiName = optional(optional($book->dauSach)->theLoai)->TenTheLoai;
                  $nxbName = optional($book->nhaXuatBan)->TenNXB;

                  // Tình trạng hiển thị dựa trên CUONSACH (sẽ lazy-load nếu controller chưa eager load)
                  $statusText = 'Chưa có thông tin';
                  $statusClass = '';
                  $statuses = $book->cuonSachs ? $book->cuonSachs->pluck('TinhTrang') : collect();

                  $availableCount = $statuses->filter(fn($s) => (int)$s === \App\Models\CuonSach::TINH_TRANG_CO_SAN)->count();
                  $borrowedCount  = $statuses->filter(fn($s) => (int)$s === \App\Models\CuonSach::TINH_TRANG_DANG_MUON)->count();
                  $damagedCount   = $statuses->filter(fn($s) => (int)$s === \App\Models\CuonSach::TINH_TRANG_HONG)->count();
                  $lostCount      = $statuses->filter(fn($s) => (int)$s === \App\Models\CuonSach::TINH_TRANG_BI_MAT)->count();

                  if ($availableCount > 0) {
                      $statusClass = 'status-available';
                      $statusText  = 'Có sẵn' . ' (' . $availableCount . ')';
                      if ($borrowedCount > 0) {
                          $statusText .= ' • Đang mượn (' . $borrowedCount . ')';
                      }
                      if ($damagedCount > 0) {
                          $statusText .= ' • Hỏng (' . $damagedCount . ')';
                      }
                      if ($lostCount > 0) {
                          $statusText .= ' • Mất (' . $lostCount . ')';
                      }
                  } elseif ($borrowedCount > 0) {
                      $statusClass = 'status-borrowed';
                      $statusText  = 'Đang được mượn (' . $borrowedCount . ')';
                  } elseif ($damagedCount > 0) {
                      $statusClass = 'status-damaged';
                      $statusText  = 'Hỏng (' . $damagedCount . ')';
                  } elseif ($lostCount > 0) {
                      $statusClass = 'status-lost';
                      $statusText  = 'Mất (' . $lostCount . ')';
                  }
@endphp

                <tr>
                  <td><strong>{{ $book->MaSach }}</strong></td>
                  <td>{{ $tenDauSach }}</td>
                  <td>{{ $authorNames !== '' ? $authorNames : 'Chưa có thông tin' }}</td>
                  <td>
                    @if($theLoaiName)
                      <div class="genre-tags">
                        <span class="genre-tag">{{ $theLoaiName }}</span>
                      </div>
                    @else
                      <span style="color: #999;">Chưa có thông tin</span>
                    @endif
                  </td>
                  <td>{{ $nxbName ?? 'Chưa có thông tin' }}</td>
                  <td>{{ $book->NamXuatBan }}</td>
                  <td>{{ number_format($book->TriGia ?? 0, 0, ',', '.') }} VNĐ</td>
                  <td>
                    @if($statusClass)
                      <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                    @else
                      <span style="color: #999;">{{ $statusText }}</span>
                    @endif
                  </td>
                </tr>
              @endforeach

            </tbody>
          </table>
        @else
          <div class="no-books">
            <h3>📚 Không tìm thấy sách nào</h3>
            <p>Vui lòng thử lại với từ khóa khác hoặc xóa bộ lọc để xem tất cả sách.</p>
          </div>
        @endif
          </div>
        
      <!-- Phân trang -->
      @if($books->hasPages())
        <div class="pagination-container">
          <div class="pagination-info">
            @if($books->count() > 0)
              Hiển thị {{ $books->firstItem() }} - {{ $books->lastItem() }} trong tổng số {{ $books->total() }} cuốn sách
            @else
              Không có sách nào để hiển thị
            @endif
          </div>
          {{ $books->links('vendor.pagination.bootstrap-4') }}
        </div>
      @endif
    </main>
  </div>
  @if(!$isLoggedIn)
    </div>
  @endif
@endsection

@section('scripts')
<script>
  @if($isLoggedIn)
  const toggleBtn = document.getElementById('toggle-sidebar');
  const sidebar = document.querySelector('.sidebar');
  const container = document.querySelector('.container');

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    container.classList.toggle('sidebar-collapsed');
  });

  // Toggle account info box
  document.getElementById('user-display').addEventListener('click', function() {
    const accountBox = document.getElementById('account-info-box');
    accountBox.style.display = accountBox.style.display === 'none' ? 'block' : 'none';
  });
  @endif

  // Auto-submit form when filters change
  document.getElementById('genre').addEventListener('change', function() {
    document.getElementById('filter-form').submit();
  });
  
  document.getElementById('author').addEventListener('change', function() {
    document.getElementById('filter-form').submit();
  });
  
  document.getElementById('publisher').addEventListener('change', function() {
    document.getElementById('filter-form').submit();
  });

  document.getElementById('status').addEventListener('change', function() {
    document.getElementById('filter-form').submit();
  });
</script>
@endsection
