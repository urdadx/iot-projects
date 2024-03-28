#include <DHT.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WebServer.h>

#define uS_TO_S_FACTOR 1000000ULL  /* Conversion factor for micro seconds to seconds */
#define TIME_TO_SLEEP  5        /* Time ESP32 will go to sleep (in seconds) */
#define DHT22_PIN 13

//store the sleep type
RTC_DATA_ATTR int sleepType = 2;
//Wifi connection variables
const char* ssid = "shinobi";
const char* password = "jack12345";

//Domain name with URL path or IP address with path
const char* readServer = "http://<ipaddress>/<folder_path>/backend.php?query=preferences";
const char* postServer = "http://<ipaddress>/<folder_path>/backend.php?query=sensor";

DHT dht22(DHT22_PIN, DHT22);

void setup() {
  Serial.begin(115200);
  dht22.begin(); // initialize the DHT11 sensor

  analogReadResolution(12);

  //setup esp32 to wake up after
  esp_sleep_enable_timer_wakeup(TIME_TO_SLEEP * uS_TO_S_FACTOR);

}

void connect_to_wifi() {

    //connect to WiFi
  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while(WiFi.status() != WL_CONNECTED) {
    delay(100);
    Serial.print("-");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
 
  Serial.println("It will take 1 seconds before publishing the first reading.");

}

String get_sleep_type(){
  //Check WiFi connection status
    if(WiFi.status()== WL_CONNECTED){
      HTTPClient http;      
      // Your Domain name with URL path or IP address with path
      String serverPath = String(readServer);
      http.begin(serverPath.c_str());  //alt use char host[] = "example.com";
      
      // Send HTTP GET request
      int httpResponseCode = http.GET();
      
      if (httpResponseCode > 0) {
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
        String payload = http.getString();
        Serial.println(payload);
        return payload;
      }
      else {
        Serial.print("Error code: ");
        Serial.println(httpResponseCode);
        return String(sleepType);
      }
      // Free resources
      http.end();
    }
    else {
      Serial.println("WiFi Disconnected");
      return String(sleepType);
    }
}



void loop(){

  connect_to_wifi();
  sleepType = get_sleep_type();

  // DHT11 sensor readings
    float humi  = dht22.readHumidity();
    float tempC = dht22.readTemperature();

    //Check WiFi connection status
    if(WiFi.status()== WL_CONNECTED){
      WiFiClient client;
      HTTPClient http;
      float humi  = dht22.readHumidity();
      float tempC = dht22.readTemperature();
    
      // serverName=URL 
      http.begin(client, postServer);

      // For HTTP request with a content type: application/json:
      http.addHeader("Content-Type", "application/json");
      String jsonString = "{\"temperature\":\"" + String(tempC) +
                    "\",\"humidity\":\"" + String(humi) + "\"}";
      Serial.println(jsonString);
      int httpResponseCode = http.POST(jsonString.c_str());
     
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
        
      // Free resources
      http.end();

      if (sleepType == "Light") {
        Serial.println("Going to a light sleep now");
        Serial.flush(); 
        esp_light_sleep_start();
      } else {
        Serial.println("Going to a deep sleep now");
        Serial.flush(); 
        esp_deep_sleep_start();
      }
    }
    else {
      Serial.println("WiFi Disconnected");
    }
}
