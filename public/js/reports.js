// Reports Page JavaScript Functions

// Set default values when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Set default date to today
    document.getElementById('overdueDate').value = new Date(
        Date.now() - new Date().getTimezoneOffset() * 60000
    ).toISOString().split('T')[0];
    
    // Set default month and year
    const now = new Date();
    document.getElementById('genreMonth').value = now.getMonth() + 1;
    
    const yearSelect = document.getElementById('genreYear');
    const currentYear = String(now.getFullYear());
    if (![...yearSelect.options].some(opt => opt.value === currentYear)) {
        const opt = document.createElement('option');
        opt.value = currentYear;
        opt.textContent = currentYear;
        yearSelect.appendChild(opt);
    }
    yearSelect.value = currentYear;

    // Initialize states - hide loading, show empty states
    document.getElementById('genreLoading').style.display = 'none';
    document.getElementById('overdueLoading').style.display = 'none';
    document.getElementById('genreEmpty').style.display = 'block';
    document.getElementById('overdueEmpty').style.display = 'block';
    document.getElementById('genreResults').style.display = 'none';
    document.getElementById('overdueResults').style.display = 'none';
    document.getElementById('exportGenreBtn').disabled = true;
    document.getElementById('exportOverdueBtn').disabled = true;
});

// Genre Statistics Report
document.getElementById('genreReportForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const month = document.getElementById('genreMonth').value;
    const year = document.getElementById('genreYear').value;
    
    if (!month || !year) {
        alert('Vui lòng chọn tháng và năm');
        return;
    }

    // Show loading
    document.getElementById('genreLoading').style.display = 'block';
    document.getElementById('genreResults').style.display = 'none';
    document.getElementById('genreEmpty').style.display = 'none';
    document.getElementById('exportGenreBtn').disabled = true;

    try {
        const response = await fetch(`/api/reports/genre-statistics?month=${month}&year=${year}`);
        const data = await response.json();

        if (data.success) {
            displayGenreResults(data.data);
            document.getElementById('exportGenreBtn').disabled = false;
        } else {
            alert('Lỗi: ' + data.message);
            document.getElementById('genreEmpty').style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi tạo báo cáo');
        document.getElementById('genreEmpty').style.display = 'block';
    } finally {
        document.getElementById('genreLoading').style.display = 'none';
    }
});

function displayGenreResults(data) {
    document.getElementById('totalBorrows').textContent = data.total_borrows.toLocaleString();
    document.getElementById('totalGenres').textContent = data.genres.length;
    document.getElementById('totalBorrowsFooter').textContent = data.total_borrows.toLocaleString();

    const tbody = document.getElementById('genreTableBody');
    tbody.innerHTML = '';

    if (data.genres.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="5" class="text-center text-muted py-4">Không có dữ liệu trong tháng này</td>';
        tbody.appendChild(row);
    } else {
        data.genres.forEach((genre, index) => {
            const row = document.createElement('tr');
            const booksList = genre.books.length > 5 
                ? genre.books.slice(0, 5).join(', ') + '...' 
                : genre.books.join(', ');
            
            row.innerHTML = `
                <td class="text-center">${index + 1}</td>
                <td><strong>${genre.name}</strong></td>
                <td class="text-center">${genre.borrow_count.toLocaleString()}</td>
                <td class="text-center">${genre.percentage.toFixed(1)}%</td>
                <td><small title="${genre.books.join(', ')}">${booksList}</small></td>
            `;
            tbody.appendChild(row);
        });
    }

    document.getElementById('genreResults').style.display = 'block';
}

// Overdue Books Report
document.getElementById('overdueReportForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const date = document.getElementById('overdueDate').value;
    
    if (!date) {
        alert('Vui lòng chọn ngày');
        return;
    }

    // Show loading
    document.getElementById('overdueLoading').style.display = 'block';
    document.getElementById('overdueResults').style.display = 'none';
    document.getElementById('overdueEmpty').style.display = 'none';
    document.getElementById('exportOverdueBtn').disabled = true;

    try {
        const apiUrl = `/api/reports/overdue-books?date=${date}`;
        
        const response = await fetch(apiUrl);
        const data = await response.json();

        if (data.success) {
            displayOverdueResults(data.data);
            document.getElementById('exportOverdueBtn').disabled = false;
        } else {
            alert('Lỗi: ' + data.message);
            document.getElementById('overdueEmpty').style.display = 'block';
        }
    } catch (error) {
        alert('Có lỗi xảy ra khi tạo báo cáo: ' + error.message);
        document.getElementById('overdueEmpty').style.display = 'block';
    } finally {
        document.getElementById('overdueLoading').style.display = 'none';
    }
});

function displayOverdueResults(data) {
    const totalOverdueUnreturned = Number(
        data.total_overdue_unreturned ?? data.total_overdue ?? 0
    );
    const totalOverdueBooks = Number(
        data.total_overdue ?? (Array.isArray(data.overdue_books) ? data.overdue_books.length : 0)
    );
    const totalFine = Number(data.total_fine || 0);
    document.getElementById('totalOverdue').textContent = totalOverdueUnreturned.toLocaleString();
    document.getElementById('totalFine').textContent = totalFine.toLocaleString() + 'đ';
    document.getElementById('reportDate').textContent = new Date(data.date).toLocaleDateString('vi-VN');
    document.getElementById('totalOverdueFooter').textContent = totalOverdueBooks.toLocaleString();
    document.getElementById('totalFineFooter').textContent = totalFine.toLocaleString() + 'đ';

    const tbody = document.getElementById('overdueTableBody');
    tbody.innerHTML = '';

    if (!data.overdue_books || data.overdue_books.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="7" class="text-center text-muted py-4">Không có sách trả trễ trong ngày này</td>';
        tbody.appendChild(row);
    } else {
        data.overdue_books.forEach((book, index) => {
            const row = document.createElement('tr');
            
            const fineAmount = Number(book.fine_amount || 0);
            row.innerHTML = `
                <td class="text-center">${index + 1}</td>
                <td><strong>${book.book_title}</strong></td>
                <td>${book.reader_name}</td>
                <td class="text-center">${new Date(book.borrow_date).toLocaleDateString('vi-VN')}</td>
                <td class="text-center"><span class="badge bg-danger">${book.overdue_days} ngày</span></td>
                <td><span class="badge ${book.status.includes('Chưa trả') ? 'bg-danger' : 'bg-warning'}">${book.status}</span></td>
                <td class="text-center">${fineAmount.toLocaleString()}đ</td>
            `;
            tbody.appendChild(row);
        });
    }

    document.getElementById('overdueResults').style.display = 'block';
}

// Export functions
function exportGenreReport() {
    const month = document.getElementById('genreMonth').value;
    const year = document.getElementById('genreYear').value;
    
    if (!month || !year) {
        alert('Vui lòng chọn tháng và năm để xuất báo cáo');
        return;
    }
    
    window.open(`/api/reports/export-genre-statistics?month=${month}&year=${year}`);
}

function exportOverdueReport() {
    const date = document.getElementById('overdueDate').value;
    
    if (!date) {
        alert('Vui lòng chọn ngày để xuất báo cáo');
        return;
    }
    
    window.open(`/api/reports/export-overdue-books?date=${date}`);
}
