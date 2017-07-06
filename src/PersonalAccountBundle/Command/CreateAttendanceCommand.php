<?php
namespace PersonalAccountBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CreateAttendanceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:create-attendance')
            ->setDescription('Creates new attendances')
            ->setHelp('This command allows you to create attendances for all groups and teachers for a specific date...')
            ->addArgument('date', InputArgument::REQUIRED, 'date (default: current date)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $attendanceManager = $this->getContainer()->get('app.attendance_manager');
        $date = $input->getArgument('date');
        $attendanceManager->create($date);
        $output->writeln('Attendances for '.$input->getArgument('date').'were successfully generated!');
    }
}