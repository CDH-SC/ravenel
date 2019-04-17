define([
  'dojo/_base/declare',
  'dojo/dom-construct',
  'app/config',
  'dojo/text!app/view/view.search.html',
  'ext/geojsonlayer',
  'esri/layers/GraphicsLayer',
  'esri/graphic',
  'esri/geometry/webMercatorUtils',
  'esri/geometry/Point',
  'esri/InfoTemplate',
  'esri/renderers/SimpleRenderer'
  ], function (declare, domConstruct, config, view, GeoJsonLayer, GraphicsLayer, Graphic, WebMercatorUtils, Point, InfoTemplate, SimpleRenderer) {
    var searchVM = function (map) {
      var self                = this;
      self.map                = map;
      geoJsonLayer            = null;
      defaultExtent           = null;

      letters                 = ko.observableArray([]);
      lettersTo               = ko.observableArray([]);
      lettersTo2              = ko.observableArray([]);

      lettersFrom             = ko.observableArray([]);
      lettersFrom2            = ko.observableArray([]);

      selectedFirstLetter     = ko.observable();
      selectedFirstLetter2    = ko.observable();

      selectedSecondLetter    = ko.observable();
      selectedSecondLetter2   = ko.observable();

      showSecondLetter        = ko.observable(false);
      showFourthLetter        = ko.observable(false);

      showSearch              = ko.observable(true);
      searchResults           = ko.observableArray([]);

      selectedFirstLetter.subscribe(function (newValue) {
        $('[data-hide="bottom"], hr').slideUp();

        showSecondLetter(false);

        lettersTo.removeAll();
        ko.utils.arrayForEach(letters(), function (letter) {
          if (letter.letterFrom.trim() == newValue) {
            lettersTo.push(letter);
          } // if (letter.letterFrom.trim() == newValue)
        });

        lettersTo.sort(function (a, b) {
          if (a.letterTo.toLowerCase() < b.letterTo.toLowerCase()) {
            return -1;
          } else if (a.letterTo.toLowerCase() > b.letterTo.toLowerCase()) {
            return 1;
          } else {
            return 0;
          } // if (a.letterTo.toLowerCase() < b.letterTo.toLowerCase())
        });

        lettersTo.unshift({
          'letterFrom': newValue,
          'letterTo':   'All',
          'FID':        -1
        });

        selectedSecondLetter(null);
      });

      selectedFirstLetter2.subscribe(function (newValue) {
        $('[data-hide="top"], hr').slideUp();

        showFourthLetter(false);

        lettersFrom2.removeAll();
        ko.utils.arrayForEach(letters(), function (letter) {
          if (letter.letterTo.trim() == newValue) {
            lettersFrom2.push(letter);
          } // if (letter.letterTo.trim() == newValue)
        });

        lettersFrom2.sort(function (a, b) {
          if (a.letterFrom.toLowerCase() < b.letterFrom.toLowerCase()) {
            return -1;
          } else if (a.letterFrom.toLowerCase() > b.letterFrom.toLowerCase()) {
            return 1;
          } else {
            return 0;
          } // if (a.letterFrom.toLowerCase() < b.letterFrom.toLowerCase())
        });

        lettersFrom2.unshift({
          'letterTo':   newValue,
          'letterFrom': 'All',
          'FID':        -1
        });

        selectedSecondLetter2(false);
      });

      selectedSecondLetter.subscribe(function (newValue) {
        if (newValue) {
          showSecondLetter(true);
        } // if (newValue)
      });

      selectedSecondLetter2.subscribe(function (newValue) {
        if (newValue) {
          showFourthLetter(true);
        } // if (newValue)
      });

      rend = new SimpleRenderer(config.letterLineRenderer);

      template = new InfoTemplate();
      template.setTitle("<b>${Item}</b>");
      template.setContent('<div class="list-group"><p class="list-group-item-text clearfix"><strong class="list-group-item-text-label">From:</strong><span class="list-group-item-text-data">${From_}</span><p class="list-group-item-text clearfix"><strong class="list-group-item-text-label">To:</strong><span class="list-group-item-text-data">${To}</span><p class="list-group-item-text clearfix"><strong class="list-group-item-text-label">Route:</strong><span class="list-group-item-text-data">${From_Loc} to ${To_Loc}</span><p class="list-group-item-text clearfix"><strong class="list-group-item-text-label">Date:</strong><span class="list-group-item-text-data">${Date}</span><p class="list-group-item-text clearfix"><strong class="list-group-item-text-label">Link:</strong><span class="list-group-item-text-data"><a href="http://ravenel.cdh.sc.edu/viewer/transcript/Carolina/${segmentID}/">View Letter</a></span></p></div>');

      self.init = function () {
        domConstruct.place(view, dojo.byId('tools-div'));
        ko.applyBindings(self, dojo.byId('searchVM'));
        addGeoJsonLayer(config.jsonPath);

        gl = new GraphicsLayer({
          id: 'selectLayer'
        });
        gl.setInfoTemplate(template);

        self.map.addLayer(gl);
      }; // self.init = function ()

      clickSearch = function () {
        console.log('Click Search 1');

        geoJsonLayer.setVisibility(false);
        setSelectedLetter();
        showSearch(false);
      }; // clickSearch = function ()

      clickSearch2 = function () {
        console.log('Click Search 2');

        geoJsonLayer.setVisibility(false);
        setSelectedLetter2();
        showSearch(false);
      }; // clickSearch2 = function ()

      clickReset = function () {
        gl.clear();

        geoJsonLayer.setVisibility(true);

        selectedFirstLetter("");
        selectedSecondLetter(null);
        selectedFirstLetter2("");
        selectedSecondLetter(null);

        if (self.map.extent.xmin !== defaultExtent.xmin && self.map.extent.ymin !== defaultExtent.ymin && self.map.extent.xmax !== defaultExtent.xmax && self.map.extent.ymax !== defaultExtent.ymax) {
          console.log('Resetting extent.');

          self.map.setExtent(defaultExtent);
        } // if (self.map.extent.xmin !== defaultExtent.xmin && self.map.extent.ymin !== defaultExtent.ymin && self.map.extent.xmax !== defaultExtent.xmax && self.map.extent.ymax !== defaultExtent.ymax)

        $('[data-hide]:not([data-bind]), hr').slideDown();
      }; // clickReset = function ()

      clickBack = function () {
        showSearch(true);
      }; // clickBack = function ()

      setSelectedLetter = function () {
        gl.clear();
        searchResults.removeAll();

        if (selectedSecondLetter) {
          var extender = {
            xmin:             0,
            ymin:             0,
            xmax:             0,
            ymax:             0,
            spatialReference: defaultExtent.spatialReference
          };

          for (var i = 0; i < geoJsonLayer.graphics.length; i++) {
            if ((selectedSecondLetter() === 'All' && selectedFirstLetter() == geoJsonLayer.graphics[i].attributes.From_.trim()) || (selectedSecondLetter() == geoJsonLayer.graphics[i].attributes.To.trim() && selectedFirstLetter() == geoJsonLayer.graphics[i].attributes.From_.trim())) {
              var graphic = new Graphic(WebMercatorUtils.geographicToWebMercator(geoJsonLayer.graphics[i].geometry), geoJsonLayer.graphics[i].symbol, geoJsonLayer.graphics[i].attributes);

              gl.add(graphic);

              extender = adjustExtent(extender, graphic._extent);

              searchResults.push(geoJsonLayer.graphics[i].attributes);
            } // if ((selectedSecondLetter() === 'All' && selectedFirstLetter() == geoJsonLayer.graphics[i].attributes.From_.trim()) || (selectedSecondLetter() == geoJsonLayer.graphics[i].attributes.To.trim() && selectedFirstLetter() == geoJsonLayer.graphics[i].attributes.From_.trim()))
          } // for (var i = 0; i < geoJsonLayer.graphics.length; i++)

          self.map.setExtent(new esri.geometry.Extent(extender));
        } // if (selectedSecondLetter)

        gl.redraw();
      }; // setSelectedLetter = function ()

      setSelectedLetter2 = function () {
        gl.clear();
        searchResults.removeAll();

        if (selectedSecondLetter2) {
          var extender = {
            xmin:             0,
            ymin:             0,
            xmax:             0,
            ymax:             0,
            spatialReference: defaultExtent.spatialReference
          };

          for (var i = 0; i < geoJsonLayer.graphics.length; i++) {
            if ((selectedSecondLetter2() === 'All' && selectedFirstLetter2() == geoJsonLayer.graphics[i].attributes.To.trim()) || (selectedSecondLetter2() == geoJsonLayer.graphics[i].attributes.From_.trim() && selectedFirstLetter2() == geoJsonLayer.graphics[i].attributes.To.trim())) {
              var graphic = new Graphic(WebMercatorUtils.geographicToWebMercator(geoJsonLayer.graphics[i].geometry), geoJsonLayer.graphics[i].symbol, geoJsonLayer.graphics[i].attributes);

              gl.add(graphic);

              extender = adjustExtent(extender, graphic._extent);

              searchResults.push(geoJsonLayer.graphics[i].attributes);
            } // if ((selectedSecondLetter2() === 'All' && selectedFirstLetter2() == geoJsonLayer.graphics[i].attributes.From_.trim()) || (selectedSecondLetter2() == geoJsonLayer.graphics[i].attributes.To.trim() && selectedFirstLetter2() == geoJsonLayer.graphics[i].attributes.From_.trim()))
          } // for (var i = 0; i < geoJsonLayer.graphics.length; i++)

          self.map.setExtent(new esri.geometry.Extent(extender));
        } // if (selectedSecondLetter2)

        gl.redraw();
      }; // setSelectedLetter2 = function ()

      addGeoJsonLayer = function (url) {
        // Create the layer.
        geoJsonLayer = new GeoJsonLayer({
          id:           'lettersLayer',
          url:          url,
          infoTemplate: template,
          renderer:     rend
        });

        // On update end (Data gets populated).
        geoJsonLayer.on("update-end", function (e) {
          ko.utils.arrayForEach(geoJsonLayer.graphics, function (gra) {
            var doesExist = false;

            for (var i = 0; i < lettersFrom().length; i++) {
              if (lettersFrom()[i] == gra.attributes.From_.trim()) {
                doesExist = true;
                break;
              } // if (lettersFrom()[i] == gra.attributes.From_.trim())
            } // for (var i = 0; i < lettersFrom().length; i++)

            if (!doesExist) {
              lettersFrom.push(gra.attributes.From_.trim());
            } // if (!doesExist)

            doesExist = false;
            for (var i = 0; i < lettersTo2().length; i++) {
              if (lettersTo2()[i] == gra.attributes.To.trim()) {
                doesExist = true;
                break;
              } // if (lettersTo2()[i] == gra.attributes.To.trim())
            } // for (var i = 0; i < lettersTo2().length; i++)

            if (!doesExist) {
              lettersTo2.push(gra.attributes.To.trim());
            } // if (!doesExist)

            doesExist = false;
            for (var i = 0; i < letters().length; i++) {
              if (letters()[i].letterFrom == gra.attributes.From_.trim() && letters()[i].letterTo == gra.attributes.To.trim()) {
                doesExist = true;
                break;
              } // if (letters()[i].letterFrom == gra.attributes.From_.trim() && letters()[i].letterTo == gra.attributes.To.trim())
            } // for (var i = 0; i < letters().length; i++)

            if (!doesExist) {
              letters.push({
                'letterFrom': gra.attributes.From_.trim(),
                'letterTo':   gra.attributes.To.trim(),
                'FID':        gra.attributes.FID
              });
            } // if (!doesExist)
          });

          lettersFrom.sort(function (a, b) {
            if (a.toLowerCase() < b.toLowerCase()) {
              return -1;
            } else if (a.toLowerCase() > b.toLowerCase()) {
              return 1;
            } else {
              return 0;
            } // if (a.toLowerCase() < b.toLowerCase())
          });

          lettersTo2.sort(function (a, b) {
            if (a.toLowerCase() < b.toLowerCase()) {
              return -1;
            } else if (a.toLowerCase() > b.toLowerCase()) {
              return 1;
            } else {
              return 0;
            } // if (a.toLowerCase() < b.toLowerCase())
          });

          self.map.setExtent(e.target.extent.expand(0.5));
          self.map.centerAt(new Point(-80, 35));

          console.log('Map now has a center and extent.');

          setTimeout(function () {
            console.log('Setting defaultExtent.');
            defaultExtent = self.map.extent;
          }, 1300);
        });

        // Add to map
        self.map.addLayer(geoJsonLayer);

        console.log('Map now has layers.');
      }; // addGeoJsonLayer = function (url)

      adjustExtent = function (extender, extent) {
        if (extender.xmin < Math.abs(extent.xmin)) {
          console.log('New extent xmin =>', extent.xmin);

          extender['xmin'] = extent.xmin;
        } // if (extender.xmin < Math.abs(extent.xmin))

        if (extender.ymin < Math.abs(extent.ymin)) {
          console.log('New extent ymin =>', extent.ymin);

          extender['ymin'] = extent.ymin;
        } // if (extender.ymin < Math.abs(extent.ymin))

        if (extender.xmax < Math.abs(extent.xmax)) {
          console.log('New extent xmax =>', extent.xmax);

          extender['xmax'] = extent.xmax;
        } // if (extender.xmax < Math.abs(extent.xmax))

        if (extender.ymax < Math.abs(extent.ymax)) {
          console.log('New extent ymax =>', extent.ymax);

          extender['ymax'] = extent.ymax;
        } // if (extender.ymax < Math.abs(extent.ymax))

        return extender;
      }; // adjustExtent = function (extender, extent)
    };

    return searchVM;
  } // function (declare, domConstruct, config, view, GeoJsonLayer, GraphicsLayer, Graphic, WebMercatorUtils, InfoTemplate, SimpleRenderer)
);
