// Load jQuery
var script = document.createElement("SCRIPT");
script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js";
script.type = "text/javascript";
document.getElementsByTagName("head")[0].appendChild(script);

let currentProductId = null;
let currentSnapshotDate = null;

// Wait for jQuery to load
function checkJQueryReady() {
  return new Promise(async function (resolve) {
    while (!window.jQuery) {
      await new Promise(resolve => setTimeout(resolve, 20));
    }
    resolve();
  });
}

// Initialize on page load
$(document).ready(async function () {
  await checkJQueryReady();
  initializeEventListeners();
  loadCurrentStock();
  setDefaultSnapshotDate();
});

function setDefaultSnapshotDate() {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");
  $("#snapshotDate").val(`${year}-${month}-${day}`);
}

function initializeEventListeners() {
  // Tab switching
  $(".tab-btn").click(function () {
    const tabName = $(this).data("tab");

    $(".tab-btn").removeClass("active");
    $(this).addClass("active");

    $(".tab-content").removeClass("active");
    $("#" + tabName).addClass("active");

    if (tabName === "current-tab") {
      loadCurrentStock();
    }
  });

  // History tab - Search
  $("#historySearch").click(function () {
    searchHistory();
  });

  // History tab - Reset
  $("#historyReset").click(function () {
    resetHistoryFilters();
  });

  // Current stock - Refresh
  $("#currentRefresh").click(function () {
    loadCurrentStock();
  });

  // Snapshot - Search
  $("#snapshotSearch").click(function () {
    searchSnapshot();
  });

  // Modal close buttons
  $(".modal-close").click(function () {
    $(this).closest(".modal-overlay").removeClass("active");
    currentProductId = null;
    currentSnapshotDate = null;
  });

  // Modal search
  $("#modalSearch").click(function () {
    if (currentProductId) {
      loadProductHistory(currentProductId);
    }
  });

  // Close modal when clicking outside
  $(document).click(function (event) {
    const modal = $(event.target).closest(".modal-overlay");
    if (event.target.classList.contains("modal-overlay")) {
      $(event.target).removeClass("active");
      currentProductId = null;
      currentSnapshotDate = null;
    }
  });
}
  const filters = {
    product_id: $("#historyProductId").val(),
    product_name: $("#historyProductName").val(),
    type: $("#historyType").val(),
    date_start: $("#historyDateStart").val(),
    date_end: $("#historyDateEnd").val(),
  };

  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getHistory",
      ...filters,
    },
  }).done(function (result) {
    displayHistoryTable(result);
  }).fail(function () {
    alert("Lỗi tải dữ liệu lịch sử!");
  });
}

function displayHistoryTable(data) {
  const tbody = $("#historyTableBody");
  tbody.empty();

  if (data.length === 0) {
    tbody.append("<tr><td colspan='8' class='text-center'>Không có kết quả tìm kiếm</td></tr>");
    return;
  }

  data.forEach(function (row) {
    const typeClass = row.type === "Nhập" ? "badge-input" : "badge-output";
    const date = formatDate(row.transaction_date);
    const html = `
      <tr>
        <td>${date}</td>
        <td>${row.product_id}</td>
        <td>${row.product_name}</td>
        <td>${row.supplier_name}</td>
        <td><span class="badge ${typeClass}">${row.type}</span></td>
        <td>${row.quantity}</td>
        <td>${row.staff_name}</td>
        <td><button class="btn-action" onclick="viewProductHistoryDetail(${row.product_id}, '${row.product_name}')">Chi tiết</button></td>
      </tr>
    `;
    tbody.append(html);
  });
}

function resetHistoryFilters() {
  $("#historyProductId").val("");
  $("#historyProductName").val("");
  $("#historyType").val("");
  $("#historyDateStart").val("");
  $("#historyDateEnd").val("");
  $("#historyTableBody").html("<tr><td colspan='8' class='text-center'>Chọn tiêu chí tìm kiếm</td></tr>");
}

function loadCurrentStock() {
  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getCurrentStock",
    },
  }).done(function (result) {
    displayCurrentStockTable(result);
  }).fail(function () {
    alert("Lỗi tải dữ liệu tồn kho!");
  });
}

function displayCurrentStockTable(data) {
  const tbody = $("#currentTableBody");
  tbody.empty();

  if (data.length === 0) {
    tbody.append("<tr><td colspan='7' class='text-center'>Không có sản phẩm</td></tr>");
    return;
  }

  data.forEach(function (row) {
    const currentStock = row.current_quantity || 0;
    const stockClass = currentStock > 0 ? "text-success" : "text-danger";
    
    const html = `
      <tr>
        <td>${row.id}</td>
        <td>${row.name}</td>
        <td>${row.supplier_name}</td>
        <td>${row.total_input || 0}</td>
        <td>${row.total_output || 0}</td>
        <td class="${stockClass}" style="font-weight: bold;">${currentStock}</td>
        <td><button class="btn-action" onclick="viewProductHistoryDetail(${row.id}, '${row.name}')">Xem lịch sử</button></td>
      </tr>
    `;
    tbody.append(html);
  });
}

function viewProductHistoryDetail(productId, productName) {
  currentProductId = productId;
  $("#modalTitle").text(`Lịch sử tồn kho: ${productName}`);
  $("#modalDateStart").val("");
  $("#modalDateEnd").val("");
  $("#productHistoryModal").addClass("active");
  loadProductHistory(productId);
}

