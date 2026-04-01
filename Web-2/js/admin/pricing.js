let allProducts = [];
let currentProductId = null;
let currentProductInputPrice = null;
let currentProductAverageCostPrice = null;
let currentView = 'byProduct'; // byProduct or byBatch
let currentPage = 1;
let totalPages = 1;
let allBatches = [];
let currentBatchSearch = '';
let currentBatchId = null;
let currentBatchProductName = null;

$(document).ready(function () {
  loadProducts();
  initializeEventListeners();
});

function initializeEventListeners() {
  // Subtab switching
  $("#tabByProduct").click(function () {
    switchView('byProduct');
  });

  $("#tabByBatch").click(function () {
    switchView('byBatch');
  });

  // Direct price modal close
  $(".modal-close").click(function () {
    $(this).closest(".modal-overlay").removeClass("active");
  });

  // Close modal when clicking outside
  $(document).click(function (event) {
    if (event.target.classList.contains("modal-overlay")) {
      $(event.target).removeClass("active");
    }
  });

  // Product view: Search
  $("#searchProduct").on("input", function () {
    filterProducts();
  });

  // Batch view: Search
  $("#btnSearchBatch").click(function () {
    currentBatchSearch = $("#searchBatch").val();
    currentPage = 1;
    loadReceipts();
  });

  // Batch view: Search on Enter
  $("#searchBatch").keypress(function (e) {
    if (e.which === 13) {
      $("#btnSearchBatch").click();
    }
  });

  // Save direct price
  $("#btnSavePrice").click(function () {
    const newPrice = $("#newPrice").val();
    if (!newPrice || newPrice <= 0) {
      alert("Vui lòng nhập giá hợp lệ!");
      return;
    }
    
    console.log("Saving price for product:", currentProductId);
    $.ajax({
      url: "../controller/admin/pricing.controller.php",
      type: "POST",
      dataType: "json",
      data: {
        function: "updatePrice",
        product_id: currentProductId,
        price: newPrice
      }
    }).done(function (result) {
      if (result.success) {
        alert("Cập nhật giá thành công!");
        $("#directPriceModal").removeClass("active");
        loadProducts();
      } else {
        alert("Cập nhật giá thất bại!");
      }
    }).fail(function () {
      alert("Lỗi khi cập nhật giá!");
    });
  });

  // Cancel direct price
  $("#btnCancelPrice").click(function () {
    $("#directPriceModal").removeClass("active");
  });

  // Save margin
  $("#btnSaveMargin").click(function () {
    const margin = $("#marginPercent").val();
    if (!margin || margin < 0) {
      alert("Vui lòng nhập % lợi nhuận hợp lệ!");
      return;
    }
    
    console.log("Applying margin for product:", currentProductId);
    $.ajax({
      url: "../controller/admin/pricing.controller.php",
      type: "POST",
      dataType: "json",
      data: {
        function: "applyMargin",
        product_id: currentProductId,
        margin: margin
      }
    }).done(function (result) {
      if (result.success) {
        alert(`Cập nhật giá thành công!\nGiá nhập bình quân: ${formatPrice(result.cost_price)}\nGiá bán mới: ${formatPrice(result.new_price)}`);
        $("#marginModal").removeClass("active");
        loadProducts();
      } else {
        alert("Lỗi: " + (result.message || "Không thể áp dụng lợi nhuận"));
      }
    }).fail(function () {
      alert("Lỗi khi áp dụng lợi nhuận!");
    });
  });

  // Cancel margin
  $("#btnCancelMargin").click(function () {
    $("#marginModal").removeClass("active");
  });

  // Real-time margin calculation
  $("#marginPercent").on("input", function () {
    calculateMarginPrice();
  });

  // Batch Details Modal - Close
  $("#btnCloseBatchDetails").click(function () {
    $("#batchDetailsModal").removeClass("active");
  });

  // Batch Details - Edit margin button
  $(document).on("click", ".btn-edit-batch-margin", function () {
    const productId = $(this).data("product-id");
    const productName = $(this).data("product-name");
    const averageCost = parseFloat($(this).data("average-cost"));
    openBatchMarginModal(productId, productName, averageCost);
  });

  // Batch Margin Modal - Save
  $("#btnSaveBatchMargin").click(function () {
    const margin = $("#batchMarginInput").val();
    if (!margin || margin < 0) {
      alert("Vui lòng nhập % lợi nhuận hợp lệ!");
      return;
    }

    $.ajax({
      url: "../controller/admin/pricing.controller.php",
      type: "POST",
      dataType: "json",
      data: {
        function: "applyMargin",
        product_id: currentBatchId,
        margin: margin
      }
    }).done(function (result) {
      if (result.success) {
        alert(`Cập nhật giá thành công!\nGiá nhập bình quân: ${formatPrice(result.cost_price)}\nGiá bán mới: ${formatPrice(result.new_price)}`);
        $("#batchMarginModal").removeClass("active");
        loadReceipts();
      } else {
        alert("Lỗi: " + (result.message || "Không thể áp dụng lợi nhuận"));
      }
    }).fail(function () {
      alert("Lỗi khi áp dụng lợi nhuận!");
    });
  });

  // Batch Margin Modal - Cancel
  $("#btnCancelBatchMargin").click(function () {
    $("#batchMarginModal").removeClass("active");
  });

  // Real-time batch margin calculation
  $("#batchMarginInput").on("input", function () {
    calculateBatchMarginPrice();
  });

  // Batch view - Pagination
  $(document).on("click", "#batchPrevBtn", function () {
    if (currentPage > 1) {
      currentPage--;
      loadReceipts();
    }
  });

  $(document).on("click", "#batchNextBtn", function () {
    if (currentPage < totalPages) {
      currentPage++;
      loadReceipts();
    }
  });

  $(document).on("click", ".batch-page-btn", function () {
    currentPage = parseInt($(this).data("page"));
    loadReceipts();
  });

  // Batch view - View details button
  $(document).on("click", ".btn-view-batch-details", function () {
    const batchId = $(this).data("batch-id");
    viewBatchDetails(batchId);
  });
}

