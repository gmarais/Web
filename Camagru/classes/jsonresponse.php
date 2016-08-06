<?php
/* ******************************************************** _ *** _ ******** */
/*                                                   ______//_____\\______   */
/*   WhiteCanvas 2016                               |                     |  */
/*                                                  |                     |  */
/*   Created by Gabriel Marais                      |                     |  */
/*                                                  |                     |  */
/*                                                  |_____.____.______W_C_|  */
/*   https://github.com/gmarais                     |_____________________|  */
/*                                                  //         ||        \\  */
/* *********************************************** // ******************* \\ */

class JsonResponse
{
	private $_status;
	private $_data;

	public function __construct($status, $data = null)
	{
		$this->_status = $status;
		if ($data !== null && !is_array($data))
		{
			$data = array($data);
		}
		$this->_data = $data;
	}

	public function __toString()
	{
		if ($this->_data !== null)
			return json_encode(array("status" => $this->_status, "data" => $this->_data));
		else
			return json_encode(array("status" => $this->_status));
	}
}