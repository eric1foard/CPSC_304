app.controller('RegisterCtrl', function ($scope, $http) {
	// $http.get('/hello/you').success(function(data) {
	// 	$scope.name = data.name;
	// });

	$scope.showRegModal = function (){
		$('.confirm-modal').modal('show');
	};

	// $http.post('/register', { "foo": "bar" }).success(function(data) {
	// 	$scope.foo = data.foo;
	// });
});