// Load jQuery
var script = document.createElement("SCRIPT");
script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js";
script.type = "text/javascript";
document.getElementsByTagName("head")[0].appendChild(script);
console.log("jQuery script loading...");

let currentProductId = null;
let currentDate = null;
let currentPage = 1;
let totalPages = 1;
let currentView = "current";
let currentRangePage = 1;
let totalRangePages = 1;
let currentRangeStartDate = null;
let currentRangeEndDate = null;
let currentDocumentType = null;
let currentDocumentId = null;
let allData = [];
let filteredData = [];

function getInventoryFilters() {
  return {
    search: $("#searchProduct").val().trim(),
    status: $("#statusFilter").val()
  };
}

function getRangeFilters() {
  return {
    search: $("#searchProductRange").val().trim()
  };
}

// Wait for jQuery to load
function checkJQueryReady() {
  return new Promise(async function (resolve) {
    while (!window.jQuery) {
      console.log("Waiting for jQuery...");
      await new Promise(resolve => setTimeout(resolve, 20));
    }
    console.log("jQuery ready!");
    resolve();
  });
}

// Initialize on page load
$(document).ready(async function () {
  await checkJQueryReady();
  initializeEventListeners();
  setDefaultDate();
  setDefaultRangeDates();
  loadDefaultThresholds();
  setTimeout(() => {
    loadStock(); // Tải tồn kho hiện tại khi mở trang

    updateLowStockAlert(); // Cập nhật cảnh báo sản phẩm sắp hết hàng
  }, 500);
});

function setDefaultDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  $("#stockDate").val(`${year}-${month}-${day}`);
  currentDate = `${year}-${month}-${day}`;
  $("#tabCurrentStock").html('<i class="fas fa-boxes"></i> Tồn kho cuối ngày');
}

function setDefaultRangeDates() {
  const today = new Date();
  const startDate = new Date();
  startDate.setDate(today.getDate() - 30);

  const formatDateInput = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  };

  currentRangeStartDate = formatDateInput(startDate);
  currentRangeEndDate = formatDateInput(today);

  $("#rangeStartDate").val(currentRangeStartDate);
  $("#rangeEndDate").val(currentRangeEndDate);
}