function switchView(view) {
  currentView = view;
  currentPage = 1;

  // Update subtab buttons
  $(".subtab-btn").removeClass("active");
  if (view === 'byProduct') {
    $("#tabByProduct").addClass("active");
    $("#filterByProduct").show();
    $("#filterByBatch").hide();
    $("#viewByProduct").show();
    $("#viewByBatch").hide();
    loadProducts();
  } else {
    $("#tabByBatch").addClass("active");
    $("#filterByProduct").hide();
    $("#filterByBatch").show();
    $("#viewByProduct").hide();
    $("#viewByBatch").show();
    loadReceipts();
  }
}

function loadProducts() {
  console.log("Loading products...");
  $.ajax({
    url: "../controller/admin/pricing.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getProducts"
    }
  }).done(function (result) {
    allProducts = result;
    displayProducts(result);
    console.log("Products loaded:", result);
  }).fail(function (jqXHR, textStatus, errorThrown) {
    console.error("AJAX Error Details:", {
      status: jqXHR.status,
      statusText: jqXHR.statusText,
      responseText: jqXHR.responseText,
      textStatus: textStatus,
      errorThrown: errorThrown,
      contentType: jqXHR.getResponseHeader("content-type")
    });
    alert("Lỗi tải danh sách sản phẩm!\n\nStatus: " + jqXHR.status + "\nResponse: " + jqXHR.responseText);
  });
}

function displayProducts(products) {
  const tbody = $("#pricingTableBody");
  tbody.empty();
  
  if (products.length === 0) {
    tbody.append("<tr><td colspan='5' class='text-center'>Không có sản phẩm</td></tr>");
    return;
  }

  products.forEach(function (product) {
    const html = `
      <tr>
        <td>${product.id}</td>
        <td>${product.name}</td>
        <td>${product.supplier_name}</td>
        <td class="price-column">${formatPrice(product.price)}</td>
        <td>
          <button class="btn-action btn-edit-price" onclick="openDirectPriceModal(${product.id}, '${product.name}', ${product.price})">
            <i class="fas fa-tag"></i> Nhập giá
          </button>
          <button class="btn-action btn-edit-margin" onclick="openMarginModal(${product.id}, '${product.name}')">
            <i class="fas fa-percent"></i> % Lợi nhuận
          </button>
        </td>
      </tr>
    `;
    tbody.append(html);
  });
}

