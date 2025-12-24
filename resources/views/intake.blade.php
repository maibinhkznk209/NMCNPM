@extends('layouts.app')

@section('title', 'Tiếp nhận sách')

@push('styles')
<style>
  .container { max-width: 980px; margin: 0 auto; padding: 16px; }
  .header { display:flex; justify-content:space-between; align-items:flex-end; gap:12px; margin-bottom: 16px; }
  .header h1 { margin:0; font-size: 22px; }
  .header p { margin:0; color:#666; }

  .card { background:white; border:1px solid #eee; border-radius:12px; overflow:hidden; margin-bottom:14px; }
  .card-hd { padding:12px 14px; background:#f7fafc; border-bottom:1px solid #eee; font-weight:700; }
  .card-bd { padding:14px; }

  .grid { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
  .form-group { margin-bottom:12px; }
  label { display:block; margin-bottom:6px; font-weight:600; font-size:13px; }
  input, select { width:100%; padding:9px 10px; border:1px solid #ddd; border-radius:10px; }
  .hint { color:#718096; font-size:12px; margin-top:6px; }

  .btn { padding:9px 12px; border-radius:10px; border:none; cursor:pointer; }
  .btn-primary { background:#2b6cb0; color:white; }
  .btn-secondary { background:#edf2f7; color:#2d3748; }
  .toolbar { display:flex; justify-content:flex-end; gap:10px; margin-top:10px; }

  .toast { padding:10px 12px; border-radius:10px; margin: 10px 0; }
  .toast.success { background:#c6f6d5; color:#22543d; }
  .toast.error { background:#fed7d7; color:#742a2a; }
</style>
@endpush

@section('content')
<div class="container">

  <div class="header">
    <div>
      <h1>TIẾP NHẬN SÁCH</h1>
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
      <a class="btn btn-secondary" href="{{ route('home') }}">Trang chủ</a>
      <a class="btn btn-secondary" href="{{ route('books.index') }}">Tra cứu cuốn sách</a>
    </div>
  </div>

  @if (session('success'))
    <div class="toast success">{{ session('success') }}</div>
  @endif
  @if (session('error'))
    <div class="toast error">{{ session('error') }}</div>
  @endif

  <div class="card">
    <div class="card-hd">Phiếu Nhận Đầu Sách</div>
    <div class="card-bd">
      <form method="POST" action="{{ route('intake.dausach.store') }}">
        @csrf

        <div class="grid">
          <div class="form-group">
            <label for="TenDauSach">Tên đầu sách</label>
            <input type="text" id="TenDauSach" name="TenDauSach" required>
          </div>

          <div class="form-group">
            <label for="MaTheLoai">Thể loại</label>
            <select id="MaTheLoai" name="MaTheLoai" required>
              <option value="">-- Chọn thể loại --</option>
              @foreach($theLoais as $tl)
                <option value="{{ $tl->MaTheLoai }}">{{ $tl->TenTheLoai }}</option>
              @endforeach
            </select>
           
          </div>
        </div>

        <div class="form-group">
          <label for="MaTacGia">Tác giả (có thể chọn nhiều)</label>
          <select id="MaTacGia" name="MaTacGia[]" multiple required>
            @foreach($tacGias as $tg)
              <option value="{{ $tg->MaTacGia }}">{{ $tg->TenTacGia }}</option>
            @endforeach
          </select>
          <div class="hint">Giữ Ctrl/Cmd để chọn nhiều tác giả.</div>
        </div>

        <div class="form-group">
          <label for="NgayNhapDauSach">Ngày nhập</label>
          <input type="date" id="NgayNhapDauSach" name="NgayNhap"
                 value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="toolbar">
          <button type="submit" class="btn btn-primary">Tạo đầu sách</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-hd">Phiếu Nhận Sách</div>
    <div class="card-bd">
      <form method="POST" action="{{ route('intake.sach.store') }}">
        @csrf

        <div class="grid">
          <div class="form-group">
            <label for="MaDauSach">Đầu sách</label>
            <select id="MaDauSach" name="MaDauSach" required>
              <option value="">-- Chọn đầu sách --</option>
              @foreach($dauSachs as $ds)
                <option value="{{ $ds->MaDauSach }}">{{ $ds->TenDauSach }}</option>
              @endforeach
            </select>
          </div>

          <div class="form-group">
            <label for="MaNXB">Nhà xuất bản</label>
            <select id="MaNXB" name="MaNXB" required>
              <option value="">-- Chọn NXB --</option>
              @foreach($nhaXuatBans as $nxb)
                <option value="{{ $nxb->MaNXB }}">{{ $nxb->TenNXB }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="grid">
          <div class="form-group">
            <label for="NamXuatBan">Năm xuất bản</label>
            <input type="number" id="NamXuatBan" name="NamXuatBan"
                   min="{{ date('Y') - 8 }}" max="{{ date('Y') }}" required>
            <div class="hint">Chỉ nhận sách xuất bản trong vòng số năm theo quy định.</div>
          </div>

          <div class="form-group">
            <label for="TriGia">Trị giá</label>
            <input type="number" id="TriGia" name="TriGia" min="0" step="0.01" required>
          </div>
        </div>

        <div class="grid">
          <div class="form-group">
            <label for="SoLuong">Số lượng cuốn</label>
            <input type="number" id="SoLuong" name="SoLuong" min="1" required>
            <div class="hint">Hệ thống sẽ tạo tương ứng các bản ghi CUONSACH theo số lượng.</div>
          </div>

          <div class="form-group">
            <label for="NgayNhapSach">Ngày nhập</label>
            <input type="date" id="NgayNhapSach" name="NgayNhap"
                   value="{{ date('Y-m-d') }}" required>
          </div>
        </div>

        <div class="toolbar">
          <button type="submit" class="btn btn-primary">Tiếp nhận sách</button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection
