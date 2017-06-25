/*!
 * Plants and Planters - Henry William Ravenel
 * Copyright 2014-2016 Center for Digital Humanities - University of South Carolina
 */

if (typeof jQuery === 'undefined') {
  throw new Error('Henry William Ravenel requires jQuery');
} // if (typeof jQuery === 'undefined')

/* ============================================================
 * Ravenel: mail.js
 * http://ravenel.cdh.sc.edu/#feedback
 * ============================================================
 * Object for dealing with a contact form and sending it off as
 * an email through AJAX and PHP.
 * ============================================================ */

(function ($) {
  'use strict';

  var data;

  // MAIL CLASS DEFINITION
  // =====================

  var Mail = function () {
    this.addListeners();
  }; // var Mail = function ()

  Mail.prototype.addListeners = function () {
    var $this = this;

    $('#feedback').on('submit', function (event) {
      data = {
        url:      window.location.href,
        name:     $(this).find('[name="name"]').val().trim(),
        email:    $(this).find('[name="email"]').val().trim(),
        message:  $(this).find('[name="message"]').val().trim(),
        category: $(this).find('[name="category"]').val(),
        platform: platform.description,
        response: grecaptcha.getResponse()
      };

      if ($this.validate()) {
        $this.send();
      } // if ($this.validate())

      event.target.blur();
      event.preventDefault();
    });
  }; // Mail.prototype.addListeners = function ()

  Mail.prototype.validate = function () {
    // Validate the variable.
    if (data === null || data === undefined) { return false; }

    var properties = ['name', 'email', 'category', 'message', 'response'];
    for (var i = 0; i < properties.length; i++) {
      if (!data.hasOwnProperty(properties[i])) {
        if (properties[i] === 'response') {
          this.notify('danger', 'There was an error adding the reCAPTCHA response. Please try again.');
        } else {
          this.notify('danger', 'There was an error adding the ' + properties[i] + ' field. Please try again.');
        } // if (properties[i] === 'response')

        return false;
      } // if (!data.hasOwnProperty(properties[i]))
    } // for (var i = 0; i < properties.length; i++)

    // Validate the name.
    if (data.name === '') {
      this.notify('warning', 'Please input a name');
      return false;
    } else if (50 < data.name.length) {
      this.notify('warning', 'Please input a name with less than 50 characters.');
      return false;
    } // if (data.name === '')

    // Validate the email.
    if (data.email === '') {
      this.notify('warning', 'Please input an email.');
      return false;
    } else if (100 < data.email.length) {
      this.notify('warning', 'Please input an email with less than 100 characters.');
      return false;
    } else if (!data.email.match(/\S+@\S+/)) {
      this.notify('warning', 'Please input a valid email.');
      return false;
    } // if (data.email === '')

    // Validate the category.
    if (data.category === '') {
      this.notify('warning', 'Please select a category.');
      return false;
    } else if (data.category !== 'general' && data.category !== 'manuscripts' && data.category !== 'specimens') {
      this.notify('danger', 'Please do not alter the values of the category selection.');
      return false;
    } // if (data.category === '')

    // Validate the message.
    if (data.message === '') {
      this.notify('warning', 'Please input a message.');
      return false;
    } else if (300 < data.message.length) {
      this.notify('warning', 'Please input a message with less than 300 characters.');
      return false;
    } // if (data.message === '')

    // Validate the reCAPTCHA.
    if (data.response === '') {
      this.notify('warning', 'Please validate the reCAPTCHA.');
      return false;
    } // if (data.response === '')

    return true;
  }; // Mail.prototype.validate = function ()

  Mail.prototype.notify = function (type, text) {
    if ($('#feedback').prev().is('.alert')) {
      $('#feedback').prev().removeClass('alert-danger alert-info alert-success alert-warning').addClass('alert-' + type).html('<p>' + text + '</p>').parent().slideDown();
    } else {
      $('<div class="alert alert-' + type + '"><p>' + text + '</p></div>').insertBefore($('#feedback'));
    } // if ($('#feedback').prev().is('.alert'))
  }; // Mail.prototype.notify = function (type, text)

  Mail.prototype.send = function () {
    var $this = this;

    $.ajax({
      url:  window.location.href,
      type: 'POST',
      data: {
        data: data
      },
      success: function (result) {
        var response = JSON.parse(result);

        $this.notify(response.status, response.text);

        if (response.status === 'success') {
          $this.empty();
        } // if (response.status === 'success')
      }, // success: function (result)
      error: function (error) {
        $this.nofity('danger', error.responseText);
      } // error: function (error)
    });
  }; // Mail.prototype.send = function ()

  Mail.prototype.empty = function () {
    data = null;
    grecaptcha.reset();
    $('#feedback .form-control').val('');
  }; // Mail.prototype.empty = function ()

  // MAIL PLUGIN DEFINITION
  // ======================

  function Plugin(option) {
    return this.each(function () {
      new Mail();
    });
  } // function Plugin(option)

  $.fn.mail             = Plugin;
  $.fn.mail.Constructor = Mail;

}(jQuery));

