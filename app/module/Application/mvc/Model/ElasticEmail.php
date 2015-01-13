<?php
namespace Application\Model;
 
class ElasticEmail
{
    /**
     * @var string
     * Api Key http://elasticemail.com
     */
     
    private static $_apiKey = 'e6cb5e1a-1b13-4cfd-acb1-933a2dda16ae';
    
    /**
     * @var string
     * Username http://elasticemail.com
     */
    private static $_username = 'jahangir@evatix.com';
    
    
    /**
     * Send Email By Elastic.com
     * semi colon separated list of email recipients
     * 
     * @param   $htmlBody       string
     * @param   $textBody       string
     * @param   $subject        string
     * @param   $formName       string
     * @param   $form           string
     * @param   $to             string
     */
    public static function send($htmlBody = '', $textBody = '', $subject = '', $fromName = '', $from = '', $to = '') 
    {
        $data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey).
    			'&from='.urlencode($from).
    			'&from_name='.urlencode($fromName).
    			'&to='.urlencode($to).
    			'&subject='.urlencode($subject);
    
    	if ($htmlBody)	
            $data .= '&body_html='.urlencode($htmlBody);
            
    	if ($textBody)	
            $data .= '&body_text='.urlencode($textBody);
    	
    	$apiUri = 'https://api.elasticemail.com/mailer/send';
        return self::_execute($apiUri, $data);
        
    } 
    
    /**
     * Creates a new contact and optionally adds it to the list provided.
     * Name of the list or lists (separated by semi-colon) the contact will be added to - 
     * if blank, it will just create the contact. If the list does not exist it will raise an error and fail
     * @param   $firstname  string
     * @param   $lastname   string
     * @param   $email      string
     * @param   $listname   string
     */
    public static function createContact($firstname = '', $lastname = '', $email = '', $listname = '') 
    {
        $data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey).
    			'&email='.urlencode($email).
                '&firstname='.urlencode($firstname).
                '&lastname='.urlencode($lastname).
    			'&listname='.urlencode($listname);
    
    	$apiUri = 'https://api.elasticemail.com/lists/create-contact';
        return self::_execute($apiUri, $data);
    }
    
    /**
     * Creates a new contact and optionally adds it to the list provided. 
     * Adds an existing contact to an existing list.  If the contact or list does not exist an error will result.
     * @param   $email      string
     * @param   $listname   string
     */
    public static function addContactsInList($email = '', $listname = '') 
    {
        $data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey).
    			'&email='.urlencode($email).
    			'&listname='.urlencode($listname);
    
    	$apiUri = 'https://api.elasticemail.com/lists/add-contact';
        return self::_execute($apiUri, $data);
       
    }
    
    /**
     * Returns email, firstname and lastname for an existing list.
     * @param   $listname   string
     */
    public static function getContactsFromList($listname = '') 
    {
       	$data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey).
    			'&listname='.urlencode($listname);
        $apiUri = 'https://api.elasticemail.com/lists/get-contacts';
        return self::_execute($apiUri, $data);
       
    } 
    
    /**
     * Returns all the lists for your account and how many users are in each list.
     */
    public static function getList() 
    {
       	$data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey);
        $apiUri = 'https://api.elasticemail.com/lists/get';
        return self::_execute($apiUri, $data);
       
    }
    
    /**
     * Creates a new list.
     * @param   $listname   string
     */
    public static function createList($listname = '') 
    {
        
    	$data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey).
    			'&listname='.urlencode($listname);
        $apiUri = 'https://api.elasticemail.com/lists/create-list';
        return self::_execute($apiUri, $data);
    }
    
    /**
     * Deletes an existing list.  Note that contacts from this list will not be deleted from the system.
     * @param   $listname   string
     */
    public static function deleteList($listname = '') 
    {
        $apiUri = 'https://api.elasticemail.com/lists/delete';
        $data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey).
    			'&listname='.urlencode($listname);
        return self::_execute($apiUri, $data);
    }
    
    /**
     * Removes an existing contact from an existing list.  If the contact or list does not exist an error will result.
     * Separate by semi-colon to remove multiple contacts
     * @param   $listname   string
     * @param   $email      string
     */
    
    public static function removeContactFromList($listname = '', $email = '') 
    {
        $apiUri = 'https://api.elasticemail.com/lists/remove-contact';
        $data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey).
                '&email='.urlencode($email). //separate by semi-colon to remove multiple contacts
    			'&listname='.urlencode($listname);
        return self::_execute($apiUri, $data);
    }
    
    /**
     * Deletes an existing contact and removes it from ALL lists.
     * separate by semi-colon to remove multiple
     * @param   $email  string
     */
    public static function deleteContactFromAllList($email = '') 
    {
        $apiUri = 'https://api.elasticemail.com/lists/delete-contact';
        $data = 'username='.urlencode(self::$_username).
    			'&api_key='.urlencode(self::$_apiKey).
                '&email='.urlencode($email); //separate by semi-colon to remove multiple contacts
    			
        return self::_execute($apiUri, $data);
    }
    
    /**
     * Call Api By Curl
     * @param  $apiUri  string
     * @param  $data    string
     */  
    private static function _execute($apiUri, $data) 
    {
        
        // Initialize cURL
    	$ch = curl_init();
    	
    	// Set cURL options
    	curl_setopt($ch, CURLOPT_URL, $apiUri);
    	curl_setopt($ch, CURLOPT_POST, 1);
        
        // Set parameter data to POST fields
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    
    	// Header data
    	$header = "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".strlen($data)."\r\n\r\n";
    
    	// Set header
    	curl_setopt($ch, CURLOPT_HEADER, $header);
    	
    	// Set to receive server response
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	
    	// Set cURL to verify SSL
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	
        $result = curl_exec($ch);
    	
        curl_close($ch);

	    // Return the response or NULL on failure
	    return ($result === false) ? NULL : $result;
    }
    
} 
