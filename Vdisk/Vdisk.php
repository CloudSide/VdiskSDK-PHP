<?php
/**
 * PHP SDK for Sina Vdisk (using OAuth2)
 * @author Bruce Chen <662005@qq.com>
 */
namespace Vdisk;

/**
 * @ignore
 */
class Exception extends \Exception {

}

/**
 * @ignore
 */
class OAuthException extends Exception {
	
	// pass
}

/**
 * @ignore
 */
class BadRequestException extends Exception {

	// pass
}

/**
 * @ignore
 */
class CurlException extends Exception {

	// pass
}

/**
 * @ignore
 */
class NotAcceptableException extends Exception {
	
	// pass
}

/**
 * @ignore
 */
class NotFoundException extends Exception {

	// pass
}

/**
 * @ignore
 */
class NotModifiedException extends Exception {
	
	// pass
}

/**
 * @ignore
 */
class UnsupportedMediaTypeException extends Exception {
	
	// pass
}

/**
 * @ignore
 */
class UnauthorizedException extends Exception {
	
	// pass
}

/**
 * @ignore
 */
class ForbiddenException extends Exception {
	
	// pass
}


/**
 * 新浪微盘 OAuth 认证类(OAuth2) Vdisk\OAuth2
 *
 * 授权机制说明请参考文档：{@link http://vdisk.weibo.com/developers/index.php?module=api&action=authinfo}
 *
 * @package Vdisk
 * @subpackage OAuth2
 * @author Bruce Chen <662005@qq.com>
 * @version 1.0
 */
class OAuth2 {
	
	/**
	 * App Key
	 */
	public $clientId;
	/**
	 * App Secret
	 */
	public $clientSecret;
	/**
	 *  Access Token
	 */
	public $accessToken;
	/**
	 * Refresh Token
	 */
	public $refreshToken;
	/**
	 * Expires In
	 */
	public $expiresIn;
	/**
	 * print the debug info
	 *
	 * @ignore
	 */
	public $debug = false;
	/**
	 * @ignore
	 */
	public $remoteIp = null;
	/**
	 * @ignore
	 */
	public $outFile = null;
	/**
	 * @ignore
	 */
	public $inFile = null;	
	/**
	 * @ignore
	 * Default cURL options
	 * @var array
	 */
	protected $defaultCurlOptions = array(
	
		CURLOPT_SSL_VERIFYPEER	=> false,
		CURLOPT_VERBOSE			=> true,
		CURLOPT_HEADER			=> true,
		CURLINFO_HEADER_OUT		=> false,
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_FOLLOWLOCATION	=> false,
		CURLOPT_TIMEOUT			=> 30,
		CURLOPT_CONNECTTIMEOUT	=> 30,
		CURLOPT_USERAGENT		=> 'VdiskSDK-PHP OAuth2 v0.1',
	);
	