/* ============================================================
 * Ravenel: browse.js
 * http://ravenel.cdh.sc.edu/browse
 * ============================================================
 * Basic functionality on the browse page. Seriously, this only
 * detects a mouse click.
 * ============================================================ */

(function ($) {
  'use strict';

  // BROWSE CLASS DEFINITION
  // =======================

  var Browse = function () {
    this.addListeners();
  }; // var Browse = function ()

  Browse.prototype.addListeners = function () {
    // For when a user clicks on a letter and requests to view a list.
    $('.browse-column').on('click', 'button', function (event) {
      if ($(this).next().is(':visible')) {
        $(this).next().slideUp('fast');
      } else if ($(this).parent().find('.list-group:visible').length) {
        $(this).parent().find('.list-group:visible').prev().css('border-bottom-width', '').next().slideUp('fast');
        $(this).next().slideDown('fast');
      } else {
        $(this).next().slideDown('fast');
      } // if ($(this).next().is(':visible'))

      event.target.blur();
    });
  }; // Browse.prototype.addListeners = function ()

  // BROWSE PLUGIN DEFINITION
  // ========================

  function Plugin(option) {
    return this.each(function () {
      new Browse();
    });
  } // function Plugin(option)

  $.fn.browse             = Plugin;
  $.fn.browse.Constructor = Browse;

}(jQuery));

/* ============================================================
 * Ravenel: search.js
 * http://ravenel.cdh.sc.edu/search
 * ============================================================
 * Functionality used for the search page. This applies to both
 * the search bar and search results.
 * ============================================================ */

