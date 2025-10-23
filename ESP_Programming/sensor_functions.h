//-------------------------------
// Title: sensor_functions.h
//-------------------------------
// Program Detail: defines the functions for sensor_functions.cpp
//-------------------------------
// Purpose: defines the functions for the program that will read and transmit sensor data.
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

#ifndef SENSOR_FUNCTIONS_H
#define SENSOR_FUNCTIONS_H

#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>
#include <DHTesp.h>     //using DHTesp library

//-------------------------------
// Main Program
//-------------------------------

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
void init_dht();
void select_timezone();

#endif