	/**
	  * @ignore
	  * Store the last response form the API
	  * @var mixed
	  */
	protected $lastResponse = null;
	
	
	/**
	 * Set API URLs
	 */
	/**
	 * @ignore
	 */
	public function accessTokenURL() { return 'https://auth.sina.com.cn/oauth2/access_token'; }
	/**
	 * @ignore
	 */
	public function authorizeURL() { return 'https://auth.sina.com.cn/oauth2/authorize'; }
	
	
	/**
	 * 新浪微盘OAuth2构造方法
	 * @param string $clientId 应用的app key
	 * @param string $clientSecret 应用的app secret
	 * @param string $accessToken 用户授权后产生的access token 可选
	 * @param string $refreshToken 用户授权后产生的refresh token 可选
	 * @return void
	 */
	public function __construct($clientId, $clientSecret, $accessToken = null, $refreshToken = null) {
		
		// Check the cURL extension is loaded
		if (!extension_loaded('curl')) {
		
			throw new Exception('The cURL OAuth consumer requires the cURL extension');
		}
		
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->accessToken = $accessToken;
		$this->refreshToken = $refreshToken;
	}
	
	
	/**
	 * @ignore
	 * @todo 实现签名
	 */
	private function getSignedRequest($method, $url, array $headers = array(), array $postfields = array()) {

		if (isset($this->accessToken) && $this->accessToken) {
			
			$headers[] = "Authorization: OAuth2 " . $this->accessToken;
		}
		
		if (!empty($this->remoteIp)) {
			
			$headers[] = "x-vdisk-cip: " . $this->remoteIp;
		
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			
			$headers[] = "x-vdisk-cip: " . $_SERVER['REMOTE_ADDR'];
		}
		
		// @todo 签名后生成新的url
		
		return array (
			
			'url' => $url,
			'postfields' => $postfields,
			'method' => $method,
			'headers' => $headers,
		);
	}
	
	
	/**
	 * @ignore
	 * Execute an API call
	 * @todo Improve error handling
	 * @param string $method The HTTP method
	 * @param string $url
	 * @param string $headers The HTTP headers
	 * @param array $postfields The HTTP POST parameters
	 * @return string|object stdClass
	 */
	public function fetch($method, $url, array $headers = array(), array $postfields = array()) {
		
		// Get the signed request URL
		$request = $this->getSignedRequest($method, $url, $headers, $postfields);
		
		// Initialise and execute a cURL request
		$handle = curl_init($request['url']);
		
		// Get the default options array
		$options = $this->defaultCurlOptions;
		
		if (isset($request['headers']) && !empty($request['headers'])) {
			
			$options[CURLOPT_HTTPHEADER] = $request['headers'];
		}
		
		if ($method == 'GET' && $this->outFile) { // GET
		
			$options[CURLOPT_HTTPHEADER] = array();
			$options[CURLOPT_RETURNTRANSFER] = false;
			$options[CURLOPT_HEADER] = false;
			$options[CURLOPT_FILE] = $this->outFile;
			$options[CURLOPT_BINARYTRANSFER] = true;
			$this->outFile = null;
		
		} elseif ($method == 'POST') { // POST
		
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = $request['postfields'];
		
		} elseif ($method == 'PUT' && $this->inFile) { // PUT
		
			$options[CURLOPT_PUT] = true;
			$options[CURLOPT_INFILE] = $this->inFile;
			// @todo Update so the data is not loaded into memory to get its size
			$options[CURLOPT_INFILESIZE] = strlen(stream_get_contents($this->inFile));
			fseek($this->inFile, 0);
			$this->inFile = null;
		}
		
		// Set the cURL options at once
		curl_setopt_array($handle, $options);
		
		// Execute, get any error and close
		$response = curl_exec($handle);
		$error = curl_error($handle);
		
		
		if ($this->debug) {
			
			echo "<pre>";
			echo "=====post data======\r\n";
			print_r($request['postfields']);
			
			echo "=====headers======\r\n";
			print_r($request['headers']);
			
			echo '=====request info====='."\r\n";
			print_r(curl_getinfo($handle));
			
			echo '=====response====='."\r\n";
			print_r($response);
			echo "</pre>";
		}
		
		curl_close($handle);
									
		//Check if a cURL error has occured
		if ($response === false) {
			
			throw new CurlException($error);
	    
	    } else {
		
			// Parse the response if it is a string
			if (is_string($response)) {
				
				$response = $this->parse($response);
			}
			
			// Set the last response
			$this->lastResponse = $response;
			
			// The API doesn't return an error message for the 304 status code...
			// 304's are only returned when the path supplied during metadata calls has not been modified
			if ($response['code'] == 304) {
				
				$response['body'] = new \stdClass;
				$response['body']->error = 'The folder contents have not changed';
			}
			
			// Check if an error occurred and throw an Exception
			if (!empty($response['body']->error)) {
				
				// returns error messages inconsistently...
				if ($response['body']->error instanceof \stdClass) {
					
					$array = array_values((array) $response['body']->error);
					$message = $array[0];
	
				} else {
					
					$message = $response['body']->error;
				}
				
				// Throw an Exception with the appropriate with the appropriate message and code
				switch ($response['code']) {
					
					case 304:
						throw new NotModifiedException($message, 304);
						break;
					case 400:
						throw new BadRequestException($message, 400);
						break;
					case 401:
						throw new UnauthorizedException($message, 401);
						break;
					case 404:
						throw new NotFoundException($message, 404);
						break;
					case 403:
						throw new ForbiddenException($message, 403);
						break;
					case 406:
						throw new NotAcceptableException($message, 406);
						break;
					case 415:
						throw new UnsupportedMediaTypeException($message, 415);
						break;
					default:
						throw new Exception($message, $response['code']);
						break;
				}
			}
			
			return $response;
		}
	}
	
	
	/**
	 * @ignore
     * Parse a cURL response
     * @param string $response 
     * @return array
     */
    private function parse($response) {
	
        // Explode the response into headers and body parts (separated by double EOL)
        list($headers, $response) = explode("\r\n\r\n", $response, 2);
        
        // Explode response headers
        $lines = explode("\r\n", $headers);
        
        // If the status code is 100, the API server must send a final response
        // We need to explode the response again to get the actual response
        if (preg_match('#^HTTP/1.1 100#', $lines[0])) {
	
            list($headers, $response) = explode("\r\n\r\n", $response, 2);
            $lines = explode("\r\n", $headers);
        }
        
        // Get the HTTP response code from the first line
        $first = array_shift($lines);
        $pattern = '#^HTTP/1.1 ([0-9]{3})#';
        preg_match($pattern, $first, $matches);
        $code = $matches[1];
        
        // Parse the remaining headers into an associative array
        $headers = array();

        foreach ($lines as $line) {
	
            list($k, $v) = explode(': ', $line, 2);
            $headers[strtolower($k)] = $v;
        }
        
        // If the response body is not a JSON encoded string
        // we'll return the entire response body
        if (!$body = json_decode($response)) {
	
            $body = $response;
        }
        
        return array('code' => $code, 'body' => $body, 'headers' => $headers);
    }
	    
