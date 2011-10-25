<?php  
/** 
 * Sitemap Deluxe v1.0 Beta 
 * 
 * by Cristian Deluxe http://www.cristiandeluxe.com // http://blog.cristiandeluxe.com 
 *  
 * Licenced by a Creative Commons GNU LGPL license 
 * http://creativecommons.org/license/cc-lgpl 
 * 
 * @copyright     Copyright 2008-2009, Cristian Deluxe (http://www.cristiandeluxe.com) 
 * @link          http://bakery.cakephp.org/articles/view/sitemap-deluxe 
 */  
class SitemapsController extends AppController { 

    var $name = 'Sitemaps'; 
    var $helpers = array('Time', 'Xml', 'Javascript'); 
    var $components = array('RequestHandler'); 
    var $uses = array(); 
    var $array_dynamic = array(); 
    var $array_static = array(); 
    var $sitemap_url = '/sitemap.xml'; 
    var $yahoo_key = 'insert your yahoo api key here'; 
	var $allowedActions =  array('index', 'send_sitemap');

    /*  
     * Our sitemap  
     */ 
    function index(){
        $this->__get_data(); 
        $this->set('dynamics', $this->array_dynamic); 
        $this->set('statics', $this->array_static); 
        if ($this->RequestHandler->accepts('html')) { 
            $this->RequestHandler->respondAs('html'); 
        } elseif ($this->RequestHandler->accepts('xml')) { 
            $this->RequestHandler->respondAs('xml'); 
        }
    } 
     
    /*  
     * Action for send sitemaps to search engines 
     */ 
    function send_sitemap() { 
        // This action must be only for admins 
    } 
     
