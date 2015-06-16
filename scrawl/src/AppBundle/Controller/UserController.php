<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * User controller.
 *
 */
class UserController extends Controller
{

    public function homepageAction()
    {

        $entity = new User();
        $form = $this->createCreateForm($entity);

        return $this->render('AppBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
            ));
    }

    /**
     * Lists all User entities.
     *
     */
    public function indexAction()
    {

        $sql = 'SELECT * FROM scrawl_users';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //execute query
        $stmt->execute();

        //get all rows of results 
        $entities = $stmt->fetchAll();

        return $this->render('AppBundle:User:index.html.twig', array(
            'entities' => $entities,
            ));
    }
    /**
     * Creates a new User entity.
     *
     */
    public function createAction(Request $request)
    {
        if (($this->get('security.token_storage')->getToken()->getUser())
            != 'anon.')
        {
            $this->get('session')->getFlashBag()
            ->add('error','you cannot create a new account since you are already logged in!');
            return $this->redirect($this->generateUrl('homepage'));
        }

        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {

            //save users's location in appropriate table
            $this->persistGeolocationForUser($user);

            //persist user to DB
            $sql = 'INSERT INTO scrawl_users 
            value(:username, :role, :password, :salt, :email, :lat, :lng, :self_sum)';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            //set path of photo to be username_somephoto
            $stmt->bindValue('username', $form["username"]->getData());
            $stmt->bindValue('role', 'ROLE_USER');
            $stmt->bindValue('password', $this->encodePassword($user, $form["password"]->getData()));
            $stmt->bindValue('salt', $user->getSalt());
            $stmt->bindValue('email', $form["email"]->getData());
            $stmt->bindValue('lat', $form["latitude"]->getData());
            $stmt->bindValue('lng', $form["longitude"]->getData());
            $stmt->bindValue('self_sum', $form["selfSummary"]->getData());
            
            //execute query
            $stmt->execute();

            return $this->redirect($this->generateUrl('user_show', array('id' => $user->getId())));
        }

        return $this->render('AppBundle:User:new.html.twig', array(
            'entity' => $user,
            'form'   => $form->createView(),
            ));
    }

    //consume a user and the plain text password submitted in the 
    //user create form and return the bcrypt encoded password
    private function encodePassword($user, $password)
    {
        $encoderFactory = $this->get('security.encoder_factory');
        $encoder = $encoderFactory->getEncoder($user);
        $encoded = $encoder->encodePassword($password, $user->getSalt());

        return $encoded;
    }

        /**
    * Helper to save geolocation based on lat/long entry in User form
    **/
        private function persistGeolocationForUser($entity)
        {
            try{
                $location = $this->reverseGeocode($entity->getLatitude(), $entity->getLongitude());
            }
            catch(\Exception $e){
                $this->get('session')->getFlashBag()
                ->add('error','issue decoding user specified location. Please try again.');

                return $this->redirect($this->generateUrl('homepage'));
            }

        try{
            // Insert into Locations1 table
            $sql = 'INSERT INTO scrawl_locations1
                    value(:postalCode, :country, :region, :city)';
        
            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            $stmt->bindValue('postalCode', $location['postalCode']);
            $stmt->bindValue('country', $location['country']);
            $stmt->bindValue('region', $location["region"]);
            $stmt->bindValue('city', $location["city"]);

            //execute query
            $stmt->execute();

            // Insert into Locations2 tables
            $sql2 = 'INSERT INTO scrawl_locations2
                    value(:latitude, :longitude, :postalCode, :streetAddress)';
        
            $stmt2 = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql2);

            $stmt2->bindValue('latitude', $entity->getLatitude());
            $stmt2->bindValue('longitude', $entity->getLongitude());
            $stmt2->bindValue('postalCode', $location['postalCode']);
            $stmt2->bindValue('streetAddress', $location["streetAddress"]);

            //execute query
            $stmt2->execute();
        }
        catch (\Doctrine\DBAL\DBALException $e) { // Should check for more specific exception
            // duplicate entry. Entry we want already in the table. Everything is good.
        }

        $this->get('session')->getFlashBag()
        ->add('notice','user location successfully saved!');
    }

    private function reverseGeocode($lat, $lon){
        $url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . $lat . "," . $lon;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $json = json_decode(curl_exec($ch), true);
        
        
        if ($json['status'] == 'ZERO_RESULTS'){
            throw new Exception("Issues decoding specified user location", 1);
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

    // int would be the ith array it loops through
    // type would be the keyword of the location that it looks through
    private function geolocationJSONParser($sourcearray, $keyword)
    {
        if(stristr($sourcearray[$i]['types'][0], $keyword) != FALSE){
            $val = '';
            for($i = 0; $i < count($sourcearray); $i++){
                if(strpos($sourcearray[$i]['types'][0], $keyword)>0){
                    $val = $sourcearray[$i]['long_name'];
                }
            }
        }
        return $val;
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
            ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     */
    public function newAction()
    {
        $entity = new User();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:User:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
            ));
    }

    /**
     * Finds and displays a User entity.
     *
     */
    public function showAction($id)
    {
        $sql = 'SELECT * FROM scrawl_users WHERE username=?';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        //execute query
        $stmt->execute();

        //get only row of result
        $entity = $stmt->fetch();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        return $this->render('AppBundle:User:show.html.twig', array(
            'entity'      => $entity
            ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function editAction($id)
    {
        $loggedIn = $this->get('security.token_storage')->getToken()->getUser();
        
        if (!$this->canUpdateOrDelete($loggedIn, $id))
        {
            $this->get('session')->getFlashBag()
            ->add('error','you do not have permission to edit another user profile!!');
            return $this->redirect($this->generateUrl('homepage'));
        }

        $sql = 'SELECT * FROM scrawl_users WHERE username=?';
        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);
        //replace ? in query with $id
        $stmt->bindValue(1, $id);
        //execute query
        $stmt->execute();
        //get only row of result
        $result = $stmt->fetch();

        if (!$result) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $entity = new User();
        $editForm = $this->createEditForm($entity, $id);

        $editForm->get('username')->setData($result['username']);
        $editForm->get('email')->setData($result['email']);
        $editForm->get('latitude')->setData($result['latitude']);
        $editForm->get('longitude')->setData($result['longitude']);
        $editForm->get('selfSummary')->setData($result['selfSummary']);

        return $this->render('AppBundle:User:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
            ));
    }

    /**
    * Creates a form to edit a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $entity, $id)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $id)),
            'method' => 'PUT',
            ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        try
        {
            //get the hash of values passed to the form
            $postParamsHash = $request->request->get('appbundle_user', 'does not exist!');

            if (!$this->matchPasswords($postParamsHash['password']))
            {
                return $this->editAction($id);
            }

            //we already know 1st and 2nd passwords match, so just use 1st
            $pass = $postParamsHash['password']['first'];
            $loggedIn = $this->get('security.token_storage')->getToken()->getUser();
            $encoded = $this->encodePassword($loggedIn, $pass);

            //prepare rest of fields
            $lat = $postParamsHash['latitude'];
            $lng = $postParamsHash['longitude'];
            $uname = $postParamsHash['username'];
            $email = $postParamsHash['email'];
            $selfSum = $postParamsHash['selfSummary'];

            //WHERE statement must reference current uname (id passed to this fn)
            //in case user has updated username
            $sql = 'UPDATE scrawl_users 
                    SET username=:uname, email=:email, latitude=:lat, 
                    longitude=:lng, selfSummary=:selfSum, password_hash=:encoded 
                    WHERE username=:id';

            $stmt = $this->getDoctrine()->getManager()
            ->getConnection()->prepare($sql);

            //bind variables
            $stmt->bindValue('uname', $uname);
            $stmt->bindValue('email', $email);
            $stmt->bindValue('lat', $lat);
            $stmt->bindValue('lng', $lng);
            $stmt->bindValue('selfSum', $selfSum);
            $stmt->bindValue('encoded', $encoded);
            $stmt->bindValue('id', $id);

            //execute query
            $stmt->execute();

        }
        catch (\Doctrine\DBAL\DBALException $e) {

            $this->get('session')->getFlashBag()
            ->add('error','there was a problem updating the user! Please try again.');

            return $this->redirect($this->generateUrl('homepage'));
        }

        $this->get('session')->getFlashBag()
        ->add('notice','user '.$uname. ' successfully updated!');

        return $this->redirect($this->generateUrl('user_show', array('id' => $uname)));
    }

    //consumes array of first and second passwords submitted in form data
    //produces true if passwords match and false otherwise
    private function matchPasswords($passArray)
    {
        if (strcmp($passArray['first'], $passArray['second']) != 0)
        {
            $this->get('session')->getFlashBag()
            ->add('error','passwords did not match!');

            return false;
        }

        return true;
    }
    /**
     * Deletes a User entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $loggedIn = $this->get('security.token_storage')->getToken()->getUser();

        if (!$this->canUpdateOrDelete($loggedIn, $id))
        {
            $this->get('session')->getFlashBag()
            ->add('error','you do not have permission to delete another user profile!!');
            return $this->redirect($this->generateUrl('homepage'));
        }

        $sql = 'DELETE FROM scrawl_users WHERE username=?';

        $stmt = $this->getDoctrine()->getManager()
        ->getConnection()->prepare($sql);

        //replace ? in query with $id
        $stmt->bindValue(1, $id);

        //execute query
        $stmt->execute();

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */

    //$user, $id --> boolean
    //return true if user is Admin, or
    //user is accessing own page, false otherwise
    private function canUpdateOrDelete($user, $id)
    {
        if ((in_array('ROLE_ADMIN', $user->getRoles())) ||
            $user->getID() == $id)
        {
            return true;
        }
        return false;
    }
}