function filterProducts() {
  const searchTerm = $("#searchProduct").val().toLowerCase();
  
  const filtered = allProducts.filter(product => {
    const idMatch = product.id.toString().includes(searchTerm);
    const nameMatch = product.name.toLowerCase().includes(searchTerm);
    const supplierMatch = product.supplier_name.toLowerCase().includes(searchTerm);
    
    return idMatch || nameMatch || supplierMatch;
  });
  
  displayProducts(filtered);
}

function openDirectPriceModal(productId, productName, currentPrice) {
  currentProductId = productId;
  $("#modalProductName").text(productName);
  $("#modalCurrentPrice").text(formatPrice(currentPrice));
  $("#newPrice").val(currentPrice);
  $("#directPriceModal").addClass("active");
  setTimeout(() => $("#newPrice").focus(), 100);
}

function openMarginModal(productId, productName) {
  currentProductId = productId;
  
  // Fetch average cost price from server
  $.ajax({
    url: "../controller/admin/pricing.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getAverageCostPrice",
      product_id: productId
    }
  }).done(function (result) {
    if (result.success) {
      currentProductAverageCostPrice = parseFloat(result.average_cost_price);
      
      $("#modalProductName2").text(productName);
      $("#modalInputPrice").text(formatPrice(currentProductAverageCostPrice));
      $("#marginPercent").val("");
      $("#calculatedPriceGroup").hide();
      $("#marginModal").addClass("active");
      setTimeout(() => $("#marginPercent").focus(), 100);
    } else {
      alert("Không thể lấy giá nhập bình quân: " + (result.message || "Lỗi không xác định"));
    }
  }).fail(function () {
    alert("Lỗi khi lấy giá nhập bình quân!");
  });
}

function calculateMarginPrice() {
  const margin = parseFloat($("#marginPercent").val()) || 0;
  
  if (margin < 0 || !currentProductAverageCostPrice) {
    $("#calculatedPriceGroup").hide();
    return;
  }
  
  // Calculate selling price using average cost price: Giá bán = Giá nhập × (100% + lợi nhuận%)
  const calculatedPrice = currentProductAverageCostPrice * (1 + margin / 100);
  
  if (margin > 0) {
    $("#calculatedPrice").text(formatPrice(calculatedPrice));
    $("#calculatedPriceGroup").show();
  } else {
    $("#calculatedPriceGroup").hide();
  }
}

// ============ BATCH VIEW FUNCTIONS ============

function loadReceipts() {
  console.log("Loading receipts. Page:", currentPage, "Search:", currentBatchSearch);
  
  $.ajax({
    url: "../controller/admin/pricing.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getReceipts",
      search: currentBatchSearch,
      page: currentPage
    }
  }).done(function (result) {
    console.log("Receipts loaded:", result);
    displayReceipts(result);
    updateBatchPagination(result);
  }).fail(function (jqXHR, textStatus, errorThrown) {
    console.error("AJAX Error:", jqXHR.responseText);
    alert("Lỗi tải danh sách lô hàng!");
  });
}

function displayReceipts(result) {
  const tbody = $("#batchTableBody");
  tbody.empty();

  if (!result.data || result.data.length === 0) {
    tbody.append("<tr><td colspan='5' class='text-center'>Không có lô hàng</td></tr>");
    return;
  }

  result.data.forEach(function (batch) {
    const html = `
      <tr>
        <td>${batch.batch_id}</td>
        <td>${batch.date_create}</td>
        <td>${batch.product_count}</td>
        <td>${batch.supplier_name || '-'}</td>
        <td>
          <button class="btn-action btn-view-batch-details" data-batch-id="${batch.batch_id}">
            <i class="fas fa-eye"></i> Xem
          </button>
        </td>
      </tr>
    `;
    tbody.append(html);
  });
}

