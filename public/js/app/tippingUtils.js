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
      let modal = $(this).closest('.tip-modal');
      $.post(SP_source() + 'ajax/send-tip-post', {post_id: modal.find("#post-id").val(), amount: modal.find("#etTipAmount").val(), note: modal.find('#tipNote').val()}, function(data) {
        if (data.status == 200) {
          notify(data.message,'success');
          modal.modal("hide");
          // location.reload(); TODO: Not needed
        }
      });
    });

    $(document).on('click', ".sendUserTip", function () {
      let modal = $(this).closest('.tip-modal');
      $.post(SP_source() + 'ajax/send-tip-user', {user_id: modal.find("#user-id").val(), amount: modal.find("#etTipAmount").val(), note: modal.find('#tipNote').val()}, function(data) {
        if (data.status == 200) {
          notify(data.message,'success');
          modal.modal("hide");
          // location.reload(); TODO: Not needed
        }
      });
    });
  }
});