	/**
	 * Return the response for the last API request
	 * @return mixed
	 */
	public function getlastResponse() {
		
		return $this->lastResponse;
	}
	

	/**
	 * authorize接口, 返回授权页面的URL
	 *
	 * 对应API：{@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#authorize /oauth2/authorize}
	 *
	 * @param string $url 授权后的回调地址, 站外应用需与回调地址一致, 站内应用需要填写canvas page的地址
	 * @param string $response_type 支持的值包括 code 和token 默认值为code
	 * @param string $state 用于保持请求和回调的状态。在回调时,会在Query Parameter中回传该参数
	 * @param string $display 授权页面类型 可选范围: 
	 *  - default		默认授权页面		
	 *  - mobile		支持html5的手机		
	 *  - popup			弹窗授权页		
	 * @return array
	 */
	public function getAuthorizeURL($url, $response_type = 'code', $state = NULL, $display = NULL) {
		
		$params = array();
		$params['client_id'] = $this->clientId;
		$params['redirect_uri'] = $url;
		$params['response_type'] = $response_type;
		$params['state'] = $state;
		$params['display'] = $display;
		
		return $this->authorizeURL() . "?" . http_build_query($params);
	}

	/**
	 * access_token接口 用authorization_code换取accsee_token
	 *
	 * 对应API：{@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#access_token /oauth2/access_token}
	 *
	 * @param string $type 请求的类型,可以为:code, password, token
	 * @param array $keys 其他参数：
	 *  - 当$type为code时： array('code'=>..., 'redirect_uri'=>...)
	 *  - 当$type为password时： array('username'=>..., 'password'=>...)
	 *  - 当$type为token时： array('refresh_token'=>...)
	 * @return array
	 */
	public function getAccessToken($type = 'code', $keys) {
		
		$params = array();
		$params['client_id'] = $this->clientId;
		$params['client_secret'] = $this->clientSecret;
		
		if ( $type === 'token' ) {
		
			$params['grant_type'] = 'refresh_token';
			$params['refresh_token'] = $keys['refresh_token'];
		
		} elseif ( $type === 'code' ) {
		
			$params['grant_type'] = 'authorization_code';
			$params['code'] = $keys['code'];
			$params['redirect_uri'] = $keys['redirect_uri'];
		
		} elseif ( $type === 'password' ) {
		
			$params['grant_type'] = 'password';
			$params['username'] = $keys['username'];
			$params['password'] = $keys['password'];
		
		} else {
		
			throw new OAuthException("wrong auth type");
		}

		$response = $this->fetch('POST', $this->accessTokenURL(), array(), $params);
		$token = $response['body'];
		
		if (is_object($token) && isset($token->access_token)) {
		
			$this->accessToken = $token->access_token;
			$this->refreshToken = $token->refresh_token;
			$this->expiresIn = $token->time_left;
		
		} else {
		
			throw new OAuthException("get access token failed. " . $token->code . ': ' . $token->msg);
		}
		
		return $token;
	}

