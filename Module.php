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
        // Add assets from the "asset" block layout
        $sharedEventManager->attach(
            'StaticSiteExport\Job\ExportStaticSite',
            'static_site_export.ids.assets',
            function (Event $event) {
                $job = $event->getTarget();
                $entityManager = $job->get('Omeka\EntityManager');
                $addIds = $event->getParam('addIds');

                $dql = "SELECT b
                    FROM Omeka\Entity\SitePageBlock b
                    JOIN b.page p
                    JOIN p.site s
                    WHERE s.id = :siteId
                    AND b.layout = 'asset'";
                $query = $entityManager->createQuery($dql);
                $query->setParameters([
                    'siteId' => $job->getStaticSite()->site()->id(),
                ]);
                $blocks = $query->getResult();
                foreach ($blocks as $block) {
                    foreach ($block->getData() as $assetData) {
                        $addIds[] = $assetData['id'];
                    }
                }
            }
        );
        // Add the itemLink block to the media page.
        $sharedEventManager->attach(
            'StaticSiteExport\Job\ExportStaticSite',
            'static_site_export.bundle.media',
            function (Event $event) {
                $job = $event->getTarget();
                $media = $event->getParam('resource');
                $blocks = $event->getParam('blocks');

                $frontMatter = [
                    'params' => [
                        'layout' => 'sseItemLink', // namespace with module name
                    ],
                ];
                $markdown = sprintf("## %s\n%s", $job->translate('Item'), $job->getLinkMarkdown($media->item(), [
                    'thumbnailType' => 'square',
                    'thumbnailHeight' => 40,
                ]));
                $blocks[] = [
                    'name' => 'sseItemLink',
                    'frontMatter' => $frontMatter,
                    'markdown' => $markdown,
                ];
            }
        );
        // Add the itemList block to the item set page.
        $sharedEventManager->attach(
            'StaticSiteExport\Job\ExportStaticSite',
            'static_site_export.bundle.item_set',
            function (Event $event) {
                $job = $event->getTarget();
                $itemSet = $event->getParam('resource');
                $blocks = $event->getParam('blocks');

                $frontMatter = [
                    'params' => [
                        'layout' => 'sseItemList', // namespace with module name
                    ],
                ];
                $items = $job->get('Omeka\ApiManager')->search('items', [
                    'item_set_id' => $itemSet->id(),
                    'site_id' => $job->getStaticSite()->site()->id(),
                ])->getContent();
                if (!$items) {
                    return;
                }
                $markdown = sprintf("## %s\n", $job->translate('Items'));
                foreach ($items as $item) {
                    $markdown .= sprintf(
                        "- %s\n",
                        $job->getLinkMarkdown($item, [
                            'thumbnailType' => 'square',
                            'thumbnailHeight' => 40,
                        ])
                    );
                }
                $blocks[] = [
                    'name' => 'sseItemList',
                    'frontMatter' => $frontMatter,
                    'markdown' => $markdown,
                ];
            }
        );
        // Add the figure block to the asset page.
        $sharedEventManager->attach(
            'StaticSiteExport\Job\ExportStaticSite',
            'static_site_export.bundle.asset',
            function (Event $event) {
                $job = $event->getTarget();
                $asset = $event->getParam('resource');
                $blocks = $event->getParam('blocks');

                $frontMatter = [
                    'params' => [
                        'layout' => 'sseAsset', // namespace with module name
                    ],
                ];
                $markdown = $job->getFigureShortcode([
                    'type' => 'image',
                    'filePage' => sprintf('/assets/%s', $asset->id()),
                    'fileResource' => 'file',
                    'imgPage' => sprintf('/assets/%s', $asset->id()),
                    'imgResource' => 'file',
                    'linkPage' => sprintf('/assets/%s', $asset->id()),
                    'linkResource' => 'file',
                ]);
                $blocks[] = [
                    'name' => 'sseAsset',
                    'frontMatter' => $frontMatter,
                    'markdown' => $markdown,
                ];
            }
        );

    }

    public static function sitesDirectoryPathIsValid(string $sitesDirectoryPath)
    {
        return (is_dir($sitesDirectoryPath) && is_writable($sitesDirectoryPath));
    }
}
