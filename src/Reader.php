<?php
declare(strict_types=1);

namespace LogReader;
use Cake\Core\Exception\Exception;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;

/**
 * Reader class
*/
class Reader {
    /*
    * Class config
    * Options:
    *   Types: Used to filter the results. Available types are in $logTypes
    *   Files: The files where to read the logs from
    */
    protected $config = [
        'types' => [],
        'files' => [],
    ];

    private $logTypes = [
        'info' => 'Info',
        'emergency' => 'Emergency',
        'critical' => 'Critical',
        'alert' => 'Alert',
        'error' => 'Error',
        'warning' => 'Warning',
        'notice' => 'Notice',
        'debug' => 'Debug'
    ];

    function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * Get the date of the files
     * @return array List of different dates of files
     */
    public function getFileDates(): array
    {
        $dates = [];
        $folder = new Folder(LOGS);
        $files = $folder->findRecursive('.*', true);
        
        if(!empty($files)) {
            foreach($files as $file) {
                $file = new File($file);
                $date = date('Y-m-d H:i:s', $file->lastChange());

                if($date) {
                    $dates[] = $date;
                }
            }
        }

        return array_unique($dates);
    }
 
    /**
     * Main reader function
     * The files and types that are parsed need to be set in config
     * @return array List of logs
     */
    public function read(): array
    {
        $date = !empty($this->config['date']) ? $this->config['date'] : null;
        $selectedTypes = !empty($this->config['types']) ? $this->config['types'] : [];
        $selectedFiles = !empty($this->config['files']) ? $this->config['files'] : [];
        $data = $this->getLogFile($selectedFiles);
        $logs = [];

        if($data) {
            // todo move to regex
            $pattern = "/^(?<date>.*)\s(?<type>\w+):.(?<message>.*)/m";

            foreach($data as $dataType => $d) {
                // preg_match_all($pattern, $d, $matches, PREG_SET_ORDER, 0);
                $matches = $this->_parseData($d);
                
                if(!empty($matches)) {
                    foreach($matches as $key => $match) {
                        if(!empty($selectedTypes)) {
                            if(in_array(strtolower($match['type']), $selectedTypes)) {
                                $logs[] = [
                                    'date' => !empty($match['date']) ? $match['date'] : null,
                                    'type' => !empty($match['type']) ? $match['type'] : 'Unknown',
                                    'message' => !empty($match['message']) ? $match['message'] : '',
                                ];
                            }
                        } else {
                            $logs[] = [
                                'date' => !empty($match['date']) ? $match['date'] : null,
                                'type' => !empty($match['type']) ? $match['type'] : 'Unknown',
                                'message' => !empty($match['message']) ? $match['message'] : '',
                            ];
                        }
                    }
                }
            }
        }

        return $logs;
    }

    /**
     * Get logs inside file or files
     * @param  array  $selectedFiles List of files to get the logs from
     * @return array                Content of the selected files
     */
    private function getLogFile($selectedFiles = []): array
    {
        $folder = new Folder(LOGS);
        $files = $folder->findRecursive('.*', true);
        $data = [];
            
        if(empty($selectedFiles)) {
            return [];
            // $selectedFiles = ['debug.log', 'error.log'];
        }

        if(!empty($files)) {
            foreach($files as $file) {
                $file = new File($file);
                $info = $file->info();
                $path = null;

                // check if file is under a folder inside logs folder
                if(strpos($info['dirname'], 'logs/') !== false) {
                    $path = substr($info['dirname'], strrpos($info['dirname'], '/') + 1);
                }

                if(!empty($selectedFiles)) {
                    if(!in_array((!empty($path) ? $path . '/' : '') . $info['basename'], $selectedFiles)) {
                        continue;
                    }
                }

                // $date = date('Y-m-d H:i:s', $file->lastChange());
                
                if(strpos($file->name(), 'cli-debug') !== false) {
                    $type = 'cli-debug';
                } elseif(strpos($file->name(), 'cli-error') !== false) {
                    $type = 'cli-error';
                } elseif(strpos($file->name(), 'error') !== false) {
                    $type = 'error';
                } elseif(strpos($file->name(), 'debug') !== false) {
                    $type = 'debug';
                } else {
                    $type = 'unknown';
                }
                
                if(!isset($data[$type])) {
                    $data[$type] = '';
                }

                $data[$type] .= file_get_contents($file->pwd());
            }

            return $data;
        }

        return [];   
    }

    /**
     * Get list of log files inside the logs folder
     * @return array List of files
     */
    public function getFiles(): array
    {
        $filesList = [];
        $folder = new Folder(LOGS);
        $files = $folder->findRecursive('.*', true);
        
        if(!empty($files)) {
            foreach($files as $file) {
                $file = new File($file);
                $date = date('Y-m-d H:i:s', $file->lastChange());
                $info = $file->info();
                $path = null;

                // check if file is under a folder inside logs folder
                if(strpos($info['dirname'], 'logs/') !== false) {
                    $path = substr($info['dirname'], strrpos($info['dirname'], '/') + 1);
                }

                if($date) {
                    $filesList[] = [
                        'name' => (!empty($path) ? $path . '/' : '') . $info['basename'],
                        'date' => $date,
                        'type' => strpos($file->name(), 'cli-debug') !== false || strpos($file->name(), 'cli-error') !== false ? 'cli' : 'app',
                    ];
                }
            }
        }

        return $filesList;
    }

    /**
     * Parse log file content
     * Move this to use regex later
     * @param  array $data  Content of log file
     * @return array       Parsed data with type, date and content
     */
    private function _parseData($data): array
    {
        $data = preg_split("/\r\n|\n|\r/", $data);
        $buildData = [];
        $tmp = '';
        $first = true;
        $last = false;

        if(!empty($data)) {
            foreach($data as $k => $d) {
                $d = explode(' ', $d);
                // dd($data);

                if(isset($d[0]) && isset($d[1])) {
                    $date = $d[0] . ' ' . $d[1];

                    if(\DateTime::createFromFormat('Y-m-d H:i:s', $date) !== false) {
                        $type = str_replace(':', '', $d[2]);
                        unset($d[0]);
                        unset($d[1]);
                        unset($d[2]);
                        $newLine = true;
                    } else {
                        // not a date
                        $newLine = false;
                    }
                } else {
                    $newLine = false;
                }

                if($k == 15) {
                    // dd($newLine);
                }

                $message = implode(' ', $d);

                if($newLine) {
                    // new line
                    $buildData[] = [
                        'date' => $date,
                        'type' => $type,
                        'message' => $message,
                    ];
                } else {
                    // get the last key in php 7.3 > we can use array_key_last for this
                    $key = key(array_slice($buildData, -1, 1, true));
                    $buildData[$key]['message'] .= ' ' . $message;
                }
            }
        }

        return $buildData;
    }

    /**
     * Return available log file types
     * @return array
     */
    public function getLogTypes(): array
    {
        return $this->logTypes;
    }
}