<?php

namespace App\Command;

use App\Service\MotorizedSlider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TimeLapseCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:time-lapse')
            ->setDescription('Starts a time lapse sequency')
            ->setHelp('This command allows you to trigger the time lapse sequence')
            ->addOption('horizontal-length', 'l', InputOption::VALUE_REQUIRED, 'Length in mm of the rail movement')
            ->addOption('horizontal-direction', 's', InputOption::VALUE_REQUIRED, 'Horizontal direction, left or right')
            ->addOption('orbital-length', 'o', InputOption::VALUE_REQUIRED, 'Length in degrees of the orbital movement')
            ->addOption('total-time', 't', InputOption::VALUE_REQUIRED, 'Total time in minutes')
            ->addOption('interval', 'i', InputOption::VALUE_REQUIRED, 'Interval between photos in seconds')
            ->addOption('dry-run', 'd', InputOption::VALUE_REQUIRED, 'Indicates if its a test')
        ;
    }

    /** @var MotorizedSlider */
    private $motorizedSlider;

    public function __construct(
        $name = null,
        MotorizedSlider $motorizedSlider
    ){
        $this->motorizedSlider = $motorizedSlider;
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $horizontalLength = $input->getOption('horizontal-length');
        $orbitalLength = $input->getOption('orbital-length');
        $totalTime = $input->getOption('total-time');
        $interval = $input->getOption('interval');
        $test = $input->getOption('dry-run');
        $totalPhotos = $totalTime/$interval;
        $horizontalInterval = $horizontalLength / $totalPhotos;
        $horizontalMovementInterval = $horizontalLength / $totalPhotos;
        $orbitalMovementInterval = $orbitalLength / $totalPhotos;
        $output->writeln([
            "$totalPhotos pictures will be taken",
            "$totalTime secs",
            "Horizontal dir: " . $this->motorizedSlider->getHorizontalDirName(),
            "Orbital dir: " . $this->motorizedSlider->getOrbitalDir(),
            "1 pic every $interval secs",
            "Head will move $horizontalInterval mm every $interval secs",
            "Camera will move $horizontalMovementInterval mm horizontally every $interval secs",
            "Camera will move $orbitalMovementInterval degrees every $interval secs"
        ]);
        $picsTaken = 0;

        $this->motorizedSlider->turnWorkingLedOn();

        while (!$this->motorizedSlider->isEmergencyButtonPressed() && $picsTaken < $totalPhotos) {
            $picsTaken++;
            if (!$test) {
                $output->writeln("Taking picture $picsTaken of $totalPhotos");
                $this->motorizedSlider->takePicture();
            }
            $output->writeln("Moving $horizontalInterval mm to the " . $this->motorizedSlider->getHorizontalDirName());
            $this->motorizedSlider->moveSideways($horizontalInterval);
            $output->writeln("Rotating $orbitalMovementInterval degrees to the " . $this->motorizedSlider->getOrbitalDirName());
            $this->motorizedSlider->rotate($orbitalMovementInterval);
            $output->writeln("Waiting $interval secs");
            if (!$test) {
                usleep($interval * 1000000);
            }
        }

        $this->motorizedSlider->turnWorkingLedOff();

    }
}


