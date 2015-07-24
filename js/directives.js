
angular.module('reviews.directives', []).directive('activeLink', ['$location', function(location) {
  return {
    restrict: 'A',
    link: function(scope, element, attrs, controller) {
      var clazz = attrs.activeLink;
      var elementPath;

      //Observe the href value because it is interpolated
      attrs.$observe('href', function(value) {
        elementPath = value.substring(1);
      });

      scope.location = location;
      scope.$watch('location.path()', function(newPath) {
        if (elementPath === newPath) {
          element.addClass(clazz);
        } else {
          element.removeClass(clazz);
        }
      });
    }
  };
}]);

angular.module('reviews.directives', []).directive('ngConfirmClick', [function() {
  return {
    link: function (scope, element, attr) {
      var msg = attr.ngConfirmClick || "Are you sure?";
      var clickAction = attr.confirmedClick;
      element.bind('click',function (event) {
        if ( window.confirm(msg) ) {
          scope.$eval(clickAction);
        }
      });
    }
  };
}]);
