const itemsPerPage = 8;
const modelPath = "../model";
let listCategoryIds = [];
let priceRange = null;
let keyword = "";
let minPrice = null;
let maxPrice = null;
let categoryId = "";

// Hàm để render HTML của mỗi sản phẩm
function renderProductHTML(data) {
  let productHTML = '<div class="collection-product-list">';

  data.products.forEach((product) => {
    const formatPrice = parseInt(product.price, 10).toLocaleString("vi-VN", {
      style: "currency",
      currency: "VND",
    });

    productHTML += `
      <div class="product-item--wrapper">
        <div class="product-item">
          <div class="product-img">
            <div class="product-action">
              <div class="product-action--wrapper">
                <a href="index.php?page=product_detail&pid=${
                  product.id || product.product_id
                }" class="product-action--btn product-action__detail">Chi tiết</a>
                <input type="hidden" class="productId" value="${
                  product.id || product.product_id
                }"/>
                <button class="product-action--btn product-action__addToCart">Thêm vào giỏ</button>
              </div>
            </div>
            <div class="img-resize">
              <img
                src="${product.image_path}"
                alt="${product.product_name || product.name}" />
            </div>
          </div>
          <a href="index.php?page=product_detail&pid=${
            product.id || product.product_id
          }" >
            <div class="product-detail">
                <p class="product-title">${
                  product.name || product.product_name
                }</p>
                <p class="product-price">${formatPrice}</p>
            </div>
          </a>
        </div>
      </div>`;
  });
  productHTML += "</div>";
  return productHTML;
}

// Hàm để render HTML của phân trang
function renderPaginationHTML(data, itemsPerPage) {
  let paginationHTML = '<div class="pagination">';
  const totalPage = Math.ceil(data.amountProduct / itemsPerPage);

  if (data.page > 1) {
    const prev = data.page - 1;
    paginationHTML += `
      <button class="pagination-btn" data="${prev}">
        <i class="fa-solid fa-angle-left"></i>
      </button>`;
  }

  if (data.page - 3 >= 1) {
    paginationHTML += '<button class="pagination-btn" data="1">1</button>';
    paginationHTML += "...";
  }

  for (let i = 1; i <= totalPage; i += 1) {
    const isActive = data.page === i ? "active" : "";
    if (i < data.page + 3 && i > data.page - 3) {
      paginationHTML += `<button class="pagination-btn ${isActive}" data="${i}">${i}</button>`;
    }
  }

  if (data.page + 3 <= totalPage) {
    paginationHTML += "...";
    paginationHTML += `<button class="pagination-btn" data="${totalPage}">${totalPage}</button>`;
  }

  if (data.page < totalPage) {
    const next = data.page + 1;
    paginationHTML += `
      <button class="pagination-btn" data="${next}">
        <i class="fa-solid fa-angle-right"></i>
      </button>`;
  }

  paginationHTML += "</div>";
  return paginationHTML;
}

function normalizePriceValue(value) {
  if (value === undefined || value === null || value === "") {
    return null;
  }

  const numericValue = String(value).replace(/[^0-9]/g, "");
  if (numericValue === "") {
    return null;
  }

  const parsed = parseInt(numericValue, 10);
  if (Number.isNaN(parsed) || parsed < 0) {
    return null;
  }
  return parsed;
}

function formatNumberWithComma(value) {
  const normalized = normalizePriceValue(value);
  if (normalized === null) {
    return "";
  }
  return normalized.toLocaleString("en-US");
}

function syncStateFromAdvancedPanel() {
  keyword = $("#advancedKeyword").val().trim();
  categoryId = $("#advancedCategory").val();
  minPrice = normalizePriceValue($("#priceMin").val());
  maxPrice = normalizePriceValue($("#priceMax").val());

  if (minPrice !== null && maxPrice !== null && minPrice > maxPrice) {
    const temp = minPrice;
    minPrice = maxPrice;
    maxPrice = temp;
    $("#priceMin").val(formatNumberWithComma(minPrice));
    $("#priceMax").val(formatNumberWithComma(maxPrice));
  }
}

