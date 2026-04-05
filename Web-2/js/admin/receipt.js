var filter_form = document.querySelector(".admin__content--body__filter");
function getFilterFromURL() {
  filter_form.querySelector("#id").value =
    urlParams["id"] != null ? urlParams["id"] : "";
  filter_form.querySelector("#supplierName").value =
    urlParams["supplier_id"] != null ? urlParams["supplier_id"] : "";
  filter_form.querySelector("#staff_id").value =
    urlParams["staff_id"] != null ? urlParams["staff_id"] : "";
  filter_form.querySelector("#date_start").value =
    urlParams["date_start"] != null ? urlParams["date_start"] : "";
  filter_form.querySelector("#date_end").value =
    urlParams["date_end"] != null ? urlParams["date_end"] : "";
  filter_form.querySelector("#price_start").value =
    urlParams["price_start"] != null ? urlParams["price_start"] : "";
  filter_form.querySelector("#price_end").value =
    urlParams["price_end"] != null ? urlParams["price_end"] : "";
}
function pushFilterToURL() {
  var filter = getGRFilterFromForm();
  var url_key = {
    supplierName: "supplierName",
    id: "id",
    staff_id: "staff_id",
    date_start: "date_start",
    date_end: "date_end",
    price_start: "price_start",
    price_end: "price_end",
  };
  var url = "";
  Object.keys(filter).forEach(key => {
    url +=
      filter[key] != null && filter[key] != ""
        ? `&${url_key[key]}=${filter[key]}`
        : "";
  });
  return url;
}
function getGRFilterFromForm() {
  return {
    supplierName: filter_form.querySelector("#supplierName").value,
    id: filter_form.querySelector("#id").value,
    staff_id: filter_form.querySelector("#staff_id").value,
    date_start: filter_form.querySelector("#date_start").value,
    date_end: filter_form.querySelector("#date_end").value,
    price_start: filter_form.querySelector("#price_start").value,
    price_end: filter_form.querySelector("#price_end").value,
  };
}

// Load the jquery
var script = document.createElement("SCRIPT");
script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js";
script.type = "text/javascript";
document.getElementsByTagName("head")[0].appendChild(script);
var search = location.search.substring(1);
urlParams = JSON.parse(
  '{"' + search.replace(/&/g, '","').replace(/=/g, '":"') + '"}',
  function (key, value) {
    return key === "" ? value : decodeURIComponent(value);
  }
);
var number_of_item = urlParams["item"];
var current_page = urlParams["pag"];
var orderby = urlParams["orderby"];
var order_type = urlParams["order_type"];
if (current_page == null) {
  current_page = 1;
}
if (number_of_item == null) {
  number_of_item = 5;
}
if (orderby == null) {
  orderby = "";
}
if (order_type != "ASC" && order_type != "DESC") {
  order_type = "ASC";
}
function checkReady() {
  return new Promise(async function (resolve) {
    while (!window.jQuery) {
      await new Promise(resolve => setTimeout(resolve, 20));
    }
    resolve();
  });
}
async function loadForFirstTime() {
  await checkReady();
  getFilterFromURL();
  loadItem();
}
function pagnationBtn() {
  // pagnation
  document.querySelectorAll(".pag").forEach(btn =>
    btn.addEventListener(
      "click",
      function () {
        current_page = btn.innerHTML;
        loadItem();
      },
      { once: true }
    )
  );
  if (document.getElementsByClassName("pag-pre").length > 0)
    document.querySelector(".pag-pre").addEventListener(
      "click",
      function () {
        current_page =
          Number(document.querySelector("span.active").innerHTML) - 1;
        loadItem(number_of_item, current_page);
      },
      { once: true }
    );
  if (document.getElementsByClassName("pag-con").length > 0)
    document.querySelector(".pag-con").addEventListener(
      "click",
      function () {
        current_page =
          Number(document.querySelector("span.active").innerHTML) + 1;

        loadItem();
      },
      { once: true }
    );
}
function loadItem() {
  var filter = getGRFilterFromForm();
  $.ajax({
    url: "../controller/admin/pagnation.controller.php",
    type: "post",
    dataType: "html",
    data: {
      number_of_item: number_of_item,
      current_page: current_page,
      function: "getRecords",
      filter: filter,
    },
  }).done(function (result) {
    if (current_page > parseInt(result)) current_page = parseInt(result);
    if (current_page < 1) current_page = 1;
    $.ajax({
      url: "../controller/admin/pagnation.controller.php",
      type: "post",
      dataType: "html",
      data: {
        number_of_item: number_of_item,
        current_page: current_page,
        function: "render",
        orderby: orderby,
        order_type: order_type,
        filter: filter,
      },
    }).done(function (result) {
      var newurl =
        window.location.protocol +
        "//" +
        window.location.host +
        window.location.pathname +
        "?page=" +
        urlParams["page"] +
        "&item=" +
        number_of_item +
        "&current_page=" +
        current_page;
      newurl += pushFilterToURL();
      window.history.pushState({ path: newurl }, "", newurl);
      $(".result").html(result);
      pagnationBtn();
      filterBtn();
      js();
    });
  });
}
document.addEventListener("DOMContentLoaded", () => {
  loadForFirstTime();
});

