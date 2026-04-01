let allProducts = [];
let currentProductId = null;
let currentProductInputPrice = null;

$(document).ready(function () {
  loadProducts();
  initializeEventListeners();
});

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

function initializeEventListeners() {
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
        alert(`Cập nhật giá thành công!\nGiá nhập: ${formatPrice(result.input_price)}\nGiá bán mới: ${formatPrice(result.new_price)}`);
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

  // Real-time search
  $("#searchProduct").on("input", function () {
    filterProducts();
  });

  // Real-time margin calculation
  $("#marginPercent").on("input", function () {
    calculateMarginPrice();
  });
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
  
  // Get input price from goodsreceipt_details
  const product = allProducts.find(p => p.id == productId);
  if (!product) return;
  
  // For now, we'll need to fetch the input price from the server
  $.ajax({
    url: "../controller/admin/pricing.controller.php",
    type: "POST",
    dataType: "json",
    data: {
      function: "getProducts"
    }
  }).done(function (products) {
    // We'll calculate based on the latest price if we can't get input price
    // For proper implementation, get input_price from goodsreceipt_details
    $("#modalProductName2").text(productName);
    $("#marginPercent").val("");
    $("#calculatedPriceGroup").hide();
    $("#marginModal").addClass("active");
    setTimeout(() => $("#marginPercent").focus(), 100);
  });
}

function calculateMarginPrice() {
  const margin = parseFloat($("#marginPercent").val()) || 0;
  
  if (margin < 0) {
    $("#calculatedPriceGroup").hide();
    return;
  }
  
  // For margin calculation, we need the input price
  // This would typically come from the last goods receipt
  // Here we show the calculation
  const product = allProducts.find(p => p.id == currentProductId);
  if (!product) return;
  
  // This is a simplified version - in production, get actual input price
  // For now, assume markup on current price
  const estimatedInputPrice = product.price / 1.2; // Assume 20% baseline
  const calculatedPrice = estimatedInputPrice * (1 + margin / 100);
  
  if (margin > 0) {
    $("#calculatedPrice").text(formatPrice(calculatedPrice));
    $("#calculatedPriceGroup").show();
  } else {
    $("#calculatedPriceGroup").hide();
  }
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

function formatPrice(price) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
}
