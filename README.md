#	Serial2net for P1 / DSMR

Bridges a serial DSMR P1-port to wifi using a ESP8266 (ESP12) board. A schematic can be found here: 

https://github.com/thijsmans/serial2net_for_p1

Assign the ESP a static IP address in your router to connect it to Home Assistant. Then use it's IP and the port below to connect. Up to four clients (default) can join. 
  
Although this sketch is inteded for DSMR / P1, it should work with any serial input device.

# LED-behaviour
* Power LED is always on (given power);
* Wifi LED blinks when connecting/disconnected, is on when connected;
* RX LED blinks when serial data is received
* Client-LEDs turn on when a client connected, blinks when data is sent

# Based on 
* ESP8266 Ser2net by Daniel Parnell (https://github.com/dparnell/esp8266-ser2net/), but simplified (no writing to UART from clients) and without ancient dependencies for what could easily be done with simple blinking LED's.
