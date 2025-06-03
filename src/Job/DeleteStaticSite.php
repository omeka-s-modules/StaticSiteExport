<?php
namespace StaticSiteExport\Job;

class DeleteStaticSite extends AbstractStaticSiteJob
{
    /**
     * Delete the static site server artifacts.
     */
    public function perform(): void
    {
        $this->deleteSiteDirectory();
        $this->deleteSiteZip();
    }
}
