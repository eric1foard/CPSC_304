app.controller('PhotoCtrl', [ '$scope', '$http',
 function ($scope, $http) {

	var photos = [];
	$http.get('/ajax/photos').success(function(data){
		angular.forEach(data, function(value, key){
			photos.push(value);
		});
		$scope.photos = photos;

		console.log(photos);
	});

}]);