function filterBtn() {
  $(".body__filter--action__filter").click(e => {
    e.preventDefault();
    var regex2 = /^[0-9]\d*$/;
    var message_date_end = filter_form.querySelector("#message_date_end");
    var message_date_start = filter_form.querySelector("#message_date_begin");
    var message_price_end = filter_form.querySelector("#message_price_end");
    var message_price_start = filter_form.querySelector("#message_price_begin");
    const start_date_str = filter_form.querySelector("#date_start").value;
    const end_date_str = filter_form.querySelector("#date_end").value;
    const start_price_str = filter_form.querySelector("#price_start").value;
    const end_price_str = filter_form.querySelector("#price_end").value;
    const start_date = new Date(start_date_str);
    const end_date = new Date(end_date_str);
    const start_price = parseInt(start_price_str);
    const end_price = parseInt(end_price_str);
    var check = true;

    if (!start_date_str && end_date_str) {
      message_date_start.innerHTML = "*Vui lòng chọn ngày bắt đầu";
      check = false;
    } else if (!end_date_str && start_date_str) {
      message_date_end.innerHTML = "*Vui lòng chọn ngày kết thúc";
      check = false;
    } else if (start_date > end_date) {
      message_date_start.innerHTML =
        "*Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc.";
      check = false;
    } else {
      message_date_start.innerHTML = "";
      message_date_end.innerHTML = "";
    }

    if (start_price_str || end_price_str) {
      if (!start_price_str) {
        message_price_start.innerHTML = "*Nhập giá bắt đầu";
        check = false;
      } else if (!regex2.test(start_price_str)) {
        message_price_start.innerHTML = "*Giá tiền phải là 1 số không âm";
        console.log("sda");
        check = false;
      } else if (
        end_price_str &&
        regex2.test(end_price_str) &&
        start_price > end_price
      ) {
        message_price_start.innerHTML =
          "*Giá tiền bắt đầu phải nhỏ hơn giá tiền kết thúc";
        check = false;
      } else {
        message_price_start.innerHTML = "";
      }

      if (!end_price_str) {
        message_price_end.innerHTML = "*Nhập giá kết thúc";
        check = false;
      } else if (!regex2.test(end_price_str)) {
        message_price_end.innerHTML = "*Giá tiền phải là 1 số không âm";
        check = false;
      } else if (
        start_price_str &&
        regex2.test(start_price_str) &&
        start_price > end_price
      ) {
        message_price_end.innerHTML =
          "*Giá tiền bắt đầu phải nhỏ hơn giá tiền kết thúc";
        check = false;
      } else {
        message_price_end.innerHTML = "";
      }
    }

    if (check) {
      current_page = 1;
      loadItem();
    }
  });

  $(".body__filter--action__reset").click(e => {
    var message_date_end = filter_form.querySelector("#message_date_end");
    var message_date_start = filter_form.querySelector("#message_date_begin");
    var message_price_end = filter_form.querySelector("#message_price_end");
    var message_price_start = filter_form.querySelector("#message_price_begin");
    message_date_start.innerHTML = "";
    message_date_end.innerHTML = "";
    message_price_start.innerHTML = "";
    message_price_end.innerHTML = "";

    current_page = 1;
    $.ajax({
      url: "../controller/admin/pagnation.controller.php",
      type: "post",
      dataType: "html",
      data: {
        number_of_item: number_of_item,
        current_page: current_page,
        function: "render",
      },
    }).done(function (result) {
      var newurl =
        window.location.protocol +
        "//" +
        window.location.host +
        window.location.pathname +
        "?page=" +
        urlParams["page"] +
        "&item=" +
        number_of_item +
        "&current_page=" +
        current_page;
      window.history.pushState({ path: newurl }, "", newurl);
      $(".result").html(result);
      pagnationBtn();
      js();
    });
  });
}

