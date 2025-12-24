@extends('layouts.app')

@section('title', 'BM6 + BM10 - Tra cứu & cập nhật tình trạng cuốn sách')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/books.css') }}">
<style>
  .container { max-width: 1200px; margin: 0 auto; padding: 16px; }
  .header { display:flex; justify-content:space-between; align-items:flex-end; gap:12px; margin-bottom: 14px; }
  .header h1 { margin:0; font-size: 22px; }
  .header p { margin:0; color:#666; }

  .toolbar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom: 12px; }
  .toolbar input, .toolbar select { padding:8px 10px; border:1px solid #ddd; border-radius:8px; }
  .btn { padding:8px 12px; border-radius:8px; border: none; cursor:pointer; font-weight: 600; }
  .btn-primary { background:#2b6cb0; color:white; }
  .btn-secondary { background:#edf2f7; color:#2d3748; }
  .btn-outline { background:white; border:1px solid #ddd; }

  table { width:100%; border-collapse:collapse; background:white; border-radius:10px; overflow:hidden; }
  th, td { padding:10px 8px; border-bottom:1px solid #eee; vertical-align:middle; }
  th { background:#f7fafc; text-align:left; font-size:12px; color:#4a5568; }

  .badge { display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; border:1px solid #e2e8f0; background:#f7fafc; }
  .toast { padding:10px 12px; border-radius:10px; margin: 10px 0; }
  .toast.success { background:#c6f6d5; color:#22543d; }
  .toast.error { background:#fed7d7; color:#742a2a; }

  /* modal */
  .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; }
  .modal-content { background:white; width:520px; max-width: calc(100% - 24px); margin: 6% auto; border-radius:12px; overflow:hidden; }
  .modal-header { padding:14px 16px; background:#2b6cb0; color:white; display:flex; justify-content:space-between; align-items:center; }
  .modal-body { padding:16px; }
  .modal-footer { padding:12px 16px; display:flex; justify-content:flex-end; gap:10px; background:#f7fafc; }
  .close { cursor:pointer; font-size:22px; line-height:1; }

  .form-group { margin-bottom:12px; }
  .form-group label { display:block; margin-bottom:6px; font-weight:600; font-size:13px; }
  .form-group input, .form-group select { width:100%; padding:9px 10px; border:1px solid #ddd; border-radius:10px; }
</style>
@endpush

@section('content')
<div class="container">

  <div class="header">
    <div>
      <h1>BM6 — Tra cứu cuốn sách</h1>
      <p>BM10 — Cập nhật tình trạng cuốn sách</p>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <a class="btn btn-secondary" href="{{ route('home') }}">Trang chủ</a>
      {{-- File BM4+BM5 bạn sẽ thêm route sau --}}
      <a class="btn btn-outline" href="{{ route('intake.index') }}">Tiếp nhận (BM4+BM5)</a>
    </div>
  </div>

  @if (session('success'))
    <div class="toast success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="toast error">{{ session('error') }}</div>
  @endif

  {{-- BM6: Tra cứu --}}
  <form class="toolbar" method="GET" action="{{ route('books.index') }}">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Tìm theo mã cuốn/mã sách/tên đầu sách/tác giả..." />

    {{-- nếu controller bạn có lọc tình trạng theo chuỗi --}}
    <select name="tinhtrang">
      <option value="">-- Tất cả tình trạng --</option>
      <option value="Sẵn có" {{ request('tinhtrang')==='Sẵn có' ? 'selected' : '' }}>Sẵn có</option>
      <option value="Đã cho mượn" {{ request('tinhtrang')==='Đã cho mượn' ? 'selected' : '' }}>Đã cho mượn</option>
      <option value="Hỏng" {{ request('tinhtrang')==='Hỏng' ? 'selected' : '' }}>Hỏng</option>
      <option value="Mất" {{ request('tinhtrang')==='Mất' ? 'selected' : '' }}>Mất</option>
    </select>

    <button class="btn btn-primary" type="submit">Tra cứu</button>
    <a class="btn btn-secondary" href="{{ route('books.index') }}">Xóa lọc</a>
  </form>

  <table>
    <thead>
      <tr>
        <th style="width:60px;">STT</th>
        <th style="width:140px;">Mã cuốn</th>
        <th style="width:120px;">Mã sách</th>
        <th>Tên đầu sách</th>
        <th>Thể loại</th>
        <th>Tác giả</th>
        <th style="width:140px;">Tình trạng</th>
        <th style="width:160px;">BM10</th>
      </tr>
    </thead>

    <tbody>
      @forelse($danhSachSach as $i => $cs)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td><strong>{{ $cs->MaCuonSach }}</strong></td>
          <td>{{ $cs->MaSach }}</td>
          <td>{{ $cs->TenDauSach ?? '-' }}</td>
          <td>{{ $cs->TenTheLoai ?? '-' }}</td>
          <td>{{ $cs->TenTacGia ?? '-' }}</td>
          <td><span class="badge">{{ $cs->TinhTrang ?? '-' }}</span></td>
          <td>
            <button type="button" class="btn btn-primary"
              onclick="openStatusModal(
                '{{ $cs->MaCuonSach }}',
                @json($cs->TenDauSach ?? ''),
                @json($cs->TinhTrang ?? '')
              )">
              Cập nhật tình trạng
            </button>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" style="text-align:center; padding:18px;">Không có dữ liệu.</td></tr>
      @endforelse
    </tbody>
  </table>

</div>

{{-- Modal BM10 --}}
<div id="statusModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <strong>BM10 — Cập nhật tình trạng cuốn sách</strong>
      <span class="close" onclick="closeStatusModal()">&times;</span>
    </div>

    <form id="statusForm" method="POST">
      @csrf
      <div class="modal-body">
        <div class="form-group">
          <label>Mã cuốn sách</label>
          <input type="text" id="bm10_maCuon" name="MaCuonSach" readonly>
        </div>

        <div class="form-group">
          <label>Tên sách</label>
          <input type="text" id="bm10_tenSach" readonly>
        </div>

        <div class="form-group">
          <label>Tình trạng</label>
          <select id="bm10_tinhTrang" name="TinhTrang" required>
            <option value="">-- Chọn tình trạng --</option>
            <option value="Sẵn có">Sẵn có</option>
            <option value="Đã cho mượn">Đã cho mượn</option>
            <option value="Hỏng">Hỏng</option>
            <option value="Mất">Mất</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Hủy</button>
        <button type="submit" class="btn btn-primary">Lưu</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function openStatusModal(maCuon, tenSach, tinhTrang) {
    document.getElementById('bm10_maCuon').value = maCuon || '';
    document.getElementById('bm10_tenSach').value = tenSach || '';
    document.getElementById('bm10_tinhTrang').value = tinhTrang || '';

    // đúng route của bạn: /books/cuon-sach/{maCuonSach}/tinh-trang (POST)
    document.getElementById('statusForm').action =
      "{{ url('/books/cuon-sach') }}/" + encodeURIComponent(maCuon) + "/tinh-trang";

    document.getElementById('statusModal').style.display = 'block';
  }

  function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
  }

  window.addEventListener('click', function(e) {
    const modal = document.getElementById('statusModal');
    if (e.target === modal) closeStatusModal();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeStatusModal();
  });
</script>
@endpush