function initializeEventListeners() {
  console.log("Initializing event listeners...");

  $("#tabCurrentStock").click(function () {
    switchInventoryTab("current");
  });

  $("#tabRangeStock").click(function () {
    switchInventoryTab("range");
  });

  $(document).on("click", ".btn-view-detail", function () {
    const productId = $(this).data("product-id");
    const productName = $(this).data("product-name");
    const date = $(this).data("date") || currentDate;
    viewDetail(productId, productName, date);
  });

  $(document).on("click", ".btn-view-range-detail", function () {
    const productId = $(this).data("product-id");
    const productName = $(this).data("product-name");
    const date = $(this).data("date") || currentRangeEndDate;
    viewDetail(productId, productName, date);
  });

  $(document).on("click", ".btn-view-document", function () {
    const documentType = $(this).data("document-type");
    const documentId = $(this).data("document-id");
    if (!documentType || !documentId) {
      return;
    }
    loadTransactionDocument(documentType, documentId);
  });
  
  // Toggle Settings Section
  $("#btnToggleSettings").click(function () {
    const $section = $("#globalSettingsSection");
    const $button = $(this);
    const $icon = $button.find("i");
    const $text = $button.find("span");
    
    if ($section.is(":hidden")) {
      $section.slideDown(300);
      $icon.removeClass("fa-chevron-down").addClass("fa-chevron-up");
      $text.text("Ẩn cài đặt");
    } else {
      $section.slideUp(300);
      $icon.removeClass("fa-chevron-up").addClass("fa-chevron-down");
      $text.text("Cài đặt mức cảnh báo");
    }
  });
  
  // Alert Qty Modal - Save
  $("#btnSaveAlertQty").click(function () {
    const alertQty = $("#newAlertQty").val();
    const outOfStockQty = $("#newOutOfStockQty").val();
    if (!isValidThresholds(alertQty, outOfStockQty)) {
      return;
    }
    
    $.ajax({
      url: "../controller/admin/inventory.controller.php",
      type: "POST",
      dataType: "json",
      data: {
        function: "updateAlertQty",
        product_id: currentProductId,
        alert_qty: alertQty,
        out_of_stock_qty: outOfStockQty
      }
    }).done(function (result) {
      if (result.success) {
        alert("Cập nhật mức cảnh báo thành công!");
        $("#alertQtyModal").removeClass("active");
        loadStock(currentDate, currentPage);        updateLowStockAlert();      } else {
        alert("Cập nhật thất bại!");
      }
    }).fail(function () {
      alert("Lỗi khi cập nhật!");
    });
  });
  
  // Alert Qty Modal - Cancel
  $("#btnCancelAlertQty").click(function () {
    $("#alertQtyModal").removeClass("active");
  });
  
  // Low Stock Edit Button
  $(document).on("click", ".btn-edit-alert", function () {
    currentProductId = $(this).data("product-id");
    const productName = $(this).data("product-name");
    const currentQty = $(this).data("current-qty");
    const alertQty = $(this).data("alert-qty");
    const outOfStockQty = $(this).data("out-of-stock-qty") || 0;
    
    $("#modalProductName").text(productName);
    $("#modalCurrentQty").text(currentQty);
    $("#newAlertQty").val(alertQty);
    $("#newOutOfStockQty").val(outOfStockQty);
    $("#alertQtyModal").addClass("active");
    setTimeout(() => $("#newAlertQty").focus(), 100);
  });

  // Bulk Alert Qty - Apply to all products
  $("#btnSetBulkAlertQty").click(function () {
    const alertQty = $("#globalAlertQty").val();
    const outOfStockQty = $("#globalOutOfStockQty").val();
    if (!isValidThresholds(alertQty, outOfStockQty)) {
      return;
    }

    const confirmed = confirm(
      `Bạn chắc chắn muốn đặt ngưỡng sắp hết hàng = ${alertQty} và ngưỡng hết hàng = ${outOfStockQty} cho TẤT CẢ sản phẩm?`
    );
    if (!confirmed) return;

    $(this).prop("disabled", true).text("Đang xử lý...");

    $.ajax({
      url: "../controller/admin/inventory.controller.php",
      type: "POST",
      dataType: "json",
      data: {
        function: "setBulkAlertQty",
        alert_qty: alertQty,
        out_of_stock_qty: outOfStockQty
      }
    }).done(function (result) {
      console.log("BulkAlertQty Response:", result);
      if (result && result.success) {
        alert("Cập nhật ngưỡng trạng thái cho tất cả sản phẩm thành công!");
        loadStock(currentDate, currentPage);
        updateLowStockAlert();
        loadDefaultThresholds();
      } else {
        const errorMsg = result && result.message ? result.message : "Cập nhật thất bại!";
        alert(errorMsg);
      }
    }).fail(function (jqXHR, textStatus, errorThrown) {
      console.error("BulkAlertQty Error:", textStatus, errorThrown);
      console.error("Response Text:", jqXHR.responseText);
      alert("Lỗi khi cập nhật! " + errorThrown);
    }).always(function () {
      $("#btnSetBulkAlertQty").prop("disabled", false).text("Áp dụng cho tất cả sản phẩm");
    });
  });
  
  // Search button
  $("#searchStock").click(function () {
    console.log("Search Stock button clicked");
    const selectedDate = $("#stockDate").val();
    if (selectedDate) {
      currentDate = selectedDate;
      currentPage = 1;
      loadStock(selectedDate, 1);
    }
  });

  // Search button for range tab
  $("#searchStockRange").click(function () {
    const startDate = $("#rangeStartDate").val();
    const endDate = $("#rangeEndDate").val();

    if (!startDate || !endDate) {
      alert("Vui lòng chọn đầy đủ từ ngày và đến ngày!");
      return;
    }

    if (startDate > endDate) {
      alert("Từ ngày không được lớn hơn đến ngày!");
      return;
    }

    currentRangeStartDate = startDate;
    currentRangeEndDate = endDate;
    currentRangePage = 1;
    loadStockRange(currentRangeStartDate, currentRangeEndDate, 1);
  });

  // Reset button - back to today
  $("#resetStock").click(function () {
    console.log("Reset Stock button clicked");
    setDefaultDate();
    $("#searchProduct").val("");
    $("#statusFilter").val("");
    currentPage = 1;
    loadStock();
  });

  $("#resetStockRange").click(function () {
    setDefaultRangeDates();
    $("#searchProductRange").val("");
    currentRangePage = 1;
    loadStockRange(currentRangeStartDate, currentRangeEndDate, 1);
  });

  // Real-time search
  $("#searchProduct").on("input", function () {
    currentPage = 1;
    loadStock(currentDate, 1);
  });

  // Status filter
  $("#statusFilter").on("change", function () {
    currentPage = 1;
    loadStock(currentDate, 1);
  });

  $("#searchProductRange").on("input", function () {
    currentRangePage = 1;
    loadStockRange(currentRangeStartDate, currentRangeEndDate, 1);
  });

  // Pagination buttons
  $("#prevPage").click(function () {
    if (currentPage > 1) {
      currentPage--;
      loadStock(currentDate, currentPage);
    }
  });

  $("#nextPage").click(function () {
    if (currentPage < totalPages) {
      currentPage++;
      loadStock(currentDate, currentPage);
    }
  });

  $("#rangePrevPage").click(function () {
    if (currentRangePage > 1) {
      currentRangePage--;
      loadStockRange(currentRangeStartDate, currentRangeEndDate, currentRangePage);
    }
  });

  $("#rangeNextPage").click(function () {
    if (currentRangePage < totalRangePages) {
      currentRangePage++;
      loadStockRange(currentRangeStartDate, currentRangeEndDate, currentRangePage);
    }
  });

  // Pagination number click
  $(document).on("click", ".inventory-page-btn", function () {
    const page = parseInt($(this).data("page"));
    if (page !== currentPage) {
      currentPage = page;
      loadStock(currentDate, currentPage);
    }
  });

  $(document).on("click", ".inventory-range-page-btn", function () {
    const page = parseInt($(this).data("page"), 10);
    if (page !== currentRangePage) {
      currentRangePage = page;
      loadStockRange(currentRangeStartDate, currentRangeEndDate, currentRangePage);
    }
  });

  // Modal close button
  $(".modal-close").click(function () {
    $(this).closest(".modal-overlay").removeClass("active");
    currentProductId = null;
  });

  // Close modal when clicking outside
  $(document).click(function (event) {
    if (event.target.classList.contains("modal-overlay")) {
      $(event.target).removeClass("active");
      currentProductId = null;
    }
  });

  // Enter key to search
  $("#stockDate").keypress(function (e) {
    if (e.which == 13) {
      e.preventDefault();
      $("#searchStock").click();
    }
  });
  
  console.log("Event listeners initialized");
}

