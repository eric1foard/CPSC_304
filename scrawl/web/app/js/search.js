app.controller('SearchCtrl', function ($scope, $http) {

	$scope.isCollapsed = true;
	var tagNames = [];
	var geos = [];
	var photos = [];

	$scope.search = {
		tags: [],
		distance: 1
	};

	var photos = [];

	$scope.showTags = function() {

		$scope.isCollapsed = !$scope.isCollapsed;
		console.log('from show tags' + $scope.isCollapsed);

		//only make call to get tags if opening modal
		if ($scope.isCollapsed === false)
		{
			$http.get('/ajax/tags').success(function(data){
				console.log('DATA' + data); 

				angular.forEach(data, function(tagName){
					tagNames.push(tagName);
					console.log('TAG  '+tagName);
				});
				$scope.tagNames = tagNames;
			});
		}

		else 
		{
			//clear array
			$scope.tagNames.length = 0;
			$scope.search.tags.length = 0;
			console.log('empty' + $scope.tagNames);
		}
	};

	$scope.submitSearch = function() {
		$scope.$parent.photos.length=0;
		$http.get('/ajax/search/' + $scope.search.tags +'&'+$scope.search.distance).success(function(data){
			console.log('FROM SUBMIT SEARCH'); 

			// angular.forEach(data, function(value, key){
			// 		photos.push({value, key});
			// 		console.log('VALUE  '+value);
			// 		console.log('KEY  '+key);
			// });
		angular.forEach(data, function(value, key){
				//photos.push({ value['path'], key });
				photos[key] = {
					path: value['path'],
					key: key
				};
				console.log('search photo key type:' + typeof(key));
				console.log('search photo key:' + key);
				console.log('search photo path:' + photos[key].path);
			});
			//$scope.photos = photos;

			angular.forEach(data, function(value, key){

				//lat and lon are strings from JSON object
				latitude = parseFloat(value['latitude']);
				longitude = parseFloat(value['longitude']);

				geos[key] = {
					lat: latitude, 
					lng: longitude,
					focus: false,
					icon: {}
				};

				console.log('GEOS key ' + key);
				console.log('GEOS lat ' + geos[key].lat);
				console.log('GEOS lng ' + geos[key].lng);
			});

			$scope.$parent.photos = photos;
			$scope.$parent.markers = geos;
			console.log('photos '+photos[0]);

		});
};
});