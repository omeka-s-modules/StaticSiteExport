<?php
namespace StaticSiteExport\BlockLayout;

use ArrayObject;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Job\JobInterface;

class Media implements BlockLayoutInterface
{
    public function getMarkdown(
        JobInterface $job,
        SitePageBlockRepresentation $block,
        ArrayObject $frontMatterPage,
        ArrayObject $frontMatterBlock
    ): string {
        $attachments = $block->attachments();
        if (!$attachments) {
            return '';
        }

        $layout = $block->dataValue('layout');
        $mediaDisplay = $block->dataValue('media_display');
        $thumbnailType = $block->dataValue('thumbnail_type', 'square');
        $showTitleOption = $block->dataValue('show_title_option', 'item_title');

        // Set the classes to the block's container via front matter.
        $classes = ['media-embed'];
        if ('horizontal' === $layout) {
            $classes[] = 'layout-horizontal';
        } else {
            $classes[] = 'layout-vertical';
        }
        if ('thumbnail' === $mediaDisplay) {
            $classes[] = 'media-display-thumbnail';
        } else {
            $classes[] = 'media-display-embed';
        }
        if (3 < count($attachments)) {
            $classes[] = 'multiple-attachments';
        } else {
            $classes[] = 'attachment-count-' . count($attachments);
        }
        $frontMatterBlock['params']['container'] = [
            'classes' => $classes,
        ];

        $markdown = [];
        foreach($attachments as $attachment) {
            $item = $attachment->item();
            $media = $attachment->media() ?: $item->primaryMedia();
            if ($media) {
                if ('thumbnail' === $mediaDisplay) {
                    $markdown[] = $job->getThumbnailShortcode($media, ['thumbnailType' => $thumbnailType]);
                } else {
                    $mediaRenderer = $job->get('StaticSiteExport\MediaRendererManager')->get($media->renderer());
                    $markdown[] = $mediaRenderer->getMarkdown($job, $media, $frontMatterPage, $frontMatterBlock);
                }
            }
        }

        return implode("\n\n", $markdown);
    }
}
