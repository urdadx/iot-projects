#include <DHT.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include "SPIFFS.h"

#define uS_TO_S_FACTOR 1000000ULL  /* Conversion factor for microseconds to seconds */
#define TIME_TO_SLEEP  5            /* Time ESP32 will go to sleep (in seconds) */
#define DHT22_PIN 13

// Store the sleep type
RTC_DATA_ATTR int sleepType = 2;
RTC_DATA_ATTR int numReadings = 0;

// WiFi connection variables
const char* ssid = "shinobi";
const char* password = "jack12345";

// Domain name with URL path or IP address with path
const char* postServer = "http://192.168.93.95/lab_4/backend.php?query=spiffs";
const char* readServer = "http://192.168.93.95/lab_4/backend.php?query=preferences";

// Variables for DHT22 readings
float humi;
float tempC;

DHT dht22(DHT22_PIN, DHT22);

void setup() {
  Serial.begin(115200);
  connect_to_wifi();
  dht22.begin(); // Initialize the DHT11 sensor
  analogReadResolution(12);

  // Setup ESP32 to wake up after
  esp_sleep_enable_timer_wakeup(TIME_TO_SLEEP * uS_TO_S_FACTOR);

  if (!SPIFFS.begin(true)) {
    Serial.println("An Error has occurred while mounting SPIFFS");
    return;
  }
}

void connect_to_wifi() {
  // Connect to WiFi
  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(100);
    Serial.print("-");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());

  Serial.println("It will take 1 second before publishing the first reading.");
}

String get_sleep_type() {
  // Check WiFi connection status
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    // Your Domain name with URL path or IP address with path
    String serverPath = String(readServer);
    http.begin(serverPath.c_str());  // Alt use char host[] = "example.com";

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

void post_to_database(String result) {
  // Check WiFi connection status
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;
    // readServer=URL
    http.begin(client, postServer);

    // For HTTP request with a content type: application/json:
    http.addHeader("Content-Type", "application/json");

    String jsonString = result;
    // how to send multiple records. collect in a list and try to send at once
    Serial.println("Array changed to JSON :");
    Serial.println(jsonString.c_str());
    int httpResponseCode = http.POST(jsonString.c_str());

    Serial.print("HTTP Response code: ");
    Serial.println(httpResponseCode);
        // Check for response from the server
    if (httpResponseCode > 0) {
      String response = http.getString(); // Get response from server
      Serial.println("Response from server:");
      Serial.println(response); // Print response to serial monitor
    } else {
      Serial.println("Error sending POST request");
    }


    // Free resources
    http.end();
  }
  else {
    Serial.println("WiFi Disconnected");
  }
}

String get_sensor_readings_array() {
  String jsonArray = "["; // Start of JSON array

  File file = SPIFFS.open("/readings.txt", FILE_READ); // Open file for reading
  if (!file) {
    Serial.println("Failed to open file for reading");
    return jsonArray; // Return empty array if file opening fails
  }

  // Read each line from the file and add it to the JSON array
  boolean isFirstReading = true;
  while (file.available()) {
    String reading = file.readStringUntil('\n'); // Read one line
    reading.trim(); // Remove leading and trailing whitespaces
    if (!reading.isEmpty()) { // Check if the line is not empty
      if (!isFirstReading) {
        jsonArray += ","; // Add comma if it's not the first reading
      }
      jsonArray += reading; // Add reading to the JSON array
      isFirstReading = false;
    }
  }
  file.close(); // Close the file

  jsonArray += "]"; // End of JSON array

  return jsonArray;
}


String get_sensor_reading() {
  humi  = dht22.readHumidity();
  tempC = dht22.readTemperature();
  String jsonString = "{\"temperature\":\"" + String(tempC) +
                    "\",\"humidity\":\"" + String(humi) + "\"}";

  return jsonString;
}

void loop() {

  if (WiFi.status() != WL_CONNECTED) {
    connect_to_wifi();
    }
  sleepType = get_sleep_type().toInt();

  // DHT11 sensor readings
  float humi  = dht22.readHumidity();
  float tempC = dht22.readTemperature();

  String reading = get_sensor_reading();

  // Write to file
  Serial.println("Writing to temperature reading");
  File file = SPIFFS.open("/readings.txt", FILE_APPEND);
  if (!file) {
    Serial.println("Failed to open file for writing");
    return;
  }
  Serial.println(reading);
  file.println(reading);
  numReadings++;
  file.close();
  
  if (numReadings == 4) {

     String res = get_sensor_readings_array();
     Serial.println(res);
     post_to_database(res);

     SPIFFS.remove("/readings.txt");
     numReadings = 0;
    
    }

  // After the number of records gets to some value send to database
  if (sleepType == 1) {
    Serial.println("Going to a light sleep now");
    Serial.flush();
    esp_light_sleep_start();
  }
  else {
    Serial.println("Going to a deep sleep now");
    Serial.flush();
    esp_deep_sleep_start();
  }
}