function addProduct() {
  let productName = "";
  let productId = "";
  let quantity = "";
  let inputPrice = "";
  let productNumber = 0;

  function onProductSelect(id, name) {
    productId = id;
    productName = name;
    document.getElementById("productSearch").value = id + " - " + name;
    document.getElementById("productDropdown").style.display = "none";
    if (!id) return;

    $.ajax({
      url: "../controller/admin/receipt.controller.php",
      type: "post",
      dataType: "html",
      data: { function: "getPrice", field: { id: id } },
    }).done(function (result) {
      inputPrice = Math.round(0.8 * parseFloat(result));
      document.getElementById("inputPrice").value = inputPrice.toLocaleString("en-US");
    }).fail(function () {
      alert("Đã xảy ra lỗi khi lấy giá.");
    });

    $.ajax({
      url: "../controller/admin/receipt.controller.php",
      type: "post",
      dataType: "json",
      data: { function: "getProductsNumber", field: { id: id } },
      success: function (response) {
        if (response.success) {
          productNumber = response.product_count;
        } else {
          console.error("Lỗi: " + response.error);
        }
      },
      error: function (xhr, status, error) {
        console.error("Yêu cầu thất bại: " + error);
      },
    });
  }

  function buildDropdown(keyword) {
    const select = document.getElementById("productId");
    const dropdown = document.getElementById("productDropdown");
    if (!dropdown || !select) return;
    const kw = (keyword || "").toLowerCase().trim();
    dropdown.innerHTML = "";
    let hasItems = false;
    Array.from(select.options).forEach(function (opt) {
      if (!opt.value) return;
      if (kw && !opt.text.toLowerCase().includes(kw) && !opt.value.toLowerCase().includes(kw)) return;
      const li = document.createElement("li");
      li.className = "product-dropdown-item";
      li.textContent = opt.text;
      li.setAttribute("data-id", opt.value);
      li.addEventListener("mousedown", function (e) {
        e.preventDefault();
        onProductSelect(this.getAttribute("data-id"), this.textContent);
      });
      dropdown.appendChild(li);
      hasItems = true;
    });
    dropdown.style.display = hasItems ? "block" : "none";
  }

  const searchInput = document.getElementById("productSearch");
  const productDropdownEl = document.getElementById("productDropdown");

  searchInput.addEventListener("focus", function () {
    buildDropdown(this.value);
  });

  searchInput.addEventListener("input", function () {
    productId = "";
    productName = "";
    buildDropdown(this.value);
  });

  searchInput.addEventListener("blur", function () {
    setTimeout(function () { productDropdownEl.style.display = "none"; }, 150);
  });

  // Format giá nhập khi gõ
  document.getElementById("inputPrice").addEventListener("input", function () {
    const raw = this.value.replace(/[^\d]/g, "");
    this.value = raw === "" ? "" : parseInt(raw).toLocaleString("en-US");
  });

  document.getElementById("addProduct").addEventListener("click", function () {
    var regex = /^[1-9]\d*$/;

    quantity = document.getElementById("quantity").value;
    const inputPriceFieldVal = document.getElementById("inputPrice").value.replace(/[^\d]/g, "");
    inputPrice = parseInt(inputPriceFieldVal) || 0;

    if (productId.trim() === "" || quantity.trim() === "") {
      alert("Vui lòng nhập đầy đủ thông tin.");
      return;
    }

    if (!regex.test(quantity)) {
      alert("Số lượng phải là số nguyên dương lớn hơn 0");
      return;
    }

    if (inputPrice <= 0) {
      alert("Giá nhập phải là số nguyên dương lớn hơn 0.");
      return;
    }

    if (Number(quantity) > 100001 - productNumber) {
      alert("Số lượng không hợp lệ!");
      return;
    }

    const tableBody = document.getElementById("productTableBody");
    let productExists = false;

    Array.from(tableBody.rows).forEach(function (row) {
      if (row.cells[0].textContent === productId) {
        let prevQuantity = parseInt(row.cells[2].textContent);
        let newQuantity = prevQuantity + parseInt(quantity);
        row.cells[2].textContent = newQuantity;
        row.cells[3].textContent = inputPrice.toLocaleString("vi-VN", {
          style: "currency",
          currency: "VND",
        });
        productExists = true;
      }
    });

    if (!productExists) {
      const newRow = tableBody.insertRow();
      newRow.innerHTML = `
        <td>${productId}</td>
        <td>${productName}</td> 
        <td>${quantity}</td>
        <td>${inputPrice.toLocaleString("vi-VN", {
          style: "currency",
          currency: "VND",
        })}</td>
      `;
    }

    document.getElementById("productSearch").value = "";
    productId = "";
    productName = "";
    document.getElementById("quantity").value = "";
    document.getElementById("inputPrice").value = "";
  });
}

