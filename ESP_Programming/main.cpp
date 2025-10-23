//-------------------------------
// Title: main.cpp
//-------------------------------
// Program Detail: runs code using the functions from sensor_function.cpp
//-------------------------------
// Purpose: runs the functions from sensor_functions.cpp
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

void setup() {
  Serial.begin(115200);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  pinMode(TILT_PIN, INPUT_PULLUP);

  Serial.println("WiFi Connected");
  Serial.print("MAC Address: ");
  Serial.println(WiFi.macAddress());
  
  WiFi.begin(ssid, password);
  Serial.print("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("WiFi Connected");

  init_dht();          // start sensor
  select_timezone();   // 🕒 prompt user for zone before first reading
}

void loop() {
  check_switch();   // Detect which switch triggered a node
  delay(200);       // simple debounce
}
