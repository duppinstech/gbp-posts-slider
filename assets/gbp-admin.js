/* global gbpAdmin, jQuery */
(function ($) {
  'use strict';

  var i18n        = gbpAdmin.i18n;
  var allLocations = [];   // full list for client-side search

  /* ── Utility ─────────────────────────────────────────────────────── */

  function ajax(action, data, success, error) {
    $.post(gbpAdmin.ajaxUrl, $.extend({ action: action, nonce: gbpAdmin.nonce }, data))
      .done(function (res) {
        if (res.success) { success(res.data); }
        else             { error(res.data || i18n.error); }
      })
      .fail(function () { error(i18n.error); });
  }

  function showError(el, msg) {
    $(el).text(msg).show();
  }

  function hideError(el) {
    $(el).hide().text('');
  }

  /* ── Render helpers ──────────────────────────────────────────────── */

  function renderLocationList(locations) {
    var $list = $('#gbps-location-list').empty();

    if (!locations.length) {
      $list.html('<p class="gbps-picker-empty">' + i18n.noLocations + '</p>');
      return;
    }

    locations.forEach(function (loc) {
      var $item = $('<div class="gbps-location-item" tabindex="0">')
        .attr('data-id',       loc.id)
        .attr('data-name',     loc.name)
        .attr('data-subtitle', loc.subtitle || '');

      var html = '<span class="dashicons dashicons-store"></span>'
        + '<div class="gbps-location-item-text">'
        + '<strong>' + escHtml(loc.name) + '</strong>';

      if (loc.subtitle) {
        html += '<span class="gbps-location-subtitle">' + escHtml(loc.subtitle) + '</span>';
      }

      html += '</div>';
      $item.html(html);
      $list.append($item);
    });
  }

  function escHtml(str) {
    return $('<div>').text(str).html();
  }

  /* ── Load accounts on page ready ────────────────────────────────── */

  $(function () {

    var $accountSelect   = $('#gbps-account-select');
    var $locationStep    = $('#gbps-location-step');
    var $locationSearch  = $('#gbps-location-search');
    var $saveRow         = $('#gbps-save-row');
    var $selectedPreview = $('#gbps-selected-preview');

    // Only run if the picker is present on the page.
    if (!$accountSelect.length) return;

    // Fetch accounts immediately.
    ajax(
      'gbp_fetch_accounts',
      {},
      function (accounts) {
        $accountSelect.empty().append(
          $('<option value="">').text(i18n.selectAccount)
        );
        accounts.forEach(function (acct) {
          $('<option>').val(acct.id).text(acct.name).appendTo($accountSelect);
        });

        // Pre-select if we already have a saved account ID.
        var savedAccount = $('#gbps-account-id-input').val();
        if (savedAccount) {
          $accountSelect.val(savedAccount);
          if ($accountSelect.val()) {
            loadLocations(savedAccount);
          }
        }
      },
      function (msg) {
        $accountSelect.empty().append($('<option value="">').text('— ' + msg + ' —'));
        showError('#gbps-account-error', msg);
      }
    );

    /* ── Account changed ─────────────────────────────────────────── */

    $accountSelect.on('change', function () {
      var accountId = $(this).val();
      hideError('#gbps-account-error');
      $locationStep.hide();
      $saveRow.hide();
      $selectedPreview.hide();
      allLocations = [];

      if (!accountId) return;
      loadLocations(accountId);
    });

    function loadLocations(accountId) {
      $locationStep.show();
      $('#gbps-location-list').html(
        '<p class="gbps-picker-loading"><span class="spinner is-active"></span> ' + i18n.loadingLocations + '</p>'
      );
      hideError('#gbps-location-error');

      ajax(
        'gbp_fetch_locations',
        { account_id: accountId },
        function (locations) {
          allLocations = locations;
          renderLocationList(locations);

          // Pre-select saved location.
          var savedLoc = $('#gbps-location-id-input').val();
          if (savedLoc) {
            var match = locations.find(function (l) { return l.id === savedLoc; });
            if (match) { selectLocation(match, accountId); }
          }
        },
        function (msg) {
          $('#gbps-location-list').empty();
          showError('#gbps-location-error', msg);
        }
      );
    }

    /* ── Location list click / keyboard ─────────────────────────── */

    $('#gbps-location-list').on('click keydown', '.gbps-location-item', function (e) {
      if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) return;
      e.preventDefault();

      var $item     = $(this);
      var locId     = $item.data('id').toString();
      var locName   = $item.data('name');
      var accountId = $accountSelect.val();

      selectLocation({ id: locId, name: locName }, accountId);
    });

    function selectLocation(loc, accountId) {
      // Highlight selected item.
      $('#gbps-location-list .gbps-location-item').removeClass('is-selected');
      $('#gbps-location-list .gbps-location-item[data-id="' + loc.id + '"]').addClass('is-selected');

      // Populate hidden inputs.
      $('#gbps-account-id-input').val(accountId || $accountSelect.val());
      $('#gbps-location-id-input').val(loc.id);
      $('#gbps-location-name-input').val(loc.name);

      // Show preview + save button.
      $selectedPreview
        .html('<span class="dashicons dashicons-yes-alt"></span> ' + i18n.selected + ': <strong>' + escHtml(loc.name) + '</strong>')
        .show();
      $saveRow.show();
    }

    /* ── Location search filter ──────────────────────────────────── */

    $locationSearch.on('input', function () {
      var q = $(this).val().toLowerCase().trim();
      if (!q) {
        renderLocationList(allLocations);
        return;
      }
      var filtered = allLocations.filter(function (l) {
        return l.name.toLowerCase().includes(q) ||
               (l.subtitle || '').toLowerCase().includes(q);
      });
      renderLocationList(filtered);
    });

    /* ── "Change Business" button ────────────────────────────────── */

    $('#gbps-change-location').on('click', function () {
      $('#gbps-location-saved').hide();
      $('#gbps-location-picker').show();
      $saveRow.show();
    });

  }); // end document ready

}(jQuery));
