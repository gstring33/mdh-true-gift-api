<?php

namespace App\Command;

use App\Entity\GiftList;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.',
    aliases: ['app:add-user'],
    hidden: false
)]
class CreateUserCommand extends Command
{
    private UserPasswordHasherInterface $passwordHasher;
    private ManagerRegistry $doctrine;

    public function __construct(UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine)
    {
        parent::__construct('app:create-user');
        $this->passwordHasher = $passwordHasher;
        $this->doctrine = $doctrine;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $list = (new GiftList())
            ->setUuid(uniqid('', false))
            ->setIsPublished(0);
        $user = (new User())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('john.doe@t-online.de')
            ->setRoles(['ROLE_ADMIN'])
            ->setIsActive(0)
            ->setGiftList($list)
            ->setUuid(uniqid('', false));

        $hashedPassword = $this->passwordHasher->hashPassword($user, '123456');
        $user->setPassword($hashedPassword);

        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();

        return Command::SUCCESS;
    }
}