(function ($) {
  'use strict';

  // SEARCH CLASS DEFINITION
  // =======================

  var Search = function (isResults) {
    if (isResults) {
      // Initialize all three tables.
      this.initializeTable($('#journals > table'));
      this.initializeTable($('#letters > table'));
      this.initializeTable($('#specimens > table'));

      $.ajax({
        url:      'includes/search-results?type=photographs&' + window.location.search.substring(1),
        dataType: 'json',
        success:  function (result) {
          $('#photographs').html(result['images']);
          $('.search-tabs a[href="#photographs"]').parent().removeClass('disabled').find('span > span').text(result['images'].length).counter();
        } // success: function (result)
      });
    } else {
      // Initialize the Bootstrap Tour.
      this.initializeTour();

      // Add event listeners.
      this.addListeners();
    } // if (isResults)
  }; // var Search = function (isResults)

  Search.prototype.initializeTour = function () {
    var steps = [];
    $('[data-step]').each(function () {
      steps.push(new Object({
        element:   $(this),
        title:     $(this).data('step-title'),
        content:   $(this).data('step-content'),
        placement: $(this).data('step-placement')
      }));
    });

    var tour = new Tour({
      steps:    steps,
      backdrop: true
    });

    tour.init();

    $('#tourStart').on('click', function (event) {
      tour.restart();
      event.target.blur();
    });
  }; // Search.prototype.initializeTour = function ()

  Search.prototype.initializeTable = function (element) {
    var id = element.parent().attr('id');

    var columns;
    if (id === 'journals') {
      columns = [
        {
          data: 'pointer'
        }, {
          data: 'date'
        }, {
          data: 'people'
        }, {
          data: 'geogra'
        }, {
          data: 'transc'
        }, {
          data: 'scient'
        }, {
          data: 'common'
        }, {
          data: 'title'
        }, {
          data: 'lattit'
        }, {
          data: 'descri'
        }
      ];
    } else if (id === 'letters') {
      columns = [
        {
          data: 'pointer'
        }, {
          data: 'date'
        }, {
          data: 'people'
        }, {
          data: 'title'
        }, {
          data: 'geogra'
        }, {
          data: 'descri'
        }, {
          data: 'scient'
        }, {
          data: 'common'
        }, {
          data: 'lattit'
        }, {
          data: 'transc'
        }
      ];
    } else if (id === 'specimens') {
      columns = [
        {
          data: 'thumbnailurl'
        }, {
          data: 'scientificName'
        }, {
          data: 'eventDate'
        }, {
          data: 'identifiedBy'
        }, {
          data: 'location'
        }, {
          data: 'coordinates'
        }, {
          data: 'habitat'
        }, {
          data: 'recordedBy'
        }, {
          data: 'cultivationStatus'
        }
      ];
    } // if (id === 'journals')

    var table = element.DataTable({
      processing:   true,
      ajax:         'includes/search-results?type=' + id + '&' + window.location.search.substring(1),
      filter:       false,
      lengthChange: true,
      autoWidth:    true,
      scrollY:      '50vh',
      columns:      columns,
      columnDefs: [
        {
          targets: this.getColumnTargets(element),
          visible: false
        }
      ],
      initComplete: function () {
        $('.search-tabs a[href="#' + id + '"]').parent().removeClass('disabled').find('span > span').text(table.data().count()).counter();

        if (id !== 'journals') {
          $('#' + id).css({
            position: '',
            display: '',
            'pointer-events': '',
          });
        } // if (id === 'letters')

        // Add an event listener for when a user clicks on a column toggle.
        $('#' + id + ' > [data-toggle="columns"]').on('click', 'li > a', function (event) {
          var column = table.column($(this).attr('data-column'));

          column.visible(!column.visible());

          $(this).toggleClass('is-visible is-not-visible');

          event.target.blur();
          event.preventDefault();
        });
      } // initComplete: function ()
    });
  }; // Search.prototype.initializeTable = function (element)

  Search.prototype.addListeners = function () {
    // For when a user wants to add a new row to the advanced search.
    $('#addAdvancedRow').on('click', function (event) {
      // Do not add more than 6 fields.
      if ($('#advancedForm .form-group').length === 7) { return; }

      // Grab, reset, and append the operators.
      var operators = $('#advancedForm select[name="operators[]"]').last().parent().clone();
      operators.find('select').val('and');
      $(operators).insertAfter($('#advancedForm input[name="input[]"]').last().parent());

      // Grab and reset the options.
      var options = $('#advancedForm select[name="options[]"]').last().parent().clone();
      options.find('select').val('CISOSEARCHALL');

      // Grab and reset the text input.
      var input = $('#advancedForm input[name="input[]"]').last().parent().clone();
      input.find('input').val('');

      // Append the options and input.
      $('<div class="form-group">' + options.get(0).outerHTML + input.get(0).outerHTML + '</div>').insertBefore($('#advancedForm > fieldset > .clearfix'));

      // Remove the button for user inability to add more and avoid confusion.
      if ($('#advancedForm .form-group').length === 7) {
        $(this).remove();
      } // if ($('#advancedForm .form-group').length === 7)
    });

    // For when a user types into the basic search.
    $('#basicSearch').on('keyup', function (event) {
      $('#basicLive').text($(this).val().trim() === '' ? '' : 'Search everything for ' + $(this).val().trim().replace(/ /g, ' and '));
    });

    // For when a user types into the advanced search.
    $('#advancedForm').on('change keyup', 'input, select', function () {
      var text    = 'Searching';
      var input   = [];
      var logic   = [];
      var options = [];

      // Populate above variables.
      $('#advancedForm .form-control').each(function () {
        if ($(this).is('input')) {
          if ($(this).val().trim() === '') { return true; }

          input.push($(this).val().trim());
        } else if ($(this).prop('name') === 'options[]') {
          options.push($(this).find(':selected').text() === 'All Fields' ? 'everything' : $(this).find(':selected').text());
        } else {
          logic.push($(this).find(':selected').text());
        } // if ($(this).is('input'))
      });

      // Concatenate the variables.
      for (var i = 0; i < input.length; i++) {
        if (i !== 0) {
          text += ' ' + logic[i - 1].toLowerCase();
        } // if (i !== 0)

        text += ' ' + options[i] + ' for ' + input[i];
      } // for (var i = 0; i < input.length; i++)

      $('#advancedLive').text(text);
    });

    // For when a user submits the advanced search.
    $('#advancedForm').on('submit', function (event) {
      $('#advancedForm .form-group:not(.clearfix)').each(function () {
        if ($(this).find('input').val().trim() === '') {
          $(this).prev().find('select[name="operators[]"]').parent().remove();
          $(this).remove();
        } // if ($(this).find('input').val().trim() === '')
      });
    });
  }; // Search.prototype.addListeners = function ()

  Search.prototype.getColumnTargets = function (element) {
    var targets = [];

    for (var i = 5; i < element.find('thead > tr > th').length; i++) {
      targets.push(i);
    } // for (var i = 5; i < element.find('thead > tr > th').length; i++)

    return targets;
  }; // Search.prototype.getColumnTargets = function (element)

  // SEARCH PLUGIN DEFINITION
  // ========================

  function Plugin(option) {
    return this.each(function () {
      new Search($('[data-step]').length === 0);
    });
  } // function Plugin(option)

  $.fn.search             = Plugin;
  $.fn.search.Constructor = Search;

}(jQuery));

