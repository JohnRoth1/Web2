<!DOCTYPE html>
<html lang="en">
<?php
$_SESSION["render"]->setTable("products");
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý tồn kho</title>
    <link rel="stylesheet" href="../css/fonts/fonts.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.1-web/css/all.min.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/inventory.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/filter.css?v=<?php echo time(); ?>" />

</head>

<body>
    <div class="inventory-container">
        <!-- Tab Navigation -->
        <div class="inventory-tabs">
            <button class="tab-btn active" data-tab="history-tab">📋 Lịch sử tồn kho</button>
            <button class="tab-btn" data-tab="current-tab">📦 Tồn kho hiện tại</button>
            <button class="tab-btn" data-tab="snapshot-tab">🗓️ Tồn kho tại một thời điểm</button>
        </div>

        <!-- Tab 1: Lịch sử tồn kho -->
        <div id="history-tab" class="tab-content active">
            <div class="filter-section">
                <h2>Tìm kiếm lịch sử tồn kho</h2>
                <div class="filter-group">
                    <div class="filter-field">
                        <label>Mã sản phẩm</label>
                        <input type="text" id="historyProductId" placeholder="Nhập mã sản phẩm" />
                    </div>
                    <div class="filter-field">
                        <label>Tên sản phẩm</label>
                        <input type="text" id="historyProductName" placeholder="Nhập tên sản phẩm" />
                    </div>
                    <div class="filter-field">
                        <label>Loại giao dịch</label>
                        <select id="historyType">
                            <option value="">-- Tất cả --</option>
                            <option value="Nhập">Nhập</option>
                            <option value="Xuất">Xuất</option>
                        </select>
                    </div>
                </div>

                <div class="filter-group">
                    <div class="filter-field">
                        <label>Từ ngày</label>
                        <input type="date" id="historyDateStart" />
                    </div>
                    <div class="filter-field">
                        <label>Đến ngày</label>
                        <input type="date" id="historyDateEnd" />
                    </div>
                </div>

                <div class="filter-actions">
                    <button class="btn-search" id="historySearch">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <button class="btn-reset" id="historyReset">
                        <i class="fas fa-redo"></i> Đặt lại
                    </button>
                </div>
            </div>

            <div class="table-section">
                <h3>Kết quả lịch sử tồn kho</h3>
                <div class="table-wrapper">
                    <table id="historyTable" class="data-table">
                        <thead>
                            <tr>
                                <th>Ngày giao dịch</th>
                                <th>Mã sản phẩm</th>
                                <th>Tên sản phẩm</th>
                                <th>Nhà cung cấp</th>
                                <th>Loại giao dịch</th>
                                <th>Số lượng</th>
                                <th>Người thực hiện</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <tr><td colspan="8" class="text-center">Chọn tiêu chí tìm kiếm</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 2: Tồn kho hiện tại -->
        <div id="current-tab" class="tab-content">
            <div class="filter-section">
                <h2>Tồn kho sản phẩm hiện tại</h2>
                <div class="filter-actions">
                    <button class="btn-search" id="currentRefresh">
                        <i class="fas fa-sync"></i> Làm mới
                    </button>
                </div>
            </div>

            <div class="table-section">
                <h3>Danh sách tồn kho</h3>
                <div class="table-wrapper">
                    <table id="currentTable" class="data-table">
                        <thead>
                            <tr>
                                <th>Mã sản phẩm</th>
                                <th>Tên sản phẩm</th>
                                <th>Nhà cung cấp</th>
                                <th>Tổng nhập</th>
                                <th>Tổng xuất</th>
                                <th>Tồn hiện tại</th>
                                <th>Xem lịch sử</th>
                            </tr>
                        </thead>
                        <tbody id="currentTableBody">
                            <tr><td colspan="7" class="text-center">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 3: Tồn kho tại một thời điểm -->
        <div id="snapshot-tab" class="tab-content">
            <div class="filter-section">
                <h2>Kiểm tra tồn kho tại một thời điểm</h2>
                <div class="filter-group">
                    <div class="filter-field" style="max-width: 250px;">
                        <label>Chọn ngày</label>
                        <input type="date" id="snapshotDate" />
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn-search" id="snapshotSearch">
                        <i class="fas fa-search"></i> Xem tồn kho
                    </button>
                </div>
                <p style="color: #666; margin-top: 10px; font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Hiển thị tồn kho của tất cả sản phẩm tính đến ngày đã chọn
                </p>
            </div>

            <div class="table-section">
                <h3 id="snapshotTableTitle">Kết quả</h3>
                <div class="table-wrapper">
                    <table id="snapshotTable" class="data-table">
                        <thead>
                            <tr>
                                <th>Mã sản phẩm</th>
                                <th>Tên sản phẩm</th>
                                <th>Nhà cung cấp</th>
                                <th>Tổng nhập (đến ngày)</th>
                                <th>Tổng xuất (đến ngày)</th>
                                <th>Tồn kho tại thời điểm</th>
                                <th>Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody id="snapshotTableBody">
                            <tr><td colspan="7" class="text-center">Vui lòng chọn ngày để xem tồn kho</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xem chi tiết lịch sử sản phẩm -->
    <div id="productHistoryModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Lịch sử tồn kho sản phẩm</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="filter-group">
                    <div class="filter-field">
                        <label>Từ ngày</label>
                        <input type="date" id="modalDateStart" />
                    </div>
                    <div class="filter-field">
                        <label>Đến ngày</label>
                        <input type="date" id="modalDateEnd" />
                    </div>
                    <button class="btn-search" id="modalSearch">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
                <div class="table-wrapper">
                    <table id="modalTable" class="data-table">
                        <thead>
                            <tr>
                                <th>Ngày giao dịch</th>
                                <th>Loại giao dịch</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                                <th>Từ/Đến</th>
                                <th>Người thực hiện</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xem chi tiết giao dịch tại thời điểm snapshot -->
    <div id="snapshotDetailModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="snapshotModalTitle">Chi tiết giao dịch sản phẩm</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-wrapper">
                    <table id="snapshotDetailTable" class="data-table">
                        <thead>
                            <tr>
                                <th>Ngày giao dịch</th>
                                <th>Loại</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                                <th>Từ/Đến</th>
                                <th>Người thực hiện</th>
                            </tr>
                        </thead>
                        <tbody id="snapshotDetailTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script defer src="../js/admin/inventory.js?v=<?php echo time(); ?>"></script>

</body>

</html>