    /*  
     * This make a simple robot.txt file use it if you don't have your own 
     */ 
    function robot() { 
        $expire = 25920000; 
        header('Date: ' . date("D, j M Y G:i:s ", time()) . ' GMT'); 
        header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $expire) . ' GMT'); 
        header('Content-Type: text/plain'); 
        header('Cache-Control: max-age='.$expire.', s-maxage='.$expire.', must-revalidate, proxy-revalidate'); 
        header('Pragma: nocache'); 
        echo 'User-Agent: *'."\n".'Allow: /'."\n".'Sitemap: ' . FULL_BASE_URL . $this->sitemap_url; 
        exit(); 
    } 

    /*  
     * Here must be all our public controllers and actions 
     */ 
    function __get_data() { 
		
		$Aco = ClassRegistry::init('Aro');
		$guestAro = $Aco->find('first', array('conditions' => array('model' => 'UserRole', 'foreign_key' => __SYSTEM_GUESTS_USER_ROLE_ID)));
				
		$ArosAco = ClassRegistry::init('ArosAco');
        $guestAccess = $ArosAco->find('all', array(
			'conditions' => array(
				'ArosAco._read' => 1,
				'ArosAco.aro_id' => $guestAro['Aro']['id'],
				),
			));
				
		$Aco = ClassRegistry::init('Aco');
		$i = 0;
		foreach ($guestAccess as $access) {
			$guestPages = $Aco->getPath($access['ArosAco']['aco_id']);
			$actionPaths[$i]['plugin'] = Inflector::tableize($guestPages[1]['Aco']['alias']);
			$actionPaths[$i]['controller'] = Inflector::tableize($guestPages[2]['Aco']['alias']);
			$actionPaths[$i]['action'] = strtolower($guestPages[3]['Aco']['alias']);
			$actionPaths[$i]['model'] = Inflector::classify($guestPages[2]['Aco']['alias']);
			$i++;
		}
				
		foreach ($actionPaths as $path) {
	        ClassRegistry::init($path['model'])->recursive = false; 
	        $this->__add_dynamic_section($path['model'], ClassRegistry::init($path['model'])->find('all'), 
                	array( 
                    	'controllertitle' => $path['model'],
                        'fields' => array('id' => 'id', 'title' => ClassRegistry::init($path['model'])->displayField), 
                        'changefreq' => 'daily', 
                        'pr' => '1.0',  
                        'url' => array('plugin' => $path['plugin'], 'controller' => $path['controller'], 'action' => $path['action']) 
                        ));
		}
		
        /*$this->__add_static_section( 
                             'Contact Form',  
                             array('controller' => 'contact', 'action' => 'index'),  
                             array( 
                                    'changefreq' => 'yearly', 
                                    'pr' => '0.4' 
                                   ) 
                             );   */

    } 
     
	 
    /*  
     * Add a "static" section 
     */ 
    function __add_static_section($title = null, $url = null, $options = null) { 
        if(is_null($title) || empty($title) || is_null($url) || empty($url) ) { 
            return false; 
        } 
        $defaultoptions = array( 
                                'pr' => '0.5', // Valid values range from 0.0 to 1.0 
                                'changefreq' => 'monthly',  // Possible values: always, hourly, daily, weekly, monthly, yearly, never
                            ); 
        $options = array_merge($defaultoptions, $options);         
        $this->array_static[] = array( 
                                     'title' => $title, 
                                     'url' => $url, 
                                     'options' => $options 
                                     );         
    } 
     
     
    /*  
     * Add a section based on data from our database 
     */ 
    function __add_dynamic_section($model = null, $data = null, $options = null){ 
        if(is_null($model) || empty($model) || is_null($data) || empty($data) ) { 
            return false; 
        }
		
        $defaultoptions = array( 
                                'fields' => array( 
                                                  'id' => 'id',  
                                                  'date' => 'modified', 
                                                  'title' => 'title' 
                                                  ), 
                                'controllertitle' => 'not set', 
                                'pr' => '0.5', // Valid values range from 0.0 to 1.0 
                                'changefreq' => 'monthly',  // Possible values: always, hourly, daily, weekly, monthly, yearly, never
                                'url' => array( 
                                               'plugin' => false, 
                                               'controller' => false,  
                                               'action' => false,  
                                               'index' => 'index' 
                                               ) 
                                ); 
        $options = array_merge($defaultoptions, $options); 
		 		
        $options['fields'] = array_merge($defaultoptions['fields'], $options['fields']); 
        $options['url'] = array_merge($defaultoptions['url'], $options['url']); 
        if($options['fields']['date'] == false) { 
            $options['fields']['date'] = time(); 
        }
		
		$this->array_dynamic[] = array('model' => $model, 'options' => $options, 'data' => $data); 
		
		// if alias is there, then change the link to that instead
		$n = 0;
		foreach ($this->array_dynamic as $array_dyn) {
			if (!empty($array_dyn['data'][0]['Alias'])) {
				$i = 0;
				foreach ($array_dyn['data'] as $dat) {
					if (empty($dat['Alias']['name'])) {
		      		 	unset($this->array_dynamic[$n]['data'][$i]);
					}
					$i++;
				}
			}
			$n++;
		} 
		// dedupe 
    } 
	
	
     
    /*  
     * This make a GET petition to search engine url 
     */     
    function __ping_site($url = null, $params = null) { 
        if(is_null($url) || empty($url) || is_null($params) || empty($params) ) { 
            return false;     
        } 
        App::import('Core', 'HttpSocket'); 
        $HttpSocket = new HttpSocket(); 
        $html = $HttpSocket->get($url, $params);
        return $HttpSocket->response; 
    } 
     
    /*  
     * Show response for ajax based on a boolean result 
     */     
    function __ajaxresponse($result = false){ 
        if(!$result) { 
            return 'fail'; 
        } 
        return 'success'; 
    } 
     
    /*  
     * Function for ping Google 
     */     
    function ping_google() { 
        $url = 'http://www.google.com/webmasters/tools/ping'; 
        $params = 'sitemap=' . urlencode(FULL_BASE_URL . $this->sitemap_url); 
        echo $this->__ajaxresponse($this->__check_ok_google( $this->__ping_site($url, $params) ));         
        exit(); 
    } 
     
    /*  
     * Function for check Google's response 
     */     
    function __check_ok_google($response = null){ 
        if( is_null($response) || !is_array($response) || empty($response) ) { 
            return false; 
        } 
        if( 
           isset($response['status']['code']) && $response['status']['code'] == '200' && 
           isset($response['status']['reason-phrase']) && $response['status']['reason-phrase'] == 'OK' && 
           isset($response['body']) && !empty($response['body']) &&  
           strpos(strtolower($response['body']), "successfully added") != false) { 
            return true; 
        } 
        return false; 
    } 
     
    /*  
     * Function for ping Ask.com 
     */     
    function ping_ask() { // fail if we are in local environment 
        $url = 'http://submissions.ask.com/ping'; 
        $params = 'sitemap=' .  urlencode(FULL_BASE_URL . $this->sitemap_url); 
        echo $this->__ajaxresponse($this->__check_ok_ask( $this->__ping_site($url, $params) )); 
        exit(); 
    } 
     
    /*  
     * Function for check Ask's response 
     */     
    function __check_ok_ask($response = null){ 
        if( is_null($response) || !is_array($response) || empty($response) ) { 
            return false; 
        } 
        if( 
           isset($response['status']['code']) && $response['status']['code'] == '200' && 
           isset($response['status']['reason-phrase']) && $response['status']['reason-phrase'] == 'OK' && 
           isset($response['body']) && !empty($response['body']) &&  
           strpos(strtolower($response['body']), "has been successfully received and added") != false) { 
            return true; 
        } 
        return false; 
    } 
     
    /*  
     * Function for ping Yahoo 
     */     
    function ping_yahoo() {
        $url = 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification'; 
        $params = 'appid='.$this->yahoo_key.'&url=' . urlencode(FULL_BASE_URL . $this->sitemap_url); 
        echo $this->__ajaxresponse($this->__check_ok_yahoo( $this->__ping_site($url, $params) )); 
        exit(); 
    } 
     
    /*  
     * Function for check Yahoo's response 
     */     
    function __check_ok_yahoo($response = null){ 
        if( is_null($response) || !is_array($response) || empty($response) ) { 
            return false; 
        } 
        if( 
           isset($response['status']['code']) && $response['status']['code'] == '200' && 
           isset($response['status']['reason-phrase']) && $response['status']['reason-phrase'] == 'OK' && 
           isset($response['body']) && !empty($response['body']) &&  
           strpos(strtolower($response['body']), "successfully submitted") != false) { 
            return true; 
        } 
        return false; 
    } 
     
    /*  
     * Function for ping Bing 
     */     
    function ping_bing() {
        $url = 'http://www.bing.com/webmaster/ping.aspx'; 
        $params = '&siteMap=' . urlencode(FULL_BASE_URL . $this->sitemap_url); 
        echo $this->__ajaxresponse($this->__check_ok_bing( $this->__ping_site($url, $params) )); 
        exit(); 
    } 
     
    /*  
     * Function for check Bing's response 
     */     
    function __check_ok_bing($response = null){ 
        if( is_null($response) || !is_array($response) || empty($response) ) { 
            return false; 
        } 
        if( 
           isset($response['status']['code']) && $response['status']['code'] == '200' && 
           isset($response['status']['reason-phrase']) && $response['status']['reason-phrase'] == 'OK' && 
           isset($response['body']) && !empty($response['body']) &&  
           strpos(strtolower($response['body']), "thanks for submitting your sitemap") != false) { 
            return true; 
        } 
        return false; 
    } 
}  
?> 