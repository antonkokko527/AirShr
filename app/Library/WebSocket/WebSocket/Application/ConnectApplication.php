<?php

namespace WebSocket\Application;

class ConnectApplication extends Application
{
    private $_clients = array();
	private $_filename = '';

	public function onConnect($client)
    {
		$id = $client->getClientId();
        $this->_clients[$id] = $client;		
    }

    public function onDisconnect($client)
    {
        $id = $client->getClientId();		
		unset($this->_clients[$id]);     
    }

    public function onData($data, $client)
    {	
    	print_r("Data Received: " . $data . "\n");
    	
        $decodedData = json_decode($data, true);
        		
		if($decodedData === false)
		{
			return;
		}
		
		if (isset($decodedData['ping']) && $decodedData['ping'] == 'pong') {
			return;
		}
				
		// broadcast messages
		$this->_sendAll(json_encode($decodedData));
    }
    
    
    private function _sendAll($encodedData)
    {
    	if(count($this->_clients) < 1)
    	{
    		return false;
    	}
    	foreach($this->_clients as $sendto)
    	{
    		$sendto->send($encodedData);
    	}
    }
}