<?php

namespace App\Service;

use PiPHP\GPIO\GPIO;

class MotorizedSlider
{
    const GPIO_PIN_HORIZONTAL_MOTOR_STEP = 13;
    const GPIO_PIN_HORIZONTAL_MOTOR_DIR = 19;
    const GPIO_PIN_HORIZONTAL_MODE_PIN1 = 20;
    const GPIO_PIN_HORIZONTAL_MODE_PIN2 = 21;
    const GPIO_PIN_HORIZONTAL_MODE_PIN3 = 25;
    const HORIZONTAL_MOTOR_MICRO_STEPS = 32;

    const GPIO_PIN_ORBITAL_MOTOR_STEP = 22;
    const GPIO_PIN_ORBITAL_MOTOR_DIR = 23;
    const GPIO_PIN_ORBITAL_MODE_PIN1 = 20;
    const GPIO_PIN_ORBITAL_MODE_PIN2 = 21;
    const GPIO_PIN_ORBITAL_MODE_PIN3 = 25;
    const ORBITAL_MOTOR_MICRO_STEPS = 32;

    const GPIO_PIN_DIR_SWITCH_HORIZONTAL = 16;
    const GPIO_PIN_DIR_SWITCH_ORBITAL = 26;
    const GPIO_PIN_STOPPER_EMERGENCY = 12;
    const GPIO_PIN_CAMERA_SWITCH = 24;
    const GPIO_PIN_LED_WORKING = 27;
    const ENGINE_MICROSECONDS_DELAY = 500;
    const ENGINE_MICRO_STEPS_RESOLUTIONS = [
        '1' => [0, 0, 0],
        '2' => [1, 0, 0],
        '4' => [0, 1, 0],
        '8' => [1, 1, 0],
        '16' => [0, 0, 1],
        '32' => [1, 0, 1]
    ];
    const HORIZONTAL_STEPS_PER_MM = 5;
    const ORBITAL_DEGREES_PER_STEP = 1.8;
    const HORIZONTAL_DIR_NAMES = ['right', 'left'];
    const ORBITAL_DIR_NAMES = ['left', 'right'];
    const CAMERA_MICROSECONDS_DELAY = 1000000;

    /** @var GPIO */
    private $gpio;

    public function __construct()
    {
        $this->gpio = new GPIO();
    }

    public function turnWorkingLedOff()
    {
        $this->setPin(self::GPIO_PIN_LED_WORKING, 0);
        return $this;
    }

    public function turnWorkingLedOn()
    {
        $this->setPin(self::GPIO_PIN_LED_WORKING, 1);
        return $this;
    }

    public function isEmergencyButtonPressed()
    {
        return $this->getPin(self::GPIO_PIN_STOPPER_EMERGENCY);
    }

    public function getHorizontalDir(): bool
    {
        return $this->getPin(self::GPIO_PIN_DIR_SWITCH_HORIZONTAL);
    }

    public function getHorizontalDirName(): string
    {
        return self::HORIZONTAL_DIR_NAMES[$this->getPin(self::GPIO_PIN_DIR_SWITCH_HORIZONTAL)];
    }

    public function getOrbitalDirName(): string
    {
        return self::ORBITAL_DIR_NAMES[$this->getHorizontalDir()];
    }

    public function takePicture(): self
    {
        $this->setPin(self::GPIO_PIN_CAMERA_SWITCH, true);
        usleep(self::CAMERA_MICROSECONDS_DELAY);
        $this->setPin(self::GPIO_PIN_CAMERA_SWITCH, false);
        return $this;
    }

    /**
     * @return int
     */
    public function getOrbitalDir()
    {
        return $this->getPin(self::GPIO_PIN_DIR_SWITCH_HORIZONTAL);
    }

