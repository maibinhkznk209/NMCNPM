<table>
    <thead>
        <tr>
            <th>Mã sách</th>
            <th>Tên đầu sách</th>
            <th>Thể loại</th>
            <th>Nhà xuất bản</th>
            <th>Năm xuất bản</th>
            <th>Trị giá</th>
            <th>Số lượng</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($danhSachSach as $sach)
            <tr>
                <td>{{ $sach->MaSach ?? $sach->MaSach }}</td>
                <td><strong>{{ $sach->TenDauSach ?? $sach->TenSach ?? '' }}</strong></td>
                <td>{{ $sach->TenTheLoai ?? '' }}</td>
                <td>{{ $sach->TenNXB ?? '' }}</td>
                <td>{{ $sach->NamXuatBan ?? '' }}</td>
                <td>{{ $sach->TriGia ?? '' }}</td>
                <td>{{ $sach->SoLuong ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
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

<div style="margin-top: 20px;">
    {{ $danhSachSach->withQueryString()->links() }}
</div>
