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
        <div class="filter-section">
            <h2>📦 Quản lý tồn kho</h2>
            <p>Chọn ngày để xem tồn kho của tất cả sản phẩm tại thời điểm đó. Nếu không chọn, sẽ hiển thị tồn kho hiện tại.</p>
            <div class="filter-group">
                <div class="filter-field" style="max-width: 250px;">
                    <label>Chọn ngày</label>
                    <input type="date" id="stockDate" />
                </div>
                <div class="filter-field" style="flex: 1; min-width: 250px;">
                    <label>Tìm kiếm sản phẩm</label>
                    <input type="text" id="searchProduct" placeholder="Nhập mã hoặc tên sản phẩm..." />
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

        <div class="table-section">
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
