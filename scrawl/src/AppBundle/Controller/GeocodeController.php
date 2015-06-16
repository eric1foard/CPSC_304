<?php
namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
/**
 * Geocode controller.
 *
 */
class GeocodeController extends Controller
{
    /**
    * Save location to locations tables based on supplied latitude and longitude
    **/
    public function persistLocation($latitude, $longitude)
    {
    	// Call helper function that accesses Google Geocoder API
        try{
            $location = $this->reverseGeocode($latitude, $longitude);
        }
        catch(\Exception $e){
            $this->get('session')->getFlashBag()
            ->add('error','issue decoding specified location. Please try again.');
            return $this->redirect($this->generateUrl('homepage'));
        }

        // Persist to database the location details returned by the Google Geocoder API
        try{
            // Insert into Locations1 table
			$this->saveToLocations1Table($location);
            // Insert into Locations2 tables
			$this->saveToLocations2Table($location);
        }
        catch (\Doctrine\DBAL\DBALException $e) { // Should check for more specific exception
            // duplicate entry. Entry we want already in the table. Everything is good.
        }

        $this->get('session')->getFlashBag()
        ->add('notice','location successfully saved!');
        return new Response($this->redirect($this->generateUrl('homepage')));
    }


	/**
	 * Save postal code, country, region, and city to Locations1 table
	 *
	 */
    private function saveToLocations1Table($location){
        $sql = 'INSERT INTO scrawl_locations1 value(:postalCode, :country, :region, :city)';
// THIS IS WHERE THE CONTROL FLOW DIES
// UNABLE TO GET ENTITY MANAGER 
        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);
// ABOVE NEEDS HELP!

        $stmt->bindValue('postalCode', $location['postalCode']);
        $stmt->bindValue('country', $location['country']);
        $stmt->bindValue('region', $location["region"]);
        $stmt->bindValue('city', $location["city"]);
        //execute query
        $stmt->execute();
    }

	/**
	 * Save latitude, longitude, postal code, and street address to Locations2 table
	 *
	 */
    private function saveToLocations2Table($location, $latitude, $longitude){
        $sql = 'INSERT INTO scrawl_locations2 value(:latitude, :longitude, :postalCode, :streetAddress)';
        
        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);
        $stmt->bindValue('latitude', $latitude);
        $stmt->bindValue('longitude', $longitude);
        $stmt->bindValue('postalCode', $location['postalCode']);
        $stmt->bindValue('streetAddress', $location["streetAddress"]);
        //execute query
        $stmt->execute();
    }

	/**
	 * Call Google Geocoder API with given latitude and longitude
	 * Return location details:
	 * postal code, street address, city, region, country
	 */
    private function reverseGeocode($latitude, $longitude){
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $latitude . "," . $longitude;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $json = json_decode(curl_exec($ch), true);
        if ($json['status'] == 'ZERO_RESULTS'){
            throw new Exception("Issues decoding specified photo location", 1);
        }

        $addressComponents = $json['results'][0]['address_components'];
        
        $location = array(
            'postalCode' => $this->geolocationJSONParser($addressComponents, 'postal_code'),
            'streetAddress' => $this->geolocationJSONParser($addressComponents, 'street_number') . " " . $this->geolocationJSONParser($addressComponents, 'route'),
            'city' => $this->geolocationJSONParser($addressComponents, 'locality'),
            'region' => $this->geolocationJSONParser($addressComponents, 'administrative_area_level_1'),
            'country' => $this->geolocationJSONParser($addressComponents, 'country')
            ); 
        
        return $location;
    }

	/**
	 * Parse JSON response to find value associated with specified keyword
	 *
     * int would be the ith array it loops through
     * type would be the keyword of the location that it looks through
     */
    private function geolocationJSONParser($sourcearray, $keyword)
    {
        $val = '';
        
            for($i = 0; $i < count($sourcearray); $i++){
                foreach ($sourcearray[$i]['types'] as $type) {
                    if(stristr($type, $keyword)){
                        $val = $sourcearray[$i]['long_name'];
                    }
                }
            }

        return $val;
    }
}