<?php
/**
 * This file is part of the tiqr project.
 * 
 * The tiqr project aims to provide an open implementation for 
 * authentication using mobile devices. It was initiated by 
 * SURFnet and developed by Egeniq.
 *
 * More information: http://www.tiqr.org
 *
 * @author Ivo Jansch <ivo@egeniq.com>
 * 
 * @package tiqr
 *
 * @license New BSD License - See LICENSE file for details.
 *
 * @copyright (C) 2010-2011 SURFnet BV
 */

/** 
 * @internal includes of utility classes
 */
require_once("Tiqr/StateStorage.php");
require_once("Tiqr/DeviceStorage.php");
require_once("Tiqr/Random.php");

require_once("Tiqr/OATH/OCRAWrapper.php");
require_once("Tiqr/OcraService.php");


/** 
 * The main Tiqr Service class.
 * This is the class that an application interacts with to provide mobile 
 * authentication
 * @author ivo
 *
 */
class Tiqr_Service
{
    /**
     * @internal Various variables internal to the service class
     */
    protected $_options;
    
    protected $_protocolAuth = "tiqr";
    protected $_protocolEnroll = "tiqrenroll";
    
    protected $_identifier = "";
    protected $_ocraSuite = "";
    protected $_name = "";
    protected $_logoUrl = "";
    protected $_infoUrl = "";
    protected $_protocolVersion = 0;
    
    protected $_stateStorage = NULL;
    protected $_deviceStorage = NULL;

    protected $_ocraWrapper;
    protected $_ocraService;

    /**
     * Enrollment status codes
     */
    const ENROLLMENT_STATUS_IDLE = 1;        // Nothing happens
    const ENROLLMENT_STATUS_INITIALIZED = 2; // An enrollment session has begun
    const ENROLLMENT_STATUS_RETRIEVED = 3;   // The device has retrieved the metadata
    const ENROLLMENT_STATUS_PROCESSED = 4;   // The device has snet back a secret
    const ENROLLMENT_STATUS_FINALIZED = 5;   // The application has stored the secret
    const ENROLLMENT_STATUS_VALIDATED = 6;   // A first succesful authentication was performed

    /**
     * Default timeout values
     */
    const ENROLLMENT_EXPIRE = 300; // If enrollment isn't cmpleted within 120 seconds, we discard data
    const LOGIN_EXPIRE      = 3600; // Logins timeout after an hour
    const CHALLENGE_EXPIRE  = 180; // If login is not performed within 3 minutes, we discard the challenge

    /**
     * Authentication result codes
     */
    const AUTH_RESULT_INVALID_REQUEST   = 1;
    const AUTH_RESULT_AUTHENTICATED     = 2;
    const AUTH_RESULT_INVALID_RESPONSE  = 3;
    const AUTH_RESULT_INVALID_CHALLENGE = 4;
    const AUTH_RESULT_INVALID_USERID    = 5;
    
    /**
     * The default OCRA Suite to use for authentication
     */
    const DEFAULT_OCRA_SUITE = "OCRA-1:HOTP-SHA1-6:QH10-S";
      
