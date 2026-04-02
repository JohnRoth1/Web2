<!DOCTYPE html>
<html lang="en">
<?php
$_SESSION["render"]->setTable("products");
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giá bán</title>
    <link rel="stylesheet" href="../css/fonts/fonts.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.1-web/css/all.min.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/pricing.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/filter.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div class="pricing-container">
        <div class="filter-section">
            <h2>💰 Giá bán</h2>
            
            <!-- Subtab Navigation -->
            <div class="subtab-container">
                <button class="subtab-btn active" id="tabByProduct" data-tab="byProduct">
                    <i class="fas fa-tag"></i> Giá sản phẩm
                </button>
                <button class="subtab-btn" id="tabByBatch" data-tab="byBatch">
                    <i class="fas fa-boxes"></i> Giá theo lô hàng
                </button>
            </div>

            <!-- Filter for By Product -->
            <div id="filterByProduct" class="filter-group-by-product active-filter">
                <div class="filter-field" style="flex: 1; min-width: 250px;">
                    <label>Tìm kiếm sản phẩm</label>
                    <input type="text" id="searchProduct" placeholder="Nhập mã hoặc tên sản phẩm..." />
                </div>
            </div>

            <!-- Filter for By Batch -->
            <div id="filterByBatch" class="filter-group-by-batch" style="display: none;">
                <div class="filter-field" style="flex: 1; min-width: 250px;">
                    <label>Tìm kiếm lô hàng / sản phẩm</label>
                    <input type="text" id="searchBatch" placeholder="Nhập mã lô, mã sản phẩm, tên, hoặc nhà cung cấp..." />
                </div>
                <button class="btn-filter" id="btnSearchBatch">
                    <i class="fas fa-search"></i> Tìm
                </button>
            </div>
        </div>

        <!-- Toggle Button for Profit Settings -->
        <div class="settings-toggle-section">
            <button class="btn-toggle-settings" id="btnToggleProfitSettings">
                <i class="fas fa-chevron-down"></i> <span>Tăng % cho tất cả sản phẩm</span>
            </button>
        </div>

        <!-- Profit Margin Adjustment Section -->
        <div class="profit-margin-section" id="profitSettingsSection" style="display: none;">
            <h3><i class="fas fa-percentage"></i> Tăng % Lợi Nhuận</h3>
            <div class="margin-form">
                <div class="form-group-inline">
                    <div class="form-group">
                        <label>Tăng % lợi nhuận (%):</label>
                        <input type="number" id="profitMarginIncrease" placeholder="Nhập số %" min="0" step="0.1" />
                        <small>Tất cả sản phẩm sẽ được tăng % lợi nhuận thêm</small>
                    </div>
                    <div class="form-actions">
                        <button class="btn-save" id="btnIncreaseProfitMargin">
                            <i class="fas fa-check"></i> Áp dụng cho tất cả sản phẩm
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- View: By Product -->
        <div id="viewByProduct" class="table-section active-view">
            <h3>Danh sách sản phẩm</h3>
            <div class="table-wrapper">
                <table id="pricingTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Mã sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th>Nhà cung cấp</th>
                            <th>Giá bán theo lô</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="pricingTableBody">
                        <tr><td colspan="5" class="text-center">Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- View: By Batch -->
        <div id="viewByBatch" class="table-section" style="display: none;">
            <h3>Danh sách lô hàng</h3>
            <div class="table-wrapper">
                <table id="batchTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Mã lô</th>
                            <th>Ngày nhập</th>
                            <th>Số sản phẩm</th>
                            <th>Nhà cung cấp</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody id="batchTableBody">
                        <tr><td colspan="5" class="text-center">Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Batch -->
            <div id="batchPaginationContainer" class="pagination-container" style="display: none;">
                <div class="pagination-info">
                    Hiển thị <span id="batchPageInfo">0-0</span> trong <span id="batchTotalInfo">0</span> lô hàng
                </div>
                <div class="pagination-buttons">
                    <button class="btn-pagination" id="batchPrevBtn"><i class="fas fa-chevron-left"></i> Trước</button>
                    <span class="page-numbers" id="batchPageNumbers"></span>
                    <button class="btn-pagination" id="batchNextBtn">Sau <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cập nhật giá trực tiếp -->
    <div id="directPriceModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nhập giá bán mới</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-info">
                    <p><strong>Sản phẩm:</strong> <span id="modalProductName"></span></p>
                    <p><strong>Giá bán hiện tại:</strong> <span id="modalCurrentPrice"></span></p>
                </div>
                <div class="form-group">
                    <label>Giá bán mới:</label>
                    <input type="number" id="newPrice" placeholder="Nhập giá mới" step="100" />
                </div>
                <div class="modal-actions">
                    <button class="btn-save" id="btnSavePrice">Lưu giá</button>
                    <button class="btn-cancel" id="btnCancelPrice">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal áp dụng lợi nhuận -->
    <div id="marginModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Áp dụng % lợi nhuận</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-info">
                    <p><strong>Sản phẩm:</strong> <span id="modalProductName2"></span></p>
                    <p><strong>Giá nhập:</strong> <span id="modalInputPrice"></span></p>
                </div>
                <div class="form-group">
                    <label>% lợi nhuận:</label>
                    <input type="number" id="marginPercent" placeholder="Nhập %" step="0.01" />
                    <small>Ví dụ: 20 = 20% lợi nhuận</small>
                </div>
                <div class="form-group" id="calculatedPriceGroup" style="display: none;">
                    <label>Giá bán dự tính:</label>
                    <p id="calculatedPrice" style="font-size: 18px; font-weight: bold; color: #4CAF50;"></p>
                </div>
                <div class="modal-actions">
                    <button class="btn-save" id="btnSaveMargin">Áp dụng</button>
                    <button class="btn-cancel" id="btnCancelMargin">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal chi tiết lô hàng -->
    <div id="batchDetailsModal" class="modal-overlay">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>Chi tiết lô hàng <span id="modalBatchId"></span></h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="batch-info-section">
                    <p><strong>Ngày nhập:</strong> <span id="modalBatchDate"></span></p>
                    <p><strong>Nhà cung cấp:</strong> <span id="modalBatchSupplier"></span></p>
                </div>

                <table id="batchDetailsTable" class="data-table batch-details-table">
                    <thead>
                        <tr>
                            <th>Mã sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá vốn (lô)</th>
                            <th>% Lợi nhuận</th>
                            <th>Giá bán hiện tại</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="batchDetailsBody">
                        <tr><td colspan="7" class="text-center">Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="btn-close" id="btnCloseBatchDetails">Đóng</button>
            </div>
        </div>
    </div>

    <!-- Modal sửa margin từ batch detail -->
    <div id="batchMarginModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cập nhật % lợi nhuận</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-info">
                    <p><strong>Sản phẩm:</strong> <span id="batchModalProductName"></span></p>
                    <p><strong>Giá nhập bình quân:</strong> <span id="batchModalAverageCost">0 đ</span></p>
                </div>
                <div class="form-group">
                    <label>% lợi nhuận:</label>
                    <input type="number" id="batchMarginInput" placeholder="Nhập %" step="0.01" />
                    <small>Ví dụ: 20 = 20% lợi nhuận</small>
                </div>
                <div class="form-group" id="batchCalculatedPriceGroup" style="display: none;">
                    <label>Giá bán dự tính:</label>
                    <p id="batchCalculatedPrice" style="font-size: 18px; font-weight: bold; color: #4CAF50;"></p>
                </div>
                <div class="modal-actions">
                    <button class="btn-save" id="btnSaveBatchMargin">Lưu</button>
                    <button class="btn-cancel" id="btnCancelBatchMargin">Hủy</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script defer src="../js/admin/pricing.js?v=<?php echo time(); ?>"></script>
</body>

</html>
