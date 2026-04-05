var filter_form = document.querySelector(".admin__content--body__filter");
function getFilterFromURL() {
  filter_form.querySelector("#idOrder").value =
    urlParams["idOrder"] != null ? urlParams["idOrder"] : "";
  filter_form.querySelector("#idCus").value =
    urlParams["idCus"] != null ? urlParams["idCus"] : "";
  filter_form.querySelector("#idStaff").value =
    urlParams["idStaff"] != null ? urlParams["idStaff"] : "";
  filter_form.querySelector("#statusSelect").value =
    urlParams["status"] != null ? urlParams["status"] : "all";
  filter_form.querySelector("#address").value =
    urlParams["address"] != null ? urlParams["address"] : "";
  filter_form.querySelector("#date_begin").value =
    urlParams["date_begin"] != null ? urlParams["date_begin"] : "";
  filter_form.querySelector("#date_end").value =
    urlParams["date_end"] != null ? urlParams["date_end"] : "";
}
function pushFilterToURL() {
  var filter = getFilterFromForm();
  var url_key = {
    id_customer: "idCus",
    id_staff: "idStaff",
    id_Order: "idOrder",
    Order_status: "status",
    date_begin: "date_begin",
    date_end: "date_end",
    address: "address",
  };
  var params = new URLSearchParams();
  Object.keys(filter).forEach((key) => {
    if (filter[key] == null || filter[key] === "") return;
    if (key === "Order_status" && filter[key] === "all") return;
    params.set(url_key[key], filter[key]);
  });
  return params.toString();
}
function getFilterFromForm() {
  return {
    id_customer: filter_form.querySelector("#idCus").value.trim(),
    id_staff: filter_form.querySelector("#idStaff").value.trim(),
    id_Order: filter_form.querySelector("#idOrder").value.trim(),
    Order_status: filter_form.querySelector("#statusSelect").value,
    date_begin: filter_form.querySelector("#date_begin").value,
    date_end: filter_form.querySelector("#date_end").value,
    address: filter_form.querySelector("#address").value.trim(),
  };
}

// Load the jquery
var script = document.createElement("SCRIPT");
script.src = "https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js";
script.type = "text/javascript";
document.getElementsByTagName("head")[0].appendChild(script);
var urlSearchParams = new URLSearchParams(window.location.search);
var urlParams = Object.fromEntries(urlSearchParams.entries());

var number_of_item = parseInt(urlParams["item"], 10);
var current_page = parseInt(urlParams["pag"] || urlParams["current_page"], 10);
var orderby = urlParams["orderby"];
var order_type = urlParams["order_type"];
if (!Number.isInteger(current_page) || current_page < 1) {
  current_page = 1;
}
if (!Number.isInteger(number_of_item) || number_of_item < 1) {
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
      await new Promise((resolve) => setTimeout(resolve, 20));
    }
    resolve();
  });
}
async function loadForFirstTime() {
  await checkReady();
  getFilterFromURL();
  filterBtn();
  loadItem();
}
function pagnationBtn() {
  // pagnation
  document.querySelectorAll(".pag").forEach((btn) =>
    btn.addEventListener("click", function () {
      current_page = btn.innerHTML;
      loadItem();
    })
  );
  if (document.getElementsByClassName("pag-pre").length > 0)
    document.querySelector(".pag-pre").addEventListener("click", function () {
      current_page =
        Number(document.querySelector("span.active").innerHTML) - 1;
      loadItem(number_of_item, current_page);
    });
  if (document.getElementsByClassName("pag-con").length > 0)
    document.querySelector(".pag-con").addEventListener("click", function () {
      current_page =
        Number(document.querySelector("span.active").innerHTML) + 1;

      loadItem();
    });
}
function loadItem() {
  var filter = getFilterFromForm();
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
      var basePage = urlParams["page"] || "order";
      var query = new URLSearchParams();
      query.set("page", basePage);
      query.set("item", number_of_item);
      query.set("pag", current_page);
      if (orderby) query.set("orderby", orderby);
      if (order_type) query.set("order_type", order_type);

      var filterQuery = pushFilterToURL();
      if (filterQuery) {
        var filterParams = new URLSearchParams(filterQuery);
        filterParams.forEach((value, key) => query.set(key, value));
      }

      var newurl =
        window.location.protocol +
        "//" +
        window.location.host +
        window.location.pathname +
        "?" +
        query.toString();

      urlParams = Object.fromEntries(query.entries());
      window.history.pushState({ path: newurl }, "", newurl);
      $(".result").html(result);
      pagnationBtn();
      js();
    });
  });
}
document.addEventListener("DOMContentLoaded", () => {
  loadForFirstTime();
});