function storeAdvancedFilters() {
  localStorage.setItem("keyword", keyword || "");
  localStorage.setItem("listCategoryIds", JSON.stringify(listCategoryIds));
  localStorage.setItem("advancedCategoryId", categoryId || "");

  if (priceRange) {
    localStorage.setItem("priceRange", priceRange);
  } else {
    localStorage.removeItem("priceRange");
  }

  if (minPrice !== null) {
    localStorage.setItem("minPrice", minPrice);
  } else {
    localStorage.removeItem("minPrice");
  }

  if (maxPrice !== null) {
    localStorage.setItem("maxPrice", maxPrice);
  } else {
    localStorage.removeItem("maxPrice");
  }
}

function restoreAdvancedFilters() {
  if (localStorage.getItem("listCategoryIds")) {
    listCategoryIds = JSON.parse(localStorage.getItem("listCategoryIds"));
    listCategoryIds.forEach((id) => {
      $("input[name='theloai'][data='" + id + "']").prop("checked", true);
    });
  }

  if (localStorage.getItem("priceRange")) {
    priceRange = localStorage.getItem("priceRange");
    $("input[name='giaban'][data='" + priceRange + "']")
      .prop("checked", true)
      .attr("checked", "checked");
  }

  if (localStorage.getItem("keyword")) {
    keyword = localStorage.getItem("keyword");
  }

  if (localStorage.getItem("advancedCategoryId")) {
    categoryId = localStorage.getItem("advancedCategoryId");
  }

  if (localStorage.getItem("minPrice")) {
    minPrice = normalizePriceValue(localStorage.getItem("minPrice"));
  }

  if (localStorage.getItem("maxPrice")) {
    maxPrice = normalizePriceValue(localStorage.getItem("maxPrice"));
  }

  $("#advancedKeyword").val(keyword);
  $("#advancedCategory").val(categoryId);
  $("#priceMin").val(minPrice !== null ? formatNumberWithComma(minPrice) : "");
  $("#priceMax").val(maxPrice !== null ? formatNumberWithComma(maxPrice) : "");

  if (document.querySelector("#searchInput")) {
    document.querySelector("#searchInput").value = keyword;
  }
}

function resetFilter() {
  const ckbs = document.querySelectorAll(".advanced-checkbox-list input[type='checkbox']");
  ckbs.forEach((ckb) => {
    ckb.checked = false;
    ckb.removeAttribute("checked");
  });

  listCategoryIds = [];
  priceRange = null;
  keyword = "";
  minPrice = null;
  maxPrice = null;
  categoryId = "";

  $("#advancedKeyword").val("");
  $("#advancedCategory").val("");
  $("#priceMin").val("");
  $("#priceMax").val("");

  if (document.querySelector("#searchInput")) {
    document.querySelector("#searchInput").value = "";
  }

  localStorage.removeItem("listCategoryIds");
  localStorage.removeItem("priceRange");
  localStorage.removeItem("keyword");
  localStorage.removeItem("advancedCategoryId");
  localStorage.removeItem("minPrice");
  localStorage.removeItem("maxPrice");
}

function uncheckPriceRange(currentCkb) {
  const ckbs = document.querySelectorAll("input[type='checkbox'][name='giaban']");
  ckbs.forEach((ckb) => {
    if (ckb !== currentCkb) {
      ckb.checked = false;
      ckb.removeAttribute("checked");
    }
  });
}

// Hàm để render dữ liệu sản phẩm và phân trang (AJAX)
function renderProductsPerPage(currentPage) {
  $.ajax({
    url: "controller/product.controller.php",
    type: "post",
    dataType: "html",
    data: {
      listCategoryIds,
      priceRange,
      itemsPerPage,
      currentPage,
      keyword,
      categoryId,
      minPrice,
      maxPrice,
      modelPath,
    },
  }).done((result) => {
    const data = JSON.parse(result);
    try {
      if (data.success) {
        const productHTML = renderProductHTML(data);
        const paginationHTML = renderPaginationHTML(data, itemsPerPage);
        $(".result").html(`${productHTML}${paginationHTML}`);
      } else {
        $(".result").html(data.message);
      }
    } catch (error) {
      $(".result").html("Không tìm thấy sản phẩm");
    }
  });
}

