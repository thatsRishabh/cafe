<?php




    function prepareResult($status, $message, $payload, $status_code)
    {
            if(empty($payload)) {
                $payload = new stdClass();
            } else {
                $payload = $payload;
            }
            return response()->json(['success' => $status, 'message' => $message, 'payload' => $payload, 'code' => $status_code],$status_code);
        
    }

      
	function getUser() {
		return auth('api')->user();
	}