    /**
     * Construct an instance of the Tiqr_Service. 
     * The server is configured using an array of options. All options have
     * reasonable defaults but it's recommended to at least specify a custom 
     * name and identifier and a randomly generated sessions secret.
     * If you use the Tiqr Service with your own apps, you must also specify
     * a custom auto.protocol and enroll.protocol specifier.
     * 
     * The options are:
     * - auth.protocol: The protocol specifier (e.g. tiqr://) that the 
     *                  server uses to communicate challenge urls to the phone. 
     *                  This must match the url handler specified in the 
     *                  iPhone app's build settings. You do not have to add the
     *                  '://', just the protocolname.
     *                  Default: tiqr
     * - enroll.protocol: The protocol specifier for enrollment urls.
     *                    Default: tiqrenroll
     * - ocra.suite: The OCRA suite to use. Defaults to OCRA-1:HOTP-SHA1-6:QN10-S.
     * - identifier: A short ASCII identifier for your service.
     *               Defaults to the SERVER_NAME of the server.
     * - name: A longer description of your service.
     *         Defaults to the SERVER_NAME of the server.              
     * - logoUrl: A full http url pointing to a logo for your service.
     * - infoUrl: An http url pointing to an info page of your service
     * - phpqrcode.path: The location of the phpqrcode library.
     *                   Defaults to ../phpqrcode
     * - apns.path: The location of the ApnsPHP library.
     *              Defaults to ../apns-php
     * - apns.certificate: The location of your Apple push notification
     *                     certificate.
     *                     Defaults to ../certificates/cert.pem
     * - apns.environment: Whether to use apple's "sandbox" or "production" 
     *                     apns environment
     * - c2dm.username: The username for your android c2dm account
     * - c2dm.password: The password for your android c2dm account
     * - c2dm.application: The application identifier for your android 
     *                     app, e.g. com.example.authenticator.
     * - statestorage: An array with the configuration of the storage for 
     *                 temporary data. It has the following sub keys:
     *                 - type: The type of state storage. (default: file) 
     *                 - parameters depending on the storage.
     *                 See the classes inside the StateStorage folder for 
     *                 supported types and their parameters.
     * - devicestorage: An array with the configruation of the storage for
     *                  device push notification tokens. Only necessary if 
     *                  you use the Tiqr Service as step-up authentication
     *                  for an already existing user. It has the following 
     *                  keys:
     *                  - type: The type of  storage. (default: dummy) 
     *                  - parameters depending on the storage.
     *                 See the classes inside the DeviceStorage folder for 
     *                 supported types and their parameters.
     *  
     * @param array $options
     * @param int $version The protocol version to use (defaults to the latest)
     */
    public function __construct($options=array(), $version = 2)
    {
        $this->_options = $options;
        
        if (isset($options["auth.protocol"])) {
            $this->_protocolAuth = $options["auth.protocol"];
        }
        
        if (isset($options["enroll.protocol"])) {
            $this->_protocolEnroll = $options["enroll.protocol"];
        }
        
        if (isset($options["ocra.suite"])) {
            $this->_ocraSuite = $options["ocra.suite"];
        } else {
            $this->_ocraSuite = self::DEFAULT_OCRA_SUITE;
        }
        
        if (isset($options["identifier"])) { 
            $this->_identifier = $options["identifier"];
        } else {
            $this->_identifier = $_SERVER["SERVER_NAME"];
        }
        
        if (isset($options["name"])) {
            $this->_name = $options["name"];
        } else {
            $this->_name = $_SERVER["SERVER_NAME"];
        }

        if (isset($options["logoUrl"])) { 
            $this->_logoUrl = $options["logoUrl"];
        }

        if (isset($options["infoUrl"])) {
            $this->_infoUrl = $options["infoUrl"];
        }
        
        if (isset($options["statestorage"])) {
            $type = $options["statestorage"]["type"];
            $storageOptions = $options["statestorage"];
        } else {
            $type = "file";
            $storageOptions = array();
        }        
        
        $this->_stateStorage = Tiqr_StateStorage::getStorage($type, $storageOptions);
        
        if (isset($options["devicestorage"])) {
            $type = $options["devicestorage"]["type"];
            $storageOptions = $options["devicestorage"];
        } else {
            $type = "dummy";
            $storageOptions = array();
        }
        
        $this->_deviceStorage = Tiqr_DeviceStorage::getStorage($type, $storageOptions);
        
        $this->_protocolVersion = $version;
        $this->_ocraWrapper = new Tiqr_OCRAWrapper($this->_ocraSuite);

        $type = 'tiqr';
        if (isset($options['usersecretstorage']) && $options['usersecretstorage']['type'] == 'oathservice') {
            $type = 'oathservice';
        }
        $ocraConfig = array();
        switch ($type) {
            case 'tiqr':
                $ocraConfig['ocra.suite'] = $this->_ocraSuite;
                $ocraConfig['protocolVersion'] = $version;
                break;
            case 'oathservice':
                $ocraConfig = $options['usersecretstorage'];
                break;
        }
        $this->_ocraService = Tiqr_OcraService::getOcraService($type, $ocraConfig);
    }
    
