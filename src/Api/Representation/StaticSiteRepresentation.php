<?php
namespace StaticSiteExport\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class StaticSiteRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLdType()
    {
        return 'o-module-static-site-export:StaticSite';
    }

    public function getJsonLd()
    {
        $owner = $this->owner();
        $site = $this->site();
        $job = $this->job();
        return [
            'o:owner' => $owner ? $owner->getReference() : null,
            'o:site' => $site->getReference(),
            'o:job' => $job ? $job->getReference() : null,
            'o:created' => $this->getDateTime($this->created()),
            'o:name' => $this->name(),
            'o:data' => $this->data(),
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/site/slug/static-site-export',
            [
                'site-slug' => $this->site()->slug(),
                'id' => $this->id(),
                'action' => $action,
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function owner()
    {
        return $this->getAdapter('users')->getRepresentation($this->resource->getOwner());
    }

    public function site()
    {
        return $this->getAdapter('sites')->getRepresentation($this->resource->getSite());
    }

    public function job()
    {
        return $this->getAdapter('jobs')->getRepresentation($this->resource->getJob());
    }

    public function created()
    {
        return $this->resource->getCreated();
    }

    public function name()
    {
        return $this->resource->getName();
    }

    public function data()
    {
        return $this->resource->getData();
    }

    public function dataValue(string $key, $default = null)
    {
        $data = $this->data();
        return $data[$key] ?? $default;
    }
}
