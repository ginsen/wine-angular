
angular.module('tag.services', ['ngResource']).
    factory('Tag', ['$resource', '$http', '$rootScope', function($resource, $http, $rootScope){
      return {
        api: $resource('api/tags/:tagId', {}, {
          update: {method:'PUT'}
        }),
        broadcastChange: function(){
          $rootScope.$broadcast('handleBroadcast');
        }
      };
    }]);

angular.module('response.services', ['ngResource']).
    factory('Response', ['$resource', '$http', '$rootScope', function($resource, $http, $rootScope){
      return {
        apiGet: $resource('api/responses/search/filter/:filtro/tags/:tags', {}, {
          get: { method: "GET", isArray: true}
        }),
        api: $resource('api/responses/:responseId', {}, {
          update: {method: "PUT"}
        }),
        apiDel: $resource('api/responses/id/:id', {}, {
          delete: { method: "DELETE", isArray: true}
        }),

        broadcastChange: function(){
          $rootScope.$broadcast('handleBroadcast');
        }
      };
    }]);

angular.module('report.services', ['ngResource']).
    factory('Report', ['$resource', '$http', '$rootScope', function($resource, $http, $rootScope){
      return {
        api: $resource('api/report/:reportId', {}, {
          update: {method: 'PUT'}
        }),
        broadcastChange: function() {
          $rootScope.$broadcast('handleBroadcast');
        }
      };
    }]);

angular.module('culture.services', ['ngResource']).
    factory('Culture', ['$resource', '$http', '$rootScope', function($resource, $http, $rootScope){
      return {
        api: $resource('api/cultures/:culture', {}, {
          update: {method: 'PUT'}
        }),
        broadcastChange: function() {
          $rootScope.$broadcast('handleBroadcast');
        }
      };
    }]);