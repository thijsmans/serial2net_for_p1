// Congifuration
#define WIFI_SSID       "YOUR_SSID"
#define WIFI_PASSWORD   "YOUR_PASSWORD"

#define LISTEN_PORT     2001
#define BAUD_RATE       115200
#define BUFFER_SIZE     128
#define MAX_CLIENTS     4

int last_clients_count = 0;
int pin_wifi = 13;
int pin_rx = 12;
int pin_clients[] = { 14, 16, 15, 4 };

// Includes and variables
#include <ESP8266WiFi.h>

WiFiServer server( LISTEN_PORT );
WiFiClient serverClients[ MAX_CLIENTS ];

// Sketch setup
void setup(void) {

  pinMode( pin_wifi, OUTPUT);
  pinMode( pin_rx, OUTPUT);
  for( int i=0; i<4; i++ )
  {
    pinMode( pin_clients[i], OUTPUT);
  }

  // Connect to WiFi network
  connect_to_wifi();

  // Start UART
  Serial.begin(BAUD_RATE);

  // Start server
  server.begin();
  server.setNoDelay(true);
}

// Sketch main
void loop(void)
{
  // Check Wifi connection
  if(WiFi.status() != WL_CONNECTED) 
  {
    // Connection lost, close connections
    for(int i=0; i<MAX_CLIENTS; i++ )
    {
      if(serverClients[i])
      {
        serverClients[i].stop();
        digitalWrite( pin_clients[i], LOW );
      }
    }
   
    connect_to_wifi();
  }

  // Remove disconnected clients
  for( int i=0; i<MAX_CLIENTS; i++ )
  {
    if( !serverClients[i].connected() )
    {
      serverClients[i].stop();
      digitalWrite( pin_clients[i], LOW );
    }
  }

  // Check if there are any new clients
  if( server.hasClient() )
  {
    for( int i=0; i<MAX_CLIENTS; i++ )
    {
      // Find free/disconnected spots
      if (!serverClients[i] || !serverClients[i].connected())
      {            
        serverClients[i] = server.available();
        digitalWrite( pin_clients[i], HIGH );
        continue;
      }
    }
   
    // No free/disconnected spot so reject
    WiFiClient serverClient = server.available();
    serverClient.stop();
  }
  
  // Check UART for data
  if( Serial.available() )
  {
    digitalWrite( pin_rx, HIGH );
    
    size_t len = Serial.available();
    uint8_t sbuf[len];
    Serial.readBytes(sbuf, len);
    
    // Push UART data to all connected clients
    for( int i=0; i<MAX_CLIENTS; i++ )
    {
      if(serverClients[i] && serverClients[i].connected())
      {
        digitalWrite( pin_clients[i], LOW );
        serverClients[i].write(sbuf, len);
        digitalWrite( pin_clients[i], HIGH );
      }
    }

    digitalWrite( pin_rx, LOW );
  }
}

// Functions

void connect_to_wifi() {
  /// Is this really needed ?
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();

  digitalWrite(pin_wifi, LOW);
    
  delay(50);

  // Connect
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  // Wait for wifi connection
  while (WiFi.status() != WL_CONNECTED) 
  {
    // toggle led
    digitalWrite( pin_wifi, digitalRead(pin_wifi) ? LOW : HIGH );
    delay(500);
  }

  // Wifi connection established
  digitalWrite( pin_wifi, HIGH );
}