function switchInventoryTab(tab) {
  currentView = tab;
  $(".subtab-btn").removeClass("active");

  if (tab === "current") {
    $("#tabCurrentStock").addClass("active");
    $("#filterInventory").show();
    $("#filterInventoryRange").hide();
    $("#viewInventory").show();
    $("#viewInventoryRange").hide();
    loadStock(currentDate, currentPage || 1);
  } else {
    $("#tabRangeStock").addClass("active");
    $("#filterInventory").hide();
    $("#filterInventoryRange").show();
    $("#viewInventory").hide();
    $("#viewInventoryRange").show();
    loadStockRange(currentRangeStartDate, currentRangeEndDate, currentRangePage || 1);
  }
}

function loadStock(date = null, page = 1) {
  const searchDate = date || currentDate || new Date().toISOString().split('T')[0];
  const filters = getInventoryFilters();
  currentPage = page;
  
  console.log("Loading stock for date:", searchDate, "page:", page, "filters:", filters);
  
  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getStockAtDate",
      date: searchDate,
      page: page,
      search: filters.search,
      status: filters.status
    },
  }).done(function (result) {
    console.log("Stock data loaded:", result);
    displayTable(result, searchDate);
  }).fail(function (jqXHR, textStatus, errorThrown) {
    console.error("AJAX Error Details:", {
      status: jqXHR.status,
      statusText: jqXHR.statusText,
      responseText: jqXHR.responseText,
      textStatus: textStatus,
      errorThrown: errorThrown,
      contentType: jqXHR.getResponseHeader("content-type")
    });
    alert("Lỗi tải dữ liệu tồn kho!\n\nStatus: " + jqXHR.status + "\nResponse: " + jqXHR.responseText);
  });
}

