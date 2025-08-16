<?php

namespace App\Command;

use App\Doctrine\CustomSchemaTool;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Exception\InvalidOptionUsage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\SqlFormatter\SqlFormatter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:migrations:diff',
    description: 'Generiert eine Migration mit sprechenden Key-Namen gemäß Konvention.'
)]
class MigrationDiffCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire(service: 'doctrine.migrations.dependency_factory')]
        private readonly DependencyFactory $dependencyFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('formatted', null, InputOption::VALUE_NONE, 'Formatierte Ausgabe der Migration')
            ->addOption('filter-expression', null, InputOption::VALUE_REQUIRED, 'Filter für Entities')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if (empty($metadata)) {
            $output->writeln('<info>Keine Mapping-Informationen gefunden.</info>');

            return Command::SUCCESS;
        }

        $formatted = filter_var($input->getOption('formatted'), FILTER_VALIDATE_BOOLEAN);

        if ($formatted) {
            if (!class_exists(SqlFormatter::class)) {
                throw InvalidOptionUsage::new('The "--formatted" option can only be used if the sql formatter is installed. Please run "composer require doctrine/sql-formatter".');
            }
        }

        $schemaTool = new CustomSchemaTool($this->em);
        $toSchema = $schemaTool->getSchemaFromMetadata($metadata);
        $fromSchema = $this->dependencyFactory->getConnection()->createSchemaManager()->introspectSchema();

        $className = $this->dependencyFactory->getClassNameGenerator()->generateClassName('DoctrineMigrations');
        $schemaDiffProvider = $this->dependencyFactory->getSchemaDiffProvider();
        $upSql = $schemaDiffProvider->getSqlDiffToMigrate($fromSchema, $toSchema);
        $downSql = $schemaDiffProvider->getSqlDiffToMigrate($toSchema, $fromSchema);

        $migrationGenerator = $this->dependencyFactory->getMigrationGenerator();
        $upCode = $this->dependencyFactory->getMigrationSqlGenerator()->generate($upSql, $formatted);
        $downCode = $this->dependencyFactory->getMigrationSqlGenerator()->generate($downSql, $formatted);

        if (empty($upCode) && empty($downCode)) {
            $output->writeln('<info>Keine Änderungen erkannt.</info>');

            return Command::SUCCESS;
        }

        $path = $migrationGenerator->generateMigration($className, $upCode, $downCode);
        $output->writeln('<info>Migration erstellt: ' . $path . '</info>');

        return Command::SUCCESS;
    }
}
