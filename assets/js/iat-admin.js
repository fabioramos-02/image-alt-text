(function ($) {
  $(document).ready(function () {
    /* display with alt text server side datatable */
    fnIatWithAltTextDataTable();
    /* display without alt text server side datatable */
    fnIatWithoutAltTextDataTable();
    /* tooltip */
    $('[data-bs-toggle="tooltip"]').each(function () {
      new bootstrap.Tooltip(this);
    });
  });

  $(document).on("click", "#iat-generate-bulk-alt-text-btn", function (e) {
    e.preventDefault();

    if (confirm(iatObj.msg1)) {
      $.ajax({
        type: "POST",
        url: iatObj.ajaxUrl,
        data: {
          action: "iat_generate_bulk_alt_text_from_gpt",
          nonce: iatObj.nonce,
        },
        beforeSend: function () {
          $("#iat-generate-bulk-alt-text-loader").show();
        },
        success: function (res) {
          if (res.data.flg == 0) {
            toastr.error(res.data.message, "Error", {
              closeButton: true,
              progressBar: true,
              positionClass: "toast-top-right",
              timeOut: 5000,
            });
          } else {
            toastr.success(
              "Textos alternativos gerados com sucesso!",
              "Sucesso",
              {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 5000,
              }
            );
          }
        },
        complete: function () {
          $("#iat-generate-bulk-alt-text-loader").hide();
        },
      });
    }
  });

  function generateBulkAltText(data) {
    $.ajax({
      type: "POST",
      url: iatObj.ajaxUrl,
      data: {
        action: "iat_generate_bulk_alt_text_from_gpt",
        nonce: iatObj.nonce,
        form_data: data,
      },
      beforeSend: function () {
        $("#iat-copy-bulk-post-title-to-alt-text-loader").show();
      },
      success: function (res) {
        if (res.data.flg == 0) {
          toastr.error(res.data.message, "Error", {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            timeOut: 5000,
          });
        } else {
          toastr.success(
            "Textos alternativos gerados com sucesso!",
            "Sucesso",
            {
              closeButton: true,
              progressBar: true,
              positionClass: "toast-top-right",
              timeOut: 5000,
            }
          );
        }
      },
      complete: function () {
        $("#iat-copy-bulk-post-title-to-alt-text-loader").hide();
      },
    });
  }

  /* adicionar texto alternativo */
  $(document).on("click", ".iat-add-alt-text-btn", function (e) {
    e.preventDefault();
    var postId = $(this).data("post-id");
    var altText = $("#iat-add-alt-text-input-" + postId).val();
    $.ajax({
      type: "POST",
      url: iatObj.ajaxUrl,
      data: {
        action: "iat_add_alt_text",
        nonce: iatObj.nonce,
        post_id: postId,
        alt_text: altText,
      },
      beforeSend: function () {
        $("#iat-add-alt-text-loader-" + postId).show();
      },
      success: function (res) {
        var res = JSON.parse(res);
        if (res.flg == 0) {
          toastr.error(res.message, "Error", {
            closeButton: true,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            timeOut: 5000,
            extendedTimeOut: 1000,
            tapToDismiss: true,
          });
        } else if (res.flg == 1) {
          $("#iat-display-added-alt-text-" + postId)
            .show()
            .find("b")
            .text(altText);
          $("#iat-add-alt-text-" + postId).val("");
          $("#iat-add-alt-text-btn-area-" + postId)
            .hide()
            .attr("style", "display: none !important;");
          $("#iat-copy-post-title-to-alt-text-" + postId).hide();
          $("#iat-copy-attached-post-title-to-alt-text-" + postId).hide();
          if (res.total == 0) {
            $("#iat-copy-bulk-post-title-to-alt-text-btn").toggle();
            $("#iat-copy-bulk-attached-post-title-to-alt-text-btn").toggle();
          }
        }
      },
      complete: function () {
        $("#iat-add-alt-text-loader-" + postId).hide();
      },
    });
  });

  /* atualizar texto alt com lista alt (existente) */
  $(document).on("click", ".iat-update-ex-alt-text-btn", function (e) {
    e.preventDefault();
    var postId = $(this).data("post-id");
    var exAltText = $("#iat-updated-ex-alt-text-input-" + postId).val();
    $.ajax({
      type: "POST",
      url: iatObj.ajaxUrl,
      data: {
        action: "iat_update_existing_alt_text",
        nonce: iatObj.nonce,
        post_id: postId,
        ex_alt_text: exAltText,
      },
      beforeSend: function () {
        $("#iat-update-ex-alt-text-loader-" + postId).show();
      },
      success: function (res) {
        var res = JSON.parse(res);
        if (res.flg == 0) {
          toastr.error(res.message, "Error", {
            closeButton: true,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            timeOut: 5000,
            extendedTimeOut: 1000,
            tapToDismiss: true,
          });
        } else if (res.flg == 1) {
          $("#iat-display-updated-ex-alt-text-" + postId)
            .show()
            .find("b")
            .text(exAltText);
          $("#iat-updated-ex-alt-text-input-" + postId).val("");
          $("#iat-copy-post-title-to-alt-text-display-msg-" + postId).hide();
          $(
            "#iat-copy-attached-post-title-to-alt-text-display-msg-" + postId
          ).hide();
          if (res.total == 0) {
            $("#iat-copy-bulk-post-title-to-alt-text-btn").toggle();
            $("#iat-copy-bulk-attached-post-title-to-alt-text-btn").toggle();
          }
        }
      },
      complete: function () {
        $("#iat-update-ex-alt-text-loader-" + postId).hide();
      },
    });
  });

  /* copiar título de postagem em massa para texto alternativo */
  $(document).on(
    "click",
    "#iat-copy-bulk-post-title-to-alt-text-btn",
    function (e) {
      e.preventDefault();
      if (confirm(iatObj.msg1)) {
        var data = $("#iat-copy-bulk-post-title-to-alt-text-form").serialize();
        fnCopyBulkPostTitleToAltText(data);
      }
    }
  );

  /* copiar título do post anexado em massa para texto alternativo */
  $(document).on(
    "click",
    "#iat-copy-bulk-attached-post-title-to-alt-text-btn",
    function (e) {
      e.preventDefault();
      if (confirm(iatObj.msg1)) {
        var data = $(
          "#iat-copy-bulk-attached-post-title-to-alt-text-form"
        ).serialize();
        fnCopyBulkAttachedPostTitleToAltText(data);
      }
    }
  );

  /* copiar url */
  $(document).on("click", ".iat-copy-url-span p", function (e) {
    e.preventDefault();
    var postID = $(this).data("post-id");
    var url = $(this).data("url");
    var copied = fnIatCopyUrl(url);
    if (copied) {
      var html =
        '<p style="color:green;">Copied&nbsp<span class="dashicons dashicons-saved"></span></p>';
      $("#iat-copy-url-" + postID + "").html(html);
      setTimeout(function () {
        $("#iat-copy-url-" + postID + "").html(url);
      }, 1000);
    }
  });

  function fnIatWithAltTextDataTable() {
    $("#with-alt-list-table").DataTable({
      destroy: true,
      paging: true,
      processing: true,
      serverSide: true,
      pageLength: 10,
      ordering: false,
      searching: true,
      ajax: {
        type: "POST",
        url: iatObj.ajaxUrl,
        data: function (d) {
          d.action = "iat_get_with_alt_text_list";
          d.nonce = iatObj.nonce;
          d.search_value = d.search.value;
        },
        dataSrc: "data",
      },
      columns: [
        { data: "post_image", width: "5%" },
        { data: "post_title", width: "20%" },
        { data: "post_url", width: "15%" },
        { data: "attached_to", width: "15%" },
        { data: "iat_add_alt_text_form", width: "25%" },
        { data: "post_date", width: "10%" },
        { data: "iat_action", width: "5%" },
      ],
    });
  }

  function fnIatWithoutAltTextDataTable() {
    $("#without-alt-list-table").DataTable({
      destroy: true,
      paging: true,
      processing: true,
      serverSide: true,
      pageLength: 10,
      ordering: false,
      searching: true,
      oLanguage: {
        sEmptyTable: iatObj.msg3,
      },
      ajax: {
        type: "POST",
        url: iatObj.ajaxUrl,
        data: {
          action: "iat_get_without_alt_text_list",
          nonce: iatObj.nonce,
        },
        dataSrc: "data",
      },
      columns: [
        { data: "post_image", width: "5%" },
        { data: "post_title", width: "20%" },
        { data: "post_url", width: "15%" },
        { data: "attached_to", width: "15%" },
        { data: "iat_add_alt_text_form", width: "25%" },
        { data: "post_date", width: "10%" },
        { data: "iat_action", width: "5%" },
      ],
    });
  }

  // funcao para copiar titulo de postagem para texto alternativo
  function fnCopyBulkPostTitleToAltText(data) {
    $.ajax({
      type: "POST",
      url: iatObj.ajaxUrl,
      data: data,
      beforeSend: function () {
        $("#iat-copy-bulk-post-title-to-alt-text-loader").show();
        $("#iat-copy-bulk-post-title-to-alt-text-btn").prop("disabled", true);
        $("#iat-copy-bulk-attached-post-title-to-alt-text-btn").prop(
          "disabled",
          true
        );
      },
      success: function (res) {
        var res = JSON.parse(res);
        if (res.flg == 0) {
          toastr.error(res.message, "Error", {
            closeButton: true,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            timeOut: 5000,
            extendedTimeOut: 1000,
            tapToDismiss: true,
          });
          location.reload();
        } else if (res.flg == 1) {
          var ajax_call = res.ajax_call;
          $("#iat-copy-bulk-post-title-to-alt-text-form #iat_ajax_call").val(
            ajax_call
          );
          var data = $(
            "#iat-copy-bulk-post-title-to-alt-text-form"
          ).serialize();
          fnCopyBulkPostTitleToAltText(data);
        } else if (res.flg == 2) {
          $("#iat-copy-bulk-post-title-to-alt-text-loader").hide();
          $("#iat-copy-bulk-post-title-to-alt-text-btn").prop(
            "disabled",
            false
          );
          $("#iat-copy-bulk-attached-post-title-to-alt-text-btn").prop(
            "disabled",
            false
          );
          location.reload();
        }
      },
    });
  }

  /* copiar título do post anexado para texto alternativo */
  function fnCopyBulkAttachedPostTitleToAltText(data) {
    $.ajax({
      type: "POST",
      url: iatObj.ajaxUrl,
      data: data,
      beforeSend: function () {
        $("#iat-copy-bulk-attached-post-title-to-alt-text-loader").show();
        $("#iat-copy-bulk-post-title-to-alt-text-btn").prop("disabled", true);
        $("#iat-copy-bulk-attached-post-title-to-alt-text-btn").prop(
          "disabled",
          true
        );
      },
      success: function (res) {
        var res = JSON.parse(res);
        if (res.flg == 0) {
          toastr.error(res.message, "Error", {
            closeButton: true,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            timeOut: 5000,
            extendedTimeOut: 1000,
            tapToDismiss: true,
          });
          location.reload();
        } else if (res.flg == 1) {
          var ajax_call = res.ajax_call;
          $(
            "#iat-copy-bulk-attached-post-title-to-alt-text-form #iat_ajax_call"
          ).val(ajax_call);
          var data = $(
            "#iat-copy-bulk-attached-post-title-to-alt-text-form"
          ).serialize();
          fnCopyBulkAttachedPostTitleToAltText(data);
        } else if (res.flg == 2) {
          $("#iat-copy-bulk-attached-post-title-to-alt-text-loader").hide();
          $("#iat-copy-bulk-post-title-to-alt-text-btn").prop(
            "disabled",
            false
          );
          $("#iat-copy-bulk-attached-post-title-to-alt-text-btn").prop(
            "disabled",
            false
          );
          location.reload();
        }
      },
    });
  }

  /* função prestes a copiar url ou texto. */
  function fnIatCopyUrl(text) {
    var copyText = text.trim();
    let input = document.createElement("input");
    input.setAttribute("type", "text");
    input.value = copyText;
    document.body.appendChild(input);
    input.select();
    document.execCommand("copy");
    return document.body.removeChild(input);
  }
})(jQuery);