	/**
	 * @ignore
	 */
	public static function base64decode($str) {
		
		return base64_decode(strtr($str.str_repeat('=', (4 - strlen($str) % 4)), '-_', '+/'));
	}

	/**
	 * 从数组中读取access_token和refresh_token
	 * 常用于从Session或Cookie中读取token，或通过Session/Cookie中是否存有token判断登录状态。
	 *
	 * @param stdClass $object 存有access_token和secret_token的对象
	 * @return stdClass 成功返回object('access_token'=>'value', 'refresh_token'=>'value'); 失败返回false
	 */
	public function getTokenFromObject($object) {
		
		if (isset($object->access_token) && $object->access_token) {
				
			$token = new \stdClass;
			$this->accessToken = $token->access_token = $object->access_token;
		
			if (isset($object->refresh_token) && $object->refresh_token) {
		
				$this->refreshToken = $token->refresh_token = $object->refresh_token;
			}
			
			if (isset($object->time_left) && $object->time_left) {
				
				$this->expiresIn = $token->expires_in = $object->time_left;
			}

			return $token;
		
		} else {
		
			return false;
		}
	}
	
	
    /**
     * Set the output file
     * @param resource Resource to stream response data to
     * @return void
     */
    public function setOutFile($handle) {
	
        if (!is_resource($handle) || get_resource_type($handle) != 'stream') {
	
            throw new Exception('Outfile must be a stream resource');
        }

        $this->outFile = $handle;
    }
    
    /**
     * Set the input file
     * @param resource Resource to read data from
     * @return void
     */
    public function setInFile($handle) {
	
        if (!is_resource($handle) || get_resource_type($handle) != 'stream') {
	
            throw new Exception('Infile must be a stream resource');
        }

        fseek($handle, 0);
        $this->inFile = $handle;
    }

}




/**
 * 微盘接口作类 \Vdisk\Client
 *
 * 请参考微盘接口文档：{@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc}
 *
 * @package Vdisk
 * @subpackage Client
 * @author Bruce Chen <662005@qq.com>
 * @version 1.0
 */
class Client {

	// API Endpoints
	/**
	 * @ignore
	 */
	const API_URL     		= 'https://api.weipan.cn/2/';
	/**
	 * @ignore
	 */	
	const CONTENT_URL 		= 'http://upload-vdisk.sina.com.cn/2/';
	/**
	 * @ignore
	 */
	const CONTENT_SAFE_URL	= 'https://upload-vdisk.sina.com.cn:4443/2/';
	
	/**
	 * @ignore
	 * OAuth consumer object
	 * @var null|OAuth2 
	 */
	private $OAuth;
	
	
	/**
	 * @ignore
	 * The root level for file paths
	 * Either `basic` or `sandbox` (preferred)
	 * @var null|string
	 */
	private $root;
	 
	/**
	 * @ignore
	 * Format of the API response
	 * @var string
	 */
	private $responseFormat = 'php';
	
	
	/**
	 * JSONP callback
	 * @var string
	 */
	private $callback = 'vdiskCallback';
	    

	/**
	 * @ignore
	 * Chunk size used for chunked uploads
	 * @see \Vdisk\Client::chunkedUpload()
	 */
	private $chunkSize = 4194304; 
	    
	/**
	 * 构造函数
	 * 
	 * @access public
	 * @param OAuth2 $oAuth \Vdisk\OAuth2实例
	 * @param mixed $root 微盘容器的类型(basic or sandbox), 默认为sandbox
	 * @return void
	 */
	public function __construct(OAuth2 $oAuth, $root = 'sandbox') {
		
		$this->OAuth = $oAuth;
		$this->setRoot($root);
	}

