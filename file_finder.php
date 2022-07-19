<?php

/** @noinspection UseStaticReturnTypeInsteadOfSelfInspection */

/*
  Завдання: необхідно написати клас для пошуку файлів і директорій.
  Клас має реалізовувати інтерфейс FileFinderInterface.
  На виході клас має повертати масив строк - повних шляхів до папок/файлів, які відповідають заданим умовам.
  Нижче в файлі є приклади використання класу.

  Можна використовувати тільки вбудований функціонал PHP.

  Завдання розраховане на 1-2 години роботи, просимо не витрачати більше.
  Краще додайте до реалізації список доробок/покращень, які ви б зробили в коді, якби працювали б над ним далі.
  */

interface FileFinderInterface
{

    /**
     * Search in directory $directory.
     * If called multiple times, the result must include paths from all provided directories.
     */
    public function inDir(string $directory): self;

    /** Filter: only files, ignore directories */
    public function onlyFiles(): self;

    /** Filter: only directories, ignore files */
    public function onlyDirectories(): self;

    /**
     * Filter by regular expression on full path.
     * If called multiple times, the result must include paths that match at least one of the provided expressions.
     */
    public function match(string $regularExpression): self;


    /**
     * Returns array of all found files/directories (full path)
     * @return string[]
     */
    public function find(): array;
}


/** @noinspection PhpHierarchyChecksInspection */
class FileFinderImplementation implements FileFinderInterface
{
    protected $dirsArr = [];
    protected $mode = [];
    protected $matchMode = [];
    protected $inDir = false;

    public function inDir(string $directory): self
    {
        
        if (is_dir($directory)) {
            $this->inDir = true;
            $this->dirsArr[$directory] = array_diff(scandir($directory), ['..', '.']);
            foreach ($this->mode as $mode) {
                if ($mode === 'onlyFiles') {
                    $this->onlyFiles();
                }
                if ($mode === 'onlyDirectories') {
                    $this->onlyDirectories();
                }
            }

            foreach ($this->matchMode as $regEx) {
                $this->match($regEx);
            }

            return $this;
        } else {
            try {
                throw new \Exception("The directory: $directory does not exist");
            } catch (\Exception $e) {
                exit($e->getMessage());
            }
        }
    }

    public function onlyFiles(): self
    {
        $this->mode[] = 'onlyFiles';
        $resArr = [];
        foreach ($this->dirsArr as $path => $value) {
            foreach (glob($path . '*') as $file) {
                if (is_file($file)) {
                    $resArr[$path][] = $file;
                }
            }
        }
        $this->dirsArr = $resArr;
        return $this;
    }

    public function onlyDirectories(): self
    {
        $this->mode[] = 'onlyDirectories';
        $resArr = [];
        foreach ($this->dirsArr as $path => $value) {
            foreach (glob($path . '*') as $file) {
                if (is_dir($file)) {
                    $resArr[$path][] = $file;
                }
            }
        }
        $this->dirsArr = $resArr;
        return $this;
    }

    public function match(string $regularExpression): self
    {
        $this->matchMode[] = $regularExpression;

        $resArr = [];
        foreach ($this->dirsArr as $path => $value) {
            foreach (glob($path . '*') as $file) {
                if (preg_match($regularExpression, $file, $matches)) {
                    $resArr[$path][] = $file;
                }
            }
        }
        $this->dirsArr = $resArr;

        return $this;
    }

    public function find(): array
    {
        $resArr = [];
        foreach ($this->dirsArr as $path => $value) {
            foreach ($value as $file) {
                $resArr[] = $file;
            }
        }

        if (!$this->inDir) throw new \Exception('You need to specify the directory!');
        return $resArr;
    }
}

$finder = new FileFinderImplementation();

$finder
  ->onlyFiles()
  ->inDir('/etc/')
  ->inDir('/var/log/')
  ->match('/.*\.conf$/');
foreach ($finder->find() as $file) {
  print $file . "\n";
}
print "\n\n";


// # search for all files in /tmp
$finder = (new FileFinderImplementation())
  ->onlyFiles()
  ->inDir('/tmp');
foreach ($finder->find() as $file) {
  print $file . "\n";
}
print "\n\n";


// # search for .doc files in /tmp
$finder = (new FileFinderImplementation())
  ->match('/.*\.doc$/')
  ->onlyFiles()
  ->inDir('/tmp');
foreach ($finder->find() as $file) {
  print $file . "\n";
}
print "\n\n";


// # list all directories in /var
$finder = (new FileFinderImplementation())
  ->onlyDirectories()
  ->inDir('/var/log/');
foreach ($finder->find() as $file) {
  print $file . "\n";
}
print "\n\n";


# should throw an exception if no directories were provided
try {
  $files = (new FileFinderImplementation())
    ->onlyFiles()
    ->match('/.*\.ini$/')
    ->find(); # -> exception
  print "When no dir were provided: exception was not thrown, something does not work correctly\n";
} catch (\Exception $exception) {
  print "When no dir were provided: exception was thrown with message \"" . $exception->getMessage() . "\"\n";
}




