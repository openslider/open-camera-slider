<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PiPHP\GPIO\GPIO;
use App\Service\MotorizedSlider;

class ResetHeadCommand extends Command
{
    /** @var GPIO */
    private $motorizedSlider;

    public function __construct(
        $name = null,
        MotorizedSlider $motorizedSlider
    ){
        $this->motorizedSlider = $motorizedSlider;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('app:reset-head')
            ->setDescription('Moves the head either left or right')
            ->setHelp('This command allows you to move the camera head left or right');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->motorizedSlider->turnWorkingLedOn();

        $output->writeln("Moving slider to the " . $this->motorizedSlider->getHorizontalDirName());

        while (!$this->motorizedSlider->isEmergencyButtonPressed()) {
            $this->motorizedSlider->moveSideways(10);
        }

        $this->motorizedSlider->turnWorkingLedOff();

    }
}


