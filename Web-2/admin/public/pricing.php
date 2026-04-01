<!DOCTYPE html>
<html lang="en">
<?php
$_SESSION["render"]->setTable("products");
?>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản lý giá bán</title>
    <link rel="stylesheet" href="../css/fonts/fonts.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../assets/fontawesome-free-6.5.1-web/css/all.min.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/pricing.css?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="../css/admin/filter.css?v=<?php echo time(); ?>" />
</head>

<body>
    <div class="pricing-container">
        <div class="filter-section">
            <h2>💰 Quản lý giá bán</h2>
            <div class="filter-group">
                <div class="filter-field" style="flex: 1; min-width: 250px;">
                    <label>Tìm kiếm sản phẩm</label>
                    <input type="text" id="searchProduct" placeholder="Nhập mã hoặc tên sản phẩm..." />
                </div>
            </div>
        </div>

        <div class="table-section">
            <h3>Danh sách sản phẩm</h3>
            <div class="table-wrapper">
                <table id="pricingTable" class="data-table">
                    <thead>
                        <tr>
                            <th>Mã sản phẩm</th>
                            <th>Tên sản phẩm</th>
                            <th>Nhà cung cấp</th>
                            <th>Giá hiện tại</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="pricingTableBody">
                        <tr><td colspan="5" class="text-center">Đang tải dữ liệu...</td></tr>
                    </tbody>
                </table>
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
                    <p><strong>Giá hiện tại:</strong> <span id="modalCurrentPrice"></span></p>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script defer src="../js/admin/pricing.js?v=<?php echo time(); ?>"></script>
</body>

</html>
