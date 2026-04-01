<!DOCTYPE html>
<html lang="en">
<?php
$_SESSION["render"]->setTable("products");
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tồn kho</title>
    <link rel="stylesheet" href="../css/fonts/fonts.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.1-web/css/all.min.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/inventory.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/filter.css?v=<?php echo time(); ?>" />

</head>

<body>
    <div class="inventory-container">
        <div class="filter-section">
            <h2>📦 Tồn kho</h2>
            
            <!-- Alert for Low Stock Products -->
            <div id="lowStockAlert" class="alert alert-warning" style="display: none; margin-bottom: 15px;">
                <i class="fas fa-exclamation-triangle"></i> 
                <span>Có <strong id="lowStockCount">0</strong> sản phẩm sắp hết hàng</span>
            </div>
            
            <!-- Filter for Inventory Tab -->
            <div id="filterInventory" class="filter-group-inventory active-filter">
                <div class="filter-group">
                    <div class="filter-field" style="max-width: 250px;">
                        <label>Chọn ngày</label>
                        <input type="date" id="stockDate" />
                    </div>
                    <div class="filter-field" style="flex: 1; min-width: 250px;">
                        <label>Tìm kiếm sản phẩm</label>
                        <input type="text" id="searchProduct" placeholder="Nhập mã hoặc tên sản phẩm..." />
                    </div>
                    <div class="filter-field" style="max-width: 200px;">
                        <label>Lọc theo trạng thái</label>
                        <select id="statusFilter">
                            <option value="">-- Tất cả --</option>
                            <option value="instock">Còn hàng</option>
                            <option value="outstock">Hết hàng</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn-search" id="searchStock">
                        <i class="fas fa-search"></i> Xem tồn kho
                    </button>
                    <button class="btn-reset" id="resetStock">
                        <i class="fas fa-redo"></i> Tồn kho hiện tại
                    </button>
                </div>
            </div>


        </div>

        <!-- Toggle Button for Settings -->
        <div class="settings-toggle-section">
            <button class="btn-toggle-settings" id="btnToggleSettings">
                <i class="fas fa-chevron-down"></i> <span>Cài đặt mức cảnh báo</span>
            </button>
        </div>

        <!-- Global Default Alert Threshold Section -->
        <div class="global-settings-section" id="globalSettingsSection" style="display: none;">
            <h3><i class="fas fa-sliders-h"></i> Cài đặt mức cảnh báo mặc định</h3>
            <p>Đặt mức cảnh báo sắp hết cho tất cả sản phẩm cùng lúc</p>
            <div class="settings-form">
                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Mức cảnh báo tối thiểu (số lượng):</label>
                        <input type="number" id="globalAlertQty" placeholder="Nhập số lượng" min="0" />
                        <small>Tất cả sản phẩm sẽ được set về mức này</small>
                    </div>
                    <div class="form-actions">
                        <button class="btn-save" id="btnSetBulkAlertQty">
                            <i class="fas fa-check"></i> Áp dụng cho tất cả sản phẩm
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Table -->
        <div id="viewInventory" class="table-section active-view">
            <h3 id="tableTitle">Tồn kho sản phẩm</h3>
            <div class="table-wrapper">
                <table id="stockTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Mã sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th>Nhà cung cấp</th>
                            <th>Tổng nhập</th>
                            <th>Tổng xuất</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody id="stockTableBody">
                        <tr><td colspan="7" class="text-center">Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination-section">
                <div class="pagination-info">
                    <span>Hiển thị <span id="startRecord">0</span> - <span id="endRecord">0</span> trong <span id="totalRecords">0</span> sản phẩm</span>
                </div>
                <div class="pagination-controls">
                    <button class="btn-pagination" id="prevPage" disabled>
                        <i class="fas fa-chevron-left"></i> Trang trước
                    </button>
                    <div class="pagination-numbers" id="paginationNumbers"></div>
                    <button class="btn-pagination" id="nextPage" disabled>
                        Trang sau <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>


    </div>

    <!-- Modal cập nhật mức cảnh báo -->
    <div id="alertQtyModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cập nhật mức cảnh báo</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-info">
                    <p><strong>Sản phẩm:</strong> <span id="modalProductName"></span></p>
                    <p><strong>Tồn kho hiện tại:</strong> <span id="modalCurrentQty"></span></p>
                </div>
                <div class="form-group">
                    <label>Mức cảnh báo tối thiểu (số lượng):</label>
                    <input type="number" id="newAlertQty" placeholder="Nhập số lượng" min="0" />
                    <small>Sản phẩm sẽ được cảnh báo khi tồn kho dưới mức này</small>
                </div>
                <div class="modal-actions">
                    <button class="btn-save" id="btnSaveAlertQty">Lưu</button>
                    <button class="btn-cancel" id="btnCancelAlertQty">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal xem chi tiết giao dịch -->
    <div id="detailModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Chi tiết giao dịch sản phẩm</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-wrapper">
                    <table id="detailTable" class="data-table">
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
                        <tbody id="detailTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script defer src="../js/admin/inventory.js?v=<?php echo time(); ?>"></script>

</body>

</html>