/* copiar nome ou título do post para texto alternativo */
function fnIatCopyPostTitleToAltText(component, postId) {
  var postTitle = jQuery(component).data("post-title");
  var type = jQuery(component).data("type");
  jQuery.ajax({
    type: "POST",
    url: iatObj.ajaxUrl,
    data: {
      action: "iat_copy_post_title_to_alt_text",
      nonce: iatObj.nonce,
      post_id: postId,
      title_to_alt_text: postTitle,
    },
    beforeSend: function () {
      jQuery("#iat-copy-post-title-loader-" + postId).show();
    },
    success: function (res) {
      var res = JSON.parse(res);
      if (res.flg == 0) {
        toastr.error(res.message, "Error", {
          closeButton: true,
          newestOnTop: true,
          progressBar: true,
          positionClass: "toast-top-right",
          preventDuplicates: true,
          timeOut: 5000,
          extendedTimeOut: 1000,
          tapToDismiss: true,
        });
      } else if (res.flg == 1) {
        if (type == "without-alt") {
          jQuery("#iat-copy-post-title-to-alt-text-" + postId).hide();
          jQuery("#iat-copy-attached-post-title-to-alt-text-" + postId).hide();
          jQuery("#iat-add-alt-text-btn-area-" + postId)
            .hide()
            .attr("style", "display: none !important;");
        }
        jQuery(
          "#iat-copy-attached-post-title-to-alt-text-display-msg-" + postId
        ).hide();
        jQuery("#iat-copy-post-title-to-alt-text-display-msg-" + postId)
          .show()
          .find("b")
          .text(postTitle);
        jQuery("#iat-display-ex-alt-text-" + postId)
          .find("b")
          .text(postTitle);
        jQuery("#iat-display-updated-ex-alt-text-" + postId).hide();
      }
    },
    complete: function () {
      jQuery("#iat-copy-post-title-loader-" + postId).hide();
    },
  });
}