function loadProductHistory(productId) {
  const filters = {
    date_start: $("#modalDateStart").val(),
    date_end: $("#modalDateEnd").val(),
  };

  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getProductHistory",
      product_id: productId,
      ...filters,
    },
  }).done(function (result) {
    displayProductHistoryTable(result);
  }).fail(function () {
    alert("Lỗi tải lịch sử sản phẩm!");
  });
}

function displayProductHistoryTable(data) {
  const tbody = $("#modalTableBody");
  tbody.empty();

  if (data.length === 0) {
    tbody.append("<tr><td colspan='6' class='text-center'>Không có dữ liệu</td></tr>");
    return;
  }

  let runningStock = 0;

  data.forEach(function (row) {
    const typeClass = row.type === "Nhập" ? "badge-input" : "badge-output";
    const date = formatDate(row.transaction_date);
    
    // Tính tồn kho đang chạy (chạy từ dưới lên)
    if (row.type === "Nhập") {
      runningStock += parseInt(row.quantity);
    } else {
      runningStock -= parseInt(row.quantity);
    }

    const formattedPrice = formatPrice(row.price);
    const sourceDestination = row.type === "Nhập" ? row.supplier_name : row.supplier_name;

    const html = `
      <tr>
        <td>${date}</td>
        <td><span class="badge ${typeClass}">${row.type}</span></td>
        <td>${row.quantity}</td>
        <td>${formattedPrice}</td>
        <td>${sourceDestination}</td>
        <td>${row.staff_name}</td>
      </tr>
    `;
    tbody.append(html);
  });
}

function closeModal() {
  $("#productHistoryModal").removeClass("active");
  currentProductId = null;
}

function searchSnapshot() {
  const date = $("#snapshotDate").val();
  if (!date) {
    alert("Vui lòng chọn ngày!");
    return;
  }

  currentSnapshotDate = date;
  const displayDate = formatDate(date);
  $("#snapshotTableTitle").text(`Tồn kho tính đến ngày ${displayDate}`);

  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getStockAtDate",
      date: date,
    },
  }).done(function (result) {
    displaySnapshotTable(result, date);
  }).fail(function () {
    alert("Lỗi tải dữ liệu tồn kho!");
  });
}

function displaySnapshotTable(data, date) {
  const tbody = $("#snapshotTableBody");
  tbody.empty();

  if (data.length === 0) {
    tbody.append("<tr><td colspan='7' class='text-center'>Không có sản phẩm</td></tr>");
    return;
  }

  data.forEach(function (row) {
    const stock = parseInt(row.stock_at_date) || 0;
    const stockClass = stock > 0 ? "text-success" : (stock < 0 ? "text-danger" : "text-warning");

    const html = `
      <tr>
        <td>${row.id}</td>
        <td>${row.name}</td>
        <td>${row.supplier_name}</td>
        <td>${row.total_input || 0}</td>
        <td>${row.total_output || 0}</td>
        <td class="${stockClass}" style="font-weight: bold;">${stock}</td>
        <td><button class="btn-action" onclick="viewSnapshotDetail(${row.id}, '${row.name}', '${date}')">Chi tiết</button></td>
      </tr>
    `;
    tbody.append(html);
  });
}

function viewSnapshotDetail(productId, productName, date) {
  currentSnapshotDate = date;
  const displayDate = formatDate(date);
  $("#snapshotModalTitle").text(`Chi tiết giao dịch ${productName} (đến ${displayDate})`);
  $("#snapshotDetailModal").addClass("active");
  loadSnapshotProductTransactions(productId, date);
}

function loadSnapshotProductTransactions(productId, date) {
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
    displaySnapshotDetailTable(result);
  }).fail(function () {
    alert("Lỗi tải chi tiết giao dịch!");
  });
}

function displaySnapshotDetailTable(data) {
  const tbody = $("#snapshotDetailTableBody");
  tbody.empty();

  if (data.length === 0) {
    tbody.append("<tr><td colspan='6' class='text-center'>Không có giao dịch tại thời điểm này</td></tr>");
    return;
  }

  data.forEach(function (row) {
    const typeClass = row.type === "Nhập" ? "badge-input" : "badge-output";
    const date = formatDate(row.transaction_date);
    const formattedPrice = formatPrice(row.price);

    const html = `
      <tr>
        <td>${date}</td>
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

// Close snapshot modal
$(document).on("click", "#snapshotDetailModal .modal-close", function () {
  $("#snapshotDetailModal").removeClass("active");
  currentSnapshotDate = null;
});

$(document).on("click", function (event) {
  const modal = $(event.target).closest(".modal-overlay");
  if (event.target.id === "snapshotDetailModal") {
    $("#snapshotDetailModal").removeClass("active");
    currentSnapshotDate = null;
  }
});

function formatDate(dateStr) {
  if (!dateStr) return "";
  const date = new Date(dateStr);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

function formatPrice(price) {
  if (!price) return "0 đ";
  return parseInt(price).toLocaleString("vi-VN") + " đ";
}

// Trigger search when pressing Enter in input fields
$("#historyProductId, #historyProductName, #historyDateStart, #historyDateEnd").keypress(function (e) {
  if (e.which == 13) {
    e.preventDefault();
    searchHistory();
  }
});

// Load current stock on page load
window.addEventListener("load", function () {
  if (document.getElementById("current-tab").classList.contains("active")) {
    loadCurrentStock();
  }
});
