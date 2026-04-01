// Load jQuery
var script = document.createElement("SCRIPT");
script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js";
script.type = "text/javascript";
document.getElementsByTagName("head")[0].appendChild(script);
console.log("jQuery script loading...");

let currentProductId = null;
let currentDate = null;

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
  
  // Search button
  $("#searchStock").click(function () {
    console.log("Search Stock button clicked");
    const selectedDate = $("#stockDate").val();
    if (selectedDate) {
      currentDate = selectedDate;
      loadStock(selectedDate);
    }
  });

  // Reset button - back to today
  $("#resetStock").click(function () {
    console.log("Reset Stock button clicked");
    setDefaultDate();
    loadStock();
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

function loadStock(date = null) {
  const searchDate = date || currentDate || new Date().toISOString().split('T')[0];
  
  console.log("Loading stock for date:", searchDate);
  
  $.ajax({
    url: "../controller/admin/inventory.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getStockAtDate",
      date: searchDate,
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

function displayTable(data, date) {
  console.log("Displaying table with data:", data);
  const tbody = $("#stockTableBody");
  tbody.empty();

  const displayDate = formatDate(date);
  
  if (isToday(date)) {
    $("#tableTitle").text("Tồn kho sản phẩm (hôm nay)");
  } else {
    $("#tableTitle").text(`Tồn kho sản phẩm (tính đến ${displayDate})`);
  }

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
        <td><button class="btn-action" onclick="viewDetail(${row.id}, '${row.name}', '${date}')">Chi tiết</button></td>
      </tr>
    `;
    tbody.append(html);
  });
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