function loadStockRange(startDate = null, endDate = null, page = 1) {
  const start = startDate || currentRangeStartDate;
  const end = endDate || currentRangeEndDate;
  const filters = getRangeFilters();
  currentRangePage = page;

  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getStockInRange",
      date_start: start,
      date_end: end,
      page: page,
      search: filters.search
    },
  }).done(function (result) {
    displayRangeTable(result, start, end);
  }).fail(function (jqXHR) {
    alert("Lỗi tải dữ liệu tồn kho theo khoảng thời gian!\n\nStatus: " + jqXHR.status + "\nResponse: " + jqXHR.responseText);
  });
}

function displayRangeTable(response, startDate, endDate) {
  const data = response.data || [];
  const total = response.total || 0;
  const page = response.page || 1;
  const perPage = response.perPage || 8;
  totalRangePages = response.totalPages || 1;

  const tbody = $("#stockRangeTableBody");
  tbody.empty();

  const formattedStart = formatDate(startDate);
  const formattedEnd = formatDate(endDate);
  $("#rangeTableTitle").text(`Tồn kho trong khoảng thời gian (${formattedStart} - ${formattedEnd})`);

  if (data.length === 0) {
    tbody.append("<tr><td colspan='7' class='text-center'>Không có sản phẩm</td></tr>");
    updateRangePaginationUI(0, 0, total, page, totalRangePages);
    return;
  }

  data.forEach(function (row) {
    const totalInput = parseInt(row.total_input_range, 10) || 0;
    const totalOutput = parseInt(row.total_output_range, 10) || 0;
    const netChange = parseInt(row.net_change_range, 10) || 0;
    const netClass = netChange > 0 ? "text-success" : (netChange < 0 ? "text-danger" : "text-warning");

    tbody.append(`
      <tr>
        <td>${row.id}</td>
        <td>${row.name}</td>
        <td>${row.supplier_name}</td>
        <td>${totalInput}</td>
        <td>${totalOutput}</td>
        <td class="${netClass}" style="font-weight: bold;">${netChange}</td>
        <td>
          <button class="btn-action btn-view-range-detail" data-product-id="${row.id}" data-product-name="${row.name}" data-date="${endDate}">
            Xem
          </button>
        </td>
      </tr>
    `);
  });

  const startRecord = (page - 1) * perPage + 1;
  const endRecord = Math.min(page * perPage, total);
  updateRangePaginationUI(startRecord, endRecord, total, page, totalRangePages);
}

