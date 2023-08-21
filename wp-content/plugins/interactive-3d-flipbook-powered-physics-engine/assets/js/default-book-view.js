function init(container) {
  var instance;
  if(window.jQuery) {
    var $ = window.jQuery, jDoc = $(container[0].ownerDocument), view = container.find('.view'), canvas = view.find('canvas');

    function toggleMenu(e) {
      e.preventDefault();
      e.stopPropagation();
      var el = $(e.target);
      while(!el.hasClass('menu-toggle')) {
        el = $(el[0].parentNode);
      }
      var menu = $(el[0].parentNode).find('.v-menu');
      if(menu.hasClass('hidden')) {
        menu.removeClass('hidden');
        el.addClass('active');
      }
      else {
        menu.addClass('hidden');
        el.removeClass('active');
      }
    }
    function hideDropMenu() {
      container.find('.v-menu').addClass('hidden');
      container.find('.menu-toggle').removeClass('active');
    }

    function pickFloatWnd(e) {
      if(instance.pos) {
        dropFloatWnd();
      }
      else {
        instance.floatWnd = $(e.target);
        while(!instance.floatWnd.hasClass('float-wnd')) {
          instance.floatWnd = $(instance.floatWnd[0].parentNode);
        }
        instance.pos = {
          x: e.pageX,
          y: e.pageY
        };
      }
    }
    function moveFloatWnd(e) {
      if(instance.pos) {
        e.preventDefault();
        var dv = {
          x: e.pageX-instance.pos.x,
          y: e.pageY-instance.pos.y
        }, old = {
          x: parseInt(instance.floatWnd.css('left')),
          y: parseInt(instance.floatWnd.css('top'))
        };
        instance.floatWnd.css('left', old.x+dv.x+'px').css('top', old.y+dv.y+'px');
        instance.pos = {
          x: e.pageX,
          y: e.pageY
        };
      }
    }
    function dropFloatWnd() {
      delete instance.pos;
      delete instance.floatWnd;
    }

    instance = {
      dispose: function() {
        container.find('.menu-toggle').off('click', toggleMenu);
        jDoc.off('click', hideDropMenu);

        jDoc.off('mousemove', moveFloatWnd);
        jDoc.off('mouseup', dropFloatWnd);
        container.find('.float-wnd .head').off('mousedown', pickFloatWnd);
      },
      appLoaded: function(scene) {
        var hoverHeight = scene.view.getStyleData()['hover-height'] || 0;
        if(hoverHeight) {
          scene.ctrl.bookWatcher.setPadding((container.find('.tbox').css('bottom')==='10px'? -1: 1)*(hoverHeight+20));
        }
      },
      linkLoaded: function(link) {
      }
    };

    container.find('.menu-toggle').on('click', toggleMenu);
    jDoc.on('click', hideDropMenu);

    jDoc.on('mousemove', moveFloatWnd);
    jDoc.on('mouseup', dropFloatWnd);
    container.find('.float-wnd .head').on('mousedown', pickFloatWnd);
  }
  else {
    instance = {
      dispose: function() {
      }
    };
    console.error('jQuery is not found');
  }
  return instance;
} init
