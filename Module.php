<?php
namespace StaticSiteExport;

use Omeka\Module\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\Controller\AbstractController;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Renderer\PhpRenderer;
use StaticSiteExport\Form\ModuleConfigForm;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include sprintf('%s/config/module.config.php', __DIR__);
    }

    public function install(ServiceLocatorInterface $services)
    {
        $sql = <<<'SQL'
CREATE TABLE static_site (id INT UNSIGNED AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, site_id INT NOT NULL, job_id INT DEFAULT NULL, created DATETIME NOT NULL, name VARCHAR(255) DEFAULT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:json)', INDEX IDX_F2ED50517E3C61F9 (owner_id), INDEX IDX_F2ED5051F6BD1646 (site_id), INDEX IDX_F2ED5051BE04EA9 (job_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE static_site ADD CONSTRAINT FK_F2ED50517E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE SET NULL;
ALTER TABLE static_site ADD CONSTRAINT FK_F2ED5051F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE;
ALTER TABLE static_site ADD CONSTRAINT FK_F2ED5051BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) ON DELETE SET NULL;
SQL;
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec($sql);
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $settings = $services->get('Omeka\Settings');

        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec('DROP TABLE IF EXISTS static_site;');
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function getConfigForm(PhpRenderer $view)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        $form = $services->get('FormElementManager')->get(ModuleConfigForm::class);
        $form->setData([
            'sites_directory_path' => $settings->get('static_site_export_sites_directory_path'),
        ]);
        return $view->partial('common/static-site-export-config-form', ['form' => $form]);
    }

    public function handleConfigForm(AbstractController $controller)
    {
        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings');
        $form = $services->get('FormElementManager')->get(ModuleConfigForm::class);
        $form->setData($controller->params()->fromPost());
        if ($form->isValid()) {
            $formData = $form->getData();
            $settings->set('static_site_export_sites_directory_path', $formData['sites_directory_path']);
            return true;
        }
        $controller->messenger()->addErrors($form->getMessages());
        return false;
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
    }

    public static function sitesDirectoryPathIsValid(string $sitesDirectoryPath)
    {
        return (is_dir($sitesDirectoryPath) && is_writable($sitesDirectoryPath));
    }
}