    /**
     * Get the identifier of the service.
     * @return String identifier
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }
    
    /**
     * Generate an authentication challenge QR image and send it directly to 
     * the browser.
     * 
     * In normal authentication mode, you would not specify a userId - however
     * in step up mode, where a user is already authenticated using a
     * different mechanism, pass the userId of the authenticated user to this 
     * function. 
     * @param String $sessionKey The sessionKey identifying this auth session (typically returned by startAuthenticationSession)
     */
    public function generateAuthQR($sessionKey)
    {
        // TODO
        $challengeUrl = $this->_getChallengeUrl($sessionKey);  
        
        QRcode::png($challengeUrl, false, 4, 5);
    }
    
    /**
     * Send a push notification to a user containing an authentication challenge
     * @param String $sessionKey          The session key identifying this authentication session
     * @param String $notificationType    Notification type, e.g. APNS, C2DM, (SMS?)
     * @param String $notificationAddress Notification address, e.g. device token, phone number etc.
     *
     * @return boolean True if the notification was sent succesfully, false if not.
     *
     * @todo Use exceptions in case of errors
     */
    public function sendAuthNotification($sessionKey, $notificationType, $notificationAddress)
    {
        try {
            $class = "Tiqr_Message_{$notificationType}";
            if (!class_exists($class)) {
                return false;
            }

            $message = new $class($this->_options);
            $message->setId($sessionKey);
            $message->setText("Please authenticate for " . $this->_name);
            $message->setAddress($notificationAddress);
            $message->setCustomProperty('challenge', $this->_getChallengeUrl($sessionKey));
            $message->send();

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /** 
     * Generate an authentication challenge URL.
     * This URL can be used to link directly to the authentication
     * application, for example to create a link in a mobile website on the
     * same device as where the application is installed
     * @param String $sessionKey The session key identifying this authentication session
     * @param String $userId The userId of a pre-authenticated user, if in  
     *                       step-up mode. NULL in other scenario's.
     * @param String $sessionId The application's session identifier. 
     *                          (defaults to php session)
     */
    public function generateAuthURL($sessionKey)
    {
        $challengeUrl = $this->_getChallengeUrl($sessionKey);  
        
        return $challengeUrl;
        
    }

    /**
     * Start an authentication session. This generates a challenge for this 
     * session and stores it in memory. The returned sessionKey should be used
     * throughout the authentication process.
     * @param String $userId The userId of a pre-authenticated user (optional)
     * @param String $sessionId The session id the application uses to 
     *                          identify its user sessions; (optional, 
     *                          defaults to the php session id).
     * @param String $spIdentifier If SP and IDP are 2 different things, pass the url/identifier of the SP the user is logging into.
     *                             For setups where IDP==SP, just leave this blank.
     */
    public function startAuthenticationSession($userId="", $sessionId="", $spIdentifier="")
    {
        if ($sessionId=="") {
            $sessionId = session_id();
        }

        if ($spIdentifier=="") {
            $spIdentifier = $this->_identifier;
        }

        $sessionKey = $this->_uniqueSessionKey("challenge");
    
        $challenge = $this->_ocraService->generateChallenge();
        
        $data = array("sessionId"=>$sessionId, "challenge"=>$challenge, "spIdentifier" => $spIdentifier);
        
        if ($userId!="") {
            $data["userId"] = $userId;
        }
        
        $this->_stateStorage->setValue("challenge".$sessionKey, $data, self::CHALLENGE_EXPIRE);
       
        return $sessionKey;
    }
    
    /**
     * Start an enrollment session. This can either be the enrollment of a new 
     * user or of an existing user, there is no difference from Tiqr's point
     * of view.
     * 
     * The call returns the temporary enrollmentKey that the phone needs to 
     * retrieve the metadata; you must therefor embed this key in the metadata
     * URL that you communicate to the phone.
     * 
     * @param String $userId The user's id
     * @param String $displayName The user's full name
     * @param String $sessionId The application's session identifier (defaults 
     *                           to php session)
     * @return String The enrollment key
     */
    public function startEnrollmentSession($userId, $displayName, $sessionId="")
    {
        if ($sessionId=="") {
            $sessionId = session_id();
        }
    
        $enrollmentKey = $this->_uniqueSessionKey("enroll");
        $this->_stateStorage->setValue("enroll".$enrollmentKey, array("userId"=>$userId, "displayName"=>$displayName, "sessionId"=>$sessionId), self::ENROLLMENT_EXPIRE);   

        $this->_setEnrollmentStatus($sessionId, self::ENROLLMENT_STATUS_INITIALIZED);

        return $enrollmentKey;
    }

    /**
     * Reset an existing enrollment session. (start over)
     * @param $sessionId The application's session identifier (defaults
     *                   to php session)
     */
    public function resetEnrollmentSession($sessionId="")
    {
        if ($sessionId=="") {
            $sessionId = session_id();
        }

        $this->_setEnrollmentStatus($sessionId, self::ENROLLMENT_STATUS_IDLE);
    }

    /**
     * Retrieve the enrollment status of an enrollment session.
     * 
     * @param String $sessionId the application's session identifier 
     *                          (defaults to php session)
     * @return int Enrollment status. Can be any one of these values:
     *             - Tiqr_Server::ENROLLMENT_STATUS_IDLE 
     *               There is no enrollment going on in this session
     *             - Tiqr_Server::ENROLLMENT_STATUS_INITIALIZED
     *               An enrollment session was started but the phone has not
     *               yet taken action. 
     *             - Tiqr_Server::ENROLLMENT_STATUS_RETRIEVED
     *               The device has retrieved the metadata
     *             - Tiqr_Server::ENROLLMENT_STATUS_PROCESSED
     *               The device has sent back a secret for the user
     *             - Tiqr_Server::ENROLLMENT_STATUS_FINALIZED
     *               The application has stored the secret
     *             - Tiqr_Server::ENROLLMENT_STATUS_VALIDATED
     *               A first successful authentication was performed 
     *               (todo: currently not used)
     */
    public function getEnrollmentStatus($sessionId="")
    { 
        if ($sessionId=="") {
            $sessionId = session_id(); 
        }
        $status = $this->_stateStorage->getValue("enrollstatus".$sessionId);
        if (is_null($status)) return self::ENROLLMENT_STATUS_IDLE;
        return $status;
    }
        
    /**
     * Generate an enrollment QR code and send it to the browser.
     * @param String $metadataUrl The URL you provide to the phone to retrieve
     *                            metadata. This URL must contain the enrollmentKey
     *                            provided by startEnrollmentSession (you can choose
     *                            the variable name as you are responsible yourself
     *                            for retrieving this from the request and passing it
     *                            on to the Tiqr server.
     */
    public function generateEnrollmentQR($metadataUrl) 
    { 
        $enrollmentString = $this->_protocolEnroll."://".$metadataUrl;
        
        QRcode::png($enrollmentString, false, 4, 5);
    }
    
    /**
     * Retrieve the metadata for an enrollment session.
     * 
     * When the phone calls the url that you have passed to 
     * generateEnrollmentQR, you must provide it with the output
     * of this function. (Don't forget to json_encode the output.)
     * 
     * Note, you can call this function only once, as the enrollment session
     * data will be destroyed as soon as it is retrieved.
     * 
     * @param String $enrollmentKey The enrollmentKey that the phone has
     *                              posted along with its request.
     * @param String $authenticationUrl The url you provide to the phone to
     *                                  post authentication responses
     * @param String $enrollmentUrl The url you provide to the phone to post
     *                              the generated user secret. You must include
     *                              a temporary enrollment secret in this URL
     *                              to make this process secure. This secret
     *                              can be generated with the 
     *                              getEnrollmentSecret call.
     * @return array An array of metadata that the phone needs to complete
     *               enrollment. You must encode it in JSON before you send
     *               it to the phone.
     */
    public function getEnrollmentMetadata($enrollmentKey, $authenticationUrl, $enrollmentUrl)
    {
        $data = $this->_stateStorage->getValue("enroll".$enrollmentKey);
        if (!is_array($data)) {
            return false;
        }

        $metadata = array("service"=>
                               array("displayName"       => $this->_name,
                                     "identifier"        => $this->_identifier,
                                     "logoUrl"           => $this->_logoUrl,
                                     "infoUrl"           => $this->_infoUrl,
                                     "authenticationUrl" => $authenticationUrl,
                                     "ocraSuite"         => $this->_ocraSuite,
                                     "enrollmentUrl"     => $enrollmentUrl
                               ),
                          "identity"=>
                               array("identifier" =>$data["userId"],
                                     "displayName"=>$data["displayName"]));

        $this->_stateStorage->unsetValue("enroll".$enrollmentKey);

        $this->_setEnrollmentStatus($data["sessionId"], self::ENROLLMENT_STATUS_RETRIEVED);
        return $metadata;
    }

    /** 
     * Get a temporary enrollment secret to be able to securely post a user 
     * secret.
     *
     * As part of the enrollment process the phone will send a user secret. 
     * This shared secret is used in the authentication process. To make sure
     * user secrets can not be posted by malicious hackers, a secret is 
     * required. This secret should be included in the enrollmentUrl that is 
     * passed to the getMetadata function.
     * @param String $enrollmentKey The enrollmentKey generated at the start
     *                              of the enrollment process.
     * @return String The enrollment secret
     */
    public function getEnrollmentSecret($enrollmentKey)
    {
         $data = $this->_stateStorage->getValue("enroll".$enrollmentKey);
         $secret = $this->_uniqueSessionKey("enrollmentsecret");
         $this->_stateStorage->setValue("enrollsecret".$secret, array("userId"=>$data["userId"], "sessionId"=>$data["sessionId"]), self::ENROLLMENT_EXPIRE); 
         return $secret;
    } 

    /**
     * Validate if an enrollmentSecret that was passed from the phone is valid.
     * @param $enrollmentSecret The secret that the phone posted; it must match
     *                          the secret that was generated using 
     *                          getEnrollmentSecret earlier in the process.
     * @return mixed The userid of the user that was being enrolled if the 
     *               secret is valid. This userid should be used to store the 
     *               user secret that the phone posted.
     *               If the enrollmentSecret is invalid, false is returned.
     */
    public function validateEnrollmentSecret($enrollmentSecret)
    {
         $data = $this->_stateStorage->getValue("enrollsecret".$enrollmentSecret);
         if (is_array($data)) { 
             // Secret is valid, application may accept the user secret. 
             $this->_setEnrollmentStatus($data["sessionId"], self::ENROLLMENT_STATUS_PROCESSED);
             return $data["userId"];
         }
         return false;
    }
    
    /**
     * Finalize the enrollment process.
     * If the user secret was posted by the phone, was validated using 
     * validateEnrollmentSecret AND if the secret was stored securely on the 
     * server, you should call finalizeEnrollment. This clears some enrollment
     * temporary pieces of data, and sets the status of the enrollment to 
     * finalized.
     * @param String The enrollment secret that was posted by the phone. This 
     *               is the same secret used in the call to 
     *               validateEnrollmentSecret.
     * @return boolean True if succesful 
     */
    public function finalizeEnrollment($enrollmentSecret) 
    {
         $data = $this->_stateStorage->getValue("enrollsecret".$enrollmentSecret);
         if (is_array($data)) {
             // Enrollment is finalized, destroy our session data.
             $this->_setEnrollmentStatus($data["sessionId"], self::ENROLLMENT_STATUS_FINALIZED);
             $this->_stateStorage->unsetValue("enrollsecret".$enrollmentSecret);
         }
         return true;
    }

    /**
     * Authenticate a user.
     * This method should be called when the phone posts a response to an
     * authentication challenge. The method will validate the response and
     * mark the user's session as authenticated. This essentially logs the
     * user in.
     * @param String $userId The userid of the user that should be 
     *                       authenticated
     * @param String $userSecret The user's secret. This should be the 
     *                           secret stored in a secure storage. 
     * @param String $sessionKey The phone will post a session key, this 
     *                           should be passed to this method in order
     *                           for the server to unlock the user's browser
     *                           session.
     * @param String $response   The response to the challenge that the phone
     *                           has posted.
     * @return String The result of the authentication. This is one of the
     *                AUTH_RESULT_* constants of the Tiqr_Server class.
     *                (do not make assumptions on the values of these 
     *                constants.)
     */
    public function authenticate($userId, $userSecret, $sessionKey, $response)
    {
        $state = $this->_stateStorage->getValue("challenge".$sessionKey);
        if (is_null($state)) {
            return self::AUTH_RESULT_INVALID_CHALLENGE;
        }
        
        $sessionId       = $state["sessionId"];
        $challenge       = $state["challenge"];

        $challengeUserId = NULL;
        if (isset($state["userId"])) {
          $challengeUserId = $state["userId"];
        }
        // Check if we're dealing with a second factor
        if ($challengeUserId!=NULL && ($userId != $challengeUserId)) {
            return self::AUTH_RESULT_INVALID_USERID; // only allowed to authenticate against the user that's authenticated in the first factor
        }

        if (stristr(get_class($this->_ocraService), 'oathservice')) {
            $equal = $this->_ocraService->verifyResponse($response, $userId, $challenge, $sessionKey);
        } else {
            $equal = $this->_ocraService->verifyResponse($response, $userSecret, $challenge, $sessionKey);
        }

        if ($equal) {
            $this->_stateStorage->setValue("authenticated_".$sessionId, $userId, self::LOGIN_EXPIRE);
            
            // Clean up the challenge.
            $this->_stateStorage->unsetValue("challenge".$sessionKey);
            
            return self::AUTH_RESULT_AUTHENTICATED;
        }
        return self::AUTH_RESULT_INVALID_RESPONSE;
    }
    
    /**
     * Log the user out.
     * @param String $sessionId The application's session identifier (defaults
     *                          to the php session).
     */
    public function logout($sessionId="")
    {
        if ($sessionId=="") {
            $sessionId = session_id(); 
        }
        
        return $this->_stateStorage->unsetValue("authenticated_".$sessionId);
    }
    
    /**
     * Exchange a notificationToken for a deviceToken.
     * 
     * During enrollment, the phone will post a notificationAddress that can be 
     * used to send notifications. To actually send the notification, 
     * this address should be converted to the real device address.
     *
     * @param String $notificationType    The notification type.
     * @param String $notificationAddress The address that was stored during enrollment.
     *
     * @return String The device address that can be used to send a notification.
     */
    public function translateNotificationAddress($notificationType, $notificationAddress)
    {
        if ($notificationType == 'APNS' || $notificationType == 'C2DM') {
            return $this->_deviceStorage->getDeviceToken($notificationAddress);
        } else {
            return $notificationAddress;
        }
    }
    
    /**
     * Retrieve the currently logged in user.
     * @param String $sessionId The application's session identifier (defaults
     *                          to the php session).
     * @return mixed An array with user data if a user was logged in or NULL if
     *               no user is logged in.
     */
    public function getAuthenticatedUser($sessionId="")
    {
        if ($sessionId=="") {
            $sessionId = session_id(); 
        }
        
        // Todo, we should return false, not null, to be more consistent
        return $this->_stateStorage->getValue("authenticated_".$sessionId);
    }
    
    /**
     * Generate a challenge URL
     * @param String $sessionKey The key that identifies the session.
     * @param String $challenge The authentication challenge
     * @param String $userId The userid to embed in the challenge url (only
     *                       if a user was pre-authenticated)
     *                       
     */
    protected function _getChallengeUrl($sessionKey)
    {                
        $state = $this->_stateStorage->getValue("challenge".$sessionKey);
        if (is_null($state)) {
            return false;
        }
        
        $userId   = NULL;
        $challenge = $state["challenge"];
        if (isset($state["userId"])) {
            $userId = $state["userId"];
        }
        $spIdentifier = $state["spIdentifier"];
        
        // Last bit is the spIdentifier
        return $this->_protocolAuth."://".(!is_null($userId)?urlencode($userId).'@':'').$this->getIdentifier()."/".$sessionKey."/".$challenge."/".urlencode($spIdentifier)."/".$this->_protocolVersion;
    }
    
    /**
     * Generate a unique random key to be used to store temporary session
     * data.
     * @param String $prefix A prefix for the key (different prefixes should
     *                       be used to store different pieces of data).
     *                       The function guarantees that the same key is nog
     *                       generated for the same prefix.
     * @return String The unique session key. (without the prefix!)
     */
    protected function _uniqueSessionKey($prefix)
    {      
        $value = 1;
        while ($value!=NULL) {
            $sessionKey = $this->_ocraWrapper->generateSessionKey();
            $value = $this->_stateStorage->getValue($prefix.$sessionKey);
        }
        return $sessionKey;
    }
    
    /**
     * Internal function to set the enrollment status of a session.
     * @param String $sessionId The sessionId to set the status for
     * @param int $status The new enrollment status (one of the 
     *                    self::ENROLLMENT_STATUS_* constants)
     */
    protected function _setEnrollmentStatus($sessionId, $status)
    {
       $this->_stateStorage->setValue("enrollstatus".$sessionId, $status, self::ENROLLMENT_EXPIRE);
    }
}
