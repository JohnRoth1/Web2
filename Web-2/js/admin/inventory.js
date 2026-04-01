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
      currentPage = 1;
      loadStock(selectedDate, 1);
    }
  });

  // Reset button - back to today
  $("#resetStock").click(function () {
    console.log("Reset Stock button clicked");
    setDefaultDate();
    $("#searchProduct").val("");
    currentPage = 1;
    loadStock();
  });

  // Real-time search
  $("#searchProduct").on("input", function () {
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
    tbody.append("<tr><td colspan='7' class='text-center'>Không có sản phẩm</td></tr>");
    updatePaginationUI(0, 0, total, page, totalPages);
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
  
  // Update pagination info and controls
  const startRecord = (page - 1) * perPage + 1;
  const endRecord = Math.min(page * perPage, total);
  updatePaginationUI(startRecord, endRecord, total, page, totalPages);
}

function filterProducts() {
  const searchTerm = $("#searchProduct").val().toLowerCase();
  console.log("Filtering products with search term:", searchTerm);
  
  const tbody = $("#stockTableBody");
  tbody.empty();
  
  if (allData.length === 0) {
    tbody.append("<tr><td colspan='7' class='text-center'>Không có sản phẩm</td></tr>");
    return;
  }
  
  if (!searchTerm) {
    // Show all data from current page
    allData.forEach(function (row) {
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
          <td><button class="btn-action" onclick="viewDetail(${row.id}, '${row.name}', '${currentDate}')">Chi tiết</button></td>
        </tr>
      `;
      tbody.append(html);
    });
    return;
  }
  
  // Filter data
  const filtered = allData.filter(product => {
    const idMatch = product.id.toString().includes(searchTerm);
    const nameMatch = product.name.toLowerCase().includes(searchTerm);
    const supplierMatch = product.supplier_name.toLowerCase().includes(searchTerm);
    
    return idMatch || nameMatch || supplierMatch;
  });
  
  if (filtered.length === 0) {
    tbody.append("<tr><td colspan='7' class='text-center'>Không có sản phẩm phù hợp</td></tr>");
    return;
  }
  
  filtered.forEach(function (row) {
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