	/**
	 * Set the root level
	 * @param mixed $root
	 * @throws Exception
	 * @return void
	 */
	public function setRoot($root) {
		
		if ($root !== 'sandbox' && $root !== 'basic') {
			
			throw new Exception("Expected a root of either 'basic' or 'sandbox', got '$root'");
		
		} else {
			
			$this->root = $root;
		}
	}

	/**
	 * 开启调试信息
	 *
	 * 开启调试信息后, SDK会将每次请求微盘API所发送的POST Data、Headers以及请求信息、返回内容输出出来。
	 *
	 * @access public
	 *
	 * @param boolean $enable 是否开启调试信息
	 *
	 * @return void
	 */
	public function setDebug($enable) {
		
		$this->OAuth->debug = $enable;
	}

	/**
	 * 设置用户IP
	 *
	 * SDK默认将会通过$_SERVER['REMOTE_ADDR']获取用户IP, 在请求微盘API时将用户IP附加到Request Header中. 但某些情况下$_SERVER['REMOTE_ADDR']取到的IP并非用户IP, 而是一个固定的IP, 此时就有可能会造成该固定IP达到微盘API调用频率限额, 导致API调用失败. 此时可使用本方法设置用户IP, 以避免此问题.
	 *
	 * @access public
	 * @param string $ip 用户IP
	 * @return bool IP为非法IP字符串时，返回false，否则返回true
	 */
	public function setRemoteIp($ip) {
		
		if (ip2long($ip) !== false) {
			
			$this->OAuth->remoteIp = $ip;
			
			return true;
		
		} else {
		
			return false;
		}
	}

	/**
	 * 获取用户信息以及容量信息
	 *
	 * 对应API：{@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#account_info account/info}
	 *
	 * @access public
	 * @return array
	 */
	public function accountInfo() {
		
		$response = $this->fetch('GET', self::API_URL, 'account/info');
		
		return $response;
	}

	/**
	 * 获取文件和目录信息
	 *
	 * <br />对应API：{@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#metadata metadata}
	 * 
	 * @param string $path The path to the file/folder, relative to root
	 * @param string $rev Return metadata for a specific revision (Default: latest rev)
	 * @param int $limit Maximum number of listings to return
	 * @param string $hash Metadata hash to compare against
	 * @param bool $list Return contents field with response
	 * @param bool $deleted Include files/folders that have been deleted
	 * @return object stdClass 
	 */

    public function metaData($path = null, $rev = null, $limit = 10000, $hash = false, $list = true, $deleted = false) {
	
        $params = array(

            'file_limit' => ($limit < 1) ? 1 : (($limit > 10000) ? 10000 : (int)$limit),
            'hash' => (is_string($hash)) ? $hash : 0,
            'list' => $list ? 'true' : 'false',
            'include_deleted' => (int)$deleted ? 'true' : 'false',
            'rev' => (is_string($rev)) ? $rev : null,
        );

		$call = 'metadata/' . $this->root . '/' . $this->encodePath($path) . '?' . http_build_query($params);

        $response = $this->fetch('GET', self::API_URL, $call);

        return $response;
    }

    /**
     * Return "delta entries", intructing you how to update
     * your application state to match the server's state
     * Important: This method does not make changes to the application state
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#delta delta}
	 *
     * @param null|string $cursor Used to keep track of your current state
     * @return array Array of delta entries
     */
    public function delta($cursor = null) {
	
        $params = array('cursor' => $cursor);
		$call = 'delta?' . http_build_query($params);;
        $response = $this->fetch('GET', self::API_URL, $call);

        return $response;
    }
    
    /**
     * Obtains metadata for the previous revisions of a file
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#revisions revisions}
	 *
     * @param string Path to the file, relative to root
     * @param integer Number of revisions to return (1-1000)
     * @return array
     */
    public function revisions($file, $limit = 10) {
	
        $params = array(

            'rev_limit' => ($limit < 1) ? 1 : (($limit > 1000) ? 1000 : (int) $limit),
        );
		
		$call = 'revisions/' . $this->root . '/' . $this->encodePath($file) . '?' . http_build_query($params);

        $response = $this->fetch('GET', self::API_URL, $call);

        return $response;
    }
    
