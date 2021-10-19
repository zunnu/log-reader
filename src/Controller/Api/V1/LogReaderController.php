<?php
namespace LogReader\Controller\Api\V1;

use LogReader\Controller\Api\V1\AppController;
use Cake\Log\Log;
use LogReader\Reader;

class LogReaderController extends AppController
{
    public function initialize() {
        parent::initialize();
    }

    /**
     * Logs method
     */
    public function logs($date = null) {
        $this->viewBuilder()->setLayout(false);
        $conditions = [];

        // SEARCH
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if(!empty($data['files'])) {
                $files = explode(',', $data['files']);
                $conditions['files'] = array_map('trim', array_filter($files));
            }

            if(!empty($data['types'])) {
                $types = explode(',', $data['types']);
                $conditions['types'] = array_map('trim', array_filter($types));
            }
        }

        $this->Reader = new Reader($conditions);
        $logs = $this->Reader->read();

        $this->set([
            'logs' => $logs,
            '_serialize' => ['logs']
        ]);
    }

    /**
     * Types method
     * Return available log types
     */
    public function types($date = null) {
        $this->viewBuilder()->setLayout(false);
        $this->Reader = new Reader();
        $types = $this->Reader->getLogTypes();

        $this->set([
            'types' => $types,
            '_serialize' => ['types']
        ]);
    }

    /**
     * Files method
     * Return the available log files
     */
    public function files($date = null) {
        $this->viewBuilder()->setLayout(false);
        $this->Reader = new Reader();
        $files = $this->Reader->getFiles();

        $this->set([
            'files' => $files,
            '_serialize' => ['files']
        ]);
    }
}
