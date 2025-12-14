<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sách</th>
            <th>Tác giả</th>
            <th>Thể loại</th>
            <th>Nhà xuất bản</th>
            <th>Năm xuất bản</th>
            <th>Ngày thêm</th>
            <th>Tình trạng</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($danhSachSach as $sach)
            <tr>
                <td>{{ $sach->id }}</td>
                <td><strong>{{ $sach->TenSach }}</strong></td>
                <td>{{ $sach->tacGias->pluck('TenTacGia')->join(', ') ?: 'N/A' }}</td>
                <td>
                    @forelse($sach->theLoais as $theLoai)
                        <span class="genre-tag" style="cursor: default; background: #e2e8f0;">{{ $theLoai->TenTheLoai }}</span>
                    @empty
                        <span class="text-muted">N/A</span>
                    @endforelse
                </td>
                <td>{{ $sach->nhaXuatBans->pluck('TenNXB')->join(', ') ?: 'N/A' }}</td>
                <td>{{ $sach->NamXuatBan }}</td>
                <td>{{ \Carbon\Carbon::parse($sach->NgayNhap)->format('d/m/Y') }}</td>
                <td>
                    <span class="status-badge {{ $sach->TinhTrang === 'Có sẵn' ? 'status-available' : ($sach->TinhTrang === 'Đang được mượn' ? 'status-borrowed' : 'status-maintenance') }}">
                        {{ $sach->TinhTrang }}
                    </span>
                </td>
                <td class="actions">
                    <div class="action-buttons">
                        <button class="btn edit-btn" onclick="openEditModal('{{ $sach->id }}')">✏️ Sửa</button>
                        <form action="{{ route('books.destroy', $sach->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn delete-btn">🗑️ Xóa</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">
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

{{-- Pagination --}}
<div style="margin-top: 20px;">
    {{ $danhSachSach->withQueryString()->links() }}
</div>