    /**
     * Restores a file path to a previous revision
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#restore restore}
	 *
     * @param string $file Path to the file, relative to root
     * @param string $revision The revision of the file to restore
     * @return object stdClass
     */
    public function restore($file, $revision) {
	
        $call = 'restore/' . $this->root . '/' . $this->encodePath($file);
        $params = array('rev' => $revision);
        $response = $this->fetch('POST', self::API_URL, $call, $params);
        return $response;
    }
    
    /**
     * Returns metadata for all files and folders that match the search query
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#search search}
	 *
     * @param mixed $query The search string. Must be at least 3 characters long
     * @param string $path The path to the folder you want to search in
     * @param integer $limit Maximum number of results to return (1-1000)
     * @param boolean $deleted Include deleted files/folders in the search
     * @return array
     */
    public function search($query, $path = '', $limit = 1000, $deleted = false) {
	
		$params = array(
		
			'query' => $query,
			'file_limit' => ($limit < 1) ? 1 : (($limit > 1000) ? 1000 : (int) $limit),
			'include_deleted' => (int) $deleted,
		);
	
        $call = 'search/' . $this->root . '/' . $this->encodePath($path) . '?' . http_build_query($params);

        $response = $this->fetch('GET', self::API_URL, $call);

        return $response;
    }
    
    /**
     * 获取图片文件的缩略图
	 *
     * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#thumbnails thumbnails}
	 *
     * @param string $path The path to the file/folder you want a sharable link to
     * @param string $size 缩略图的尺寸 字符串(s,m,l,xl)
     * @param string &$url 缩略图下载地址
     *
     * @return object stdClass
     */
    public function thumbnails($path, $size, &$url) {
	
        $call = 'thumbnails/' . $this->root . '/' .$this->encodePath($path);
        $response = $this->fetch('GET', self::API_URL, $call);
        
        
        if (isset($response['headers']['location'])) {
					
			$url = $response['headers']['location'];
		}
		       
        return $response;
    }
    
    
    /**
     * Creates and returns a shareable link to files or folders
	 *
     * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#shares shares}
	 *
     * @param string $path The path to the file/folder you want a sharable link to
     * @return object stdClass
     */
    public function shares($path) {
	
        $call = 'shares/' . $this->root . '/' .$this->encodePath($path);
        $response = $this->fetch('POST', self::API_URL, $call);
        return $response;
    }
    
    
    
    /**
     * Returns a link directly to a file
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#media media}
	 *
     * @param string $path The path to the media file you want a direct link to
     * @return object stdClass
     */
    public function media($path) {
	
        $call = 'media/' . $this->root . '/' . $this->encodePath($path);
        $response = $this->fetch('GET', self::API_URL, $call);
        return $response;
    }
    
   
    /**
     * Creates and returns a copy_ref to a file
	 *
     * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#copy_ref copy_ref}
	 *
     * @param $path File for which ref should be created, relative to root
     * @return array
     */
    public function copyRef($path) {
	
        $call = 'copy_ref/' . $this->root . '/' . $this->encodePath($path);
        $response = $this->fetch('POST', self::API_URL, $call);

        return $response;
    }
    
    /**
     * Copies a file or folder to a new location
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#copy fileops/copy}
	 *
     * @param string $from File or folder to be copied, relative to root
     * @param string $to Destination path, relative to root
     * @param null|string $fromCopyRef Must be used instead of the from_path
     * @return object stdClass
     */
    public function copy($from, $to, $fromCopyRef = null) {
	
        $call = 'fileops/copy';

        $params = array(

            'root' => $this->root,
            'from_path' => $this->normalisePath($from),
            'to_path' => $this->normalisePath($to),
        );
        
        if ($fromCopyRef) {
	
            $params['from_path'] = null;
            $params['from_copy_ref'] = $fromCopyRef;
        }
        
        $response = $this->fetch('POST', self::API_URL, $call, $params);

        return $response;
    }
    
    /**
     * Creates a folder
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#create_folder fileops/create_folder}
	 *
     * @param string New folder to create relative to root
     * @return object stdClass
     */
    public function create($path) {
	
        $call = 'fileops/create_folder';
        $params = array('root' => $this->root, 'path' => $this->normalisePath($path));
        $response = $this->fetch('POST', self::API_URL, $call, $params);
        return $response;
    }
    
