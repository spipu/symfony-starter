<?php

declare(strict_types=1);

namespace App\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\CoreBundle\Fixture\FixtureInterface;
use Spipu\CoreBundle\Service\Environment;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUsersFixture implements FixtureInterface
{
    private EntityManagerInterface $entityManager;

    private UserPasswordHasherInterface $hasher;

    private ModuleConfigurationInterface $moduleConfiguration;

    private UserRepository $userRepository;

    private Environment $environment;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
        ModuleConfigurationInterface $moduleConfiguration,
        UserRepository $userRepository,
        Environment $environment
    ) {
        $this->entityManager = $entityManager;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
        $this->environment = $environment;
    }

    public function getOrder(): int
    {
        return 20;
    }

    public function getCode(): string
    {
        return 'app-admin-user';
    }

    public function load(OutputInterface $output): void
    {
        $output->writeln("Add Admin Users");
        $data = $this->getData();
        foreach ($data as $row) {
            $object = $this->findObject($row['username']);
            if ($object) {
                continue;
            }

            $object = $this->moduleConfiguration->getNewEntity();
            $object
                ->setUsername($row['username'])
                ->setEmail($row['email'])
                ->setPassword($this->hasher->hashPassword($object, $this->getPassword($row['username'])))
                ->setFirstName($row['firstname'])
                ->setLastName($row['lastname'])
                ->setRoles($row['roles'])
                ->setActive($row['active']);

            $this->entityManager->persist($object);
            $this->entityManager->flush();
        }

        if ($this->environment->isProduction()) {
            $output->writeln("Disable Default Admin User");
            $object = $this->findObject('admin');
            if ($object) {
                $object->setActive(false);
                $this->entityManager->persist($object);
                $this->entityManager->flush();
            }
        }
    }

    public function remove(OutputInterface $output): void
    {
        $output->writeln("Remove Users");
        $data = $this->getData();
        foreach ($data as $row) {
            $output->writeln(" - " . $row['username']);
            $object = $this->findObject($row['username']);
            if (!$object) {
                $output->writeln("    => Already removed");
                continue;
            }

            $this->entityManager->remove($object);
            $this->entityManager->flush();
        }
    }

    private function findObject(string $identifier): ?UserInterface
    {
        /** @var UserInterface $object */
        $object = $this->userRepository->findOneBy(['username' => $identifier]);

        return $object;
    }

    protected function getPassword(string $username = ''): string
    {
        if (!$this->environment->isProduction()) {
            return 'password';
        }

        $random = (string) (time() * 10000 + random_int(0, 10000));
        return substr(md5($username . $random), 0, 16);
    }

    protected function getData(): array
    {
        return [
            [
                'username'  => 'spipu',
                'email'     => 'spipu@spipu.net',
                'firstname' => 'Laurent',
                'lastname'  => 'MINGUET',
                'roles'     => ['ROLE_SUPER_ADMIN'],
                'active'    => true,
            ],
        ];
    }
}
