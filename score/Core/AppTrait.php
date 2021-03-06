<?php
namespace Swoolefy\Core;

trait AppTrait {
	/**
	 * $previousUrl,记录url
	 * @var array
	 */
	public static $previousUrl = [];

	/**
	 * $selfModel 控制器对应的自身model
	 * @var array
	 */
	public static $selfModel = [];
	/**
	 * _beforeAction 
	 * @return   mixed
	 */
	public function _beforeAction() {

	}

	/**
	 * _afterAction
	 * @return   mixed
	 */
	public function _afterAction() {

	}
	/**
	 * isGet
	 * @return boolean
	 */
	public function isGet() {
		return ($this->request->server['REQUEST_METHOD'] == 'GET') ? true :false;
	}

	/**
	 * isPost
	 * @return boolean
	 */
	public function isPost() {
		return ($this->request->server['REQUEST_METHOD'] == 'POST') ? true :false;
	}

	/**
	 * isPut
	 * @return boolean
	 */
	public function isPut() {
		return ($this->request->server['REQUEST_METHOD'] == 'PUT') ? true :false;
	}

	/**
	 * isDelete
	 * @return boolean
	 */
	public function isDelete() {
		return ($this->request->server['REQUEST_METHOD'] == 'DELETE') ? true :false;
	}

	/**
	 * isAjax
	 * @return boolean
	 */
	public function isAjax() {
		return (isset($this->request->header['x-requested-with']) && strtolower($this->request->header['x-requested-with']) == 'xmlhttprequest') ? true : false;
	}

	/**
	 * isSsl
	 * @return   boolean
	 */
	public function isSsl() {
	    if(isset($this->request->server['HTTPS']) && ('1' == $this->request->server['HTTPS'] || 'on' == strtolower($this->request->server['HTTPS']))){
	        return true;
	    }elseif(isset($this->request->server['SERVER_PORT']) && ('443' == $this->request->server['SERVER_PORT'] )) {
	        return true;
	    }
	    return false;
	}

