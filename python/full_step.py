from time import sleep
import RPi.GPIO as GPIO

DIR = 13   # Direction GPIO Pin
STEP = 19  # Step GPIO Pin
CW = 1     # Clockwise Rotation
CCW = 0    # Counterclockwise Rotation
MICROSTEP = 1
SPR = 200 * MICROSTEP  # Steps per Revolution (360 / 7.5)

step_count = SPR
delay = 0.0020 / MICROSTEP

MODE = (21, 25, 20)   # Microstep Resolution GPIO Pins
RESOLUTION = {'1': (0, 0, 0),
              '2': (1, 0, 0),
              '4': (0, 1, 0),
              '8': (1, 1, 0),
              '16': (0, 0, 1),
              '32': (1, 0, 1)}


GPIO.setmode(GPIO.BCM)
GPIO.setup(MODE, GPIO.OUT)
GPIO.output(MODE, RESOLUTION['1'])
GPIO.setup(DIR, GPIO.OUT)
GPIO.setup(STEP, GPIO.OUT)
GPIO.output(DIR, CW)

for x in range(step_count):
    GPIO.output(STEP, GPIO.HIGH)
    sleep(delay)
    GPIO.output(STEP, GPIO.LOW)
    sleep(delay)

GPIO.output(DIR, CCW)
for x in range(step_count/2):
    GPIO.output(STEP, GPIO.HIGH)
    sleep(delay)
    GPIO.output(STEP, GPIO.LOW)
    sleep(delay)

GPIO.output(DIR, CW)
for x in range(step_count/4):
    GPIO.output(STEP, GPIO.HIGH)
    sleep(delay)
    GPIO.output(STEP, GPIO.LOW)
    sleep(delay)

GPIO.output(DIR, CCW)
for x in range(step_count):
    GPIO.output(STEP, GPIO.HIGH)
    sleep(delay)
    GPIO.output(STEP, GPIO.LOW)
    sleep(delay)

GPIO.cleanup()