function updateRangePaginationUI(startRecord, endRecord, totalRecords, currentPageNum, totalPagesNum) {
  const paginationContainer = $("#inventoryRangePaginationContainer");

  $("#rangeStartRecord").text(totalRecords > 0 ? startRecord : 0);
  $("#rangeEndRecord").text(endRecord);
  $("#rangeTotalRecords").text(totalRecords);

  if (totalPagesNum <= 1) {
    paginationContainer.hide();
  } else {
    paginationContainer.show();
  }

  $("#rangePrevPage").prop("disabled", currentPageNum <= 1);
  $("#rangeNextPage").prop("disabled", currentPageNum >= totalPagesNum);

  const pageNumbers = $("#rangePaginationNumbers");
  pageNumbers.empty();

  const maxPageButtons = 5;
  let startPage = Math.max(1, currentPageNum - Math.floor(maxPageButtons / 2));
  let endPage = Math.min(totalPagesNum, startPage + maxPageButtons - 1);

  if (endPage - startPage < maxPageButtons - 1) {
    startPage = Math.max(1, endPage - maxPageButtons + 1);
  }

  if (startPage > 1) {
    pageNumbers.append(`<button class="inventory-range-page-btn" data-page="1">1</button>`);
    if (startPage > 2) {
      pageNumbers.append(`<span class="pagination-dots">...</span>`);
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    const activeClass = i === currentPageNum ? "active" : "";
    pageNumbers.append(`<button class="inventory-range-page-btn ${activeClass}" data-page="${i}">${i}</button>`);
  }

  if (endPage < totalPagesNum) {
    if (endPage < totalPagesNum - 1) {
      pageNumbers.append(`<span class="pagination-dots">...</span>`);
    }
    pageNumbers.append(`<button class="inventory-range-page-btn" data-page="${totalPagesNum}">${totalPagesNum}</button>`);
  }
}

function displayTable(response, date) {
  console.log("Displaying table with data:", response);
  
  // Extract data from pagination response
  const data = response.data || [];
  const total = response.total || 0;
  const page = response.page || 1;
  const perPage = response.perPage || 8;
  totalPages = response.totalPages || 1;
  
  allData = data;
  filteredData = data;
  
  const tbody = $("#stockTableBody");
  tbody.empty();

  const displayDate = formatDate(date);
  
  if (isToday(date)) {
    $("#tableTitle").text(`Tồn kho sản phẩm cuối ngày ${displayDate}`);
  } else {
    $("#tableTitle").text(`Tồn kho sản phẩm cuối ngày ${displayDate}`);
  }

  if (data.length === 0) {
    tbody.append("<tr><td colspan='5' class='text-center'>Không có sản phẩm</td></tr>");
    updatePaginationUI(0, 0, total, page, totalPages);
    return;
  }

  data.forEach(function (row) {
    const stock = parseInt(row.stock_at_date) || 0;
    const alertQty = parseInt(row.alert_qty) || 0;
    const outOfStockQty = parseInt(row.out_of_stock_qty) || 0;
    const statusMeta = getInventoryStatusMeta(stock, alertQty, outOfStockQty);

    const html = `
      <tr>
        <td>${row.id}</td>
        <td>${row.name}</td>
        <td>${row.supplier_name}</td>
        <td class="${statusMeta.stockClass}" style="font-weight: bold;">${stock}</td>
        <td><span class="status-badge ${statusMeta.statusClass}">${statusMeta.statusText}</span></td>
      </tr>
    `;
    tbody.append(html);
  });
  
  // Update pagination info and controls
  const startRecord = (page - 1) * perPage + 1;
  const endRecord = Math.min(page * perPage, total);
  updatePaginationUI(startRecord, endRecord, total, page, totalPages);
}

function updatePaginationUI(startRecord, endRecord, totalRecords, currentPageNum, totalPagesNum) {
  const paginationContainer = $("#inventoryPaginationContainer");

  // Update pagination info
  $("#startRecord").text(totalRecords > 0 ? startRecord : 0);
  $("#endRecord").text(endRecord);
  $("#totalRecords").text(totalRecords);

  if (totalPagesNum <= 1) {
    paginationContainer.hide();
  } else {
    paginationContainer.show();
  }
  
  // Update prev/next buttons
  $("#prevPage").prop("disabled", currentPageNum <= 1);
  $("#nextPage").prop("disabled", currentPageNum >= totalPagesNum);
  
  // Update page numbers
  const pageNumbers = $("#paginationNumbers");
  pageNumbers.empty();
  
  const maxPageButtons = 5;
  let startPage = Math.max(1, currentPageNum - Math.floor(maxPageButtons / 2));
  let endPage = Math.min(totalPagesNum, startPage + maxPageButtons - 1);
  
  if (endPage - startPage < maxPageButtons - 1) {
    startPage = Math.max(1, endPage - maxPageButtons + 1);
  }
  
  if (startPage > 1) {
    pageNumbers.append(`<button class="inventory-page-btn" data-page="1">1</button>`);
    if (startPage > 2) {
      pageNumbers.append(`<span class="pagination-dots">...</span>`);
    }
  }
  
  for (let i = startPage; i <= endPage; i++) {
    const activeClass = i === currentPageNum ? "active" : "";
    pageNumbers.append(`<button class="inventory-page-btn ${activeClass}" data-page="${i}">${i}</button>`);
  }
  
  if (endPage < totalPagesNum) {
    if (endPage < totalPagesNum - 1) {
      pageNumbers.append(`<span class="pagination-dots">...</span>`);
    }
    pageNumbers.append(`<button class="inventory-page-btn" data-page="${totalPagesNum}">${totalPagesNum}</button>`);
  }
}

function viewDetail(productId, productName, date) {
  currentProductId = productId;
  const displayDate = formatDate(date);
  if (displayDate) {
    $("#detailModalTitle").text(`Chi tiết nhập/xuất: ${productName} (đến cuối ngày ${displayDate})`);
  } else {
    $("#detailModalTitle").text(`Chi tiết nhập/xuất: ${productName}`);
  }

  $("#detailModal").addClass("active");
  loadProductTransactions(productId, date);
}

function loadProductTransactions(productId, date) {
  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getProductTransactionsAtDate",
      product_id: productId,
      date: date,
    },
  }).done(function (result) {
    displayDetailTable(result);
  }).fail(function () {
    const tbody = $("#detailTableBody");
    tbody.empty();
    tbody.append("<tr><td colspan='8' class='text-center'>Lỗi tải dữ liệu chi tiết</td></tr>");
  });
}