	/**
	 * isMobile 
	 * @return   boolean
	 */
    public function isMobile() {
        if (isset($this->request->server['HTTP_VIA']) && stristr($this->request->server['HTTP_VIA'], "wap")) {
            return true;
        } elseif (isset($this->request->server['HTTP_ACCEPT']) && strpos(strtoupper($this->request->server['HTTP_ACCEPT']), "VND.WAP.WML")) {
            return true;
        } elseif (isset($this->request->server['HTTP_X_WAP_PROFILE']) || isset($this->request->server['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($this->request->server['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $this->request->server['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

	/**
	 * getMethod 
	 * @return   string
	 */
	public function getMethod() {
		return $this->request->server['REQUEST_METHOD'];
	}

	/**
	 * getRequestUri
	 * @return string
	 */
	public function getRequestUri() {
		return $this->request->server['PATH_INFO'];
	}

	/**
	 * getRoute
	 * @return  string
	 */
	public function getRoute() {
		return $this->request->server['ROUTE'];
	}

	/**
	 * getQueryString
	 * @return   string
	 */
	public function getQueryString() {
		return $this->request->server['QUERY_STRING'];
	}

	/**
	 * getProtocol
	 * @return   string
	 */
	public function getProtocol() {
		return $this->request->server['SERVER_PROTOCOL'];
	}

	/**
	 * getHomeUrl 获取当前请求的url
	 * @param    $ssl
	 * @return   string
	 */
	public function getHomeUrl($ssl=false) {
		$protocol_version = $this->getProtocol();
		list($protocol, $version) = explode('/', $protocol_version);
		
		$protocol = strtolower($protocol).'://';

		if($ssl) {
			$protocol = 'https://';
		}
		return $protocol.$this->getHostName().$this->getRequestUri().'?'.$this->getQueryString();
	}

	/**
	 * rememberUrl
	 * @param  string  $name
	 * @param  string  $url
	 * @param  boolean $ssl
	 * @return   void   
	 */
	public function rememberUrl($name=null,$url=null,$ssl=false) {
		if($url && $name) {
			static::$previousUrl[$name] = $url;
		}else {
			// 获取当前的url保存
			static::$previousUrl['home_url'] = $this->getHomeUrl($ssl);
		}
	}

	/**
	 * getPreviousUrl
	 * @param  string  $name
	 * @return   mixed
	 */
	public function getPreviousUrl($name=null) {
		if($name) {
			if(isset(static::$previousUrl[$name])) {
				return static::$previousUrl[$name];
			}
			return null;
		}else {
			if(isset(static::$previousUrl['home_url'])) {
				return static::$previousUrl['home_url'];
			}

			return null;
		}
	} 

	/**
	 * getRoute
	 * @return array
	 */
	public function getRouteParams() {
		return $this->request->server['ROUTE_PARAMS'];
	}

	/**
	 * getModule 
	 * @return string|null
	 */
	public function getModule() {
		list($count,$routeParams) = $this->getRouteParams();
		if($count == 3) {
			return $routeParams[0];
		}else {
			return null;
		}
	}

	/**
	 * getController
	 * @return string
	 */
	public function getController() {
		list($count,$routeParams) = $this->getRouteParams();
		if($count == 3) {
			return $routeParams[1];
		}else {
			return $routeParams[0];
		}
	}

	/**
	 * getAction
	 * @return string
	 */
	public function getAction() {
		$routeParams = $this->getRouteParams();
		return array_pop($routeParams);
	}

	/**
	 * getModel 默认获取当前module下的控制器对应的module
	 * @param  string  $model
	 * @return object
	 */
	public function getModel($model='') {
		$module = $this->getModule();
		$controller = $this->getController();
		// 如果存在module
		if(!empty($module)) {
			// model的类文件对应控制器
			if(!empty($model)) {
				$modelClass = $this->config['app_namespace'].'\\'.'Module'.'\\'.$module.'\\'.'Model'.'\\'.$model;
			}else {
				$modelClass = $this->config['app_namespace'].'\\'.'Module'.'\\'.$module.'\\'.'Model'.'\\'.$controller;
			}
		}else {
			// model的类文件对应控制器
			if(!empty($model)) {
				$modelClass = $this->config['app_namespace'].'\\'.'Model'.'\\'.$model;
				
			}else {
				$modelClass = $this->config['app_namespace'].'\\'.'Model'.'\\'.$controller;
			}
		}
		// 从内存数组中返回
		if(isset(self::$selfModel[$modelClass])) {
			return self::$selfModel[$modelClass];
		}else {
			try{
				$modelInstance = new $modelClass;
				return self::$selfModel[$modelClass] = $modelInstance;
			}catch(\Exception $e) {
				throw new \Exception($e->getMessage(), 1);
			}
		}

	}

	/**
	 * getQuery
	 * @return string
	 */
	public function getQuery() {
		return $this->request->get;
	}

	/**
	 * getView
	 * @return   object
	 */
	public function getView() {
		return Application::$app->view;
	}


	/**
	 * assign
	 * @param   string  $name
	 * @param   string|array  $value
	 * @return  void   
	 */
	public function assign($name,$value) {
		Application::$app->view->assign($name,$value);
	}

	/**
	 * display
	 * @param    string  $template_file
	 * @return   void             
	 */
	public function display($template_file=null) {
		Application::$app->view->display($template_file);
	}

	/**
	 * fetch
	 * @param    string  $template_file
	 * @return   void              
	 */
	public function fetch($template_file=null) {
		Application::$app->view->display($template_file);
	}

	/**
	 * returnJson
	 * @param    array  $data    
	 * @param    string  $formater
	 * @return   void         
	 */
	public function returnJson($data,$formater = 'json') {
		Application::$app->view->returnJson($data,$formater);
	}

	/**
	 * sendfile
	 * @param    string  $filename 
	 * @param    int     $offset   
	 * @param    string  $length   
	 * @return   void          
	 */
	public function sendfile($filename, $offset = 0, $length = 0) {
		$this->response->sendfile($filename, $offset = 0, $length = 0);
	}

	/**
	 * parseUri 解析URI
	 * @param    string  $url
	 * @return   array
	 */
	public function parseUri($url)
    {
        $res = parse_url($url);
        $return['protocol'] = $res['scheme'];
        $return['host'] = $res['host'];
        $return['port'] = $res['port'];
        $return['user'] = $res['user'];
        $return['pass'] = $res['pass'];
        $return['path'] = $res['path'];
        $return['id'] = $res['fragment'];
        parse_str($res['query'], $return['params']);
        return $return;
    }

	/**
	 * redirect 重定向,使用这个函数后,要return,停止程序执行
	 * @param    string  $url
	 * @param    array   $params eg:['name'=>'ming','age'=>18]
	 * @param    int     $code default 301
	 * @return   void
	 */
	public function redirect($url,array $params=[], $code=301) {
		$query_string = '';
		trim($url);
		if(strpos($url, 'http') === false || strpos($url, 'https') === false) {
			if(strpos($url, '/') != 0) {
				$url = '/'.$url;
			}
		}
		
		if($params) {
			if(strpos($url,'?') > 0) {
				foreach($params as $name=>$value) {
					$query_string .= '&'.$name.'='.$value;
				}
			}else {
				$query_string = '?';
				foreach($params as $name=>$value) {
					$query_string .= $name.'='.$value.'&';
				}

				$query_string = rtrim($query_string,'&');
			}
		}
		$this->status($code);
		$this->response->header('Location', $url.$query_string);
		return;
	}

	/**
	 * dump，调试函数
	 * @param    string|array  $var
	 * @param    boolean       $echo
	 * @param    $label
	 * @param    $strict
	 * @return   string            
	 */
	public function dump($var, $echo=true, $label=null, $strict=true) {
	    $label = ($label === null) ? '' : rtrim($label) . ' ';
	    if (!$strict) {
	        if (ini_get('html_errors')) {
	            $output = print_r($var, true);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        } else {
	            $output = $label . print_r($var, true);
	        }
	    } else {
	        ob_start();
	        var_dump($var);
	        // 获取终端输出
	        $output = ob_get_clean();
	        if (!extension_loaded('xdebug')) {
	            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        }
	    }
	    if($echo) {
	    	// 调试环境这个函数使用
	        if(SW_DEBUG) @$this->response->write($output);
	        return null;
	    }else
	        return $output;
	}

	/**
	 * cors 
	 * @return  
	 */
	public function setCors() {
		if(isset($this->config['cors']) && is_array($this->config['cors'])) {
			$cors = $this->config['cors'];
			foreach($cors as $k=>$value) {
				if(is_array($value)) {
					$this->response->header($k,implode(',',$value));
				}else {
					$this->response->header($k,$value);
				}
			}
		}
	}

	/**
	 * asyncHttpClient 简单的模拟http异步并发请求
	 * @param    array   $urls 
	 * @param    int     $timeout 单位ms
	 * @return   
	 */
	public function asyncHttpClient($urls=[],$timeout=500) {
		if(!empty($urls)) {
			$conn = [];
			$mh = curl_multi_init();
			foreach($urls as $i => $url) {
				$conn[$i] = curl_init($url);
					curl_setopt($conn[$i], CURLOPT_CUSTOMREQUEST, "GET");
				  	curl_setopt($conn[$i], CURLOPT_HEADER ,0);
				  	curl_setopt($conn[$i], CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($conn[$i], CURLOPT_SSL_VERIFYHOST, FALSE);
					curl_setopt($conn[$i], CURLOPT_NOSIGNAL, 1);
					curl_setopt($conn[$i], CURLOPT_TIMEOUT_MS,$timeout);   
				  	curl_setopt($conn[$i],CURLOPT_RETURNTRANSFER,true);
				  	curl_multi_add_handle($mh,$conn[$i]);
			}

			do {   
  				curl_multi_exec($mh,$active);   
			}while ($active);

			foreach ($urls as $i => $url) {   
  				curl_multi_remove_handle($mh,$conn[$i]);   
  				curl_close($conn[$i]);   
			}
			curl_multi_close($mh);
			return true;
		}
		return false;
	}

	/**
	 * sendHttpStatus,参考tp的
	 * @param    int  $code
	 * @return   void     
	 */
	public function status($code) {
		$http_status = array(
			// Informational 1xx
			100,
			101,

			// Success 2xx
			200,
			201,
			202,
			203,
			204,
			205,
			206,

			// Redirection 3xx
			300,
			301,
			302,  // 1.1
			303,
			304,
			305,
			// 306 is deprecated but reserved
			307,

			// Client Error 4xx
			400,
			401,
			402,
			403,
			404,
			405,
			406,
			407,
			408,
			409,
			410,
			411,
			412,
			413,
			414,
			415,
			416,
			417,

			// Server Error 5xx
			500,
			501,
			502,
			503,
			504,
			505,
			509
		);
		if(in_array($code, $http_status)) {
			$this->response->status($code);
		}else {
			if(SW_DEBUG) {
				$this->response->write('error: '.$code .'is not a standard http code!');
			}
		}
	}	
}