    /**
     * Deletes a file or folder
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#delete fileops/delete}
	 *
     * @param string $path The path to the file or folder to be deleted
     * @return object stdClass
     */
    public function delete($path) {
	
        $call = 'fileops/delete';
        $params = array('root' => $this->root, 'path' => $this->normalisePath($path));
        $response = $this->fetch('POST', self::API_URL, $call, $params);

        return $response;
    }
    
    /**
     * Moves a file or folder to a new location
	 *
	 * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#move fileops/move}	 
	 *
     * @param string $from File or folder to be moved, relative to root
     * @param string $to Destination path, relative to root
     * @return object stdClass
     */
    public function move($from, $to) {
	
        $call = 'fileops/move';

        $params = array(

                'root' => $this->root,
                'from_path' => $this->normalisePath($from),
                'to_path' => $this->normalisePath($to),
        );

        $response = $this->fetch('POST', self::API_URL, $call, $params);
        return $response;
    }


	/**
     * Uploads a physical file from disk
     * 
     * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#files_post files(POST)}	 
     *
     * exceeds this limit or does not exist, an Exception will be thrown
     * @param string $file 要上传的文件的真实路径
     * @param string $toPath 目标文件路径
     * @param boolean $overwrite Should the file be overwritten? (Default: true)
	 * @param string $parentRev 当前文件的版本
	 * @param boolean $safe 是否使用https 默认为false
     * @return object stdClass
     */
    public function putFile($file, $toPath, $overwrite = true, $parentRev = null, $safe = false) {
    
        if (file_exists($file)) {
        
            $call = 'files/' . $this->root . '/' . $this->encodePath($toPath);
            // If no filename is provided we'll use the original filename
            $filename = basename($file);
            
            $params = array(

                'file' => '@' . str_replace('\\', '/', $file) . ';filename=' . $filename,
                'overwrite' => $overwrite ? 'true' : 'false',
            );

			if ($parentRev && is_string($parentRev)) {
				
				$params['parent_rev'] = $parentRev;
			}
            
			if ($safe) {
				
				$response = $this->fetch('POST', self::CONTENT_SAFE_URL, $call, $params);
				
			} else {
				
				$response = $this->fetch('POST', self::CONTENT_URL, $call, $params);
			}
			
			return $response;
        }
        
        // Throw an Exception if the file does not exist
        throw new Exception('Local file ' . $file . ' does not exist');
    }
    
    /**
     * Uploads file data from a stream
     * Note: This function is experimental and requires further testing
     *
     * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#files_put files_put}
     *
     * @todo Add filesize check
     * @param resource $stream A readable stream created using fopen()
     * @param string $toPath The destination filename, including path
     * @param boolean $overwrite Should the file be overwritten? (Default: true)
	 * @param boolean $safe 是否使用https 默认为false
     * @return array
     */
    public function putStream($stream, $toPath, $overwrite = true, $safe = false) {
    
        $this->OAuth->setInFile($stream);
        $path = $this->encodePath($toPath);
        $params = array('overwrite' => $overwrite ? 'true' : 'false');
        $call = 'files_put/' . $this->root . '/' . $path . '?' . http_build_query($params);

		if ($safe) {
			
			$response = $this->fetch('PUT', self::CONTENT_SAFE_URL, $call);
		
		} else {
			
			$response = $this->fetch('PUT', self::CONTENT_URL, $call);
		}
		
        return $response;
    }
    
