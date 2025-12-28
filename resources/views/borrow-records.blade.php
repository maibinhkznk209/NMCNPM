@extends('layouts.app')

@section('title', 'Quản lý mượn sách - Hệ thống thư viện')

@push('styles')
<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      color: #2d3748;
      line-height: 1.6;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
      animation: fadeInDown 1s ease-out;
    }

    .header h1 {
      color: #fff;
      font-size: 2.5rem;
      margin-bottom: 10px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .header p {
      color: rgba(255, 255, 255, 0.9);
      font-size: 1.1rem;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
      animation: fadeInUp 1s ease-out 0.2s both;
    }

    .stat-card {
      background: rgba(255, 255, 255, 0.95);
      padding: 20px;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .stat-label {
      color: #718096;
      font-weight: 500;
    }

    .stat-card.total .stat-number { color: #4299e1; }
    .stat-card.active .stat-number { color: #48bb78; }
    .stat-card.overdue .stat-number { color: #e53e3e; }
    .stat-card.due-soon .stat-number { color: #ed8936; }
    .stat-card.returned .stat-number { color: #48bb78; }

    .controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      animation: fadeInUp 1s ease-out 0.4s both;
      flex-wrap: wrap;
      gap: 15px;
    }

    .search-filter-group {
      display: flex;
      gap: 15px;
      align-items: center;
      flex: 1;
      flex-wrap: wrap;
    }

    .search-box {
      position: relative;
      flex: 1;
      max-width: 400px;
      min-width: 250px;
    }

    .search-box input {
      width: 100%;
      padding: 12px 45px 12px 15px;
      border: none;
      border-radius: 25px;
      font-size: 16px;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .search-box input:focus {
      outline: none;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      background: #fff;
    }

    .search-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #a0aec0;
    }

    .filter-select {
      padding: 10px 15px;
      border: none;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.95);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      font-size: 14px;
      color: #4a5568;
      min-width: 150px;
    }

    .filter-select:focus {
      outline: none;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
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
    
    .add-borrow-btn {
      background: linear-gradient(135deg, #48bb78, #38a169) !important;
      box-shadow: 0 4px 15px rgba(72, 187, 120, 0.2) !important;
    }
    
    .add-borrow-btn:hover {
      background: linear-gradient(135deg, #38a169, #2f855a) !important;
      box-shadow: 0 8px 25px rgba(56, 161, 105, 0.3) !important;
    }

    .table-container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      animation: fadeInUp 1s ease-out 0.6s both;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      min-width: 1000px;
      table-layout: fixed;
    }

    th, td {
      padding: 12px 8px;
      text-align: left;
      border-bottom: 1px solid #e2e8f0;
      vertical-align: middle;
    }

    /* Special styling for actions column */
    td:nth-child(8) {
      vertical-align: middle;
      padding: 8px 6px;
      text-align: center;
      min-height: 60px;
      height: auto;
      position: relative;
      display: table-cell;
    }

    th:nth-child(1) { width: 8%; }   /* Mã phiếu */
    th:nth-child(2) { width: 15%; }  /* Thông tin độc giả */
    th:nth-child(3) { width: 18%; }  /* Thông tin sách */
    th:nth-child(4) { width: 8%; }   /* Ngày mượn */
    th:nth-child(5) { width: 8%; }   /* Ngày hẹn trả */
    th:nth-child(6) { width: 8%; }   /* Ngày trả thực tế */
    th:nth-child(7) { width: 10%; }  /* Trạng thái */
    th:nth-child(8) { width: 25%; }  /* Hành động */

    /* Responsive actions for smaller containers */
    @media (max-width: 1400px) {
      .actions {
        gap: 4px;
      }
      
      .btn {
        padding: 5px 8px;
        font-size: 10px;
        min-width: 60px;
      }
    }

    @media (max-width: 1200px) {
      th:nth-child(8) { width: 30%; }  /* Increase actions column width */
      
      .actions {
        gap: 3px;
        justify-content: flex-start;
      }
      
      .btn {
        padding: 4px 6px;
        font-size: 9px;
        min-width: 55px;
        border-radius: 12px;
      }
    }

    @media (max-width: 1000px) {
      .actions {
        flex-direction: column;
        gap: 3px;
        align-items: stretch;
      }
      
      .btn {
        width: 100%;
        min-width: auto;
        justify-content: center;
      }
      
      td:nth-child(8) {
        padding: 6px 4px;
      }
    }

    th {
      background: linear-gradient(135deg, #f7fafc, #edf2f7);
      color: #4a5568;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    tbody tr {
      transition: all 0.3s ease;
    }

    tbody tr:hover {
      background: #f8fafc;
      transform: scale(1.01);
    }

    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      display: inline-block;
      white-space: nowrap;
      min-width: 100px;
      text-align: center;
    }

    .status-active {
      background: linear-gradient(135deg, #c6f6d5, #9ae6b4);
      color: #22543d;
    }

    .status-overdue {
      background: linear-gradient(135deg, #fed7d7, #feb2b2);
      color: #742a2a;
    }

    .status-returned {
      background: linear-gradient(135deg, #bee3f8, #90cdf4);
      color: #2a4365;
    }

    .status-due-soon {
      background: linear-gradient(135deg, #feebc8, #fbd38d);
      color: #744210;
    }

    .reader-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
      overflow: hidden;
    }

    .reader-name {
      font-weight: 600;
      color: #2d3748;
      font-size: 13px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .reader-email {
      font-size: 11px;
      color: #718096;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .book-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
      overflow: hidden;
    }

    .book-title {
      font-weight: 600;
      color: #2d3748;
      font-size: 13px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .book-author {
      font-size: 11px;
      color: #718096;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .date-info {
      font-size: 12px;
      color: #4a5568;
      white-space: nowrap;
    }

    .actions {
      display: flex;
      gap: 6px;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      min-width: 300px;
      width: 100%;
      margin: 0;
      padding: 8px 6px;
      position: relative;
      line-height: 1.2;
    }

    .btn {
      padding: 6px 10px;
      border: none;
      border-radius: 16px;
      cursor: pointer;
      font-weight: 600;
      font-size: 11px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 3px;
      white-space: nowrap;
      flex-shrink: 0;
      min-width: 65px;
      line-height: 1;
      vertical-align: middle;
      margin: 2px 0;
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

    .detail-btn {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      box-shadow: 0 3px 10px rgba(102, 126, 234, 0.2);
    }

    .detail-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }

    .extend-btn {
      background: linear-gradient(135deg, #4299e1, #3182ce);
      color: white;
      box-shadow: 0 3px 10px rgba(66, 153, 225, 0.2);
    }

    .extend-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
    }

    .return-btn {
      background: linear-gradient(135deg, #48bb78, #38a169);
      color: white;
      box-shadow: 0 3px 10px rgba(72, 187, 120, 0.2);
    }

    .return-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
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

    .delete-btn:disabled {
      background: linear-gradient(135deg, #a0aec0, #718096);
      color: #cbd5e0;
      box-shadow: none;
      cursor: not-allowed;
      transform: none;
    }

    .delete-btn:disabled:hover {
      transform: none;
      box-shadow: none;
    }

    .fine-btn {
      background: linear-gradient(135deg, #d69e2e, #b7791f);
      color: white;
      box-shadow: 0 3px 10px rgba(214, 158, 46, 0.2);
    }

    .fine-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(214, 158, 46, 0.4);
    }

    .fine-btn {
      background: linear-gradient(135deg, #f39c12, #e67e22);
      color: white;
      box-shadow: 0 3px 10px rgba(243, 156, 18, 0.2);
    }

    .fine-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(5px);
      z-index: 1000;
      animation: fadeIn 0.3s ease-out;
    }

    .modal-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 30px;
      border-radius: 20px;
      width: 90%;
      max-width: 600px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: slideIn 0.3s ease-out;
    }
    
    /* Modal header styling */
    .modal-content h2 {
      color: #2d3748;
      margin-bottom: 20px;
      font-size: 1.5rem;
      font-weight: 600;
      text-align: center;
      padding: 15px 0;
      background: white;
      border-radius: 10px;
      border-bottom: 2px solid #e2e8f0;
    }

    /* Book Details Modal Styles */
    #detailModal .modal-content {
      animation: slideInDown 0.4s ease-out;
    }
    
    .borrow-detail-info {
      background: linear-gradient(135deg, #f7fafc, #edf2f7);
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 24px;
      border-left: 5px solid #667eea;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .detail-info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      padding: 8px 0;
      border-bottom: 1px solid #e2e8f0;
    }
    
    .detail-info-row:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }
    
    .detail-info-label {
      font-weight: 600;
      color: #4a5568;
      font-size: 14px;
    }
    
    .detail-info-value {
      color: #2d3748;
      font-weight: 500;
    }
    
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translate(-50%, -60%);
      }
      to {
        opacity: 1;
        transform: translate(-50%, -50%);
      }
    }

    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(100%);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Responsive */
    
    /* Multi-select dropdown styles */
    .multi-select-container {
      position: relative;
      width: 100%;
    }

    .selected-items {
      min-height: 45px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      padding: 8px 12px;
      background: #f7fafc;
      cursor: pointer;
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      align-items: center;
      transition: all 0.3s ease;
    }

    .selected-items:hover,
    .selected-items.active {
      border-color: #4299e1;
      background: white;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .selected-items .placeholder {
      color: #a0aec0;
      font-size: 16px;
    }

    .selected-item {
      background: linear-gradient(135deg, #4299e1, #3182ce);
      color: white;
      padding: 4px 8px;
      border-radius: 15px;
      font-size: 12px;
      display: flex;
      align-items: center;
      gap: 5px;
      animation: fadeIn 0.3s ease;
    }

    .selected-item .remove {
      cursor: pointer;
      font-weight: bold;
      padding: 0 3px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.2);
      transition: background 0.2s ease;
    }

    .selected-item .remove:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .search-input {
      width: 100%;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      padding: 12px 15px;
      font-size: 16px;
      background: #f7fafc;
      margin-top: 5px;
      transition: all 0.3s ease;
    }

    .search-input:focus {
      outline: none;
      border-color: #4299e1;
      background: white;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .dropdown-list {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      max-height: 200px;
      overflow-y: auto;
      z-index: 1000;
      margin-top: 5px;
    }

    .dropdown-item {
      padding: 10px 15px;
      cursor: pointer;
      border-bottom: 1px solid #f7fafc;
      transition: background 0.2s ease;
      font-size: 14px;
    }

    .dropdown-item:hover {
      background: #f7fafc;
    }

    .dropdown-item:last-child {
      border-bottom: none;
    }

    .dropdown-item.selected {
      background: #e6fffa;
      color: #2d3748;
      font-weight: 600;
    }

    .dropdown-item .item-title {
      font-weight: 600;
      color: #2d3748;
    }

    .dropdown-item .item-subtitle {
      font-size: 12px;
      color: #718096;
      margin-top: 2px;
    }

    .no-results {
      padding: 15px;
      text-align: center;
      color: #718096;
      font-style: italic;
    }
    
    .books-detail-container {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border: 1px solid #e2e8f0;
    }
    
    .books-detail-container h3 {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      margin: 0;
      padding: 18px 24px;
      font-size: 16px;
      font-weight: 600;
    }
    
    .books-detail-table-container {
      max-height: 400px;
      overflow-y: auto;
      overflow-x: auto;
    }
    
    .books-detail-table {
      width: 100%;
      min-width: 900px;
      border-collapse: collapse;
      background: white;
    }
    
    .books-detail-table th {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      color: #374151;
      font-weight: 600;
      padding: 16px 12px;
      text-align: center;
      border-bottom: 2px solid #e5e7eb;
      position: sticky;
      top: 0;
      z-index: 10;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }
    
    .books-detail-table td {
      padding: 16px 12px;
      border-bottom: 1px solid #f3f4f6;
      vertical-align: middle;
      transition: all 0.2s ease;
    }
    
    .books-detail-table tbody tr {
      transition: all 0.2s ease;
    }
    
    .books-detail-table tbody tr:hover {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .books-detail-table tbody tr:nth-child(even) {
      background-color: #fafbfc;
    }
    
    .books-detail-table tbody tr:nth-child(even):hover {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    }

    /* Fine details column styling for detail table */
    .books-detail-table td:last-child {
      max-width: 200px;
      word-wrap: break-word;
      word-break: break-word;
      white-space: normal;
      line-height: 1.4;
    }

    /* Book title column styling for detail table */
    .books-detail-table td:nth-child(3) {
      max-width: 200px;
      word-wrap: break-word;
      word-break: break-word;
      white-space: normal;
      line-height: 1.4;
    }
    
    /* Book info styling */
    .book-code {
      font-family: 'Courier New', monospace !important;
      background: #f7fafc !important;
      padding: 6px 10px !important;
      border-radius: 6px !important;
      color: #4a5568 !important;
      font-weight: 600 !important;
      font-size: 12px !important;
      border: 1px solid #e2e8f0 !important;
    }
    
    .book-title {
      font-weight: 600 !important;
      color: #2d3748 !important;
      line-height: 1.4 !important;
    }
    
    .book-genre {
      background: #e6fffa !important;
      color: #285e61 !important;
      padding: 6px 12px !important;
      border-radius: 16px !important;
      font-size: 11px !important;
      font-weight: 600 !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
    }
    
    .book-author {
      color: #4a5568 !important;
      font-weight: 500 !important;
    }

    /* Toast notification */
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 8px;
      color: white;
      font-weight: 600;
      z-index: 9999;
      animation: slideInRight 0.3s ease-out;
      max-width: 400px;
      word-wrap: break-word;
    }

    .toast-success {
      background: linear-gradient(135deg, #48bb78, #38a169);
    }

    .toast-error {
      background: linear-gradient(135deg, #e53e3e, #c53030);
    }

    .toast-info {
      background: linear-gradient(135deg, #4299e1, #3182ce);
    }

    /* Empty state styling */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #718096;
    }
    
    .empty-state h3 {
      margin-bottom: 10px;
      color: #4a5568;
    }

    /* Form styling */
    .form-group {
      margin-bottom: 20px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #4a5568;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-size: 16px;
      background: #f7fafc;
      transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #4299e1;
      background: white;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    /* Return Books Modal Styling */
    .books-return-container {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border: 1px solid #e2e8f0;
      margin: 20px 0;
    }
    
    .books-return-container h3 {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      margin: 0;
      padding: 18px 24px;
      font-size: 16px;
      font-weight: 600;
    }
    
    .books-return-table-container {
      max-height: 400px;
      overflow-y: auto;
      overflow-x: auto;
    }
    
    .books-return-table {
      width: 100%;
      min-width: 800px;
      border-collapse: collapse;
      background: white;
    }
    
    .books-return-table th {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      color: #374151;
      font-weight: 600;
      padding: 16px 12px;
      text-align: center;
      border-bottom: 2px solid #e5e7eb;
      position: sticky;
      top: 0;
      z-index: 10;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }
    
    .books-return-table td {
      padding: 16px 12px;
      border-bottom: 1px solid #f3f4f6;
      vertical-align: middle;
      transition: all 0.2s ease;
    }
    
    .books-return-table tbody tr {
      transition: all 0.2s ease;
    }
    
    .books-return-table tbody tr:hover {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .books-return-table tbody tr:nth-child(even) {
      background-color: #fafbfc;
    }
    
    .books-return-table tbody tr:nth-child(even):hover {
      background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    }

    /* Fine details column styling */
    .books-return-table td:last-child {
      max-width: 300px;
      word-wrap: break-word;
      word-break: break-word;
      white-space: normal;
      line-height: 1.4;
    }

    /* Book title column styling */
    .books-return-table td:nth-child(3) {
      max-width: 200px;
      word-wrap: break-word;
      word-break: break-word;
      white-space: normal;
      line-height: 1.4;
    }

    /* Book status select styling */
    .book-status-select {
      padding: 8px 12px;
      border: 2px solid #e2e8f0;
      border-radius: 8px;
      font-size: 14px;
      background: white;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .book-status-select:focus {
      outline: none;
      border-color: #4299e1;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }
    
    .book-status-select option[value="1"] {
      color: #48bb78;
    }
    
    .book-status-select option[value="2"] {
      color: #ed8936;
    }
    
    .book-status-select option[value="3"] {
      color: #e53e3e;
    }

    /* Return summary styling */
    .return-summary {
      background: linear-gradient(135deg, #f7fafc, #edf2f7);
      border-radius: 12px;
      padding: 20px;
      margin: 20px 0;
      border: 1px solid #e2e8f0;
    }
    
    .return-summary h3 {
      margin: 0 0 15px 0;
      color: #2d3748;
      font-size: 18px;
      font-weight: 600;
    }
    
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 15px;
    }
    
    .summary-item {
      background: white;
      padding: 15px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      text-align: center;
    }
    
    .summary-item.total {
      background: linear-gradient(135deg, #4299e1, #3182ce);
      color: white;
      border-color: #3182ce;
    }
    
    .summary-label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 5px;
      color: #4a5568;
    }
    
    .summary-item.total .summary-label {
      color: rgba(255, 255, 255, 0.9);
    }
    
    .summary-value {
      display: block;
      font-size: 18px;
      font-weight: bold;
      color: #2d3748;
    }
    
    .summary-item.total .summary-value {
      color: white;
    }

    /* Button styling for return modal */
    .calculate-btn {
      background: linear-gradient(135deg, #ed8936, #dd6b20);
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .calculate-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(237, 137, 54, 0.3);
    }
    

      background: white;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid #e2e8f0;
    }

    .modal-actions .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      min-width: 120px;
    }

    .cancel-btn {
      background: #e2e8f0;
      color: #4a5568;
    }

    .cancel-btn:hover {
      background: #cbd5e0;
      transform: translateY(-2px);
    }

    .save-btn {
      background: linear-gradient(135deg, #48bb78, #38a169);
      color: white;
    }

    .save-btn:hover {
      background: linear-gradient(135deg, #38a169, #2f855a);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
    }

    .extend-modal .save-btn {
      background: linear-gradient(135deg, #4299e1, #3182ce);
    }
  </style>
@endpush

@section('content')
  <div class="container">
    <div class="header">
      <h1>📖 Hệ thống quản lý mượn sách</h1>
      <p>Dành cho nhân viên thư viện</p>
    </div>

    <!-- Toast notifications -->
    <div id="toast" class="toast" style="display: none;">
      <span id="toast-message"></span>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="stat-number" id="totalBorrows">0</div>
        <div class="stat-label">Tổng số phiếu</div>
      </div>
      <div class="stat-card returned">
        <div class="stat-number" id="returnedBorrows">0</div>
        <div class="stat-label">Số phiếu trả</div>
      </div>
      <div class="stat-card active">
        <div class="stat-number" id="activeBorrows">0</div>
        <div class="stat-label">Số phiếu mượn</div>
      </div>
      <div class="stat-card overdue">
        <div class="stat-number" id="overdueBorrows">0</div>
        <div class="stat-label">Số phiếu quá hạn</div>
      </div>
    </div>

    <!-- Controls -->
    <div class="controls">
      <div class="search-filter-group">
        <div class="search-box">
          <input type="text" id="searchInput" placeholder="Tìm kiếm theo tên độc giả, sách..." />
          <span class="search-icon">🔍</span>
        </div>
        <select class="filter-select" id="statusFilter">
          <option value="">Tất cả trạng thái</option>
          <option value="active">Đang mượn</option>
          <option value="overdue">Quá hạn</option>
          <option value="due-soon">Sắp hết hạn</option>
          <option value="returned">Đã trả</option>
        </select>
      </div>
      <div class="button-group">
        <a href="{{ route('home') }}" class="add-btn nav-home">
          🏠 Trang chủ
        </a>
        <button class="add-btn add-borrow-btn" onclick="openAddModal()">➕ Lập phiếu mượn</button>
      </div>
    </div>

    <!-- Main Table -->
    <div class="table-container">
      <table id="borrowsTable">
        <thead>
          <tr>
            <th>Mã phiếu</th>
            <th>Thông tin độc giả</th>
            <th>Thông tin sách</th>
            <th>Ngày mượn</th>
            <th>Ngày hẹn trả</th>
            <th>Ngày trả thực tế</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody id="borrowsTableBody">
          <!-- Dynamic content will be loaded here -->
        </tbody>
      </table>
      
      <!-- Empty State -->
      <div class="empty-state" id="emptyState" style="display: none;">
        <div style="font-size: 4rem; margin-bottom: 20px;">📚</div>
        <h3>Chưa có phiếu mượn nào</h3>
        <p>Hãy lập phiếu mượn đầu tiên!</p>
      </div>
    </div>
  </div>

  <!-- Add/Edit Borrow Modal -->
  <div class="modal" id="borrowModal">
    <div class="modal-content">
      <h2 id="borrowModalTitle">Lập phiếu mượn mới</h2>
      <form id="borrowForm">
        <div class="form-row">
          <div class="form-group">
            <label for="borrowReader">👤 Độc giả *</label>
            <div class="multi-select-container">
              <div class="selected-items" id="selectedReaderContainer">
                <span class="placeholder" id="readerPlaceholder">Chọn độc giả...</span>
              </div>
              <input type="text" id="readerSearchInput" class="search-input" placeholder="Tìm kiếm độc giả..." style="display: none;">
              <div class="dropdown-list" id="readerDropdown" style="display: none;"></div>
            </div>
            <input type="hidden" id="borrowReader" name="docgia_id" required>
          </div>
          <div class="form-group">
            <label for="borrowBooks">📖 Sách *</label>
            <div class="multi-select-container">
              <div class="selected-items" id="selectedBooksContainer">
                <span class="placeholder" id="booksPlaceholder">Chọn sách...</span>
              </div>
              <input type="text" id="booksSearchInput" class="search-input" placeholder="Tìm kiếm sách..." style="display: none;">
              <div class="dropdown-list" id="booksDropdown" style="display: none;"></div>
            </div>
            <input type="hidden" id="borrowBooks" name="sach_ids" required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="borrowDate">📅 Ngày mượn *</label>
          <input type="date" id="borrowDate" required>
        </div>
        <div class="form-group">
          <label for="borrowDueDate">📅 Ngày hẹn trả *</label>
          <input type="date" id="borrowDueDate" required>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn cancel-btn" onclick="closeModal('borrowModal')">Hủy</button>
          <button type="submit" class="btn save-btn" id="borrowSubmitBtn">Lưu phiếu mượn</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Extend Borrow Modal -->
  <div class="modal extend-modal" id="extendModal">
    <div class="modal-content">
      <h2>⏰ Gia hạn phiếu mượn</h2>
      <form id="extendForm">
        <div class="form-group">
          <label>📖 Thông tin phiếu mượn</label>
          <div style="background: #f7fafc; padding: 15px; border-radius: 10px; margin-bottom: 15px;">
            <div id="extendBorrowInfo">
              <!-- Thông tin phiếu mượn sẽ được cập nhật khi mở modal -->
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="newDueDate">📅 Ngày hẹn trả mới *</label>
            <input type="date" id="newDueDate" required>
          </div>
          <div class="form-group">
            <label for="extendDays">📊 Số ngày gia hạn</label>
            <input type="number" id="extendDays" min="1" max="30" placeholder="Số ngày">
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn cancel-btn" onclick="closeModal('extendModal')">Hủy</button>
          <button type="submit" class="btn save-btn">Xác nhận gia hạn</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Book Details Modal -->
  <div class="modal" id="detailModal">
    <div class="modal-content" style="max-width: 900px;">
      <h2 style="margin-bottom: 25px;">📋 Chi tiết phiếu mượn</h2>
      
      <!-- Thông tin phiếu mượn -->
      <div id="borrowDetailInfo" class="borrow-detail-info" style="margin-bottom: 30px;"></div>
      
      <!-- Danh sách sách -->
      <div class="books-detail-container" style="margin-bottom: 30px;">
        <h3>📚 Danh sách sách được mượn</h3>
        <div class="books-detail-table-container">
          <table class="books-detail-table">
            <thead>
              <tr>
                <th style="width: 5%; text-align: center;">STT</th>
                <th style="width: 12%; text-align: center;">Mã Sách</th>
                <th style="width: 15%; text-align: center;">Ngày Mượn</th>
                <th style="width: 12%; text-align: center;">Số Ngày Mượn</th>
                <th style="width: 15%; text-align: center;">Tiền Phạt</th>
                <th style="width: 15%; text-align: center;">Phí hỏng/mất</th>
                <th style="width: 13%; text-align: center;">Tổng</th>
              </tr>
            </thead>
            <tbody id="booksDetailTableBody">
              <!-- Dynamic content will be loaded here -->
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="modal-actions">
        <button type="button" class="btn cancel-btn" onclick="closeModal('detailModal')">Đóng</button>
      </div>
    </div>
  </div>

  <!-- Return Books Modal -->
  <div class="modal" id="returnModal">
    <div class="modal-content" style="max-width: 900px;">
      <h2>📚 Trả sách</h2>
      
      <!-- Borrow record info -->
      <div id="returnBorrowInfo" class="borrow-detail-info"></div>
      
      <!-- Books to return -->
      <div class="books-return-container">
        <h3>📖 Danh sách sách cần trả</h3>
        <div class="books-return-table-container">
          <table class="books-return-table">
            <thead>
              <tr>
                <th style="width: 5%; text-align: center;">STT</th>
                <th style="width: 15%; text-align: center;">Mã Sách</th>
                <th style="width: 25%; text-align: center;">Tên Sách</th>
                <th style="width: 15%; text-align: center;">Giá Trị</th>
                <th style="width: 15%; text-align: center;">Tình Trạng</th>
                <th style="width: 25%; text-align: left;">Chi Tiết Phạt</th>
              </tr>
            </thead>
            <tbody id="booksReturnTableBody">
              <!-- Dynamic content will be loaded here -->
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Summary -->
      <div id="returnSummary" class="return-summary" style="display: none;">
        <h3>💰 Tổng kết phạt</h3>
        <div class="summary-grid">
          <div class="summary-item">
            <span class="summary-label">⏰ Tổng phạt trễ:</span>
            <span class="summary-value" id="totalLateFine">0 VNĐ</span>
          </div>
          <div class="summary-item">
            <span class="summary-label">💰 Tổng đền bù:</span>
            <span class="summary-value" id="totalCompensation">0 VNĐ</span>
          </div>
          <div class="summary-item total">
            <span class="summary-label">💳 Tổng cộng:</span>
            <span class="summary-value" id="totalFine">0 VNĐ</span>
          </div>
        </div>
        <div class="summary-note" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 5px; font-size: 12px; color: #666;">
          <strong>📋 Quy định phạt:</strong><br>
          • Phạt trễ: 1.000 VNĐ/ngày/sách<br>
          • Sách hỏng: Đền bù 50% giá trị sách<br>
          • Sách mất: Đền bù 100% giá trị sách
        </div>
      </div>
      
      <div class="modal-actions">
        <button type="button" class="btn cancel-btn" onclick="closeModal('returnModal')">Hủy</button>
        <button type="button" class="btn calculate-btn" onclick="calculateReturnFines()">Tính phạt</button>
        <button type="button" class="btn return-btn" id="confirmReturnBtn" onclick="confirmReturnBooks()" style="display: none;">Xác nhận trả</button>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  // Prevent errors from undefined functions that might be called by extensions or cached code
  window.checkReaderEligibility = window.checkReaderEligibility || function() {
    console.log('checkReaderEligibility called but not implemented');
    return Promise.resolve(true);
  };

  const BORROW_DURATION_DAYS = Number({{ $borrowDurationDays ?? 14 }});

  // Global variables
  let borrowRecords = [];
  let allReaders = [];
  let allBooks = [];
  let currentEditId = null;
  let currentExtendId = null;
  let filteredRecords = [];
  let selectedReader = null;
  let selectedBooks = [];
  let isEditMode = false;

  // Initialize page
  document.addEventListener('DOMContentLoaded', function() {
    loadInitialData();
    setupEventListeners();
    setDefaultDates();
  });

  // Load initial data from APIs
  async function loadInitialData() {
    try {
      // Load borrow records
      const borrowResponse = await fetch('/api/borrow-records');
      const borrowData = await borrowResponse.json();
      
      if (borrowData.success) {
        borrowRecords = (borrowData.data || []).map(record => {
          const normalizedStatus = normalizeStatus(record.status);
          return {
            ...record,
            status: normalizedStatus,
            TrangThai: record.TrangThai ? normalizeStatus(record.TrangThai) : record.TrangThai,
          };
        });
        filteredRecords = [...borrowRecords];
      } else {
        borrowRecords = [];
        filteredRecords = [];
      }

      // Load readers for dropdowns
      const readersResponse = await fetch('/api/readers-list');
      const readersData = await readersResponse.json();
      
      if (readersData.success) {
        allReaders = readersData.data || [];
        initializeReaderSelector();
      } else {
        allReaders = [];
      }

      // Load books for dropdowns
      const booksResponse = await fetch('/api/books-list');
      const booksData = await booksResponse.json();
      
      if (booksData.success) {
        allBooks = booksData.data || [];
        initializeBooksSelector();
      } else {
        allBooks = [];
      }

      updateStats();
      renderBorrowRecords();
      
    } catch (error) {
      console.error('Error loading data:', error);
      showToast('Lỗi khi tải dữ liệu: ' + error.message, 'error');
    }
  }

  // Initialize reader selector
  function initializeReaderSelector() {
    const container = document.getElementById('selectedReaderContainer');
    const searchInput = document.getElementById('readerSearchInput');
    const dropdown = document.getElementById('readerDropdown');
    const hiddenInput = document.getElementById('borrowReader');
    
    // Clear previous selections
    container.innerHTML = '<span class="placeholder" id="readerPlaceholder">Chọn độc giả...</span>';
    hiddenInput.value = '';
    selectedReader = null;

    // Show/hide dropdown based on input
    container.addEventListener('click', function() {
      searchInput.style.display = 'block';
      searchInput.focus();
      showReadersDropdown('');
    });

    searchInput.addEventListener('input', function() {
      showReadersDropdown(this.value);
    });

    searchInput.addEventListener('blur', function() {
      setTimeout(() => {
        if (!dropdown.matches(':hover')) {
          hideReadersDropdown();
        }
      }, 200);
    });

    document.addEventListener('click', function(e) {
      if (!container.contains(e.target)) {
        hideReadersDropdown();
      }
    });
  }

  function showReadersDropdown(searchTerm) {
    const dropdown = document.getElementById('readerDropdown');
    const filteredReaders = allReaders.filter(reader => {
      const isNotSelected = !selectedReader || selectedReader.id !== reader.id;
      const name = (reader.name || reader.TenDocGia || '').toString();
      const email = (reader.email || reader.Email || '').toString();
      const matchesSearch = name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                          email.toLowerCase().includes(searchTerm.toLowerCase());
      return isNotSelected && matchesSearch;
    });

    if (filteredReaders.length === 0) {
      dropdown.innerHTML = '<div class="no-results">Không tìm thấy độc giả phù hợp</div>';
    } else {
      dropdown.innerHTML = filteredReaders.map(reader => `
        <div class="dropdown-item" onclick="selectReader('${reader.id}')">
          <div class="item-title">${reader.name || reader.TenDocGia || 'Không rõ tên độc giả'}</div>
          <div class="item-subtitle">${reader.email || reader.Email || 'Chưa có email'}</div>
        </div>
      `).join('');
    }
    
    dropdown.style.display = 'block';
  }

  function hideReadersDropdown() {
    const container = document.getElementById('selectedReaderContainer');
    const searchInput = document.getElementById('readerSearchInput');
    const dropdown = document.getElementById('readerDropdown');
    
    container.classList.remove('active');
    searchInput.style.display = 'none';
    searchInput.value = '';
    dropdown.style.display = 'none';
    
    if (!selectedReader) {
      const placeholder = container.querySelector('.placeholder');
      if (placeholder) placeholder.style.display = 'inline';
    }
  }

  function selectReader(readerId) {
    const reader = allReaders.find(r => r.id === readerId);
    if (!reader) return;

    selectedReader = reader;
    updateReaderDisplay();
    document.getElementById('readerSearchInput').value = '';
    hideReadersDropdown();
  }

  function updateReaderDisplay() {
    const container = document.getElementById('selectedReaderContainer');
    const hiddenInput = document.getElementById('borrowReader');
    
    if (!selectedReader) {
      container.innerHTML = '<span class="placeholder" id="readerPlaceholder">Chọn độc giả...</span>';
      hiddenInput.value = '';
    } else {
      container.innerHTML = `
        <div class="selected-item">
          <span>${selectedReader.name}</span>
          <span class="remove" onclick="removeReader()">&times;</span>
        </div>
      `;
      
      hiddenInput.value = selectedReader.MaDocGia || selectedReader.id;
    }
  }

  function removeReader() {
    selectedReader = null;
    updateReaderDisplay();
  }

  // Initialize books selector
  function initializeBooksSelector() {
    const container = document.getElementById('selectedBooksContainer');
    const searchInput = document.getElementById('booksSearchInput');
    const dropdown = document.getElementById('booksDropdown');
    const hiddenInput = document.getElementById('borrowBooks');
    
    // Clear previous selections only if not in edit mode
    if (!isEditMode) {
    container.innerHTML = '<span class="placeholder" id="booksPlaceholder">Chọn sách...</span>';
    hiddenInput.value = '';
    selectedBooks = [];
    }

    // Show/hide dropdown based on input
    container.addEventListener('click', function() {
      searchInput.style.display = 'block';
      searchInput.focus();
      showBooksDropdown('');
    });

    searchInput.addEventListener('input', function() {
      showBooksDropdown(this.value);
    });

    searchInput.addEventListener('blur', function() {
      setTimeout(() => {
        if (!dropdown.matches(':hover')) {
          hideBooksDropdown();
        }
      }, 200);
    });

    document.addEventListener('click', function(e) {
      if (!container.contains(e.target)) {
        hideBooksDropdown();
      }
    });
  }

  function showBooksDropdown(searchTerm) {
    const dropdown = document.getElementById('booksDropdown');
    const filteredBooks = allBooks.filter(book => {
      const title = (book.title || book.TenSach || book.TenDauSach || '').toString();
      const author = (book.author || book.TenTacGia || book.tac_gia?.TenTacGia || '').toString();
      const matchesSearch = title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                          author.toLowerCase().includes(searchTerm.toLowerCase());
      return matchesSearch;
    });

    if (filteredBooks.length === 0) {
      dropdown.innerHTML = '<div class="no-results">Không tìm thấy sách phù hợp</div>';
    } else {
      dropdown.innerHTML = filteredBooks.map(book => {
        const isSelected = selectedBooks.some(sb => sb.id === book.id);
        const bookTitle = book.title || book.TenSach || book.TenDauSach || 'Không rõ tên sách';
        const bookAuthor = book.author || book.TenTacGia || (book.tac_gia ? book.tac_gia.TenTacGia : '') || 'Chưa có tác giả';
        
        return `
          <div class="dropdown-item ${isSelected ? 'selected' : ''}" onclick="selectBook(${book.id})">
            <div class="item-title">${bookTitle} ${isSelected ? '<span style="color: #4299e1; font-weight: bold;">(Đã chọn)</span>' : ''}</div>
            <div class="item-subtitle">${bookAuthor}</div>
        </div>
        `;
      }).join('');
    }
    
    dropdown.style.display = 'block';
  }

  function hideBooksDropdown() {
    const container = document.getElementById('selectedBooksContainer');
    const searchInput = document.getElementById('booksSearchInput');
    const dropdown = document.getElementById('booksDropdown');
    
    container.classList.remove('active');
    searchInput.style.display = 'none';
    searchInput.value = '';
    dropdown.style.display = 'none';
    
    if (selectedBooks.length === 0) {
      const placeholder = container.querySelector('.placeholder');
      if (placeholder) placeholder.style.display = 'inline';
    }
  }

  function selectBook(bookId) {
    const id = Number(bookId);
    const book = allBooks.find(b => Number(b.id) === id);
    if (!book) return;

    // Check if book is already selected
    const existingIndex = selectedBooks.findIndex(sb => Number(sb.id) === id);
    if (existingIndex !== -1) {
      // Book is already selected, remove it
      selectedBooks.splice(existingIndex, 1);
    } else {
      // Book is not selected, add it
    selectedBooks.push(book);
    }

    updateBooksDisplay();
    document.getElementById('booksSearchInput').value = '';
    showBooksDropdown('');
  }

  function removeBook(bookId) {
    const id = Number(bookId);
    selectedBooks = selectedBooks.filter(book => Number(book.id) !== id);
    updateBooksDisplay();
  }

  function updateBooksDisplay() {
    const container = document.getElementById('selectedBooksContainer');
    const hiddenInput = document.getElementById('borrowBooks');
    
    console.log('updateBooksDisplay called with selectedBooks:', selectedBooks);
    console.log('Container element:', container);
    console.log('Container exists:', !!container);
    console.log('Container innerHTML before:', container ? container.innerHTML : 'N/A');
    
    if (selectedBooks.length === 0) {
      container.innerHTML = '<span class="placeholder" id="booksPlaceholder">Chọn sách...</span>';
      hiddenInput.value = '';
      console.log('No books selected, showing placeholder');
    } else {
      const placeholder = container.querySelector('.placeholder');
      if (placeholder) placeholder.style.display = 'none';
      
      const booksHTML = selectedBooks.map(book => `
        <div class="selected-item">
          <span>${book.title || book.TenSach || book.TenDauSach || 'Không rõ tên sách'}</span>
          <span class="remove" onclick="removeBook(${book.id})">&times;</span>
        </div>
      `).join('');
      
      container.innerHTML = booksHTML;
      hiddenInput.value = selectedBooks.map(book => book.MaSach || book.id).join(',');
      
      console.log('Books HTML generated:', booksHTML);
      console.log('Container innerHTML after:', container.innerHTML);
      console.log('Container innerHTML set');
    }
    
    console.log('Books display updated. Selected books count:', selectedBooks.length);
  }

  // Update statistics
  function updateStats() {
    const total = filteredRecords.length;
    const returned = filteredRecords.filter(r => r.status === 'returned').length;
    const active = filteredRecords.filter(r => r.status === 'active').length;
    const overdue = filteredRecords.filter(r => r.status === 'overdue').length;

    document.getElementById('totalBorrows').textContent = total;
    document.getElementById('returnedBorrows').textContent = returned;
    document.getElementById('activeBorrows').textContent = active;
    document.getElementById('overdueBorrows').textContent = overdue;
  }

  // Render borrow records table
  function renderBorrowRecords() {
    const tbody = document.getElementById('borrowsTableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (filteredRecords.length === 0) {
      tbody.innerHTML = '';
      emptyState.style.display = 'block';
      return;
    }

    emptyState.style.display = 'none';
    
    tbody.innerHTML = filteredRecords.map(record => {
      // Handle multiple books display - use new API structure
      let bookTitles = 'Không có sách';
      let bookAuthors = 'Chưa có tác giả';
      
      if (record.books && record.books.length > 0) {
        bookTitles = record.books.map(book => book.TenSach || book.title || 'Không rõ tên sách').join(', ');
        bookAuthors = record.books.map(book => {
          if (book.tac_gia && book.tac_gia.TenTacGia) {
            return book.tac_gia.TenTacGia;
          }
          return book.author || 'Chưa có tác giả';
        }).join(', ');
      } else if (record.book_title) {
        // Fallback to old structure if available
        bookTitles = record.book_title;
        bookAuthors = record.book_author || 'Chưa có tác giả';
      }
      
      // Check if this record can have a fine button
      // Conditions: 1. Status is 'returned', 2. Is overdue, 3. No fine created yet
      const canHaveFineButton = record.status === 'returned' && record.is_overdue && !record.fine_created;
      
      return `
        <tr>
          <td>${record.ma_phieu || record.code || 'N/A'}</td>
          <td>
            <div class="reader-info">
              <div class="reader-name">${record.reader ? record.reader.name : (record.reader_name || 'N/A')}</div>
              <div class="reader-email">${record.reader ? record.reader.email : (record.reader_email || '')}</div>
            </div>
          </td>
          <td>
            <div class="book-info">
              <div class="book-title" title="${bookTitles}">${bookTitles}</div>
              <div class="book-author" title="${bookAuthors}">${bookAuthors}</div>
            </div>
          </td>
          <td class="date-info">${formatDate(record.borrow_date)}</td>
          <td class="date-info">${formatDate(record.due_date)}</td>
          <td class="date-info">${record.return_date ? formatDate(record.return_date) : '-'}</td>
          <td>
            <span class="status-badge status-${getStatusClass(record.status)}">${getStatusText(record.status)}</span>
          </td>
          <td>
            <div class="actions">
              <button class="btn detail-btn" onclick="openDetailModal(\'${record.id}\')" title="Xem chi tiết sách">📋 Chi tiết</button>
              <button class="btn edit-btn" onclick="openEditModal(\'${record.id}\')">✏️ Sửa</button>
              ${record.status !== 'returned' ? `
                <button class="btn extend-btn" onclick="openExtendModal(\'${record.id}\')">⏰ Gia hạn</button>
                <button class="btn return-btn" onclick="returnAllBooksInRecord(\'${record.id}\')">↩️ Trả</button>
              ` : ''}
              ${canHaveFineButton ? `
                <button class="btn fine-btn" onclick="createFineFromRecord(\'${record.id}\')">💰 Lập phiếu phạt</button>
              ` : ''}
              <button class="btn delete-btn" onclick="deleteBorrow(\'${record.id}\')" title="Xóa phiếu mượn">🗑️ Xóa</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  // Helper functions
  function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
  }

  function formatCurrency(amount) {
    if (amount === null || amount === undefined) return '0 VNĐ';
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      minimumFractionDigits: 0
    }).format(amount);
  }

  function getStatusText(status) {
    const statusMap = {
      'active': '\u0110ang m\u01b0\u1ee3n',
      'borrowed': '\u0110ang m\u01b0\u1ee3n',
      'overdue': 'Qu\u00e1 h\u1ea1n',
      'due-soon': 'S\u1eafp h\u1ebft h\u1ea1n',
      'returned': '\u0110\u00e3 tr\u1ea3'
    };
    return statusMap[status] || status;
  }

  function normalizeStatus(status) {
    return status === 'borrowed' ? 'active' : status;
  }

  function getStatusClass(status) {
    return normalizeStatus(status);
  }

  function getDocGiaName(docGia) {
    if (!docGia) return 'N/A';
    return docGia.TenDocGia || docGia.HoTen || docGia.HoTenDocGia || docGia.name || 'N/A';
  }

  function calculateDueDate(baseDate, days) {
    if (!baseDate) return '';
    const date = new Date(baseDate);
    if (Number.isNaN(date.getTime())) return '';
    date.setDate(date.getDate() + days);
    return date.toISOString().split('T')[0];
  }

  function setDefaultDates() {
    const today = new Date().toISOString().split('T')[0];
    
    document.getElementById('borrowDate').value = today;
    const dueInput = document.getElementById('borrowDueDate');
    if (dueInput) {
      dueInput.value = calculateDueDate(today, BORROW_DURATION_DAYS);
    }
  }

  function handleBorrowDateChange() {
    const borrowDate = document.getElementById('borrowDate').value;
    const dueInput = document.getElementById('borrowDueDate');
    if (!dueInput) return;
    if (!borrowDate) {
      dueInput.value = '';
      return;
    }
    if (!isEditMode || !dueInput.value) {
      dueInput.value = calculateDueDate(borrowDate, BORROW_DURATION_DAYS);
    }
  }

  // Setup event listeners
  function setupEventListeners() {
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', handleSearch);
    
    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', handleFilter);
    
    // Form submissions
    document.getElementById('borrowForm').addEventListener('submit', handleBorrowSubmit);
    document.getElementById('extendForm').addEventListener('submit', handleExtendSubmit);

    document.getElementById('borrowDate').addEventListener('change', handleBorrowDateChange);
    
    // Date calculation for extend modal
    document.getElementById('newDueDate').addEventListener('change', calculateExtendDays);
    document.getElementById('extendDays').addEventListener('input', syncExtendDaysToDate);

    
    // Modal close on outside click
    window.addEventListener('click', handleModalOutsideClick);
  }

  // Search and filter functions
  function handleSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    applyFilters();
  }

  function handleFilter() {
    applyFilters();
  }

  function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    filteredRecords = borrowRecords.filter(record => {
      const readerName = record.reader ? record.reader.name : (record.reader_name || '');
      const readerEmail = record.reader ? record.reader.email : (record.reader_email || '');
      
      let bookTitles = '';
      let bookAuthors = '';
      
      if (record.books && record.books.length > 0) {
        bookTitles = record.books.map(book => book.TenSach || book.title || '').join(' ');
        bookAuthors = record.books.map(book => {
          if (book.tac_gia && book.tac_gia.TenTacGia) {
            return book.tac_gia.TenTacGia;
          }
          return book.author || '';
        }).join(' ');
      } else {
        bookTitles = record.book_title || '';
        bookAuthors = record.book_author || '';
      }
      
      // Get record code from new API structure
      const recordCode = record.ma_phieu || record.code || '';
      
      const matchesSearch = !searchTerm || 
        readerName.toLowerCase().includes(searchTerm) ||
        readerEmail.toLowerCase().includes(searchTerm) ||
        bookTitles.toLowerCase().includes(searchTerm) ||
        bookAuthors.toLowerCase().includes(searchTerm) ||
        recordCode.toLowerCase().includes(searchTerm);
      
      const matchesStatus = !statusFilter || record.status === statusFilter;
      
      return matchesSearch && matchesStatus;
    });
    
    updateStats();
    renderBorrowRecords();
  }

  // Modal functions
  function openAddModal() {
    isEditMode = false;
    currentEditId = null;
    selectedReader = null;
    selectedBooks = [];
    
    document.getElementById('borrowModalTitle').textContent = 'Lập phiếu mượn mới';
    document.getElementById('borrowSubmitBtn').textContent = 'Lưu phiếu mượn';
    document.getElementById('borrowForm').reset();
    
    // Reset reader selector
    document.getElementById('selectedReaderContainer').innerHTML = '<span class="placeholder" id="readerPlaceholder">Chọn độc giả...</span>';
    document.getElementById('borrowReader').value = '';
    
    // Reset books selector
    document.getElementById('selectedBooksContainer').innerHTML = '<span class="placeholder" id="booksPlaceholder">Chọn sách...</span>';
    document.getElementById('borrowBooks').value = '';
    
    // Enable book selection for new records
    document.getElementById('borrowBooks').disabled = false;
    document.getElementById('borrowBooks').style.opacity = '1';
    document.getElementById('borrowBooks').title = '';
    
    setDefaultDates();
    document.getElementById('borrowModal').style.display = 'block';
  }

  async function openEditModal(id) {
    currentEditId = id;
    isEditMode = true;
    const record = borrowRecords.find(r => r.id === id);
    if (!record) return;
    
    // Set appropriate modal title and button text based on record status
    const isReturnedRecord = record.status === 'returned';
    const modalTitle = isReturnedRecord ? 'Chỉnh sửa phiếu trả' : 'Chỉnh sửa phiếu mượn';
    const buttonText = isReturnedRecord ? 'Lưu phiếu trả' : 'Lưu phiếu mượn';
    
    document.getElementById('borrowModalTitle').textContent = modalTitle;
    document.getElementById('borrowSubmitBtn').textContent = buttonText;
    
    // Reset selections
    selectedReader = null;
    selectedBooks = [];
    
    // Load all readers for edit modal (including those with overdue books)
    try {
      const readersResponse = await fetch('/api/all-readers-list');
      const readersData = await readersResponse.json();
      
      if (readersData.success) {
        allReaders = readersData.data || [];
        initializeReaderSelector();
    
    // Set reader - use new API structure
    let readerId = null;
    if (record.reader && record.reader.MaDocGia) {
      readerId = record.reader.MaDocGia;
        } else if (record.docgia_id) {
          readerId = record.docgia_id; // fallback to old structure
    }
    
    if (readerId) {
      const reader = allReaders.find(r => r.id == readerId);
      if (reader) {
        selectedReader = reader;
        selectReader(reader.MaDocGia);
      }
        }
      }
    } catch (error) {
      console.error('Error loading all readers for edit:', error);
      showToast('Lỗi khi tải danh sách độc giả cho chỉnh sửa', 'error');
    }
    
    console.log('Edit modal - Record data:', record);
    console.log('Edit modal - Record books:', record.books);
    console.log('Edit modal - Selected books after reset:', selectedBooks);
    
    // Load books for edit modal (available + currently borrowed in this record)
    try {
      const response = await fetch(`/api/edit-books-list?phieu_muon_id=${id}`);
      const data = await response.json();
      
      if (data.success) {
        const booksForEdit = data.data || [];
        const isReturnedRecord = data.is_returned_record || false;
    
        console.log('Edit modal - Books for edit:', booksForEdit);
        console.log('Edit modal - Is returned record:', isReturnedRecord);
        
                if (isReturnedRecord) {
          // For returned records, show all books that were in this record
          console.log('Edit modal - Processing returned record books');
          
    if (record.books && record.books.length > 0) {
      record.books.forEach(bookData => {
              console.log('Edit modal - Processing returned book data:', bookData);
              const bookId = bookData.id;
              const book = booksForEdit.find(b => b.id == bookId);
        if (book) {
          selectedBooks.push(book);
                console.log('Edit modal - Added returned book to selection:', book);
              } else {
                console.warn('Returned book not found in booksForEdit:', bookData);
              }
            });
          }
          
          // Enable book selection for returned records (can add available books)
          document.getElementById('borrowBooks').disabled = false;
          document.getElementById('borrowBooks').style.opacity = '1';
          document.getElementById('borrowBooks').title = 'Có thể thêm sách có sẵn vào phiếu mượn đã trả';
          
        } else {
          // For active records, proceed with normal logic
          console.log('Edit modal - Processing active record books');
          
          if (record.books && record.books.length > 0) {
            // Multiple books format from API
            console.log('Edit modal - Processing multiple books:', record.books);
            
            record.books.forEach(bookData => {
              console.log('Edit modal - Processing book data:', bookData);
              // API returns books with 'id' field
              const bookId = bookData.id;
              const book = booksForEdit.find(b => b.id == bookId);
              if (book) {
                selectedBooks.push(book);
                console.log('Edit modal - Added book to selection:', book);
              } else {
                console.warn('Book not found in booksForEdit:', bookData);
        }
      });
    } else if (record.book_id) {
      // Single book format (backward compatibility)
            console.log('Edit modal - Processing single book:', record.book_id);
            const book = booksForEdit.find(b => b.id == record.book_id);
      if (book) {
        selectedBooks = [book];
              console.log('Edit modal - Added single book to selection:', book);
            }
          }
          
          // Enable book selection for active records
          document.getElementById('borrowBooks').disabled = false;
          document.getElementById('borrowBooks').style.opacity = '1';
          document.getElementById('borrowBooks').title = '';
        }
        
        console.log('Edit modal - Final selected books:', selectedBooks);
        
        // Update the global allBooks for this edit session
        allBooks = booksForEdit;
        initializeBooksSelector();
        
        // Update display after all data is loaded
        console.log('Edit modal - About to call updateBooksDisplay');
    updateBooksDisplay();
    
        console.log('Edit modal - Selected books:', selectedBooks);
        console.log('Edit modal - Books display updated');
        
        // Force update display again after a short delay
        setTimeout(() => {
          console.log('Edit modal - Force update display after delay');
          updateBooksDisplay();
        }, 100);
      }
    } catch (error) {
      console.error('Error loading books for edit:', error);
      showToast('Lỗi khi tải danh sách sách cho chỉnh sửa', 'error');
    }
    
    // Set borrow date - handle different field names
    const borrowDate = record.borrow_date || record.NgayMuon || record.ngay_muon;
    if (borrowDate) {
      document.getElementById('borrowDate').value = borrowDate;
    } else {
      // Set today's date as default if no date found
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('borrowDate').value = today;
    }

    const dueDate = record.due_date || record.NgayHenTra || record.ngay_hen_tra;
    const dueInput = document.getElementById('borrowDueDate');
    if (dueInput) {
      if (dueDate) {
        dueInput.value = dueDate;
      } else {
        dueInput.value = calculateDueDate(document.getElementById('borrowDate').value, BORROW_DURATION_DAYS);
      }
    }
    
    document.getElementById('borrowModal').style.display = 'block';
  }

  function openDetailModal(id) {
    // Find the borrow record
    const record = borrowRecords.find(r => r.id === id);
    if (!record) {
      showToast('Không tìm thấy phiếu mượn', 'error');
      return;
    }

    // Fetch detailed information from server
    fetch(`/api/borrow-records/${id}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          const borrowDetail = data.data;
          displayBorrowDetail(borrowDetail);
          document.getElementById('detailModal').style.display = 'block';
        } else {
          showToast(data.message || 'Không thể tải chi tiết phiếu mượn', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra khi tải chi tiết phiếu mượn', 'error');
      });
  }

  function displayBorrowDetail(borrowDetail) {
    // Debug log to check data
    console.log('Borrow Detail Data:', borrowDetail);
    
    // Determine if record is returned based on chi_tiet_phieu_muon
    const isReturnedRecord = borrowDetail.chi_tiet_phieu_muon && 
      borrowDetail.chi_tiet_phieu_muon.every(item => item.NgayTra !== null);
    
    console.log('Is returned record:', isReturnedRecord);
    
    if (isReturnedRecord) {
      // Display returned record with detailed information
      displayReturnedRecordDetail(borrowDetail);
    } else {
      // Display active record with normal information
      displayActiveRecordDetail(borrowDetail);
    }
  }

  function displayReturnedRecordDetail(borrowDetail) {
    // Calculate total fines and compensation
    let totalFine = 0;
    let totalCompensation = 0;
    let returnDate = null;
    
    if (borrowDetail.chi_tiet_phieu_muon && borrowDetail.chi_tiet_phieu_muon.length > 0) {
      borrowDetail.chi_tiet_phieu_muon.forEach(chiTiet => {
        if (chiTiet.NgayTra) {
          totalFine += Math.round(parseFloat(chiTiet.TienPhat || 0));
          totalCompensation += Math.round(parseFloat(chiTiet.TienDenBu || 0));
          if (!returnDate || new Date(chiTiet.NgayTra) > new Date(returnDate)) {
            returnDate = chiTiet.NgayTra;
          }
        }
      });
    }
    
    const totalDebt = totalFine + totalCompensation;
    
    // Display returned record info
    let infoHTML = `
      <div class="detail-info-row">
        <span class="detail-info-label">📋 Mã phiếu:</span>
        <span class="detail-info-value">${borrowDetail.MaPhieu || 'N/A'}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">👤 Họ tên độc giả:</span>
        <span class="detail-info-value">${getDocGiaName(borrowDetail.doc_gia)}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">📅 Ngày trả:</span>
        <span class="detail-info-value">${formatDate(returnDate)}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">💰 Tiền phạt kỳ này:</span>
        <span class="detail-info-value" style="color: #e53e3e; font-weight: 600;">${formatCurrency(totalFine)}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">💳 Tổng nợ:</span>
        <span class="detail-info-value" style="color: #e53e3e; font-weight: 600;">${formatCurrency(totalDebt)}</span>
      </div>
    `;

    document.getElementById('borrowDetailInfo').innerHTML = infoHTML;

    // Display returned books detail table
    const tableContainer = document.querySelector('.books-detail-table');
    
    let tableHTML = `
      <thead>
        <tr>
          <th style="width: 5%; text-align: center;">STT</th>
          <th style="width: 12%; text-align: center;">Mã Sách</th>
          <th style="width: 15%; text-align: center;">Ngày Mượn</th>
          <th style="width: 12%; text-align: center;">Số Ngày Mượn</th>
          <th style="width: 15%; text-align: center;">Tiền Phạt</th>
          <th style="width: 15%; text-align: center;">Phí hỏng/mất</th>
          <th style="width: 13%; text-align: center;">Tổng</th>
        </tr>
      </thead>
      <tbody id="booksDetailTableBody">
      </tbody>
    `;
    
    tableContainer.innerHTML = tableHTML;

    // Display returned books detail table body
    const tableBody = document.getElementById('booksDetailTableBody');
    if (borrowDetail.chi_tiet_phieu_muon && borrowDetail.chi_tiet_phieu_muon.length > 0) {
      // Display returned book details
      tableBody.innerHTML = borrowDetail.chi_tiet_phieu_muon.map((chiTiet, index) => {
        const book = chiTiet.sach;
        if (!book) {
          return `
            <tr>
              <td style="text-align: center; font-weight: 600; color: #667eea;">${index + 1}</td>
              <td colspan="6" style="text-align: center; color: #e53e3e;">
                ⚠️ Sách ID ${chiTiet.sach_id} không tìm thấy
              </td>
            </tr>
          `;
        }
        
        const bookCode = book.MaSach || 'N/A';
        const borrowDate = formatDate(borrowDetail.NgayMuon);
        const returnDate = formatDate(chiTiet.NgayTra);
        
        // Calculate days borrowed
        const borrowDateObj = new Date(borrowDetail.NgayMuon);
        const returnDateObj = new Date(chiTiet.NgayTra);
        const daysBorrowed = Math.ceil((returnDateObj - borrowDateObj) / (1000 * 60 * 60 * 24));
        
        // Get fine and compensation amounts - ensure they are integers
        const fineAmount = Math.round(parseFloat(chiTiet.TienPhat || 0));
        const compensationAmount = Math.round(parseFloat(chiTiet.TienDenBu || 0));
        const totalAmount = fineAmount + compensationAmount;
        
        return `
          <tr>
            <td style="text-align: center; font-weight: 600; color: #667eea;">${index + 1}</td>
            <td style="text-align: center;">
              <span class="book-code" style="font-family: monospace; background: #f7fafc; padding: 4px 8px; border-radius: 4px; color: #4a5568;">${bookCode}</span>
            </td>
            <td style="text-align: center;">
              <span class="date-info" style="color: #4a5568;">${borrowDate}</span>
            </td>
            <td style="text-align: center;">
              <span class="days-borrowed" style="color: #2d3748; font-weight: 500;">${daysBorrowed} ngày</span>
            </td>
            <td style="text-align: center;">
              <span class="fine-amount" style="color: #e53e3e; font-weight: 500;">${formatCurrency(fineAmount)}</span>
            </td>
            <td style="text-align: center;">
              <span class="compensation-amount" style="color: #e53e3e; font-weight: 500;">${formatCurrency(compensationAmount)}</span>
            </td>
            <td style="text-align: center;">
              <span class="total-amount" style="color: #e53e3e; font-weight: 600;">${formatCurrency(totalAmount)}</span>
            </td>
          </tr>
        `;
      }).join('');
    } else {
      // No books found
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align: center; color: #718096; padding: 30px; font-style: italic;">
            📚 Không có sách nào trong phiếu mượn này
          </td>
        </tr>
      `;
    }
  }

  function displayActiveRecordDetail(borrowDetail) {
    // Display active record info
    let infoHTML = `
      <div class="detail-info-row">
        <span class="detail-info-label">📋 Mã phiếu:</span>
        <span class="detail-info-value">${borrowDetail.MaPhieu || 'N/A'}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">👤 Độc giả:</span>
        <span class="detail-info-value">${getDocGiaName(borrowDetail.doc_gia)} ${borrowDetail.doc_gia && borrowDetail.doc_gia.Email ? `(${borrowDetail.doc_gia.Email})` : ''}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">📅 Ngày mượn:</span>
        <span class="detail-info-value">${formatDate(borrowDetail.NgayMuon)}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">📅 Ngày hẹn trả:</span>
        <span class="detail-info-value">${formatDate(borrowDetail.NgayHenTra)}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">📊 Trạng thái:</span>
        <span class="detail-info-value">
          <span class="status-badge status-${getStatusClass(borrowDetail.TrangThai)}">${getStatusText(borrowDetail.TrangThai)}</span>
        </span>
      </div>
    `;

    document.getElementById('borrowDetailInfo').innerHTML = infoHTML;

    // Display books detail table
    const tableContainer = document.querySelector('.books-detail-table');
    
    let tableHTML = `
      <thead>
        <tr>
          <th style="width: 6%; text-align: center;">STT</th>
          <th style="width: 12%; text-align: center;">Mã Sách</th>
          <th style="width: 30%; text-align: center;">Tên Sách</th>
          <th style="width: 18%; text-align: center;">Thể Loại</th>
          <th style="width: 18%; text-align: center;">Tác Giả</th>
          <th style="width: 16%; text-align: center;">Trạng Thái</th>
        </tr>
      </thead>
      <tbody id="booksDetailTableBody">
      </tbody>
    `;
    
    tableContainer.innerHTML = tableHTML;

    // Display books detail table body
    const tableBody = document.getElementById('booksDetailTableBody');
    if (borrowDetail.chi_tiet_phieu_muon && borrowDetail.chi_tiet_phieu_muon.length > 0) {
      // Display all book details
      tableBody.innerHTML = borrowDetail.chi_tiet_phieu_muon.map((chiTiet, index) => {
        const book = chiTiet.sach;
        if (!book) {
          return `
            <tr>
              <td style="text-align: center; font-weight: 600; color: #667eea;">${index + 1}</td>
              <td colspan="5" style="text-align: center; color: #e53e3e;">
                ⚠️ Sách ID ${chiTiet.sach_id} không tìm thấy
              </td>
            </tr>
          `;
        }
        
        const bookCode = book.MaSach || 'N/A';
        const bookTitle = book.TenSach || 'Không rõ tên sách';
        
        // Handle multiple genres
        let bookGenre = 'Chưa phân loại';
        if (book.the_loais && book.the_loais.length > 0) {
          if (book.the_loais.length === 1) {
            bookGenre = book.the_loais[0].TenTheLoai;
          } else {
            // Multiple genres - create badges for each
            bookGenre = book.the_loais.map(genre => 
              `<span class="book-genre" style="background: #e6fffa; color: #285e61; padding: 2px 6px; border-radius: 8px; font-size: 11px; margin: 1px; display: inline-block;">${genre.TenTheLoai}</span>`
            ).join('');
          }
        }
        
        const bookAuthor = book.tac_gia ? book.tac_gia.TenTacGia : 'Chưa có tác giả';
        
        // Determine book status
        const isReturned = chiTiet.NgayTra !== null;
        const statusText = isReturned ? 'Đã trả' : 'Đang mượn';
        const statusClass = isReturned ? 'status-returned' : 'status-active';
        
        return `
          <tr>
            <td style="text-align: center; font-weight: 600; color: #667eea;">${index + 1}</td>
            <td style="text-align: center;">
              <span class="book-code" style="font-family: monospace; background: #f7fafc; padding: 4px 8px; border-radius: 4px; color: #4a5568;">${bookCode}</span>
            </td>
            <td style="text-align: center;">
              <span class="book-title" style="font-weight: 600; color: #2d3748;">${bookTitle}</span>
            </td>
            <td style="text-align: center;">
              ${bookGenre.includes('<span') ? bookGenre : `<span class="book-genre" style="background: #e6fffa; color: #285e61; padding: 4px 8px; border-radius: 12px; font-size: 12px;">${bookGenre}</span>`}
            </td>
            <td style="text-align: center;">
              <span class="book-author" style="color: #4a5568; font-weight: 500;">${bookAuthor}</span>
            </td>
            <td style="text-align: center;">
              <span class="status-badge ${statusClass}" style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                ${isReturned ? '✅' : '📚'} ${statusText}
              </span>
            </td>
          </tr>
        `;
      }).join('');
    } else {
      // No books found
      tableBody.innerHTML = `
        <tr>
          <td colspan="6" style="text-align: center; color: #718096; padding: 30px; font-style: italic;">
            📚 Không có sách nào trong phiếu mượn này
          </td>
        </tr>
      `;
    }
  }

  // Open extend modal
  function openExtendModal(id) {
    currentExtendId = id;
    const record = borrowRecords.find(r => r.id === id);
    if (!record) return;
    
    // Get first book for display (could be multiple books)
    let bookInfo = 'Không có thông tin sách';
    
    if (record.books && record.books.length > 0) {
      // New API structure
      const firstBook = record.books[0];
      const bookTitle = firstBook.title || 'Không rõ tên sách';
      const bookCode = firstBook.code || 'N/A';
      bookInfo = `${bookCode} - ${bookTitle}`;
    } else if (record.chi_tiet_phieu_muon && record.chi_tiet_phieu_muon.length > 0) {
      // Fallback to old structure
      const firstChiTiet = record.chi_tiet_phieu_muon[0];
      if (firstChiTiet.sach) {
        const bookTitle = firstChiTiet.sach.TenSach || 'Không rõ tên sách';
        const bookCode = firstChiTiet.sach.MaSach || firstChiTiet.sach.id || 'N/A';
        bookInfo = `${bookCode} - ${bookTitle}`;
      }
    }
    
    // Set modal content
    document.getElementById('extendBorrowInfo').textContent = bookInfo;
    document.getElementById('newDueDate').value = record.due_date || record.NgayHenTra;
    document.getElementById('extendDays').value = '';
    
    // Show modal
    document.getElementById('extendModal').style.display = 'block';
  }

  // Extend borrow function
  async function extendBorrow() {
    const newDueDate = document.getElementById('newDueDate').value;
    const extendDays = document.getElementById('extendDays').value;
    
    if (!newDueDate) {
      showToast('Vui lòng chọn ngày hẹn trả mới', 'error');
      return;
    }
    
    if (!extendDays || extendDays <= 0) {
      showToast('Số ngày gia hạn phải lớn hơn 0', 'error');
      return;
    }
    
    try {
      const formData = {
        new_due_date: newDueDate,
        extend_days: extendDays
      };
      
      const response = await fetch(`/api/borrow-records/${currentExtendId}/extend`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
      });
      
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Response is not JSON:', text);
        throw new Error('Server trả về dữ liệu không phải JSON');
      }
      
      const result = await response.json();
      
      if (result.success) {
        showToast(result.message, 'success');
        closeModal('extendModal');
        loadInitialData(); // Reload data
      } else {
        showToast(result.message || 'Có lỗi xảy ra', 'error');
      }
      
    } catch (error) {
      console.error('Error:', error);
      showToast('Lỗi khi gia hạn: ' + error.message, 'error');
    }
  }

  
  // Allow typing number of extension days: sync extendDays -> newDueDate
  function syncExtendDaysToDate() {
    const record = borrowRecords.find(r => r.id === currentExtendId);
    if (!record) return;

    const days = parseInt(document.getElementById('extendDays').value, 10);
    if (!days || days < 1) return;

    const currentDue = new Date(record.due_date);
    if (isNaN(currentDue.getTime())) return;

    currentDue.setDate(currentDue.getDate() + days);

    const yyyy = currentDue.getFullYear();
    const mm = String(currentDue.getMonth() + 1).padStart(2, '0');
    const dd = String(currentDue.getDate()).padStart(2, '0');

    document.getElementById('newDueDate').value = `${yyyy}-${mm}-${dd}`;
  }

function calculateExtendDays() {
    const record = borrowRecords.find(r => r.id === currentExtendId);
    if (!record) return;
    
    const currentDue = new Date(record.due_date);
    const newDue = new Date(document.getElementById('newDueDate').value);
    
    if (newDue > currentDue) {
      const diffTime = Math.abs(newDue - currentDue);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      document.getElementById('extendDays').value = diffDays;
    } else {
      document.getElementById('extendDays').value = '';
    }
  }

  function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = 'none';
    }
  }

  function handleModalOutsideClick(event) {
    if (event.target.classList.contains('modal')) {
      event.target.style.display = 'none';
    }
  }

  // Toast notification
  function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const messageEl = document.getElementById('toast-message');
    
    messageEl.textContent = message;
    toast.className = `toast toast-${type}`;
    toast.style.display = 'block';
    
    setTimeout(() => {
      toast.style.display = 'none';
    }, 3000);
  }

  // Form submission handlers
  async function handleBorrowSubmit(event) {
    event.preventDefault();
    
    if (!selectedReader) {
      showToast('Vui lòng chọn độc giả', 'error');
      return;
    }
    
    // Check books for all cases (new, active, and returned records)
    if (selectedBooks.length === 0) {
      showToast('Vui lòng chọn ít nhất một cuốn sách', 'error');
      return;
    }
    
    const borrowDate = document.getElementById('borrowDate').value;
    if (!borrowDate) {
      showToast('Vui lòng chọn ngày mượn', 'error');
      return;
    }
    
    const dueDate = document.getElementById('borrowDueDate').value;
    if (!dueDate) {
      showToast('Vui lAýng ch ¯?n ngAÿy h §1n tr §œ', 'error');
      return;
    }

    try {
      const formData = {
        MaDocGia: selectedReader.MaDocGia,
        MaSach: selectedBooks.map(book => book.MaSach),
        borrow_date: borrowDate,
        due_date: dueDate
      };
      
      const url = currentEditId ? `/api/borrow-records/${currentEditId}` : '/api/borrow-records';
      const method = currentEditId ? 'PUT' : 'POST';
      
      const response = await fetch(url, {
        method: method,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
      });
      
      const result = await response.json();
      
      if (result.success) {
        showToast(result.message, 'success');
        closeModal('borrowModal');
        loadInitialData();
      } else {
        showToast(result.message || 'Có lỗi xảy ra', 'error');
      }
      
    } catch (error) {
      console.error('Error:', error);
      showToast('Có lỗi xảy ra khi lưu phiếu mượn', 'error');
    }
  }

  async function handleExtendSubmit(event) {
    event.preventDefault();
    await extendBorrow();
  }

  // Return books functionality
  let currentReturnId = null;
  let returnBooksData = null;

  function returnAllBooksInRecord(id) {
    currentReturnId = id;
    const record = borrowRecords.find(r => r.id === id);
    if (!record) {
      showToast('Không tìm thấy phiếu mượn', 'error');
      return;
    }

    // Display borrow record info
    const borrowInfo = document.getElementById('returnBorrowInfo');
    borrowInfo.innerHTML = `
      <div class="detail-info-row">
        <span class="detail-info-label">📋 Mã phiếu:</span>
        <span class="detail-info-value">${record.MaPhieuMuon || record.code || 'N/A'}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">👤 Độc giả:</span>
        <span class="detail-info-value">${record.reader ? record.reader.name : (record.reader_name || 'N/A')}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">📅 Ngày mượn:</span>
        <span class="detail-info-value">${formatDate(record.borrow_date)}</span>
      </div>
      <div class="detail-info-row">
        <span class="detail-info-label">📅 Ngày hẹn trả:</span>
        <span class="detail-info-value">${formatDate(record.due_date)}</span>
      </div>
    `;

    // Load books for return
    loadBooksForReturn(id);
    
    // Reset summary
    document.getElementById('returnSummary').style.display = 'none';
    document.getElementById('confirmReturnBtn').style.display = 'none';
    
    document.getElementById('returnModal').style.display = 'block';
  }

  async function loadBooksForReturn(phieuMuonId) {
    try {
      const response = await fetch(`/api/borrow-records/${phieuMuonId}`);
      const data = await response.json();
      
      if (data.success) {
        const borrowDetail = data.data;
        displayBooksForReturn(borrowDetail);
      } else {
        showToast('Lỗi khi tải thông tin phiếu mượn', 'error');
      }
    } catch (error) {
      console.error('Error loading books for return:', error);
      showToast('Lỗi khi tải thông tin phiếu mượn', 'error');
    }
  }

  function displayBooksForReturn(borrowDetail) {
    const tableBody = document.getElementById('booksReturnTableBody');
    
    if (!borrowDetail.chi_tiet_phieu_muon || borrowDetail.chi_tiet_phieu_muon.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #718096;">Không có sách nào để trả</td></tr>';
      return;
    }

    // Filter only books that haven't been returned yet
    const unreturnedBooks = borrowDetail.chi_tiet_phieu_muon.filter(item => item.NgayTra === null);
    
    if (unreturnedBooks.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #718096;">Tất cả sách đã được trả</td></tr>';
      return;
    }

    tableBody.innerHTML = unreturnedBooks.map((chiTiet, index) => {
      const book = chiTiet.sach;
      if (!book) {
        return `
          <tr>
            <td style="text-align: center; font-weight: 600; color: #667eea;">${index + 1}</td>
            <td colspan="5" style="text-align: center; color: #e53e3e;">
              ⚠️ Sách ID ${chiTiet.sach_id} không tìm thấy
            </td>
          </tr>
        `;
      }
      
      const bookCode = book.MaSach || 'N/A';
      const bookTitle = book.TenSach || 'Không rõ tên sách';
      const bookValue = book.TriGia || 0;
      
      return `
        <tr>
          <td style="text-align: center; font-weight: 600; color: #667eea;">${index + 1}</td>
          <td style="text-align: center;">
            <span class="book-code">${bookCode}</span>
          </td>
          <td style="text-align: left;">
            <div class="book-title">${bookTitle}</div>
          </td>
          <td style="text-align: center; font-weight: 600; color: #2d3748;">
            ${bookValue.toLocaleString('vi-VN')} VNĐ
          </td>
          <td style="text-align: center;">
            <select class="book-status-select" id="book-status-${book.MaSach}" onchange="updateBookStatus(${book.MaSach})">
              <option value="1">📖 Tốt</option>
              <option value="2">⚠️ Hỏng</option>
              <option value="3">❌ Mất</option>
            </select>
          </td>
          <td style="text-align: left; font-size: 12px; color: #718096;">
            <div id="fine-details-${book.MaSach}">Chưa tính phạt</div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function updateBookStatus(bookId) {
    // This function can be used to update fine details in real-time if needed
    console.log('Book status updated for book ID:', bookId);
  }

  async function calculateReturnFines() {
    if (!currentReturnId) {
      showToast('Không tìm thấy phiếu mượn', 'error');
      return;
    }

    try {
      // Get all book IDs and their statuses
      const bookStatusSelects = document.querySelectorAll('.book-status-select');
      const sachIds = [];
      const bookStatuses = {};
      
      bookStatusSelects.forEach(select => {
        const bookId = select.id.replace('book-status-', '');
        sachIds.push(parseInt(bookId));
        bookStatuses[parseInt(bookId)] = parseInt(select.value);
      });

      if (sachIds.length === 0) {
        showToast('Không có sách nào để tính phạt', 'error');
        return;
      }

      const formData = {
        sach_ids: sachIds,
        book_statuses: bookStatuses
      };

      const response = await fetch(`/api/borrow-records/${currentReturnId}/calculate-fines`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
      });

      const result = await response.json();

      if (result.success) {
        returnBooksData = result.data;
        displayReturnSummary(result.data);
        document.getElementById('returnSummary').style.display = 'block';
        document.getElementById('confirmReturnBtn').style.display = 'inline-block';
      } else {
        showToast(result.message || 'Lỗi khi tính phạt', 'error');
      }

    } catch (error) {
      console.error('Error calculating fines:', error);
      showToast('Lỗi khi tính phạt: ' + error.message, 'error');
    }
  }

  function displayReturnSummary(data) {
    // Update summary values
    document.getElementById('totalLateFine').textContent = data.summary.total_late_fine.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('totalCompensation').textContent = data.summary.total_compensation.toLocaleString('vi-VN') + ' VNĐ';
    document.getElementById('totalFine').textContent = data.summary.total_fine.toLocaleString('vi-VN') + ' VNĐ';

    // Update fine details for each book
    data.book_details.forEach(bookDetail => {
      const fineDetailsElement = document.getElementById(`fine-details-${bookDetail.sach_id}`);
      if (fineDetailsElement) {
        fineDetailsElement.innerHTML = `
          <div style="margin-bottom: 5px;">
            <strong>${bookDetail.late_fine_details}</strong>
          </div>
          <div>
            <strong>${bookDetail.compensation_details}</strong>
          </div>
          <div style="margin-top: 5px; color: #e53e3e; font-weight: bold;">
            Tổng: ${bookDetail.tong_tien_phat.toLocaleString('vi-VN')} VNĐ
          </div>
        `;
      }
    });
  }

  async function confirmReturnBooks() {
    if (!currentReturnId || !returnBooksData) {
      showToast('Vui lòng tính phạt trước khi xác nhận trả sách', 'error');
      return;
    }

    if (!confirm('Bạn có chắc chắn muốn trả sách và áp dụng phạt này?')) {
      return;
    }

    try {
      const formData = {
        sach_ids: returnBooksData.book_details.map(book => book.sach_id),
        book_statuses: returnBooksData.book_details.reduce((acc, book) => {
          acc[book.sach_id] = book.tinh_trang_moi;
          return acc;
        }, {})
      };

      const response = await fetch(`/api/borrow-records/${currentReturnId}/return`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formData)
      });

      const result = await response.json();

      if (result.success) {
        showToast(result.message, 'success');
        closeModal('returnModal');
        loadInitialData(); // Reload data
      } else {
        showToast(result.message || 'Lỗi khi trả sách', 'error');
      }

    } catch (error) {
      console.error('Error returning books:', error);
      showToast('Lỗi khi trả sách: ' + error.message, 'error');
    }
  }

  function createFineFromRecord(id) {
    showToast('Chức năng lập phiếu phạt đang phát triển', 'info');
  }

  async function deleteBorrow(id) {
    // Find the record to check if it can be deleted
    const record = borrowRecords.find(r => r.id === id);
    if (!record) {
      showToast('Không tìm thấy phiếu mượn', 'error');
      return;
    }

    // Warning message based on record status
    let confirmMessage = 'Bạn có chắc chắn muốn xóa phiếu mượn này? Hành động này không thể hoàn tác.';
    
    if (record.status === 'returned') {
      confirmMessage = '🗑️ Xác nhận xóa phiếu mượn đã trả\n\nPhiếu mượn "' + record.ma_phieu + '" đã được trả hoàn toàn.\n\nBạn có chắc chắn muốn xóa phiếu mượn này?';
    } else {
      confirmMessage = '⚠️ Cảnh báo: Phiếu mượn đang hoạt động!\n\nPhiếu mượn "' + record.ma_phieu + '" hiện đang hoạt động.\n\nNếu xóa, tình trạng sách sẽ được cập nhật về "Có sẵn" và phiếu mượn sẽ bị xóa hoàn toàn.\n\nBạn có chắc chắn muốn tiếp tục?';
    }

    // Confirm deletion
    if (!confirm(confirmMessage)) {
      return;
    }

    try {
      const response = await fetch(`/api/borrow-records/${id}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (result.success) {
        showToast(result.message, 'success');
        loadInitialData(); // Reload data
      } else {
        showToast(result.message || 'Có lỗi xảy ra khi xóa phiếu mượn', 'error');
      }

    } catch (error) {
      console.error('Error:', error);
      showToast('Lỗi khi xóa phiếu mượn: ' + error.message, 'error');
    }
  }
</script>
@endpush