/* ============================================================
 * Ravenel: viewer.js
 * http://ravenel.cdh.sc.edu/viewer
 * ============================================================
 * Functionality used for the viewer page. This includes both a
 * transcript or specimen being viewed.
 * ============================================================ */

(function ($) {
  'use strict';

  // VIEWER CLASS DEFINITION
  // =======================

  var Viewer = function () {
    $('#mainViewer').css('height', $('.panel-reading').height());

    this.addListeners();

    if (document.title.indexOf('Manuscript Viewer') > -1) {
      this.addManuscriptListeners();
    } else if (document.title.indexOf('Specimen Viewer') > -1) {
      this.addSpecimenListeners();
    } else {
      throw new Error('Error in determing type of viewer. Possible problem: Title is not typed properly.');
    } // if (document.title.indexOf('Manuscript Viewer') > -1)

    setTimeout(function () {
      if (!$('#mainImage').hasClass('ui-draggable')) {
        $('#mainImage').load();
      } // if (!$('#mainImage').hasClass('ui-draggable'))
    }, 800);
  }; // var Viewer = function ()

  Viewer.prototype.addListeners = function () {
    $('.row').on('click', '.panel-viewer .zoom-plus', function (event) {
      var image = $(this).parentsUntil('.panel-viewer').parent().find('> img');
      var width = image.innerWidth() * 1.2;

      // Increase the width.
      image.css('max-width', width);

      setTimeout(function () {
        image.parent().find('.thumbnail-overlay').css({
          height:      image.parent().innerHeight() / image.innerHeight() * 100 + '%',
          'max-width': image.parent().innerWidth() / width * 100 + '%'
        });
      }, 100);

      // Catch if the image gets too big.
      if (image.attr('data-width') * (5 / 3) < width) {
        $(this).addClass('disabled');
      } // if (image.attr('data-width') * (5 / 3) < width)

      $(this).parentsUntil('.panel-tools').find('.zoom-minus, .refresh').removeClass('disabled');

      event.target.blur();
      event.preventDefault();
    });

    $('.row').on('click', '.panel-viewer .zoom-minus', function (event) {
      var image = $(this).parentsUntil('.panel-viewer').parent().find('> img');
      var width = image.innerWidth() * 0.8;

      // Decrease the width.
      image.css('max-width', width);

      setTimeout(function () {
        image.parent().find('.thumbnail-overlay').css({
          height:      image.parent().innerHeight() / image.innerHeight() * 100 + '%',
          'max-width': image.parent().innerWidth() / width * 100 + '%'
        });
      }, 100);

      // Catch if the image gets too small.
      if (width < image.attr('data-width') * (1 / 3)) {
        $(this).addClass('disabled');
      } // if (width < image.attr('data-width') * (1 / 3))

      $(this).parentsUntil('.panel-tools').find('.zoom-plus, .refresh').removeClass('disabled');

      event.target.blur();
      event.preventDefault();
    });

    // Refreshes an image to the original size.
    $('.row').on('click', '.panel-viewer .refresh', function (event) {
      var image = $(this).parentsUntil('.panel-viewer').parent().find('> img');

      image.css({
        top:         0,
        left:        0,
        'max-width': '100%'
      });

      setTimeout(function () {
        image.parent().find('.thumbnail-overlay').css({
          top:         0,
          left:        0,
          height:      image.parent().innerHeight() / image.innerHeight() * 100 + '%',
          'max-width': '100%'
        });
      }, 100);

      $(this).addClass('disabled').parentsUntil('.panel-tools').find('.zoom-plus, .zoom-minus').removeClass('disabled');

      event.target.blur();
      event.preventDefault();
    });

    // Live update the thumbnail as the image is drug around.
    $('.row').on('mousemove', '.panel-viewer > img', function (event) {
      if (event.which == 1) {
        $(this).parent().find('.thumbnail-overlay').css({
          top:  $(this).position().top * ($(this).parent().find('.panel-thumbnail').height() / $(this).height()) * -1,
          left: $(this).position().left * ($(this).parent().find('.panel-thumbnail').width() / $(this).width()) * -1
        });
      } // if (event.which == 1)

      $(this).parent().find('.refresh').removeClass('disabled');
    });

    // Once the image is loaded, have all of its functionality appear.
    $('#mainImage').on('load', function () {
      $(this).overlay().draggable().parent().resizable({
        handles:  'e',
        minWidth: 340,
        maxWidth: $(window).width() * 0.6
      }).find('.ui-resizable-e').html('<i class="fa fa-chevron-left"></i><i class="fa fa-chevron-right"></i>');

      $(this).attr('data-width', $(this).innerWidth());

      $('#mainViewer .image-loading-icon').fadeOut();
    });

    // Detect the main image being resized.
    $('#mainViewer').on('resize', function () {
      // Resize the other panel accordingly.
      // 30 is for margin-left + margin-right of .panel-reading
      $('.panel-reading').css({
        width:  $(this).parent().width() - $(this).width() - 30,
        height: $(this).height()
      });

      // Resize the thumbnail accordingly.
      $(this).find('.thumbnail-overlay').css({
        height:      $(this).height() / $(this).find('> img').height() * 100 + '%',
        'max-width': $(this).width() / $(this).find('> img').width() * 100 + '%'
      });
    });
  }; // Viewer.prototype.addListeners = function ()

  Viewer.prototype.addManuscriptListeners = function () {
    var $this = this;

    // Detect when the resize is created, give users the ability to select specimens.
    $('#mainViewer').on('resizecreate', function () {
      $('.viewer-specimen').addClass('viewer-specimen-ready');
    });

    // For when a user wants to view a specimen while viewing a manuscript.
    $('.viewer-specimen').on('click', function () {
      if ($('#mainViewer').resizable('instance') === undefined && $('#specimenSideViewer').length === 0) { return; }

      if ($('#specimenSideViewer').length === 0) {
        $this.rearrange();
      } // if ($('#specimenSideViewer').length === 0)

      $this.getData({
        catalogNumber: $(this).data('catalog')
      }, function (result) {
        var response = JSON.parse(result);

        $this.renderSide(response.image, response.metadata, 'specimen');
        $this.reinitialize();
      });
    });
  }; // Viewer.prototype.addManuscriptListeners = function ()

  Viewer.prototype.addSpecimenListeners = function () {
    var $this = this;

    $('#specimenJournalMentions .text-center').on('click', function () {
      $(this).css('outline', 'none');

      if ($('#mainViewer').resizable('instance') === undefined && $('#manuscriptSideViewer').length === 0) { return; }

      if ($('#manuscriptSideViewer').length === 0) {
        $this.rearrange();
      } // if ($('#manuscriptSideViewer').length === 0)

      $this.getData({
        pointer:   $(this).data('pointer'),
        institute: $(this).data('institute')
      }, function (result) {
        var response = JSON.parse(result);

        $this.renderSide(response.image, response.metadata, 'manuscript');
        $this.reinitialize();
      });
    });
  }; // Viewer.prototype.addSpecimenListeners = function ()

  Viewer.prototype.rearrange = function () {
    if ($('#specimenSideViewer').length || $('#manuscriptSideViewer').length) { return; }

    $('#mainViewer').addClass('width-transition').css('width', '').removeClass('width-transition');

    $('.panel-reading').hide();

    // Move transcript to the bottom metadata.
    if ($('.panel-reading pre').length) {
      $('<li class="list-group-item" data-field="transc"><h4 class="list-group-item-heading">Transcript</h4>' + $('.panel-reading .panel-content').html() + '</li>').insertAfter($('#viewerMetadata > ul > li:nth-child(4)'));

      $('#toggleRightPanel').show();
    } // if ($('.panel-reading pre').length)

    // Create a row for metadata.
    if ($('#specimenJournalMentions').length) {
      $('<hr><div class="row"><div class="col-sm-6" id="specimenData">' + $('.panel-reading .panel-content').html() + '</div></div>').insertBefore($('.container > hr'));
    } // if ($('#specimenJournalMentions').length)

    $('#mainViewer').resizable('destroy');
  }; // Viewer.prototype.rearrange = function ()

  Viewer.prototype.reinitialize = function () {
    $('[data-toggle="tooltip"]').tooltip();

    $('.fb').fancybox({
      type:       'image',
      beforeLoad: function () {
        this.title = this.element.parentsUntil('.panel-viewer').parent().find('> img').prop('alt');
      } // beforeLoad: function ()
    });
  }; // Viewer.prototype.reinitialize = function ()

  Viewer.prototype.renderSide = function (image, data, type) {
    if ($('#' + type + 'SideViewer').length === 0) {
      $($(image)).insertAfter($('.panel-reading'));

      if (type === 'manuscript') {
        $($(data)).insertAfter($('#specimenData'));
      } else if (type === 'specimen') {
        $($(data)).insertAfter($('#viewerMetadata'));
      } else {
        throw new Error('Improper type "' + type + '" discovered while rendering.');
      } // if (type === 'manuscript')
    } else {
      $('#' + type + 'Data').get(0).outerHTML       = data;
      $('#' + type + 'SideViewer').get(0).outerHTML = image;
    } // if ($('#' + type + 'SideViewer').length === 0)

    $('#' + type + 'Data').fadeIn();
    $('#' + type + 'SideViewer').css('height', '50px').addClass('height-transition').css('height', $('#mainViewer').height()).fadeIn(function () {
      $(this).find('> img').overlay().draggable();
    });

    // CONTENTdm does not return images as fast as Symbiota.
    if (type === 'manuscript') {
      $('#manuscriptSideViewer > img').on('load', function () {
        $(this).overlay();
      });
    } // if (type === 'manuscript')
  }; // Viewer.prototype.renderSide = function (image, data, type)

  Viewer.prototype.getData = function (data, success) {
    $.ajax({
      url:     'http://ravenel.cdh.sc.edu/viewer',
      type:    'GET',
      data:    data,
      success: success,
      error:   function (error) {
        throw new Error(error.responseText);
      } // error: function (error)
    });
  }; // Viewer.prototype.getData = function (data, success)

  // VIEWER PLUGIN DEFINITION
  // ========================

  function Plugin(option) {
    return this.each(function () {
      new Viewer();
    });
  } // function Plugin(option)

  $.fn.viewer             = Plugin;
  $.fn.viewer.Constructor = Viewer;

}(jQuery));