    /**
     * Downloads a file
     * Returns the base filename, raw file data and mime type returned by Fileinfo
     *
     * <br />对应API: {@link http://vdisk.weibo.com/developers/index.php?module=api&action=apidoc#files_get files(GET)}
     *
     * @param string $file Path to file, relative to root, including path
     * @param string $outFile Filename to write the downloaded file to
     * @param string $revision The revision of the file to retrieve
     * @return array
     */
    public function getFile($file, $outFile = false, $revision = null) {
    
        // Only allow php response format for this call
        if ($this->responseFormat !== 'php') {
        
            throw new Exception('This method only supports the `php` response format');
        }
        
        $file = $this->encodePath($file);
        $params = array('rev' => $revision);
        $call = 'files/' . $this->root . '/' . $file . '?' . http_build_query($params);
        $response = $this->fetch('GET', self::API_URL, $call);

		$handle = null;
        
        if ($outFile !== false) {
        
            // Create a file handle if $outFile is specified
            if (!$handle = fopen($outFile, 'w')) {
        
                throw new Exception("Unable to open file handle for $outFile");
        
            } else {
        
                $this->OAuth->setOutFile($handle);

				if (isset($response['headers']['location'])) {
					
					$this->fetch('GET', $response['headers']['location']);
				}
            }
        }
        
        // Close the file handle if one was opened
        if ($handle) fclose($handle);

        return array(
        
            'name' => ($outFile) ? $outFile : basename($file),
            'mime' => $this->getMimeType(($outFile) ?: $response['body'], $outFile),
            'meta' => json_decode($response['headers']['x-vdisk-metadata']),
            //'data' => $response['body'],
			'download_url' => $response['headers']['location'],
        );
    }
    
	

	/* ================================================================= */

    /**
	 * @ignore
     * Intermediate fetch function
     * @param string $method The HTTP method
     * @param string $url The API endpoint
     * @param string $call The API method to call
     * @param array $params Additional parameters
     * @return mixed
     */
    private function fetch($method, $url, $call = '', array $params = array(), array $headers = array()) {
		
		$url .= $call;
		
        // Make the API call via the consumer
        $response = $this->OAuth->fetch($method, $url, $headers, $params);
        
        // Format the response and return
        switch ($this->responseFormat) {
            case 'json':
                return json_encode($response);
            case 'jsonp':
                $response = json_encode($response);
                return $this->callback . '(' . $response . ')';
            default:
                return $response;
        }
    }
    
    /**
     * Set the API response format
     * @param string $format One of php, json or jsonp
     * @return void
     */
    public function setResponseFormat($format) {
	
        $format = strtolower($format);

        if (!in_array($format, array('php', 'json', 'jsonp'))) {
	
            throw new Exception("Expected a format of php, json or jsonp, got '$format'");
    
	    } else {
    
	        $this->responseFormat = $format;
        }
    }
    
    /**
     * Set the chunk size for chunked uploads
     * If $chunkSize is empty, set to 4194304 bytes (4 MB)
	 * @param int $chunkSize 分片大小
	 * @return void
     */
    public function setChunkSize($chunkSize = 4194304) {
	
        if (!is_int($chunkSize)) {
	
            throw new Exception('Expecting chunk size to be an integer, got ' . gettype($chunkSize));
    
	    } elseif ($chunkSize > 157286400) {
    
	        throw new Exception('Chunk size must not exceed 157286400 bytes, got ' . $chunkSize);
    
	    } else {
    	
	        $this->chunkSize = $chunkSize;
        }
    }
    
    /**
    * Set the JSONP callback function
    * @param string $function
    * @return void
    */
    public function setCallback($function) {
	
        $this->callback = $function;
    }
    
    /**
	 * @ignore
     * Get the mime type of downloaded file
     * If the Fileinfo extension is not loaded, return false
     * @param string $data File contents as a string or filename
     * @param string $isFilename Is $data a filename?
     * @return boolean|string Mime type and encoding of the file
     */
    private function getMimeType($data, $isFilename = false) {
	
        if (extension_loaded('fileinfo')) {
    
	        $finfo = new \finfo(FILEINFO_MIME);
    
	        if ($isFilename !== false) {

                return $finfo->file($data);
            }
    
	        return $finfo->buffer($data);
        }
    
	    return false;
    }
    
    /**
	 * @ignore
     * Trim the path of forward slashes and replace
     * consecutive forward slashes with a single slash
     * @param string $path The path to normalise
     * @return string
     */
    private function normalisePath($path) {
	
        $path = preg_replace('#/+#', '/', trim($path, '/'));
        return $path;
    }


    /**
	 * @ignore
     * Encode the path, then replace encoded slashes
     * with literal forward slash characters
     * @param string $path The path to encode
     * @return string
     */
    private function encodePath($path) {
	
        $path = $this->normalisePath($path);
        $path = str_replace('%2F', '/', rawurlencode($path));
        return $path;
    }

}





