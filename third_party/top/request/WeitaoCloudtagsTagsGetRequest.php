<?php
/**
 * TOP API: taobao.weitao.cloudtags.tags.get request
 * 
 * @author auto create
 * @since 1.0, 2014-04-22 17:26:02
 */
class WeitaoCloudtagsTagsGetRequest
{
	
	private $apiParas = array();
	
	public function getApiMethodName()
	{
		return "taobao.weitao.cloudtags.tags.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