function deleteRow() {
  const table = document.getElementById("addTable");
  const rowCount = table.rows.length;
  if (rowCount > 1) {
    table.deleteRow(rowCount - 1);
  } else {
    alert("Không có dòng để xóa.");
  }
}
function getProductsBySupplier(selectElement) {
  const tableBody = document.getElementById("productTableBody");

  // Nếu đã có sản phẩm trong phiếu, xác nhận trước khi đổi nhà cung cấp
  if (tableBody.rows.length > 0) {
    const confirmChange = confirm(
      "Thay đổi nhà cung cấp sẽ xóa tất cả sản phẩm đã thêm vào phiếu.\nBạn có chắc chắn muốn tiếp tục?"
    );
    if (!confirmChange) {
      // Hoàn tác lựa chọn: giữ lại nhà cung cấp cũ
      // Lấy supplierId cũ từ sản phẩm đầu tiên trong bảng không khả thi,
      // nên đặt lại về option rỗng để buộc chọn lại
      selectElement.value = selectElement.getAttribute("data-current") || "";
      return;
    }
  }

  // Lưu nhà cung cấp hiện tại để có thể hoàn tác nếu cần
  selectElement.setAttribute("data-current", selectElement.value);

  tableBody.innerHTML = "";

  const supplierId = selectElement.value;
  $.ajax({
    url: "../controller/admin/receipt.controller.php",
    type: "post",
    dataType: "html",
    data: {
      function: "getIdProducts",
      field: {
        id: supplierId,
      },
    },
  }).done(function (htmlResult) {
    const idProductSelect = document.getElementById("productId");
    idProductSelect.innerHTML = htmlResult;
    // Reset ô tìm kiếm khi đổi nhà cung cấp
    const searchField = document.getElementById("productSearch");
    if (searchField) {
      searchField.value = "";
      searchField.dispatchEvent(new Event("input"));
    }
  });
}