$(document).ready(() => {
  restoreAdvancedFilters();
  renderProductsPerPage(1);

  $(".btn-toggle-advanced-search").click(() => {
    $(".advanced-search-panel").toggleClass("hide");
  });

  $(".btn-apply-advanced-filter").click(() => {
    syncStateFromAdvancedPanel();

    // Khi dùng giá từ/đến thì bỏ preset giá
    if (minPrice !== null || maxPrice !== null) {
      priceRange = null;
      $("input[name='giaban']").prop("checked", false).removeAttr("checked");
    }

    storeAdvancedFilters();
    renderProductsPerPage(1);
  });

  $(".btn-reset-advanced-filter").click(() => {
    resetFilter();
    renderProductsPerPage(1);
  });

  $("#advancedKeyword, #priceMin, #priceMax").on("keydown", (event) => {
    if (event.key === "Enter") {
      event.preventDefault();
      $(".btn-apply-advanced-filter").click();
    }
  });

  $("#priceMin, #priceMax").on("input", function () {
    const formattedValue = formatNumberWithComma($(this).val());
    $(this).val(formattedValue);
  });

  $("input[name='theloai']").click(function () {
    const categoryIdData = $(this).attr("data");
    if ($(this).is(":checked")) {
      if (!listCategoryIds.includes(+categoryIdData)) {
        listCategoryIds.push(+categoryIdData);
      }
    } else {
      listCategoryIds = listCategoryIds.filter(
        (categoryId) => categoryId !== +categoryIdData
      );
    }

    storeAdvancedFilters();
    renderProductsPerPage(1);
  });

  $("input[name='giaban']").click(function () {
    priceRange = $(this).attr("data");

    if ($(this).attr("checked")) {
      $(this).removeAttr("checked");
      priceRange = null;
    } else {
      $(this).attr("checked", true);
      uncheckPriceRange($(this)[0]);
    }

    // Khi chọn giá preset thì xóa giá nhập tay
    minPrice = null;
    maxPrice = null;
    $("#priceMin").val("");
    $("#priceMax").val("");

    storeAdvancedFilters();
    renderProductsPerPage(1);
  });

  $("#advancedCategory").change(() => {
    syncStateFromAdvancedPanel();
    storeAdvancedFilters();
    renderProductsPerPage(1);
  });

  $(document).on("click", ".pagination-btn", function () {
    const currentPage = $(this).attr("data");
    renderProductsPerPage(currentPage);
  });

  // Giữ nguyên tìm kiếm cơ bản ở header và đồng bộ vào panel nâng cao
  $("#searchButton").click((event) => {
    event.preventDefault();
    keyword = document.querySelector("#searchInput").value.trim();
    $("#advancedKeyword").val(keyword);
    storeAdvancedFilters();
    renderProductsPerPage(1);
  });

  // Xử lý add to Cart
  $(document).on("click", ".product-action__addToCart", function (e) {
    e.preventDefault();

    if ($(this).hasClass("notAllowed")) {
      return;
    }

    const productId = $(this)
      .closest(".product-item")
      .find(".productId")[0]
      .getAttribute("value");
    addToCart(productId, 1);
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const logoLinks = document.querySelectorAll(".logo-link");

  logoLinks.forEach((logo) => {
    logo.addEventListener("click", (event) => {
      event.preventDefault();
      resetFilter();
      setTimeout(() => {
        window.location.href = "index.php";
      }, 50);
    });
  });
});

// Function xử lý addToCart
function addToCart(productId, amount) {
  $.ajax({
    type: "post",
    url: "controller/cart.controller.php",
    dataType: "html",
    data: {
      "product-action__addToCart": true,
      productId,
      amount,
    },
  }).done((result) => {
    const data = JSON.parse(result);
    if (!data.success) {
      window.location.href = "index.php?page=signup";
      alert("Vui lòng đăng nhập để có thể thêm sản phẩm!");
      return;
    }
    $(".cart-qnt").removeClass("hide");
    $(".cart-qnt").text(data.quantity);
    alert(data.message);
  });
}
