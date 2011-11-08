<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */
class viewProjectsAction extends sfAction {

    private $projectService;

    public function getProjectService() {
        if (is_null($this->projectService)) {
            $this->projectService = new ProjectService();
            $this->projectService->setProjectDao(new ProjectDao());
        }
        return $this->projectService;
    }

    public function setForm(sfForm $form) {
        if (is_null($this->form)) {
            $this->form = $form;
        }
    }

    /**
     *
     * @param <type> $request
     */
    public function execute($request) {

        $isPaging = $request->getParameter('pageNo');
        $sortField = $request->getParameter('sortField');
        $sortOrder = $request->getParameter('sortOrder');
        $projectId = $request->getParameter('projectId');

        $this->setForm(new SearchProjectForm());

        $pageNumber = $isPaging;
        $limit = Project::NO_OF_RECORDS_PER_PAGE;
        $offset = ($pageNumber >= 1) ? (($pageNumber - 1) * $limit) : ($request->getParameter('pageNo', 1) - 1) * $limit;
        $searchClues = $this->_setSearchClues($sortField, $sortOrder, $offset, $limit);
        if (!empty($sortField) && !empty($sortOrder) || $isPaging > 0 || $projectId) {
            if ($this->getUser()->hasAttribute('searchClues')) {
                $searchClues = $this->getUser()->getAttribute('searchClues');
                $searchClues['offset'] = $offset;
                $searchClues['sortField'] = $sortField;
                $searchClues['sortOrder'] = $sortOrder;
                $this->form->setDefaultDataToWidgets($searchClues);
            }
        } else {
            $this->getUser()->setAttribute('searchClues', $searchClues);
        }
        $projectList = $this->getProjectService()->searchProjects($searchClues);
        $projectListCount = $this->getProjectService()->getSearchProjectListCount($searchClues);
        $this->_setListComponent($projectList, $limit, $pageNumber, $projectListCount);
        $params = array();
        $this->parmetersForListCompoment = $params;

        if ($this->getUser()->hasFlash('templateMessage')) {
            list($this->messageType, $this->message) = $this->getUser()->getFlash('templateMessage');
        }

        if ($request->isMethod('post')) {
            $offset = 0;
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $searchClues = $this->_setSearchClues($sortField, $sortOrder, $offset, $limit);
                $this->getUser()->setAttribute('searchClues', $searchClues);
                $searchedProjectList = $this->getProjectService()->searchProjects($searchClues);
                $projectListCount = $this->getProjectService()->getSearchProjectListCount($searchClues);
                $this->_setListComponent($searchedProjectList, $limit, $pageNumber, $projectListCount);
            }
        }
    }

    /**
     *
     * @param <type> $projectList
     * @param <type> $noOfRecords
     * @param <type> $pageNumber
     */
    private function _setListComponent($projectList, $limit, $pageNumber, $recordCount) {
        $configurationFactory = new ProjectHeaderFactory();
        ohrmListComponent::setPageNumber($pageNumber);
        ohrmListComponent::setConfigurationFactory($configurationFactory);
        ohrmListComponent::setListData($projectList);
        ohrmListComponent::setItemsPerPage($limit);
        ohrmListComponent::setNumberOfRecords($recordCount);
    }

    private function _setSearchClues($sortField, $sortOrder, $offset, $limit) {
        return array(
            'customer' => $this->form->getValue('customer'),
            'project' => $this->form->getValue('project'),
            'projectAdmin' => $this->form->getValue('projectAdmin'),
            'sortField' => $sortField,
            'sortOrder' => $sortOrder,
            'offset' => $offset,
            'limit' => $limit
        );
    }

}
