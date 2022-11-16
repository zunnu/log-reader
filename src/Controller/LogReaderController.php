<?php
declare(strict_types=1);

namespace LogReader\Controller;

use LogReader\Reader;

class LogReaderController extends AppController
{
    /**
     * initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * Index method
     */
    public function index($date = null)
    {
        $this->viewBuilder()->disableAutoLayout();
        $conditions = [];

        // SEARCH
        if ($this->request->is('get')) {
            if (!empty($this->request->getQueryParams())) {
                $conditions = $this->request->getQueryParams();
            }
        }

        $this->Reader = new Reader($conditions);
        $logs = $this->Reader->read();

        // paginate
        $pagination = [
            'limit' => !empty($conditions['limit']) ? $conditions['limit'] : 100,
            'total' => sizeof($logs),
            'page' => !empty($conditions['page']) ? $conditions['page'] : 1,
        ];
        $pagination['pages'] = ceil($pagination['total'] / $pagination['limit']);
        $pagination['offset'] = ($pagination['page'] * $pagination['limit']) - $pagination['limit'];
        $logs = array_slice($logs, $pagination['offset'], $pagination['limit'], true);

        $this->set(compact('logs', 'pagination'));
        $this->set('files', $this->Reader->getFiles());
        $this->set('types', $this->Reader->getLogTypes());
        $this->set('selectedFiles', !empty($conditions['files']) ? $conditions['files'] : []);
        $this->set('selectedTypes', !empty($conditions['types']) ? $conditions['types'] : []);
    }
}