/* ============================================================
 * Ravenel: counter.js
 * http://ravenel.cdh.sc.edu/
 * ============================================================
 * This functionality is used for an added visual effect when a
 * number is displayed.
 * ============================================================ */

$.fn.extend({
  // Counts a number from 0 to its value.
  counter: function () {
    return this.each(function () {
      var $this = this;

      jQuery({
        Counter: 0
      }).animate({
        Counter: $this.innerText
      }, {
        duration: 1200,
        easing:   'swing',
        step:     function () {
          $this.innerHTML = Math.ceil(this.Counter);
        } // step: function ()
      });
    });
  }, // counter: function ()

  overlay: function () {
    return this.each(function () {
      $(this).parent().find('.thumbnail-overlay').css({
        height: $(this).parent().height() / $(this).height() * 100 + '%'
      });
    });
  } // overlay: function ()
});

/* ============================================================
 * Like gravity, the code below executes the code above.
 * ============================================================ */

// Call the functions. No need for an extra file.
if (document.title.indexOf('Browse') > -1) {
  $(document).browse();
} else if (document.title.indexOf('Search') > -1) {
  $(document).search();
} else if (document.title.indexOf('Viewer') > -1) {
  $(document).viewer();
} else if (document.title.indexOf('Portraits') > -1) {
  $('#gallery').unitegallery({
    theme_enable_preloader:    true,
    theme_preloading_height:   200,

    gallery_theme:             'tiles',
    gallery_width:             '100%',
    gallery_min_width:         150,

    tiles_space_between_cols:  20,
    tiles_col_width:           220,

    tile_enable_shadow:        true,
    tile_enable_textpanel:     true
  });
} // if (document.title.indexOf('Browse') > -1)