function displayDetailTable(data) {
  const tbody = $("#detailTableBody");
  tbody.empty();

  if (!Array.isArray(data) || data.length === 0) {
    tbody.append("<tr><td colspan='8' class='text-center'>Không có giao dịch</td></tr>");
    return;
  }

  data.forEach(function (row) {
    const typeClass = row.type === "Nhập" ? "badge-input" : "badge-output";
    const txDate = formatDate(row.transaction_date);
    const formattedPrice = formatMoney(row.price);
    const documentType = row.document_type || (row.type === "Nhập" ? "receipt" : "order");
    const documentId = row.transaction_id || row.receipt_id;
    const documentLabel = documentType === "receipt" ? `PN${documentId}` : `DH${documentId}`;
    const actionLabel = "Xem";

    const html = `
      <tr>
        <td>${txDate}</td>
        <td><span class="badge ${typeClass}">${row.type}</span></td>
        <td>${documentLabel}</td>
        <td>${row.quantity}</td>
        <td>${formattedPrice}</td>
        <td>${row.source}</td>
        <td>${row.staff_name}</td>
        <td>
          <button class="btn-action btn-view-document" data-document-type="${documentType}" data-document-id="${documentId}">${actionLabel}</button>
        </td>
      </tr>
    `;
    tbody.append(html);
  });
}

function loadTransactionDocument(documentType, documentId) {
  currentDocumentType = documentType;
  currentDocumentId = documentId;
  $("#documentModal").addClass("active");
  $("#documentModalTitle").text("Chi tiết chứng từ");
  $("#documentMetaInfo").html("");
  $("#documentTableBody").html("<tr><td colspan='5' class='text-center'>Đang tải dữ liệu...</td></tr>");

  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getTransactionDocumentDetail",
      document_type: documentType,
      document_id: documentId
    }
  }).done(function (result) {
    displayTransactionDocument(result, documentType, documentId);
  }).fail(function () {
    $("#documentTableBody").html("<tr><td colspan='5' class='text-center'>Không thể tải chứng từ</td></tr>");
  });
}

function displayTransactionDocument(result, documentType, documentId) {
  const tbody = $("#documentTableBody");
  tbody.empty();

  if (!result || !result.success) {
    tbody.append("<tr><td colspan='5' class='text-center'>Không tìm thấy chứng từ</td></tr>");
    return;
  }

  const header = result.header || {};
  const details = result.details || [];
  const isReceipt = (result.document_type || documentType) === "receipt";
  const codeLabel = isReceipt ? `PN${documentId}` : `DH${documentId}`;
  const titleLabel = isReceipt ? "Phiếu nhập" : "Bill xuất";
  const statusLabel = isReceipt
    ? mapReceiptStatusLabel(header.status)
    : mapOrderStatusLabel(header.status_id);

  $("#documentModalTitle").text(`${titleLabel}: ${codeLabel}`);
  $("#documentMetaInfo").html(`
    <p><strong>Mã chứng từ:</strong> ${codeLabel}</p>
    <p><strong>Ngày tạo:</strong> ${formatDate(header.document_date)}</p>
    <p><strong>Đối tác:</strong> ${header.partner_name || "-"}</p>
    <p><strong>Nhân viên:</strong> ${header.staff_id || "-"}</p>
    <p><strong>Trạng thái:</strong> ${statusLabel || "-"}</p>
    <p><strong>Tổng tiền:</strong> ${formatMoney(header.total_price || 0)}</p>
  `);

  if (!Array.isArray(details) || details.length === 0) {
    tbody.append("<tr><td colspan='5' class='text-center'>Không có dòng hàng</td></tr>");
    return;
  }

  details.forEach(function (item) {
    tbody.append(`
      <tr>
        <td>${item.product_id}</td>
        <td>${item.product_name}</td>
        <td>${item.quantity}</td>
        <td>${formatMoney(item.unit_price)}</td>
        <td>${formatMoney(item.line_total)}</td>
      </tr>
    `);
  });
}

