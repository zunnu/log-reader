<?php
namespace LogReader\Controller;

use LogReader\Controller\AppController;
use Cake\Log\Log;
use LogReader\Reader;

class LogReaderController extends AppController
{
    public function initialize() {
        parent::initialize();
    }

    /**
     * Index method
     */
    public function index($date = null) {
        $this->viewBuilder()->setLayout(false);
        $conditions = [];

        // SEARCH
        if ($this->request->is('get')) {
            if(!empty($this->request->getQueryParams())) {
                $conditions = $this->request->getQueryParams();
            }
        }

        $this->Reader = new Reader($conditions);
        $this->set('logs', $this->Reader->read());
        $this->set('files', $this->Reader->getFiles());
        $this->set('selectedFiles', !empty($conditions['files']) ? $conditions['files'] : []);
        $this->set('selectedTypes', !empty($conditions['types']) ? $conditions['types'] : []);
    }
}
