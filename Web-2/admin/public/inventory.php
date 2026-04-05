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

            <div class="inventory-subtabs">
                <button class="subtab-btn active" id="tabCurrentStock">
                    <i class="fas fa-boxes"></i> Tồn kho cuối ngày
                </button>
                <button class="subtab-btn" id="tabRangeStock">
                    <i class="fas fa-calendar-alt"></i> Tồn kho trong khoảng thời gian
                </button>
            </div>
            
            <!-- Alert for Low Stock Products -->
            <div id="lowStockAlert" class="alert alert-warning" style="display: none; margin-bottom: 10px;">
                <i class="fas fa-exclamation-triangle"></i> 
                <span>Có <strong id="lowStockCount">0</strong> sản phẩm sắp hết hàng</span>
            </div>

            <!-- Alert for Out Of Stock Products -->
            <div id="outOfStockAlert" class="alert alert-danger" style="display: none; margin-bottom: 15px;">
                <i class="fas fa-times-circle"></i>
                <span>Có <strong id="outOfStockCount">0</strong> sản phẩm đã hết hàng</span>
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
                            <option value="lowstock">Sắp hết hàng</option>
                            <option value="outstock">Hết hàng</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn-search" id="searchStock">
                        <i class="fas fa-search"></i> Xem tồn kho
                    </button>
                    <button class="btn-reset" id="resetStock">
                        <i class="fas fa-redo"></i> Hôm nay
                    </button>
                </div>
            </div>

            <div id="filterInventoryRange" class="filter-group-inventory" style="display: none;">
                <div class="filter-group">
                    <div class="filter-field" style="max-width: 220px;">
                        <label>Từ ngày</label>
                        <input type="date" id="rangeStartDate" />
                    </div>
                    <div class="filter-field" style="max-width: 220px;">
                        <label>Đến ngày</label>
                        <input type="date" id="rangeEndDate" />
                    </div>
                    <div class="filter-field" style="flex: 1; min-width: 250px;">
                        <label>Tìm kiếm sản phẩm</label>
                        <input type="text" id="searchProductRange" placeholder="Nhập mã hoặc tên sản phẩm..." />
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn-search" id="searchStockRange">
                        <i class="fas fa-search"></i> Xem trong khoảng thời gian
                    </button>
                    <button class="btn-reset" id="resetStockRange">
                        <i class="fas fa-redo"></i> Đặt lại
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
            <h3><i class="fas fa-sliders-h"></i> Cài đặt ngưỡng trạng thái mặc định</h3>
            <p>Thiết lập ngưỡng Sắp hết hàng và Hết hàng cho tất cả sản phẩm. Hết hàng mặc định là 0, bạn có thể đổi nếu cần.</p>
            <div class="settings-form">
                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Ngưỡng sắp hết hàng:</label>
                        <input type="number" id="globalAlertQty" placeholder="Nhập số lượng" min="0" />
                        <small>Sản phẩm có tồn kho lớn hơn ngưỡng hết hàng và nhỏ hơn hoặc bằng ngưỡng này sẽ ở trạng thái sắp hết hàng</small>
                    </div>
                    <div class="form-group">
                        <label>Ngưỡng hết hàng:</label>
                        <input type="number" id="globalOutOfStockQty" placeholder="0" min="0" value="0" />
                        <small>Mặc định là 0. Sản phẩm có tồn kho nhỏ hơn hoặc bằng mức này sẽ ở trạng thái hết hàng</small>
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
            <h3 id="tableTitle">Tồn kho sản phẩm cuối ngày</h3>
            <div class="table-wrapper">
                <table id="stockTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Mã sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th>Nhà cung cấp</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody id="stockTableBody">
                        <tr><td colspan="5" class="text-center">Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container" id="inventoryPaginationContainer">
                <div class="pagination-info">
                    <span>Hiển thị <span id="startRecord">0</span> - <span id="endRecord">0</span> trong <span id="totalRecords">0</span> sản phẩm</span>
                </div>
                <div class="pagination-buttons">
                    <button class="btn-pagination" id="prevPage" disabled>
                        <i class="fas fa-chevron-left"></i> Trang trước
                    </button>
                    <span class="page-numbers" id="paginationNumbers"></span>
                    <button class="btn-pagination" id="nextPage" disabled>
                        Trang sau <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="viewInventoryRange" class="table-section" style="display: none;">
            <h3 id="rangeTableTitle">Tồn kho trong khoảng thời gian</h3>
            <div class="table-wrapper">
                <table id="stockRangeTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Mã sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th>Nhà cung cấp</th>
                            <th>Tổng nhập kỳ</th>
                            <th>Tổng xuất kỳ</th>
                            <th>Biến động kỳ</th>
                            <th>Xem chi tiết</th>
                        </tr>
                    </thead>
                    <tbody id="stockRangeTableBody">
                        <tr><td colspan="7" class="text-center">Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container" id="inventoryRangePaginationContainer" style="display: none;">
                <div class="pagination-info">
                    <span>Hiển thị <span id="rangeStartRecord">0</span> - <span id="rangeEndRecord">0</span> trong <span id="rangeTotalRecords">0</span> sản phẩm</span>
                </div>
                <div class="pagination-buttons">
                    <button class="btn-pagination" id="rangePrevPage" disabled>
                        <i class="fas fa-chevron-left"></i> Trang trước
                    </button>
                    <span class="page-numbers" id="rangePaginationNumbers"></span>
                    <button class="btn-pagination" id="rangeNextPage" disabled>
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
                    <p><strong>Tồn kho cuối ngày đang xem:</strong> <span id="modalCurrentQty"></span></p>
                </div>
                <div class="form-group">
                    <label>Ngưỡng sắp hết hàng:</label>
                    <input type="number" id="newAlertQty" placeholder="Nhập số lượng" min="0" />
                    <small>Sản phẩm có tồn kho lớn hơn ngưỡng hết hàng và nhỏ hơn hoặc bằng mức này sẽ được gắn trạng thái sắp hết hàng</small>
                </div>
                <div class="form-group">
                    <label>Ngưỡng hết hàng:</label>
                    <input type="number" id="newOutOfStockQty" placeholder="0" min="0" value="0" />
                    <small>Mặc định là 0. Bạn có thể thay đổi nếu muốn</small>
                </div>
                <div class="modal-actions">
                    <button class="btn-save" id="btnSaveAlertQty">Lưu</button>
                    <button class="btn-cancel" id="btnCancelAlertQty">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <div id="detailModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="detailModalTitle">Chi tiết nhập/xuất sản phẩm</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-wrapper">
                    <table id="detailTable" class="data-table">
                        <thead>
                            <tr>
                                <th>Ngày giao dịch</th>
                                <th>Loại</th>
                                <th>Mã chứng từ</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                                <th>Từ/Đến</th>
                                <th>Người thực hiện</th>
                                <th>Xem bill/phiếu</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            <tr><td colspan="8" class="text-center">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="documentModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="documentModalTitle">Chi tiết chứng từ</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-info" id="documentMetaInfo"></div>
                <div class="table-wrapper">
                    <table id="documentTable" class="data-table">
                        <thead>
                            <tr>
                                <th>Mã SP</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="documentTableBody">
                            <tr><td colspan="5" class="text-center">Đang tải dữ liệu...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script defer src="../js/admin/inventory.js?v=<?php echo time(); ?>"></script>

</body>

</html>