function mapReceiptStatusLabel(status) {
  if (status === "completed") {
    return "Hoàn thành";
  }
  if (status === "draft") {
    return "Chưa hoàn thành";
  }
  return status || "-";
}

function mapOrderStatusLabel(statusId) {
  const numericStatus = parseInt(statusId, 10);
  if (Number.isNaN(numericStatus)) {
    return "-";
  }

  const map = {
    1: "Chờ xác nhận",
    2: "Đang xử lý",
    3: "Đã hủy",
    4: "Đang giao",
    5: "Hoàn thành"
  };

  return map[numericStatus] || String(statusId);
}

function formatDate(dateStr) {
  if (!dateStr) return "";
  const date = new Date(dateStr);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

function formatMoney(value) {
  const amount = Math.round(parseFloat(value) || 0);
  return new Intl.NumberFormat("vi-VN").format(amount) + " đ";
}

function isToday(dateStr) {
  const today = new Date().toISOString().split('T')[0];
  return dateStr === today;
}

function getInventoryStatusMeta(stock, alertQty, outOfStockQty) {
  if (stock <= outOfStockQty) {
    return {
      statusText: "⬤ Hết hàng",
      statusClass: "status-out-stock",
      stockClass: stock < 0 ? "text-danger" : "text-warning"
    };
  }

  if (stock <= alertQty) {
    return {
      statusText: "⬤ Sắp hết hàng",
      statusClass: "status-low-stock",
      stockClass: "text-warning"
    };
  }

  return {
    statusText: "⬤ Còn hàng",
    statusClass: "status-in-stock",
    stockClass: "text-success"
  };
}

function isValidThresholds(alertQty, outOfStockQty) {
  const alertValue = parseInt(alertQty, 10);
  const outOfStockValue = parseInt(outOfStockQty, 10);

  if (Number.isNaN(alertValue) || Number.isNaN(outOfStockValue) || alertValue < 0 || outOfStockValue < 0) {
    alert("Vui lòng nhập ngưỡng hợp lệ!");
    return false;
  }

  if (outOfStockValue > alertValue) {
    alert("Ngưỡng hết hàng không được lớn hơn ngưỡng sắp hết hàng!");
    return false;
  }

  return true;
}

function loadDefaultThresholds() {
  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getDefaultAlertQty"
    }
  }).done(function (result) {
    $("#globalAlertQty").val(result.default_alert_qty ?? 10);
    $("#globalOutOfStockQty").val(result.default_out_of_stock_qty ?? 0);
    $("#newOutOfStockQty").val(result.default_out_of_stock_qty ?? 0);
  }).fail(function () {
    $("#globalOutOfStockQty").val(0);
    $("#newOutOfStockQty").val(0);
  });
}

function updateLowStockAlert() {
  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getStockStatusCounts"
    }
  }).done(function (result) {
    const lowStockCount = result.low_stock_count || 0;
    const outOfStockCount = result.out_of_stock_count || 0;
    const $lowStockAlert = $("#lowStockAlert");
    const $outOfStockAlert = $("#outOfStockAlert");
    const $lowStockCountSpan = $("#lowStockCount");
    const $outOfStockCountSpan = $("#outOfStockCount");

    $lowStockCountSpan.text(lowStockCount);
    $outOfStockCountSpan.text(outOfStockCount);

    if (lowStockCount > 0) {
      $lowStockAlert.show();
    } else {
      $lowStockAlert.hide();
    }

    if (outOfStockCount > 0) {
      $outOfStockAlert.show();
    } else {
      $outOfStockAlert.hide();
    }
    
    console.log("Stock alerts updated", {
      lowStockCount,
      outOfStockCount
    });
  }).fail(function () {
    console.error("Failed to load stock status counts");
  });
}