function updateBatchPagination(result) {
  const total = result.total;
  const pages = result.totalPages;
  const page = result.currentPage;
  const perPage = result.perPage;

  totalPages = pages;

  if (pages <= 1) {
    $("#batchPaginationContainer").hide();
    return;
  }

  $("#batchPaginationContainer").show();

  const start = (page - 1) * perPage + 1;
  const end = Math.min(page * perPage, total);

  $("#batchPageInfo").text(`${start}-${end}`);
  $("#batchTotalInfo").text(total);

  // Disable prev/next buttons
  $("#batchPrevBtn").prop("disabled", page === 1);
  $("#batchNextBtn").prop("disabled", page === pages);

  // Generate page numbers
  let pageNumbers = "";
  const maxPages = 5;
  let startPage = Math.max(1, page - 2);
  let endPage = Math.min(pages, startPage + maxPages - 1);

  if (endPage - startPage < maxPages - 1) {
    startPage = Math.max(1, endPage - maxPages + 1);
  }

  for (let i = startPage; i <= endPage; i++) {
    const activeClass = i === page ? "active" : "";
    pageNumbers += `<button class="batch-page-btn ${activeClass}" data-page="${i}">${i}</button>`;
  }

  $("#batchPageNumbers").html(pageNumbers);
}

function viewBatchDetails(batchId) {
  console.log("Loading batch details for:", batchId);

  $.ajax({
    url: "../controller/admin/pricing.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getBatchDetails",
      batch_id: batchId
    }
  }).done(function (result) {
    console.log("Batch details loaded:", result);
    displayBatchDetails(result);
    $("#batchDetailsModal").addClass("active");
  }).fail(function () {
    alert("Lỗi tải chi tiết lô hàng!");
  });
}

function displayBatchDetails(result) {
  const batchInfo = result.batchInfo;
  const products = result.products;

  // Update modal header
  $("#modalBatchId").text(`#${batchInfo.batch_id}`);
  $("#modalBatchDate").text(batchInfo.batch_date);
  $("#modalBatchSupplier").text(batchInfo.supplier_name || '-');

  // Display products
  const tbody = $("#batchDetailsBody");
  tbody.empty();

  if (!products || products.length === 0) {
    tbody.append("<tr><td colspan='7' class='text-center'>Không có sản phẩm</td></tr>");
    return;
  }

  products.forEach(function (product) {
    const html = `
      <tr>
        <td>${product.product_id}</td>
        <td>${product.product_name}</td>
        <td>${product.input_quantity}</td>
        <td>${formatPrice(product.cost_price)}</td>
        <td>${product.margin_percent}%</td>
        <td>${formatPrice(product.current_selling_price)}</td>
        <td>
          <button class="btn-action btn-edit-batch-margin" 
                  data-product-id="${product.product_id}" 
                  data-product-name="${product.product_name}"
                  data-average-cost="${product.cost_price}">
            <i class="fas fa-percent"></i> Sửa
          </button>
        </td>
      </tr>
    `;
    tbody.append(html);
  });
}

function openBatchMarginModal(productId, productName, averageCost) {
  currentBatchId = productId;
  currentBatchProductName = productName;
  currentProductAverageCostPrice = averageCost;

  $("#batchModalProductName").text(productName);
  $("#batchModalAverageCost").text(formatPrice(averageCost));
  $("#batchMarginInput").val("");
  $("#batchCalculatedPriceGroup").hide();
  $("#batchMarginModal").addClass("active");
  
  setTimeout(() => $("#batchMarginInput").focus(), 100);
}

function calculateBatchMarginPrice() {
  const margin = parseFloat($("#batchMarginInput").val()) || 0;

  if (margin < 0 || !currentProductAverageCostPrice) {
    $("#batchCalculatedPriceGroup").hide();
    return;
  }

  // Calculate selling price: cost × (1 + margin%)
  const calculatedPrice = currentProductAverageCostPrice * (1 + margin / 100);

  if (margin > 0) {
    $("#batchCalculatedPrice").text(formatPrice(calculatedPrice));
    $("#batchCalculatedPriceGroup").show();
  } else {
    $("#batchCalculatedPriceGroup").hide();
  }
}

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
}