function filterBtn() {
  $(".body__filter--action__filter")
    .off("click")
    .on("click", (e) => {
      e.preventDefault();
    current_page = 1;
    var idOrder = filter_form.querySelector("#idOrder").value.trim();
    var idCus = filter_form.querySelector("#idCus").value.trim();
    var idStaff = filter_form.querySelector("#idStaff").value.trim();
    var message_idOrder = filter_form.querySelector("#message_idOrder");
    var message_idCus = filter_form.querySelector("#message_idCus");
    var message_idStaff = filter_form.querySelector("#message_idStaff");
    var message_end = filter_form.querySelector("#message_end");
    var message_start = filter_form.querySelector("#message_begin");
    const start_date_str = filter_form.querySelector("#date_begin").value;
    const end_date_str = filter_form.querySelector("#date_end").value;
    const start_date = new Date(start_date_str);
    const end_date = new Date(end_date_str);
    var address = filter_form.querySelector("#address").value.trim();
    var message_address = filter_form.querySelector("#message_address");

    var check = true;
    var regex = /^\d+$/;

    // Order ID validation
    if (!idOrder.match(regex) && idOrder !== "") {
      message_idOrder.innerHTML = "*Mã đơn hàng phải là kí tự số";
      filter_form.querySelector("#idOrder").focus();
      check = false;
    } else {
      message_idOrder.innerHTML = "";
    }

    if (!idCus.match(regex) && idCus !== "") {
      message_idCus.innerHTML = "*Mã khách hàng phải là kí tự số";
      filter_form.querySelector("#idCus").focus();
      check = false;
    } else {
      message_idCus.innerHTML = "";
    }

    if (!idStaff.match(regex) && idStaff !== "") {
      message_idStaff.innerHTML = "*Mã nhân viên phải là kí tự số";
      filter_form.querySelector("#idStaff").focus();
      check = false;
    } else {
      message_idStaff.innerHTML = "";
    }

    // Date validation
    if (!start_date_str && end_date_str) {
      message_start.innerHTML = "*Vui lòng chọn ngày bắt đầu";
      check = false;
    } else if (start_date_str && end_date_str && start_date > end_date) {
      message_start.innerHTML =
        "*Ngày bắt đầu phải nhỏ hơn hoặc bằng ngày kết thúc.";
      check = false;
    } else {
      message_start.innerHTML = "";
    }

    if (!end_date_str && start_date_str) {
      message_end.innerHTML = "*Vui lòng chọn ngày kết thúc";
      check = false;
    } else if (start_date_str && end_date_str && start_date > end_date) {
      message_end.innerHTML =
        "*Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.";
      check = false;
    } else {
      message_end.innerHTML = "";
    }

    if (address.length > 255) {
      message_address.innerHTML = "*Địa chỉ không được vượt quá 255 ký tự";
      filter_form.querySelector("#address").focus();
      check = false;
    } else {
      message_address.innerHTML = "";
    }

    if (check == true) {
      message_idOrder.innerHTML = "";
      message_idCus.innerHTML = "";
      message_idStaff.innerHTML = "";
      message_start.innerHTML = "";
      message_end.innerHTML = "";
      message_address.innerHTML = "";
      current_page = 1;
      loadItem();
    }
    });

  $(".body__filter--action__reset")
    .off("click")
    .on("click", (e) => {
    e.preventDefault();
    filter_form.reset();
    filter_form.querySelector("#statusSelect").value = "all";

    var message_idOrder = filter_form.querySelector("#message_idOrder");
    var message_idCus = filter_form.querySelector("#message_idCus");
    var message_idStaff = filter_form.querySelector("#message_idStaff");
    var message_end = filter_form.querySelector("#message_end");
    var message_start = filter_form.querySelector("#message_begin");
    var message_address = filter_form.querySelector("#message_address");
    message_idOrder.innerHTML = "";
    message_idCus.innerHTML = "";
    message_idStaff.innerHTML = "";
    message_start.innerHTML = "";
    message_end.innerHTML = "";
    message_address.innerHTML = "";

    current_page = 1;
    loadItem();
    });

  $(filter_form)
    .off("keydown")
    .on("keydown", "input, select", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();
        $(".body__filter--action__filter").trigger("click");
      }
    });
}

