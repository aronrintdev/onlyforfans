$( document ).ready(function() {
  if ( !g_defined_tipping_utils ) {

    var g_defined_tipping_utils = true;

    /*
  if (typeof currency !== "function" && typeof currency === "undefined" ) { 
  //
  // 12345 => $12,345.00
  //
  // @param {String} sign
  // @param {Number} decimals Decimal places
  ///
    var digitsRE = /(\d{3})(?=\d)/g;

    function currency(value, _currency, decimals) {
      value = parseFloat(value);
      if (!isFinite(value) || !value && value !== 0) return '';
      _currency = _currency != null ? _currency : '$';
      decimals = decimals != null ? decimals : 2;
      var stringified = Math.abs(value).toFixed(decimals);
      var _int = decimals ? stringified.slice(0, -1 - decimals) : stringified;
      var i = _int.length % 3;
      var head = i > 0 ? _int.slice(0, i) + (_int.length > 3 ? ',' : '') : '';
      var _float = decimals ? stringified.slice(-1 - decimals) : '';
      var sign = value < 0 ? '-' : '';
      return sign + _currency + head + _int.slice(i).replace(digitsRE, '$1,') + _float;
    }
  }
  */

  // span decrement 1
  $('span.decrement').click(function () {
    let input = $(this).siblings('input.filter-input');
    let val = input.val() != '' ? parseFloat(input.val()) : 0;
    let newVal;

    if (val != NaN) {
      if (input.data('id') == 1) {
        newVal = (val - 100) < 3 ? 3 : (val - 100);
      } else if(input.data('id') == 2) {
        newVal = (val - 10) < 3 ? 3 : (val - 10);
      } else { 
        newVal = (val - 1) < 3 ? 3 : (val - 1);
      }
      input.val( newVal ).trigger('keyup');
    }
  });

    $('span.increment').click(function () {
      let input = $(this).siblings('input.filter-input');
      let val = input.val() != '' ? parseFloat(input.val()) : 0;
      let newVal;

      if ( val < 10 ) {
        val = 0; // %PSG: take care of case where val is 3 so next up is 10
      }

      if (val != NaN) {
        if (input.data('id') == 1) {
          newVal = val + 100;
        } else if(input.data('id') == 2) {
          newVal = val + 10;
        } else if(input.data('id') == 3) {
          newVal = (val + 1) > 12 ? val : (val + 1);
        } else if(input.data('id') == 4) {
          newVal = (val + 1) > 30 ? val : (val + 1);
        }
        input.val( newVal ).trigger('keyup');
      }

    });

    $('.subscriberFilterModal').on('show.bs.modal', function () {
      $('.filter-input').trigger('keyup');
    });

    $('.subscriberFilterModal').on('hidden.bs.modal', function () {
      $(this).find('.text-wrapper').text('');
      $(this).find('.etTipAmount').val('');
      $(this).find('#tipNote').val('');
    });

    $(document).on('keyup', '.filter-input', function () {
      let activeFilter = $(this).closest('li').find('input[type="radio"]');
      activeFilter.prop('checked', true);
      let val = $(this).val() != '' ? parseFloat($(this).val()) : 0;
      if($(this).data('id') == 1) {
        $(this).next('.text-wrapper').text($(this).val() + ' USD');
      }else if($(this).data('id') == 2) {
        $(this).val(val > 200 ? val : val);
        $(this).next('.text-wrapper').text(currency($(this).val()).format() + ' USD');
      }else if($(this).data('id') == 3) {
        $(this).val(val > 12 ? 12 : val);
        $(this).next('.text-wrapper').text($(this).val() + ' Month');
      } else if($(this).data('id') == 4) {
        $(this).val(val > 30 ? 30 : val);
        $(this).next('.text-wrapper').text($(this).val() + ' Day');
      }
    });

    // moved from public/themes/default/assets/js/app.xj

    /*
    $(document).on("input", "#etTipAmount, .etTipAmount", function() {
      var myLength = $(this).val().length;
      if (myLength > 0) {
        $(this).closest(".tip-modal").find('#sendTip').removeAttr("disabled");
      } else {
        $(this).closest(".tip-modal").find('#sendTip').attr("disabled", "disabled");
      }
    });
    */

    $(document).on('click', ".sendTip", function () {
      let thisModal = $(this).closest('.tip-modal');
      const postId = thisModal.find('#post-id').val();
      $.post(`/ajax/send-tip-post/${postId}`, {
        //post_id: thisModal.find("#post-id").val(), 
        amount: thisModal.find("#etTipAmount").val(), 
        note: thisModal.find('#tipNote').val()
      }, function(data) {
        notify('Tip Sent!');
        thisModal.modal("hide");
        // location.reload(); TODO: Not needed
      });
    });

    $(document).on('click', ".sendUserTip", function () {
      let modal = $(this).closest('.tip-modal');
      const userId = modal.find('#user-id');
      $.post(`ajax/send-tip-user/${userId}`, {
        //user_id: modal.find("#user-id").val(), 
        amount: modal.find("#etTipAmount").val(), 
        note: modal.find('#tipNote').val()
      }, function(data) {
        if (data.status == 200) {
          notify('Tip Sent!');
          modal.modal("hide");
          // location.reload(); TODO: Not needed
        }
      });
    });

    // --- Purchase Post ---

    $(document).on('click', '.clickme_to-show_purchase_post_confirm', function (e) {
      e.preventDefault();
      const context = $(this);
      const postID = context.data('post_id');
      const url = `ajax/timeline-render-modal?template=_purchase_post_confirm&post_id=${postID}`;
      $.getJSON(url, function(response) {
        $('#global-modal-placeholder').html(response.html);
        $('#global-modal-placeholder').modal('toggle');
      });
    });

    $(document).on('click', '.clickme_to-purchase_post', function (e) {
      e.preventDefault();
      const context = $(this);
      const thisForm = context.closest('form');
      return $.ajax({
        url: thisForm.attr('action'),
        type: thisForm.attr('method'),
        data: thisForm.serializeArray()
      }).then( function(response) {
        console.log('purchase post: success');
        $('#global-modal-placeholder').modal('toggle'); // close modal
        const postID = context.data('post_id');
        const url = `ajax/timeline-render-modal?template=_post&post_id=${postID}`;
        $.getJSON(url, function(response) {
          $(`.timeline-posts .crate-post#tag-post_id_${postID}`).html(response.html);
        });
        notify('Purchased!');
      }).fail( function(response) {
        console.log('purchase post: failed');
      });
    });

    // --- Subsribe | Follow Timeline ---

    $(document).on('click', '.clickme_to-show_subscribe_confirm', function (e) {
      e.preventDefault();
      const context = $(this);
      const timelineID = context.data('timeline_id');
      const url = `ajax/timeline-render-modal?template=_subcribe_confirm&timeline_id=${timelineID}`;
      $.getJSON(url, function(response) {
        $('#global-modal-placeholder').html(response.html);
        $('#global-modal-placeholder').modal('toggle');
      });
    });

    $(document).on('click', '.clickme_to-purchase_subscription', function (e) {
      e.preventDefault();
      const context = $(this);
      const thisForm = context.closest('form');
      return $.ajax({
        url: thisForm.attr('action'),
        type: thisForm.attr('method'),
        data: thisForm.serializeArray()
      }).then( function(response) {
        console.log('subscribe timeline: success');
        $('#global-modal-placeholder').modal('toggle'); // close modal
        notify('Purchased!');
        window.location.reload(false); 
      }).fail( function(response) {
        console.log('subscribe timeline: failed');
      });
    });

    // --- Misc ---

    function notify(message,type,layout)
    {
        var n = noty({
            text: message,
            layout: 'bottomLeft',
            type : type ? type : 'success',
            theme : 'relax',
            timeout:5000,
            animation: {
                open: 'animated fadeIn', // Animate.css class names
                close: 'animated fadeOut', // Animate.css class names
                easing: 'swing', // unavailable - no need
                speed: 500 // unavailable - no need
            }
        });
    };
  }
});
