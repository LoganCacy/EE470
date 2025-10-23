#ifndef SENSOR_FUNCTIONS_H
#define SENSOR_FUNCTIONS_H

#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <DHTesp.h>     // ✅ using DHTesp library

// ===== Pin Definitions =====
#define DHT_PIN D5          // DHT sensor data pin
#define BUTTON_PIN D2       // Pushbutton switch for Node_1
#define TILT_PIN D3         // Tilt switch for Node_2

// ===== WiFi credentials =====
extern const char* ssid;
extern const char* password;

// ===== Function Prototypes =====
void check_switch();
void read_time();
void read_sensor();
void transmit();
void check_error();
void init_dht();
void select_timezone();

#endif
