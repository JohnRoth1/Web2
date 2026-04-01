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
let allData = [];
let filteredData = [];

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
}

function initializeEventListeners() {
  console.log("Initializing event listeners...");
  
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
    if (!alertQty || alertQty < 0) {
      alert("Vui lòng nhập mức cảnh báo hợp lệ!");
      return;
    }
    
    $.ajax({
      url: "../controller/admin/inventory.controller.php",
      type: "POST",
      dataType: "json",
      data: {
        function: "updateAlertQty",
        product_id: currentProductId,
        alert_qty: alertQty
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
    
    $("#modalProductName").text(productName);
    $("#modalCurrentQty").text(currentQty);
    $("#newAlertQty").val(alertQty);
    $("#alertQtyModal").addClass("active");
    setTimeout(() => $("#newAlertQty").focus(), 100);
  });

  // Bulk Alert Qty - Apply to all products
  $("#btnSetBulkAlertQty").click(function () {
    const alertQty = $("#globalAlertQty").val();
    if (!alertQty || alertQty < 0) {
      alert("Vui lòng nhập mức cảnh báo hợp lệ!");
      return;
    }

    const confirmed = confirm(`Bạn chắc chắn muốn đặt mức cảnh báo ${alertQty} cho TẤT CẢ sản phẩm?`);
    if (!confirmed) return;

    $(this).prop("disabled", true).text("Đang xử lý...");

    $.ajax({
      url: "../controller/admin/inventory.controller.php",
      type: "POST",
      dataType: "json",
      data: {
        function: "setBulkAlertQty",
        alert_qty: alertQty
      }
    }).done(function (result) {
      console.log("BulkAlertQty Response:", result);
      if (result && result.success) {
        alert("Cập nhật mức cảnh báo cho tất cả sản phẩm thành công!");
        loadStock(currentDate, currentPage);
        updateLowStockAlert();
        $("#globalAlertQty").val("");
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

  // Reset button - back to today
  $("#resetStock").click(function () {
    console.log("Reset Stock button clicked");
    setDefaultDate();
    $("#searchProduct").val("");
    $("#statusFilter").val("");
    currentPage = 1;
    loadStock();
  });

  // Real-time search
  $("#searchProduct").on("input", function () {
    filterProducts();
  });

  // Status filter
  $("#statusFilter").on("change", function () {
    filterProducts();
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

  // Pagination number click
  $(document).on("click", ".pagination-number", function () {
    const page = parseInt($(this).data("page"));
    if (page !== currentPage) {
      currentPage = page;
      loadStock(currentDate, currentPage);
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

function loadStock(date = null, page = 1) {
  const searchDate = date || currentDate || new Date().toISOString().split('T')[0];
  currentPage = page;
  
  console.log("Loading stock for date:", searchDate, "page:", page);
  
  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getStockAtDate",
      date: searchDate,
      page: page
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
    $("#tableTitle").text("Tồn kho sản phẩm (hôm nay)");
  } else {
    $("#tableTitle").text(`Tồn kho sản phẩm (tính đến ${displayDate})`);
  }

  if (data.length === 0) {
    tbody.append("<tr><td colspan='8' class='text-center'>Không có sản phẩm</td></tr>");
    updatePaginationUI(0, 0, total, page, totalPages);
    return;
  }

  data.forEach(function (row) {
    const stock = parseInt(row.stock_at_date) || 0;
    const alertQty = parseInt(row.alert_qty) || 0;
    const stockClass = stock > 0 ? "text-success" : (stock < 0 ? "text-danger" : "text-warning");
    const statusText = stock >= alertQty ? "⬤ Còn hàng" : "⬤ Hết hàng";
    const statusClass = stock >= alertQty ? "status-in-stock" : "status-out-stock";

    const html = `
      <tr>
        <td>${row.id}</td>
        <td>${row.name}</td>
        <td>${row.supplier_name}</td>
        <td>${row.total_input || 0}</td>
        <td>${row.total_output || 0}</td>
        <td class="${stockClass}" style="font-weight: bold;">${stock}</td>
        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
        <td><button class="btn-action" onclick="viewDetail(${row.id}, '${row.name}', '${date}')">Chi tiết</button></td>
      </tr>
    `;
    tbody.append(html);
  });
  
  // Update pagination info and controls
  const startRecord = (page - 1) * perPage + 1;
  const endRecord = Math.min(page * perPage, total);
  updatePaginationUI(startRecord, endRecord, total, page, totalPages);
}

function filterProducts() {
  const searchTerm = $("#searchProduct").val().toLowerCase();
  const statusFilter = $("#statusFilter").val();
  
  console.log("Filtering products with search term:", searchTerm, "status:", statusFilter);
  
  const tbody = $("#stockTableBody");
  tbody.empty();
  
  if (allData.length === 0) {
    tbody.append("<tr><td colspan='8' class='text-center'>Không có sản phẩm</td></tr>");
    return;
  }
  
  // Filter data based on search term and status
  let filtered = allData;
  
  // Filter by search term
  if (searchTerm) {
    filtered = filtered.filter(product => {
      const idMatch = product.id.toString().includes(searchTerm);
      const nameMatch = product.name.toLowerCase().includes(searchTerm);
      const supplierMatch = product.supplier_name.toLowerCase().includes(searchTerm);
      return idMatch || nameMatch || supplierMatch;
    });
  }
  
  // Filter by status
  if (statusFilter) {
    filtered = filtered.filter(product => {
      const stock = parseInt(product.stock_at_date) || 0;
      const alertQty = parseInt(product.alert_qty) || 0;
      const isInStock = stock >= alertQty;
      
      if (statusFilter === "instock") {
        return isInStock;
      } else if (statusFilter === "outstock") {
        return !isInStock;
      }
      return true;
    });
  }
  
  if (filtered.length === 0) {
    tbody.append("<tr><td colspan='8' class='text-center'>Không có sản phẩm phù hợp</td></tr>");
    return;
  }
  
  filtered.forEach(function (row) {
    const stock = parseInt(row.stock_at_date) || 0;
    const alertQty = parseInt(row.alert_qty) || 0;
    const stockClass = stock > 0 ? "text-success" : (stock < 0 ? "text-danger" : "text-warning");
    const statusText = stock >= alertQty ? "⬤ Còn hàng" : "⬭ Hết hàng";
    const statusClass = stock >= alertQty ? "status-in-stock" : "status-out-stock";

    const html = `
      <tr>
        <td>${row.id}</td>
        <td>${row.name}</td>
        <td>${row.supplier_name}</td>
        <td>${row.total_input || 0}</td>
        <td>${row.total_output || 0}</td>
        <td class="${stockClass}" style="font-weight: bold;">${stock}</td>
        <td><span class="status-badge ${statusClass}">${statusText}</span></td>
        <td><button class="btn-action" onclick="viewDetail(${row.id}, '${row.name}', '${currentDate}')">Chi tiết</button></td>
      </tr>
    `;
    tbody.append(html);
  });
}

function updatePaginationUI(startRecord, endRecord, totalRecords, currentPageNum, totalPagesNum) {
  // Update pagination info
  $("#startRecord").text(totalRecords > 0 ? startRecord : 0);
  $("#endRecord").text(endRecord);
  $("#totalRecords").text(totalRecords);
  
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
    pageNumbers.append(`<button class="pagination-number" data-page="1">1</button>`);
    if (startPage > 2) {
      pageNumbers.append(`<span class="pagination-dots">...</span>`);
    }
  }
  
  for (let i = startPage; i <= endPage; i++) {
    const activeClass = i === currentPageNum ? "active" : "";
    pageNumbers.append(`<button class="pagination-number ${activeClass}" data-page="${i}">${i}</button>`);
  }
  
  if (endPage < totalPagesNum) {
    if (endPage < totalPagesNum - 1) {
      pageNumbers.append(`<span class="pagination-dots">...</span>`);
    }
    pageNumbers.append(`<button class="pagination-number" data-page="${totalPagesNum}">${totalPagesNum}</button>`);
  }
}

function viewDetail(productId, productName, date) {
  currentProductId = productId;
  const displayDate = formatDate(date);
  
  if (isToday(date)) {
    $("#modalTitle").text(`Chi tiết giao dịch: ${productName}`);
  } else {
    $("#modalTitle").text(`Chi tiết giao dịch: ${productName} (đến ${displayDate})`);
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
    alert("Lỗi tải chi tiết giao dịch!");
  });
}

function displayDetailTable(data) {
  const tbody = $("#detailTableBody");
  tbody.empty();

  if (data.length === 0) {
    tbody.append("<tr><td colspan='6' class='text-center'>Không có giao dịch</td></tr>");
    return;
  }

  data.forEach(function (row) {
    const typeClass = row.type === "Nhập" ? "badge-input" : "badge-output";
    const txDate = formatDate(row.transaction_date);
    const formattedPrice = formatPrice(row.price);

    const html = `
      <tr>
        <td>${txDate}</td>
        <td><span class="badge ${typeClass}">${row.type}</span></td>
        <td>${row.quantity}</td>
        <td>${formattedPrice}</td>
        <td>${row.source}</td>
        <td>${row.staff_name}</td>
      </tr>
    `;
    tbody.append(html);
  });
}

function formatDate(dateStr) {
  if (!dateStr) return "";
  const date = new Date(dateStr);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

function isToday(dateStr) {
  const today = new Date().toISOString().split('T')[0];
  return dateStr === today;
}

function formatPrice(price) {
  if (!price) return "0 đ";
  return parseInt(price).toLocaleString("vi-VN") + " đ";
}

function updateLowStockAlert() {
  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getLowStockCount"
    }
  }).done(function (result) {
    const count = result.count || 0;
    const $alert = $("#lowStockAlert");
    const $countSpan = $("#lowStockCount");
    
    $countSpan.text(count);
    
    if (count > 0) {
      $alert.show();
    } else {
      $alert.hide();
    }
    
    console.log("Low stock alert updated, count:", count);
  }).fail(function () {
    console.error("Failed to load low stock count");
  });
}
