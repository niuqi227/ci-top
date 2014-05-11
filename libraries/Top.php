<?php
class Top{
		const CONFIG_FILE='../config/config.php';
		
		public $appkey;

		public $secretKey;

		public $gatewayUrl;
		
		public $auth_url;
		
		public $format = "json";

		public $connectTimeout;

		public $readTimeout;
		
		public $data_dir;

		/** 是否打开入参check**/
		private $checkRequest;

		protected $signMethod = "md5";

		protected $apiVersion = "2.0";

		protected $sdkVersion = "top-sdk-php-20140422";

		private $apiMethod;
		private $apiParas = array();
		
		function __construct(){
			require_once($config_file);
			
			$this->appkey 		= $testmode ? $app_key_online : $app_key_test;
			$this->secretKey 	= $testmode ? $secret_key_online : $secret_key_test;
			$this->gatewayUrl	= $testmode ? $gateway_url_online : $gateway_url_test;
			$this->auth_url		= $testmode ? $auth_url_online : $auth_url_test;
			$this->data_dir		- $data_dir;
			$this->checkRequest = $check_request;
			!empty($connect_timeout) && $this->connectTimeout = $connect_timeout;
			!empty($read_timeout) && $this->readTimeout = $read_timeout;
		}

		protected function generateSign($params)
		{
			ksort($params);

			$stringToBeSigned = $this->secretKey;
			foreach ($params as $k => $v)
			{
				if("@" != substr($v, 0, 1))
				{
					$stringToBeSigned .= "$k$v";
				}
			}
			unset($k, $v);
			$stringToBeSigned .= $this->secretKey;

			return strtoupper(md5($stringToBeSigned));
		}

		public function curl($url, $postFields = null)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FAILONERROR, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			if ($this->readTimeout) {
				curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
			}
			if ($this->connectTimeout) {
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
			}
			//https 请求
			if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			}

			if (is_array($postFields) && 0 < count($postFields))
			{
				$postBodyString = "";
				$postMultipart = false;
				foreach ($postFields as $k => $v)
				{
					if("@" != substr($v, 0, 1))//判断是不是文件上传
					{
						$postBodyString .= "$k=" . urlencode($v) . "&"; 
					}
					else//文件上传用multipart/form-data，否则用www-form-urlencoded
					{
						$postMultipart = true;
					}
				}
				unset($k, $v);
				curl_setopt($ch, CURLOPT_POST, true);
				if ($postMultipart)
				{
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
				}
				else
				{
					curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
				}
			}
			$reponse = curl_exec($ch);

			if (curl_errno($ch))
			{
				throw new Exception(curl_error($ch),0);
			}
			else
			{
				$httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if (200 !== $httpStatusCode)
				{
					throw new Exception($reponse,$httpStatusCode);
				}
			}
			curl_close($ch);
			return $reponse;
		}

		protected function logCommunicationError($apiName, $requestUrl, $errorCode, $responseTxt) {
			$localIp = isset ($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "CLI";
			$log_file = rtrim($this->data_dir, '\\/') . '/' . "logs/top_comm_err_" . date("Y-m-d") . ".log";
			$logData = array (
				date("Y-m-d H:i:s"),
				$apiName,
				$this->appId,
				$localIp,
				PHP_OS,
				$this->alipaySdkVersion,
				$requestUrl,
				$errorCode,
				str_replace("\n", "", $responseTxt)
			);
			error_log(var_export($logData,true).'\r\n\r\n',3,$log_file);
		}
		
		public function execute()
		{
			//组装系统参数
			$sysParams["app_key"] = $this->appkey;
			$sysParams["v"] = $this->apiVersion;
			$sysParams["format"] = $this->format;
			$sysParams["sign_method"] = $this->signMethod;
			$sysParams["method"] = $this->ApiMethod;
			$sysParams["timestamp"] = date("Y-m-d H:i:s");
			$sysParams["partner_id"] = $this->sdkVersion;
			if (null != $session)
			{
				$sysParams["session"] = $session;
			}

			//获取业务参数
			$apiParams = $this->ApiParas;

			//签名
			$sysParams["sign"] = $this->generateSign(array_merge($apiParams, $sysParams));

			//系统参数放入GET请求串
			$requestUrl = $this->gatewayUrl . "?";
			foreach ($sysParams as $sysParamKey => $sysParamValue)
			{
				$requestUrl .= "$sysParamKey=" . urlencode($sysParamValue) . "&";
			}
			$requestUrl = substr($requestUrl, 0, -1);

			//发起HTTP请求
			try
			{
				$resp = $this->curl($requestUrl, $apiParams);
			}
			catch (Exception $e)
			{
				$this->logCommunicationError($sysParams["method"],$requestUrl,"HTTP_ERROR_" . $e->getCode(),$e->getMessage());
				$result->code = $e->getCode();
				$result->msg = $e->getMessage();
				return $result;
			}

			//解析TOP返回结果
			$respWellFormed = false;
			if ("json" == $this->format)
			{
				$respObject = json_decode($resp);
				if (null !== $respObject)
				{
					$respWellFormed = true;
					foreach ($respObject as $propKey => $propValue)
					{
						$respObject = $propValue;
					}
				}
			}
			else if("xml" == $this->format)
			{
				$respObject = @simplexml_load_string($resp);
				if (false !== $respObject)
				{
					$respWellFormed = true;
				}
			}

			//返回的HTTP文本不是标准JSON或者XML，记下错误日志
			if (false === $respWellFormed)
			{
				$this->logCommunicationError($sysParams["method"],$requestUrl,"HTTP_RESPONSE_NOT_WELL_FORMED",$resp);
				$result->code = 0;
				$result->msg = "HTTP_RESPONSE_NOT_WELL_FORMED";
				return $result;
			}

			//如果TOP返回了错误码，记录到业务错误日志中
			if (isset($respObject->code))
			{
				$logger = new LtLogger;
				$logger->conf["log_file"] = rtrim(APPPATH, '\\/') . '/' . "logs/top_biz_err_" . $this->appkey . "_" . date("Y-m-d") . ".log";
				$logger->log(array(
					date("Y-m-d H:i:s"),
					$resp
				));
			}
			return $respObject;
		}
		
}
