<?php
/**
 * TOP API: taobao.ump.activity.delete request
 * 
 * @author auto create
 * @since 1.0, 2014-04-22 17:26:02
 */
class UmpActivityDeleteRequest
{
	/** 
	 * 活动id
	 **/
	private $actId;
	
	private $apiParas = array();
	
	public function setActId($actId)
	{
		$this->actId = $actId;
		$this->apiParas["act_id"] = $actId;
	}

	public function getActId()
	{
		return $this->actId;
	}

	public function getApiMethodName()
	{
		return "taobao.ump.activity.delete";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->actId,"actId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