var js = function () {
  if (orderby != "" && order_type != "")
    document.querySelector("[data-order=" + "'" + orderby + "']").innerHTML +=
      order_type == "ASC"
        ? ' <i class="fas fa-sort-up">'
        : ' <i class="fas fa-sort-down">';
  else
    document.querySelector("[data-order]").innerHTML +=
      order_type == "ASC"
        ? ' <i class="fas fa-sort-up">'
        : ' <i class="fas fa-sort-down">';
  document
    .querySelector(".result")
    .querySelectorAll("th")
    .forEach((th) => {
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
  const btnDetails = document.querySelectorAll(".actions--view");
  const modal = document.querySelector(".order-modal");
  const overlay = document.querySelector(".overlay");
  const btnCloseModal = document.querySelectorAll(".close-modal");

  const openModal = function () {
    modal.classList.remove("hidden");
    overlay.classList.remove("hidden");
  };

  const closeModal = function () {
    modal.classList.add("hidden");
    overlay.classList.add("hidden");
  };

  btnDetails.forEach((btn) =>
    btn.addEventListener("click", () => {
      openModal();

      $.ajax({
        url: "../controller/admin/order.controller.php",
        type: "post",
        dataType: "html",
        data: {
          id: btn.parentNode.parentNode.querySelector(".order_id").innerHTML,
          function: "order_details",
        },
      }).done(function (result) {
        modal.querySelector("tbody").innerHTML = result;
        modal.querySelector("tbody").querySelector("#discount_code").innerHTML =
          btn.parentNode.parentNode.querySelector(".discount_code").innerHTML;
        if (
          modal.querySelector("tbody").querySelector("#discount_code")
            .innerHTML == ""
        )
          modal
            .querySelector("tbody")
            .querySelector("#discount_code")
            .parentNode.remove();
        modal.querySelector("tbody").querySelector("#price-number").innerHTML =
          btn.parentNode.parentNode.querySelector(".total_price").innerHTML;
        btn_contain = "";
        switch (modal.querySelector("tbody").querySelector("#status").value) {
          case "1":
            btn_contain = `<input type="button" value="Hủy đơn hàng" data-status-id="3" class="cancel-order">
              <input type="button" value="Duyệt đơn hàng" data-status-id="2" class="confirm-order">`;
            break;
          case "2":
            btn_contain = `<input type="button" value="Hủy đơn hàng" data-status-id="3" class="cancel-order">
              <input type="button" value="Giao đơn hàng" data-status-id="4" class="confirm-order">`;
            break;
          case "4":
            btn_contain = `<input type="button" value="Xác nhận đã giao" data-status-id="5" class="confirm-order">`;
            break;
        }
        modal.querySelector(".del-btn-container").innerHTML = btn_contain;
        modal
          .querySelector(".del-btn-container")
          .querySelectorAll("input")
          .forEach((ch_btn) =>
            ch_btn.addEventListener("click", () => {
              change_status(ch_btn.getAttribute("data-status-id"));
            })
          );
      });
    })
  );

  overlay.addEventListener("click", closeModal);
  btnCloseModal[0].addEventListener("click", closeModal);

  function change_status(status) {
    $.ajax({
      url: "../controller/admin/order.controller.php",
      type: "post",
      dataType: "html",
      data: {
        id: document.querySelector(".order-modal").querySelector("#id").value,
        status: status,
        function: "order_status",
      },
    }).done(function (result) {
      closeModal();
      loadItem();
    });
  }
};