const openModal = addHtml => {
  const addModal = document.getElementById("addReiceptModal");
  const addModalContent = document.querySelector(".addModal-content .form");
  const openModalBtn = document.querySelector(".body__filter--action__add");
  const addButton = document.getElementById("addButton");
  const closeAddIcon = document.querySelector(".addModal-content .close i");

  openModalBtn.addEventListener(
    "click",
    function () {
      addModalContent.innerHTML = addHtml;
      addModalContent.addEventListener("click", addProduct(), { once: true });
      addModal.style.display = "block";

      // Điền ngày hiện tại làm mặc định
      const today = new Date().toISOString().split("T")[0];
      document.getElementById("date_create_add").value = today;

      $.ajax({
        url: "../controller/admin/receipt.controller.php",
        type: "post",
        dataType: "html",
        data: {
          function: "getSuppliers",
        },
      }).done(function (htmlResult) {
        const supplierSelect = document.getElementById("supplier");
        supplierSelect.innerHTML = htmlResult;
      });
    },
    { once: true }
  );

  closeAddIcon.addEventListener("click", function () {
    addModal.style.display = "none";
  });

  addButton.addEventListener(
    "click",
    function (e) {
      e.preventDefault();

      const supplierId = document.getElementById("supplier").value;

      const products = document.querySelectorAll("#productTableBody tr");
      let totalPrice = 0;

      let detailData = [];
      products.forEach(product => {
        const productId = product.cells[0].textContent;
        const quantity = product.cells[2].textContent;
        const inputPriceText = product.cells[3].textContent;
        const inputPrice =
          parseFloat(inputPriceText.replace(/[^\d.-]/g, "")) * 1000;
        totalPrice += parseFloat(quantity) * inputPrice;
        detailData.push({ productId, quantity, inputPrice });
      });
      if (totalPrice === 0) {
        alert("Vui lòng nhập đầy đủ thông tin.");
        return;
      }
      const staffName = document
        .querySelector(".topbar__admin-info h2")
        .innerHTML.trim();
      const dateCreateAdd = document.getElementById("date_create_add").value;
      $.ajax({
        url: "../controller/admin/receipt.controller.php",
        type: "post",
        dataType: "html",
        data: {
          function: "create",
          field: {
            supplierId: supplierId,
            totalPrice: totalPrice,
            details: detailData,
            staffId: staffName,
            dateCreate: dateCreateAdd,
          },
        },
      }).done(function (result) {
        loadItem();
        $("#sqlresult").html(result);
        setTimeout(() => {
          $("#sqlresult").html(""); // Xóa nội dung sau 3 giây
        }, 3000);
        addModal.style.display = "none";
      });
    },
    { once: true }
  );

  const editModal = document.getElementById("editModal");
  const editModalContent = document.querySelector(".editModal-content .form");
  const closeEditIcon = document.querySelector(".editModal-content .close i");

  const editHtml = `
<div class="form">
  <h2>Sửa thông tin đơn nhập hàng</h2>
  <form id="form">
    <div class="input-field">
      <label for="idReceipt">Mã đơn nhập</label>
      <input type="text" id="idForm" readonly="">
      </div>
      <div class="input-field">
      <label for="supplierName" >Tên nhà cung cấp</label>
      <input type="text" id="supplierNameForm" readonly="">
    </div>
    <div class="input-field">
      <label for="staff_id">Tên người nhập hàng</label>
      <input type="email" id="staff_idForm" readonly="" >
      </div>
      <div class="input-field">
      <label for="total_price">Tổng giá</label>
      <input type="text" id="total_price" readonly="">
    </div>
    <div class="input-field">
      <label for="date_create">Ngày lập</label>
      <input type="date" id="date_create">
    </div>
    <div class="input-field">
      <label for="receipt_status">Trạng thái phiếu</label>
      <input type="text" id="receipt_status" readonly="">
    </div>
  
    <div class="book-table">
    <table id=Table> </table>
    </div>
  <div class="form-actions" style="margin-top: 12px;">
    <button type="button" id="saveReceiptBtn" class="btn">Lưu thay đổi</button>
  </div>

</div>`;

  closeEditIcon.addEventListener("click", () => {
    editModal.style.display = "none";
  });

  window.addEventListener("click", event => {
    if (event.target === editModal) {
      editModal.style.display = "none";
    }
  });

  var edit_btns = document.querySelectorAll(".actions--edit");
  edit_btns.forEach(function (btn) {
    btn.addEventListener("click", function () {
      editModalContent.innerHTML = editHtml;
      editModal.style.display = "block";

      let id = this.closest("tr").querySelector(".id").innerHTML;
      const rowStatus = this.closest("tr").querySelector(".receipt_status")?.getAttribute("data-status") || "draft";
      const isCompleted = rowStatus === "completed";

      document.getElementById("idForm").value = id;
      document.getElementById("supplierNameForm").value =
        this.closest("tr").querySelector(".supplierName").innerHTML;
      document.getElementById("total_price").value =
        this.closest("tr").querySelector(".total_price").innerHTML;
      document.getElementById("staff_idForm").value =
        this.closest("tr").querySelector(".staff_id").innerHTML;
      document.getElementById("date_create").value = "";

      // Lấy ngày nhập từ DB để đảm bảo đúng dữ liệu gốc
      $.ajax({
        url: "../controller/admin/receipt.controller.php",
        type: "post",
        dataType: "json",
        data: {
          function: "getById",
          field: { id: id }
        }
      }).done(function (res) {
        if (res && res.success && res.data && res.data.date_create) {
          document.getElementById("date_create").value = res.data.date_create;
          const st = res.data.status || rowStatus;
          document.getElementById("receipt_status").value = st === "completed" ? "Hoàn thành" : "Chưa hoàn thành";
        } else {
          document.getElementById("receipt_status").value = rowStatus === "completed" ? "Hoàn thành" : "Chưa hoàn thành";
        }
      });

      let Table = document.querySelector("#Table");
      Table.innerHTML = "";
      $.ajax({
        url: "../controller/admin/receipt.controller.php",
        type: "post",
        dataType: "html",
        data: {
          function: "details",
          field: {
            id: id,
          },
        },
        success: function (response) {
          Table.innerHTML = response;

          // Chỉ cho phép chỉnh sửa khi phiếu chưa hoàn thành
          const rows = Table.querySelectorAll("tbody tr");
          if (!isCompleted) {
            rows.forEach(row => {
              const cells = row.querySelectorAll("td");
              if (cells.length >= 4) {
                const qtyText = cells[2].textContent.trim().replace(/[^\d-]/g, "");
                const priceText = cells[3].textContent.trim().replace(/[^\d-]/g, "");
                const qty = parseInt(qtyText) || 0;
                const price = parseInt(priceText) || 0;

                cells[2].innerHTML = `<input type="number" class="edit-qty" min="1" value="${qty}" style="width: 100%;">`;
                cells[3].innerHTML = `<input type="number" class="edit-input-price" min="1" value="${price}" style="width: 100%;">`;
              }
            });
          }

          // Tính tổng giá từ bảng chi tiết
          let totalPrice = 0;
          
          rows.forEach(row => {
            const cells = row.querySelectorAll("td");
            if (cells.length >= 4) {
              const qty = isCompleted
                ? (parseInt(cells[2].textContent.trim().replace(/[^\d-]/g, "")) || 0)
                : (parseInt(cells[2].querySelector(".edit-qty").value) || 0);
              const price = isCompleted
                ? (parseInt(cells[3].textContent.trim().replace(/[^\d-]/g, "")) || 0)
                : (parseInt(cells[3].querySelector(".edit-input-price").value) || 0);
              const lineTotal = qty * price;
              cells[cells.length - 1].textContent = Math.round(lineTotal).toLocaleString("vi-VN") + "₫";
              totalPrice += lineTotal;
            }
          });

          document.getElementById("total_price").value = Math.round(totalPrice).toLocaleString('vi-VN');

          // Re-calc tổng tiền khi thay đổi input
          if (!isCompleted) {
            Table.querySelectorAll(".edit-qty, .edit-input-price").forEach(input => {
              input.addEventListener("input", () => {
                const allRows = Table.querySelectorAll("tbody tr");
                let nextTotal = 0;
                allRows.forEach(r => {
                  const c = r.querySelectorAll("td");
                  if (c.length >= 4) {
                    const q = parseInt(c[2].querySelector(".edit-qty").value) || 0;
                    const p = parseInt(c[3].querySelector(".edit-input-price").value) || 0;
                    const t = q * p;
                    c[c.length - 1].textContent = Math.round(t).toLocaleString("vi-VN") + "₫";
                    nextTotal += t;
                  }
                });
                document.getElementById("total_price").value = Math.round(nextTotal).toLocaleString("vi-VN");
              });
            });
          }

          // Lưu chỉnh sửa phiếu nhập
          const saveBtn = document.getElementById("saveReceiptBtn");
          if (isCompleted && saveBtn) {
            saveBtn.style.display = "none";
            document.getElementById("date_create").setAttribute("disabled", "disabled");
          }
          if (saveBtn) {
            saveBtn.onclick = function () {
              if (isCompleted) {
                alert("Phiếu nhập đã hoàn thành, không thể sửa.");
                return;
              }
              const tableRows = Table.querySelectorAll("tbody tr");
              const detailData = [];
              let isValid = true;

              tableRows.forEach(r => {
                const c = r.querySelectorAll("td");
                if (c.length >= 4) {
                  const productId = c[0].textContent.trim();
                  const qty = parseInt(c[2].querySelector(".edit-qty").value);
                  const inputPrice = parseInt(c[3].querySelector(".edit-input-price").value);

                  if (!Number.isInteger(qty) || qty <= 0 || !Number.isFinite(inputPrice) || inputPrice <= 0) {
                    isValid = false;
                    return;
                  }

                  detailData.push({
                    productId: productId,
                    quantity: qty,
                    inputPrice: inputPrice
                  });
                }
              });

              if (!isValid || detailData.length === 0) {
                alert("Vui lòng nhập số lượng và giá nhập hợp lệ (> 0).");
                return;
              }

              $.ajax({
                url: "../controller/admin/receipt.controller.php",
                type: "post",
                dataType: "html",
                data: {
                  function: "update",
                  field: {
                    id: id,
                    date_create: document.getElementById("date_create").value,
                    details: detailData
                  }
                }
              }).done(function (resultText) {
                $("#sqlresult").html(resultText);
                setTimeout(() => {
                  $("#sqlresult").html("");
                }, 3000);
                loadItem();
                editModal.style.display = "none";
              }).fail(function () {
                alert("Lỗi khi cập nhật phiếu nhập.");
              });
            };
          }
        },
      });
    });
  });

  // Đánh dấu hoàn thành phiếu nhập
  var complete_btns = document.querySelectorAll(".actions--complete");
  complete_btns.forEach(function (btn) {
    btn.addEventListener("click", function () {
      const row = this.closest("tr");
      const id = row.querySelector(".id").innerHTML;
      const status = row.querySelector(".receipt_status")?.getAttribute("data-status");
      if (status === "completed") return;

      if (!confirm("Xác nhận hoàn thành phiếu nhập? Sau khi hoàn thành sẽ không thể sửa.")) {
        return;
      }

      $.ajax({
        url: "../controller/admin/receipt.controller.php",
        type: "post",
        dataType: "html",
        data: {
          function: "complete",
          field: { id: id }
        }
      }).done(function (resultText) {
        $("#sqlresult").html(resultText);
        setTimeout(() => {
          $("#sqlresult").html("");
        }, 3000);
        loadItem();
      }).fail(function () {
        alert("Lỗi khi hoàn thành phiếu nhập.");
      });
    });
  });

  // Xóa phiếu nhập (chỉ áp dụng cho phiếu chưa hoàn thành)
  var delete_btns = document.querySelectorAll(".actions--delete");
  delete_btns.forEach(function (btn) {
    btn.addEventListener("click", function () {
      const row = this.closest("tr");
      const id = row.querySelector(".id").innerHTML;
      const status = row.querySelector(".receipt_status")?.getAttribute("data-status");

      if (status === "completed") {
        alert("Phiếu nhập đã hoàn thành, không thể xóa.");
        return;
      }

      if (!confirm("Bạn có chắc muốn xóa phiếu nhập này?")) {
        return;
      }

      $.ajax({
        url: "../controller/admin/receipt.controller.php",
        type: "post",
        dataType: "html",
        data: {
          function: "delete",
          field: { id: id }
        }
      }).done(function (resultText) {
        $("#sqlresult").html(resultText);
        setTimeout(() => {
          $("#sqlresult").html("");
        }, 3000);
        loadItem();
      }).fail(function () {
        alert("Lỗi khi xóa phiếu nhập.");
      });
    });
  });

  closeEditIcon.addEventListener("click", () => {
    editModal.style.display = "none";
  });

  window.addEventListener("click", event => {
    if (event.target === editModal) {
      editModal.style.display = "none";
    }
  });
};

