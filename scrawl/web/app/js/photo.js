app.controller('PhotoCtrl', [ '$scope', '$http',
 function ($scope, $http) {

	var photos = [];
	$http.get('/ajax/photos').success(function(data){
		console.log("!!!!!", data);

		angular.forEach(data, function(value, key){
			photos.push({value, key});
		});
		$scope.photos = photos;

		console.log("????", photos);
	});

}]);