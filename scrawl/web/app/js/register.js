var app = angular.module('Scrawl', []);

demoApp.controller('RegisterCtrl', function ($scope, $http) {
  $http.get('/hello/you').success(function(data) {
    $scope.name = data.name;
  });

  $http.post('/register', { "foo": "bar" }).success(function(data) {
    $scope.foo = data.foo;
  });
});