var js = function () {
  var addHtml = `
  <div class="form">
    <h2>Thêm thông tin đơn nhập hàng</h2>
    <form id="form">
      <div class="input-field">
        <label for="supplier">Nhà cung cấp:</label>
        <select id="supplier" onchange="getProductsBySupplier(this)" ">
        <!-- Options for suppliers will be dynamically added -->
      </select>
        </select>
      </div>
      <div class="input-field">
        <label>Mã sản phẩm:</label>
        <div class="product-combobox">
          <input type="text" id="productSearch" placeholder="Nhập mã hoặc tên để tìm..." autocomplete="off">
          <ul id="productDropdown" class="product-dropdown"></ul>
        </div>
        <select id="productId" style="display:none"></select>
      </div>
      <div class="input-field">
        <label for="quantity">Số lượng:</label>
        <input type="text" id="quantity">
      </div>
      <div class="input-field">
        <label for="inputPrice">Giá nhập (VNĐ):</label>
        <input type="text" id="inputPrice" inputmode="numeric" placeholder="Nhập giá nhập">
      </div>
      <div class="input-field">
        <label for="date_create_add">Ngày nhập:</label>
        <input type="date" id="date_create_add">
      </div>
      <div class="form-actions">
        <button type="button"  class="btn" id="addProduct">Thêm sản phẩm</button>
        <button type="button"  onclick="deleteRow()">Xóa</button>

      </div>    
    </form>
    <div class="book-table">
      <table id="addTable">
        <thead>
          <tr>
            <th style="width: 10%">Mã SP</th>
            <th style=" width: 55%">Tên SP</th>
            <th style=" width: 15%">Số Lượng</th>
            <th style=" width: 20%">Giá Nhập</th>
          </tr>
        </thead>
        <tbody id="productTableBody">
          <!-- Products added will be shown here -->
        </tbody>
      </table>
    </div>
  </div>`;
  let flag = false;
  if (orderby != "" && order_type != "")
    document.querySelector("[data-order=" + "'" + orderby + "']").innerHTML +=
      order_type == "ASC"
        ? ' <i class="fas fa-sort-up">'
        : ' <i class="fas fa-sort-down">';
  else if (!document.querySelector("[data-order]")) {
    openModal(addHtml);
    flag = true;
  } else
    document.querySelector("[data-order]").innerHTML +=
      order_type == "ASC"
        ? ' <i class="fas fa-sort-up">'
        : ' <i class="fas fa-sort-down">';
  document
    .querySelector(".result")
    .querySelectorAll("th")
    .forEach(th => {
      if (th.hasAttribute("data-order"))
        th.addEventListener("click", () => {
          if (orderby == "")
            orderby = document
              .querySelector("[data-order]")
              .getAttribute("data-order");
          if (orderby == th.getAttribute("data-order") && order_type == "ASC") {
            order_type = "DESC";
          } else {
            order_type = "ASC";
          }
          orderby = th.getAttribute("data-order");
          loadItem();
        });
    });
  !flag ? openModal(addHtml) : "";
};
