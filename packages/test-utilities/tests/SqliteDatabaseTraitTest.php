<?php


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Fusonic\TestUtilities\SqliteDatabaseTrait;
use PHPUnit\Framework\TestCase;

class SqliteDatabaseTraitTest extends TestCase
{
    use SqliteDatabaseTrait;
    private $em;
    
    public function testSqliteDatabase(): void
    {
        $this->createDatabaseFile();
        self::setUpDatabase($this->getEntityManager());
        
        $this->assertFileExists($this->getSqlitePath());
        $this->assertFileExists($this->getSqliteBackupPath());
        $this->assertFileEquals($this->getSqlitePath(), $this->getSqliteBackupPath());
    
        self::tearDownDatabase($this->getEntityManager());
    }
    
    private function createDatabaseFile(): void
    {
        $sqliteFile = $this->getSqlitePath();
        $handle = fopen($sqliteFile, 'wb');
        $data = '{}';
        fwrite($handle, $data);
        fclose($handle);
    }
    
    private function getEntityManager(): EntityManagerInterface
    {
        if (!$this->em) {
            $config = Setup::createAnnotationMetadataConfiguration([]);
            $dbParams = ['driver' => 'pdo_sqlite', 'path' => $this->getSqlitePath()];
    
            $this->em = EntityManager::create($dbParams, $config);
        }
        
        return $this->em;
    }
    
    protected function setUp(): void
    {
        if (file_exists($this->getSqliteBackupPath())) {
            unlink($this->getSqliteBackupPath());
        }
        
        if (file_exists($this->getSqlitePath())) {
            unlink($this->getSqlitePath());
        }
    }
    
    private function getSqliteBackupPath(): string
    {
        return '/tmp/backup.sqlite';
    }
    
    private function getSqlitePath(): string
    {
        return '/tmp/database.sqlite';
    }
}
