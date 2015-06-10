//angular controller for angular-leaflet-directive

app.controller('MapCtrl', ['$scope',
	function($scope) {
		angular.extend($scope, {
			center: {
				lat: 24.0391667,
				lng: 121.525,
				zoom: 6
			},
			markers: {
				taipei: {
					lat: 25.0391667,
					lng: 121.525,
				},
				yangmei: {
					lat: 24.9166667,
					lng: 121.1333333
				},
				hsinchu: {
					lat: 24.8047222,
					lng: 120.9713889
				},
				miaoli: {
					lat: 24.5588889,
					lng: 120.8219444
				},
				tainan: {
					lat: 22.9933333,
					lng: 120.2036111
				},
				puzi: {
					lat: 23.4611,
					lng: 120.242
				},
				kaohsiung: {
					lat: 22.6252777778,
					lng: 120.3088888889
				},
				taitun: {
					lat: 22.75,
					lng: 121.15
				}
			}
		});

	}]);