    private function activateHorizontalMotor(int $steps): self
    {
        $this
            ->setPin(
                self::GPIO_PIN_HORIZONTAL_MOTOR_DIR,
                $this->getHorizontalDir()
            )
            ->setPin(
                self::GPIO_PIN_HORIZONTAL_MODE_PIN1,
                self::ENGINE_MICRO_STEPS_RESOLUTIONS[self::HORIZONTAL_MOTOR_MICRO_STEPS][0]
            )
            ->setPin(
                self::GPIO_PIN_HORIZONTAL_MODE_PIN2,
                self::ENGINE_MICRO_STEPS_RESOLUTIONS[self::HORIZONTAL_MOTOR_MICRO_STEPS][1]
            )
            ->setPin(
                self::GPIO_PIN_HORIZONTAL_MODE_PIN2,
                self::ENGINE_MICRO_STEPS_RESOLUTIONS[self::HORIZONTAL_MOTOR_MICRO_STEPS][2]
            )
            ;
        for($step = 1; $step <= ($steps * self::HORIZONTAL_MOTOR_MICRO_STEPS); $step++) {
            $this->stepMotor(self::GPIO_PIN_HORIZONTAL_MOTOR_STEP, self::HORIZONTAL_MOTOR_MICRO_STEPS);
        }
        return $this;
    }

    private function activateOrbitalMotor(int $steps): self
    {
        $this
            ->setPin(
                self::GPIO_PIN_ORBITAL_MOTOR_DIR,
                $this->getOrbitalDir()
            )
            ->setPin(
                self::GPIO_PIN_ORBITAL_MODE_PIN1,
                self::ENGINE_MICRO_STEPS_RESOLUTIONS[self::ORBITAL_MOTOR_MICRO_STEPS][0]
            )
            ->setPin(
                self::GPIO_PIN_ORBITAL_MODE_PIN2,
                self::ENGINE_MICRO_STEPS_RESOLUTIONS[self::ORBITAL_MOTOR_MICRO_STEPS][1]
            )
            ->setPin(
                self::GPIO_PIN_ORBITAL_MODE_PIN3,
                self::ENGINE_MICRO_STEPS_RESOLUTIONS[self::ORBITAL_MOTOR_MICRO_STEPS][2]
            )
        ;
        echo "Steps $steps\n";
        for($step = 1; $step <= ($steps * self::ORBITAL_MOTOR_MICRO_STEPS); $step++) {
            $this->stepMotor(self::GPIO_PIN_ORBITAL_MOTOR_STEP, self::ORBITAL_MOTOR_MICRO_STEPS);
        }
        return $this;
    }

    /**
     * @param int $motorPin
     * @param int $resolution
     * @return MotorizedSlider
     */
    private function stepMotor(int $motorPin, int $resolution = 1): self
    {
        $this->setPin($motorPin, 1);
        usleep(self::ENGINE_MICROSECONDS_DELAY / $resolution);
        $this->setPin($motorPin, 0);
        usleep(self::ENGINE_MICROSECONDS_DELAY / $resolution);
        return $this;
    }

    /**
     * @param int $pinNumber
     * @param bool $value
     * @return MotorizedSlider
     */
    private function setPin(int $pinNumber, bool $value): self
    {
        $this->gpio->getOutputPin($pinNumber)->setValue($value);
        return $this;
    }

    /**
     * @param int $pinNumber
     * @return bool
     */
    private function getPin(int $pinNumber): bool
    {
        return $this->gpio->getOutputPin($pinNumber)->getValue();
    }

    public function moveSideways(float $scroll)
    {
        $steps = intval($scroll * self::HORIZONTAL_STEPS_PER_MM);
        return $this->activateHorizontalMotor($steps);
    }

    public function rotate(float $degrees)
    {
        $steps = intval($degrees / self::ORBITAL_DEGREES_PER_STEP);
        return $this->activateOrbitalMotor($steps);
    }

    public function setHorizontalDirection(string $horizontalDirection)
    {
        $direction = $horizontalDirection == 'left' ? 1 : 0;
        $this->setPin(self::GPIO_PIN_HORIZONTAL_MOTOR_DIR, $direction);
    }
}