// Execute when the DOM is ready.
$(document).ready(function () {
  // For when an empty search is attempted.
  $('#submit-search').on('submit', function (event) {
    if ($(this).find('input').val().trim() === '') {
      event.preventDefault();
    } // if ($(this).find('input').val().trim() === '')
  });

  // For when the user clicks the magnifying glass in the navigation.
  $('#search').on('click', function (event) {
    event.target.blur();

    if ($('header form').is(':visible')) {
      $('header form').fadeOut('fast');
    } else {
      $('header form').fadeIn(function () {
        $(this).find('input').focus();
      });
    } // if ($('header form').is(':visible'))

    event.preventDefault();
  });

  // For when the user decides to close an alert and does not want the element removed from the DOM.
  $('.alert').on('click', 'button.close', function (event) {
    $(this).parent().slideUp();
  });

  // Initialize Bootstrap's tooltips.
  $('[data-toggle="tooltip"]').tooltip();

  // Initialize Fancybox.
  $('[data-effect="fancybox"]').fancybox({
    type:       'image',
    beforeLoad: function () {
      this.title = this.element.parentsUntil('.panel-viewer').parent().find('> img').prop('alt');
    } // beforeLoad: function ()
  });

  // Initialize Slick.
  $('[data-effect="slick"][data-slick="on"]').slick({
    dots:           true,
    infinite:       true,
    slidesToShow:   $('[data-effect="slick"][data-slick="on"]').data('slick-amount'),
    slidesToScroll: $('[data-effect="slick"][data-slick="on"]').data('slick-amount')
  });

  // Initialize Counters.
  $('[data-effect="count"]').counter();

  // Initialize Mail.
  $(document).mail();

  // Determine browser compatibility.
  if (platform.name === 'IE' && parseFloat(platform.version) < 10) {
    $('#alertBrowser p').text('Your browser, Internet Explorer, version ' + platform.version + ' is not recommended to view this website. Please upgrade to at least version 10 or switch to another browser.').parent().removeClass('hide');
  } else if (platform.name === 'Opera' && parseFloat(platform.version) < 15) {
    $('#alertBrowser p').text('Your browser, Opera, version ' + platform.version + ' is not recommended to view this website. Please upgrade to at least version 15 or switch to another browser.').parent().removeClass('hide');
  } else if (platform.name === 'Safari' && parseFloat(platform.version) < 5.1) {
    $('#alertBrowser p').text('Your browser, Safari, version ' + platform.version + ' is not recommended to view this website. Please upgrade to at least version 5.1 or switch to another browser.').parent().removeClass('hide');
  } // if (platform.name === 'IE' && parseFloat(platform.version) < 10)
});
