//-------------------------------
// Title: sensor_functions.cpp
//-------------------------------
// Program Detail: defines the specific programming of functions from sensor_functions.h
//-------------------------------
// Purpose: defines the specific functions for the program that will read and transmit sensor data.
// Inputs: sensor data (D5), node 1 (D2), node 2 (D3)
// Outputs: data into a data base
// Date: 10/22/2025
// Compiler: VS Code
// Author: Logan Cacy
// Versions:
//    V1 – Initial Version

//-------------------------------
// File Dependencies:
//-------------------------------

#include "sensor_functions.h"

//-------------------------------
// Main Program
//-------------------------------

// ===== Globals =====
const char* ssid     = "Cy's S24 Ultra";
const char* password = "pklq795@";
const char* serverName = "https://www.logancacy.com/db_insert.php?";  // ✅ point to db_insert.php, not ESP_display.php
String selectedZone = "America/Los_Angeles";   // default (Pacific Time)
String currentTime = "";
float tempValue = 0.0;
float humidValue = 0.0;
int node_id = 0;

DHTesp dht;   // ✅ use DHTesp object

// ===== 1️⃣ Detect which switch is pressed =====
void check_switch() {
  if (digitalRead(BUTTON_PIN) == LOW) {   // Node_1 trigger
    node_id = 1;
    read_sensor();
  } 
  else if (digitalRead(TILT_PIN) == LOW) { // Node_2 trigger
    node_id = 2;
    read_sensor();
  }
}

// ===== 2️⃣ Get current time from API =====
void read_time() {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClientSecure client;   // ✅ use WiFiClientSecure for HTTPS
    client.setInsecure();      // ✅ disable certificate check (safe for classroom/testing)
    HTTPClient http;

    String apiURL = "https://timeapi.io/api/Time/current/zone?timeZone=" + selectedZone;
    http.begin(client, apiURL);
    int code = http.GET();

    if (code == 200) {
      String payload = http.getString();
      DynamicJsonDocument doc(1024);
      deserializeJson(doc, payload);
      currentTime = doc["dateTime"].as<String>();
      Serial.println("🕒 Time received: " + currentTime);
    } else {
      Serial.println("❌ Failed to fetch time: " + String(code));
    }

    http.end();
  } else {
    Serial.println("WiFi not connected");
  }
}

// ===== 3️⃣ Read Temperature & Humidity (DHTesp) =====
void read_sensor() {
  TempAndHumidity data = dht.getTempAndHumidity();

  if (isnan(data.temperature) || isnan(data.humidity)) {
    Serial.println("❌ Failed to read from DHT sensor!");
    return;
  }

  tempValue = data.temperature;
  humidValue = data.humidity;

  Serial.print("🌡️ Temp: ");
  Serial.print(tempValue);
  Serial.print(" °C  |  💧 Humidity: ");
  Serial.print(humidValue);
  Serial.println(" %");

  read_time();
  transmit();
}

// ===== 4️⃣ Transmit Data =====
void transmit() {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClientSecure client;      // ✅ Secure client for HTTPS
    client.setInsecure();         // ✅ Skip certificate check
    HTTPClient http;

    http.begin(client, serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String nodeName = (node_id == 1) ? "node_1" : "node_2";
    String postData = "nodeId=" + nodeName +
                      "&nodeTemp=" + String(tempValue, 1) +
                      "&nodeHumidity=" + String(humidValue, 1) +
                      "&timeReceived=" + currentTime;

    Serial.println("➡️ Sending to: " + String(serverName));
    Serial.println("Data: " + postData);

    int httpCode = http.POST(postData);
    Serial.println("POST Response: " + String(httpCode));

    if (httpCode > 0) {
      String response = http.getString();
      Serial.println("Server Response: " + response);
    } else {
      Serial.println("❌ HTTP POST failed");
    }

    http.end();
  } else {
    Serial.println("WiFi not connected");
  }
}


void init_dht() {
  dht.setup(DHT_PIN, DHTesp::DHT11);   // or DHT22 if you're using that model
  Serial.println("✅ DHT sensor initialized!");
}

void select_timezone() {
  Serial.println("\n--- Select Your Time Zone ---");
  Serial.println("1) Eastern  (America/New_York)");
  Serial.println("2) Central  (America/Chicago)");
  Serial.println("3) Mountain (America/Denver)");
  Serial.println("4) Pacific  (America/Los_Angeles)");
  Serial.println("5) Alaska   (America/Anchorage)");
  Serial.println("6) Hawaii   (Pacific/Honolulu)");
  Serial.println("7) Atlantic (America/Puerto_Rico)");
  Serial.print("Enter the number of your time zone: ");

  // wait until user types something
  while (!Serial.available()) { delay(100); }

  char choice = Serial.read();

  switch (choice) {
    case '1': selectedZone = "America/New_York";    break;
    case '2': selectedZone = "America/Chicago";     break;
    case '3': selectedZone = "America/Denver";      break;
    case '4': selectedZone = "America/Los_Angeles"; break;
    case '5': selectedZone = "America/Anchorage";   break;
    case '6': selectedZone = "Pacific/Honolulu";    break;
    case '7': selectedZone = "America/Puerto_Rico"; break;
    default:  selectedZone = "America/Los_Angeles"; break;  // default PT
  }

  Serial.print("✅ Time zone selected: ");
  Serial.println(selectedZone);
}
