define([
  'dojo/_base/declare',
  'dojo/dom-construct',
  'dojo/on',
  'dojo/_base/lang',
  'app/config',
  'dojo/text!app/view/view.map.html',
  'esri/map',
  'app/vm/vm.search'
  ], function (declare, domConstruct, on, lang, config, view, Map, Search) {
    var mapVM = function () {
      var self      = this;
      self.map      = null;
      self.searchVM = null;

      self.init = function () {
        domConstruct.place(view, dojo.byId("map-div"));

        self.map = new Map("map", {
          basemap: "topo"
        });

        self.map.on('load', lang.hitch(this, function () {
          self.searchVM = new Search(self.map);
          self.searchVM.init();
        }));
      }; // self.init = function ()

      this.View = view;
    }; // var mapVM = function ()

    return mapVM;
  } // function (declare, domConstruct, on, lang, config, view, Map, Search)
);
