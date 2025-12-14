@extends('layouts.app')

@section('title', 'Báo Cáo Thống Kê - Hệ Thống Quản Lý Thư Viện')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/reports.css') }}">
@endpush

@section('content')
    <div class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0"><i class="fas fa-chart-bar me-3"></i>Báo Cáo Thống Kê</h1>
                    <p class="mb-0 mt-2">Hệ thống quản lý thư viện - Thống kê và báo cáo</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="/" class="btn btn-light">
                        <i class="fas fa-home me-2"></i>Trang chủ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4 justify-content-center" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="genre-tab" data-bs-toggle="pill" data-bs-target="#genre-report" type="button" role="tab">
                    <i class="fas fa-book me-2"></i>Thống Kê Theo Thể Loại
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="overdue-tab" data-bs-toggle="pill" data-bs-target="#overdue-report" type="button" role="tab">
                    <i class="fas fa-clock me-2"></i>Báo Cáo Trả Trễ
                </button>
            </li>
        </ul>

        <div class="tab-content" id="reportTabsContent">
            <!-- Genre Statistics Report -->
            <div class="tab-pane fade show active" id="genre-report" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Báo Cáo Thống Kê Tình Hình Mượn Sách Theo Thể Loại</h5>
                    </div>
                    <div class="card-body">
                        <form id="genreReportForm" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="genreMonth" class="form-label">Tháng</label>
                                <select class="form-select" id="genreMonth" required>
                                    <option value="">Chọn tháng</option>
                                    <option value="1">Tháng 1</option>
                                    <option value="2">Tháng 2</option>
                                    <option value="3">Tháng 3</option>
                                    <option value="4">Tháng 4</option>
                                    <option value="5">Tháng 5</option>
                                    <option value="6">Tháng 6</option>
                                    <option value="7">Tháng 7</option>
                                    <option value="8">Tháng 8</option>
                                    <option value="9">Tháng 9</option>
                                    <option value="10">Tháng 10</option>
                                    <option value="11">Tháng 11</option>
                                    <option value="12">Tháng 12</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="genreYear" class="form-label">Năm</label>
                                <select class="form-select" id="genreYear" required>
                                    <option value="">Chọn năm</option>
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Tạo Báo Cáo
                                    </button>
                                    <button type="button" class="btn btn-success" id="exportGenreBtn" onclick="exportGenreReport()">
                                        📊 Xuất Excel
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div id="genreLoading" class="loading" style="display: none;">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="mt-2">Đang tạo báo cáo...</p>
                        </div>

                        <div id="genreResults" style="display: none;">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="statistics-card">
                                        <i class="fas fa-book-open icon-large"></i>
                                        <h3 id="totalBorrows">0</h3>
                                        <p class="mb-0">Tổng số lượt mượn</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="statistics-card">
                                        <i class="fas fa-list icon-large"></i>
                                        <h3 id="totalGenres">0</h3>
                                        <p class="mb-0">Số thể loại có sách mượn</p>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">STT</th>
                                            <th style="width: 20%;">Tên Thể Loại</th>
                                            <th style="width: 12%;">Số Lượt Mượn</th>
                                            <th style="width: 10%;">Tỉ Lệ (%)</th>
                                            <th style="width: 50%;">Danh Sách Sách</th>
                                        </tr>
                                    </thead>
                                    <tbody id="genreTableBody">
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info fw-bold">
                                            <td colspan="2" class="text-center">Tổng cộng</td>
                                            <td class="text-center" id="totalBorrowsFooter">0</td>
                                            <td class="text-center">100%</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div id="genreEmpty" class="empty-state">
                            <i class="fas fa-chart-pie"></i>
                            <h4>Chưa có báo cáo</h4>
                            <p>Chọn tháng và năm để tạo báo cáo thống kê theo thể loại</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue Books Report -->
            <div class="tab-pane fade" id="overdue-report" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Báo Cáo Thống Kê Sách Trả Trễ</h5>
                    </div>
                    <div class="card-body">
                        <form id="overdueReportForm" class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="overdueDate" class="form-label">Ngày Thống Kê</label>
                                <input type="date" class="form-control" id="overdueDate" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Tạo Báo Cáo
                                    </button>
                                    <button type="button" class="btn btn-success" id="exportOverdueBtn" onclick="exportOverdueReport()">
                                        📊 Xuất Excel
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div id="overdueLoading" class="loading">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="mt-2">Đang tạo báo cáo...</p>
                        </div>

                        <div id="overdueResults" style="display: none;">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="statistics-card">
                                        <i class="fas fa-exclamation-circle icon-large"></i>
                                        <h3 id="totalOverdue">0</h3>
                                        <p class="mb-0">Số sách trả trễ</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="statistics-card">
                                        <i class="fas fa-money-bill icon-large"></i>
                                        <h3 id="totalFine">0đ</h3>
                                        <p class="mb-0">Tổng tiền phạt</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="statistics-card">
                                        <i class="fas fa-calendar icon-large"></i>
                                        <h3 id="reportDate"></h3>
                                        <p class="mb-0">Ngày báo cáo</p>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 6%;">STT</th>
                                            <th style="width: 25%;">Tên Sách</th>
                                            <th style="width: 18%;">Độc Giả</th>
                                            <th style="width: 12%;">Ngày Mượn</th>
                                            <th style="width: 12%;">Số Ngày Trễ</th>
                                            <th style="width: 15%;">Trạng Thái</th>
                                            <th style="width: 12%;">Tiền Phạt</th>
                                        </tr>
                                    </thead>
                                    <tbody id="overdueTableBody">
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-warning fw-bold">
                                            <td colspan="4" class="text-center">Tổng cộng</td>
                                            <td class="text-center" id="totalOverdueFooter">0</td>
                                            <td></td>
                                            <td class="text-center" id="totalFineFooter">0đ</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div id="overdueEmpty" class="empty-state">
                            <i class="fas fa-clock"></i>
                            <h4>Chưa có báo cáo</h4>
                            <p>Chọn ngày để tạo báo cáo thống kê sách trả trễ</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reports.js') }}"></script>
@endsection
