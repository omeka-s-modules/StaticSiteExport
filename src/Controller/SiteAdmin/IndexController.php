<?php
namespace StaticSiteExport\Controller\SiteAdmin;

use Doctrine\ORM\EntityManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;
use Omeka\Stdlib\Message;
use StaticSiteExport\Form\StaticSiteForm;
use StaticSiteExport\Job\DeleteStaticSite;
use StaticSiteExport\Job\ExportStaticSite;

class IndexController extends AbstractActionController
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function indexAction()
    {
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function browseAction()
    {
        $this->setBrowseDefaults('created');
        $query = array_merge(
            $this->params()->fromQuery(),
            ['site_id' => $this->currentSite()->id()]
        );
        $response = $this->api()->search('static_site_export_static_sites', $query);
        $this->paginator($response->getTotalResults(), $this->params()->fromQuery('page'));
        $staticSites = $response->getContent();

        $view = new ViewModel;
        $view->setVariable('staticSites', $staticSites);
        $view->setVariable('site', $this->currentSite());
        return $view;
    }

    public function exportAction()
    {
        $form = $this->getForm(StaticSiteForm::class);

        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            $form->setData($postData);
            if ($form->isValid()) {
                // Set the static site data.
                $formData = $form->getData();
                $formData['o:data'] = [
                    'base_url' => $formData['base_url'],
                    'theme' => $formData['theme'],
                    'include_private' => $formData['include_private'],
                ];
                $site = $this->currentSite();
                $formData['o:site'] = ['o:id' => $site->id()];
                // Create the static site resource.
                $response = $this->api($form)->create('static_site_export_static_sites', $formData);
                if ($response) {
                    $staticSite = $response->getContent();
                    // Dispatch the static site export job.
                    $job = $this->jobDispatcher()->dispatch(
                        ExportStaticSite::class,
                        ['static_site_id' => $staticSite->id()]
                    );
                    // Set the job and directory name to the static site entity.
                    $staticSiteEntity = $this->entityManager->find('StaticSiteExport\Entity\StaticSite', $staticSite->id());
                    $staticSiteEntity->setJob($job);
                    $name = sprintf('%s-%s', $site->slug(), $job->getId());
                    $staticSiteEntity->setName($name);
                    $this->entityManager->flush();
                    // Set the message and redirect to browse.
                    $message = new Message(
                        '%s <a href="%s">%s</a>',
                        $this->translate('Exporting static site. This may take a while.'),
                        htmlspecialchars($this->url()->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()])),
                        $this->translate('See this job for progress.')
                    );
                    $message->setEscapeHtml(false);
                    $this->messenger()->addSuccess($message);
                    return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        return $view;
    }

    public function deleteConfirmAction()
    {
        $staticSite = $this->api()->read('static_site_export_static_sites', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $staticSite);
        $view->setVariable('resourceLabel', 'static site'); // @translate
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $staticSite = $this->api()->read('static_site_export_static_sites', $this->params('id'))->getContent();
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('static_site_export_static_sites', $staticSite->id());
                if ($response) {
                    // Dispatch the static site delete job only if the export job
                    // is at a state where it can be done safely.
                    if (in_array($staticSite->job()->status(), ['completed', 'stopped', 'error'])) {
                        $job = $this->jobDispatcher()->dispatch(
                            DeleteStaticSite::class,
                            ['static_site_name' => $staticSite->name()]
                        );
                        $message = new Message(
                            '%s <a href="%s">%s</a>',
                            $this->translate('Successfully deleted the static site resource. Deleting static site artifacts from the server.'),
                            htmlspecialchars($this->url()->fromRoute('admin/id', ['controller' => 'job', 'id' => $job->getId()])),
                            $this->translate('See this job for progress.')
                        );
                        $message->setEscapeHtml(false);
                    } else {
                        $message = $this->translate('Successfully deleted the static site resource. Could not delete static site artifacts from the server.');
                    }
                    $this->messenger()->addSuccess($message);
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function showDetailsAction()
    {
        $staticSite = $this->api()->read('static_site_export_static_sites', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('staticSite', $staticSite);
        return $view;
    }
}
