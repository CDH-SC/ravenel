define([
  'dojo/_base/declare',
  'app/config',
  'app/vm/vm.map'
  ], function (declare, config, vmMap) {
    var Main = declare('main', null, {
      constructor: function () {
        this.init();
      }, // constructor: function ()

      init: function () {
        var mp = new vmMap();
        mp.init();
      }, // init: function ()
    });

    return Main;
  } // function (declare, config, vmMap)
);