/* copiar o nome do post anexado para o texto alternativo */
function fnIatCopyAttachedPostTitleToAltText(component, postId) {
  var postTitle = jQuery(component).data("post-title");
  var type = jQuery(component).data("type");
  jQuery.ajax({
    type: "POST",
    url: iatObj.ajaxUrl,
    data: {
      action: "iat_copy_attached_post_title_to_alt_text",
      nonce: iatObj.nonce,
      post_id: postId,
      post_title_to_alt_text: postTitle,
    },
    beforeSend: function () {
      jQuery("#iat-copy-attached-post-title-loader-" + postId).show();
    },
    success: function (res) {
      var res = JSON.parse(res);
      if (res.flg == 0) {
        toastr.error(res.message, "Error", {
          closeButton: true,
          newestOnTop: true,
          progressBar: true,
          positionClass: "toast-top-right",
          preventDuplicates: true,
          timeOut: 5000,
          extendedTimeOut: 1000,
          tapToDismiss: true,
        });
      } else if (res.flg == 1) {
        if (type == "without-alt") {
          jQuery("#iat-copy-attached-post-title-to-alt-text-" + postId).hide();
          jQuery("#iat-copy-post-title-to-alt-text-" + postId).hide();
          jQuery("#iat-add-alt-text-btn-area-" + postId)
            .hide()
            .attr("style", "display: none !important;");
        }
        jQuery(
          "#iat-copy-attached-post-title-to-alt-text-display-msg-" + postId
        )
          .show()
          .find("b")
          .text(postTitle);
        jQuery("#iat-copy-post-title-to-alt-text-display-msg-" + postId).hide();
        jQuery("#iat-display-ex-alt-text-" + postId)
          .find("b")
          .text(postTitle);
        jQuery("#iat-display-updated-ex-alt-text-" + postId).hide();
      }
    },
    complete: function () {
      jQuery("#iat-copy-attached-post-title-loader-" + postId).hide();
    },
  });
}
