<?php
	/**
	 * Class P1Reader
	 * Reads and parses DSMR-telegrams through a socket connection
	 */
	class P1Reader 
	{
	    private $host;		// Host of the DSMR meter
	    private $port;		// Port of the DSMR meter
	    private $socket;	// Socketverbinding

	    /**
	     * P1Reader constructor.
	     * @param string $host - Host of the meter
	     * @param int $port - Port of the meter
	     */
	    public function __construct($host, $port) 
	    {
	        $this->host = $host;
	        $this->port = $port;
	        $this->connect();
	    }

	    /**
	     * Create a connection to the DSMR meter.
	     * @throws Exception - Throws an exception if the connection could not be established.
	     */
	    private function connect() 
	    {
	        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);
	        if (!$this->socket) {
	            throw new Exception("Unable to connect to {$this->host}:{$this->port} - $errstr ($errno)");
	        }
	    }

	    /**
	     * Checks if the socket is stilla ctive.
	     * @return bool - True when active, false otherwise.
	     */
	    private function isSocketAlive() 
	    {
	        if ($this->socket === false) 
	        {
	            return false;
	        }

	        $ping = fwrite($this->socket, "\r\n");
	        return $ping !== false;
	    }

	    /**
	     * Reads a telegram of the DSMR meter.
	     * @return array - Parsed telegram as an associative array.
	     * @throws Exception - Throws an exception when the connection was lost and could not be re-established.
	     */
	    public function readTelegram() 
	    {
	        if (!$this->isSocketAlive()) 
	        {
	            $this->connect();
	        }

	        $telegram = '';
	        while (($line = fgets($this->socket, 128)) !== false) {
	            $telegram .= $line;
	            if (strpos($line, '!') === 0) {
	                break;
	            }
	        }
	        return $this->parseTelegram($telegram);
	    }

	    /**
	     * Parses the telegram and convers keys to human readable names.
	     * @param string $telegram - The raw DSMR telegram.
	     * @return array - Parsed telegram with descriptive keys.
	     */
	    private function parseTelegram($telegram) 
	    {
	    	$keyMap = [
	            '0-0:1.0.0' 	=> 'timestamp',
	    		    '0-0:96.1.1' 	=> 'equipment_identifier',
	    		    '0-0:96.7.9' 	=> 'no_long_powerfailures',
	    		    '0-0:96.7.21'	=> 'no_powerfailures',
	    	    	'0-0:96.14.0' => 'tariff_indicator',
	            '1-0:1.7.0' 	=> 'current_consumption',
	            '1-0:1.8.1' 	=> 'consumption_tariff_1',
	            '1-0:1.8.2' 	=> 'consumption_tariff_2',
	            '1-0:2.7.0' 	=> 'current_production',
	            '1-0:2.8.1' 	=> 'production_tariff_1',
	            '1-0:2.8.2' 	=> 'production_tariff_2',
	            '1-0:21.7.0'	=> 'instant_active_power_L1',
	            '1-0:22.7.0'	=> 'current_production_L1',
	            '1-0:31.7.0' 	=> 'current_phase_L1',
	            '1-0:32.7.0' 	=> 'voltage_phase_L1',
	            '1-0:32.32.0' => 'no_voltage_sags_L1',
	            '1-0:32.36.0'	=> 'no_voltage_swells_L1',
	            '1-0:41.7.0'	=> 'instant_active_power_L2',
	            '1-0:42.7.0'	=> 'current_production_L2',
	            '1-0:51.7.0' 	=> 'current_phase_L2',
	            '1-0:52.7.0' 	=> 'voltage_phase_L2',
	            '1-0:52.32.0' => 'no_voltags_sags_L2',
	            '1-0:52.36.0'	=> 'no_voltage_swells_L2',
	            '1-0:61.7.0'	=> 'instant_active_power_L3',
	            '1-0:62.7.0'	=> 'current_production_L#',
	            '1-0:71.7.0' 	=> 'current_phase_L3',
	            '1-0:72.7.0' 	=> 'voltage_phase_L3',
	            '1-0:72.32.0' => 'no_voltags_sags_L3',
	            '1-0:72.36.0'	=> 'no_voltage_swells_L3',
	            '1-0:99.97.0' => 'power_failure_event_log', 
	            '1-3:0.2.8'		=> 'version_information',
	        ];

	        $data = [];
	        
	        $lines = explode("\n", $telegram);

	        foreach ($lines as $line) 
	        {
	            if (preg_match('/^(\d+-\d+:\d+\.\d+\.\d+)\(([^)]+)\)/', trim($line), $matches)) 
	            {
	                $key = $matches[1];
	                $value = $matches[2];

	                if( array_key_exists($key, $keyMap) ) 
	                	$key = $keyMap[ $key ];

	                $data[ $key ] = $value;
	            }
	           }
	        
	        return $data;
	    }

	    /**
	     * Destructor closes the socket if it still open.
	     */
	    public function __destruct() 
	    {
	        if ($this->socket) {
	            fclose($this->socket);
	        }
	    }
	}
