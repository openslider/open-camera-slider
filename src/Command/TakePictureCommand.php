<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PiPHP\GPIO\GPIO;
use App\Service\MotorizedSlider;

class TakePictureCommand extends Command
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
            ->setName('app:take-picture')
            ->setDescription('Takes a picture')
            ->setHelp('This command takes a picture');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Taking picture");
        $this->motorizedSlider->takePicture();


    }
}


